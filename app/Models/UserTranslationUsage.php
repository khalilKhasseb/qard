<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTranslationUsage extends Model
{
    use HasFactory;

    protected $table = 'user_translation_usage';

    protected $fillable = [
        'user_id',
        'credits_available',
        'credits_used',
        'total_translations',
        'period_start',
        'period_end',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credits_available' => 'integer',
            'credits_used' => 'integer',
            'total_translations' => 'integer',
            'period_start' => 'date',
            'period_end' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user this usage record belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter active periods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter current period.
     */
    public function scopeCurrentPeriod($query)
    {
        $now = now();
        return $query->where('period_start', '<=', $now)
                    ->where('period_end', '>=', $now)
                    ->where('is_active', true);
    }

    /**
     * Check if user has enough credits.
     */
    public function hasCredits(int $required = 1): bool
    {
        return $this->getRemainingCredits() >= $required;
    }

    /**
     * Get remaining credits.
     */
    public function getRemainingCredits(): int
    {
        return max(0, $this->credits_available - $this->credits_used);
    }

    /**
     * Deduct credits.
     */
    public function deductCredits(int $amount = 1): bool
    {
        if (!$this->hasCredits($amount)) {
            return false;
        }

        $this->increment('credits_used', $amount);
        $this->increment('total_translations');

        return true;
    }

    /**
     * Reset credits for new period.
     */
    public function resetForNewPeriod(int $newCredits, \DateTime $startDate, \DateTime $endDate): void
    {
        $this->update([
            'credits_available' => $newCredits,
            'credits_used' => 0,
            'period_start' => $startDate,
            'period_end' => $endDate,
            'is_active' => true,
        ]);
    }

    /**
     * Get usage percentage.
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->credits_available === 0) {
            return 0;
        }

        return round(($this->credits_used / $this->credits_available) * 100, 2);
    }

    /**
     * Check if period has expired.
     */
    public function isExpired(): bool
    {
        return now()->isAfter($this->period_end);
    }

    /**
     * Mark as expired.
     */
    public function markAsExpired(): void
    {
        $this->update(['is_active' => false]);
    }
}
