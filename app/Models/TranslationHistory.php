<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TranslationHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'translation_history';

    protected $fillable = [
        'user_id',
        'business_card_id',
        'translatable_type',
        'translatable_id',
        'source_language',
        'target_language',
        'source_text',
        'translated_text',
        'translation_method',
        'provider',
        'model',
        'quality_score',
        'verification_status',
        'verified_by',
        'verified_at',
        'character_count',
        'credits_used',
        'cost',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'quality_score' => 'integer',
            'character_count' => 'integer',
            'credits_used' => 'integer',
            'cost' => 'decimal:6',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user who created the translation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the business card associated with the translation.
     */
    public function businessCard(): BelongsTo
    {
        return $this->belongsTo(BusinessCard::class);
    }

    /**
     * Get the user who verified the translation.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the translatable entity (CardSection, etc.).
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter pending verifications.
     */
    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    /**
     * Scope to filter auto-verified translations.
     */
    public function scopeAutoVerified($query)
    {
        return $query->where('verification_status', 'auto_verified');
    }

    /**
     * Scope to filter approved translations.
     */
    public function scopeApproved($query)
    {
        return $query->where('verification_status', 'approved');
    }

    /**
     * Scope to filter translations needing review.
     */
    public function scopeNeedsReview($query)
    {
        return $query->where('verification_status', 'needs_review');
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by business card.
     */
    public function scopeForCard($query, int $cardId)
    {
        return $query->where('business_card_id', $cardId);
    }

    /**
     * Scope to filter by language pair.
     */
    public function scopeForLanguagePair($query, string $sourceLang, string $targetLang)
    {
        return $query->where('source_language', $sourceLang)
                    ->where('target_language', $targetLang);
    }

    /**
     * Mark translation as verified.
     */
    public function markAsVerified(int $verifierId, string $status = 'approved'): void
    {
        $this->update([
            'verification_status' => $status,
            'verified_by' => $verifierId,
            'verified_at' => now(),
        ]);
    }

    /**
     * Calculate quality score based on character count and other factors.
     */
    public function calculateQualityScore(?int $score = null): void
    {
        if ($score !== null) {
            $this->update(['quality_score' => $score]);
            
            // Auto-set verification status based on score
            if ($score >= 80) {
                $this->update(['verification_status' => 'auto_verified']);
            } elseif ($score < 60) {
                $this->update(['verification_status' => 'needs_review']);
            }
        }
    }

    /**
     * Get cost in dollars.
     */
    public function getCostInDollarsAttribute(): string
    {
        return number_format((float) $this->cost, 6);
    }
}
