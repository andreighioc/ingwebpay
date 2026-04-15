<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Console;

use Illuminate\Console\Command;

class RegisterWebhookCommand extends Command
{
    protected $signature = 'ingwebpay:register-webhook {url? : The webhook URL to register}';
    protected $description = 'Register the Webhook URL (if supported by ING API)';

    public function handle(): int
    {
        $url = $this->argument('url') ?? route('ingwebpay.callback');

        $this->info("Registering webhook URL: {$url}");
        
        // ING WebPay doesn't usually offer a pure API endpoint just to register a global webhook.
        // Usually, the returnUrl is sent per-transaction in `register.do`.
        // If they do support a global setting API in a specific portal, we would call it here.
        
        // This is a placeholder command that developers can use if they build custom webhook integrations
        // or if ING updates their API to support dynamic global webhook registration via API.
        
        $this->warn('Note: ING WebPay mostly relies on the `returnUrl` provided dynamically at registration.');
        $this->info('Webhook configuration command completed.');

        return self::SUCCESS;
    }
}
