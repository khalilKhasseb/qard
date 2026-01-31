<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a hex color code.
 *
 * Accepts: #fff, #ffffff, #FFFFFF (3 or 6 character hex)
 */
class HexColor implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        if (! is_string($value)) {
            $fail("The {$attribute} must be a valid hex color code.");

            return;
        }

        // Match #fff or #ffffff (case insensitive)
        if (! preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value)) {
            $fail("The {$attribute} must be a valid hex color code (e.g., #ffffff or #fff).");
        }
    }
}
