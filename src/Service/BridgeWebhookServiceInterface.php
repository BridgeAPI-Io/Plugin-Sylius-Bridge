<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Bridge\SyliusBridgePlugin\Client\BridgePaymentApiClientInterface;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentApiWrongSignatureException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentEmptyPayloadException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentFailedSignatureVerificationException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentMethodNotConfiguredException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentMissingSignatureException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentUnknownTransactionTypeException;
use Safe\Exceptions\JsonException;
use Safe\Exceptions\StringsException;
use Symfony\Component\HttpFoundation\Request;

interface BridgeWebhookServiceInterface
{
    public function getGatewayConfig(): ?array;

    public function getConfiguredClient(): BridgePaymentApiClientInterface;

    /**
     * This function allows to check the received signatures from the Bridge API
     * (see https://docs.bridgeapi.io/docs/secure-your-webhooks)
     *
     * @throws BridgePaymentApiWrongSignatureException
     * @throws BridgePaymentFailedSignatureVerificationException
     * @throws BridgePaymentMissingSignatureException
     * @throws StringsException
     */
    public function checkSignatures(Request $request, string $payload, ?string $webhookSecret): void;

    /**
     * This function allows to check whether the payload sent by the API is not empty
     * An BridgePaymentEmptyPayload is thrown if it was the case
     *
     * @throws BridgePaymentEmptyPayloadException
     */
    public function getPayload(Request $request): string;

    /**
     * This function allows to remove the prefix 'v1=' from Bridge API signature
     * (see https://docs.bridgeapi.io/docs/secure-your-webhooks)
     *
     * @throws BridgePaymentApiWrongSignatureException|StringsException
     */
    public function removePrefixFromSignature(string $signature): string;

    /**
     * This function allows to do a check by signature verification to ensure that the response comes from Bridge API
     * (see https://docs.bridgeapi.io/docs/secure-your-webhooks)
     */
    public function verifySignature(string $payload, ?string $webhookSecret, string $signature): bool;

    /**
     * This function allows to update the status of payment depending on the webhook response status
     * (see https://docs.bridgeapi.io/docs/payments-statuses)
     */
    public function updateStatus(string $status, string $orderId): void;

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
    public function updateStatusViaWebhook(Request $request, string $type): void;
}
