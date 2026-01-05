<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Payment $payment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Confirmed - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your payment has been confirmed and processed successfully.')
            ->line('Amount: $' . number_format($this->payment->amount, 2) . ' ' . strtoupper($this->payment->currency))
            ->line('Transaction ID: ' . $this->payment->transaction_id)
            ->line('Payment Date: ' . $this->payment->paid_at->format('F j, Y'))
            ->action('View Payment History', url('/dashboard'))
            ->line('Your subscription is now active. Thank you for choosing ' . config('app.name') . '!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'currency' => $this->payment->currency,
            'paid_at' => $this->payment->paid_at,
            'message' => 'Payment confirmed: $' . number_format($this->payment->amount, 2),
        ];
    }
}
