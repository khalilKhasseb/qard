<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Http\Resources\CardResource;
use App\Models\BusinessCard;
use App\Services\CardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CardController extends Controller
{
    public function __construct(
        private CardService $cardService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $cards = $request->user()
            ->cards()
            ->with(['sections', 'theme'])
            ->latest()
            ->paginate(15);

        return CardResource::collection($cards);
    }

    public function store(CreateCardRequest $request): JsonResponse
    {
        $card = $this->cardService->createCard(
            $request->user(),
            $request->validated()
        );

        return (new CardResource($card->load(['sections', 'theme'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, BusinessCard $card): CardResource
    {
        $this->authorize('view', $card);

        return new CardResource($card->load(['sections', 'theme']));
    }

    public function update(UpdateCardRequest $request, BusinessCard $card): CardResource
    {
        $this->authorize('update', $card);

        $updatedCard = $this->cardService->updateCard($card, $request->validated());

        return new CardResource($updatedCard->load(['sections', 'theme']));
    }

    public function destroy(Request $request, BusinessCard $card): JsonResponse
    {
        $this->authorize('delete', $card);

        $this->cardService->deleteCard($card);

        return response()->json([
            'message' => 'Card deleted successfully',
        ], 200);
    }

    public function publish(Request $request, BusinessCard $card): CardResource
    {
        $this->authorize('update', $card);

        $request->validate([
            'is_published' => 'required|boolean',
        ]);

        $card->is_published = $request->input('is_published');
        $card->save();

        return new CardResource($card->load(['sections', 'theme']));
    }

    public function duplicate(Request $request, BusinessCard $card): JsonResponse
    {
        $this->authorize('view', $card);

        $newCard = $this->cardService->duplicateCard($card, $request->user());

        return (new CardResource($newCard->load(['sections', 'theme'])))
            ->response()
            ->setStatusCode(201);
    }

    public function analytics(Request $request, BusinessCard $card): JsonResponse
    {
        $this->authorize('view', $card);

        $analytics = $this->cardService->getAnalytics($card);

        return response()->json([
            'card_id' => $card->id,
            'analytics' => $analytics,
        ]);
    }
}
