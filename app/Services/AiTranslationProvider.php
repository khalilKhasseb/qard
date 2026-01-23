<?php

namespace App\Services;

use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\IntegerSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Illuminate\Support\Facades\Log;

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
     * @param array|string $content
     * @param string $sourceLang
     * @param string $targetLang
     * @param ObjectSchema $schema
     * @param string|null $context Additional context for better translation
     * @return array
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

        $prompt .= "Content to translate:\n{$contentJson}";

        return $prompt;
    }

    /**
     * Verify translation quality using AI.
     *
     * @param string $sourceText
     * @param string $translatedText
     * @param string $sourceLang
     * @param string $targetLang
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
        $prompt .= "Provide a score (integer 0-100) and brief feedback.";

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

        // If response is a string, return as is
        if (is_string($responseData)) {
            return $responseData;
        }

        // If response is an array/object, check if it's the expected format
        if (is_array($responseData) || is_object($responseData)) {
            $data = is_object($responseData) ? (array) $responseData : $responseData;
            
            // Check if this looks like the full prompt structure that some models return
            if (isset($data['header']) && isset($data['body']) && isset($data['footer'])) {
                // This is the full prompt structure - extract the translated content
                if (isset($data['body']['content_to_translate'])) {
                    $extractedContent = $data['body']['content_to_translate'];
                    
                    // If original was a string (like for text sections), return the string
                    if (is_string($originalContent)) {
                        return is_string($extractedContent) ? $extractedContent : json_encode($extractedContent);
                    }
                    
                    return $extractedContent;
                }
            }
            
            // Check if this is already in our expected schema format
            if (is_array($originalContent)) {
                // For structured content (arrays/objects), return the response as is
                return $data;
            } else {
                // For simple text content, we need to return in the expected format
                if (isset($data['text'])) {
                    return ['text' => $data['text']];
                }
                
                // Look for any string field that might contain the translation
                foreach (['content', 'translated_text', 'translation', 'result'] as $field) {
                    if (isset($data[$field]) && is_string($data[$field])) {
                        return ['text' => $data[$field]];
                    }
                }
                
                // Find the first non-empty string value in the response
                foreach ($data as $key => $value) {
                    if (is_string($value) && !empty(trim($value)) && !in_array($key, ['type', 'status', 'model'])) {
                        return ['text' => $value];
                    }
                }
                
                // If still no luck, try to extract any readable content
                if (count($data) === 1) {
                    $firstValue = reset($data);
                    if (is_string($firstValue)) {
                        return ['text' => $firstValue];
                    }
                }
            }
        }

        // Last resort: convert to string if it's an object/array
        if (is_array($responseData) || is_object($responseData)) {
            Log::warning('Could not parse AI response properly, converting to JSON', [
                'response' => $responseData
            ]);
            return json_encode($responseData);
        }

        // Fallback: return the response as is
        return $responseData;
    }
}
