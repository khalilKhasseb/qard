<?php

namespace App\Providers;

use App\Models\BusinessCard;
use App\Models\Payment;
use App\Models\Theme;
use App\Policies\BusinessCardPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ThemePolicy;
use App\Settings\AiSettings;
use App\Settings\GeneralSettings;
use App\Settings\MailSettings;
use App\Settings\PaymentSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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

        $this->bootSettings();

        // Register policies
        Gate::policy(BusinessCard::class, BusinessCardPolicy::class);
        Gate::policy(Theme::class, ThemePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);

        // Register Blade directive for HTML sanitization
        \Illuminate\Support\Facades\Blade::directive('sanitize', function ($expression) {
            return "<?php echo app(\App\Services\HtmlSanitizer::class)->sanitize($expression); ?>";
        });

        // Configure rate limiters for AI translation
        $this->configureRateLimiting();
    }

    protected function bootSettings(): void
    {
        try {
            $general = app(GeneralSettings::class);
            $mail = app(MailSettings::class);
            $payment = app(PaymentSettings::class);
            $ai = app(AiSettings::class);

            config([
                'app.name' => $general->site_name,
                'mail.default' => $mail->mailer,
                'mail.mailers.smtp.host' => $mail->host,
                'mail.mailers.smtp.port' => $mail->port,
                'mail.mailers.smtp.username' => $mail->username,
                'mail.mailers.smtp.password' => $mail->password,
                'mail.mailers.smtp.encryption' => $mail->encryption,
                'mail.from.address' => $mail->from_address,
                'mail.from.name' => $mail->from_name,
                'services.lahza.key' => $payment->lahza_public_key,
                'services.lahza.secret' => $payment->lahza_secret_key,
                'services.lahza.test_mode' => $payment->lahza_test_mode,
                'services.lahza.currency' => $payment->lahza_currency,
                'services.openrouter.key' => $ai->openrouter_api_key,
                'services.openrouter.url' => $ai->openrouter_url,
                'prism.translation_model' => $ai->translation_model,
                'prism.request_timeout' => $ai->request_timeout,
                'payments.default' => $payment->default_gateway,
            ]);
        } catch (\Exception $e) {
            // Settings table might not exist during migration
        }
    }

    /**
     * Configure rate limiting for translation endpoints.
     */
    protected function configureRateLimiting(): void
    {
        // AI Translation rate limiter - 10 translations per minute per user
        RateLimiter::for('ai-translation', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(100)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // Translation history viewing - higher limit
        RateLimiter::for('translation-history', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
