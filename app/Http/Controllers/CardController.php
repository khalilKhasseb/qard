<?php

namespace App\Http\Controllers;

use App\Models\BusinessCard;
use App\Models\Theme;
use App\Services\CardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CardController extends Controller
{
    public function __construct(
        protected CardService $cardService
    ) {}

    public function index(Request $request): Response
    {
        $cards = $request->user()
            ->cards()
            ->with(['theme', 'sections'])
            ->latest()
            ->paginate(12);

        return Inertia::render('Cards/Index', [
            'cards' => $cards,
        ]);
    }

    public function create(Request $request): Response
    {
        $themes = Theme::forUser($request->user()->id)->get();

        return Inertia::render('Cards/Create', [
            'themes' => $themes,
            'appUrl' => url('/'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'theme_id' => 'nullable|exists:themes,id',
            'custom_slug' => 'nullable|string|max:255|unique:business_cards,custom_slug',
            'is_published' => 'boolean',
        ]);

        $card = $this->cardService->createCard($request->user(), $validated);

        return redirect()->route('cards.edit', $card->id)
            ->with('success', 'Card created successfully!');
    }

    public function edit(Request $request, BusinessCard $card): Response
    {
        $this->authorize('update', $card);

        $card->load(['sections' => fn($q) => $q->ordered(), 'theme']);

        $themes = Theme::forUser($request->user()->id)->get();

        $publicUrl = $card->custom_slug
            ? route('card.public.slug', $card->custom_slug)
            : route('card.public.share', $card->share_url);

        return Inertia::render('Cards/Edit', [
            'card' => $card,
            'sections' => $card->sections,
            'themes' => $themes,
            'publicUrl' => $publicUrl,
        ]);
    }

    public function update(Request $request, BusinessCard $card)
    {
        $this->authorize('update', $card);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'theme_id' => 'nullable|exists:themes,id',
            'custom_slug' => 'nullable|string|max:255|unique:business_cards,custom_slug,' . $card->id,
        ]);

        $this->cardService->updateCard($card, $validated);

        return back()->with('success', 'Card updated successfully!');
    }

    public function destroy(Request $request, BusinessCard $card)
    {
        $this->authorize('delete', $card);

        $this->cardService->deleteCard($card);

        return redirect()->route('cards.index')
            ->with('success', 'Card deleted successfully!');
    }

    public function publish(Request $request, BusinessCard $card)
    {
        $this->authorize('update', $card);

        $request->validate([
            'is_published' => 'required|boolean'
        ]);

        $card->is_published = $request->input('is_published');
        $card->save();

        return back()->with('success', $card->is_published ? 'Card published successfully!' : 'Card unpublished successfully!');
    }
}
