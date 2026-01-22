<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Http\Resources\SectionResource;
use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Services\CardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function __construct(
        private CardService $cardService
    ) {}

    public function store(CreateSectionRequest $request, BusinessCard $card): JsonResponse
    {
        $this->authorize('update', $card);

        $section = $this->cardService->addSection($card, $request->validated());

        return (new SectionResource($section))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateSectionRequest $request, CardSection $section): SectionResource
    {
        $this->authorize('update', $section->businessCard);

        $updatedSection = $this->cardService->updateSection($section, $request->validated());

        return new SectionResource($updatedSection);
    }

    public function uploadGallery(Request $request, CardSection $section): JsonResponse
    {
        $this->authorize('update', $section->businessCard);

        if ($section->section_type !== 'gallery') {
            return response()->json(['message' => 'Section is not a gallery'], 422);
        }

        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'index' => 'nullable|integer',
        ]);

        $user = $request->user();
        $card = $section->businessCard;

        $directory = "users/{$user->id}/cards/{$card->id}/gallery";
        $filename = uniqid('gallery_').'.'.$request->file('image')->getClientOriginalExtension();
        $path = $request->file('image')->storeAs($directory, $filename, 'public');
        $url = \Illuminate\Support\Facades\Storage::url($path);

        $content = $section->content ?? [];
        if (!isset($content['items']) || !is_array($content['items'])) {
            $content['items'] = [];
        }

        if (isset($validated['index']) && isset($content['items'][$validated['index']])) {
            $content['items'][$validated['index']]['image_path'] = $path;
            $content['items'][$validated['index']]['image_url'] = $url;
            $content['items'][$validated['index']]['url'] = $url;
            $index = $validated['index'];
        } else {
            $content['items'][] = ['url' => $url, 'image_url' => $url, 'image_path' => $path];
            $index = count($content['items']) - 1;
        }

        $updatedSection = $this->cardService->updateSection($section, ['content' => $content]);

        return response()->json([
            'success' => true,
            'url' => $url,
            'path' => $path,
            'index' => $index,
            'section' => new SectionResource($updatedSection),
        ]);
    }

    public function destroy(Request $request, CardSection $section): JsonResponse
    {
        $this->authorize('update', $section->businessCard);

        $this->cardService->deleteSection($section);

        return response()->json([
            'message' => 'Section deleted successfully',
        ], 200);
    }

    public function reorder(Request $request, BusinessCard $card): JsonResponse
    {
        $this->authorize('update', $card);

        $request->validate([
            'section_ids' => 'required|array',
            'section_ids.*' => 'exists:card_sections,id',
        ]);

        $this->cardService->reorderSections($card, $request->input('section_ids'));

        return response()->json([
            'message' => 'Sections reordered successfully',
        ], 200);
    }
}
