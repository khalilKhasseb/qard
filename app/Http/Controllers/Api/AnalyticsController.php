<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Services\CardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        protected CardService $cardService
    ) {}

    public function track(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_type' => 'required|string|in:section_click,link_click,contact_save,social_share',
            'card_id' => 'required|exists:business_cards,id',
            'section_id' => 'nullable|exists:card_sections,id',
            'metadata' => 'nullable|array',
        ]);

        $card = BusinessCard::findOrFail($validated['card_id']);

        if ($validated['event_type'] === 'section_click' && isset($validated['section_id'])) {
            $section = CardSection::findOrFail($validated['section_id']);
            $this->cardService->trackSectionClick($card, $section, [
                'metadata' => $validated['metadata'] ?? null,
            ]);
        } elseif ($validated['event_type'] === 'social_share') {
            $this->cardService->trackShare(
                $card,
                $validated['metadata']['platform'] ?? 'unknown',
                ['metadata' => $validated['metadata'] ?? null]
            );
        }

        return response()->json(['success' => true]);
    }
}
