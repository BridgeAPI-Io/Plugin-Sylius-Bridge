<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Exception;

use Exception;

final class BridgePaymentPluginException extends Exception
{
    public function __construct()
    {
        parent::__construct('Could not load data from Bridge.');
    }
}
