<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Exception;

use Exception;

final class BridgePaymentFailedSignatureVerificationException extends Exception
{
    public function __construct()
    {
        parent::__construct('The signature verification has failed.');
    }
}
