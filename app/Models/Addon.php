<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Addon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'feature_key',
        'value',
        'price',
        'currency',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'value' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function userAddons(): HasMany
    {
        return $this->hasMany(UserAddon::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeExtraCards(Builder $query): Builder
    {
        return $query->where('type', 'extra_cards');
    }

    public function scopeFeatureUnlocks(Builder $query): Builder
    {
        return $query->where('type', 'feature_unlock');
    }

    public function isExtraCards(): bool
    {
        return $this->type === 'extra_cards';
    }

    public function isFeatureUnlock(): bool
    {
        return $this->type === 'feature_unlock';
    }
}
