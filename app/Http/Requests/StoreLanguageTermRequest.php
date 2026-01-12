<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreLanguageTermRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('language_term_create');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
                'unique:language_terms',
            ],
            'active' => [
                'boolean',
            ],
            'language_term_group_id' => [
                'integer',
                'exists:language_term_groups,id',
                'required',
            ],
        ];
    }
}
