<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'language',
        'subscription_tier',
        'subscription_status',
        'subscription_expires_at',
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
        $limit = $this->getCardLimit();

        return $this->cards()->count() < $limit;
    }

    public function canCreateTheme(): bool
    {
        $limit = $this->getThemeLimit();

        return $this->themes()->count() < $limit;
    }

    public function canUseCustomCss(): bool
    {
        $subscription = $this->activeSubscription()->with('subscriptionPlan')->first();

        if ($subscription && $subscription->subscriptionPlan) {
            return $subscription->subscriptionPlan->custom_css_allowed ?? false;
        }

        return false;
    }

    public function getCardLimit(): int
    {
        $subscription = $this->activeSubscription()->with('subscriptionPlan')->first();

        if ($subscription && $subscription->subscriptionPlan) {
            return $subscription->subscriptionPlan->cards_limit ?? 1;
        }

        // Free tier default (no subscription)
        return 1;
    }

    public function getThemeLimit(): int
    {
        $subscription = $this->activeSubscription()->with('subscriptionPlan')->first();

        if ($subscription && $subscription->subscriptionPlan) {
            return $subscription->subscriptionPlan->themes_limit ?? 1;
        }

        // Free tier default (no subscription)
        return 1;
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
}
