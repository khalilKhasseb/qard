<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('payment.default_gateway', 'lahza');
        $this->migrator->add('payment.lahza_public_key', null);
        $this->migrator->add('payment.lahza_secret_key', null);
        $this->migrator->add('payment.lahza_test_mode', true);
        $this->migrator->add('payment.lahza_currency', 'USD');
    }
};
