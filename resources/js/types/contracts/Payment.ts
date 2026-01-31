/**
 * Payment & Subscription Contracts
 * Matches: app/Http/Resources/PaymentResource.php
 *          app/Http/Resources/SubscriptionPlanResource.php
 *          app/Http/Resources/UserSubscriptionResource.php
 * Database: payments, subscription_plans, user_subscriptions tables
 */

import type { PaymentStatus, SubscriptionStatus, BillingCycle } from './enums';

/**
 * Subscription Plan entity
 * Field names match database columns exactly
 */
export interface SubscriptionPlan {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price: string; // decimal as string
    currency: string;
    billing_cycle: BillingCycle;
    trial_days: number;
    cards_limit: number;
    themes_limit: number;
    custom_css_allowed: boolean;
    analytics_enabled: boolean;
    nfc_enabled: boolean;
    custom_domain_allowed: boolean;
    translation_credits_monthly: number;
    unlimited_translations: boolean;
    features: string[] | null;
    is_active: boolean;
    is_popular: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
}

/**
 * User Subscription entity
 */
export interface UserSubscription {
    id: number;
    user_id: number;
    subscription_plan_id: number;
    status: SubscriptionStatus;
    starts_at: string;
    ends_at: string | null;
    trial_ends_at: string | null;
    canceled_at: string | null;
    auto_renew: boolean;
    plan: SubscriptionPlan | null;
    // Computed fields
    is_active: boolean;
    is_trial: boolean;
    days_remaining: number | null;
    created_at: string;
    updated_at: string;
}

/**
 * Payment entity
 */
export interface Payment {
    id: number;
    user_id: number;
    subscription_plan_id: number;
    amount: string; // decimal as string
    currency: string;
    status: PaymentStatus;
    payment_method: string;
    transaction_id: string;
    gateway_reference: string | null;
    receipt_url: string | null;
    notes: string | null;
    metadata: Record<string, unknown> | null;
    paid_at: string | null;
    subscription_plan: SubscriptionPlan | null;
    created_at: string;
    updated_at: string;
}

/**
 * Payment initialization payload
 */
export interface InitializePaymentPayload {
    plan_id: number;
    payment_method?: string;
}

/**
 * Payment confirmation payload
 */
export interface ConfirmPaymentPayload {
    transaction_id: string;
    gateway_reference?: string;
}

/**
 * Payment history response
 */
export interface PaymentHistory {
    data: Payment[];
    total: number;
}
