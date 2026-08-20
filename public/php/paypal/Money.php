<?php

/**
 * Pence (int, matches DB storage) <-> PayPal decimal-string amount conversion.
 * PayPal REST amounts are always major-unit decimal strings (e.g. "12.34"),
 * never minor-unit integers like Stripe uses.
 */
class Money
{
    public static function penceToDecimal(int $pence): string
    {
        return number_format($pence / 100, 2, '.', '');
    }

    public static function decimalToPence(string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
