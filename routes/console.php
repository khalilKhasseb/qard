<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily check for expiring subscriptions
Schedule::command('subscriptions:check-expiring')->daily();

// Schedule monthly translation credits reset (first day of month at midnight)
Schedule::job(new \App\Jobs\ResetMonthlyTranslationCredits)->monthlyOn(1, '00:00');
