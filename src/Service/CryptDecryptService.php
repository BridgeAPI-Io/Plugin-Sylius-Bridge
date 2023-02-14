<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Service;

use Monolog\Logger;
use Safe\Exceptions\MiscException;
use Safe\Exceptions\OpensslException;
use Safe\Exceptions\UrlException;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Throwable;

use function base64_encode;
use function getenv;
use function implode;
use function is_bool;
use function openssl_random_pseudo_bytes;
use function Safe\base64_decode;
use function Safe\openssl_cipher_iv_length;
use function Safe\openssl_decrypt;
use function Safe\openssl_digest;
use function Safe\openssl_encrypt;
use function Safe\pack;
use function Safe\unpack;
use function strtoupper;
use function trim;

use const OPENSSL_RAW_DATA;

final class CryptDecryptService implements CryptDecryptServiceInterface
{
    public const CYPHERING_METHOD = 'aes-256-cbc';

    private SessionInterface $session;

    public function __construct(
        private Logger $logger,
        private RequestStack $requestStack
    ) {
        $this->session = $this->requestStack->getSession();
    }

    public function generateIv(): string|bool
    {
        try {
            $ivSize = openssl_cipher_iv_length(self::CYPHERING_METHOD);
            $strongResult = false;
            $iv = false;

            while ($strongResult === false) {
                $iv = openssl_random_pseudo_bytes($ivSize, $strongResult); //@phpstan-ignore-line - the safe function not found
            }

            return $iv;

        //@phpstan-ignore-next-line
        } catch (OpensslException | Throwable $exception) {
            $this->logger->error('Iv generate error : ' . $exception->getMessage());

            $this->session->getFlashBag()->add('error', 'Iv generate error : ' . $exception->getMessage()); //@phpstan-ignore-line - polymorphism
        }

        return false;
    }

    public function encrypt(string $data, string $key, string $iv): string|bool
    {
        try {
            $encrypted = openssl_encrypt($data, self::CYPHERING_METHOD, openssl_digest($key, 'md5', true), OPENSSL_RAW_DATA, $iv);

            return strtoupper(implode('', unpack('H*', $encrypted)));
        //@phpstan-ignore-next-line
        } catch (OpensslException | MiscException | Throwable $exception) {
            $this->logger->error('Encryption error : ' . $exception->getMessage());

            $this->session->getFlashBag()->add('error', 'Encryption error : ' . $exception->getMessage()); //@phpstan-ignore-line - polymorphism
        }

        return false;
    }

    public function decrypt(string $data, string $key, string $iv): string|bool
    {
        try {
            $data = pack('H*', $data);

            $decrypted = openssl_decrypt($data, self::CYPHERING_METHOD, openssl_digest($key, 'md5', true), OPENSSL_RAW_DATA, $iv);

            return trim($decrypted);
        //@phpstan-ignore-next-line
        } catch (OpensslException | MiscException | Throwable $exception) {
            $this->logger->error('Decrypt error : ' . $exception->getMessage());

            $this->session->getFlashBag()->add('error', 'Decrypt error : ' . $exception->getMessage()); //@phpstan-ignore-line - polymorphism
        }

        return false;
    }

    public function encryptGatewayConfig(PaymentMethodInterface $paymentMethod): PaymentMethodInterface
    {
        $config = $paymentMethod->getGatewayConfig()?->getConfig();

        if (getenv('BRIDGE_CYPHER_KEY') === false || getenv('BRIDGE_CYPHER_KEY') === '' || $config === null) {
            return $paymentMethod;
        }

        $iv = $this->generateIv();

        if (is_bool($iv)) {
            return $paymentMethod;
        }

        $encryptedConfig = [];

        foreach ($config as $key => $value) {
            if ($value === null) {
                continue;
            }

            $encrypt = $this->encrypt($value, getenv('BRIDGE_CYPHER_KEY'), $iv);

            if (is_bool($encrypt)) {
                return $paymentMethod;
            }

            $encryptedConfig[$key] = $this->encrypt($value, getenv('BRIDGE_CYPHER_KEY'), $iv);
        }

        $encryptedConfig['iv'] = base64_encode($iv);

        $paymentMethod->getGatewayConfig()?->setConfig($encryptedConfig);

        return $paymentMethod;
    }

    /**
     * @throws UrlException
     */
    public function decryptGatewayConfig(PaymentMethodInterface $paymentMethod): PaymentMethodInterface
    {
        $config = $paymentMethod->getGatewayConfig()?->getConfig();

        if (
            getenv('BRIDGE_CYPHER_KEY') === false ||
            getenv('BRIDGE_CYPHER_KEY') === '' ||
            $config === null ||
            ! isset($config['iv'])
        ) {
            return $paymentMethod;
        }

        $iv = base64_decode($config['iv'], true);

        $decryptedConfig = [];

        foreach ($config as $key => $value) {
            if ($key === 'iv') {
                continue;
            }

            if ($value === null) {
                continue;
            }

            $decrypt = $this->decrypt($value, getenv('BRIDGE_CYPHER_KEY'), $iv);

            if (is_bool($decrypt)) {
                return $paymentMethod;
            }

            $decryptedConfig[$key] =  $decrypt;
        }

        $paymentMethod->getGatewayConfig()?->setConfig($decryptedConfig);

        return $paymentMethod;
    }
}
