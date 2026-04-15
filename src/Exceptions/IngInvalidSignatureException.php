<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Exceptions;

class IngInvalidSignatureException extends IngWebPayException
{
    public static function invalidWebhookSignature(): self
    {
        return new self('ING WebPay Webhook Signature Validation Failed.', 'INVALID_SIGNATURE', 403);
    }
}
