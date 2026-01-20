<?php

namespace App\Http\Requests;

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
            'section_type' => ['sometimes', Rule::in([
                'contact', 'social', 'services', 'products',
                'testimonials', 'hours', 'appointments', 'gallery', 'about', 'links', 'custom', 'video',
            ])],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'nullable'],
            'image' => ['sometimes', 'nullable', 'image', 'max:5120'], // 5MB max
            'is_active' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
