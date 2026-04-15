<?php

declare(strict_types=1);

namespace AndreighioC\IngWebPay;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class IngWebPayServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('ingwebpay')
            ->hasConfigFile()
            ->hasCommand(Console\InstallCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(IngWebPay::class, function () {
            return new IngWebPay();
        });
    }

    public function packageBooted(): void
    {
        // Spatie package tools handles publishing the config and command.
        // We still need to manually publish stubs to specific application folders.
        $this->publishes([
            __DIR__ . '/../stubs/controller.stub' => app_path('Http/Controllers/IngWebPayController.php'),
        ], 'ingwebpay-controller');

        $this->publishes([
            __DIR__ . '/../stubs/routes.stub' => base_path('routes/ingwebpay.php'),
        ], 'ingwebpay-routes');

        $this->publishes([
            __DIR__ . '/../stubs/views/checkout.stub' => resource_path('views/ingwebpay/checkout.blade.php'),
            __DIR__ . '/../stubs/views/success.stub'  => resource_path('views/ingwebpay/success.blade.php'),
            __DIR__ . '/../stubs/views/failed.stub'   => resource_path('views/ingwebpay/failed.blade.php'),
        ], 'ingwebpay-views');
    }
}
