<?php

namespace App\Providers;

use App\Contracts\Sms\OtpService;
use App\Services\Sms\OtpManager;
use App\Services\Sms\SmsManager;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/sms.php',
            'sms'
        );

        // Register SMS Manager as singleton
        $this->app->singleton(SmsManager::class, function ($app) {
            return new SmsManager;
        });

        // Register OTP Manager as singleton
        $this->app->singleton(OtpManager::class, function ($app) {
            return new OtpManager($app->make(SmsManager::class));
        });

        // Bind interface to implementation
        $this->app->bind(OtpService::class, OtpManager::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/sms.php' => config_path('sms.php'),
            ], 'sms-config');
        }
    }
}
