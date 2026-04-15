<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Enums;

enum PaymentStatus: string
{
    case Success = 'SUCCESS';
    case Pending = 'PENDING';
    case Failed = 'FAILED';
    case Canceled = 'CANCELED';
    case Refunded = 'REFUNDED';
    case Deposited = 'DEPOSITED';
}
