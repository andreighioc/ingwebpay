<?php

namespace AndreighioC\IngWebPay\Events;

use AndreighioC\IngWebPay\DTOs\OrderStatusExtendedResponse;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessful
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public OrderStatusExtendedResponse $status,
        public string $orderId
    ) {}
}
