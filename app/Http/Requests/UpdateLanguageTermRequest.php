<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLanguageTermRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('language_term_edit');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
                'unique:language_terms,name,'.request()->route('language_term')->id,
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
