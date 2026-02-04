<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'phone_verified_at',
        'password',
        'is_admin',
        'language',
        'subscription_tier',
        'subscription_status',
        'subscription_expires_at',
        'pending_plan_id',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'subscription_expires_at' => 'datetime',
            'last_login' => 'datetime',
        ];
    }

    public function cards(): HasMany
    {
        return $this->hasMany(BusinessCard::class);
    }

    public function themes(): HasMany
    {
        return $this->hasMany(Theme::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(UserSubscription::class)
            ->where('status', 'active')
            ->latest();
    }

    public function pendingPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'pending_plan_id');
    }

    public function userAddons(): HasMany
    {
        return $this->hasMany(UserAddon::class);
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'user_addons')->withTimestamps();
    }

    /**
     * Determine where to redirect user after verification.
     */
    public function getPostVerificationRedirect(): string
    {
        // If has pending plan and no active subscription, go to checkout
        if ($this->pending_plan_id && ! $this->activeSubscription()->exists()) {
            return route('payments.checkout', $this->pending_plan_id);
        }

        // If no subscription at all, go to plan selection
        if (! $this->activeSubscription()->exists()) {
            return route('subscription.index');
        }

        return route('dashboard');
    }

    public function translationUsage(): HasMany
    {
        return $this->hasMany(UserTranslationUsage::class);
    }

    public function currentTranslationUsage(): HasOne
    {
        return $this->hasOne(UserTranslationUsage::class)
            ->where('is_active', true)
            ->latest();
    }

    public function translationHistory(): HasMany
    {
        return $this->hasMany(TranslationHistory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('subscription_status', 'active');
    }

    public function scopeByTier($query, string $tier)
    {
        return $query->where('subscription_tier', $tier);
    }

    public function canCreateCard(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        $limit = $this->getCardLimit();

        return $this->cards()->count() < $limit;
    }

    public function canUsePlan(): bool
    {
        // get user plan and  see if its active
        //
        return $this->whereHas('subscriptions', function ($query) {
            $query->whereHas('subscriptionPlan', function ($query) {
                $query->where('status', 'active');
            });
        })->get()->count() > 0;

    }

    public function canCreateTheme(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        $limit = $this->getThemeLimit();

        return $this->themes()->count() < $limit;
    }

    public function canUseCustomCss(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $subscription = $this->activeSubscription()->with('subscriptionPlan')->first();

        if (! $subscription || ! $subscription->isActive()) {
            return false;
        }

        if ($subscription->subscriptionPlan?->custom_css_allowed) {
            return true;
        }

        return $this->hasFeatureAddon('custom_css');
    }

    public function getCardLimit(): int
    {
        if ($this->isAdmin()) {
            return PHP_INT_MAX;
        }

        $subscription = $this->activeSubscription()->with('subscriptionPlan')->first();
        $planLimit = $subscription?->subscriptionPlan?->cards_limit ?? 0;

        if ($subscription && $subscription->isActive()) {
            return $planLimit + $this->getExtraCardSlots();
        }

        return $planLimit;
    }

    public function getThemeLimit(): int
    {
        if ($this->isAdmin()) {
            return PHP_INT_MAX;
        }

        $subscription = $this->activeSubscription()->with('subscriptionPlan')->first();

        return $subscription?->subscriptionPlan?->themes_limit ?? 0;
    }

    public function isSubscriptionActive(): bool
    {
        if ($this->subscription_status !== 'active') {
            return false;
        }

        if ($this->subscription_expires_at && $this->subscription_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isAdmin();
        }

        return true;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Check if the user has verified their phone number.
     */
    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified_at !== null;
    }

    /**
     * Mark the user's phone as verified.
     */
    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill([
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Get the phone number that should be used for verification.
     */
    public function getPhoneForVerification(): ?string
    {
        return $this->phone;
    }

    /**
     * Get remaining translation credits for the current period.
     */
    public function getRemainingTranslationCredits(): int
    {
        return Cache::remember(
            "translation_credits:user:{$this->id}",
            300, // 5 minutes
            function () {
                // Check if user has unlimited translations
                if ($this->hasUnlimitedTranslations()) {
                    return PHP_INT_MAX;
                }

                $usage = $this->currentTranslationUsage()->first();

                if (! $usage) {
                    // Initialize usage for current period
                    $this->initializeTranslationUsage();
                    $usage = $this->currentTranslationUsage()->first();
                }

                // Check if period expired
                if ($usage && $usage->isExpired()) {
                    $usage->markAsExpired();
                    $this->initializeTranslationUsage();
                    $usage = $this->currentTranslationUsage()->first();
                }

                // Sync credits if plan was upgraded during period
                $limit = $this->getTranslationCreditLimit();
                if ($usage && ! $this->hasUnlimitedTranslations() && $usage->credits_available < $limit) {
                    $usage->update(['credits_available' => $limit]);
                }

                return $usage ? $usage->getRemainingCredits() : 0;
            }
        );
    }

    /**
     * Check if user has enough translation credits.
     */
    public function hasTranslationCredits(int $required = 1): bool
    {
        if ($this->hasUnlimitedTranslations()) {
            return true;
        }

        return $this->getRemainingTranslationCredits() >= $required;
    }

    /**
     * Deduct translation credits.
     */
    public function deductTranslationCredits(int $amount = 1): bool
    {
        if ($this->hasUnlimitedTranslations()) {
            return true;
        }

        $usage = $this->currentTranslationUsage()->first();

        if (! $usage || ! $usage->hasCredits($amount)) {
            return false;
        }

        $result = $usage->deductCredits($amount);

        // Clear cache
        Cache::forget("translation_credits:user:{$this->id}");

        // Trigger real-time credit update for all user sessions
        Cache::put("credits_updated:{$this->id}", true, now()->addMinutes(5));

        return $result;
    }

    /**
     * Check if user has translation feature enabled.
     */
    public function hasTranslationFeature(): bool
    {
        $subscription = $this->activeSubscription()->with('subscriptionPlan')->first();

        if ($subscription?->subscriptionPlan) {
            // Check if explicitly enabled in features JSON
            if (isset($subscription->subscriptionPlan->features['ai_translation']) && $subscription->subscriptionPlan->features['ai_translation']) {
                return true;
            }

            // Or if they have credits/unlimited set
            return $subscription->subscriptionPlan->translation_credits_monthly > 0 ||
                   $subscription->subscriptionPlan->unlimited_translations;
        }

        // No subscription = no translation feature
        return false;
    }

    /**
     * Get translation credit limit from subscription.
     */
    public function getTranslationCreditLimit(): int
    {
        $subscription = $this->activeSubscription()->with('subscriptionPlan')->first();

        if ($subscription?->subscriptionPlan) {
            if ($subscription->subscriptionPlan->unlimited_translations) {
                return PHP_INT_MAX;
            }

            return $subscription->subscriptionPlan->translation_credits_monthly ?? 0;
        }

        // No subscription = no credits
        return 0;
    }

    /**
     * Check if user has unlimited translations.
     */
    public function hasUnlimitedTranslations(): bool
    {
        $subscription = $this->activeSubscription()->with('subscriptionPlan')->first();

        return $subscription && $subscription->subscriptionPlan && $subscription->subscriptionPlan->unlimited_translations;
    }

    public function canUseNfc(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $subscription = $this->activeSubscription()->with('subscriptionPlan')->first();

        if (! $subscription || ! $subscription->isActive()) {
            return false;
        }

        if ($subscription->subscriptionPlan?->nfc_enabled) {
            return true;
        }

        return $this->hasFeatureAddon('nfc');
    }

    public function canUseAnalytics(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $subscription = $this->activeSubscription()->with('subscriptionPlan')->first();

        if (! $subscription || ! $subscription->isActive()) {
            return false;
        }

        if ($subscription->subscriptionPlan?->analytics_enabled) {
            return true;
        }

        return $this->hasFeatureAddon('analytics');
    }

    public function canUseCustomDomain(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $subscription = $this->activeSubscription()->with('subscriptionPlan')->first();

        if (! $subscription || ! $subscription->isActive()) {
            return false;
        }

        if ($subscription->subscriptionPlan?->custom_domain_allowed) {
            return true;
        }

        return $this->hasFeatureAddon('custom_domain');
    }

    public function canAccessPremiumTemplates(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Premium templates require paid subscription
        $subscription = $this->activeSubscription()->with('subscriptionPlan')->first();

        return $subscription?->subscriptionPlan?->price > 0;
    }

    /**
     * Check if user has a specific feature unlock add-on.
     */
    public function hasFeatureAddon(string $featureKey): bool
    {
        return $this->userAddons()
            ->whereHas('addon', function ($query) use ($featureKey) {
                $query->where('type', 'feature_unlock')
                    ->where('feature_key', $featureKey);
            })
            ->exists();
    }

    /**
     * Get total extra card slots from purchased add-ons.
     */
    public function getExtraCardSlots(): int
    {
        return (int) $this->userAddons()
            ->whereHas('addon', function ($query) {
                $query->where('type', 'extra_cards');
            })
            ->join('addons', 'addons.id', '=', 'user_addons.addon_id')
            ->sum('addons.value');
    }

    /**
     * Initialize translation usage for current period.
     */
    protected function initializeTranslationUsage(): void
    {
        $credits = $this->getTranslationCreditLimit();
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        UserTranslationUsage::create([
            'user_id' => $this->id,
            'credits_available' => $credits,
            'credits_used' => 0,
            'total_translations' => 0,
            'period_start' => $startDate,
            'period_end' => $endDate,
            'is_active' => true,
        ]);
    }
}
