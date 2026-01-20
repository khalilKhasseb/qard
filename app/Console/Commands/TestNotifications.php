<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\User;
use App\Models\UserSubscription;
use App\Notifications\PaymentConfirmed;
use App\Notifications\ResetPassword;
use App\Notifications\SubscriptionActivated;
use App\Notifications\SubscriptionExpiring;
use App\Notifications\VerifyEmail;
use App\Notifications\WelcomeEmail;
use Illuminate\Console\Command;

class TestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test {email : The email address to send test notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all email notifications by sending them to a specified email address';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Find or create a test user
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User with email {$email} not found. Please create a user first.");

            return Command::FAILURE;
        }

        $this->info("Testing notifications for user: {$user->name} ({$user->email})");
        $this->newLine();

        // 1. Welcome Email
        $this->info('1. Sending Welcome Email...');
        $user->notify(new WelcomeEmail);
        $this->line('   ✓ WelcomeEmail queued');
        $this->newLine();

        // 2. Email Verification
        $this->info('2. Sending Email Verification...');
        $user->notify(new VerifyEmail);
        $this->line('   ✓ VerifyEmail queued');
        $this->newLine();

        // 3. Password Reset
        $this->info('3. Sending Password Reset...');
        $user->notify(new ResetPassword('test-token-12345'));
        $this->line('   ✓ ResetPassword queued');
        $this->newLine();

        // 4. Payment Confirmed
        $this->info('4. Sending Payment Confirmed...');
        $payment = $user->payments()->completed()->with('plan')->first();
        if ($payment) {
            $user->notify(new PaymentConfirmed($payment));
            $this->line('   ✓ PaymentConfirmed queued (using payment #'.$payment->id.')');
        } else {
            $this->line('   ⚠ No completed payments found. Creating mock notification...');
            // Create a mock payment for testing
            $mockPayment = new Payment([
                'id' => 999,
                'transaction_id' => 'TXN-TEST123',
                'amount' => 29.99,
                'currency' => 'USD',
                'paid_at' => now(),
            ]);
            $mockPayment->exists = true;
            $user->notify(new PaymentConfirmed($mockPayment));
            $this->line('   ✓ PaymentConfirmed queued (with mock data)');
        }
        $this->newLine();

        // 5. Subscription Expiring
        $this->info('5. Sending Subscription Expiring...');
        $subscription = $user->subscriptions()->active()->with('plan')->first();
        if ($subscription) {
            $user->notify(new SubscriptionExpiring($subscription, 7));
            $this->line('   ✓ SubscriptionExpiring queued (using subscription #'.$subscription->id.')');
        } else {
            $this->line('   ⚠ No active subscriptions found. Creating mock notification...');
            // Create a mock subscription for testing
            $mockSubscription = new UserSubscription([
                'id' => 999,
                'ends_at' => now()->addDays(7),
            ]);
            $mockSubscription->exists = true;
            $mockSubscription->setRelation('plan', (object) [
                'name' => 'Pro Plan',
                'price' => 29.99,
                'billing_cycle' => 'monthly',
            ]);
            $user->notify(new SubscriptionExpiring($mockSubscription, 7));
            $this->line('   ✓ SubscriptionExpiring queued (with mock data)');
        }
        $this->newLine();

        // 6. Subscription Activated
        $this->info('6. Sending Subscription Activated...');
        if ($subscription) {
            $user->notify(new SubscriptionActivated($subscription));
            $this->line('   ✓ SubscriptionActivated queued (using subscription #'.$subscription->id.')');
        } else {
            $this->line('   ⚠ No subscriptions found. Creating mock notification...');
            $user->notify(new SubscriptionActivated($mockSubscription ?? new UserSubscription));
            $this->line('   ✓ SubscriptionActivated queued (with mock data)');
        }
        $this->newLine();

        $this->info('All 6 notifications have been queued!');
        $this->info('Run "php artisan queue:work" to process them.');
        $this->newLine();

        return Command::SUCCESS;
    }
}
