/**
 * Language Contract
 * Matches: app/Http/Resources/LanguageResource.php
 * Database: languages table
 */

import type { TextDirection } from './enums';

/**
 * UI Labels for a language
 */
export interface LanguageLabels {
    // Common UI labels
    save?: string;
    cancel?: string;
    delete?: string;
    edit?: string;
    create?: string;
    back?: string;
    next?: string;
    previous?: string;
    loading?: string;
    // Card-specific labels
    contact?: string;
    services?: string;
    products?: string;
    testimonials?: string;
    gallery?: string;
    hours?: string;
    about?: string;
    // Allow any additional labels
    [key: string]: string | undefined;
}

/**
 * Language entity as returned by API
 */
export interface Language {
    id: number;
    name: string;
    code: string; // ISO 639-1 (2-char)
    direction: TextDirection;
    is_active: boolean;
    is_default: boolean;
    labels: LanguageLabels; // Always an object (never null)
    created_at: string;
    updated_at: string;
}

/**
 * Language switch payload
 */
export interface SwitchLanguagePayload {
    code: string;
}

/**
 * Available languages list
 */
export interface AvailableLanguages {
    languages: Language[];
    current: string;
    default: string;
}
