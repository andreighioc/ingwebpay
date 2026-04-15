<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Exceptions;

class IngEndpointUnreachableException extends IngWebPayException
{
    public static function timeout(string $reason, ?\Throwable $previous = null): self
    {
        return new self("ING WebPay Endpoint is unreachable: {$reason}", 'ENDPOINT_UNREACHABLE', 504, $previous);
    }
}
