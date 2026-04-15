<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Facades;

use AndreighioC\IngWebPay\DTOs\OrderStatusExtendedResponse;
use AndreighioC\IngWebPay\DTOs\OrderStatusResponse;
use AndreighioC\IngWebPay\DTOs\RegisterResponse;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for the ING WebPay service.
 *
 * @method static RegisterResponse register(int $amount, ?string $orderNumber = null, ?string $description = null, ?string $returnUrl = null, ?string $language = null, ?string $email = null, ?string $currency = null, ?string $reconciliationId = null)
 * @method static RegisterResponse registerPreAuth(int $amount, ?string $orderNumber = null, ?string $description = null, ?string $returnUrl = null, ?string $language = null, ?string $email = null, ?string $currency = null, ?string $reconciliationId = null)
 * @method static OrderStatusResponse getOrderStatus(string $orderId, ?string $language = null)
 * @method static OrderStatusExtendedResponse getOrderStatusExtended(string $orderId, ?string $language = null)
 * @method static array reverse(string $orderId, ?string $language = null)
 * @method static array deposit(string $orderId, int $amount = 0, ?string $language = null)
 * @method static array refund(string $orderId, int $amount)
 * @method static int toMinorUnits(float $amount)
 * @method static float toMajorUnits(int $minorUnits)
 * @method static string getBaseUrl()
 * @method static bool isTestEnvironment()
 *
 * @see \AndreighioC\IngWebPay\IngWebPay
 */
class IngWebPay extends Facade
{
    /**
     * Replace the bound instance with a fake.
     */
    public static function fake(): \AndreighioC\IngWebPay\Testing\IngWebPayFake
    {
        $fake = new \AndreighioC\IngWebPay\Testing\IngWebPayFake();
        static::swap($fake);

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \AndreighioC\IngWebPay\IngWebPay::class;
    }
}
