<?php

namespace App\Jobs;

use App\Models\UserSubscription;
use App\Notifications\SubscriptionActivated;
use App\Notifications\SubscriptionCanceled;
use App\Notifications\SubscriptionExpiring;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSubscriptionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;

    public int $tries = 3;

    public function __construct(
        public UserSubscription $subscription,
        public string $type = 'activated', // 'activated', 'canceled', 'expiring'
        public ?int $daysRemaining = null
    ) {}

    public function handle(): void
    {
        $user = $this->subscription->user;

        if (! $user) {
            return;
        }

        $notification = match ($this->type) {
            'canceled' => new SubscriptionCanceled($this->subscription),
            'expiring' => new SubscriptionExpiring($this->subscription, $this->daysRemaining ?? 7),
            default => new SubscriptionActivated($this->subscription),
        };

        $user->notify($notification);
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('SendSubscriptionNotification failed', [
            'subscription_id' => $this->subscription->id,
            'type' => $this->type,
            'error' => $exception->getMessage(),
        ]);
    }
}
