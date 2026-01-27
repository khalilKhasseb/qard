<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('mail.mailer', 'smtp');
        $this->migrator->add('mail.host', 'smtp.mailtrap.io');
        $this->migrator->add('mail.port', 2525);
        $this->migrator->add('mail.username', null);
        $this->migrator->add('mail.password', null);
        $this->migrator->add('mail.encryption', 'tls');
        $this->migrator->add('mail.from_address', 'hello@example.com');
        $this->migrator->add('mail.from_name', 'Qard');
    }
};
