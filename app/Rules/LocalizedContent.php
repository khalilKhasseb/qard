<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a localized/multilingual content field.
 *
 * Accepts an array keyed by language codes, where each value is an object/array:
 * ['en' => ['text' => 'Hello'], 'ar' => ['text' => 'مرحبا']]
 *
 * Must match: resources/js/types/contracts/Section.ts > LocalizedContent
 */
class LocalizedContent implements ValidationRule
{
    public function __construct(
        protected bool $requireDefault = false,
        protected string $defaultLanguage = 'en'
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Allow null values (handled by nullable rule)
        if ($value === null) {
            return;
        }

        // Must be an array
        if (! is_array($value)) {
            $fail("The {$attribute} must be a localized content array.");

            return;
        }

        // Check if default language is required
        if ($this->requireDefault && ! isset($value[$this->defaultLanguage])) {
            $fail("The {$attribute} must include content for the default language ({$this->defaultLanguage}).");

            return;
        }

        // Validate each language entry
        foreach ($value as $langCode => $content) {
            // Language code must be a 2-character string
            if (! is_string($langCode) || strlen($langCode) !== 2) {
                $fail("The {$attribute} contains an invalid language code: {$langCode}. Language codes must be 2 characters.");

                return;
            }

            // Content must be an array/object
            if (! is_array($content)) {
                $fail("The {$attribute} content for language '{$langCode}' must be an object.");

                return;
            }
        }
    }
}
