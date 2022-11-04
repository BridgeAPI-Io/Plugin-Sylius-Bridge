<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Bridge\SyliusBridgePlugin\BridgePaymentGatewayFactory;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentMethodNotConfiguredException;
use Bridge\SyliusBridgePlugin\Repository\PaymentMethodRepositoryInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

class BridgePaymentGatewayService
{
    public function __construct(
        private LoggerInterface $logger,
        private ChannelContextInterface $channelContext,
        private PaymentMethodRepositoryInterface $paymentMethodRepository,
    ) {
    }

    /**
     * This function allows to retrieve the configured Bridge client
     *
     * @throws BridgePaymentMethodNotConfiguredException
     */
    public function getBridgeGatewayConfig(): array
    {
        // Get the Bridge GatewayConfig
        $paymentMethod = $this->getBridgePaymentMethod();

        // Get the decrypted config of Bridge Payment Method
        $config = $paymentMethod->getGatewayConfig()?->getConfig();

        // If config is null throw an exception
        if ($config === null) {
            $this->logger->error('The Bridge payment method gateway config is null.');

            throw new BridgePaymentMethodNotConfiguredException();
        }

        return $config;
    }

    /**
     * This function allows to retrieve the Bridge payment method
     *
     * @throws BridgePaymentMethodNotConfiguredException
     */
    public function getBridgePaymentMethod(): PaymentMethodInterface
    {
        // Get the current channel
        /** @var ChannelInterface $channel */
        $channel = $this->channelContext->getChannel();

        // Get the Bridge factory name
        $factoryName = BridgePaymentGatewayFactory::FACTORY_NAME;

        // From the channel & factoryName get the Bridge payment method
        $paymentMethod = $this->paymentMethodRepository->findOneByGatewayFactoryNameAndChannel($factoryName, $channel);

        // If the payment method is null then throw an exception
        if ($paymentMethod === null) {
            $this->logger->error('The Bridge payment method is not configured on the channel "' . $channel->getName() . '"');

            throw new BridgePaymentMethodNotConfiguredException();
        }

        return $paymentMethod;
    }
}
