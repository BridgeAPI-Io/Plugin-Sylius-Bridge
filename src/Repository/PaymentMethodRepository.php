<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentMethodRepository as BasePaymentMethodRepository;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class PaymentMethodRepository extends BasePaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    /**
     * @throws NonUniqueResultException
     */
    public function findOneByGatewayFactoryNameAndChannel(string $gatewayFactoryName, ChannelInterface $channel): ?PaymentMethodInterface
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.gatewayConfig', 'gatewayConfig')
            ->where('gatewayConfig.factoryName = :gatewayFactoryName')
            ->andWhere(':channel MEMBER OF o.channels')
//            ->andWhere('o.enabled = true')
            ->addOrderBy('o.position')
            ->setParameter('gatewayFactoryName', $gatewayFactoryName)
            ->setParameter('channel', $channel)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
