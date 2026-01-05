<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_card_id',
        'card_section_id',
        'event_type',
        'referrer',
        'user_agent',
        'ip_address',
        'country',
        'city',
        'device_type',
        'browser',
        'os',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public const EVENT_TYPES = [
        'view' => 'Page View',
        'nfc_tap' => 'NFC Tap',
        'qr_scan' => 'QR Code Scan',
        'social_share' => 'Social Share',
        'section_click' => 'Section Click',
        'contact_save' => 'Contact Save',
        'link_click' => 'Link Click',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(BusinessCard::class, 'business_card_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CardSection::class, 'card_section_id');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeForCard($query, int $cardId)
    {
        return $query->where('business_card_id', $cardId);
    }

    public function scopeInPeriod($query, string $period)
    {
        return match ($period) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->where('created_at', '>=', now()->subWeek()),
            'month' => $query->where('created_at', '>=', now()->subMonth()),
            'year' => $query->where('created_at', '>=', now()->subYear()),
            default => $query,
        };
    }

    public static function track(
        int $cardId,
        string $eventType,
        ?int $sectionId = null,
        array $data = []
    ): self {
        return self::create([
            'business_card_id' => $cardId,
            'card_section_id' => $sectionId,
            'event_type' => $eventType,
            'referrer' => $data['referrer'] ?? request()->headers->get('referer'),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'device_type' => $data['device_type'] ?? null,
            'browser' => $data['browser'] ?? null,
            'os' => $data['os'] ?? null,
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }
}
