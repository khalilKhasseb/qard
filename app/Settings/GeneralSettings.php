<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;

    public string $site_description;

    public ?string $meta_keywords;

    public ?string $meta_description;

    public ?string $logo;

    public ?string $favicon;

    public static function group(): string
    {
        return 'general';
    }
}
