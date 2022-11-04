<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Exception;

use Exception;

final class BridgePaymentMethodCouldNotBeEnabledException extends Exception
{
    public function __construct()
    {
        parent::__construct('Bridge payment method could not be enabled.');
    }
}
