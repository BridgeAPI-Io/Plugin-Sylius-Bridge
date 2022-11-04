<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\EventListener;

use App\Entity\Payment\PaymentMethod;
use Bridge\SyliusBridgePlugin\BridgePaymentGatewayFactory;
use Bridge\SyliusBridgePlugin\Client\BridgePaymentApiClientInterface;
use Bridge\SyliusBridgePlugin\Service\CryptDecryptServiceInterface;
use Safe\Exceptions\UrlException;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentMethodListener
{
    public function __construct(
        private BridgePaymentApiClientInterface $client,
        private FlashBagInterface $flashBag,
        private TranslatorInterface $translator,
        private CryptDecryptServiceInterface $cryptDecryptService
    ) {
    }

    /**
     * @throws UrlException
     */
    public function preCreate(ResourceControllerEvent $event): void
    {
        $this->preAction($event);
    }

    /**
     * @throws UrlException
     */
    public function preUpdate(ResourceControllerEvent $event): void
    {
        $this->preAction($event);
    }

    /**
     * @throws UrlException
     */
    private function preAction(ResourceControllerEvent $event): void
    {
        $paymentMethod = $event->getSubject();

        $factoryName = $paymentMethod->getGatewayConfig()->getFactoryName();

        if ($factoryName !== BridgePaymentGatewayFactory::FACTORY_NAME) {
            return;
        }

        $this->checkBridgeTestApiKeys($paymentMethod);

        $this->checkPaymentMethodEnabledField($paymentMethod);

        $this->cryptDecryptService->encryptGatewayConfig($paymentMethod);
    }

    private function checkBridgeTestApiKeys(PaymentMethod $paymentMethod): void
    {
        if (! $this->configureClient($paymentMethod)) {
            return;
        }

        $config = $paymentMethod->getGatewayConfig()?->getConfig();

        if ($this->isTestKeysAreValid($config)) {
            return;
        }

        $paymentMethod->setEnabled(false);

        $this->flashBag->add('warning', $this->translator->trans('bridge.payment_method.invalid_or_not_supplied_api_keys', [], 'flashes'));

        $this->flashBag->add('warning', $this->translator->trans('bridge.payment_method.unable_to_activate_the_bridge_plugin', [], 'flashes'));
    }

    private function checkPaymentMethodEnabledField(PaymentMethod $paymentMethod): void
    {
        $config = $paymentMethod->getGatewayConfig()?->getConfig();

        if ($config === null) {
            return;
        }

        if (! $this->paymentMethodCanBeEnabled($paymentMethod->isEnabled(), $paymentMethod->isTestMode(), $config)) {
            return;
        }

        $paymentMethod->setEnabled(false);
    }

    /**
     * @param array<string> $config
     */
    private function paymentMethodCanBeEnabled(bool $isEnabled, bool $isTestMode, array $config): bool
    {
        return ! $this->isTestKeysAreProvided($config) ||
            ! $this->isTestKeysAreValid($config) &&
            $isEnabled &&
            ! $isTestMode;
    }

    /**
     * @param array<string> $config
     */
    private function isTestKeysAreProvided(array $config): bool
    {
        return isset($config['testClientId']) &&
            isset($config['testClientSecret']) &&
            isset($config['testWebhookSecret']);
    }

    /**
     * @param array<string> $config
     */
    private function isTestKeysAreValid(?array $config): bool
    {
        if ($config === null) {
            return false;
        }

        $banks = $this->client->getBanks('test');

        return isset($banks['resources']);
    }

    /**
     * @param array<string> $config
     */
    private function isProdKeysAreValid(?array $config): bool
    {
        if ($config === null) {
            return false;
        }

        $banks = $this->client->getBanks('prod');

        return isset($banks['resources']);
    }

    private function configureClient(PaymentMethod $paymentMethod): bool
    {
        $config = $paymentMethod->getGatewayConfig()?->getConfig();

        if ($config === null) {
            return false;
        }

        $this->client->setConfig(
            $config['clientId'] ?? null,
            $config['clientSecret'] ?? null,
            $config['webhookSecret'] ?? null,
            $config['testClientId'] ?? null,
            $config['testClientSecret'] ?? null,
            $config['testWebhookSecret'] ?? null,
        );

        return true;
    }
}
