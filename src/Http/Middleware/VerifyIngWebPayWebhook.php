<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use AndreighioC\IngWebPay\Exceptions\IngInvalidSignatureException;

class VerifyIngWebPayWebhook
{
    public function handle(Request $request, Closure $next)
    {
        // Example check: Validate that the request comes from ING's allowed IP addresses
        // or check a specific header signature if ING provides one.
        // ING typically sends Server-to-Server callbacks (we need to ensure authenticity).
        
        // As a basic protection, one could check if an 'orderId' exists or match an API token in URL.
        if (!$request->has('orderId')) {
            throw IngInvalidSignatureException::invalidWebhookSignature();
        }

        // Ideally, in production, we should whitelist ING WebPay IP addresses here or verify HMAC signature
        // if documented by ING.

        return $next($request);
    }
}
