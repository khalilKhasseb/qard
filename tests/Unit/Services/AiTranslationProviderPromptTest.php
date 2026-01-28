<?php

namespace Tests\Unit\Services;

use App\Services\AiTranslationProvider;
use PHPUnit\Framework\TestCase;

class AiTranslationProviderPromptTest extends TestCase
{
    public function test_build_translation_prompt_requires_json()
    {
        $ref = new \ReflectionClass(AiTranslationProvider::class);
        // Avoid calling constructor to prevent config() dependency in unit test
        $provider = $ref->newInstanceWithoutConstructor();
        $method = $ref->getMethod('buildTranslationPrompt');
        $method->setAccessible(true);

        $prompt = $method->invoke($provider, ['text' => 'Hello'], 'en', 'ar', null);

        $this->assertStringContainsString('Return ONLY a single valid JSON object', $prompt);
        $this->assertStringContainsString('If there is nothing to translate', $prompt);
        $this->assertStringContainsString('{"text": "translated text"}', $prompt);
    }
}
