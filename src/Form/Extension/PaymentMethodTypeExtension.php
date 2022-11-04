<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Form\Extension;

use Bridge\SyliusBridgePlugin\BridgePaymentGatewayFactory;
use Bridge\SyliusBridgePlugin\Service\CryptDecryptServiceInterface;
use Safe\Exceptions\MiscException;
use Safe\Exceptions\OpensslException;
use Safe\Exceptions\UrlException;
use Sylius\Bundle\PaymentBundle\Form\Type\PaymentMethodType;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class PaymentMethodTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private CryptDecryptServiceInterface $cryptDecryptService
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
    }

    /**
     * @throws MiscException|OpensslException|UrlException
     */
    public function onPreSetData(FormEvent $event): void
    {
        /** @var PaymentMethodInterface $data */
        $data = $event->getData();
        $form = $event->getForm();

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $data->getGatewayConfig();

        if ($gatewayConfig->getFactoryName() !== BridgePaymentGatewayFactory::FACTORY_NAME) {
            return;
        }

        $data = $this->cryptDecryptService->decryptGatewayConfig($data);

        $event->setData($data);

        $form
            ->add('testMode', CheckboxType::class, [
                'required' => false,
                'label' => 'bridge.payment_method.explanation_block.enable_test_mode',
            ])
            ->add('bridgeLogo', CheckboxType::class, [
                'required' => false,
                'label' => 'bridge.payment_method.explanation_block.bridge_logo',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [PaymentMethodType::class];
    }
}
