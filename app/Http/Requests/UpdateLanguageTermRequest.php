<?php

namespace App\Http\Requests;

use App\Models\LanguageTerm;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

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
                'unique:language_terms,name,' . request()->route('language_term')->id,
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
