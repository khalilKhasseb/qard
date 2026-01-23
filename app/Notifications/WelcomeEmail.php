<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to '.config('app.name').'!')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Thank you for registering with '.config('app.name').'.')
            ->line('We\'re excited to have you on board!')
            ->line('With your account, you can:')
            ->line('• Create and manage digital business cards')
            ->line('• Customize themes and styles')
            ->line('• Share your cards with anyone, anywhere')
            ->line('Please make sure to verify your email address to access all features.')
            ->action('Get Started', url('/dashboard'))
            ->line('If you have any questions, feel free to reach out to our support team.')
            ->line('Welcome aboard!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Welcome!',
            'message' => 'Thank you for registering with '.config('app.name'),
            'action_url' => url('/dashboard'),
        ];
    }
}
