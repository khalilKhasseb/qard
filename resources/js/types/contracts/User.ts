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
 * User usage stats
 */
export interface UserUsage {
    cardCount: number;
    themeCount: number;
    cardLimit: number;
    themeLimit: number;
    canCreateCard: boolean;
    canCreateTheme: boolean;
    canUseCustomCss: boolean;
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
