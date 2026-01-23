<?php

namespace Tests\Feature\Http;

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LahzaWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected SubscriptionPlan $plan;

    protected Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->plan = SubscriptionPlan::factory()->create(['price' => 10.00]);
        $this->payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => 10.00,
            'payment_method' => 'lahza',
            'status' => 'pending',
            'gateway_reference' => 'test_webhook_ref',
        ]);

        config([
            'payments.lahza.webhook_secret' => 'test_webhook_secret',
            'payments.security.verify_webhook_signatures' => true,
        ]);
    }

    /** @test */
    public function it_can_handle_successful_charge_webhook(): void
    {
        Http::fake([
            'https://api.lahza.io/transaction/verify/test_webhook_ref' => Http::response([
                'data' => [
                    'reference' => 'test_webhook_ref',
                    'status' => 'success',
                    'amount' => 1000,
                    'currency' => 'USD',
                ],
            ], 200),
        ]);

        $payload = json_encode([
            'event' => 'charge.success',
            'data' => [
                'reference' => 'test_webhook_ref',
                'status' => 'success',
                'amount' => 1000,
                'currency' => 'USD',
                'created_at' => now()->toISOString(),
            ],
        ]);

        $signature = hash_hmac('sha512', $payload, 'test_webhook_secret');

        $response = $this->postJson('/webhooks/lahza', json_decode($payload, true), [
            'X-Lahza-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $this->user->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_rejects_requests_without_signature(): void
    {
        $response = $this->postJson('/webhooks/lahza', [
            'event' => 'charge.success',
            'data' => ['reference' => 'test'],
        ]);

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Missing signature header']);
    }

    /** @test */
    public function it_rejects_invalid_webhook_signature(): void
    {
        $response = $this->postJson('/webhooks/lahza', [
            'event' => 'charge.success',
            'data' => ['reference' => 'test'],
        ], [
            'X-Lahza-Signature' => 'invalid_signature',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Invalid signature']);
    }

    /** @test */
    public function it_handles_failed_charge_webhook(): void
    {
        $payload = json_encode([
            'event' => 'charge.failed',
            'data' => [
                'reference' => 'test_webhook_ref',
                'status' => 'failed',
                'failure_reason' => 'Insufficient funds',
            ],
        ]);

        $signature = hash_hmac('sha512', $payload, 'test_webhook_secret');

        $response = $this->postJson('/webhooks/lahza', json_decode($payload, true), [
            'X-Lahza-Signature' => $signature,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'status' => 'failed',
        ]);
    }

    /** @test */
    public function it_handles_refund_webhook(): void
    {
        $this->payment->update(['status' => 'completed']);

        $payload = json_encode([
            'event' => 'charge.refunded',
            'data' => [
                'reference' => 'test_webhook_ref',
                'status' => 'refunded',
                'amount' => 1000,
            ],
        ]);

        $signature = hash_hmac('sha512', $payload, 'test_webhook_secret');

        $response = $this->postJson('/webhooks/lahza', json_decode($payload, true), [
            'X-Lahza-Signature' => $signature,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'status' => 'refunded',
        ]);
    }

    /** @test */
    public function it_creates_payment_from_webhook_if_not_found(): void
    {
        Http::fake([
            'https://api.lahza.io/transaction/verify/new_webhook_ref' => Http::response([
                'data' => [
                    'reference' => 'new_webhook_ref',
                    'status' => 'success',
                    'amount' => 2000,
                    'currency' => 'USD',
                ],
            ], 200),
        ]);

        $payload = json_encode([
            'event' => 'charge.success',
            'data' => [
                'reference' => 'new_webhook_ref',
                'status' => 'success',
                'amount' => 2000,
                'currency' => 'USD',
                'created_at' => now()->toISOString(),
                'metadata' => [
                    'user_id' => $this->user->id,
                    'plan_name' => 'Test Plan',
                ],
            ],
        ]);

        $signature = hash_hmac('sha512', $payload, 'test_webhook_secret');

        $response = $this->postJson('/webhooks/lahza', json_decode($payload, true), [
            'X-Lahza-Signature' => $signature,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'gateway_reference' => 'new_webhook_ref',
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function it_validates_required_webhook_fields(): void
    {
        $payload = json_encode([
            'event' => 'charge.success',
            // Missing 'data' field
        ]);

        $signature = hash_hmac('sha512', $payload, 'test_webhook_secret');

        $response = $this->postJson('/webhooks/lahza', json_decode($payload, true), [
            'X-Lahza-Signature' => $signature,
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function it_returns_500_on_processing_error(): void
    {
        // Force an exception during processing
        \Illuminate\Support\Facades\Log::shouldReceive('error')
            ->once()
            ->with('Webhook processing failed', \Mockery::any());

        $payload = json_encode([
            'event' => 'charge.success',
            'data' => [
                'reference' => 'test_webhook_ref',
                'status' => 'success',
            ],
        ]);

        $signature = hash_hmac('sha512', $payload, 'test_webhook_secret');

        // Mock the gateway to throw an exception
        \Mockery::mock(\App\Services\LahzaPaymentGateway::class)
            ->shouldReceive('confirmPayment')
            ->andThrow(new \Exception('Test exception'));

        $response = $this->postJson('/webhooks/lahza', json_decode($payload, true), [
            'X-Lahza-Signature' => $signature,
        ]);

        $response->assertStatus(500);
    }

    /** @test */
    public function it_handles_duplicate_webhooks_gracefully(): void
    {
        $this->payment->update(['status' => 'completed']);

        Http::fake([
            'https://api.lahza.io/transaction/verify/test_webhook_ref' => Http::response([
                'data' => [
                    'reference' => 'test_webhook_ref',
                    'status' => 'success',
                ],
            ], 200),
        ]);

        $payload = json_encode([
            'event' => 'charge.success',
            'data' => [
                'reference' => 'test_webhook_ref',
                'status' => 'success',
            ],
        ]);

        $signature = hash_hmac('sha512', $payload, 'test_webhook_secret');

        // Send the webhook twice
        $this->postJson('/webhooks/lahza', json_decode($payload, true), [
            'X-Lahza-Signature' => $signature,
        ]);

        $response = $this->postJson('/webhooks/lahza', json_decode($payload, true), [
            'X-Lahza-Signature' => $signature,
        ]);

        $response->assertStatus(200);

        // Payment should still be completed (not duplicated)
        $this->assertDatabaseCount('user_subscriptions', 1);
    }

    /** @test */
    public function it_handles_unexpected_webhook_event(): void
    {
        $payload = json_encode([
            'event' => 'subscription.updated',
            'data' => [
                'reference' => 'test_webhook_ref',
            ],
        ]);

        $signature = hash_hmac('sha512', $payload, 'test_webhook_secret');

        $response = $this->postJson('/webhooks/lahza', json_decode($payload, true), [
            'X-Lahza-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    /** @test */
    public function it_can_disable_webhook_signature_verification(): void
    {
        config(['payments.security.verify_webhook_signatures' => false]);

        $response = $this->postJson('/webhooks/lahza', [
            'event' => 'charge.success',
            'data' => [
                'reference' => 'test_webhook_ref',
                'status' => 'success',
            ],
        ]);

        // Should be accepted without signature
        $response->assertStatus(200);
    }
}
