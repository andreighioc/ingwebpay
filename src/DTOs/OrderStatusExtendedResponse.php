<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\DTOs;

use AndreighioC\IngWebPay\Enums\OrderStatus;

/**
 * Data Transfer Object for the response from getOrderStatusExtended.do.
 *
 * This endpoint returns richer data than getOrderStatus.do including
 * actionCode, actionCodeDescription, cardAuthInfo, and attributes.
 */
class OrderStatusExtendedResponse
{
    public function __construct(
        public readonly ?string $errorCode,
        public readonly ?string $errorMessage,
        public readonly ?string $orderNumber,
        public readonly ?OrderStatus $orderStatus,
        public readonly ?int $actionCode,
        public readonly ?string $actionCodeDescription,
        public readonly ?int $amount,
        public readonly ?string $currency,
        public readonly ?int $date,
        public readonly ?string $orderDescription,
        public readonly ?string $ip,
        public readonly ?array $merchantOrderParams,
        public readonly ?array $attributes,
        public readonly ?array $cardAuthInfo,
    ) {}

    /**
     * Whether the payment was successfully deposited.
     */
    public function isSuccessful(): bool
    {
        return $this->orderStatus !== null && $this->orderStatus->isSuccessful();
    }

    /**
     * Whether the payment failed.
     */
    public function isFailed(): bool
    {
        return $this->orderStatus !== null && $this->orderStatus->isFailed();
    }

    /**
     * Get the truncated card PAN from cardAuthInfo.
     */
    public function getPan(): ?string
    {
        return $this->cardAuthInfo['pan'] ?? null;
    }

    /**
     * Get the cardholder name from cardAuthInfo.
     */
    public function getCardholderName(): ?string
    {
        return $this->cardAuthInfo['cardholderName'] ?? null;
    }

    /**
     * Get the approval code from cardAuthInfo.
     */
    public function getApprovalCode(): ?string
    {
        return $this->cardAuthInfo['approvalCode'] ?? null;
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
        $status = isset($data['orderStatus'])
            ? OrderStatus::tryFrom((int) $data['orderStatus'])
            : null;

        return new self(
            errorCode: isset($data['errorCode']) ? (string) $data['errorCode'] : null,
            errorMessage: $data['errorMessage'] ?? null,
            orderNumber: $data['orderNumber'] ?? null,
            orderStatus: $status,
            actionCode: isset($data['actionCode']) ? (int) $data['actionCode'] : null,
            actionCodeDescription: $data['actionCodeDescription'] ?? null,
            amount: isset($data['amount']) ? (int) $data['amount'] : null,
            currency: isset($data['currency']) ? (string) $data['currency'] : null,
            date: isset($data['date']) ? (int) $data['date'] : null,
            orderDescription: $data['orderDescription'] ?? null,
            ip: $data['ip'] ?? null,
            merchantOrderParams: $data['merchantOrderParams'] ?? null,
            attributes: $data['attributes'] ?? null,
            cardAuthInfo: $data['cardAuthInfo'] ?? null,
        );
    }
}
