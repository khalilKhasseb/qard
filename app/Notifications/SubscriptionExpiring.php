<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiring extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public UserSubscription $subscription,
        public int $daysRemaining
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $plan = $this->subscription->plan;

        return (new MailMessage)
            ->subject('Subscription Expiring Soon - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your subscription will expire in ' . $this->daysRemaining . ' day(s).')
            ->line('Plan: ' . $plan->name)
            ->line('Expiration Date: ' . $this->subscription->ends_at->format('F j, Y'))
            ->action('Renew Subscription', url('/dashboard'))
            ->line('Renew now to continue enjoying all the features of ' . config('app.name') . '!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name,
            'ends_at' => $this->subscription->ends_at,
            'days_remaining' => $this->daysRemaining,
            'message' => 'Your subscription will expire in ' . $this->daysRemaining . ' day(s)',
        ];
    }
}
