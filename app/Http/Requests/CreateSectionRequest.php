<?php

namespace App\Http\Requests;

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
            'section_type' => ['required', Rule::in([
                'contact', 'social', 'services', 'products',
                'testimonials', 'hours', 'appointments', 'gallery',
            ])],
            'title' => ['nullable'],
            'content' => ['nullable'],  // Allow empty/null, service handles defaults
            'is_active' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
