<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface BridgeStatusServiceInterface
{
    /**
     * This function allows to update the statuses using the PaymentStateManager
     * (see https://docs.bridgeapi.io/docs/payments-statuses)
     */
    public function matchStatus(string $status, ?PaymentInterface $payment): void;

    /**
     * This function allows to update the status using the GetStatusInterface
     * (see https://docs.bridgeapi.io/docs/payments-statuses)
     */
    public function updateStatus(GetStatusInterface $request, string $status, ?PaymentInterface $payment): void;
}
