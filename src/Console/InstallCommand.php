<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'ingwebpay:install';

    protected $description = 'Install the ING WebPay package: publishes config, controller, routes, and views.';

    public function handle(): int
    {
        $this->info('🔧 Installing ING WebPay...');
        $this->newLine();

        // 1. Publish config
        $this->info('Publishing configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'ingwebpay-config',
        ]);

        // 2. Publish controller
        $this->info('Publishing controller...');
        $this->call('vendor:publish', [
            '--tag' => 'ingwebpay-controller',
        ]);

        // 3. Publish routes
        $this->info('Publishing routes...');
        $this->call('vendor:publish', [
            '--tag' => 'ingwebpay-routes',
        ]);

        // 4. Publish views
        $this->info('Publishing views...');
        $this->call('vendor:publish', [
            '--tag' => 'ingwebpay-views',
        ]);

        $this->newLine();
        $this->info('✅ ING WebPay installed successfully!');
        $this->newLine();

        $this->warn('Next steps:');
        $this->line('  1. Add your credentials to <comment>.env</comment>:');
        $this->line('     INGWEBPAY_ENVIRONMENT=test');
        $this->line('     INGWEBPAY_USERNAME=your_api_username');
        $this->line('     INGWEBPAY_PASSWORD=your_api_password');
        $this->line('     INGWEBPAY_CURRENCY=946');
        $this->line('     INGWEBPAY_RETURN_URL=/ingwebpay/callback');
        $this->newLine();
        $this->line('  2. Include the routes file in <comment>routes/web.php</comment>:');
        $this->line("     require __DIR__ . '/ingwebpay.php';");
        $this->newLine();
        $this->line('  3. Review the published controller at:');
        $this->line('     <comment>app/Http/Controllers/IngWebPayController.php</comment>');
        $this->newLine();
        $this->line('  4. Customize the views in <comment>resources/views/ingwebpay/</comment>');
        $this->newLine();

        return self::SUCCESS;
    }
}
