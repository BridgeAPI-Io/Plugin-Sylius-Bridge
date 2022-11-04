<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Exception;

use Exception;

final class BridgePaymentApiWrongSignatureException extends Exception
{
    public function __construct()
    {
        parent::__construct('The received signature does not match Bridge API signature.');
    }
}
