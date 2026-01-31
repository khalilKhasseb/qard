<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('auth.verification_method', 'email');
        $this->migrator->add('auth.allow_email_login', true);
        $this->migrator->add('auth.allow_phone_login', true);
    }
};
