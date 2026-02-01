/**
 * User Contract
 * Database: users table
 */

import type { SubscriptionStatus } from './enums';

/**
 * User entity (authenticated user context)
 */
export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    is_admin: boolean;
    language: string;
    subscription_tier: string;
    subscription_status: SubscriptionStatus | null;
    subscription_expires_at: string | null;
    last_login: string | null;
    created_at: string;
    updated_at: string;
}

/**
 * User capabilities (shared via Inertia auth.capabilities)
 */
export interface UserCapabilities {
    can_create_card: boolean;
    can_create_theme: boolean;
    can_use_custom_css: boolean;
    can_use_nfc: boolean;
    can_use_analytics: boolean;
    can_use_custom_domain: boolean;
    can_access_premium_templates: boolean;
    card_limit: number;
    theme_limit: number;
}

/**
 * User usage stats (from API /usage endpoint)
 */
export interface UserUsage {
    cards: {
        used: number;
        limit: number;
        can_create: boolean;
    };
    themes: {
        used: number;
        limit: number;
        can_create: boolean;
    };
    features: {
        custom_css: boolean;
        nfc: boolean;
        analytics: boolean;
        custom_domain: boolean;
        premium_templates: boolean;
    };
    subscription: {
        plan_name: string;
        status: string;
        expires_at: string | null;
    };
}

/**
 * Translation credits info
 */
export interface TranslationCredits {
    remaining: number;
    total: number;
    used: number;
    is_unlimited: boolean;
    period_start: string;
    period_end: string;
}

/**
 * User profile update payload
 */
export interface UpdateProfilePayload {
    name?: string;
    email?: string;
    language?: string;
}

/**
 * User password update payload
 */
export interface UpdatePasswordPayload {
    current_password: string;
    password: string;
    password_confirmation: string;
}
