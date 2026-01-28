<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBulkTranslation;
use App\Jobs\VerifyTranslationQuality;
use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Models\TranslationHistory;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TranslationController extends Controller
{
    public function __construct(
        protected TranslationService $translationService
    ) {}

    /**
     * Translate a single card section.
     */
    public function translateSection(Request $request, CardSection $section): JsonResponse
    {
        $validated = $request->validate([
            'target_language' => 'required|string|size:2|exists:languages,code',
        ]);

        $user = $request->user();

        // Check if feature is enabled for plan
        if (! $user->hasTranslationFeature()) {
            return response()->json([
                'success' => false,
                'message' => 'AI Translation is not included in your current plan.',
            ], 403);
        }

        // Check authorization
        if ($section->businessCard->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check credits
        if (! $user->hasTranslationCredits(1)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient translation credits',
                'credits_remaining' => 0,
            ], 402);
        }

        try {
            $result = $this->translationService->translateCardSection(
                $section,
                $validated['target_language'],
                $user
            );

            // Queue quality verification if not cached
            if (! ($result['cached'] ?? false) && isset($result['history_id'])) {
                VerifyTranslationQuality::dispatch($result['history_id'])
                    ->delay(now()->addSeconds(5));
            }

            return response()->json([
                'success' => true,
                'message' => 'Section translated successfully',
                'data' => [
                    'translated_content' => $result['translated_content'],
                    'cached' => $result['cached'] ?? false,
                    'credits_remaining' => $result['credits_remaining'] ?? $user->getRemainingTranslationCredits(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Translation failed', [
                'section_id' => $section->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Translate entire business card.
     */
    public function translateCard(Request $request, BusinessCard $card): JsonResponse
    {
        $validated = $request->validate([
            'target_languages' => 'required|array|min:1',
            'target_languages.*' => 'required|string|size:2|exists:languages,code',
            'async' => 'boolean',
        ]);

        $user = $request->user();

        // Check if feature is enabled for plan
        if (! $user->hasTranslationFeature()) {
            return response()->json([
                'success' => false,
                'message' => 'AI Translation is not included in your current plan.',
            ], 403);
        }

        // Check authorization
        if ($card->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $targetLanguages = $validated['target_languages'];
        $async = $validated['async'] ?? true;

        // Calculate required credits
        $sectionsCount = $card->sections()
            ->whereNotIn('section_type', ['gallery', 'qr_code'])
            ->count();
        $requiredCredits = ($sectionsCount + 1) * count($targetLanguages); // +1 for title/subtitle

        if (! $user->hasTranslationCredits($requiredCredits)) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient credits. Need {$requiredCredits}, have {$user->getRemainingTranslationCredits()}",
                'credits_required' => $requiredCredits,
                'credits_available' => $user->getRemainingTranslationCredits(),
            ], 402);
        }

        // Process async or sync
        if ($async) {
            // Queue the translation job
            ProcessBulkTranslation::dispatch($card->id, $targetLanguages, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Translation queued and will be processed in the background',
                'async' => true,
                'languages' => $targetLanguages,
            ]);
        } else {
            try {
                $result = $this->translationService->translateBusinessCard(
                    $card,
                    $targetLanguages[0], // Only first language for sync
                    $user
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Card translated successfully',
                    'data' => $result,
                ]);
            } catch (\Exception $e) {
                Log::error('Translation failed', [
                    'card_id' => $card->id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
        }
    }

    /**
     * Get available languages for translation.
     */
    public function availableLanguages(BusinessCard $card): JsonResponse
    {
        $user = request()->user();

        // Check authorization
        if ($card->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $languages = $this->translationService->getAvailableLanguages($card);

        return response()->json([
            'success' => true,
            'languages' => $languages,
        ]);
    }

    /**
     * Get translation history for user.
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $history = TranslationHistory::forUser($user->id)
            ->with(['businessCard', 'translatable', 'verifier'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get translation history for specific card.
     */
    public function cardHistory(Request $request, BusinessCard $card): JsonResponse
    {
        $user = $request->user();
        // Check if feature is enabled for plan
        if (! $user->hasTranslationFeature()) {
            return response()->json([
                'success' => false,
                'message' => 'AI Translation is not included in your current plan.',
            ], 403);
        }
        // Check authorization
        if ($card->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $history = TranslationHistory::forCard($card->id)
            ->with(['translatable', 'verifier'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get user's translation credits and usage.
     */
    public function credits(Request $request): JsonResponse
    {
        $user = $request->user();

        $usage = $user->currentTranslationUsage()->first();
        $hasFeature = $user->hasTranslationFeature();

        return response()->json([
            'success' => true,
            'data' => [
                'has_feature' => $hasFeature,
                'credits_remaining' => $user->getRemainingTranslationCredits(),
                'credits_limit' => $user->getTranslationCreditLimit(),
                'unlimited' => $user->hasUnlimitedTranslations(),
                'usage' => $usage ? [
                    'credits_used' => $usage->credits_used,
                    'total_translations' => $usage->total_translations,
                    'period_start' => $usage->period_start,
                    'period_end' => $usage->period_end,
                    'usage_percentage' => $usage->usage_percentage,
                ] : null,
            ],
        ]);
    }

    /**
     * Manually verify a translation.
     */
    public function verifyTranslation(Request $request, TranslationHistory $translation): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        // Check authorization
        if ($translation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $translation->markAsVerified($user->id, $validated['status']);

        if (isset($validated['feedback'])) {
            $metadata = $translation->metadata ?? [];
            $metadata['user_feedback'] = $validated['feedback'];
            $translation->metadata = $metadata;
            $translation->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Translation status updated',
            'data' => $translation,
        ]);
    }
}
