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
        $locale = app()->getLocale();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'languages' => Language::active()->get(['name', 'code', 'direction']),
            'currentLanguage' => $locale,
            'currentDirection' => $this->getDirection($locale),
            'translations' => fn () => $this->getTranslations($locale),
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
