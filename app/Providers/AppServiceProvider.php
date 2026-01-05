<?php

namespace App\Providers;

use App\Models\BusinessCard;
use App\Models\Payment;
use App\Models\Theme;
use App\Policies\BusinessCardPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ThemePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Register policies
        Gate::policy(BusinessCard::class, BusinessCardPolicy::class);
        Gate::policy(Theme::class, ThemePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);

        // Register Blade directive for HTML sanitization
        \Illuminate\Support\Facades\Blade::directive('sanitize', function ($expression) {
            return "<?php echo app(\App\Services\HtmlSanitizer::class)->sanitize($expression); ?>";
        });
    }
}
