<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Bridge\SyliusBridgePlugin\Client\BridgePaymentApiClientInterface;
use Bridge\SyliusBridgePlugin\Manager\PaymentStateManagerInterface;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface;

use function in_array;

final class BridgeStatusService implements BridgeStatusServiceInterface
{
    public function __construct(private PaymentStateManagerInterface $paymentStateManager)
    {
    }

    /**
     * This function allows to update the statuses using the PaymentStateManager
     * (see https://docs.bridgeapi.io/docs/payments-statuses)
     */
    public function matchStatus(string $status, ?PaymentInterface $payment): void
    {
        if ($payment === null) {
            return;
        }

        if (in_array($status, BridgePaymentApiClientInterface::STATUS_NEW, true)) {
            $this->paymentStateManager->create($payment);

            return;
        }

        if (in_array($status, BridgePaymentApiClientInterface::STATUS_PROCESSING, true)) {
            $this->paymentStateManager->process($payment);

            return;
        }

        if (in_array($status, BridgePaymentApiClientInterface::STATUS_COMPLETED, true)) {
            $this->paymentStateManager->complete($payment);

            return;
        }

        $this->paymentStateManager->fail($payment);
    }

    /**
     * This function allows to update the status using the GetStatusInterface
     * (see https://docs.bridgeapi.io/docs/payments-statuses)
     */
    public function updateStatus(GetStatusInterface $request, string $status, ?PaymentInterface $payment): void
    {
        if ($payment === null) {
            return;
        }

        if (in_array($status, BridgePaymentApiClientInterface::STATUS_NEW, true)) {
            $request->markNew();

            return;
        }

        if (in_array($status, BridgePaymentApiClientInterface::STATUS_PROCESSING, true)) {
            $request->markPending();

            return;
        }

        if (in_array($status, BridgePaymentApiClientInterface::STATUS_COMPLETED, true)) {
            $request->markCaptured();

            return;
        }

        $request->markFailed();
    }
}
