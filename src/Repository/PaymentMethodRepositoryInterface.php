<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Repository;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface as BasePaymentMethodRepositoryInterface;

interface PaymentMethodRepositoryInterface extends BasePaymentMethodRepositoryInterface
{
    public function findOneByGatewayFactoryNameAndChannel(string $gatewayFactoryName, ChannelInterface $channel): ?PaymentMethodInterface;
}
