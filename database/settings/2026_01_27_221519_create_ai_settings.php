<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('ai.openrouter_api_key', config('services.openrouter.key'));
        $this->migrator->add('ai.openrouter_url', config('services.openrouter.url', 'https://openrouter.ai/api/v1'));
        $this->migrator->add('ai.translation_model', config('prism.translation_model', 'google/gemini-3-flash-preview'));
        $this->migrator->add('ai.request_timeout', (int) config('prism.request_timeout', 120));
    }
};
