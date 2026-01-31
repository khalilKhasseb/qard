<?php

namespace App\Enums;

/**
 * Card section types.
 * Must match: resources/js/types/contracts/enums.ts > SectionType
 */
enum SectionType: string
{
    case Contact = 'contact';
    case Social = 'social';
    case Services = 'services';
    case Products = 'products';
    case Testimonials = 'testimonials';
    case Hours = 'hours';
    case Appointments = 'appointments';
    case Gallery = 'gallery';
    case Video = 'video';
    case Links = 'links';
    case About = 'about';
    case Custom = 'custom';
    case Text = 'text';
    case Image = 'image';
    case Link = 'link';
    case QrCode = 'qr_code';

    public function label(): string
    {
        return match ($this) {
            self::Contact => 'Contact Information',
            self::Social => 'Social Media Links',
            self::Services => 'Services',
            self::Products => 'Products',
            self::Testimonials => 'Testimonials',
            self::Hours => 'Business Hours',
            self::Appointments => 'Appointments',
            self::Gallery => 'Image Gallery',
            self::Video => 'Video',
            self::Links => 'Links',
            self::About => 'About',
            self::Custom => 'Custom Content',
            self::Text => 'Text',
            self::Image => 'Image',
            self::Link => 'Link',
            self::QrCode => 'QR Code',
        };
    }

    /**
     * Get all section types as options array for forms.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }

    /**
     * Get all section type values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
