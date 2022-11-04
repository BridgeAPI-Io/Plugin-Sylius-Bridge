<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Exception;

use Exception;

final class BridgePaymentEmptyPayloadException extends Exception
{
    public function __construct()
    {
        parent::__construct('The received payload is empty.');
    }
}
