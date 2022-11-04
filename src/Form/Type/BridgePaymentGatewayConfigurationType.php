<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class BridgePaymentGatewayConfigurationType extends AbstractType
{
    private string $createBridgeAccountLink;

    public function __construct(private TranslatorInterface $translator)
    {
        $this->createBridgeAccountLink = '<a href="https://dashboard.bridgeapi.io/signup">' . $this->translator->trans('bridge.payment_method.explanation_block.click_here_to_create_an_account') . '</a>';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('testClientId', TextType::class, [
                'label' => 'bridge.payment_method.explanation_block.sandbox_test_client_id',
                'required' => false,
                'help_html' => true,
                'help' => $this->getClientIdHelpText(),
            ])
            ->add('testClientSecret', TextType::class, [
                'label' => 'bridge.payment_method.explanation_block.sandbox_test_client_secret',
                'required' => false,
                'help_html' => true,
                'help' => $this->getClientSecretHelpText(),
            ])
            ->add('testWebhookSecret', TextType::class, [
                'required' => false,
                'label' => 'bridge.payment_method.explanation_block.sandbox_test_webhook_secret',
            ])
            ->add('clientId', TextType::class, [
                'label' => 'bridge.payment_method.explanation_block.production_client_id',
                'required' => false,
                'help_html' => true,
                'help' => $this->getClientIdHelpText(),
            ])
            ->add('clientSecret', TextType::class, [
                'label' => 'bridge.payment_method.explanation_block.production_client_secret',
                'required' => false,
                'help_html' => true,
                'help' => $this->getClientSecretHelpText(),
            ])
            ->add('webhookSecret', TextType::class, [
                'label' => 'bridge.payment_method.explanation_block.production_webhook_secret',
                'required' => false,
            ]);
    }

    private function getClientIdHelpText(): string
    {
        return $this->translator->trans('bridge.payment_method.explanation_block.sandbox_test_client_id_help') . $this->createBridgeAccountLink;
    }

    private function getClientSecretHelpText(): string
    {
        return $this->translator->trans('bridge.payment_method.explanation_block.sandbox_test_client_secret_help') . $this->createBridgeAccountLink;
    }
}
