<?php

namespace App\Http\Requests;

use App\Traits\CustomFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    use CustomFormRequest;

    private $arrayKeys = ['roles'];

    public function authorize()
    {
        return true; // Gate::allows('user_create');
    }

    public function prepareForValidation()
    {
        $this->convertBulkToArray($this->arrayKeys);
        $this->set('language_id', config('system.defaults.language.id'));
        $this->setIfNull('active', true);
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'email' => [
                'required',
                'unique:users',
            ],
            'password' => [
                'required',
            ],
            'roles' => [
                'required',
                'array',
            ],
            'roles.*.id' => [
                'integer',
                'exists:roles,id',
            ],
            'active' => [
                'boolean',
            ],
            'language_id' => [
                'integer',
                'exists:languages,id',
                'required',
            ],
            'settings' => [
                'string',
                'nullable',
            ],
        ];
    }
}
