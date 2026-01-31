/**
 * CardSection Contract
 * Matches: app/Http/Resources/SectionResource.php
 * Database: card_sections table
 */

import type { SectionType } from './enums';

/**
 * Localized content - keyed by language code
 * Example: { "en": "Hello", "ar": "مرحبا" }
 */
export type LocalizedString = Record<string, string>;

/**
 * Localized content object - for complex section content
 * Example: { "en": { "name": "John" }, "ar": { "name": "يوحنا" } }
 */
export type LocalizedContent = Record<string, Record<string, unknown>>;

/**
 * Section metadata - varies by section type
 */
export interface SectionMetadata {
    icon?: string;
    color?: string;
    layout?: string;
    [key: string]: unknown;
}

/**
 * CardSection entity as returned by API
 */
export interface Section {
    id: number;
    business_card_id: number;
    section_type: SectionType;
    title: LocalizedString | null;
    content: LocalizedContent | null;
    image_path: string | null;
    image_url: string | null;
    sort_order: number;
    is_active: boolean;
    metadata: SectionMetadata | null;
    created_at: string;
    updated_at: string;
}

/**
 * Payload for creating a new section
 */
export interface CreateSectionPayload {
    section_type: SectionType;
    title?: LocalizedString;
    content?: LocalizedContent;
    image_path?: string;
    sort_order?: number;
    is_active?: boolean;
    metadata?: SectionMetadata;
}

/**
 * Payload for updating an existing section
 */
export interface UpdateSectionPayload {
    section_type?: SectionType;
    title?: LocalizedString;
    content?: LocalizedContent;
    image_path?: string;
    sort_order?: number;
    is_active?: boolean;
    metadata?: SectionMetadata;
}

/**
 * Payload for reordering sections
 */
export interface ReorderSectionsPayload {
    sections: Array<{
        id: number;
        sort_order: number;
    }>;
}
