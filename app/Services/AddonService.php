<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserAddon;
use App\Notifications\AddonPurchased;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddonService
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Get all active add-ons, marking owned feature unlocks for the user.
     *
     * @return array{extra_cards: \Illuminate\Database\Eloquent\Collection, feature_unlocks: \Illuminate\Database\Eloquent\Collection}
     */
    public function getAvailableAddons(User $user): array
    {
        $addons = Addon::query()->active()->orderBy('sort_order')->orderBy('price')->get();

        $ownedFeatureKeys = $user->userAddons()
            ->whereHas('addon', fn ($q) => $q->where('type', 'feature_unlock'))
            ->with('addon')
            ->get()
            ->pluck('addon.feature_key')
            ->unique()
            ->toArray();

        $addons->each(function (Addon $addon) use ($ownedFeatureKeys) {
            $addon->is_owned = $addon->isFeatureUnlock() && in_array($addon->feature_key, $ownedFeatureKeys);
        });

        return [
            'extra_cards' => $addons->where('type', 'extra_cards')->values(),
            'feature_unlocks' => $addons->where('type', 'feature_unlock')->values(),
        ];
    }

    /**
     * Initialize a Lahza checkout for an add-on purchase.
     */
    public function initializeAddonCheckout(User $user, Addon $addon): Payment
    {
        // Check for existing pending payment for this addon
        $existingPending = $user->payments()
            ->pending()
            ->where('addon_id', $addon->id)
            ->where('created_at', '>', now()->subMinutes(30))
            ->first();

        if ($existingPending) {
            return $existingPending;
        }

        $callbackUrl = url(route('addons.callback', [], false));

        $gateway = new LahzaPaymentGateway;

        $payment = $gateway->createPayment($user, $addon->price, [
            'subscription_plan_id' => null,
            'currency' => $addon->currency,
            'notes' => "Add-on purchase: {$addon->name}",
            'callback_url' => $callbackUrl,
            'metadata' => [
                'addon_id' => $addon->id,
                'addon_name' => $addon->name,
                'addon_type' => $addon->type,
                'user_email' => $user->email,
                'user_name' => $user->name,
            ],
        ]);

        // Set the addon_id on the payment
        $payment->update(['addon_id' => $addon->id]);

        return $payment;
    }

    /**
     * Confirm payment and grant the add-on to the user.
     */
    public function confirmPaymentAndGrantAddon(Payment $payment): UserAddon
    {
        return DB::transaction(function () use ($payment) {
            // Mark payment as completed if not already
            if ($payment->isPending()) {
                $payment->markAsCompleted();
            }

            $userAddon = UserAddon::create([
                'user_id' => $payment->user_id,
                'addon_id' => $payment->addon_id,
                'payment_id' => $payment->id,
                'granted_by' => 'purchase',
            ]);

            // Send notification
            $payment->user->notify(new AddonPurchased($payment, $userAddon));

            Log::info('Add-on granted via payment', [
                'user_id' => $payment->user_id,
                'addon_id' => $payment->addon_id,
                'payment_id' => $payment->id,
                'user_addon_id' => $userAddon->id,
            ]);

            return $userAddon;
        });
    }

    /**
     * Admin manual grant (no payment required).
     */
    public function grantAddon(User $user, Addon $addon, ?string $notes = null): UserAddon
    {
        $userAddon = UserAddon::create([
            'user_id' => $user->id,
            'addon_id' => $addon->id,
            'payment_id' => null,
            'granted_by' => 'admin_grant',
            'notes' => $notes,
        ]);

        Log::info('Add-on granted by admin', [
            'user_id' => $user->id,
            'addon_id' => $addon->id,
            'user_addon_id' => $userAddon->id,
            'notes' => $notes,
        ]);

        return $userAddon;
    }

    /**
     * Check if a user has a specific feature add-on.
     */
    public function userHasFeatureAddon(User $user, string $featureKey): bool
    {
        return $user->hasFeatureAddon($featureKey);
    }

    /**
     * Get the checkout URL for an add-on payment.
     */
    public function getCheckoutUrlForPayment(Payment $payment): ?string
    {
        return $this->paymentService->getCheckoutUrlForPayment($payment);
    }

    /**
     * Process an add-on callback by reference.
     */
    public function processCallback(string $reference): ?UserAddon
    {
        $payment = Payment::where('transaction_id', $reference)
            ->orWhere('gateway_reference', $reference)
            ->first();

        if (! $payment) {
            Log::warning('Add-on payment callback received for unknown reference', [
                'reference' => $reference,
            ]);

            return null;
        }

        if (! $payment->addon_id) {
            return null;
        }

        // Check if already granted
        $existing = UserAddon::where('payment_id', $payment->id)->first();
        if ($existing) {
            return $existing;
        }

        return $this->confirmPaymentAndGrantAddon($payment);
    }
}
