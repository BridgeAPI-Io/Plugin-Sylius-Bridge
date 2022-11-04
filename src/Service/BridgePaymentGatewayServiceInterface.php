<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Bridge\SyliusBridgePlugin\Exception\BridgePaymentMethodNotConfiguredException;
use Sylius\Component\Core\Model\PaymentMethodInterface;

interface BridgePaymentGatewayServiceInterface
{
    /**
     * This function allows to retrieve the configured Bridge client
     *
     * @throws BridgePaymentMethodNotConfiguredException
     */
    public function getBridgeGatewayConfig(): array;

    /**
     * This function allows to retrieve the Bridge payment method
     *
     * @throws BridgePaymentMethodNotConfiguredException
     */
    public function getBridgePaymentMethod(): PaymentMethodInterface;
}
