<?php

namespace App\Http\Controllers;

use App\Models\BusinessCard;
use App\Models\Language;
use App\Models\Theme;
use App\Services\CardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function create(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        // Check if user can create more cards
        if (! $user->canCreateCard()) {
            return redirect()->route('subscription.index')
                ->with('error', 'You have reached your card limit. Upgrade your plan to create more cards.');
        }

        $themes = Theme::forUser($user->id)->get();
        $languages = Language::active()->get();
        $defaultLanguage = Language::default()->first() ?? $languages->first();

        return Inertia::render('Cards/Create', [
            'themes' => $themes,
            'languages' => $languages,
            'defaultLanguage' => $defaultLanguage,
            'appUrl' => url('/'),
            'cardCount' => $user->cards()->count(),
            'cardLimit' => $user->getCardLimit(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'theme_id' => 'nullable|exists:themes,id',
            'language_id' => 'required|exists:languages,id',
            'custom_slug' => 'nullable|string|max:255|unique:business_cards,custom_slug',
            'is_published' => 'boolean',
        ]);

        // Wrap title and subtitle into JSON based on selected language
        $language = Language::find($validated['language_id']);
        $validated['title'] = [$language->code => $validated['title']];
        if (! empty($validated['subtitle'])) {
            $validated['subtitle'] = [$language->code => $validated['subtitle']];
        }

        $card = $this->cardService->createCard($request->user(), $validated);

        return redirect()->route('cards.edit', $card->id)
            ->with('success', 'Card created successfully!');
    }

    public function edit(Request $request, BusinessCard $card): Response
    {
        $this->authorize('update', $card);

        $card->load(['sections' => fn ($q) => $q->ordered(), 'theme', 'language']);

        $themes = Theme::forUser($request->user()->id)->get();
        $languages = Language::active()->get();

        $publicUrl = $card->custom_slug
            ? route('card.public.slug', $card->custom_slug)
            : route('card.public.share', $card->share_url);

        return Inertia::render('Cards/Edit', [
            'card' => $card,
            'sections' => $card->sections,
            'themes' => $themes,
            'languages' => $languages,
            'publicUrl' => $publicUrl,
        ]);
    }

    public function update(Request $request, BusinessCard $card)
    {
        $this->authorize('update', $card);

        $validated = $request->validate([
            'title' => 'sometimes|array',
            'subtitle' => 'nullable|array',
            'cover_image' => 'nullable|image|max:2048',
            'profile_image' => 'nullable|image|max:2048',
            'theme_id' => 'nullable|exists:themes,id',
            'language_id' => 'sometimes|exists:languages,id',
            'custom_slug' => 'nullable|string|max:255|unique:business_cards,custom_slug,'.$card->id,
        ]);

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_image_path'] = $path;
        }

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $validated['profile_image_path'] = $path;
        }

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
            'is_published' => 'required|boolean',
        ]);

        $card->is_published = $request->input('is_published');
        $card->save();

        return back()->with('success', $card->is_published ? 'Card published successfully!' : 'Card unpublished successfully!');
    }

    public function updateSections(Request $request, BusinessCard $card)
    {
        $this->authorize('update', $card);

        $validated = $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'nullable',
            'sections.*.type' => 'required|string',
            'sections.*.title' => 'nullable|string',
            'sections.*.content' => 'required|array',
            'sections.*.order' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($card, $validated) {
            $card->sections()->delete();

            foreach ($validated['sections'] as $index => $sectionData) {
                $card->sections()->create([
                    'section_type' => $sectionData['type'],
                    'title' => $sectionData['title'] ?? ucfirst($sectionData['type']),
                    'content' => $sectionData['content'],
                    'sort_order' => $sectionData['order'] ?? ($index + 1),
                    'is_active' => true,
                ]);
            }
        });

        return back()->with('success', 'Sections updated successfully!');
    }
}
