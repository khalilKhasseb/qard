<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Language;

class SetLanguageDirection
{
    public function handle(Request $request, Closure $next)
    {
        $languageCode = app()->getLocale();
        $language = Language::where('code', $languageCode)->first();
        
        if ($language && $language->direction === 'rtl') {
            config(['app.direction' => 'rtl']);
        } else {
            config(['app.direction' => 'ltr']);
        }

        return $next($request);
    }
}
