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

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.settings.navigation_label');
    }

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
            ->title(__('filament.settings.notifications.saved'))
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return __('filament.settings.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('filament.settings.sections.general'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('site_name')
                            ->label(__('filament.settings.fields.site_name'))
                            ->required(),
                        TextInput::make('site_description')
                            ->label(__('filament.settings.fields.site_description'))
                            ->required(),
                        TextInput::make('meta_keywords')
                            ->label(__('filament.settings.fields.meta_keywords')),
                        TextInput::make('meta_description')
                            ->label(__('filament.settings.fields.meta_description')),
                        FileUpload::make('logo')
                            ->label(__('filament.settings.fields.logo'))
                            ->image()
                            ->disk('public')
                            ->directory('settings'),
                        FileUpload::make('favicon')
                            ->label(__('filament.settings.fields.favicon'))
                            ->image()
                            ->disk('public')
                            ->directory('settings'),
                    ]),

                Section::make(__('filament.settings.sections.authentication'))
                    ->description(__('filament.settings.sections.authentication_description'))
                    ->columns(2)
                    ->statePath('auth_settings')
                    ->schema([
                        Select::make('verification_method')
                            ->label(__('filament.settings.fields.verification_method'))
                            ->helperText(__('filament.settings.fields.verification_helper'))
                            ->options([
                                'email' => __('filament.settings.fields.email_verification'),
                                'phone' => __('filament.settings.fields.phone_verification'),
                            ])
                            ->required(),
                        Toggle::make('allow_email_login')
                            ->label(__('filament.settings.fields.allow_email_login'))
                            ->helperText(__('filament.settings.fields.email_login_helper')),
                        Toggle::make('allow_phone_login')
                            ->label(__('filament.settings.fields.allow_phone_login'))
                            ->helperText(__('filament.settings.fields.phone_login_helper')),
                    ]),

                Section::make(__('filament.settings.sections.mail'))
                    ->columns(2)
                    ->statePath('mail_settings')
                    ->schema([
                        TextInput::make('mailer')
                            ->label(__('filament.settings.fields.mailer'))
                            ->required(),
                        TextInput::make('host')
                            ->label(__('filament.settings.fields.host'))
                            ->required(),
                        TextInput::make('port')
                            ->label(__('filament.settings.fields.port'))
                            ->numeric()
                            ->required(),
                        TextInput::make('username')
                            ->label(__('filament.settings.fields.username')),
                        TextInput::make('password')
                            ->label(__('filament.common.password'))
                            ->password(),
                        TextInput::make('encryption')
                            ->label(__('filament.settings.fields.encryption')),
                        TextInput::make('from_address')
                            ->label(__('filament.settings.fields.from_address'))
                            ->email()
                            ->required(),
                        TextInput::make('from_name')
                            ->label(__('filament.settings.fields.from_name'))
                            ->required(),
                    ]),

                Section::make(__('filament.settings.sections.payment'))
                    ->columns(2)
                    ->statePath('payment_settings')
                    ->schema([
                        Select::make('default_gateway')
                            ->label(__('filament.settings.fields.default_gateway'))
                            ->options([
                                'lahza' => __('filament.payments.methods.lahza'),
                                'cash' => __('filament.payments.methods.cash'),
                            ])
                            ->required(),
                        TextInput::make('lahza_public_key')
                            ->label(__('filament.settings.fields.lahza_public_key')),
                        TextInput::make('lahza_secret_key')
                            ->label(__('filament.settings.fields.lahza_secret_key'))
                            ->password(),
                        Toggle::make('lahza_test_mode')
                            ->label(__('filament.settings.fields.lahza_test_mode')),
                        TextInput::make('lahza_currency')
                            ->label(__('filament.settings.fields.lahza_currency'))
                            ->required(),
                    ]),

                Section::make(__('filament.settings.sections.ai_translation'))
                    ->columns(2)
                    ->statePath('ai_settings')
                    ->schema([
                        TextInput::make('openrouter_api_key')
                            ->label(__('filament.settings.fields.openrouter_api_key'))
                            ->password()
                            ->required(),
                        TextInput::make('openrouter_url')
                            ->label(__('filament.settings.fields.openrouter_url'))
                            ->required(),
                        TextInput::make('translation_model')
                            ->label(__('filament.settings.fields.translation_model'))
                            ->required(),
                        TextInput::make('request_timeout')
                            ->label(__('filament.settings.fields.request_timeout'))
                            ->numeric()
                            ->required(),
                    ]),
            ]);
    }
}
