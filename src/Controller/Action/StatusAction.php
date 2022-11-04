<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Controller\Action;

use Bridge\SyliusBridgePlugin\Client\BridgePaymentApiClientInterface;
use Bridge\SyliusBridgePlugin\Controller\Action\Api\ApiAwareTrait;
use Bridge\SyliusBridgePlugin\Service\BridgeStatusServiceInterface;
use Monolog\Logger;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class StatusAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    public function __construct(
        BridgePaymentApiClientInterface $bridgePaymentApiClient,
        private BridgeStatusServiceInterface $bridgeStatusService,
        private Logger $logger
    ) {
        $this->setApi($bridgePaymentApiClient);
    }

    public function execute(mixed $request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $paymentMethod = $payment->getMethod();

        $mode = 'test';
        if ($paymentMethod !== null) {
            //@phpstan-ignore-next-line
            $mode = $paymentMethod->isTestMode() ? 'test' : 'production';
        }

        $paymentApiId = $payment->getPaymentApiId();

        if ($paymentApiId === null) {
            $this->logger->error('$paymentApiId is null in Bridge\SyliusBridgePlugin\Controller\Action\StatusAction.php');

            $request->markFailed();

            return;
        }

        $apiPayment = $this->bridgePaymentApiClient->getBridgeRequestPayment($paymentApiId, $mode);

        if ($apiPayment === null) {
            $this->logger->error('$apiPayment is null in Bridge\SyliusBridgePlugin\Controller\Action\StatusAction.php');

            $request->markFailed();

            return;
        }

        $this->bridgeStatusService->updateStatus($request, $apiPayment['status'], $payment);
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatusInterface &&
                $request->getModel() instanceof PaymentInterface;
    }
}
