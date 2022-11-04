<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Exception;

use Exception;

final class BridgePaymentApiException extends Exception
{
    public function __construct()
    {
        parent::__construct('An error has occurred on the Bridge API server.');
    }
}
