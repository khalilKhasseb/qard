<?php

namespace App\Enums;

/**
 * Analytics event types.
 * Must match: resources/js/types/contracts/enums.ts > EventType
 */
enum EventType: string
{
    case View = 'view';
    case NfcTap = 'nfc_tap';
    case QrScan = 'qr_scan';
    case SocialShare = 'social_share';
    case SectionClick = 'section_click';
    case ContactSave = 'contact_save';
    case LinkClick = 'link_click';

    public function label(): string
    {
        return match ($this) {
            self::View => 'Page View',
            self::NfcTap => 'NFC Tap',
            self::QrScan => 'QR Code Scan',
            self::SocialShare => 'Social Share',
            self::SectionClick => 'Section Click',
            self::ContactSave => 'Contact Save',
            self::LinkClick => 'Link Click',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::View => 'heroicon-o-eye',
            self::NfcTap => 'heroicon-o-device-phone-mobile',
            self::QrScan => 'heroicon-o-qr-code',
            self::SocialShare => 'heroicon-o-share',
            self::SectionClick => 'heroicon-o-cursor-arrow-rays',
            self::ContactSave => 'heroicon-o-user-plus',
            self::LinkClick => 'heroicon-o-link',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
