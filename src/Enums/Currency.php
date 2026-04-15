<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Enums;

/**
 * ISO 4217 numeric currency codes supported by ING WebPay.
 */
enum Currency: string
{
    case RON = '946';
    case EUR = '978';

    /**
     * Get a human-readable label for the currency.
     */
    public function label(): string
    {
        return match ($this) {
            self::RON => 'RON',
            self::EUR => 'EUR',
        };
    }
}
