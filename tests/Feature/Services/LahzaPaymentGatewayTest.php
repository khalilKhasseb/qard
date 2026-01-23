<?php

namespace Tests\Feature\Services;

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\LahzaPaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LahzaPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected SubscriptionPlan $plan;

    protected LahzaPaymentGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->plan = SubscriptionPlan::factory()->create([
            'price' => 10.00,
            'name' => 'Test Plan',
        ]);

        $this->gateway = new LahzaPaymentGateway;

        // Mock config for tests
        config([
            'payments.lahza.public_key' => 'test_public_key',
            'payments.lahza.secret_key' => 'test_secret_key',
            'payments.lahza.webhook_secret' => 'test_webhook_secret',
            'payments.lahza.test_mode' => true,
            'payments.lahza.base_url' => 'https://api.lahza.io',
            'payments.lahza.currency' => 'USD',
            'payments.lahza.channels' => ['card'],
        ]);
    }

    /** @test */
    public function it_can_create_a_payment(): void
    {
        Http::fake([
            'https://api.lahza.io/transaction/initialize' => Http::response([
                'data' => [
                    'reference' => 'test_ref_123',
                    'checkout_url' => 'https://checkout.lahza.io/pay/test_ref_123',
                ],
            ], 200),
        ]);

        $payment = $this->gateway->createPayment($this->user, 10.00, [
            'subscription_plan_id' => $this->plan->id,
            'currency' => 'USD',
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals(10.00, $payment->amount);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals('lahza', $payment->payment_method);
        $this->assertEquals('test_ref_123', $payment->gateway_reference);
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'amount' => 10.00,
            'payment_method' => 'lahza',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_converts_amount_to_cents(): void
    {
        Http::fake([
            'https://api.lahza.io/transaction/initialize' => Http::response([
                'data' => [
                    'reference' => 'test_ref_456',
                    'checkout_url' => 'https://checkout.lahza.io/pay/test_ref_456',
                ],
            ], 200),
        ]);

        $this->gateway->createPayment($this->user, 50.00, []);

        // Verify the request was sent with amount in cents
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.lahza.io/transaction/initialize' &&
                   $request->data()['amount'] === 5000; // 50.00 * 100
        });
    }

    /** @test */
    public function it_can_verify_a_transaction(): void
    {
        Http::fake([
            'https://api.lahza.io/transaction/verify/test_ref_789' => Http::response([
                'data' => [
                    'reference' => 'test_ref_789',
                    'status' => 'success',
                    'amount' => 1000,
                    'currency' => 'USD',
                ],
            ], 200),
        ]);

        $result = $this->gateway->verifyTransaction('test_ref_789');

        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('success', $result['data']['status']);
        $this->assertEquals(1000, $result['data']['amount']);
    }

    /** @test */
    public function it_can_confirm_a_successful_payment(): void
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 10.00,
            'payment_method' => 'lahza',
            'status' => 'pending',
            'gateway_reference' => 'test_ref_success',
        ]);

        Http::fake([
            'https://api.lahza.io/transaction/verify/test_ref_success' => Http::response([
                'data' => [
                    'reference' => 'test_ref_success',
                    'status' => 'success',
                    'amount' => 1000,
                    'currency' => 'USD',
                ],
            ], 200),
        ]);

        $result = $this->gateway->confirmPayment($payment);

        $this->assertTrue($result);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
        ]);
        $payment->refresh();
        $this->assertNotNull($payment->paid_at);
    }

    /** @test */
    public function it_handles_failed_payment_verification(): void
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 10.00,
            'payment_method' => 'lahza',
            'status' => 'pending',
            'gateway_reference' => 'test_ref_failed',
        ]);

        Http::fake([
            'https://api.lahza.io/transaction/verify/test_ref_failed' => Http::response([
                'data' => [
                    'reference' => 'test_ref_failed',
                    'status' => 'failed',
                    'amount' => 1000,
                    'currency' => 'USD',
                ],
            ], 200),
        ]);

        $result = $this->gateway->confirmPayment($payment);

        $this->assertFalse($result);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'failed',
        ]);
    }

    /** @test */
    public function it_can_refund_a_payment(): void
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 10.00,
            'payment_method' => 'lahza',
            'status' => 'completed',
            'gateway_reference' => 'test_ref_refund',
        ]);

        Http::fake([
            'https://api.lahza.io/transaction/refund' => Http::response([
                'data' => [
                    'reference' => 'test_ref_refund',
                    'status' => 'refunded',
                ],
            ], 200),
        ]);

        $result = $this->gateway->refundPayment($payment);

        $this->assertTrue($result);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'refunded',
        ]);
    }

    /** @test */
    public function it_throws_exception_on_api_error(): void
    {
        Http::fake([
            'https://api.lahza.io/transaction/initialize' => Http::response([
                'error' => 'Invalid amount',
            ], 400),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Lahza API error');

        $this->gateway->createPayment($this->user, -10.00, []);
    }

    /** @test */
    public function it_can_get_checkout_url(): void
    {
        $payment = Payment::factory()->create([
            'metadata' => [
                'checkout_url' => 'https://checkout.lahza.io/pay/test_ref_123',
            ],
        ]);

        $url = $this->gateway->getCheckoutUrl($payment);

        $this->assertEquals('https://checkout.lahza.io/pay/test_ref_123', $url);
    }

    /** @test */
    public function it_can_generate_callback_url(): void
    {
        $url = $this->gateway->generateCallbackUrl('test_ref_123');

        $this->assertStringContainsString('/payments/callback', $url);
        $this->assertStringContainsString('reference=test_ref_123', $url);
    }

    /** @test */
    public function it_can_verify_webhook_signature(): void
    {
        $payload = '{"event":"charge.success","data":{"reference":"test_ref"}}';
        $signature = hash_hmac('sha512', $payload, 'test_webhook_secret');

        $result = $this->gateway->verifyWebhookSignature($payload, $signature);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_detects_invalid_webhook_signature(): void
    {
        $payload = '{"event":"charge.success","data":{"reference":"test_ref"}}';
        $signature = 'invalid_signature';

        $result = $this->gateway->verifyWebhookSignature($payload, $signature);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_returns_gateway_name(): void
    {
        $name = $this->gateway->getGatewayName();

        $this->assertEquals('lahza', $name);
    }

    /** @test */
    public function it_does_not_support_recurring_payments(): void
    {
        $this->assertFalse($this->gateway->supportsRecurring());
    }
}
