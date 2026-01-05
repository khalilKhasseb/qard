<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserSubscriptionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $subscription = $request->user()
            ->activeSubscription()
            ->with('subscriptionPlan')
            ->first();

        if (!$subscription) {
            return response()->json([
                'subscription' => null,
                'message' => 'No active subscription'
            ]);
        }

        return response()->json([
            'subscription' => new UserSubscriptionResource($subscription)
        ]);
    }

    public function cancel(Request $request): JsonResponse
    {
        $subscription = $request->user()->activeSubscription;

        if (!$subscription) {
            return response()->json([
                'message' => 'No active subscription to cancel'
            ], 404);
        }

        if ($subscription->status === 'canceled') {
            return response()->json([
                'message' => 'Subscription is already canceled'
            ], 400);
        }

        $subscription->update([
            'status' => 'canceled',
            'canceled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Subscription canceled successfully',
            'subscription' => new UserSubscriptionResource($subscription->fresh())
        ]);
    }
}
