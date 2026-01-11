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
        // Check if language is set in session
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            // Set default language
            $defaultLanguage = config('app.locale', 'en');
            App::setLocale($defaultLanguage);
            Session::put('locale', $defaultLanguage);
        }

        return $next($request);
    }
}
