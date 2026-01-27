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
            'currentLanguage' => app()->getLocale(),
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
}
