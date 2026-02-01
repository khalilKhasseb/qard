<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserSubscriptionResource;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function show(Request $request)
    {

        logger('Auth check:', [
            'authenticated' => auth()->check(),
            'user' => auth()->user(),
            'guard' => auth()->getDefaultDriver(),
            'session_id' => session()->getId(),
        ]);
        $subscription = $request->user()
            ->activeSubscription()
            ->with('subscriptionPlan')
            ->first();

        if (! $subscription) {
            // Fetch free plan data
            $freePlan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();

            return new UserSubscriptionResource($freePlan);
        }

        // dd( (new UserSubscriptionResource($subscription))->toArray($request));

        return new UserSubscriptionResource($subscription);
    }

    public function cancel(Request $request)
    {
        $subscription = $request->user()->activeSubscription;

        if (! $subscription) {
            return response()->json([
                'message' => 'No active subscription to cancel',
            ], 404);
        }

        if ($subscription->status === 'canceled') {
            return response()->json([
                'message' => 'Subscription is already canceled',
            ], 400);
        }

        $subscription->update([
            'status' => 'canceled',
            'canceled_at' => now(),
        ]);

        return new UserSubscriptionResource($subscription->fresh());
    }

    public function sync(Request $request)
    {
        $user = $request->user();
        $subscription = $user->activeSubscription()->with('subscriptionPlan')->first();

        if (! $subscription) {
            // Return free plan data for users without subscription
            $freePlan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();

            return new UserSubscriptionResource($freePlan);
        }

        // Refresh the plan data to get latest changes
        $subscription->subscriptionPlan->refresh();

        return new UserSubscriptionResource($subscription);
    }
}
