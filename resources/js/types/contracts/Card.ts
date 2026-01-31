/**
 * BusinessCard Contract
 * Matches: app/Http/Resources/CardResource.php
 * Database: business_cards table
 */

import type { Section, LocalizedString } from './Section';
import type { Theme, ThemeConfig } from './Theme';

/**
 * Embedded user info in card response
 * Note: `name` is only present when user relationship is loaded
 */
export interface CardUser {
    id: number;
    name?: string;
}

/**
 * BusinessCard entity as returned by API
 */
export interface Card {
    id: number;
    user_id: number;
    language_id: number | null;
    title: LocalizedString;
    subtitle: LocalizedString | null;
    cover_image_path: string | null;
    cover_image_url: string | null;
    profile_image_path: string | null;
    profile_image_url: string | null;
    template_id: number | null;
    theme_id: number | null;
    theme_overrides: Partial<ThemeConfig> | null;
    active_languages: string[];
    draft_data: CardDraftData | null;
    custom_slug: string | null;
    share_url: string;
    qr_code_url: string | null;
    nfc_identifier: string | null;
    is_published: boolean;
    is_primary: boolean;
    views_count: number;
    shares_count: number;
    full_url: string;
    sections: Section[];
    theme: Theme | null;
    user: CardUser;
    created_at: string;
    updated_at: string;
}

/**
 * Draft data structure for unpublished changes
 */
export interface CardDraftData {
    title?: LocalizedString;
    subtitle?: LocalizedString;
    sections?: Section[];
    theme_overrides?: Partial<ThemeConfig>;
    [key: string]: unknown;
}

/**
 * Card list item (minimal data for listings)
 */
export interface CardListItem {
    id: number;
    title: LocalizedString;
    subtitle: LocalizedString | null;
    cover_image_url: string | null;
    profile_image_url: string | null;
    is_published: boolean;
    is_primary: boolean;
    views_count: number;
    shares_count: number;
    full_url: string;
    created_at: string;
    updated_at: string;
}

/**
 * Payload for creating a new card
 */
export interface CreateCardPayload {
    title: LocalizedString;
    subtitle?: LocalizedString;
    language_id?: number;
    theme_id?: number;
    template_id?: number;
    custom_slug?: string;
    active_languages?: string[];
}

/**
 * Payload for updating an existing card
 */
export interface UpdateCardPayload {
    title?: LocalizedString;
    subtitle?: LocalizedString;
    language_id?: number;
    theme_id?: number;
    theme_overrides?: Partial<ThemeConfig>;
    custom_slug?: string;
    active_languages?: string[];
    is_published?: boolean;
    is_primary?: boolean;
}

/**
 * Card analytics summary
 */
export interface CardAnalytics {
    total_views: number;
    total_shares: number;
    views_by_day: Array<{ date: string; count: number }>;
    views_by_device: Record<string, number>;
    views_by_country: Record<string, number>;
    top_sections: Array<{ section_id: number; clicks: number }>;
}
