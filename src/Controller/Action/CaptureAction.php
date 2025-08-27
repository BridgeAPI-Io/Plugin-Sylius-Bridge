<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Controller\Action;

use Bridge\SyliusBridgePlugin\Client\BridgePaymentApiClientInterface;
use Bridge\SyliusBridgePlugin\Controller\Action\Api\ApiAwareTrait;
use Bridge\SyliusBridgePlugin\Service\CryptDecryptServiceInterface;
use Bridge\SyliusBridgePlugin\Service\UserServiceInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Security\TokenInterface;
use Safe\Exceptions\JsonException;
use Safe\Exceptions\MiscException;
use Safe\Exceptions\OpensslException;
use Safe\Exceptions\UrlException;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

use function abs;
use function assert;
use function number_format;
use function Safe\json_decode;
use function Safe\json_encode;
use function str_contains;

use const JSON_UNESCAPED_SLASHES;

final class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    public function __construct(
        BridgePaymentApiClientInterface $bridgePaymentApiClient,
        private UserServiceInterface $securityService,
        private RouterInterface $router,
        private ChannelContextInterface $channelContext,
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
        private CryptDecryptServiceInterface $cryptDecryptService,
    ) {
        $this->setApi($bridgePaymentApiClient);
    }

    /**
     * @throws MiscException
     * @throws OpensslException
     * @throws UrlException
     */
    public function execute(mixed $request): void
    {
        assert($request instanceof Capture);

        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        //@phpstan-ignore-next-line
        $mode = $paymentMethod->isTestMode() ? 'test' : 'production';

        if (isset($details['orderToken']) === true) {
            return;
        }

        /** @var TokenInterface $token */
        $token = $request->getToken();

        // Get PaymentMethod with decrypted config
        $paymentMethod = $this->cryptDecryptService->decryptGatewayConfig($paymentMethod);

        $config = $paymentMethod->getGatewayConfig()?->getConfig();

        if ($config !== null) {
            $this->bridgePaymentApiClient->setConfig(
                $config['clientId'] ?? null,
                $config['clientSecret'] ?? null,
                $config['webhookSecret'] ?? null,
                $config['testClientId'] ?? null,
                $config['testClientSecret'] ?? null,
                $config['testWebhookSecret'] ?? null,
            );
        }

        try {
            $res = $this->bridgePaymentApiClient->createBridgeRequestPayment($this->getRequestBody($payment, $token), $mode);
            if ($res !== null) {
                /** @var array<string, string> $response */
                $response = json_decode($res->getContent(), true);
                $payment->setPaymentApiId($response['id']);

                throw new HttpRedirect($response['consent_url']);
            }
        } catch (ClientExceptionInterface | JsonException | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            if ($e instanceof ClientException) {
                $flashBag = $this->requestStack->getSession()->getFlashBag(); //@phpstan-ignore-line - polymorphism
                $flashBag->add('error', $this->translator->trans('bridge.payment_checkout.an_error_has_occurred'));
                if (str_contains($e->getMessage(), 'Currency')) {
                    $flashBag->add('error', $this->translator->trans('bridge.payment_checkout.currency_not_supported'));

                    return;
                }
            }

            $payment->setDetails(['status' => 400]);
        }
    }

    /** @throws JsonException */
    private function getRequestBody(PaymentInterface $payment, TokenInterface $token): string
    {
        $shopUser = $this->securityService->getAuthShopUser();

        $customer = $shopUser->getCustomer();

        $amount = $this->getFormattedAmount($payment);

        if ($payment->getMethod()?->isPaymentAccount()) {
            $transactions = (object) [
                'amount' => $amount,
                'currency' => $payment->getCurrencyCode(),
                'client_reference' => (string) $payment->getId(),
                'end_to_end_id' => (string) $payment->getId(),
            ];
        } else {
            $transactions = (object) [
                'amount' => $amount,
                'currency' => $payment->getCurrencyCode(),
                'label' => $this->channelContext->getChannel()->getName(),
                'client_reference' => (string) $payment->getId(),
                'end_to_end_id' => (string) $payment->getId(),
            ];
        }

        $user = (object) [
            'name' => $customer?->getFullName(),
        ];

        $body =  (object) [
            'successful_callback_url' => $token->getAfterUrl(),
            'unsuccessful_callback_url' => $this->getUnsuccessfulCallbackUrl($token),
            'transactions' => [$transactions],
            'user' => $user,
            'bank_id' => $payment->getBankId(),
        ];

        return json_encode($body, JSON_UNESCAPED_SLASHES);
    }

    private function getUnsuccessfulCallbackUrl(TokenInterface $token): ?string
    {
        $path = $this->router->generate(
            'bridge_failed_status',
            ['payum_token' => $token->getHash()],
            UrlGeneratorInterface::ABSOLUTE_PATH,
        );

        return $this->requestStack->getCurrentRequest()?->getUriForPath($path);
    }

    private function getFormattedAmount(PaymentInterface $payment): float
    {
        $currencyCode = $payment->getCurrencyCode();

        Assert::notNull($currencyCode);

        $this->gateway->execute($currency = new GetCurrency((string) $payment->getCurrencyCode()));

        $divisor = 10 ** $currency->exp;

        $amount = $payment->getAmount() ?? 0;

        return (float) number_format(abs($amount / $divisor), 2, '.', '');
    }

    public function supports($request): bool
    {
        return $request instanceof Capture &&
            $request->getModel() instanceof PaymentInterface;
    }
}
