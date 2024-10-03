<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Client;

use Psr\Http\Message\ResponseInterface;

interface BridgePaymentApiClientInterface
{
    public const STATUS_NEW = ['CREA', 'ACTC'];
    public const STATUS_PROCESSING = ['PART', 'PDNG'];
    public const STATUS_COMPLETED = ['ACSC'];
    public const STATUS_FAILED = ['RJCT'];

    public function setConfig(
        ?string $clientId,
        ?string $clientSecret,
        ?string $webhookSecret,
        ?string $testClientId,
        ?string $testClientSecret,
        ?string $testWebhookSecret
    ): void;

    public function getBanks(string $mode, ?string $localCode = null): ?array;

    public function createBridgeRequestPayment(string $body, string $mode = 'test'): ?ResponseInterface;

    public function getBridgeRequestPayment(string $id, string $mode = 'test'): ?array;
}
