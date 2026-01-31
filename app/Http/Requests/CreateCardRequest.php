<?php

namespace App\Http\Requests;

use App\Rules\LocalizedString;
use Illuminate\Foundation\Http\FormRequest;

class CreateCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Multilingual fields - accept array or string
            'title' => ['required', new LocalizedString(maxLength: 255)],
            'subtitle' => ['nullable', new LocalizedString(maxLength: 255)],

            // References
            'language_id' => ['nullable', 'exists:languages,id'],
            'template_id' => ['nullable', 'exists:templates,id'],
            'theme_id' => ['nullable', 'exists:themes,id'],

            // Multilingual config
            'active_languages' => ['nullable', 'array'],
            'active_languages.*' => ['string', 'size:2'],

            // URL customization
            'custom_slug' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9-]+$/',
                'unique:business_cards,custom_slug',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Card title is required',
            'custom_slug.regex' => 'Slug can only contain lowercase letters, numbers, and hyphens',
            'custom_slug.unique' => 'This slug is already taken',
            'active_languages.*.size' => 'Language codes must be 2 characters (e.g., "en", "ar")',
        ];
    }

    /**
     * Prepare the data for validation.
     * Converts string title/subtitle to multilingual array format.
     */
    protected function prepareForValidation(): void
    {
        $this->normalizeLocalizedField('title');
        $this->normalizeLocalizedField('subtitle');
    }

    /**
     * Convert a string field to multilingual array format.
     */
    protected function normalizeLocalizedField(string $field): void
    {
        $value = $this->input($field);

        if (is_string($value) && ! empty($value)) {
            $defaultLang = $this->input('language_id')
                ? (\App\Models\Language::find($this->input('language_id'))?->code ?? 'en')
                : 'en';

            $this->merge([$field => [$defaultLang => $value]]);
        }
    }
}
