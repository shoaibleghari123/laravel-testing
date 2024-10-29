<?php
namespace App\Services;

use App\Exceptions\CurrencyRateNotFoundException;
use Illuminate\Support\Arr;

class CurrencyService
{
    const RATES = [
        'USD' => [
            'EUR' => 0.98,
            //'GBP' => 0.60,
        ],
    ];

    public function convert(float $amount, string $currencyFrom, string $currencyTo): float
    {
        if (! Arr::exists(self::RATES, $currencyFrom)) {
            throw new CurrencyRateNotFoundException('Currency not found');
        }
        $rate = self::RATES[$currencyFrom][$currencyTo] ?? 0;

        return round($amount * $rate, 2);
    }
}
