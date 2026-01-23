<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Notifications\PaymentConfirmed;
use App\Notifications\PaymentReceived;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;

    public int $tries = 3;

    public function __construct(
        public Payment $payment,
        public string $type = 'received' // 'received' or 'confirmed'
    ) {}

    public function handle(): void
    {
        $user = $this->payment->user;

        if (! $user) {
            return;
        }

        $notification = match ($this->type) {
            'confirmed' => new PaymentConfirmed($this->payment),
            default => new PaymentReceived($this->payment),
        };

        $user->notify($notification);
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('SendPaymentNotification failed', [
            'payment_id' => $this->payment->id,
            'type' => $this->type,
            'error' => $exception->getMessage(),
        ]);
    }
}
