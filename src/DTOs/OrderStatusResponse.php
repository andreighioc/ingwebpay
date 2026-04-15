<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\DTOs;

use AndreighioC\IngWebPay\Enums\OrderStatus;

/**
 * Data Transfer Object for the response from getOrderStatus.do.
 */
class OrderStatusResponse
{
    public function __construct(
        public readonly ?OrderStatus $orderStatus,
        public readonly ?string $errorCode,
        public readonly ?string $errorMessage,
        public readonly ?string $orderNumber,
        public readonly ?string $pan,
        public readonly ?string $expiration,
        public readonly ?string $cardholderName,
        public readonly ?int $amount,
        public readonly ?int $depositAmount,
        public readonly ?string $currency,
        public readonly ?string $approvalCode,
        public readonly ?int $authCode,
        public readonly ?string $ip,
        public readonly ?string $clientId,
        public readonly ?string $bindingId,
    ) {}

    /**
     * Whether the payment was successfully deposited.
     */
    public function isSuccessful(): bool
    {
        return $this->orderStatus !== null && $this->orderStatus->isSuccessful();
    }

    /**
     * Whether the payment is in a pre-authorized state (awaiting completion).
     */
    public function isPreauthorized(): bool
    {
        return $this->orderStatus !== null && $this->orderStatus->isPreauthorized();
    }

    /**
     * Whether the payment failed (cancelled, reversed, or declined).
     */
    public function isFailed(): bool
    {
        return $this->orderStatus !== null && $this->orderStatus->isFailed();
    }

    /**
     * Whether the payment is still pending.
     */
    public function isPending(): bool
    {
        return $this->orderStatus === null || $this->orderStatus->isPending();
    }

    /**
     * Get the transaction amount in major currency units (e.g., RON / EUR).
     */
    public function getAmountInMajorUnits(): ?float
    {
        return $this->amount !== null ? $this->amount / 100 : null;
    }

    /**
     * Create an instance from the raw API response array.
     */
    public static function fromResponse(array $data): self
    {
        $status = isset($data['OrderStatus'])
            ? OrderStatus::tryFrom((int) $data['OrderStatus'])
            : null;

        return new self(
            orderStatus: $status,
            errorCode: isset($data['ErrorCode']) ? (string) $data['ErrorCode'] : null,
            errorMessage: $data['ErrorMessage'] ?? null,
            orderNumber: $data['OrderNumber'] ?? null,
            pan: $data['Pan'] ?? null,
            expiration: $data['expiration'] ?? null,
            cardholderName: $data['cardholderName'] ?? null,
            amount: isset($data['Amount']) ? (int) $data['Amount'] : null,
            depositAmount: isset($data['depositAmount']) ? (int) $data['depositAmount'] : null,
            currency: $data['currency'] ?? null,
            approvalCode: $data['approvalCode'] ?? null,
            authCode: isset($data['authCode']) ? (int) $data['authCode'] : null,
            ip: $data['Ip'] ?? null,
            clientId: $data['clientId'] ?? null,
            bindingId: $data['bindingId'] ?? null,
        );
    }
}
