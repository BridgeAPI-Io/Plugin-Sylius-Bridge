<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin;

use Bridge\SyliusBridgePlugin\Client\BridgePaymentApiClient;
use Bridge\SyliusBridgePlugin\Client\BridgePaymentApiClientInterface;
use GuzzleHttp\Client;
use Monolog\Logger;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class BridgePaymentGatewayFactory extends GatewayFactory
{
    public const FACTORY_NAME = 'bridge-payment';

    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => self::FACTORY_NAME,
            'payum.factory_title' => 'bridge.bridge_api.bridge_payment',
            'payum.http_client' => 'bridge_plugin.api_client',
        ]);

        if ((bool) $config['payum.api'] !== false) {
            return;
        }

        $config['payum.default_options'] = [
            'clientId' => null,
            'clientSecret' => null,
            'webhookSecret' => null,
            'testClientId' => null,
            'testClientSecret' => null,
            'testWebhookSecret' => null,
        ];

        $config->defaults($config['payum.default_options']);

        $config['payum.api'] = static function (ArrayObject $config): BridgePaymentApiClientInterface {
            $bridgePaymentApiClient = new BridgePaymentApiClient(new Client(), new Logger('console'));

            $bridgePaymentApiClient->setConfig(
                $config['clientId'],
                $config['clientSecret'],
                $config['webhookSecret'],
                $config['testClientId'],
                $config['testClientSecret'],
                $config['testWebhookSecret'],
            );

            return $bridgePaymentApiClient;
        };
    }
}
