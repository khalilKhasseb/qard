<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AiResponseParser
{
    /**
     * Attempt to extract a JSON object block from raw text.
     * Returns null if none found.
     */
    public static function extractJsonBlock(string $text): ?string
    {
        // Remove common code fences
        $text = preg_replace('/```json\s*/i', '{', $text, 1);
        $text = preg_replace('/```/i', '', $text);

        // Find first balanced JSON object
        $start = strpos($text, '{');
        if ($start === false) return null;

        $depth = 0;
        $len = strlen($text);
        for ($i = $start; $i < $len; $i++) {
            $char = $text[$i];
            if ($char === '{') $depth++;
            if ($char === '}') $depth--;
            if ($depth === 0) {
                $json = substr($text, $start, $i - $start + 1);
                return $json;
            }
        }

        // Fallback: first {...} via regex
        if (preg_match('/\{[\s\S]*?\}/', $text, $m)) {
            return $m[0];
        }

        return null;
    }

    /**
     * Try decode JSON safely with a few recovery attempts.
     */
    public static function decodeJsonSafely(string $jsonText): ?array
    {
        // 1. direct decode
        $data = json_decode($jsonText, true);
        if (json_last_error() === JSON_ERROR_NONE) return $data;

        // 2. try to unescape common escaped sequences
        $attempt = str_replace('\\\"', '"', $jsonText);
        $data = json_decode($attempt, true);
        if (json_last_error() === JSON_ERROR_NONE) return $data;

        // 3. remove non-printable characters
        $attempt = preg_replace('/[[:^print:]]+/', '', $jsonText);
        $data = json_decode($attempt, true);
        if (json_last_error() === JSON_ERROR_NONE) return $data;

        // 4. try to fix simple trailing commas
        $attempt = preg_replace('/,\s*\}/', '}', $jsonText);
        $attempt = preg_replace('/,\s*\]/', ']', $attempt);
        $data = json_decode($attempt, true);
        if (json_last_error() === JSON_ERROR_NONE) return $data;

        return null;
    }

    /**
     * Determine if the provided object values are only URLs or empty.
     */
    public static function valuesAreOnlyUrls(array $obj): bool
    {
        foreach ($obj as $v) {
            if (is_null($v) || $v === '') continue;
            if (is_string($v) && preg_match('#^https?://#i', trim($v))) continue;
            return false;
        }
        return true;
    }

    /**
     * Main parser entry point. Returns array with status and data.
     * status: ok | no_content | only_urls | unparseable | text_fallback
     */
    public static function parseResponse(mixed $responseData, mixed $original): array
    {
        // If already array/object, normalize
        if (is_array($responseData)) {
            $data = $responseData;
            // If empty
            if (empty($data)) {
                return ['status' => 'no_content', 'data' => $original];
            }

            // If values are only urls, treat as no_content
            if (self::valuesAreOnlyUrls($data)) {
                return ['status' => 'only_urls', 'data' => $original];
            }

            return ['status' => 'ok', 'data' => $data];
        }

        // If string, try to extract JSON block first
        if (is_string($responseData)) {
            $text = trim($responseData);

            // If the string is short and not JSON, treat as plain text
            if (strlen($text) < 200 && self::looksLikePlainTranslatedText($text)) {
                // If original expected structured, wrap properly
                if (is_array($original)) {
                    // fallback: return original structure unchanged (no sections)
                    return ['status' => 'no_content', 'data' => $original];
                }

                return ['status' => 'text_fallback', 'data' => ['text' => $text]];
            }

            // Try extract JSON block
            $json = self::extractJsonBlock($text);
            if ($json !== null) {
                $decoded = self::decodeJsonSafely($json);
                if ($decoded !== null) {
                    if (self::valuesAreOnlyUrls($decoded)) {
                        return ['status' => 'only_urls', 'data' => $original];
                    }

                    return ['status' => 'ok', 'data' => $decoded];
                }
            }

            // No JSON or decode failed. Try to find a first non-empty line that looks like translation
            $lines = preg_split('/\r?\n/', $text);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') continue;
                // skip Arabic/Hebrew notes like "ملاحظة:" or the model's notes
                if (preg_match('/^(note|ملاحظة|הערה)\b/i', $line)) continue;
                // If this line looks like a URL only, skip
                if (preg_match('#^https?://#i', $line)) continue;
                // If the line contains very few letters, skip as likely garbage
                $letters = preg_replace('/[^\p{L}]/u', '', $line);
                if (mb_strlen($letters) < 2) continue;
                // Accept as fallback translation
                if (is_array($original)) {
                    // structured expected -> no change
                    return ['status' => 'no_content', 'data' => $original];
                }
                return ['status' => 'text_fallback', 'data' => ['text' => $line]];
            }

            // Nothing useful extracted
            if (is_array($original)) {
                return ['status' => 'no_content', 'data' => $original];
            }
            return ['status' => 'unparseable', 'data' => $text];
        }

        // Any other type, unparseable
        if (is_array($original)) {
            return ['status' => 'no_content', 'data' => $original];
        }
        return ['status' => 'unparseable', 'data' => $responseData];
    }

    protected static function looksLikePlainTranslatedText(string $text): bool
    {
        // Heuristic: short single-line with letters and spaces (not JSON)
        if (preg_match('/^[\p{L}\p{N}\s\-\'"\,]{2,200}$/u', $text)) return true;
        return false;
    }
}
