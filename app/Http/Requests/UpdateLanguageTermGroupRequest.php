<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLanguageTermGroupRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('language_term_group_edit');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'active' => [
                'boolean',
            ],
        ];
    }
}
