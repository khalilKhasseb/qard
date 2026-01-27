<?php

namespace App\Services;

use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Models\Language;
use App\Models\TranslationHistory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class TranslationService
{
    protected AiTranslationProvider $aiProvider;

    protected TranslationSchemaFactory $schemaFactory;

    public function __construct(
        AiTranslationProvider $aiProvider,
        TranslationSchemaFactory $schemaFactory
    ) {
        $this->aiProvider = $aiProvider;
        $this->schemaFactory = $schemaFactory;
    }

    /**
     * Translate a card section to target language.
     *
     * @throws \Exception
     */
    public function translateCardSection(
        CardSection $section,
        string $targetLanguageCode,
        User $user,
        array $options = []
    ): array {
        // Check if user has credits
        if (! $user->hasTranslationCredits(1)) {
            throw new \Exception('Insufficient translation credits');
        }

        // Validate target language
        $targetLanguage = Language::where('code', $targetLanguageCode)->where('is_active', true)->first();
        if (! $targetLanguage) {
            throw new \Exception('Invalid target language');
        }

        // Get source language
        $sourceLanguageCode = $section->businessCard->language->code ?? 'en';

        // Skip if already translating from same to same language
        if ($sourceLanguageCode === $targetLanguageCode) {
            return [
                'success' => false,
                'message' => 'Source and target languages are the same',
            ];
        }

        // Get source content
        $sourceContent = $section->content[$sourceLanguageCode] ?? $section->content;

        // Check cache first
        $cacheKey = $this->getCacheKey($sourceContent, $sourceLanguageCode, $targetLanguageCode, $section->section_type);
        $cachedTranslation = Cache::get($cacheKey);

        // Always translate section title if it exists and is not empty
        $currentTitle = $section->title;
        if ($currentTitle && ! empty($currentTitle)) {
            try {
                // Ensure title is an array
                $decoded = json_decode((string) $currentTitle, true);
                if (is_array($currentTitle)) {
                    $titleData = $currentTitle;
                } elseif ($decoded !== null) {
                    $titleData = $decoded;
                } else {
                    $titleData = [$sourceLanguageCode => $currentTitle];
                }

                // If it's a string, we already handled it above, but let's be safe
                if (is_string($currentTitle)) {
                    $titleData = [$sourceLanguageCode => $currentTitle];
                }

                $sourceTitle = $titleData[$sourceLanguageCode] ?? null;

                // If source title not found, try to find any string in the array as fallback
                if (! $sourceTitle && is_array($titleData)) {
                    foreach ($titleData as $lang => $val) {
                        if (is_string($val) && ! empty($val) && ! str_starts_with(trim($val), '{')) {
                            $sourceTitle = $val;
                            // Clean up legacy/incorrect structure
                            $titleData = [$sourceLanguageCode => $sourceTitle];
                            break;
                        }
                    }
                }

                // Only proceed if we have a valid source title that's not already translated to target
                if ($sourceTitle && trim($sourceTitle) !== '' && ! str_starts_with(trim($sourceTitle), '{') && ! isset($titleData[$targetLanguageCode])) {
                    // Build context for title translation
                    $context = $this->buildContext($section->businessCard);
                    $titleSchema = $this->schemaFactory->getSchemaForSectionType('text');
                    $titleResult = $this->aiProvider->translate(
                        ['text' => $sourceTitle],
                        $sourceLanguageCode,
                        $targetLanguageCode,
                        $titleSchema,
                        $context
                    );

                    if ($titleResult['success'] && isset($titleResult['translated']['text'])) {
                        $translatedTitle = $titleResult['translated']['text'];
                        $titleData[$targetLanguageCode] = $translatedTitle;
                        $section->title = $titleData;
                        $section->save();
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Section title translation failed', [
                    'section_id' => $section->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($cachedTranslation) {
            // Still deduct credits even for cached translations
            $user->deductTranslationCredits(1);
            $this->updateSectionContent($section, $targetLanguageCode, $cachedTranslation);

            if (isset($options['on_progress'])) {
                $options['on_progress']();
            }

            return [
                'success' => true,
                'translated_content' => $cachedTranslation,
                'cached' => true,
            ];
        }

        // Build context from business card
        $context = $this->buildContext($section->businessCard);

        // Get schema for section type
        $schema = $this->schemaFactory->getSchemaForSectionType($section->section_type);

        try {
            // Translate using AI
            $result = $this->aiProvider->translate(
                $sourceContent,
                $sourceLanguageCode,
                $targetLanguageCode,
                $schema,
                $context
            );

            if (! $result['success']) {
                throw new \Exception('Translation failed');
            }

            $translatedContent = $result['translated'];
            $characterCount = strlen(json_encode($sourceContent));

            // Deduct credits
            $user->deductTranslationCredits(1);

            // Update section content
            $this->updateSectionContent($section, $targetLanguageCode, $translatedContent);

            // Cache the translation (7 days)
            Cache::put($cacheKey, $translatedContent, now()->addDays(7));

            // Save translation history
            $history = $this->saveTranslationHistory(
                $user,
                $section,
                $sourceLanguageCode,
                $targetLanguageCode,
                $sourceContent,
                $translatedContent,
                $characterCount,
                $result['model'] ?? 'unknown',
                $result['provider'] ?? 'unknown'
            );

            if (isset($options['on_progress'])) {
                $options['on_progress']();
            }

            return [
                'success' => true,
                'translated_content' => $translatedContent,
                'cached' => false,
                'history_id' => $history->id,
                'credits_remaining' => $user->getRemainingTranslationCredits(),
            ];
        } catch (\Exception $e) {
            Log::error('Translation failed', [
                'section_id' => $section->id,
                'target_lang' => $targetLanguageCode,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Translate entire business card to target language.
     *
     * @throws \Exception
     */
    public function translateBusinessCard(
        BusinessCard $card,
        string $targetLanguageCode,
        User $user,
        array $options = []
    ): array {
        // Get all translatable sections
        $sections = $card->sections()
            ->whereNotIn('section_type', ['gallery', 'qr_code'])
            ->get();

        $totalSections = $sections->count() + 1; // +1 for title/subtitle
        $completedSections = 0;

        $requiredCredits = $totalSections;

        if (! $user->hasTranslationCredits($requiredCredits)) {
            throw new \Exception("Insufficient credits. Need {$requiredCredits}, have {$user->getRemainingTranslationCredits()}");
        }

        $results = [
            'card_id' => $card->id,
            'target_language' => $targetLanguageCode,
            'sections_translated' => 0,
            'sections_failed' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            $progressCallback = function () use (&$completedSections, $totalSections, $options) {
                $completedSections++;
                if (isset($options['on_progress'])) {
                    $options['on_progress']($completedSections, $totalSections);
                }
            };

            // Translate card title and subtitle
            $this->translateCardMeta($card, $targetLanguageCode, $user);
            $progressCallback();

            // Translate each section
            foreach ($sections as $section) {
                try {
                    $this->translateCardSection($section, $targetLanguageCode, $user, [
                        'on_progress' => $progressCallback,
                    ]);
                    $results['sections_translated']++;
                } catch (\Exception $e) {
                    $results['sections_failed']++;
                    $results['errors'][] = [
                        'section_id' => $section->id,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            return [
                'success' => true,
                'results' => $results,
                'credits_remaining' => $user->getRemainingTranslationCredits(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Translate card title and subtitle.
     */
    protected function translateCardMeta(
        BusinessCard $card,
        string $targetLanguageCode,
        User $user
    ): void {
        $sourceLanguageCode = $card->language->code ?? 'en';

        if ($sourceLanguageCode === $targetLanguageCode) {
            return;
        }

        // Get source title and subtitle
        $sourceTitle = $card->title[$sourceLanguageCode] ?? null;
        $sourceSubtitle = $card->subtitle[$sourceLanguageCode] ?? null;

        if (! $sourceTitle && ! $sourceSubtitle) {
            return;
        }

        // Build schema for simple text
        $schema = $this->schemaFactory->getSchemaForSectionType('text');

        // Translate title
        if ($sourceTitle) {
            $result = $this->aiProvider->translate(
                ['text' => $sourceTitle],
                $sourceLanguageCode,
                $targetLanguageCode,
                $schema
            );

            $title = $card->title ?? [];
            $title[$targetLanguageCode] = $result['translated']['text'];
            $card->title = $title;
        }

        // Translate subtitle
        if ($sourceSubtitle) {
            $result = $this->aiProvider->translate(
                ['text' => $sourceSubtitle],
                $sourceLanguageCode,
                $targetLanguageCode,
                $schema
            );

            $subtitle = $card->subtitle ?? [];
            $subtitle[$targetLanguageCode] = $result['translated']['text'];
            $card->subtitle = $subtitle;
        }

        $card->save();

        // Save history for title/subtitle
        $this->saveTranslationHistory(
            $user,
            $card,
            $sourceLanguageCode,
            $targetLanguageCode,
            ['title' => $sourceTitle, 'subtitle' => $sourceSubtitle],
            ['title' => $card->title[$targetLanguageCode] ?? null, 'subtitle' => $card->subtitle[$targetLanguageCode] ?? null],
            strlen($sourceTitle.$sourceSubtitle),
            $result['model'] ?? 'unknown',
            $result['provider'] ?? 'unknown'
        );

        // Deduct credit
        $user->deductTranslationCredits(1);
    }

    /**
     * Update section content with translation.
     */
    protected function updateSectionContent(
        CardSection $section,
        string $targetLanguageCode,
        array $translatedContent
    ): void {
        $content = $section->content ?? [];
        $content[$targetLanguageCode] = $translatedContent;
        $section->content = $content;
        $section->save();
    }

    /**
     * Build context from business card for better translations.
     */
    protected function buildContext(BusinessCard $card): string
    {
        $context = '';

        $sourceLanguageCode = $card->language->code ?? 'en';

        if (isset($card->title[$sourceLanguageCode])) {
            $context .= "Business Card: {$card->title[$sourceLanguageCode]}";
        }

        if (isset($card->subtitle[$sourceLanguageCode])) {
            $context .= " - {$card->subtitle[$sourceLanguageCode]}";
        }

        return $context;
    }

    /**
     * Get cache key for translation.
     */
    protected function getCacheKey(
        mixed $content,
        string $sourceLang,
        string $targetLang,
        string $sectionType
    ): string {
        $contentHash = md5(json_encode($content));

        return "translation:{$sourceLang}:{$targetLang}:{$sectionType}:{$contentHash}";
    }

    /**
     * Save translation history.
     */
    protected function saveTranslationHistory(
        User $user,
        mixed $translatable,
        string $sourceLang,
        string $targetLang,
        mixed $sourceText,
        mixed $translatedText,
        int $characterCount,
        string $model,
        string $provider
    ): TranslationHistory {
        $cardId = null;
        if ($translatable instanceof \App\Models\CardSection) {
            $cardId = $translatable->business_card_id;
        } elseif ($translatable instanceof \App\Models\BusinessCard) {
            $cardId = $translatable->id;
        }

        return TranslationHistory::create([
            'user_id' => $user->id,
            'business_card_id' => $cardId,
            'translatable_type' => get_class($translatable),
            'translatable_id' => $translatable->id,
            'source_language' => $sourceLang,
            'target_language' => $targetLang,
            'source_text' => is_array($sourceText) ? json_encode($sourceText) : $sourceText,
            'translated_text' => is_array($translatedText) ? json_encode($translatedText) : $translatedText,
            'translation_method' => 'auto',
            'provider' => $provider,
            'model' => $model,
            'character_count' => $characterCount,
            'credits_used' => 1,
            'verification_status' => 'pending',
        ]);
    }

    /**
     * Translate a single text string
     */
    public function translateTitle($text, $fromLanguage, $toLanguage)
    {
        if (empty($text)) {
            return '';
        }

        // Create a simple schema for single text translation
        $schema = new ObjectSchema(
            'translation_result',
            'Simple text translation result',
            [
                'translated_text' => new StringSchema(
                    'translated_text',
                    'The translated text'
                ),
            ],
            ['translated_text']
        );

        try {
            $result = $this->aiProvider->translate($text, $fromLanguage, $toLanguage, $schema, 'section title');

            // Handle various response formats that the AI might return
            $translatedText = null;
            if (isset($result['translated']['translated_text'])) {
                $translatedText = $result['translated']['translated_text'];
            } elseif (isset($result['translated']) && is_string($result['translated'])) {
                $translatedText = $result['translated'];
            } elseif (isset($result['translated']) && is_array($result['translated'])) {
                // Try various common response keys
                $responseData = $result['translated'];
                $possibleKeys = [
                    'translated_text', 'text', 'translation', 'result', 'content',
                    'translated', 'ar', $toLanguage, $text, 'title', 'header',
                ];

                foreach ($possibleKeys as $key) {
                    if (isset($responseData[$key]) && is_string($responseData[$key]) && ! empty(trim($responseData[$key]))) {
                        $translatedText = $responseData[$key];
                        break;
                    }
                }

                // If still not found, try the first string value
                if (! $translatedText) {
                    foreach ($responseData as $key => $value) {
                        if (is_string($value) && ! empty(trim($value)) &&
                            ! in_array($key, ['original', 'note', 'type', 'status', 'model'])) {
                            $translatedText = $value;
                            break;
                        }
                    }
                }
            }

            // Return translated text or fallback to original
            return $translatedText ?: $text;

        } catch (\Exception $e) {
            Log::error('Failed to translate title', [
                'text' => $text,
                'from' => $fromLanguage,
                'to' => $toLanguage,
                'error' => $e->getMessage(),
            ]);

            return $text; // Return original text if translation fails
        }
    }

    /**
     * Get available target languages for a card.
     */
    public function getAvailableLanguages(BusinessCard $card): array
    {
        $currentLang = $card->language?->code ?? config('app.locale', 'en');

        return Language::where('is_active', true)
            ->where('code', '!=', $currentLang)
            ->orderBy('name')
            ->get()
            ->map(fn ($lang) => [
                'code' => $lang->code,
                'name' => $lang->name,
                'direction' => $lang->direction,
            ])
            ->toArray();
    }
}
