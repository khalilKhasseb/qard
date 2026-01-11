<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTranslationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'key' => 'required|string|max:255',
            'language_code' => 'required|string|size:2|exists:languages,code',
            'value' => 'required|string',
        ];
    }
}
