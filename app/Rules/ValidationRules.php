<?php

namespace App\Rules;

use App\Enums\SectionType;
use Illuminate\Validation\Rule;

/**
 * Centralized validation rules for reuse across FormRequests and Filament.
 *
 * Usage in FormRequest:
 *   return ValidationRules::card();
 *
 * Usage in Filament:
 *   TextInput::make('title')->rules(ValidationRules::cardTitle())
 */
class ValidationRules
{
    /**
     * Get validation rules for creating a card.
     *
     * @return array<string, array>
     */
    public static function cardCreate(): array
    {
        return [
            'title' => ['required', new LocalizedString(maxLength: 255)],
            'subtitle' => ['nullable', new LocalizedString(maxLength: 255)],
            'language_id' => ['nullable', 'exists:languages,id'],
            'template_id' => ['nullable', 'exists:templates,id'],
            'theme_id' => ['nullable', 'exists:themes,id'],
            'active_languages' => ['nullable', 'array'],
            'active_languages.*' => ['string', 'size:2'],
            'custom_slug' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9-]+$/',
                'unique:business_cards,custom_slug',
            ],
        ];
    }

    /**
     * Get validation rules for updating a card.
     *
     * @param  int|string|null  $cardId  Card ID to ignore in unique check
     * @return array<string, array>
     */
    public static function cardUpdate(int|string|null $cardId = null): array
    {
        return [
            'title' => ['sometimes', 'required', new LocalizedString(maxLength: 255)],
            'subtitle' => ['nullable', new LocalizedString(maxLength: 255)],
            'language_id' => ['nullable', 'exists:languages,id'],
            'template_id' => ['nullable', 'exists:templates,id'],
            'theme_id' => ['nullable', 'exists:themes,id'],
            'active_languages' => ['nullable', 'array'],
            'active_languages.*' => ['string', 'size:2'],
            'custom_slug' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('business_cards', 'custom_slug')->ignore($cardId),
            ],
            'theme_overrides' => ['nullable', 'array'],
            'is_published' => ['sometimes', 'boolean'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get validation rules for creating a section.
     *
     * @return array<string, array>
     */
    public static function sectionCreate(): array
    {
        return [
            'section_type' => ['required', Rule::enum(SectionType::class)],
            'title' => ['nullable', new LocalizedString(maxLength: 255)],
            'content' => ['nullable', new LocalizedContent],
            'image_path' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Get validation rules for updating a section.
     *
     * @return array<string, array>
     */
    public static function sectionUpdate(): array
    {
        return [
            'section_type' => ['sometimes', Rule::enum(SectionType::class)],
            'title' => ['sometimes', 'nullable', new LocalizedString(maxLength: 255)],
            'content' => ['sometimes', 'nullable', new LocalizedContent],
            'image' => ['sometimes', 'nullable', 'image', 'max:5120'],
            'image_path' => ['sometimes', 'nullable', 'string', 'max:500'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Get validation rules for creating a theme.
     *
     * @return array<string, array>
     */
    public static function themeCreate(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'config' => ['nullable', new ThemeConfig],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get validation rules for updating a theme.
     *
     * @return array<string, array>
     */
    public static function themeUpdate(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'config' => ['nullable', new ThemeConfig],
            'is_public' => ['sometimes', 'boolean'],
            'preview_image' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get validation rules for a custom slug.
     *
     * @param  int|string|null  $ignoreId  ID to ignore in unique check
     */
    public static function customSlug(int|string|null $ignoreId = null): array
    {
        return [
            'nullable',
            'string',
            'max:100',
            'regex:/^[a-z0-9-]+$/',
            $ignoreId
                ? Rule::unique('business_cards', 'custom_slug')->ignore($ignoreId)
                : 'unique:business_cards,custom_slug',
        ];
    }

    /**
     * Get validation rules for language codes array.
     *
     * @return array<string, array>
     */
    public static function languageCodes(): array
    {
        return [
            'languages' => ['nullable', 'array'],
            'languages.*' => ['string', 'size:2'],
        ];
    }

    /**
     * Get error messages for common validation rules.
     *
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'title.required' => 'Title is required',
            'custom_slug.regex' => 'Slug can only contain lowercase letters, numbers, and hyphens',
            'custom_slug.unique' => 'This slug is already taken',
            'section_type.enum' => 'Invalid section type',
            'active_languages.*.size' => 'Language codes must be 2 characters (e.g., "en", "ar")',
            'image.max' => 'Image must not exceed 5MB',
        ];
    }
}
