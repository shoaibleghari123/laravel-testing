<?php

namespace App\Exceptions;

use Exception;

class CurrencyRateNotFoundException extends Exception
{
    public function __construct(string $message, string $invalidUrl = null)
    {
        parent::__construct($message);
    }
}
