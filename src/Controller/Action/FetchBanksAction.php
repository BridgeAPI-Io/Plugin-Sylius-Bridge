<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Controller\Action;

use Bridge\SyliusBridgePlugin\Client\BridgePaymentApiClientInterface;
use Bridge\SyliusBridgePlugin\Exception\BridgePaymentMethodNotConfiguredException;
use Bridge\SyliusBridgePlugin\Service\BridgeBankServiceInterface;
use Bridge\SyliusBridgePlugin\Service\BridgePaymentGatewayService;
use Bridge\SyliusBridgePlugin\Service\CryptDecryptService;
use Bridge\SyliusBridgePlugin\Service\TemplatingServiceInterface;
use Safe\Exceptions\MiscException;
use Safe\Exceptions\OpensslException;
use Safe\Exceptions\UrlException;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class FetchBanksAction
{
    private ?array $config;

    private PaymentMethodInterface $paymentMethod;

    /**
     * @throws BridgePaymentMethodNotConfiguredException|MiscException|OpensslException|UrlException
     */
    public function __construct(
        private BridgePaymentGatewayService $bridgePaymentGatewayService,
        private BridgePaymentApiClientInterface $client,
        private TemplatingServiceInterface $templatingService,
        private BridgeBankServiceInterface $bankService,
        private CryptDecryptService $cryptDecryptService,
        private LocaleContextInterface $localeContext,
    ) {
        $this->paymentMethod = $this->bridgePaymentGatewayService->getBridgePaymentMethod();

        $paymentMethod = $this->cryptDecryptService->decryptGatewayConfig($this->paymentMethod);

        $this->config = $paymentMethod->getGatewayConfig()?->getConfig();

        if ($this->config === null) {
            return;
        }

        $this->client->setConfig(
            $this->config['clientId'] ?? null,
            $this->config['clientSecret'] ?? null,
            $this->config['webhookSecret'] ?? null,
            $this->config['testClientId'] ?? null,
            $this->config['testClientSecret'] ?? null,
            $this->config['testWebhookSecret'] ?? null,
        );
    }

    public function __invoke(Request $request): Response
    {
        //@phpstan-ignore-next-line
        $mode = $this->paymentMethod->isTestMode() === false ? 'production' : 'test';
        $banksResources = $this->client->getBanks($mode, $this->localeContext->getLocaleCode());
        $banks = $banksResources !== null ? $this->bankService->getSortedBanks($banksResources['resources']) : [];

        // This will allow to apply the banks filter by name if $bankFilter is not empty
        /** @var ?string $bankFilter */
        $bankFilter = $request->get('bank');
        if ($bankFilter !== '' && $bankFilter !== null) {
            $banks = $this->bankService->filterBanks($banks, $bankFilter);
        }

        return $this->templatingService->renderFromTemplate(
            '@BridgeSyliusPaymentPlugin/Checkout/SelectPayment/_banks_list.html.twig',
            ['banks' => $banks]
        );
    }
}
