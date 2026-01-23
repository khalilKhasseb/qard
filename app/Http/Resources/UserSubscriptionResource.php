<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Handle free plan (SubscriptionPlan model instead of UserSubscription)
        if ($this->resource && $this->resource instanceof \App\Models\SubscriptionPlan) {
            return [
                'id' => null,
                'user_id' => null,
                'subscription_plan_id' => $this->id,
                'status' => 'free',
                'starts_at' => null,
                'ends_at' => null,
                'trial_ends_at' => null,
                'canceled_at' => null,
                'created_at' => null,
                'updated_at' => null,

                // Relationships
                'plan' => new SubscriptionPlanResource($this),

                // Computed
                'is_active' => false,
                'is_trial' => false,
                'days_remaining' => null,
            ];
        }

        // Handle null (fallback)
        if ($this->resource === null) {
            return [
                'id' => null,
                'user_id' => null,
                'subscription_plan_id' => null,
                'status' => 'free',
                'starts_at' => null,
                'ends_at' => null,
                'trial_ends_at' => null,
                'canceled_at' => null,
                'created_at' => null,
                'updated_at' => null,

                // Relationships
                'plan' => null,

                // Computed
                'is_active' => false,
                'is_trial' => false,
                'days_remaining' => null,
            ];
        }

        // Handle active subscription
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'subscription_plan_id' => $this->subscription_plan_id,
            'status' => $this->status,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'trial_ends_at' => $this->trial_ends_at?->toISOString(),
            'canceled_at' => $this->canceled_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'plan' => new SubscriptionPlanResource($this->whenLoaded('subscriptionPlan')),

            // Computed
            'is_active' => $this->isActive(),
            'is_trial' => $this->isOnTrial(),
            'days_remaining' => $this->daysRemaining(),
        ];
    }
}
