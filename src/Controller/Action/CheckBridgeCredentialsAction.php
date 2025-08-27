<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Controller\Action;

use Bridge\SyliusBridgePlugin\Client\BridgePaymentApiClientInterface;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Safe\json_encode;

final class CheckBridgeCredentialsAction
{
    private const TEST_TYPE = 'test';

    public function __construct(
        private BridgePaymentApiClientInterface $client,
    ) {
    }

    /** @throws JsonException */
    public function __invoke(Request $request): Response
    {
        $env = $request->get('type') === self::TEST_TYPE ? 'test' : 'production';

        $this->setClientConfig($request, $env);

        $banks = $this->client->getBanks($env);

        return new Response(json_encode(['verified' => isset($banks['resources'][0]['id'])]));
    }

    private function setClientConfig(Request $request, string $env): void
    {
        /** @var ?string $clientId */
        $clientId = $request->get('clientId');

        /** @var ?string $clientSecret */
        $clientSecret = $request->get('clientSecret');

        /** @var ?string $webhookSecret */
        $webhookSecret = $request->get('webhookSecret');

        if ($env === self::TEST_TYPE) {
            $this->client->setConfig(
                null,
                null,
                null,
                $clientId,
                $clientSecret,
                $webhookSecret,
            );
        } else {
            $this->client->setConfig(
                $clientId,
                $clientSecret,
                $webhookSecret,
                null,
                null,
                null,
            );
        }
    }
}
