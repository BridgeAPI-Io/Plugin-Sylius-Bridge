<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Controller\Action\Api;

use Bridge\SyliusBridgePlugin\Client\BridgePaymentApiClientInterface;
use Payum\Core\Exception\UnsupportedApiException;

trait ApiAwareTrait
{
    protected BridgePaymentApiClientInterface $bridgePaymentApiClient;

    public function setApi($bridgePaymentApiClient): void
    {
        if ($bridgePaymentApiClient instanceof BridgePaymentApiClientInterface === false) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . BridgePaymentApiClientInterface::class);
        }

        $this->bridgePaymentApiClient = $bridgePaymentApiClient;
    }
}
