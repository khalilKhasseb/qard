<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\UserAddon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AddonPurchased extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Payment $payment,
        public UserAddon $userAddon
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $addon = $this->userAddon->addon;

        return (new MailMessage)
            ->subject('Add-On Purchased - '.config('app.name'))
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your add-on has been purchased and activated successfully.')
            ->line('Add-On: '.$addon->name)
            ->line('Amount: $'.number_format($this->payment->amount, 2).' '.strtoupper($this->payment->currency))
            ->line('Transaction ID: '.$this->payment->transaction_id)
            ->action('View Add-Ons', url('/addons'))
            ->line('Thank you for choosing '.config('app.name').'!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'addon_id' => $this->userAddon->addon_id,
            'addon_name' => $this->userAddon->addon->name,
            'amount' => $this->payment->amount,
            'currency' => $this->payment->currency,
            'message' => 'Add-on purchased: '.$this->userAddon->addon->name,
        ];
    }
}
