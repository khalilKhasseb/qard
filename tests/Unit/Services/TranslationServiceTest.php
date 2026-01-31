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

        // Set up expectations in order with once() to ensure sequential consumption
        $aiMock->shouldReceive('translate')
            ->once()
            ->andReturn(['success' => true, 'translated' => ['translated_text' => 'مرحبا']]);
        $aiMock->shouldReceive('translate')
            ->once()
            ->andReturn(['success' => true, 'translated' => 'שלום עולם']);
        $aiMock->shouldReceive('translate')
            ->once()
            ->andReturn(['success' => true, 'translated' => ['status' => 'error']]);

        // Case 1: provider returns structured translated_text
        $res = $service->translateTitle('Hello', 'en', 'ar');
        $this->assertEquals('مرحبا', $res);

        // Case 2: provider returns a plain string
        $res2 = $service->translateTitle('Hello', 'en', 'he');
        $this->assertEquals('שלום עולם', $res2);

        // Case 3: provider returns only excluded keys -> should fallback to original
        $res3 = $service->translateTitle('Hello', 'en', 'ar');
        $this->assertEquals('Hello', $res3);
    }
}
