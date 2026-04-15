<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\DTOs;

/**
 * Data Transfer Object for the response from register.do / registerPreAuth.do.
 */
class RegisterResponse
{
    public function __construct(
        public readonly ?string $orderId,
        public readonly ?string $formUrl,
        public readonly ?string $errorCode,
        public readonly ?string $errorMessage,
    ) {}

    /**
     * Whether the registration was successful (we received an orderId and a formUrl).
     */
    public function isSuccessful(): bool
    {
        return $this->orderId !== null
            && $this->formUrl !== null
            && ($this->errorCode === null || $this->errorCode === '0');
    }

    /**
     * Create an instance from the raw API response array.
     */
    public static function fromResponse(array $data): self
    {
        return new self(
            orderId: $data['orderId'] ?? null,
            formUrl: $data['formUrl'] ?? null,
            errorCode: isset($data['errorCode']) ? (string) $data['errorCode'] : null,
            errorMessage: $data['errorMessage'] ?? null,
        );
    }
}
