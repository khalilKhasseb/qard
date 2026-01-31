/**
 * Shared Enums - Single source of truth for all constant values
 * These must match the PHP enums in app/Enums/
 */

export const SectionType = {
    Contact: 'contact',
    Social: 'social',
    Services: 'services',
    Products: 'products',
    Testimonials: 'testimonials',
    Hours: 'hours',
    Appointments: 'appointments',
    Gallery: 'gallery',
    Video: 'video',
    Links: 'links',
    About: 'about',
    Custom: 'custom',
    Text: 'text',
    Image: 'image',
    Link: 'link',
    QrCode: 'qr_code',
} as const;

export type SectionType = (typeof SectionType)[keyof typeof SectionType];

export const SectionTypeLabels: Record<SectionType, string> = {
    [SectionType.Contact]: 'Contact Information',
    [SectionType.Social]: 'Social Media Links',
    [SectionType.Services]: 'Services',
    [SectionType.Products]: 'Products',
    [SectionType.Testimonials]: 'Testimonials',
    [SectionType.Hours]: 'Business Hours',
    [SectionType.Appointments]: 'Appointments',
    [SectionType.Gallery]: 'Image Gallery',
    [SectionType.Video]: 'Video',
    [SectionType.Links]: 'Links',
    [SectionType.About]: 'About',
    [SectionType.Custom]: 'Custom Content',
    [SectionType.Text]: 'Text',
    [SectionType.Image]: 'Image',
    [SectionType.Link]: 'Link',
    [SectionType.QrCode]: 'QR Code',
};

export const PaymentStatus = {
    Pending: 'pending',
    Completed: 'completed',
    Failed: 'failed',
    Refunded: 'refunded',
} as const;

export type PaymentStatus = (typeof PaymentStatus)[keyof typeof PaymentStatus];

export const SubscriptionStatus = {
    Active: 'active',
    Canceled: 'canceled',
    Expired: 'expired',
    Trial: 'trial',
} as const;

export type SubscriptionStatus = (typeof SubscriptionStatus)[keyof typeof SubscriptionStatus];

export const EventType = {
    View: 'view',
    NfcTap: 'nfc_tap',
    QrScan: 'qr_scan',
    SocialShare: 'social_share',
    SectionClick: 'section_click',
    ContactSave: 'contact_save',
    LinkClick: 'link_click',
} as const;

export type EventType = (typeof EventType)[keyof typeof EventType];

export const EventTypeLabels: Record<EventType, string> = {
    [EventType.View]: 'Page View',
    [EventType.NfcTap]: 'NFC Tap',
    [EventType.QrScan]: 'QR Code Scan',
    [EventType.SocialShare]: 'Social Share',
    [EventType.SectionClick]: 'Section Click',
    [EventType.ContactSave]: 'Contact Save',
    [EventType.LinkClick]: 'Link Click',
};

export const BillingCycle = {
    Monthly: 'monthly',
    Yearly: 'yearly',
    Lifetime: 'lifetime',
} as const;

export type BillingCycle = (typeof BillingCycle)[keyof typeof BillingCycle];

export const TextDirection = {
    Ltr: 'ltr',
    Rtl: 'rtl',
} as const;

export type TextDirection = (typeof TextDirection)[keyof typeof TextDirection];

export const TranslationVerificationStatus = {
    Pending: 'pending',
    AutoVerified: 'auto_verified',
    Approved: 'approved',
    NeedsReview: 'needs_review',
} as const;

export type TranslationVerificationStatus =
    (typeof TranslationVerificationStatus)[keyof typeof TranslationVerificationStatus];
