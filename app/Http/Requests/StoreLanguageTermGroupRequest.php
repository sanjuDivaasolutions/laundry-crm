<?php

namespace App\Http\Requests;

use App\Models\LanguageTermGroup;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreLanguageTermGroupRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('language_term_group_create');
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
