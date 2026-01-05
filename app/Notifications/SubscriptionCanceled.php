<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCanceled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public UserSubscription $subscription
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Subscription Canceled - TapIt')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your subscription has been canceled.')
            ->line('You will continue to have access to your plan features until ' . $this->subscription->ends_at->format('F j, Y') . '.')
            ->action('Reactivate Subscription', url('/payments'))
            ->line('We hope to see you again soon!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->subscriptionPlan->name,
            'ends_at' => $this->subscription->ends_at,
            'message' => 'Your subscription has been canceled',
        ];
    }
}
