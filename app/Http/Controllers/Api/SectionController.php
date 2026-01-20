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
