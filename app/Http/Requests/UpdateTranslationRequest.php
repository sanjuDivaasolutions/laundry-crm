<?php

namespace App\Http\Requests;

use App\Models\Translation;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateTranslationRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('translation_edit');
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
