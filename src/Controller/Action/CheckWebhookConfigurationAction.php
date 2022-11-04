<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Controller\Action;

use Bridge\SyliusBridgePlugin\Exception\BridgePaymentMethodNotConfiguredException;
use Bridge\SyliusBridgePlugin\Service\BridgePaymentGatewayService;
use Safe\Exceptions\JsonException;
use Sylius\Component\Core\Model\PaymentMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Safe\json_encode;

final class CheckWebhookConfigurationAction
{
    private const TEST_TYPE = 'test';
    private const PRODUCTION_TYPE = 'production';

    public function __construct(
        private BridgePaymentGatewayService $bridgePaymentGatewayService,
    ) {
    }

    /**
     * @throws JsonException|BridgePaymentMethodNotConfiguredException
     */
    public function __invoke(Request $request): Response
    {
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $this->bridgePaymentGatewayService->getBridgePaymentMethod();

        $type = $request->get('type');

        $webhookConfigurationDate = null;

        if ($type === self::TEST_TYPE) {
            $webhookConfigurationDate = $paymentMethod->getTestWebhookConfigurationDate();
        }

        if ($type === self::PRODUCTION_TYPE) {
            $webhookConfigurationDate = $paymentMethod->getProductionWebhookConfigurationDate();
        }

        return new Response(json_encode(['message' => 'success', 'webhook_configured_at' => $webhookConfigurationDate?->format('d/m/Y H:i:s')]));
    }
}
