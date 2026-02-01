<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetFilamentLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Always use the app locale from config for Filament admin panel
        $locale = config('app.locale', 'en');
        App::setLocale($locale);

        // Debug: Log the locale being set (remove after testing)
        logger('Filament locale set to: '.$locale.' | Translation test: '.__('filament.navigation.groups.user_management'));

        return $next($request);
    }
}
