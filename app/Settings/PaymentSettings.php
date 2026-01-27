<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentSettings extends Settings
{
    public string $default_gateway;

    public ?string $lahza_public_key;

    public ?string $lahza_secret_key;

    public bool $lahza_test_mode;

    public string $lahza_currency;

    public static function group(): string
    {
        return 'payment';
    }
}
