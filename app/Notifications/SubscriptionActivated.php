<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionActivated extends Notification implements ShouldQueue
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
        $plan = $this->subscription->plan;

        return (new MailMessage)
            ->subject('Subscription Activated - '.config('app.name'))
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your subscription has been activated successfully.')
            ->line('Plan: '.$plan->name)
            ->line('Price: $'.number_format($plan->price, 2).' per '.$plan->billing_cycle)
            ->line('Features:')
            ->line('• '.$plan->card_limit.' Business Cards')
            ->line('• '.$plan->theme_limit.' Custom Themes')
            ->action('Manage Subscription', url('/dashboard'))
            ->line('Thank you for subscribing to '.config('app.name').'!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name,
            'status' => $this->subscription->status,
            'starts_at' => $this->subscription->starts_at,
            'ends_at' => $this->subscription->ends_at,
            'message' => 'Your '.$this->subscription->plan->name.' subscription is now active',
        ];
    }
}
