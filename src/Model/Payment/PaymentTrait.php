<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Model\Payment;

use Doctrine\ORM\Mapping as ORM;

trait PaymentTrait
{
    /** @ORM\Column(name="bridge_payment_api_id", type="string", nullable=true) */
    private ?string $paymentApiId = null;

    /** @ORM\Column(name="bridge_bank_id", type="string", nullable=true) */
    private ?int $bankId = null;

    public function getPaymentApiId(): ?string
    {
        return $this->paymentApiId;
    }

    public function setPaymentApiId(?string $paymentApiId): void
    {
        $this->paymentApiId = $paymentApiId;
    }

    public function getBankId(): ?int
    {
        return $this->bankId;
    }

    public function setBankId(?int $bankId): void
    {
        $this->bankId = $bankId;
    }
}
