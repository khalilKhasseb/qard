<?php

namespace App\Http\Middleware;

use App\Models\Language;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $general = app(GeneralSettings::class);

        // Get the current locale from session or app (set by LanguageMiddleware)
        $languageCode = session('locale', app()->getLocale());

        // Fetch the language details from database
        $currentLanguage = Language::where('code', $languageCode)->active()->first();

        // Fallback to default language if the session language is not found/active
        if (! $currentLanguage) {
            $currentLanguage = Language::active()->default()->first();
            $languageCode = $currentLanguage?->code ?? 'en';
        }

        $languageDirection = $currentLanguage?->direction ?? 'ltr';

        $user = $request->user();

        //        dd($request);
        return [
            ...parent::share($request),
            // Note: CSRF is handled automatically by Inertia via XSRF-TOKEN cookie
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'auth' => [
                'user' => $user,
                'capabilities' => $user ? [
                    'can_create_card' => $user->canCreateCard(),
                    'can_create_theme' => $user->canCreateTheme(),
                    'can_use_custom_css' => $user->canUseCustomCss(),
                    'can_use_nfc' => $user->canUseNfc(),
                    'can_use_analytics' => $user->canUseAnalytics(),
                    'can_use_custom_domain' => $user->canUseCustomDomain(),
                    'can_access_premium_templates' => $user->canAccessPremiumTemplates(),
                    'card_limit' => $user->getCardLimit(),
                    'theme_limit' => $user->getThemeLimit(),
                ] : null,
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'languages' => Language::active()->get(['name', 'code', 'direction']),
            'currentLanguage' => $languageCode,
            'currentDirection' => $languageDirection,
            'translations' => fn () => $this->getTranslations($languageCode),
            'settings' => [
                'site_name' => $general->site_name,
                'site_description' => $general->site_description,
                'meta_keywords' => $general->meta_keywords,
                'meta_description' => $general->meta_description,
                'logo' => $general->logo ? asset('storage/'.$general->logo) : null,
                'favicon' => $general->favicon ? asset('storage/'.$general->favicon) : null,
            ],
        ];
    }

    /**
     * Get translations for the frontend.
     *
     * @return array<string, array<string, mixed>>
     */
    private function getTranslations(string $locale): array
    {
        return [
            'common' => trans('common', [], $locale),
            'auth' => trans('auth', [], $locale),
            'dashboard' => trans('dashboard', [], $locale),
            'cards' => trans('cards', [], $locale),
            'themes' => trans('themes', [], $locale),
            'payments' => trans('payments', [], $locale),
            'profile' => trans('profile', [], $locale),
            'public' => trans('public', [], $locale),
            'analytics' => trans('analytics', [], $locale),
            'welcome' => trans('welcome', [], $locale),
        ];
    }

    /**
     * Get text direction for the locale.
     */
    private function getDirection(string $locale): string
    {
        $language = Language::where('code', $locale)->first();

        return $language?->direction ?? 'ltr';
    }
}
