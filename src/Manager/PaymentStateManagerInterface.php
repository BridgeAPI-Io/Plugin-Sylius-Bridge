<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Manager;

use Sylius\Component\Core\Model\PaymentInterface;

interface PaymentStateManagerInterface
{
    public function create(PaymentInterface $payment): void;

    public function process(PaymentInterface $payment): void;

    public function complete(PaymentInterface $payment): void;

    public function cancel(PaymentInterface $payment): void;

    public function fail(PaymentInterface $payment): void;
}
