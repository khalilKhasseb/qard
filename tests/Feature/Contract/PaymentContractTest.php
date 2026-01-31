<?php

/**
 * Payment Contract Tests
 *
 * These tests ensure API responses match the TypeScript contract.
 * Contract: resources/js/types/contracts/Payment.ts
 */

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->plan = SubscriptionPlan::factory()->create();
    $this->actingAs($this->user);
});

it('subscription plans response matches contract', function () {
    $response = $this->getJson('/api/subscription-plans');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => getSubscriptionPlanContractFields(),
            ],
        ]);
});

it('subscription plan has correct field types', function () {
    $response = $this->getJson('/api/subscription-plans');
    $plan = $response->json('data.0');

    // Verify field types match contract
    expect($plan['id'])->toBeInt()
        ->and($plan['name'])->toBeString()
        ->and($plan['slug'])->toBeString()
        ->and($plan['billing_cycle'])->toBeString()
        ->and($plan['cards_limit'])->toBeInt()
        ->and($plan['themes_limit'])->toBeInt()
        ->and($plan['custom_css_allowed'])->toBeBool()
        ->and($plan['analytics_enabled'])->toBeBool()
        ->and($plan['nfc_enabled'])->toBeBool()
        ->and($plan['custom_domain_allowed'])->toBeBool()
        ->and($plan['translation_credits_monthly'])->toBeInt()
        ->and($plan['unlimited_translations'])->toBeBool()
        ->and($plan['is_active'])->toBeBool()
        ->and($plan['created_at'])->toBeString()
        ->and($plan['updated_at'])->toBeString();
});

it('subscription plan uses correct field names (not legacy names)', function () {
    $response = $this->getJson('/api/subscription-plans');
    $plan = $response->json('data.0');

    // Contract fields (correct - matching database)
    expect($plan)->toHaveKey('cards_limit')
        ->and($plan)->toHaveKey('themes_limit')
        ->and($plan)->toHaveKey('custom_css_allowed')
        ->and($plan)->toHaveKey('custom_domain_allowed');

    // Legacy/transformed fields should NOT exist
    expect($plan)->not->toHaveKey('card_limit')
        ->and($plan)->not->toHaveKey('theme_limit')
        ->and($plan)->not->toHaveKey('custom_css_enabled')
        ->and($plan)->not->toHaveKey('custom_domain_enabled');
});

it('payment history response matches contract', function () {
    Payment::factory()->create([
        'user_id' => $this->user->id,
        'subscription_plan_id' => $this->plan->id,
    ]);

    $response = $this->getJson('/api/payments/history');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => getPaymentContractFields(),
            ],
        ]);
});

it('payment has correct field types', function () {
    Payment::factory()->create([
        'user_id' => $this->user->id,
        'subscription_plan_id' => $this->plan->id,
    ]);

    $response = $this->getJson('/api/payments/history');
    $payment = $response->json('data.0');

    // Verify field types match contract
    expect($payment['id'])->toBeInt()
        ->and($payment['user_id'])->toBeInt()
        ->and($payment['subscription_plan_id'])->toBeInt()
        ->and($payment['status'])->toBeString()
        ->and($payment['payment_method'])->toBeString()
        ->and($payment['transaction_id'])->toBeString()
        ->and($payment['created_at'])->toBeString()
        ->and($payment['updated_at'])->toBeString();
});

/**
 * Get expected subscription plan contract fields.
 * Must match: resources/js/types/contracts/Payment.ts > SubscriptionPlan
 */
function getSubscriptionPlanContractFields(): array
{
    return [
        'id',
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'trial_days',
        'cards_limit',
        'themes_limit',
        'custom_css_allowed',
        'analytics_enabled',
        'nfc_enabled',
        'custom_domain_allowed',
        'translation_credits_monthly',
        'unlimited_translations',
        'features',
        'is_active',
        'is_popular',
        'sort_order',
        'created_at',
        'updated_at',
    ];
}

/**
 * Get expected payment contract fields.
 * Must match: resources/js/types/contracts/Payment.ts > Payment
 */
function getPaymentContractFields(): array
{
    return [
        'id',
        'user_id',
        'subscription_plan_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'transaction_id',
        'notes',
        'paid_at',
        'created_at',
        'updated_at',
    ];
}
