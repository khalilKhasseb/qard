/**
 * Theme Contract
 * Matches: app/Http/Resources/ThemeResource.php
 * Database: themes table
 */

/**
 * Theme color configuration
 */
export interface ThemeColors {
    primary: string;
    secondary: string;
    background: string;
    text: string;
    card_bg: string;
    border: string;
}

/**
 * Theme font configuration
 */
export interface ThemeFonts {
    heading: string;
    body: string;
    heading_url: string | null;
    body_url: string | null;
}

/**
 * Theme images configuration
 */
export interface ThemeImages {
    background: string | null;
    header: string | null;
    logo: string | null;
}

/**
 * Theme layout configuration
 */
export interface ThemeLayout {
    card_style: 'elevated' | 'flat' | 'bordered';
    border_radius: string;
    alignment: 'left' | 'center' | 'right';
    spacing: 'compact' | 'normal' | 'relaxed';
}

/**
 * Complete theme configuration object
 */
export interface ThemeConfig {
    colors: ThemeColors;
    fonts: ThemeFonts;
    images: ThemeImages;
    layout: ThemeLayout;
    custom_css: string;
}

/**
 * Embedded user info in theme response
 * Note: `name` is only present when user relationship is loaded
 */
export interface ThemeUser {
    id: number;
    name?: string;
}

/**
 * Theme entity as returned by API
 */
export interface Theme {
    id: number;
    user_id: number | null;
    name: string;
    is_system_default: boolean;
    is_public: boolean;
    config: ThemeConfig;
    preview_image: string | null;
    used_by_cards_count: number;
    user: ThemeUser | null;
    created_at: string;
    updated_at: string;
}

/**
 * Payload for creating a new theme
 */
export interface CreateThemePayload {
    name: string;
    config?: Partial<ThemeConfig>;
    is_public?: boolean;
}

/**
 * Payload for updating an existing theme
 */
export interface UpdateThemePayload {
    name?: string;
    config?: Partial<ThemeConfig>;
    is_public?: boolean;
}
