<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Http\Requests\SwitchLanguageRequest;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::active()->get();
        return LanguageResource::collection($languages);
    }

    public function show(Language $language)
    {
        return new LanguageResource($language);
    }

    public function switchLanguage(SwitchLanguageRequest $request)
    {
        $language = Language::where('code', $request->language_code)
            ->active()
            ->firstOrFail();

        session()->put('locale', $language->code);
        app()->setLocale($language->code);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Language switched successfully',
                'language' => new LanguageResource($language)
            ]);
        }

        return back()->with('success', 'Language switched successfully');
    }
}
