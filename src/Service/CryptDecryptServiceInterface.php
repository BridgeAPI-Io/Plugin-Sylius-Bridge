<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Safe\Exceptions\UrlException;
use Sylius\Component\Core\Model\PaymentMethodInterface;

interface CryptDecryptServiceInterface
{
    public function generateIv(): string|bool;

    public function encrypt(string $data, string $key, string $iv): string|bool;

    public function decrypt(string $data, string $key, string $iv): string|bool;

    /**
     * @throws UrlException
     */
    public function encryptGatewayConfig(PaymentMethodInterface $paymentMethod): PaymentMethodInterface;

    /**
     * @throws UrlException
     */
    public function decryptGatewayConfig(PaymentMethodInterface $paymentMethod): PaymentMethodInterface;
}
