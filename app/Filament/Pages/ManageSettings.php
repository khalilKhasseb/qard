<?php

namespace App\Filament\Pages;

use App\Settings\AiSettings;
use App\Settings\AuthSettings;
use App\Settings\GeneralSettings;
use App\Settings\MailSettings;
use App\Settings\PaymentSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends SettingsPage
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;

    protected static string $settings = GeneralSettings::class;

    protected static string|null|\UnitEnum $navigationGroup = 'Settings';

    /**
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['mail_settings'] = app(MailSettings::class)->toArray();
        $data['payment_settings'] = app(PaymentSettings::class)->toArray();
        $data['ai_settings'] = app(AiSettings::class)->toArray();
        $data['auth_settings'] = app(AuthSettings::class)->toArray();

        return $data;
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $generalSettings = app(GeneralSettings::class);
            $generalSettings->fill($data);
            $generalSettings->save();

            if (isset($data['mail_settings'])) {
                $mailSettings = app(MailSettings::class);
                $mailSettings->fill($data['mail_settings']);
                $mailSettings->port = (int) ($data['mail_settings']['port'] ?? 0);
                $mailSettings->save();
            }

            if (isset($data['payment_settings'])) {
                $paymentSettings = app(PaymentSettings::class);
                $paymentSettings->fill($data['payment_settings']);
                $paymentSettings->lahza_test_mode = (bool) ($data['payment_settings']['lahza_test_mode'] ?? false);
                $paymentSettings->save();
            }

            if (isset($data['ai_settings'])) {
                $aiSettings = app(AiSettings::class);
                $aiSettings->fill($data['ai_settings']);
                $aiSettings->request_timeout = (int) ($data['ai_settings']['request_timeout'] ?? 120);
                $aiSettings->save();
            }

            if (isset($data['auth_settings'])) {
                $authSettings = app(AuthSettings::class);
                $authSettings->fill($data['auth_settings']);
                $authSettings->allow_email_login = (bool) ($data['auth_settings']['allow_email_login'] ?? true);
                $authSettings->allow_phone_login = (bool) ($data['auth_settings']['allow_phone_login'] ?? true);
                $authSettings->save();
            }

            $this->sendSuccessNotification();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    protected function sendSuccessNotification(): void
    {
        \Filament\Notifications\Notification::make()
            ->title('Settings saved successfully.')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return 'Manage Settings';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('General Settings')
                    ->columns(2)
                    ->schema([
                        TextInput::make('site_name')
                            ->required(),
                        TextInput::make('site_description')
                            ->required(),
                        TextInput::make('meta_keywords'),
                        TextInput::make('meta_description'),
                        FileUpload::make('logo')
                            ->image()
                            ->disk('public')
                            ->directory('settings'),
                        FileUpload::make('favicon')
                            ->image()
                            ->disk('public')
                            ->directory('settings'),
                    ]),

                Section::make('Authentication Settings')
                    ->description('Configure user verification and login methods.')
                    ->columns(2)
                    ->statePath('auth_settings')
                    ->schema([
                        Select::make('verification_method')
                            ->label('Verification Method')
                            ->helperText('Choose how users verify their account after registration.')
                            ->options([
                                'email' => 'Email Verification',
                                'phone' => 'Phone (SMS) Verification',
                            ])
                            ->required(),
                        Toggle::make('allow_email_login')
                            ->label('Allow Email Login')
                            ->helperText('Users can sign in using their email address.'),
                        Toggle::make('allow_phone_login')
                            ->label('Allow Phone Login')
                            ->helperText('Users can sign in using their phone number.'),
                    ]),

                Section::make('Mail Settings')
                    ->columns(2)
                    ->statePath('mail_settings')
                    ->schema([
                        TextInput::make('mailer')
                            ->required(),
                        TextInput::make('host')
                            ->required(),
                        TextInput::make('port')
                            ->numeric()
                            ->required(),
                        TextInput::make('username'),
                        TextInput::make('password')
                            ->password(),
                        TextInput::make('encryption'),
                        TextInput::make('from_address')
                            ->email()
                            ->required(),
                        TextInput::make('from_name')
                            ->required(),
                    ]),

                Section::make('Payment Settings')
                    ->columns(2)
                    ->statePath('payment_settings')
                    ->schema([
                        Select::make('default_gateway')
                            ->options([
                                'lahza' => 'Lahza',
                                'cash' => 'Cash',
                            ])
                            ->required(),
                        TextInput::make('lahza_public_key'),
                        TextInput::make('lahza_secret_key')
                            ->password(),
                        Toggle::make('lahza_test_mode'),
                        TextInput::make('lahza_currency')
                            ->required(),
                    ]),

                Section::make('AI Translation Settings')
                    ->columns(2)
                    ->statePath('ai_settings')
                    ->schema([
                        TextInput::make('openrouter_api_key')
                            ->password()
                            ->required(),
                        TextInput::make('openrouter_url')
                            ->required(),
                        TextInput::make('translation_model')
                            ->required(),
                        TextInput::make('request_timeout')
                            ->numeric()
                            ->required(),
                    ]),
            ]);
    }
}
