<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Model\Payment;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait PaymentMethodTrait
{
    /** @ORM\Column(name="bridge_test_mode", type="boolean", options={"default":true})) */
    private bool $testMode = true;

    /** @ORM\Column(name="bridge_logo", type="boolean", options={"default":true})) */
    private bool $bridgeLogo = true;

    /** @ORM\Column(name="bridge_test_webhook_configuration_date", type="datetime", nullable=true) */
    private ?DateTime $testWebhookConfigurationDate = null;

    /** @ORM\Column(name="bridge_production_webhook_configuration_date", type="datetime", nullable=true) */
    private ?DateTime $productionWebhookConfigurationDate = null;

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    public function setTestMode(bool $testMode): void
    {
        $this->testMode = $testMode;
    }

    public function isBridgeLogo(): bool
    {
        return $this->bridgeLogo;
    }

    public function setBridgeLogo(bool $bridgeLogo): void
    {
        $this->bridgeLogo = $bridgeLogo;
    }

    public function getTestWebhookConfigurationDate(): ?DateTime
    {
        return $this->testWebhookConfigurationDate;
    }

    public function setTestWebhookConfigurationDate(?DateTime $testWebhookConfigurationDate): void
    {
        $this->testWebhookConfigurationDate = $testWebhookConfigurationDate;
    }

    public function getProductionWebhookConfigurationDate(): ?DateTime
    {
        return $this->productionWebhookConfigurationDate;
    }

    public function setProductionWebhookConfigurationDate(?DateTime $productionWebhookConfigurationDate): void
    {
        $this->productionWebhookConfigurationDate = $productionWebhookConfigurationDate;
    }
}
