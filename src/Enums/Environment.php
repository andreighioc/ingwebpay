<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Enums;

/**
 * ING WebPay API environments.
 */
enum Environment: string
{
    case TEST = 'test';
    case PRODUCTION = 'production';
}
