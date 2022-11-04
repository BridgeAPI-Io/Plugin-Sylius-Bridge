<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Exception;

use Exception;

final class BridgePaymentAuthorizationException extends Exception
{
    public function __construct()
    {
        parent::__construct('Bridge client could not be authorized.');
    }
}
