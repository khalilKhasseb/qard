<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $cardId = $this->route('card');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'template_id' => ['nullable', 'exists:templates,id'],
            'theme_id' => ['nullable', 'exists:themes,id'],
            'custom_slug' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('business_cards', 'custom_slug')->ignore($cardId),
            ],
            'theme_overrides' => ['nullable', 'array'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Card title is required',
            'custom_slug.regex' => 'Slug can only contain lowercase letters, numbers, and hyphens',
            'custom_slug.unique' => 'This slug is already taken',
        ];
    }
}
