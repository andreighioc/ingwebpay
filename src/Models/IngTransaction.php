<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Models;

use AndreighioC\IngWebPay\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class IngTransaction extends Model
{
    protected $table = 'ingwebpay_transactions';

    protected $fillable = [
        'order_id',
        'order_number',
        'amount',
        'currency',
        'status',
        'raw_response',
        'payer_email',
        'webhook_received_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'status' => PaymentStatus::class,
        'raw_response' => 'array',
        'webhook_received_at' => 'datetime',
    ];
}
