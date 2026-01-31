/**
 * Analytics Contract
 * Database: analytics_events table
 */

import type { EventType } from './enums';

/**
 * Analytics event entity
 */
export interface AnalyticsEvent {
    id: number;
    business_card_id: number;
    card_section_id: number | null;
    event_type: EventType;
    referrer: string | null;
    user_agent: string | null;
    ip_address: string | null;
    country: string | null;
    city: string | null;
    device_type: string | null;
    browser: string | null;
    os: string | null;
    metadata: Record<string, unknown> | null;
    created_at: string;
}

/**
 * Track event payload (public endpoint)
 */
export interface TrackEventPayload {
    card_id: number;
    event_type: EventType;
    section_id?: number;
    metadata?: Record<string, unknown>;
}

/**
 * Analytics summary for a card
 */
export interface CardAnalyticsSummary {
    period: 'today' | 'week' | 'month' | 'year' | 'all';
    total_views: number;
    total_shares: number;
    unique_visitors: number;
    events_by_type: Record<EventType, number>;
    views_over_time: Array<{
        date: string;
        count: number;
    }>;
    top_countries: Array<{
        country: string;
        count: number;
    }>;
    top_devices: Array<{
        device: string;
        count: number;
    }>;
    top_browsers: Array<{
        browser: string;
        count: number;
    }>;
    section_clicks: Array<{
        section_id: number;
        section_type: string;
        clicks: number;
    }>;
}

/**
 * Analytics filter options
 */
export interface AnalyticsFilter {
    card_id?: number;
    period?: 'today' | 'week' | 'month' | 'year' | 'all';
    event_type?: EventType;
    start_date?: string;
    end_date?: string;
}
