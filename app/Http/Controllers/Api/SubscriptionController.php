<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserSubscriptionResource;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function show(Request $request)
    {

        $subscription = $request->user()
            ->activeSubscription()
            ->with('subscriptionPlan')
            ->first();

        if (! $subscription) {
            return new UserSubscriptionResource(null);
        }

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
            return response()->json([
                'message' => 'No active subscription found',
            ], 404);
        }

        // Refresh the plan data to get latest changes
        $subscription->subscriptionPlan->refresh();

        return new UserSubscriptionResource($subscription);
    }
}
