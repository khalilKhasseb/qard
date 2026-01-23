<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'is_system_default',
        'is_public',
        'config',
        'preview_image',
        'used_by_cards_count',
    ];

    protected function casts(): array
    {
        return [
            'is_system_default' => 'boolean',
            'is_public' => 'boolean',
            'config' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(BusinessCard::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ThemeImage::class);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system_default', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('is_system_default', true)
                ->orWhere('is_public', true);
        });
    }

    public static function getDefaultConfig(): array
    {
        return [
            'colors' => [
                'primary' => '#2563eb',
                'secondary' => '#1e40af',
                'background' => '#ffffff',
                'text' => '#1f2937',
                'card_bg' => '#f9fafb',
                'border' => '#e5e7eb',
            ],
            'fonts' => [
                'heading' => 'Inter',
                'body' => 'Inter',
                'heading_url' => null,
                'body_url' => null,
            ],
            'images' => [
                'background' => null,
                'header' => null,
                'logo' => null,
            ],
            'layout' => [
                'card_style' => 'elevated',
                'border_radius' => '12px',
                'alignment' => 'center',
                'spacing' => 'normal',
            ],
            'custom_css' => '',
        ];
    }

    public function getConfigValue(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    public function incrementUsage(): void
    {
        $this->increment('used_by_cards_count');
    }

    public function decrementUsage(): void
    {
        if ($this->used_by_cards_count > 0) {
            $this->decrement('used_by_cards_count');
        }
    }
}
