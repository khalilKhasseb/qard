<?php

namespace App\Services;

use App\Models\Language;
use App\Models\TranslationHistory;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class AiLabelTranslationService
{
    public function __construct(
        protected AiTranslationProvider $aiProvider
    ) {}

    /**
     * Translate missing labels from source into target language.
     * Existing target labels are preserved unless $overwrite is true.
     */
    public function translateAndPersist(
        Language $sourceLanguage,
        Language $targetLanguage,
        ?User $user = null,
        bool $overwrite = false
    ): array {
        if ($sourceLanguage->code === $targetLanguage->code) {
            return $targetLanguage->labels ?? [];
        }

        $sourceLabels = is_array($sourceLanguage->labels) ? $sourceLanguage->labels : [];
        $targetLabels = is_array($targetLanguage->labels) ? $targetLanguage->labels : [];

        if (empty($sourceLabels)) {
            throw new \Exception('Source language has no labels.');
        }

        $toTranslate = $overwrite
            ? $sourceLabels
            : array_diff_key($sourceLabels, $targetLabels);

        if (empty($toTranslate)) {
            return $targetLabels;
        }

        $items = collect($toTranslate)->map(fn ($value, $key) => [
            'key' => (string) $key,
            'value' => (string) $value,
        ])->values()->all();

        $schema = new ObjectSchema(
            name: 'label_translation',
            description: 'Translated UI labels',
            properties: [
                new ArraySchema(
                    name: 'labels',
                    description: 'List of translated label key/value pairs',
                    items: new ObjectSchema(
                        name: 'label',
                        description: 'Translated label item',
                        properties: [
                            new StringSchema('key', 'Label key (keep unchanged)'),
                            new StringSchema('value', 'Translated label value'),
                        ],
                        requiredFields: ['key', 'value']
                    )
                ),
            ],
            requiredFields: ['labels']
        );

        $context = 'UI labels for the business card editor. Keep label keys unchanged. Preserve placeholders like :count, {count}, %s.';

        try {
            $result = $this->aiProvider->translate(
                ['labels' => $items],
                $sourceLanguage->code,
                $targetLanguage->code,
                $schema,
                $context
            );

            $translatedItems = $result['translated']['labels'] ?? [];
            $translatedLabels = $this->normalizeTranslatedItems($translatedItems);

            $merged = $overwrite
                ? array_merge($targetLabels, $translatedLabels)
                : ($targetLabels + $translatedLabels);

            $targetLanguage->labels = $merged;
            $targetLanguage->save();

            if ($user) {
                $this->saveTranslationHistory(
                    user: $user,
                    targetLanguage: $targetLanguage,
                    sourceLanguage: $sourceLanguage,
                    sourceLabels: $toTranslate,
                    translatedLabels: $translatedLabels,
                    characterCount: strlen(json_encode($toTranslate, JSON_UNESCAPED_UNICODE)),
                    provider: $result['provider'] ?? 'unknown',
                    model: $result['model'] ?? 'unknown'
                );
            }

            return $merged;
        } catch (\Exception $e) {
            Log::error('AI label translation failed', [
                'source' => $sourceLanguage->code,
                'target' => $targetLanguage->code,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function normalizeTranslatedItems(array $items): array
    {
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $key = $item['key'] ?? null;
            $value = $item['value'] ?? null;
            if (is_string($key) && $key !== '') {
                $out[$key] = is_string($value) ? $value : '';
            }
        }
        return $out;
    }

    protected function saveTranslationHistory(
        User $user,
        Language $targetLanguage,
        Language $sourceLanguage,
        array $sourceLabels,
        array $translatedLabels,
        int $characterCount,
        string $provider,
        string $model
    ): void {
        TranslationHistory::create([
            'user_id' => $user->id,
            'business_card_id' => null,
            'translatable_type' => Language::class,
            'translatable_id' => $targetLanguage->id,
            'source_language' => $sourceLanguage->code,
            'target_language' => $targetLanguage->code,
            'source_text' => json_encode($sourceLabels, JSON_UNESCAPED_UNICODE),
            'translated_text' => json_encode($translatedLabels, JSON_UNESCAPED_UNICODE),
            'translation_method' => 'auto',
            'provider' => $provider,
            'model' => $model,
            'character_count' => $characterCount,
            'credits_used' => 0,
            'verification_status' => 'pending',
            'metadata' => [
                'type' => 'ui_labels',
                'label_count' => count($translatedLabels),
            ],
        ]);
    }
}
