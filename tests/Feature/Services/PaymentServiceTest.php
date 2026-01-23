<?php

namespace Tests\Feature\Services;

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\LahzaPaymentGateway;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected SubscriptionPlan $plan;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->plan = SubscriptionPlan::factory()->create([
            'price' => 15.00,
            'name' => 'Pro Plan',
            'billing_cycle' => 'monthly',
        ]);

        config([
            'payments.gateway' => 'lahza',
            'payments.lahza.public_key' => 'test_public_key',
            'payments.lahza.secret_key' => 'test_secret_key',
            'payments.lahza.webhook_secret' => 'test_webhook_secret',
            'payments.lahza.test_mode' => true,
            'payments.lahza.base_url' => 'https://api.lahza.io',
        ]);

        $this->paymentService = new PaymentService(new LahzaPaymentGateway);
    }

    /** @test */
    public function it_can_initialize_lahza_checkout(): void
    {
        Http::fake([
            'https://api.lahza.io/transaction/initialize' => Http::response([
                'data' => [
                    'reference' => 'test_ref_checkout',
                    'checkout_url' => 'https://checkout.lahza.io/pay/test_ref_checkout',
                ],
            ], 200),
        ]);

        $payment = $this->paymentService->initializeLahzaCheckout($this->user, $this->plan);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals($this->user->id, $payment->user_id);
        $this->assertEquals($this->plan->id, $payment->subscription_plan_id);
        $this->assertEquals(15.00, $payment->amount);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals('test_ref_checkout', $payment->gateway_reference);
    }

    /** @test */
    public function it_reuses_existing_pending_payment(): void
    {
        $existingPayment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 15.00,
            'status' => 'pending',
            'payment_method' => 'lahza',
        ]);

        $payment = $this->paymentService->initializeLahzaCheckout($this->user, $this->plan);

        $this->assertEquals($existingPayment->id, $payment->id);
    }

    /** @test */
    public function it_can_process_callback(): void
    {
        Notification::fake();

        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 15.00,
            'status' => 'pending',
            'payment_method' => 'lahza',
            'transaction_id' => 'test_callback_ref',
            'gateway_reference' => 'test_callback_ref',
        ]);

        Http::fake([
            'https://api.lahza.io/transaction/verify/test_callback_ref' => Http::response([
                'data' => [
                    'reference' => 'test_callback_ref',
                    'status' => 'success',
                    'amount' => 1500,
                    'currency' => 'USD',
                ],
            ], 200),
        ]);

        $subscription = $this->paymentService->processCallback('test_callback_ref');

        $this->assertInstanceOf(UserSubscription::class, $subscription);
        $this->assertEquals($this->user->id, $subscription->user_id);
        $this->assertEquals($this->plan->id, $subscription->subscription_plan_id);
        $this->assertEquals('active', $subscription->status);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $this->user->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_handles_callback_for_nonexistent_payment(): void
    {
        $subscription = $this->paymentService->processCallback('nonexistent_ref');

        $this->assertNull($subscription);
        $this->assertDatabaseMissing('payments', [
            'transaction_id' => 'nonexistent_ref',
        ]);
    }

    /** @test */
    public function it_can_get_checkout_url_for_payment(): void
    {
        $payment = Payment::factory()->create([
            'metadata' => [
                'checkout_url' => 'https://checkout.lahza.io/pay/test_ref',
            ],
        ]);

        $url = $this->paymentService->getCheckoutUrlForPayment($payment);

        $this->assertEquals('https://checkout.lahza.io/pay/test_ref', $url);
    }

    /** @test */
    public function it_can_verify_webhook_signature(): void
    {
        $payload = '{"event":"charge.success"}';
        $signature = hash_hmac('sha512', $payload, 'test_webhook_secret');

        $result = $this->paymentService->verifyWebhookSignature($payload, $signature);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_get_payment_history(): void
    {
        Payment::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);

        $history = $this->paymentService->getPaymentHistory($this->user, 5);

        $this->assertCount(3, $history);
        $this->assertEquals(3, $history->count());
    }

    /** @test */
    public function it_can_get_pending_payments(): void
    {
        Payment::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        Payment::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);

        $pending = $this->paymentService->getPendingPayments($this->user);

        $this->assertCount(1, $pending);
        $this->assertTrue($pending->first()->isPending());
    }

    /** @test */
    public function it_can_get_gateway_name(): void
    {
        $name = $this->paymentService->getGatewayName();

        $this->assertEquals('lahza', $name);
    }

    /** @test */
    public function it_throws_exception_when_checkout_url_missing(): void
    {
        Http::fake([
            'https://api.lahza.io/transaction/initialize' => Http::response([
                'data' => [
                    'reference' => 'test_ref',
                    'checkout_url' => null,
                ],
            ], 200),
        ]);

        $payment = $this->paymentService->initializeLahzaCheckout($this->user, $this->plan);
        $url = $this->paymentService->getCheckoutUrlForPayment($payment);

        $this->assertNull($url);
    }

    /** @test */
    public function it_can_use_cash_gateway_when_configured(): void
    {
        config(['payments.gateway' => 'cash']);

        $cashService = new PaymentService;
        $payment = $cashService->createSubscriptionPayment($this->user, $this->plan);

        $this->assertEquals('cash', $payment->payment_method);
    }
}
