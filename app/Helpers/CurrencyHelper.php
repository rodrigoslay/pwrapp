<?php

namespace App\Helpers;

class CurrencyHelper
{
    public static function format($amount, $currency = null)
    {
        $currency = $currency ?? config('currency.default');
        $currencyConfig = config("currency.currencies.$currency");

        if (!$currencyConfig) {
            throw new \Exception("La configuración de la moneda $currency no se encontró.");
        }

        $symbol = $currencyConfig['symbol'];
        $symbolPosition = $currencyConfig['symbol_position'];
        $decimalSeparator = $currencyConfig['decimal_separator'];
        $thousandsSeparator = $currencyConfig['thousands_separator'];
        $decimalDigits = $currencyConfig['decimal_digits'];

        $formattedAmount = number_format($amount, $decimalDigits, $decimalSeparator, $thousandsSeparator);

        return $symbolPosition === 'before' ? $symbol . $formattedAmount : $formattedAmount . $symbol;
    }
}
