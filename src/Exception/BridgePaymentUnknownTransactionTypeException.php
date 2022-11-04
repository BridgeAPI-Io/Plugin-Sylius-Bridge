<?php

declare(strict_types=1);

namespace Bridge\SyliusBridgePlugin\Exception;

use Exception;

final class BridgePaymentUnknownTransactionTypeException extends Exception
{
    public function __construct()
    {
        parent::__construct('The payload has an unknown transaction type.');
    }
}
