<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BusinessCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'language_id',
        'title',
        'subtitle',
        'cover_image_path',
        'profile_image_path',
        'template_id',
        'theme_id',
        'theme_overrides',
        'draft_data',
        'custom_slug',
        'share_url',
        'qr_code_url',
        'nfc_identifier',
        'is_published',
        'is_primary',
        'views_count',
        'shares_count',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'subtitle' => 'array',
            'theme_overrides' => 'array',
            'draft_data' => 'array',
            'is_published' => 'boolean',
            'is_primary' => 'boolean',
            'views_count' => 'integer',
            'shares_count' => 'integer',
        ];
    }

    protected $appends = ['full_url', 'cover_image_url', 'profile_image_url'];

    public function getCoverImageUrlAttribute(): ?string
    {
        if (! $this->cover_image_path) {
            return null;
        }

        if (str_starts_with($this->cover_image_path, 'http')) {
            return $this->cover_image_path;
        }

        return \Illuminate\Support\Facades\Storage::url($this->cover_image_path);
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        if (! $this->profile_image_path) {
            return null;
        }

        if (str_starts_with($this->profile_image_path, 'http')) {
            return $this->profile_image_path;
        }

        return \Illuminate\Support\Facades\Storage::url($this->profile_image_path);
    }

    protected static function booted(): void
    {
        static::creating(function (BusinessCard $card) {
            if (empty($card->share_url)) {
                $card->share_url = Str::random(10);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CardSection::class)->orderBy('sort_order');
    }

    public function activeSections(): HasMany
    {
        return $this->sections()->where('is_active', true);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function getFullUrlAttribute(): string
    {
        if ($this->custom_slug) {
            return url("/u/{$this->custom_slug}");
        }

        return url("/c/{$this->share_url}");
    }

    public function getEffectiveThemeConfig(): array
    {
        $baseConfig = $this->theme?->config ?? Theme::getDefaultConfig();

        if (! empty($this->theme_overrides)) {
            return array_replace_recursive($baseConfig, $this->theme_overrides);
        }

        return $baseConfig;
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function incrementShares(): void
    {
        $this->increment('shares_count');
    }

    public function publish(): void
    {
        $this->update(['is_published' => true]);
    }

    public function unpublish(): void
    {
        $this->update(['is_published' => false]);
    }

    public function makePrimary(): void
    {
        $this->user->cards()->where('id', '!=', $this->id)->update(['is_primary' => false]);
        $this->update(['is_primary' => true]);
    }
}
