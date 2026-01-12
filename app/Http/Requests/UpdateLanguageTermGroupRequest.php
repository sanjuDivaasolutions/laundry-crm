<?php

namespace App\Http\Requests;

use App\Models\LanguageTermGroup;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

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
