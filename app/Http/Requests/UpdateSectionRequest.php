<?php

namespace App\Http\Requests;

use App\Enums\SectionType;
use App\Rules\LocalizedContent;
use App\Rules\LocalizedString;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Section type - uses canonical enum
            'section_type' => ['sometimes', Rule::enum(SectionType::class)],

            // Multilingual fields
            'title' => ['sometimes', 'nullable', new LocalizedString(maxLength: 255)],
            'content' => ['sometimes', 'nullable', new LocalizedContent],

            // Image support
            'image' => ['sometimes', 'nullable', 'image', 'max:5120'], // 5MB max
            'image_path' => ['sometimes', 'nullable', 'string', 'max:500'],

            // Ordering & visibility
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],

            // Flexible metadata
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'section_type.enum' => 'Invalid section type. Allowed types: '.implode(', ', SectionType::values()),
            'image.max' => 'Image must not exceed 5MB',
        ];
    }
}
