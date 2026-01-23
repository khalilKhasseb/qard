<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option determines the default payment gateway to use for the
    | application. Options: 'lahza', 'cash'
    |
    */

    'gateway' => env('PAYMENT_GATEWAY', 'lahza'),

    /*
    |--------------------------------------------------------------------------
    | Lahza Payment Configuration
    |--------------------------------------------------------------------------
    */

    'lahza' => [
        /*
        | Your public key from Lahza.
        | Use test key for test mode and live key for live mode.
        */
        'public_key' => env('LAHZA_PUBLIC_KEY'),

        /*
        | Your secret key from Lahza.
        | This is used for server-to-server API calls.
        | IMPORTANT: Keep this secret and never expose it on the client side.
        */
        'secret_key' => env('LAHZA_SECRET_KEY'),

        /*
        | Webhook secret for verifying webhook signatures.
        | Get this from your Lahza dashboard.
        */
        'webhook_secret' => env('LAHZA_WEBHOOK_SECRET'),

        /*
        | Whether to use test mode.
        | true = test mode (sandbox), false = live mode.
        */
        'test_mode' => env('LAHZA_TEST_MODE', true),

        /*
        | Base URL for Lahza API.
        | Usually https://api.lahza.io
        */
        'base_url' => env('LAHZA_BASE_URL', 'https://api.lahza.io'),

        /*
        | Default currency for payments.
        | Options: 'USD', 'ILS', 'JOD'
        */
        'currency' => env('LAHZA_CURRENCY', 'USD'),

        /*
        | Available payment channels.
        | Options: 'card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer'
        */
        'channels' => explode(',', env('LAHZA_CHANNELS', 'card')),

        /*
        | Callback URL route name.
        | This is where Lahza will redirect users after payment.
        */
        'callback_route' => 'payments.callback',

        /*
        | Webhook URL route name.
        | This is where Lahza will send webhook events.
        */
        'webhook_route' => 'webhooks.lahza',

        /*
        | Timeout for Lahza API requests (seconds).
        */
        'timeout' => env('LAHZA_TIMEOUT', 30),

        /*
        | Log level for payment operations.
        | Options: 'debug', 'info', 'warning', 'error', 'critical'
        */
        'log_level' => env('LAHZA_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | These are the subscription plans that can be purchased.
    | The database will have the actual plans, but this config provides
    | default settings for plan creation.
    */

    'plans' => [
        'defaults' => [
            'currency' => 'USD',
            'billing_cycles' => ['monthly', 'yearly', 'lifetime'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Security
    |--------------------------------------------------------------------------
    */

    'security' => [
        /*
        | Whether to verify webhook signatures.
        | Recommended: true in production.
        */
        'verify_webhook_signatures' => env('LAHZA_VERIFY_WEBHOOKS', true),

        /*
        | Maximum allowed payment amount (in base currency).
        */
        'max_amount' => env('MAX_PAYMENT_AMOUNT', 10000.00),

        /*
        | Minimum allowed payment amount (in base currency).
        */
        'min_amount' => env('MIN_PAYMENT_AMOUNT', 0.01),

        /*
        | Allow duplicate payments within this time window (minutes).
        */
        'duplicate_window' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        /*
        | Send payment confirmation notifications to users.
        */
        'send_confirmation' => true,

        /*
        | Send admin notifications for large payments.
        */
        'notify_admin_on_large' => true,

        /*
        | Threshold for "large" payment (in base currency).
        */
        'large_payment_threshold' => 1000.00,
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    */

    'retry' => [
        /*
        | Number of retries for failed API calls.
        */
        'attempts' => env('LAHZA_RETRY_ATTEMPTS', 3),

        /*
        | Delay between retries (seconds).
        */
        'delay' => env('LAHZA_RETRY_DELAY', 5),
    ],
];
