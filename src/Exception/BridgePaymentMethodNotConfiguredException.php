<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Exception;

use Exception;

final class BridgePaymentMethodNotConfiguredException extends Exception
{
    public function __construct()
    {
        parent::__construct('The Bridge payment method is not configured on the current channel.');
    }
}
