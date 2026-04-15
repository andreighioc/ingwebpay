<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Exceptions;

class IngAuthenticationException extends IngWebPayException
{
    public static function invalidCredentials(): self
    {
        return new self('ING WebPay Authentication Failed: Invalid credentials or certificate.', 'AUTH_FAILED', 401);
    }
}
