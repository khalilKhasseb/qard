<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle',
        'cards_limit',
        'themes_limit',
        'custom_css_allowed',
        'analytics_enabled',
        'nfc_enabled',
        'custom_domain_allowed',
        'features',
        'translation_credits_monthly',
        'unlimited_translations',
        'per_credit_cost',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'custom_css_allowed' => 'boolean',
            'analytics_enabled' => 'boolean',
            'nfc_enabled' => 'boolean',
            'custom_domain_allowed' => 'boolean',
            'features' => 'array',
            'translation_credits_monthly' => 'integer',
            'unlimited_translations' => 'boolean',
            'per_credit_cost' => 'decimal:6',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the features array based on enabled features
     */
    protected function features(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                // If features is already set in database, return it
                if ($value) {
                    return $value;
                }

                // Otherwise, build features array from boolean attributes
                $features = [];

                if ($this->custom_css_allowed) {
                    $features[] = 'Custom CSS';
                }

                if ($this->analytics_enabled) {
                    $features[] = 'Advanced Analytics';
                }

                if ($this->nfc_enabled) {
                    $features[] = 'NFC Card Support';
                }

                if ($this->custom_domain_allowed) {
                    $features[] = 'Custom Domain';
                }

                return $features ?: null;
            }
        );
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByBillingCycle($query, string $cycle)
    {
        return $query->where('billing_cycle', $cycle);
    }
}
