<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency ?? 'USD',
            'billing_cycle' => $this->billing_cycle,
            'trial_days' => $this->trial_days ?? 0,
            'cards_limit' => $this->cards_limit,
            'themes_limit' => $this->themes_limit,
            'custom_css_allowed' => $this->custom_css_allowed,
            'analytics_enabled' => $this->analytics_enabled,
            'nfc_enabled' => $this->nfc_enabled,
            'custom_domain_allowed' => $this->custom_domain_allowed,
            'translation_credits_monthly' => $this->translation_credits_monthly ?? 0,
            'unlimited_translations' => $this->unlimited_translations ?? false,
            'features' => $this->features,
            'is_active' => $this->is_active,
            'is_popular' => $this->is_popular ?? false,
            'sort_order' => $this->sort_order ?? 0,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
