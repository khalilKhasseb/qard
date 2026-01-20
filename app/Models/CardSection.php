<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_card_id',
        'section_type',
        'title',
        'content',
        'image_path',
        'sort_order',
        'is_active',
        'metadata',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        return \Illuminate\Support\Facades\Storage::url($this->image_path);
    }

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public const SECTION_TYPES = [
        'contact' => 'Contact Information',
        'social' => 'Social Media Links',
        'services' => 'Services',
        'products' => 'Products',
        'testimonials' => 'Testimonials',
        'hours' => 'Business Hours',
        'appointments' => 'Appointments',
        'gallery' => 'Image Gallery',
        'video' => 'Video',
        'links' => 'Links',
        'about' => 'About',
        'custom' => 'Custom Content',
        'text' => 'Text',
        'image' => 'Image',
        'link' => 'Link',
        'qr_code' => 'QR Code',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(BusinessCard::class, 'business_card_id');
    }

    public function businessCard(): BelongsTo
    {
        return $this->belongsTo(BusinessCard::class, 'business_card_id');
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('section_type', $type);
    }

    public function getContentValue(string $key, $default = null)
    {
        return data_get($this->content, $key, $default);
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function moveUp(): void
    {
        if ($this->sort_order > 0) {
            $this->decrement('sort_order');
        }
    }

    public function moveDown(): void
    {
        $this->increment('sort_order');
    }
}
