<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Bridge\SyliusBridgePlugin\Client\BridgePaymentApiClient;
use Bridge\SyliusBridgePlugin\Client\BridgePaymentApiClientInterface;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentApiWrongSignatureException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentEmptyPayloadException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentFailedSignatureVerificationException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentMethodNotConfiguredException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentMissingSignatureException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentUnknownTransactionTypeException;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Safe\DateTime;
use Safe\Exceptions\JsonException;
use Safe\Exceptions\StringsException;
use Safe\Exceptions\UrlException;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\PaymentMethod;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

use function explode;
use function hash_hmac;
use function Safe\json_decode;
use function Safe\substr;
use function str_starts_with;
use function strtoupper;

final class BridgeWebhookService implements BridgeWebhookServiceInterface
{
    private ?array $config;

    /**
     * @throws UrlException
     * @throws BridgePaymentMethodNotConfiguredException
     */
    public function __construct(
        private LoggerInterface $logger,
        private BridgeStatusServiceInterface $bridgeStatusService,
        private RepositoryInterface $orderRepository,
        private BridgePaymentGatewayService $bridgePaymentGatewayService,
        private EntityManagerInterface $paymentMethodManager,
        private RepositoryInterface $paymentRepository,
        private CryptDecryptServiceInterface $cryptDecryptService
    ) {
        $paymentMethod = $this->bridgePaymentGatewayService->getBridgePaymentMethod();

        $paymentMethod = $this->cryptDecryptService->decryptGatewayConfig($paymentMethod);

        $this->config = $paymentMethod->getGatewayConfig()?->getConfig();

        if ($this->config === null) {
            throw new BridgePaymentMethodNotConfiguredException();
        }
    }

    public function getGatewayConfig(): ?array
    {
        return $this->config;
    }

    public function getConfiguredClient(): BridgePaymentApiClientInterface
    {
        $client = new BridgePaymentApiClient(new Client(), new Logger('console'));

        // Set BridgePaymentApiClient config
        $client->setConfig(
            $this->config['clientId'] ?? null,
            $this->config['clientSecret'] ?? null,
            $this->config['webhookSecret'] ?? null,
            $this->config['testClientId'] ?? null,
            $this->config['testClientSecret'] ?? null,
            $this->config['testWebhookSecret'] ?? null,
        );

        return $client;
    }

    /**
     * This function allows to check the received signatures from the Bridge API
     * (see https://docs.bridgeapi.io/docs/secure-your-webhooks)
     *
     * @throws BridgePaymentApiWrongSignatureException
     * @throws BridgePaymentFailedSignatureVerificationException
     * @throws BridgePaymentMissingSignatureException
     * @throws StringsException
     */
    public function checkSignatures(Request $request, string $payload, ?string $webhookSecret): void
    {
        $signatures = $request->headers->get('bridgeapi-signature');

        if ($signatures === null) {
            throw new BridgePaymentMissingSignatureException();
        }

        $signatures = explode(',', $signatures);

        $bool = false;
        foreach ($signatures as $signature) {
            // Remove prefix from signature
            $signature = $this->removePrefixFromSignature($signature);
            // If the signature is verified at least once then the response has come from Bridge API
            // The signature is verified with the prod webhook secret and the test webhook secret at once.
            $bool = $bool || $this->verifySignature($payload, $webhookSecret, $signature);
        }

        // If the signature was not verified then raise an exception
        if ($bool === false) {
            $this->logger->error('The signature verification has failed.');

            throw new BridgePaymentFailedSignatureVerificationException();
        }
    }

    /**
     * This function allows to check whether the payload sent by the API is not empty
     * An BridgePaymentEmptyPayload is thrown if it was the case
     *
     * @throws BridgePaymentEmptyPayloadException
     */
    public function getPayload(Request $request): string
    {
        $payload = $request->getContent();
        //@phpstan-ignore-next-line
        if (empty($payload)) {
            $this->logger->error('The received payload is empty.');

            throw new BridgePaymentEmptyPayloadException();
        }

        return (string) $payload;
    }

    /**
     * This function allows to remove the prefix 'v1=' from Bridge API signature
     * (see https://docs.bridgeapi.io/docs/secure-your-webhooks)
     *
     * @throws BridgePaymentApiWrongSignatureException|StringsException
     */
    public function removePrefixFromSignature(string $signature): string
    {
        if (! str_starts_with($signature, 'v1=')) {
            $this->logger->error('The received signature has wrong prefix.');

            throw new BridgePaymentApiWrongSignatureException();
        }

        return substr($signature, 3);
    }

    /**
     * This function allows to do a check by signature verification to ensure that the response comes from Bridge API
     * (see https://docs.bridgeapi.io/docs/secure-your-webhooks)
     */
    public function verifySignature(string $payload, ?string $webhookSecret, string $signature): bool
    {
        if ($webhookSecret === null) {
            $this->logger->error('WebhookSecret is null in BridgeWebhookService.php VerifySignature');

            return false;
        }

        $hash = hash_hmac('SHA256', $payload, $webhookSecret);

        return strtoupper((string) $hash) === $signature;
    }

    /**
     * This function allows to update the status of payment depending on the webhook response status
     * (see https://docs.bridgeapi.io/docs/payments-statuses)
     */
    public function updateStatus(string $status, string $orderId): void
    {
        /** @var Order $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);

        $payment = $order->getLastPayment();

        $this->bridgeStatusService->matchStatus($status, $payment);
    }

    /**
     * @throws BridgePaymentMethodNotConfiguredException
     * @throws BridgePaymentUnknownTransactionTypeException
     * @throws StringsException
     * @throws BridgePaymentMissingSignatureException
     * @throws BridgePaymentFailedSignatureVerificationException
     * @throws BridgePaymentApiWrongSignatureException
     * @throws BridgePaymentEmptyPayloadException
     * @throws JsonException
     */
    public function updateStatusViaWebhook(Request $request, string $type): void
    {
        // Get webhook payload & check signatures
        $payload = $this->getPayload($request);

        if ($this->config === null) {
            throw new BridgePaymentMethodNotConfiguredException();
        }

        $webhookSecret = $type === 'prod' ? $this->config['webhookSecret'] : $this->config['testWebhookSecret'];

        // Check signature
        $this->checkSignatures($request, $payload, $webhookSecret);

        // Get payload's data & update the status of the payment
        $payload = json_decode($payload);

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $this->bridgePaymentGatewayService->getBridgePaymentMethod();

        switch ($payload->type) {
            case BridgePaymentApiClient::WEBHOOK_TEST_EVENT:
                $paymentMethod->setTestWebhookConfigurationDate(new DateTime());
                $this->paymentMethodManager->flush();

                $this->logger->info('WEBHOOK-TEST-EVENT: The signature was verified successfully.');

                break;
            case BridgePaymentApiClient::WEBHOOK_PAYMENT_TRANSACTION_UPDATED_EVENT:
                $paymentMethod->setProductionWebhookConfigurationDate(new DateTime());
                $this->paymentMethodManager->flush();

                $this->logger->info('WEBHOOK-PAYMENT-TRANSACTION-UPDATED-EVENT: The signature was verified successfully.');

                // The client reference received from the API corresponds to the id of the payment
                $clientReference = $payload->content->client_reference;
                $payment = $this->paymentRepository->findOneBy(['id' => $clientReference]);
                $this->bridgeStatusService->matchStatus($payload->content->status, $payment);

                break;
            default:
                // Unknown transaction type
                $this->logger->error('Unknown transaction type.');

                throw new BridgePaymentUnknownTransactionTypeException();
        }
    }
}
