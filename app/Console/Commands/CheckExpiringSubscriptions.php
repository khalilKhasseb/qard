<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Notifications\SubscriptionExpiring;
use Illuminate\Console\Command;

class CheckExpiringSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expiring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for subscriptions expiring in 7 days and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expiring subscriptions...');

        $expiringSubscriptions = UserSubscription::expiring(7)
            ->with(['user', 'plan'])
            ->get();

        $count = 0;

        foreach ($expiringSubscriptions as $subscription) {
            $daysRemaining = $subscription->daysRemaining();

            // Send notification to user
            $subscription->user->notify(
                new SubscriptionExpiring($subscription, $daysRemaining)
            );

            $count++;

            $this->line("Notification sent to: {$subscription->user->email} ({$daysRemaining} days remaining)");
        }

        $this->info("Sent {$count} expiring subscription notifications.");

        return Command::SUCCESS;
    }
}
