<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Controller\Webhook;

use Bridge\SyliusBridgePlugin\Exception\BridgePaymentApiWrongSignatureException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentEmptyPayloadException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentFailedSignatureVerificationException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentMethodNotConfiguredException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentMissingSignatureException;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentUnknownTransactionTypeException;
use Bridge\SyliusBridgePlugin\Service\BridgeWebhookServiceInterface;
use Safe\Exceptions\JsonException;
use Safe\Exceptions\StringsException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Safe\json_encode;

final class WebhookAction
{
    public function __construct(
        private BridgeWebhookServiceInterface $webhookService
    ) {
    }

    /**
     * @throws BridgePaymentApiWrongSignatureException
     * @throws BridgePaymentEmptyPayloadException
     * @throws BridgePaymentFailedSignatureVerificationException
     * @throws BridgePaymentMethodNotConfiguredException
     * @throws BridgePaymentMissingSignatureException
     * @throws BridgePaymentUnknownTransactionTypeException
     * @throws JsonException
     * @throws StringsException
     */
    public function __invoke(Request $request): Response
    {
        $this->webhookService->updateStatusViaWebhook($request, 'prod');

        // If no exception was raised then the payment status was updated successfully
        $response = new Response(json_encode(['message' => 'success']), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
