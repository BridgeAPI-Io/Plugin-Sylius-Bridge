<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Safe\Exceptions\JsonException;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_merge;
use function GuzzleHttp\json_decode;
use function Safe\json_encode;
use function strtolower;
use function urlencode;

class BridgePaymentApiClient implements BridgePaymentApiClientInterface
{
    // Possible events that the plugin may receive from the API
    public const WEBHOOK_PAYMENT_TRANSACTION_UPDATED_EVENT = 'payment.transaction.updated';

    public const WEBHOOK_TEST_EVENT = 'TEST_EVENT';

    // Bridge API Endpoints
    private const BASE_URI = 'https://api.bridgeapi.io';

    private const GET_BANKS_ENDPOINT = 'https://api.bridgeapi.io/v2/banks?capabilities=single_payment&limit=500';

    private const CREATE_PAYMENT_REQUEST_ENDPOINT = 'https://api.bridgeapi.io/v2/payment-requests';

    private const GET_PAYMENT_REQUEST_ENDPOINT = 'https://api.bridgeapi.io/v2/payment-requests/';

    protected ?string $clientId = null;

    protected ?string $clientSecret = null;

    protected ?string $webhookSecret = null;

    protected ?string $testClientId = null;

    protected ?string $testClientSecret = null;

    protected ?string $testWebhookSecret = null;

    protected ?TranslatorInterface $translator;

    public function __construct(
        protected ClientInterface $client,
        protected Logger $logger
    ) {
    }

    public function setConfig(
        ?string $clientId,
        ?string $clientSecret,
        ?string $webhookSecret,
        ?string $testClientId,
        ?string $testClientSecret,
        ?string $testWebhookSecret
    ): void {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->webhookSecret = $webhookSecret;
        $this->testClientId = $testClientId;
        $this->testClientSecret = $testClientSecret;
        $this->testWebhookSecret = $testWebhookSecret;
    }

    public function getBanks(string $mode, ?string $localCode = null): ?array
    {
        $bankResources = [];
        $url = self::GET_BANKS_ENDPOINT;

        if ($localCode !== null) {
            $url .= '&countries=' . urlencode(strtolower($localCode));
        }

        do {
            $response = $this->request($mode, 'GET', $url);
            $resources = $response['resources'] ?? [];
            $bankResources = array_merge($bankResources, $resources);

            $url = isset($response['pagination']['next_uri']) ? self::BASE_URI . $response['pagination']['next_uri'] : null;
        } while ($url !== null);

        return ['resources' => $bankResources];
    }

    public function createBridgeRequestPayment(string $body, string $mode = 'test'): ?ResponseInterface
    {
        return $this->createRequestPayment($mode, 'POST', self::CREATE_PAYMENT_REQUEST_ENDPOINT, $body);
    }

    public function getBridgeRequestPayment(string $id, string $mode = 'test'): ?array
    {
        $url = self::GET_PAYMENT_REQUEST_ENDPOINT . $id;

        return $this->request($mode, 'GET', $url);
    }

    /**
     * @throws JsonException
     */
    public function getBody(array $body): string
    {
        return json_encode($body);
    }

    protected function getHeaders(string $mode): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Bridge-Version' => '2021-06-01',
            'Content-Type' => 'application/json',
        ];

        if ($mode === 'test') {
            $headers['Client-Id'] = $this->testClientId;
            $headers['Client-Secret'] = $this->testClientSecret;
        } else {
            $headers['Client-Id'] = $this->clientId;
            $headers['Client-Secret'] = $this->clientSecret;
        }

        return $headers;
    }

    protected function createRequestPayment(string $mode, string $method, string $url, ?string $body = null): ?ResponseInterface
    {
        $options = ['headers' => $this->getHeaders($mode)];

        if ($body !== null) {
            $options['body'] = $body;
        }

        try {
            $response = $this->client->request($method, $url, $options);
        } catch (GuzzleException $exception) {
            $this->logger->error($exception->getMessage());

            throw $exception;
        }

        return $response;
    }

    protected function request(string $mode, string $method, string $url, ?string $body = null): ?array
    {
        $options = ['headers' => $this->getHeaders($mode)];

        if ($body !== null) {
            $options['body'] = $body;
        }

        try {
            $result = $this->client->request($method, $url, $options);
        } catch (GuzzleException $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }

        return json_decode((string) $result->getBody(), true);
    }
}
