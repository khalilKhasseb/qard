<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTranslationRequest;
use App\Http\Resources\TranslationResource;
use App\Models\Translation;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function index(Request $request)
    {
        $languageCode = $request->query('language_code');

        if ($languageCode) {
            $translations = Translation::where('language_code', $languageCode)->get();
        } else {
            $translations = Translation::all();
        }

        return TranslationResource::collection($translations);
    }

    public function store(CreateTranslationRequest $request)
    {
        $translation = Translation::create($request->validated());

        return new TranslationResource($translation);
    }

    public function show(Translation $translation)
    {
        return new TranslationResource($translation);
    }

    public function update(CreateTranslationRequest $request, Translation $translation)
    {
        $translation->update($request->validated());

        return new TranslationResource($translation);
    }

    public function destroy(Translation $translation)
    {
        $translation->delete();

        return response()->json(['message' => 'Translation deleted successfully']);
    }
}
