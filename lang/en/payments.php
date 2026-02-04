<?php

return [
    'title' => 'Payments & Subscription',
    'checkout' => 'Checkout',
    'confirmation' => 'Payment Confirmation',

    // Subscription
    'subscription' => [
        'title' => 'Current Subscription',
        'my_subscription' => 'My Subscription',
        'no_active' => 'No active subscription',
        'choose_plan' => 'Choose a Plan',
        'cancel' => 'Cancel Subscription',
        'cancel_confirm' => 'Are you sure you want to cancel your subscription? This action cannot be undone.',
        'cancel_success' => 'Your subscription has been canceled. You can continue using your current plan until the end of the billing period.',
        'price' => 'Price',
        'next_billing' => 'Next Billing Date',
        'features' => 'Plan Features:',
        'business_cards' => ':count Business Cards',
        'custom_themes' => ':count Custom Themes',
        'custom_domain' => 'Custom Domain',
        'advanced_analytics' => 'Advanced Analytics',
        'priority_support' => 'Priority Support',
        // Subscription page
        'loading' => 'Loading subscription details...',
        'details' => 'Subscription Details',
        'details_subtitle' => 'Your current plan and billing information',
        'plan' => 'Plan',
        'status' => 'Status',
        'days_remaining' => 'Days Remaining',
        'days' => ':count days',
        'start_date' => 'Start Date',
        'renews_on' => 'Renews On',
        'canceled_on' => 'Canceled On',
        'trial_ends' => 'Trial Ends',
        'usage' => 'Usage',
        'of_used' => ':count of :limit used',
        'remaining' => ':count remaining',
        'included_features' => 'Included Features',
        'no_premium_features' => 'No premium features included in the free plan. Upgrade to unlock advanced features.',
        'no_additional_features' => 'No additional features available in this plan.',
        // Actions
        'actions' => 'Actions',
        'upgrade_plan' => 'Upgrade Plan',
        'renew' => 'Renew Subscription',
        'refresh' => 'Refresh Details',
        'syncing' => 'Syncing...',
        'sync_plan' => 'Sync Plan',
        'view_all_plans' => 'View All Plans',
        'browse_addons' => 'Browse Add-ons',
        'purchased_addons' => 'Purchased Add-ons',
        'addon_extra_cards' => '+:count extra card slots',
        'addon_feature_unlock' => 'Feature unlock',
        'includes_addons' => 'Plan: :plan + Add-ons: :addons extra slots',
        'sync_success' => 'Subscription details refreshed successfully!',
        // Upgrade section
        'upgrade_your_plan' => 'Upgrade Your Plan',
        'upgrade_prompt' => 'You\'re currently using :count out of :limit cards. Upgrade for more space!',
        'current' => 'Current',
        'cards_themes' => ':cards cards, :themes themes',
        'upgrade' => 'Upgrade',
        // No subscription
        'no_subscription_title' => 'No Active Subscription',
        'no_subscription_desc' => 'You don\'t have an active subscription. Upgrade your account to unlock more features and create more cards.',
        'go_to_dashboard' => 'Go to Dashboard',
        // Errors
        'load_error' => 'Failed to load subscription details. Please try again.',
        'sync_error' => 'Failed to refresh subscription details. Please try again.',
    ],

    // Plans
    'plans' => [
        'title' => 'Available Plans',
        'most_popular' => 'Most Popular',
        'select' => 'Select Plan',
        'choose' => 'Choose :name',
        'current' => 'Current Plan',
        'upgrade' => 'Upgrade',
        'downgrade' => 'Downgrade',
        'free' => 'Free',
        'monthly' => '/month',
        'yearly' => '/year',
    ],

    // Features
    'features' => [
        'cards_limit' => ':count Business Cards',
        'unlimited_cards' => 'Unlimited Business Cards',
        'themes' => 'Custom Themes',
        'analytics' => 'Analytics',
        'priority_support' => 'Priority Support',
        'custom_domain' => 'Custom Domain',
        'remove_branding' => 'Remove Branding',
    ],

    // Checkout page
    'checkout_page' => [
        'title' => 'Complete Your Purchase',
        'subtitle' => 'You\'re one step away from upgrading your account',
        'order_summary' => 'Order Summary',
        'plan_name' => ':name Plan',
        'included_features' => 'Included Features:',
        'total' => 'Total',
        'payment_method' => 'Payment Method',
        'card_payment' => 'Credit/Debit Card',
        'card_payment_desc' => 'Pay securely with your card via Lahza',
        'cash_payment' => 'Cash Payment',
        'cash_payment_desc' => 'Contact support for cash payment instructions',
        'terms_agree' => 'I agree to the',
        'terms_of_service' => 'Terms of Service',
        'privacy_policy' => 'Privacy Policy',
        'and' => 'and',
        'continue' => 'Continue',
        'processing' => 'Processing...',
        'secure_checkout' => 'Secure checkout powered by Lahza',
        'complete_payment' => 'Complete Payment',
        'open_payment_form' => 'Open Payment Form',
        'payment_redirect_info' => 'Click the button below to open the secure payment form. Your payment will be processed by Lahza.',
        'redirect_notice' => 'You will be redirected to complete your payment securely',
        'dismiss' => 'Dismiss',
    ],

    // Confirmation page
    'confirmation_page' => [
        'title' => 'Payment Received!',
        'thank_you' => 'Thank you for your purchase',
        'details' => 'Payment Details',
        'order_id' => 'Order ID:',
        'plan' => 'Plan:',
        'amount' => 'Amount:',
        'payment_method' => 'Payment Method:',
        'status' => 'Status:',
        'date' => 'Date:',
        'whats_next' => 'What\'s Next?',
        'processing_info' => 'Your payment is being processed and will be confirmed shortly',
        'email_confirmation' => 'You\'ll receive a confirmation email with your receipt',
        'upgrade_info' => 'Your account will be upgraded once payment is confirmed',
        'support_info' => 'Contact support if you have any questions',
        'go_to_dashboard' => 'Go to Dashboard',
        'view_subscription' => 'View Subscription',
        'need_help' => 'Need help?',
        'contact_support' => 'Contact Support',
    ],

    // Checkout form
    'checkout_form' => [
        'card_number' => 'Card Number',
        'expiry' => 'Expiry Date',
        'cvv' => 'CVV',
        'name_on_card' => 'Name on Card',
        'billing_address' => 'Billing Address',
        'pay_now' => 'Pay Now',
        'processing' => 'Processing...',
    ],

    // Messages
    'messages' => [
        'success' => 'Payment successful! Your subscription is now active.',
        'failed' => 'Payment failed. Please try again.',
        'cancelled' => 'Payment was cancelled.',
        'pending' => 'Payment is pending.',
    ],

    // History
    'history' => [
        'title' => 'Payment History',
        'date' => 'Date',
        'description' => 'Description',
        'amount' => 'Amount',
        'method' => 'Method',
        'status' => 'Status',
        'invoice' => 'Invoice',
        'no_history' => 'No payment history yet',
    ],

    // Add-ons section on payments page
    'addons' => [
        'title' => 'Purchased Add-ons',
        'browse' => 'Browse Add-ons',
        'store_title' => 'Add-ons Store',
        'store_desc' => 'Enhance your account with extra card slots and feature unlocks.',
        'via_purchase' => 'Purchased',
        'via_admin' => 'Granted by admin',
        'via_promo' => 'Promotional',
    ],

    // Payment modal
    'modal' => [
        'title' => 'Complete Payment',
        'selected_plan' => 'You\'ve selected the :name plan for $:price/:cycle.',
        'cash_info' => 'For cash payment, please contact our support team with your payment details.',
        'confirm' => 'Confirm Payment',
    ],
];
