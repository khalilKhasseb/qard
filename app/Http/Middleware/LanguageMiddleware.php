<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Get the default language from config
        $defaultLanguage = config('app.locale', 'en');

        // Check if language is set in session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } else {
            // Use the default language from config
            $locale = $defaultLanguage;
            Session::put('locale', $locale);
        }

        // Set the application locale
        App::setLocale($locale);

        return $next($request);
    }
}
