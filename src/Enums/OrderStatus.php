<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Enums;

/**
 * ING WebPay transaction statuses returned by getOrderStatus / getOrderStatusExtended.
 */
enum OrderStatus: int
{
    /** Order registered but not yet paid. */
    case REGISTERED = 0;

    /** Pre-authorized payment (two-step transaction). */
    case PREAUTHORIZED = 1;

    /** Transaction authorized (deposited / settlement-ready). */
    case DEPOSITED = 2;

    /** Transaction cancelled. */
    case CANCELLED = 3;

    /** Transaction reversed. */
    case REVERSED = 4;

    /** Transaction initiated by the ACS system of the issuing bank. */
    case ACS_INITIATED = 5;

    /** Transaction rejected / declined. */
    case DECLINED = 6;

    /**
     * Whether the transaction was successfully authorized and deposited.
     */
    public function isSuccessful(): bool
    {
        return $this === self::DEPOSITED;
    }

    /**
     * Whether the transaction is in a pre-authorized (pending completion) state.
     */
    public function isPreauthorized(): bool
    {
        return $this === self::PREAUTHORIZED;
    }

    /**
     * Whether the transaction is in a final negative state.
     */
    public function isFailed(): bool
    {
        return in_array($this, [self::CANCELLED, self::REVERSED, self::DECLINED], true);
    }

    /**
     * Whether the transaction is still pending (registered but unpaid or ACS-initiated).
     */
    public function isPending(): bool
    {
        return in_array($this, [self::REGISTERED, self::ACS_INITIATED], true);
    }

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::REGISTERED    => 'Registered (not paid)',
            self::PREAUTHORIZED => 'Pre-authorized',
            self::DEPOSITED     => 'Deposited (authorized)',
            self::CANCELLED     => 'Cancelled',
            self::REVERSED      => 'Reversed',
            self::ACS_INITIATED => 'ACS Initiated',
            self::DECLINED      => 'Declined',
        };
    }
}
