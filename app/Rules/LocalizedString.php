<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a localized/multilingual string field.
 *
 * Accepts either:
 * - A simple string (will be converted to array in prepareForValidation)
 * - An array keyed by language codes: ['en' => 'Hello', 'ar' => 'مرحبا']
 *
 * Must match: resources/js/types/contracts/Section.ts > LocalizedString
 */
class LocalizedString implements ValidationRule
{
    public function __construct(
        protected ?int $maxLength = null,
        protected bool $requireDefault = false,
        protected string $defaultLanguage = 'en'
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Allow null values (handled by nullable rule)
        if ($value === null) {
            return;
        }

        // Accept strings (backward compatibility - will be normalized in prepareForValidation)
        if (is_string($value)) {
            if ($this->maxLength && mb_strlen($value) > $this->maxLength) {
                $fail("The {$attribute} must not exceed {$this->maxLength} characters.");
            }

            return;
        }

        // Must be an array at this point
        if (! is_array($value)) {
            $fail("The {$attribute} must be a string or a localized array.");

            return;
        }

        // Check if default language is required
        if ($this->requireDefault && ! isset($value[$this->defaultLanguage])) {
            $fail("The {$attribute} must include a value for the default language ({$this->defaultLanguage}).");

            return;
        }

        // Validate each language entry
        foreach ($value as $langCode => $text) {
            // Language code must be a 2-character string
            if (! is_string($langCode) || strlen($langCode) !== 2) {
                $fail("The {$attribute} contains an invalid language code: {$langCode}. Language codes must be 2 characters.");

                return;
            }

            // Text must be a string
            if (! is_string($text)) {
                $fail("The {$attribute} value for language '{$langCode}' must be a string.");

                return;
            }

            // Check max length per language
            if ($this->maxLength && mb_strlen($text) > $this->maxLength) {
                $fail("The {$attribute} for language '{$langCode}' must not exceed {$this->maxLength} characters.");

                return;
            }
        }
    }
}
