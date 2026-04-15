<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Exceptions;

use RuntimeException;

/**
 * Exception thrown when an ING WebPay API call fails.
 */
class IngWebPayException extends RuntimeException
{
    protected string $errorCode;

    public function __construct(string $message, string $errorCode = '0', int $code = 0, ?\Throwable $previous = null)
    {
        $this->errorCode = $errorCode;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the ING WebPay error code returned by the API.
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Create an exception from an API response array.
     */
    public static function fromResponse(array $response): self
    {
        $errorCode = (string) ($response['errorCode'] ?? $response['ErrorCode'] ?? '0');
        $errorMessage = $response['errorMessage'] ?? $response['ErrorMessage'] ?? 'Unknown ING WebPay error';

        return new self($errorMessage, $errorCode);
    }

    /**
     * Create an exception for a network/connection failure.
     */
    public static function connectionFailed(string $reason, ?\Throwable $previous = null): self
    {
        return new self(
            "ING WebPay connection failed: {$reason}",
            'CONNECTION_ERROR',
            0,
            $previous
        );
    }
}
