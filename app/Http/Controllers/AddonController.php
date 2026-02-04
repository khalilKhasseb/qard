<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Services\AddonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AddonController extends Controller
{
    public function __construct(
        protected AddonService $addonService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $addons = $this->addonService->getAvailableAddons($user);
        $hasActiveSubscription = $user->activeSubscription()
            ->whereHas('subscriptionPlan')
            ->exists();

        return Inertia::render('Addons/Index', [
            'extraCards' => $addons['extra_cards'],
            'featureUnlocks' => $addons['feature_unlocks'],
            'hasActiveSubscription' => $hasActiveSubscription,
        ]);
    }

    public function checkout(Request $request, Addon $addon): Response
    {
        return Inertia::render('Addons/Checkout', [
            'addon' => $addon,
        ]);
    }

    public function initialize(Request $request, Addon $addon)
    {
        $user = $request->user();

        // Check if user already owns this feature unlock
        if ($addon->isFeatureUnlock() && $user->hasFeatureAddon($addon->feature_key)) {
            return response()->json([
                'message' => __('addons.already_owned'),
            ], 422);
        }

        try {
            $payment = $this->addonService->initializeAddonCheckout($user, $addon);
            $checkoutUrl = $this->addonService->getCheckoutUrlForPayment($payment);

            if (! $checkoutUrl) {
                $checkoutUrl = $payment->metadata['authorization_url']
                    ?? $payment->metadata['api_response']['data']['authorization_url']
                    ?? null;
            }

            if (! $checkoutUrl) {
                Log::error('Failed to generate addon checkout URL', [
                    'payment_id' => $payment->id,
                    'addon_id' => $addon->id,
                ]);

                return response()->json([
                    'message' => __('addons.checkout_failed'),
                ], 500);
            }

            return response()->json([
                'message' => __('addons.payment_initialized'),
                'payment' => $payment,
                'checkout_url' => $checkoutUrl,
                'reference' => $payment->transaction_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Add-on payment initialization failed', [
                'user_id' => $user->id,
                'addon_id' => $addon->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => __('addons.initialization_failed'),
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (! $reference) {
            return response()->json([
                'message' => 'Missing transaction reference.',
            ], 400);
        }

        try {
            $userAddon = $this->addonService->processCallback($reference);

            if ($userAddon) {
                $userAddon->load('addon');

                return Inertia::render('Addons/Callback', [
                    'success' => true,
                    'addon' => $userAddon->addon,
                    'message' => __('addons.purchase_success'),
                ]);
            }

            return Inertia::render('Addons/Callback', [
                'success' => false,
                'message' => __('addons.purchase_failed'),
            ]);

        } catch (\Exception $e) {
            Log::error('Add-on callback error', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return Inertia::render('Addons/Callback', [
                'success' => false,
                'message' => __('addons.purchase_error'),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }
}
