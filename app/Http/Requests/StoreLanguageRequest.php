<?php

namespace App\Http\Requests;

use App\Models\Language;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreLanguageRequest extends FormRequest
{
    use CustomFormRequest;
    public function authorize()
    {
        return Gate::allows('language_create');
    }

    public function prepareForValidation()
    {
        //$this->set('locale', app()->getLocale());
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'locale' => [
                'string',
                'required',
            ],
            'active' => [
                'boolean',
            ],
        ];
    }
}
