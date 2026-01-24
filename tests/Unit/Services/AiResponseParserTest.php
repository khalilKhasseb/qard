<?php

namespace Tests\Unit\Services;

use App\Services\AiResponseParser;
use PHPUnit\Framework\TestCase;

class AiResponseParserTest extends TestCase
{
    public function test_extracts_json_block_with_extra_text()
    {
        $raw = "Here is the result:\n{\"text\":\"Translated text\"}\nNote: no further comments";

        $json = AiResponseParser::extractJsonBlock($raw);
        $this->assertNotNull($json);
        $decoded = AiResponseParser::decodeJsonSafely($json);
        $this->assertIsArray($decoded);
        $this->assertEquals('Translated text', $decoded['text']);
    }

    public function test_detects_only_urls()
    {
        $obj = ['facebook' => 'https://www.facebook.com/share/1AgNaxBNLn/'];
        $this->assertTrue(AiResponseParser::valuesAreOnlyUrls($obj));

        $result = AiResponseParser::parseResponse(json_encode($obj), []);
        $this->assertEquals('only_urls', $result['status']);
    }

    public function test_text_fallback_for_plain_translation()
    {
        $raw = "اختبار"; // Arabic short translation
        $result = AiResponseParser::parseResponse($raw, 'Test');
        $this->assertEquals('text_fallback', $result['status']);
        $this->assertEquals(['text' => 'اختبار'], $result['data']);
    }

    public function test_unparseable_response_returns_unparseable()
    {
        $raw = "\x00\x01\x02"; // garbage
        // For structured original content (array) we expect no_content (keep original)
        $result = AiResponseParser::parseResponse($raw, []);
        $this->assertEquals('no_content', $result['status']);

        // For string original we expect unparseable
        $result2 = AiResponseParser::parseResponse($raw, 'Test');
        $this->assertEquals('unparseable', $result2['status']);
    }
}
