<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default SMS provider that will be used to send
    | messages. You may set this to any of the providers defined below.
    |
    | Supported: "log", "twilio", "vonage"
    |
    */

    'default' => env('SMS_PROVIDER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | SMS Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for each SMS provider. Add your
    | credentials and other provider-specific settings here.
    |
    */

    'providers' => [

        'log' => [
            'channel' => env('SMS_LOG_CHANNEL', 'stack'),
        ],

        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from_number' => env('TWILIO_FROM_NUMBER'),
        ],

        'vonage' => [
            'api_key' => env('VONAGE_API_KEY'),
            'api_secret' => env('VONAGE_API_SECRET'),
            'from_name' => env('VONAGE_FROM_NAME', env('APP_NAME')),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    |
    | Configure OTP (One-Time Password) behavior including code length,
    | expiration time, cooldown between requests, and max attempts.
    |
    */

    'otp' => [

        // Number of digits in the OTP code
        'length' => env('OTP_LENGTH', 6),

        // OTP expiration time in seconds (default: 5 minutes)
        'expires_in' => env('OTP_EXPIRES_IN', 300),

        // Cooldown between OTP requests in seconds (default: 60 seconds)
        'cooldown' => env('OTP_COOLDOWN', 60),

        // Maximum verification attempts before OTP is invalidated
        'max_attempts' => env('OTP_MAX_ATTEMPTS', 5),

        // Message template for OTP SMS
        // Available placeholders: {code}, {minutes}
        'message' => env('OTP_MESSAGE', 'Your verification code is: {code}. Valid for {minutes} minutes.'),

    ],

];
