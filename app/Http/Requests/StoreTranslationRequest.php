<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreTranslationRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('translation_create');
    }

    public function rules()
    {
        return [
            'language_id' => [
                'integer',
                'exists:languages,id',
                'required',
            ],
            'language_term_id' => [
                'integer',
                'exists:language_terms,id',
                'required',
            ],
            'translation' => [
                'string',
                'nullable',
            ],
        ];
    }
}
