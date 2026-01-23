<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserTranslationUsage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ResetMonthlyTranslationCredits implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get all active users with subscriptions
        $users = User::whereHas('activeSubscription')->get();

        $resetCount = 0;

        foreach ($users as $user) {
            try {
                // Get current usage
                $currentUsage = $user->currentTranslationUsage()->first();

                if ($currentUsage && $currentUsage->isExpired()) {
                    // Mark old usage as expired
                    $currentUsage->markAsExpired();

                    // Get new credit limit
                    $newCredits = $user->getTranslationCreditLimit();

                    // Create new usage period
                    UserTranslationUsage::create([
                        'user_id' => $user->id,
                        'credits_available' => $newCredits,
                        'credits_used' => 0,
                        'total_translations' => 0,
                        'period_start' => now()->startOfMonth(),
                        'period_end' => now()->endOfMonth(),
                        'is_active' => true,
                    ]);

                    // Clear cache
                    Cache::forget("translation_credits:user:{$user->id}");

                    $resetCount++;

                    Log::info('Translation credits reset for user', [
                        'user_id' => $user->id,
                        'new_credits' => $newCredits,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to reset translation credits for user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Monthly translation credits reset completed', [
            'users_reset' => $resetCount,
        ]);
    }
}
