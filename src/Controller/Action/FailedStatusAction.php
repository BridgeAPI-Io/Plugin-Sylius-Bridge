<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Controller\Action;

use App\Entity\Payment\Payment;
use Monolog\Logger;
use Payum\Core\Reply\HttpRedirect;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityToken;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

final class FailedStatusAction
{
    public function __construct(
        private RepositoryInterface $paymentSecurityTokenRepository,
        private RepositoryInterface $paymentRepository,
        private Logger $logger
    ) {
    }

    public function __invoke(Request $request): void
    {
        $hash = $request->get('payum_token');

        /** @var ?PaymentSecurityToken $token */
        $token = $this->paymentSecurityTokenRepository->findOneBy(['hash' => $hash]);

        if ($token === null) {
            $this->logger->error('$token is null in Bridge\SyliusBridgePlugin\Controller\Action\FailedStatusAction.php');

            return;
        }

        /** @var Payment $payment */
        $payment = $this->paymentRepository->findOneBy(['id' => $token->getDetails()->getId()]); //@phpstan-ignore-line

        $request = new GetStatus($payment);

        $request->markFailed();

        if ($token->getAfterUrl() !== null) {
            throw new HttpRedirect($token->getAfterUrl());
        }
    }
}
