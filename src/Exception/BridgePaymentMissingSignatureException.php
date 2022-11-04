<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Exception;

use Exception;

final class BridgePaymentMissingSignatureException extends Exception
{
    public function __construct()
    {
        parent::__construct('The request does not have a Bridge signature.');
    }
}
