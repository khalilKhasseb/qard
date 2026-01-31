<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a theme configuration object.
 *
 * Must match: resources/js/types/contracts/Theme.ts > ThemeConfig
 */
class ThemeConfig implements ValidationRule
{
    protected array $colorKeys = ['primary', 'secondary', 'background', 'text', 'card_bg', 'border'];

    protected array $fontKeys = ['heading', 'body', 'heading_url', 'body_url'];

    protected array $imageKeys = ['background', 'header', 'logo'];

    protected array $layoutKeys = ['card_style', 'border_radius', 'alignment', 'spacing'];

    protected array $cardStyles = ['elevated', 'flat', 'bordered'];

    protected array $alignments = ['left', 'center', 'right'];

    protected array $spacings = ['compact', 'normal', 'relaxed'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        if (! is_array($value)) {
            $fail("The {$attribute} must be a valid theme configuration object.");

            return;
        }

        // Validate colors
        if (isset($value['colors'])) {
            if (! is_array($value['colors'])) {
                $fail("The {$attribute}.colors must be an object.");

                return;
            }

            $hexColorRule = new HexColor;
            foreach ($value['colors'] as $key => $color) {
                if (! in_array($key, $this->colorKeys, true)) {
                    continue; // Allow extra keys for extensibility
                }

                $errors = [];
                $hexColorRule->validate("{$attribute}.colors.{$key}", $color, function ($message) use (&$errors) {
                    $errors[] = $message;
                });

                if (! empty($errors)) {
                    $fail($errors[0]);

                    return;
                }
            }
        }

        // Validate fonts
        if (isset($value['fonts']) && ! is_array($value['fonts'])) {
            $fail("The {$attribute}.fonts must be an object.");

            return;
        }

        // Validate images
        if (isset($value['images']) && ! is_array($value['images'])) {
            $fail("The {$attribute}.images must be an object.");

            return;
        }

        // Validate layout
        if (isset($value['layout'])) {
            if (! is_array($value['layout'])) {
                $fail("The {$attribute}.layout must be an object.");

                return;
            }

            if (isset($value['layout']['card_style']) && ! in_array($value['layout']['card_style'], $this->cardStyles, true)) {
                $fail("The {$attribute}.layout.card_style must be one of: ".implode(', ', $this->cardStyles));

                return;
            }

            if (isset($value['layout']['alignment']) && ! in_array($value['layout']['alignment'], $this->alignments, true)) {
                $fail("The {$attribute}.layout.alignment must be one of: ".implode(', ', $this->alignments));

                return;
            }

            if (isset($value['layout']['spacing']) && ! in_array($value['layout']['spacing'], $this->spacings, true)) {
                $fail("The {$attribute}.layout.spacing must be one of: ".implode(', ', $this->spacings));

                return;
            }
        }

        // Validate custom_css (basic check)
        if (isset($value['custom_css']) && ! is_string($value['custom_css'])) {
            $fail("The {$attribute}.custom_css must be a string.");
        }
    }
}
