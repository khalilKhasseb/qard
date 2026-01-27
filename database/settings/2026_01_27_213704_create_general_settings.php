<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'Qard');
        $this->migrator->add('general.site_description', 'Digital Card Management System');
        $this->migrator->add('general.meta_keywords', 'digital cards, qard, business cards');
        $this->migrator->add('general.meta_description', 'Create and manage your digital business cards with Qard.');
        $this->migrator->add('general.logo', null);
        $this->migrator->add('general.favicon', null);
    }
};
