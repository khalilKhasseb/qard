<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\IntegerSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\ValueObjects\Messages\UserMessage;

class AiTranslationProvider
{
    protected string $provider;

    protected string $model;

    protected int $maxRetries;

    protected int $retryDelay;

    public function __construct()
    {
        $this->provider = Provider::OpenRouter->value;
        $this->model = config('prism.default_translation_model', 'google/gemini-2.0-flash-exp:free');
        $this->maxRetries = 3;
        $this->retryDelay = 100; // milliseconds
    }

    /**
     * Translate content using AI with structured output.
     *
     * @param  string|null  $context  Additional context for better translation
     *
     * @throws \Exception
     */
    public function translate(
        array|string $content,
        string $sourceLang,
        string $targetLang,
        ObjectSchema $schema,
        ?string $context = null
    ): array {
        $prompt = $this->buildTranslationPrompt($content, $sourceLang, $targetLang, $context);

        try {
            $response = Prism::structured()
                ->using(Provider::OpenRouter, $this->model)
                ->withSchema($schema)
                ->withMessages([new UserMessage($prompt)])
                ->withClientRetry($this->maxRetries, $this->retryDelay)
                ->asStructured();

            Log::info('Translation completed', [
                'source_lang' => $sourceLang,
                'target_lang' => $targetLang,
                'model' => $this->model,
                'character_count' => strlen(json_encode($content)),
            ]);

            // Validate and clean the response
            $translatedContent = $this->validateAndCleanResponse($response->structured, $content);

            return [
                'success' => true,
                'translated' => $translatedContent,
                'usage' => $response->usage ?? null,
                'model' => $this->model,
                'provider' => $this->provider,
            ];
        } catch (\Exception $e) {
            Log::error('Translation failed', [
                'error' => $e->getMessage(),
                'source_lang' => $sourceLang,
                'target_lang' => $targetLang,
                'model' => $this->model,
            ]);

            throw new \Exception("Translation failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Build translation prompt with context.
     */
    protected function buildTranslationPrompt(
        array|string $content,
        string $sourceLang,
        string $targetLang,
        ?string $context = null
    ): string {
        $contentJson = is_array($content) ? json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $content;

        $prompt = "Translate the following content from {$sourceLang} to {$targetLang}.\n\n";

        // Add context if provided (e.g., business card title)
        if ($context) {
            $prompt .= "Context: {$context}\n\n";
        }

        $prompt .= "IMPORTANT INSTRUCTIONS:\n";
        $prompt .= "- Translate ONLY the text content, not technical values\n";
        $prompt .= "- Preserve all formatting, line breaks, and special characters\n";
        $prompt .= "- Keep URLs, emails, phone numbers, and prices UNCHANGED\n";
        $prompt .= "- Maintain the same JSON structure\n";
        $prompt .= "- Be culturally appropriate for the target language\n";
        $prompt .= "- Use natural, fluent language in the target language\n";
        $prompt .= "- For RTL languages (Arabic, Hebrew, Persian, Urdu), ensure proper text direction\n\n";

        // Strict JSON-only instruction to reduce malformed responses
        $prompt .= "IMPORTANT OUTPUT FORMAT:\n";
        $prompt .= "- Return ONLY a single valid JSON object that matches the requested schema. Do NOT include any explanatory text, notes, or extra commentary.\n";
        $prompt .= "- If there is nothing to translate or the content is non-translatable (e.g., only URLs), return an empty object: {}\n";
        $prompt .= "Example: {\"text\": \"translated text\"}\n\n";

        $prompt .= "Content to translate:\n{$contentJson}";

        return $prompt;
    }

    /**
     * Verify translation quality using AI.
     *
     * @return array ['score' => int, 'feedback' => string]
     */
    public function verifyQuality(
        string $sourceText,
        string $translatedText,
        string $sourceLang,
        string $targetLang
    ): array {
        $prompt = "You are a professional translation quality assessor.\n\n";
        $prompt .= "Evaluate the following translation from {$sourceLang} to {$targetLang}.\n\n";
        $prompt .= "Source text:\n{$sourceText}\n\n";
        $prompt .= "Translated text:\n{$translatedText}\n\n";
        $prompt .= "Rate the translation quality from 0-100 based on:\n";
        $prompt .= "- Accuracy (40%): Meaning preserved\n";
        $prompt .= "- Fluency (30%): Natural in target language\n";
        $prompt .= "- Cultural appropriateness (20%): Culturally sensitive\n";
        $prompt .= "- Completeness (10%): Nothing omitted or added\n\n";
        $prompt .= 'Provide a score (integer 0-100) and brief feedback.';

        try {
            $schema = new ObjectSchema(
                name: 'quality_assessment',
                description: 'Translation quality assessment',
                properties: [
                    new IntegerSchema('score', 'Quality score from 0-100'),
                    new StringSchema('feedback', 'Brief feedback on translation quality'),
                ],
                requiredFields: ['score', 'feedback']
            );

            $response = Prism::structured()
                ->using(Provider::OpenRouter, $this->model)
                ->withSchema($schema)
                ->withMessages([new UserMessage($prompt)])
                ->withClientRetry($this->maxRetries, $this->retryDelay)
                ->asStructured();

            return [
                'score' => $response->structured['score'] ?? 75,
                'feedback' => $response->structured['feedback'] ?? 'Translation completed',
            ];
        } catch (\Exception $e) {
            Log::error('Quality verification failed', [
                'error' => $e->getMessage(),
            ]);

            // Return default score on error
            return [
                'score' => 75,
                'feedback' => 'Quality verification unavailable',
            ];
        }
    }

    /**
     * Set custom model.
     */
    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get current model.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Validate and clean the response from AI model.
     * Some models return the full prompt structure instead of just the translated content.
     */
    protected function validateAndCleanResponse($responseData, $originalContent): array|string
    {
        // Log the raw response for debugging
        Log::debug('Raw AI response', [
            'response' => $responseData,
            'original' => $originalContent,
            'response_type' => gettype($responseData),
        ]);

        // Use the new parser to robustly extract structured content or text
        try {
            $parseResult = \App\Services\AiResponseParser::parseResponse($responseData, $originalContent);
            Log::debug('Parsed AI response', [
                'status' => $parseResult['status'],
                'snippet' => is_string($parseResult['data']) ? substr($parseResult['data'], 0, 200) : null,
            ]);

            switch ($parseResult['status']) {
                case 'ok':
                    return $parseResult['data'];

                case 'only_urls':
                    // Nothing to translate - keep original structured content
                    Log::info('Translation contained only URLs or non-translatable values; keeping original content', [
                        'original_preview' => is_array($originalContent) ? json_encode(array_slice($originalContent, 0, 5)) : substr((string) $originalContent, 0, 200),
                    ]);

                    return $originalContent;

                case 'no_content':
                    Log::info('Translation returned no content; keeping original', []);

                    return $originalContent;

                case 'text_fallback':
                    // For simple text translations, return wrapped text
                    if (is_array($originalContent)) {
                        // original was structured -> nothing to translate
                        return $originalContent;
                    }

                    return $parseResult['data'];

                case 'unparseable':
                default:
                    // Log detailed info and keep original to avoid breaking the flow
                    Log::warning('Structured object could not be decoded. Received: '.print_r($parseResult['data'], true));

                    return $originalContent;
            }
        } catch (\Throwable $e) {
            Log::error('Error parsing AI response', ['error' => $e->getMessage()]);
            // On parser error, fallback to original response data handling to avoid interruption
            if (is_array($responseData) || is_object($responseData)) {
                return is_object($responseData) ? (array) $responseData : $responseData;
            }

            return is_string($responseData) ? $responseData : $originalContent;
        }
    }
}
