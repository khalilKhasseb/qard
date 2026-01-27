<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AiSettings extends Settings
{
    public ?string $openrouter_api_key;

    public string $openrouter_url;

    public string $translation_model;

    public int $request_timeout;

    public static function group(): string
    {
        return 'ai';
    }
}
