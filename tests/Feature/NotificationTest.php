<?php

use App\Models\Payment;
use App\Models\User;
use App\Models\UserSubscription;
use App\Notifications\PaymentConfirmed;
use App\Notifications\PaymentReceived;
use App\Notifications\SubscriptionActivated;
use App\Notifications\SubscriptionCanceled;
use Illuminate\Support\Facades\Notification;

test('payment received notification is sent', function () {
    Notification::fake();

    $user = User::factory()->create();
    $payment = Payment::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $user->notify(new PaymentReceived($payment));

    Notification::assertSentTo($user, PaymentReceived::class);
});

test('payment confirmed notification is sent', function () {
    Notification::fake();

    $user = User::factory()->create();
    $payment = Payment::factory()->create([
        'user_id' => $user->id,
        'status' => 'completed',
        'paid_at' => now(),
    ]);

    $user->notify(new PaymentConfirmed($payment));

    Notification::assertSentTo($user, PaymentConfirmed::class);
});

test('subscription activated notification is sent', function () {
    Notification::fake();

    $user = User::factory()->create();
    $subscription = UserSubscription::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $user->notify(new SubscriptionActivated($subscription));

    Notification::assertSentTo($user, SubscriptionActivated::class);
});

test('subscription canceled notification is sent', function () {
    Notification::fake();

    $user = User::factory()->create();
    $subscription = UserSubscription::factory()->create([
        'user_id' => $user->id,
        'status' => 'canceled',
    ]);

    $user->notify(new SubscriptionCanceled($subscription));

    Notification::assertSentTo($user, SubscriptionCanceled::class);
});
