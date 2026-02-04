<?php

namespace App\Http\Controllers;

use App\Http\Resources\LanguageResource;
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
        $user = $request->user();
        $cards = $user
            ->cards()
            ->with(['theme', 'sections'])
            ->latest()
            ->paginate(12);

        return Inertia::render('Cards/Index', [
            'cards' => $cards,
            'canCreateCard' => $user->canCreateCard(),
            'cardCount' => $user->cards()->count(),
            'cardLimit' => $user->getCardLimit(),
        ]);
    }

    public function create(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        // Check if user can create more cards
        if (! $user->canCreateCard() && ! $user->canUsePlan()) {
            return redirect()->route('subscription.index')
                ->with('error', 'You have reached your card limit. Upgrade your plan to create more cards.');
        }

        $themes = Theme::forUser($user->id)->get();
        $languages = Language::active()->get();
        $defaultLanguage = Language::default()->first() ?? $languages->first();

        return Inertia::render('Cards/Create', [
            'themes' => $themes,
            'languages' => LanguageResource::collection($languages)->resolve(),
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

        // Prepare draft info for the frontend
        $hasDraft = ! empty($card->draft_data);
        $draftFields = $hasDraft ? array_keys($card->draft_data) : [];

        // Build draft image URLs if they exist
        $draftImageUrls = [];
        if ($hasDraft && isset($card->draft_data['cover_image_path'])) {
            $draftImageUrls['cover_image_url'] = asset('storage/'.$card->draft_data['cover_image_path']);
        }
        if ($hasDraft && isset($card->draft_data['profile_image_path'])) {
            $draftImageUrls['profile_image_url'] = asset('storage/'.$card->draft_data['profile_image_path']);
        }

        return Inertia::render('Cards/Edit', [
            'card' => $card,
            'sections' => $card->sections,
            'themes' => $themes,
            'languages' => LanguageResource::collection($languages)->resolve(),
            'publicUrl' => $publicUrl,
            'hasDraft' => $hasDraft,
            'draftFields' => $draftFields,
            'draftImageUrls' => $draftImageUrls,
        ]);
    }

    public function update(Request $request, BusinessCard $card)
    {
        \Log::info('=== CardController@update called ===');
        \Log::info('Card ID: '.$card->id);
        $this->authorize('update', $card);

        \Log::info('Authorization passed');
        $validated = $request->validate([
            'title' => 'sometimes|array',
            'subtitle' => 'nullable|array',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'profile_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'theme_id' => 'nullable|exists:themes,id',
            'language_id' => 'nullable|exists:languages,id',
            'active_languages' => 'nullable|array',
            'custom_slug' => 'nullable|string|max:255|unique:business_cards,custom_slug,'.$card->id,
        ], [
            'cover_image.image' => __('The cover image must be a valid image file.'),
            'cover_image.mimes' => __('The cover image must be a JPEG, PNG, GIF, or WebP file.'),
            'cover_image.max' => __('The cover image must not exceed 2MB.'),
            'profile_image.image' => __('The profile image must be a valid image file.'),
            'profile_image.mimes' => __('The profile image must be a JPEG, PNG, GIF, or WebP file.'),
            'profile_image.max' => __('The profile image must not exceed 2MB.'),
        ]);

        \Log::info('Validation passed, data: ', $validated);

        // Remove the file keys from validated since we're not storing them
        unset($validated['cover_image'], $validated['profile_image']);

        \Log::info('After unsetting files, data: ', $validated);

        // Determine if saving as draft (use filter_var to handle string "true" from FormData)
        $saveAsDraft = filter_var($request->input('save_as_draft'), FILTER_VALIDATE_BOOLEAN);

        \Log::info('Save as draft: '.($saveAsDraft ? 'true' : 'false'));
        // Build user-specific storage path
        $userId = $request->user()->id;
        $cardId = $card->id;
        $basePath = "users/{$userId}/cards/{$cardId}";

        // Handle cover image upload with old file cleanup
        if ($request->hasFile('cover_image')) {
            // Delete old cover image (from draft or live depending on mode)
            $oldPath = $saveAsDraft
                ? ($card->draft_data['cover_image_path'] ?? null)
                : $card->cover_image_path;

            if ($oldPath && \Storage::disk('public')->exists($oldPath)) {
                \Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('cover_image')->storeAs(
                "{$basePath}/cover",
                time().'_'.$request->file('cover_image')->getClientOriginalName(),
                ['disk' => 'public', 'visibility' => 'public']
            );
            $validated['cover_image_path'] = $path;
        }

        \Log::info('After handling cover image, data: ', $validated);

        // Handle profile image upload with old file cleanup
        if ($request->hasFile('profile_image')) {
            // Delete old profile image (from draft or live depending on mode)
            $oldPath = $saveAsDraft
                ? ($card->draft_data['profile_image_path'] ?? null)
                : $card->profile_image_path;

            if ($oldPath && \Storage::disk('public')->exists($oldPath)) {
                \Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('profile_image')->storeAs(
                "{$basePath}/profile",
                time().'_'.$request->file('profile_image')->getClientOriginalName(),
                ['disk' => 'public', 'visibility' => 'public']
            );
            $validated['profile_image_path'] = $path;
        }

        \Log::info('After handling profile image, data: ', $validated);

        // If saving as draft, store in draft_data instead of updating live data
        \Log::info('Final validated data before save: ', $validated);
        if ($saveAsDraft) {
            \Log::info('Saving to draft data');
            $draftData = array_merge($card->draft_data ?? [], $validated);
            $card->draft_data = $draftData;
            $card->save();

            return back()->with('success', 'Draft saved successfully!');
        }

        \Log::info('Saving to live data');

        $this->cardService->updateCard($card, $validated);

        \Log::info('=== CardController@update completed successfully ===');

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

    public function publishDraft(Request $request, BusinessCard $card)
    {
        $this->authorize('update', $card);

        if (! $card->draft_data) {
            return back()->with('error', 'No draft changes to publish.');
        }

        // Delete old live images if draft has new ones
        if (isset($card->draft_data['cover_image_path']) && $card->cover_image_path) {
            if ($card->cover_image_path !== $card->draft_data['cover_image_path']) {
                \Storage::disk('public')->delete($card->cover_image_path);
            }
        }

        if (isset($card->draft_data['profile_image_path']) && $card->profile_image_path) {
            if ($card->profile_image_path !== $card->draft_data['profile_image_path']) {
                \Storage::disk('public')->delete($card->profile_image_path);
            }
        }

        $this->cardService->updateCard($card, $card->draft_data);
        $card->draft_data = null;
        $card->save();

        return back()->with('success', 'Draft changes published successfully!');
    }

    public function discardDraft(Request $request, BusinessCard $card)
    {
        $this->authorize('update', $card);

        if (! $card->draft_data) {
            return back()->with('error', 'No draft changes to discard.');
        }

        // Delete draft images that won't be used
        if (isset($card->draft_data['cover_image_path'])) {
            // Only delete if different from live image
            if ($card->draft_data['cover_image_path'] !== $card->cover_image_path) {
                \Storage::disk('public')->delete($card->draft_data['cover_image_path']);
            }
        }

        if (isset($card->draft_data['profile_image_path'])) {
            // Only delete if different from live image
            if ($card->draft_data['profile_image_path'] !== $card->profile_image_path) {
                \Storage::disk('public')->delete($card->draft_data['profile_image_path']);
            }
        }

        $card->draft_data = null;
        $card->save();

        return back()->with('success', 'Draft changes discarded.');
    }

    public function updateSections(Request $request, BusinessCard $card)
    {
        \Log::info('=== updateSections called ===');
        \Log::info('Card ID: '.$card->id);
        \Log::info('Request method: '.$request->method());
        \Log::info('Request all data: ', $request->all());

        $this->authorize('update', $card);

        \Log::info('Authorization passed');

        try {
            $validated = $request->validate([
                'sections' => 'required|array',
                'sections.*.id' => 'nullable',
                'sections.*.section_type' => 'required|string',
                'sections.*.title' => 'nullable',
                'sections.*.content' => 'required|array',
                'sections.*.order' => 'nullable|integer',
                'sections.*.is_active' => 'nullable|boolean',
            ]);

            \Log::info('Validation passed, sections count: '.count($validated['sections']));
            \Log::info('Validated sections: ', $validated['sections']);

            DB::transaction(function () use ($card, $validated) {
                \Log::info('Deleting old sections for card: '.$card->id);
                $deleted = $card->sections()->delete();
                \Log::info('Deleted sections count: '.$deleted);

                foreach ($validated['sections'] as $index => $sectionData) {
                    \Log::info("Creating section {$index}: ".$sectionData['section_type']);
                    $created = $card->sections()->create([
                        'section_type' => $sectionData['section_type'],
                        'title' => $sectionData['title'] ?? ucfirst($sectionData['section_type']),
                        'content' => $sectionData['content'],
                        'sort_order' => $sectionData['order'] ?? ($index + 1),
                        'is_active' => $sectionData['is_active'] ?? true,
                    ]);
                    \Log::info('Created section ID: '.$created->id);
                }
            });

            \Log::info('=== updateSections completed successfully ===');

            return back()->with('success', 'Sections updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed: ', $e->errors());
            throw $e;
        } catch (\Exception $e) {
            \Log::error('updateSections error: '.$e->getMessage());
            \Log::error($e->getTraceAsString());
            throw $e;
        }
    }
}
