<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification implements ShouldQueue
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
            ->subject('Payment Received - TapIt')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('We have received your payment.')
            ->line('Amount: $'.number_format($this->payment->amount, 2).' '.$this->payment->currency)
            ->line('Payment Method: '.ucfirst($this->payment->payment_method))
            ->line('Status: '.ucfirst($this->payment->status))
            ->action('View Payment History', url('/payments'))
            ->line('Thank you for using TapIt!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'currency' => $this->payment->currency,
            'payment_method' => $this->payment->payment_method,
            'status' => $this->payment->status,
            'message' => 'Payment of $'.number_format($this->payment->amount, 2).' received',
        ];
    }
}
