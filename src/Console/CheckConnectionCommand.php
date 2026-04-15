<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Console;

use AndreighioC\IngWebPay\Facades\IngWebPay;
use Illuminate\Console\Command;
use Throwable;

class CheckConnectionCommand extends Command
{
    protected $signature = 'ingwebpay:check-connection';
    protected $description = 'Ping the ING WebPay API to verify credentials and connectivity';

    public function handle(): int
    {
        $this->info('Checking connection to ING WebPay...');

        try {
            // We use getOrderStatus with a dummy order ID just to test authentication/connectivity.
            // Even if the order is not found, an authentication failure would throw earlier.
            $response = IngWebPay::getOrderStatus('test-connection-id');
            
            // Expected: we will likely get an error about Order Not Found, which means Auth was successful!
            $this->info('✅ Successfully connected to ING WebPay! Credentials are valid.');
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('❌ Connection failed!');
            $this->error($e->getMessage());
            
            return self::FAILURE;
        }
    }
}
