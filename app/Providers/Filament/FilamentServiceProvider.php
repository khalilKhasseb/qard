<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\LanguageSelectorWidget;
use App\Filament\Widgets\TranslationOverview;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Router;

class FilamentServiceProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->profile()
            ->widgets([
                LanguageSelectorWidget::class,
                TranslationOverview::class,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                //
            ])
            ->navigationGroups([
                'System Management',
                'Content Management',
                'User Management',
                'Settings'
            ])
            ->brandName('Qard Admin')
            ->favicon(asset('images/favicon.ico'))
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s');
    }
}
