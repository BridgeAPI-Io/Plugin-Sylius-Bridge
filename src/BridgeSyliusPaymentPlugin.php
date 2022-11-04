<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class BridgeSyliusPaymentPlugin extends Bundle
{
    use SyliusPluginTrait;
}
