<?php

namespace App\Http\Requests;

use App\Enums\SectionType;
use App\Rules\LocalizedContent;
use App\Rules\LocalizedString;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Section type - uses canonical enum
            'section_type' => ['required', Rule::enum(SectionType::class)],

            // Multilingual fields
            'title' => ['nullable', new LocalizedString(maxLength: 255)],
            'content' => ['nullable', new LocalizedContent],

            // Image support
            'image_path' => ['nullable', 'string', 'max:500'],

            // Ordering & visibility
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],

            // Flexible metadata
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'section_type.required' => 'Section type is required',
            'section_type.enum' => 'Invalid section type. Allowed types: '.implode(', ', SectionType::values()),
        ];
    }
}
