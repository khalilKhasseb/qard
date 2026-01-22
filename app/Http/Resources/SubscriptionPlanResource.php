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
        'currency' => $this->currency ?? null,
        'billing_cycle' => $this->billing_cycle,
        'trial_days' => $this->trial_days ?? null,
        'card_limit' => $this->cards_limit,  // API shows card_limit, database has cards_limit
        'theme_limit' => $this->themes_limit,  // API shows theme_limit, database has themes_limit
        'custom_css_enabled' => $this->custom_css_allowed,  // API shows custom_css_enabled
        'analytics_enabled' => $this->analytics_enabled,
        'nfc_enabled' => $this->nfc_enabled,
        'custom_domain_enabled' => $this->custom_domain_allowed,  // API shows custom_domain_enabled
        'features' => $this->features,
        'is_active' => $this->is_active,
        'is_popular' => $this->is_popular ?? null,
        'sort_order' => $this->sort_order ?? null,
        'created_at' => $this->created_at?->toISOString(),
        'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
