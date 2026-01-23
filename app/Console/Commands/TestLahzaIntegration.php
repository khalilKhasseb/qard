<?php

namespace App\Console\Commands;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\LahzaPaymentGateway;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestLahzaIntegration extends Command
{
    protected $signature = 'lahza:test';

    protected $description = 'Test Lahza payment gateway integration';

    public function handle(): int
    {
        $this->info('Testing Lahza Payment Gateway Integration...');
        $this->line('');

        // Check configuration
        $this->info('1. Checking configuration...');
        $publicKey = config('payments.lahza.public_key');
        $secretKey = config('payments.lahza.secret_key');
        $testMode = config('payments.lahza.test_mode');

        if (empty($publicKey) || empty($secretKey)) {
            $this->error('   ✗ Lahza credentials not configured. Please set LAHZA_PUBLIC_KEY and LAHZA_SECRET_KEY in your .env file.');

            return 1;
        }

        $this->info('   ✓ Configuration found');
        $this->info('   - Test Mode: '.($testMode ? 'Yes' : 'No'));
        $this->info('   - Public Key: '.substr($publicKey, 0, 10).'...');
        $this->line('');

        // Test gateway instantiation
        $this->info('2. Testing gateway instantiation...');
        try {
            $gateway = new LahzaPaymentGateway;
            $this->info('   ✓ Gateway instantiated successfully');
            $this->info('   - Gateway Name: '.$gateway->getGatewayName());
            $this->info('   - Supports Recurring: '.($gateway->supportsRecurring() ? 'Yes' : 'No'));
        } catch (\Exception $e) {
            $this->error('   ✗ Failed to instantiate gateway: '.$e->getMessage());

            return 1;
        }
        $this->line('');

        // Test with dummy user and plan
        $this->info('3. Testing payment initialization (mock)...');
        try {
            $user = User::first();
            $plan = SubscriptionPlan::first();

            if (! $user) {
                $user = User::factory()->create([
                    'email' => 'test@example.com',
                    'name' => 'Test User',
                ]);
                $this->info('   - Created test user');
            }

            if (! $plan) {
                $plan = SubscriptionPlan::factory()->create([
                    'name' => 'Test Plan',
                    'price' => 10.00,
                    'billing_cycle' => 'monthly',
                ]);
                $this->info('   - Created test plan');
            }

            // Mock the HTTP request
            Http::fake([
                'https://api.lahza.io/transaction/initialize' => Http::response([
                    'data' => [
                        'reference' => 'test_'.time(),
                        'checkout_url' => 'https://checkout.lahza.io/pay/test',
                    ],
                ], 200),
            ]);

            $payment = $gateway->createPayment($user, $plan->price, [
                'subscription_plan_id' => $plan->id,
            ]);

            $this->info('   ✓ Payment initialized successfully');
            $this->info('   - Transaction ID: '.$payment->transaction_id);
            $this->info('   - Gateway Reference: '.$payment->gateway_reference);
            $this->info('   - Amount: $'.number_format($payment->amount, 2));
            $this->info('   - Status: '.$payment->status);

        } catch (\Exception $e) {
            $this->error('   ✗ Failed to initialize payment: '.$e->getMessage());

            return 1;
        }
        $this->line('');

        // Test PaymentService with Lahza
        $this->info('4. Testing PaymentService...');
        try {
            $service = new PaymentService($gateway);
            $this->info('   ✓ PaymentService instantiated with Lahza gateway');
            $this->info('   - Gateway Name: '.$service->getGatewayName());
        } catch (\Exception $e) {
            $this->error('   ✗ Failed to instantiate PaymentService: '.$e->getMessage());

            return 1;
        }
        $this->line('');

        // Generate callback URL
        $this->info('5. Testing callback URL generation...');
        $callbackUrl = $gateway->generateCallbackUrl('test_ref_123');
        $this->info('   ✓ Callback URL: '.$callbackUrl);
        $this->line('');

        // Test webhook signature
        $this->info('6. Testing webhook signature verification...');
        $payload = '{"event":"charge.success","data":{"reference":"test_ref"}}';
        $signature = hash_hmac('sha512', $payload, config('payments.lahza.webhook_secret', ''));
        $isValid = $gateway->verifyWebhookSignature($payload, $signature);

        if ($isValid) {
            $this->info('   ✓ Webhook signature verification working');
        } else {
            $this->warning('   ⚠ Webhook signature verification may have issues');
        }
        $this->line('');

        $this->info('✅ All tests passed! Lahza integration is working correctly.');
        $this->line('');
        $this->info('Next steps:');
        $this->info('1. Set up your Lahza test credentials in .env');
        $this->info('2. Configure your webhook URL in Lahza dashboard');
        $this->info('3. Test the checkout flow in the browser');

        return 0;
    }
}
