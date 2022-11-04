<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Manager;

use Doctrine\Persistence\ObjectManager;
use SM\Factory\FactoryInterface;
use SM\SMException;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Resource\StateMachine\StateMachineInterface;

final class PaymentStateManager implements PaymentStateManagerInterface
{
    public function __construct(
        private FactoryInterface $stateMachineFactory,
        private ObjectManager $paymentManager,
    ) {
    }

    /**
     * @throws SMException
     */
    public function create(PaymentInterface $payment): void
    {
        $this->applyTransitionAndSave($payment, PaymentTransitions::TRANSITION_CREATE);
    }

    /**
     * @throws SMException
     */
    public function process(PaymentInterface $payment): void
    {
        $this->applyTransitionAndSave($payment, PaymentTransitions::TRANSITION_PROCESS);
    }

    /**
     * @throws SMException
     */
    public function complete(PaymentInterface $payment): void
    {
        $this->applyTransitionAndSave($payment, PaymentTransitions::TRANSITION_COMPLETE);
    }

    /**
     * @throws SMException
     */
    public function cancel(PaymentInterface $payment): void
    {
        $this->applyTransitionAndSave($payment, PaymentTransitions::TRANSITION_CANCEL);
    }

    /**
     * @throws SMException
     */
    public function fail(PaymentInterface $payment): void
    {
        $this->applyTransitionAndSave($payment, PaymentTransitions::TRANSITION_FAIL);
    }

    /**
     * @throws SMException
     */
    private function applyTransitionAndSave(PaymentInterface $payment, string $transition): void
    {
        /** @var StateMachineInterface $stateMachine */
        $stateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);

        if (! $stateMachine->can($transition)) {
            return;
        }

        $stateMachine->apply($transition);
        $this->paymentManager->flush();
    }
}
