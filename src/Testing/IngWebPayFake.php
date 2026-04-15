<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Testing;

use AndreighioC\IngWebPay\DTOs\OrderStatusExtendedResponse;
use AndreighioC\IngWebPay\DTOs\OrderStatusResponse;
use AndreighioC\IngWebPay\DTOs\RegisterResponse;
use PHPUnit\Framework\Assert as PHPUnit;

class IngWebPayFake
{
    protected array $recordedPayments = [];

    public function register(int $amount, ?string $orderNumber = null): RegisterResponse
    {
        $this->recordedPayments[] = [
            'type' => 'register',
            'amount' => $amount,
            'orderNumber' => $orderNumber,
        ];

        return RegisterResponse::fromResponse([
            'orderId' => 'test_id_' . uniqid(),
            'formUrl' => 'https://sandbox.ingwebpay.fake/form',
        ]);
    }

    public function registerPreAuth(int $amount, ?string $orderNumber = null): RegisterResponse
    {
        $this->recordedPayments[] = [
            'type' => 'registerPreAuth',
            'amount' => $amount,
            'orderNumber' => $orderNumber,
        ];

        return RegisterResponse::fromResponse([
            'orderId' => 'test_preauth_id_' . uniqid(),
            'formUrl' => 'https://sandbox.ingwebpay.fake/form',
        ]);
    }

    public function assertPaymentInitiated(int $amount): void
    {
        PHPUnit::assertTrue(
            collect($this->recordedPayments)->contains(function ($payment) use ($amount) {
                return $payment['type'] === 'register' && $payment['amount'] === $amount;
            }),
            "The expected payment of amount {$amount} was not initiated."
        );
    }
    
    // Stub other methods as needed:
    public function getOrderStatus(string $orderId, ?string $language = null): OrderStatusResponse
    {
        return OrderStatusResponse::fromResponse(['OrderStatus' => 2]); // Approved
    }

    public function getOrderStatusExtended(string $orderId, ?string $language = null): OrderStatusExtendedResponse
    {
        return OrderStatusExtendedResponse::fromResponse(['orderStatus' => 2]); // Approved
    }
    
    public function withCredentials(array $credentials): self
    {
        return $this;
    }
}
