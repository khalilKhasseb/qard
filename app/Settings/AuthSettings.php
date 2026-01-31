<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AuthSettings extends Settings
{
    /**
     * The verification method to use after registration.
     * Options: 'email', 'phone'
     */
    public string $verification_method;

    /**
     * Whether users can login with their email address.
     */
    public bool $allow_email_login;

    /**
     * Whether users can login with their phone number.
     */
    public bool $allow_phone_login;

    public static function group(): string
    {
        return 'auth';
    }
}
