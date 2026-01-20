<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SwitchLanguageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'language_code' => [
                'required',
                'string',
                'size:2',
                'exists:languages,code',
                function ($attribute, $value, $fail) {
                    $language = \App\Models\Language::where('code', $value)->first();
                    if ($language && ! $language->is_active) {
                        $fail('The selected language is not active.');
                    }
                },
            ],
        ];
    }
}
