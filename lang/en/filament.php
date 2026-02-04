<?php

return [
    // Navigation Groups
    'navigation' => [
        'groups' => [
            'user_management' => 'User Management',
            'finance' => 'Finance',
            'cards' => 'Cards',
            'system_management' => 'System Management',
            'management' => 'Management',
            'settings' => 'Settings',
        ],
    ],

    // Common Labels
    'common' => [
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'password' => 'Password',
        'status' => 'Status',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'actions' => 'Actions',
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'create' => 'Create',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'confirm' => 'Confirm',
        'search' => 'Search',
        'filter' => 'Filter',
        'all' => 'All',
        'yes' => 'Yes',
        'no' => 'No',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
        'pending' => 'Pending',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'user' => 'User',
        'date' => 'Date',
        'amount' => 'Amount',
        'description' => 'Description',
        'notes' => 'Notes',
        'type' => 'Type',
        'key' => 'Key',
        'value' => 'Value',
        'verified' => 'Verified',
        'password' => 'Password',
    ],

    // User Resource
    'users' => [
        'label' => 'User',
        'plural' => 'Users',
        'navigation_label' => 'Users',

        'sections' => [
            'user_information' => 'User Information',
            'subscription' => 'Subscription',
            'preferences' => 'Preferences',
        ],

        'fields' => [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'is_admin' => 'Administrator',
            'admin_helper' => 'Administrators have full access to the admin panel',
            'email_verified_at' => 'Email Verified At',
            'email_verified_helper' => 'Set this to manually verify the user\'s email',
            'subscription_tier' => 'Subscription Tier',
            'subscription_status' => 'Subscription Status',
            'subscription_expires_at' => 'Subscription Expires At',
            'language' => 'Language',
            'cards_count' => 'Cards',
            'last_login' => 'Last Login',
        ],

        'tiers' => [
            'free' => 'Free',
            'pro' => 'Pro',
            'business' => 'Business',
        ],

        'statuses' => [
            'pending' => 'Pending',
            'active' => 'Active',
            'canceled' => 'Canceled',
            'expired' => 'Expired',
        ],

        'filters' => [
            'unverified' => 'Unverified Users',
            'verified' => 'Verified Users',
        ],

        'actions' => [
            'verify' => 'Verify Email',
            'unverify' => 'Unverify Email',
            'verify_selected' => 'Verify Selected',
            'grant_addon' => 'Grant Add-On',
        ],

        'notifications' => [
            'verified' => 'User verified successfully',
            'verified_body' => 'Email for :name has been verified.',
            'unverified' => 'User unverified',
            'unverified_body' => 'Email verification for :name has been removed.',
            'bulk_verified' => 'Users verified',
            'bulk_verified_body' => ':count users have been verified.',
            'addon_granted' => 'Add-on granted successfully',
            'addon_granted_body' => ':addon has been granted to :user.',
        ],
    ],

    // Payment Resource
    'payments' => [
        'label' => 'Payment',
        'plural' => 'Payments',
        'navigation_label' => 'Payments',

        'sections' => [
            'payment_details' => 'Payment Details',
            'payment_method' => 'Payment Method',
            'transaction_information' => 'Transaction Information',
        ],

        'fields' => [
            'transaction_id' => 'Transaction ID',
            'user' => 'User',
            'plan' => 'Plan',
            'amount' => 'Amount',
            'currency' => 'Currency',
            'payment_method' => 'Payment Method',
            'status' => 'Status',
            'gateway_reference' => 'Lahza Reference',
            'notes' => 'Notes',
            'paid_at' => 'Paid At',
            'metadata' => 'Metadata',
            'not_paid' => 'Not paid',
        ],

        'methods' => [
            'cash' => 'Cash',
            'lahza' => 'Lahza Gateway',
            'card' => 'Card',
            'bank_transfer' => 'Bank Transfer',
        ],

        'statuses' => [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ],

        'actions' => [
            'verify' => 'Verify',
            'verify_with_lahza' => 'Verify with Lahza',
            'verify_heading' => 'Verify Payment with Lahza',
            'verify_description' => 'This will verify the payment status with Lahza and activate the subscription if successful.',
            'confirm' => 'Confirm',
            'confirm_heading' => 'Confirm Payment',
            'confirm_description' => 'Are you sure you want to confirm this payment? This will activate the user\'s subscription.',
            'refund' => 'Refund',
            'refund_heading' => 'Refund Payment',
            'refund_description' => 'This will refund the payment and cancel the user\'s subscription.',
            'refund_amount' => 'Refund Amount',
            'refund_reason' => 'Reason for Refund',
        ],

        'notifications' => [
            'verified' => 'Payment verified and subscription activated',
            'verification_failed' => 'Verification failed',
            'confirmed' => 'Payment confirmed',
            'refunded' => 'Payment refunded',
            'refund_failed' => 'Refund failed',
        ],
    ],

    // Subscription Plan Resource
    'subscription_plans' => [
        'label' => 'Subscription Plan',
        'plural' => 'Subscription Plans',
        'navigation_label' => 'Subscription Plans',

        'sections' => [
            'plan_details' => 'Plan Details',
            'pricing' => 'Pricing',
            'limits' => 'Limits',
            'ai_translation' => 'AI Translation',
            'features' => 'Features',
            'additional_features' => 'Additional Features',
        ],

        'fields' => [
            'name' => 'Name',
            'slug' => 'Slug',
            'description' => 'Description',
            'price' => 'Price',
            'billing_cycle' => 'Billing Cycle',
            'cards_limit' => 'Cards Limit',
            'themes_limit' => 'Themes Limit',
            'translation_credits_monthly' => 'Monthly Translation Credits',
            'translation_credits_helper' => 'Number of free translations per month.',
            'unlimited_translations' => 'Unlimited Translations',
            'per_credit_cost' => 'Cost Per Credit',
            'per_credit_helper' => 'Cost for translations after using free credits.',
            'custom_css_allowed' => 'Custom CSS Allowed',
            'analytics_enabled' => 'Analytics Enabled',
            'nfc_enabled' => 'NFC Support',
            'custom_domain_allowed' => 'Custom Domain Allowed',
            'is_active' => 'Active',
            'features' => 'Features',
            'feature' => 'Feature',
            'add_feature' => 'Add Feature',
            'subscribers' => 'Subscribers',
            'ai_credits' => 'AI Credits',
            'unlimited_ai' => 'Unlimited AI',
        ],

        'billing_cycles' => [
            'monthly' => 'Monthly',
            'yearly' => 'Yearly',
            'lifetime' => 'Lifetime',
        ],
    ],

    // User Subscription Resource
    'user_subscriptions' => [
        'label' => 'User Subscription',
        'plural' => 'User Subscriptions',
        'navigation_label' => 'User Subscriptions',

        'sections' => [
            'subscription_details' => 'Subscription Details',
            'dates' => 'Dates',
            'settings' => 'Settings',
        ],

        'fields' => [
            'user' => 'User',
            'plan' => 'Plan',
            'status' => 'Status',
            'starts_at' => 'Starts At',
            'ends_at' => 'Ends At',
            'trial_ends_at' => 'Trial Ends At',
            'canceled_at' => 'Canceled At',
            'auto_renew' => 'Auto Renew',
        ],

        'statuses' => [
            'active' => 'Active',
            'pending' => 'Pending',
            'canceled' => 'Canceled',
            'expired' => 'Expired',
        ],
    ],

    // Business Card Resource
    'business_cards' => [
        'label' => 'Business Card',
        'plural' => 'Business Cards',
        'navigation_label' => 'Business Cards',

        'sections' => [
            'card_information' => 'Card Information',
            'card_description' => 'Basic card details and ownership',
            'media_images' => 'Media & Images',
            'media_description' => 'Upload card images and media',
            'design_appearance' => 'Design & Appearance',
            'design_description' => 'Customize the card appearance',
            'urls_access' => 'URLs & Access',
            'urls_description' => 'Configure card URLs and access settings',
            'status_visibility' => 'Status & Visibility',
            'status_description' => 'Control card visibility and status',
        ],

        'fields' => [
            'owner' => 'Owner',
            'user' => 'User',
            'language' => 'Language',
            'primary_language' => 'Primary Language',
            'title' => 'Title',
            'subtitle' => 'Subtitle',
            'cover_image' => 'Cover Image',
            'cover_helper' => 'Recommended: 1200x675px (16:9) for best display',
            'profile_image' => 'Profile Image',
            'profile_helper' => 'Recommended: 400x400px (1:1) square format',
            'avatar' => 'Avatar',
            'template' => 'Template',
            'theme' => 'Theme',
            'theme_customizations' => 'Theme Customizations',
            'css_property' => 'CSS Property',
            'custom_slug' => 'Custom Slug',
            'slug_helper' => 'Only letters, numbers, hyphens, and underscores allowed',
            'share_url' => 'Share URL',
            'share_helper' => 'Auto-generated unique identifier',
            'nfc_identifier' => 'NFC Identifier',
            'nfc_helper' => 'NFC tag identifier for contactless sharing',
            'full_url_preview' => 'Card URL Preview',
            'url_generated_after_save' => 'Will be generated after saving',
            'is_published' => 'Published',
            'published_helper' => 'Make this card publicly accessible',
            'is_primary' => 'Primary Card',
            'primary_helper' => 'Set as the default card for this user',
            'views_count' => 'Total Views',
            'shares_count' => 'Total Shares',
            'sections_count' => 'Sections Count',
            'published' => 'Published',
            'primary' => 'Primary',
            'views' => 'Views',
            'shares' => 'Shares',
            'sections' => 'Sections',
        ],

        'filters' => [
            'published_status' => 'Published Status',
            'published' => 'Published',
            'draft' => 'Draft',
            'primary_card' => 'Primary Card',
            'primary_cards' => 'Primary Cards',
            'secondary_cards' => 'Secondary Cards',
            'has_cover_image' => 'Has Cover Image',
            'popular' => 'Popular Cards (10+ views)',
        ],

        'actions' => [
            'preview' => 'Preview',
            'preview_card' => 'Preview Card',
            'duplicate' => 'Duplicate',
            'duplicate_card' => 'Duplicate Card',
            'publish' => 'Publish',
            'unpublish' => 'Unpublish',
        ],
    ],

    // Theme Resource
    'themes' => [
        'label' => 'Theme',
        'plural' => 'Themes',
        'navigation_label' => 'Themes',

        'sections' => [
            'theme_details' => 'Theme Details',
            'colors' => 'Colors',
            'typography' => 'Typography',
            'layout' => 'Layout',
            'custom_css' => 'Custom CSS',
            'preview' => 'Preview',
        ],

        'fields' => [
            'name' => 'Name',
            'owner' => 'Owner',
            'system' => 'System',
            'is_system_default' => 'System Default',
            'system_helper' => 'System themes are available to all users',
            'is_public' => 'Public',
            'public_helper' => 'Public themes can be used by other users',
            'primary_color' => 'Primary Color',
            'secondary_color' => 'Secondary Color',
            'background_color' => 'Background Color',
            'text_color' => 'Text Color',
            'card_bg' => 'Card Background',
            'border_color' => 'Border Color',
            'heading_font' => 'Heading Font',
            'body_font' => 'Body Font',
            'card_style' => 'Card Style',
            'border_radius' => 'Border Radius',
            'alignment' => 'Text Alignment',
            'spacing' => 'Spacing',
            'custom_css' => 'Custom CSS',
            'css_placeholder' => '/* Add your custom CSS here */',
            'preview_image' => 'Preview Image',
            'used_by' => 'Used By',
        ],

        'card_styles' => [
            'elevated' => 'Elevated (Shadow)',
            'outlined' => 'Outlined (Border)',
            'filled' => 'Filled (Flat)',
        ],

        'alignments' => [
            'left' => 'Left',
            'center' => 'Center',
            'right' => 'Right',
        ],

        'spacings' => [
            'compact' => 'Compact',
            'normal' => 'Normal',
            'relaxed' => 'Relaxed',
        ],

        'filters' => [
            'system_default' => 'System Default',
            'public' => 'Public',
        ],
    ],

    // Language Resource
    'languages' => [
        'label' => 'Language',
        'plural' => 'Languages',
        'navigation_label' => 'Languages',

        'sections' => [
            'language_information' => 'Language Information',
            'ui_labels' => 'UI Labels',
            'labels_description' => 'Manage label translations for this language',
        ],

        'fields' => [
            'name' => 'Name',
            'code' => 'Code',
            'direction' => 'Direction',
            'is_active' => 'Active',
            'is_default' => 'Default',
            'labels' => 'Labels',
        ],

        'directions' => [
            'ltr' => 'Left to Right',
            'rtl' => 'Right to Left',
        ],
    ],

    // Translation History Resource
    'translation_history' => [
        'label' => 'Translation History',
        'plural' => 'Translation History',
        'navigation_label' => 'Translation History',

        'sections' => [
            'translation_details' => 'Translation Details',
            'quality_status' => 'Quality & Status',
            'additional_information' => 'Additional Information',
        ],

        'fields' => [
            'user' => 'User',
            'business_card' => 'Business Card',
            'card' => 'Card',
            'source_language' => 'Source Language',
            'source' => 'Source',
            'target_language' => 'Target Language',
            'target' => 'Target',
            'source_text' => 'Source Text',
            'translated_text' => 'Translated Text',
            'quality_score' => 'Quality Score',
            'verification_status' => 'Verification Status',
            'cost' => 'Cost',
            'content_hash' => 'Content Hash',
            'metadata' => 'Metadata',
            'error_message' => 'Error Message',
        ],

        'verification_statuses' => [
            'pending' => 'Pending',
            'auto_verified' => 'Auto Verified',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'needs_review' => 'Needs Review',
        ],

        'filters' => [
            'created_from' => 'Created From',
            'created_until' => 'Created Until',
        ],

        'actions' => [
            'approve' => 'Approve',
            'reject' => 'Reject',
        ],
    ],

    // Settings Page
    'settings' => [
        'title' => 'Manage Settings',
        'navigation_label' => 'Settings',

        'sections' => [
            'general' => 'General Settings',
            'authentication' => 'Authentication Settings',
            'authentication_description' => 'Configure user verification and login methods.',
            'mail' => 'Mail Settings',
            'payment' => 'Payment Settings',
            'ai_translation' => 'AI Translation Settings',
        ],

        'fields' => [
            'site_name' => 'Site Name',
            'site_description' => 'Site Description',
            'meta_keywords' => 'Meta Keywords',
            'meta_description' => 'Meta Description',
            'logo' => 'Logo',
            'favicon' => 'Favicon',
            'verification_method' => 'Verification Method',
            'verification_helper' => 'Choose how users verify their account after registration.',
            'email_verification' => 'Email Verification',
            'phone_verification' => 'Phone (SMS) Verification',
            'allow_email_login' => 'Allow Email Login',
            'email_login_helper' => 'Users can sign in using their email address.',
            'allow_phone_login' => 'Allow Phone Login',
            'phone_login_helper' => 'Users can sign in using their phone number.',
            'mailer' => 'Mailer',
            'host' => 'Host',
            'port' => 'Port',
            'username' => 'Username',
            'encryption' => 'Encryption',
            'from_address' => 'From Address',
            'from_name' => 'From Name',
            'default_gateway' => 'Default Gateway',
            'lahza_public_key' => 'Lahza Public Key',
            'lahza_secret_key' => 'Lahza Secret Key',
            'lahza_test_mode' => 'Lahza Test Mode',
            'lahza_currency' => 'Lahza Currency',
            'openrouter_api_key' => 'OpenRouter API Key',
            'openrouter_url' => 'OpenRouter URL',
            'translation_model' => 'Translation Model',
            'request_timeout' => 'Request Timeout',
        ],

        'notifications' => [
            'saved' => 'Settings saved successfully.',
        ],
    ],

    // Widgets
    'widgets' => [
        'stats' => [
            'total_users' => 'Total Users',
            'registered_users' => 'Registered users',
            'active_subscriptions' => 'Active Subscriptions',
            'paid_subscribers' => 'Paid subscribers',
            'published_cards' => 'Published Cards',
            'active_cards' => 'Active business cards',
            'weekly_views' => 'Weekly Views',
            'views_this_week' => 'Card views this week',
        ],

        'latest_payments' => [
            'heading' => 'Latest Payments',
            'transaction' => 'Transaction',
            'user' => 'User',
            'plan' => 'Plan',
            'amount' => 'Amount',
            'status' => 'Status',
            'date' => 'Date',
        ],

        'unverified_users' => [
            'heading' => 'Unverified Users',
        ],

        'revenue_chart' => [
            'heading' => 'Revenue',
        ],

        'card_views_chart' => [
            'heading' => 'Card Views',
        ],

        'event_types_chart' => [
            'heading' => 'Event Types',
        ],
    ],

    // Addon Resource
    'addons' => [
        'label' => 'Add-On',
        'plural' => 'Add-Ons',
        'navigation_label' => 'Add-Ons',

        'sections' => [
            'addon_details' => 'Add-On Details',
            'type_config' => 'Type & Configuration',
            'pricing' => 'Pricing',
        ],

        'fields' => [
            'name' => 'Name',
            'slug' => 'Slug',
            'description' => 'Description',
            'type' => 'Type',
            'feature_key' => 'Feature Key',
            'value' => 'Value',
            'value_helper' => 'Number of extra card slots (for extra_cards type)',
            'price' => 'Price',
            'currency' => 'Currency',
            'sort_order' => 'Sort Order',
            'is_active' => 'Active',
            'purchases' => 'Purchases',
        ],

        'types' => [
            'extra_cards' => 'Extra Card Slots',
            'feature_unlock' => 'Feature Unlock',
        ],

        'feature_keys' => [
            'nfc' => 'NFC',
            'custom_domain' => 'Custom Domain',
            'analytics' => 'Analytics',
            'custom_css' => 'Custom CSS',
        ],
    ],

    // User Addon Resource
    'user_addons' => [
        'label' => 'User Add-On',
        'plural' => 'User Add-Ons',
        'navigation_label' => 'User Add-Ons',

        'fields' => [
            'user' => 'User',
            'addon' => 'Add-On',
            'granted_by' => 'Granted By',
            'transaction_id' => 'Transaction ID',
        ],

        'granted_types' => [
            'purchase' => 'Purchase',
            'admin_grant' => 'Admin Grant',
            'promo' => 'Promo',
        ],
    ],

    // Relation Managers
    'relations' => [
        'sections' => [
            'label' => 'Section',
            'plural' => 'Sections',
        ],
        'subscriptions' => [
            'label' => 'Subscription',
            'plural' => 'Subscriptions',
        ],
        'addons' => [
            'label' => 'Add-On',
            'plural' => 'Add-Ons',
        ],
    ],
];
