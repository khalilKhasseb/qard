<?php

namespace Tests\Unit\Services;

use App\Services\AiTranslationProvider;
use App\Services\TranslationSchemaFactory;
use App\Services\TranslationService;
use Mockery;
use PHPUnit\Framework\TestCase;

class TranslationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_translate_title_with_various_provider_responses()
    {
        $aiMock = Mockery::mock(AiTranslationProvider::class);
        $schemaFactory = Mockery::mock(TranslationSchemaFactory::class);

        $service = new TranslationService($aiMock, $schemaFactory);

        // Case 1: provider returns structured translated_text
        $aiMock->shouldReceive('translate')->andReturn(['success' => true, 'translated' => ['translated_text' => 'مرحبا']]);
        $res = $service->translateTitle('Hello', 'en', 'ar');
        $this->assertEquals('مرحبا', $res);

        // Case 2: provider returns a plain string
        $aiMock->shouldReceive('translate')->andReturn(['success' => true, 'translated' => 'שלום עולם']);
        $res2 = $service->translateTitle('Hello', 'en', 'he');
        $this->assertEquals('שלום עולם', $res2);

        // Case 3: provider returns only URL field -> should fallback to original
        $aiMock->shouldReceive('translate')->andReturn(['success' => true, 'translated' => ['facebook' => 'https://www.facebook.com/share/1AgNaxBNLn/']]);
        $res3 = $service->translateTitle('Hello', 'en', 'ar');
        $this->assertEquals('Hello', $res3);
    }
}
