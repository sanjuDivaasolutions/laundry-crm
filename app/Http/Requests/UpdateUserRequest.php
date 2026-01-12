<?php

namespace App\Http\Requests;

use App\Traits\CustomFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize()
    {
        return true; // Gate::allows('user_edit');
    }

    public function prepareForValidation()
    {
        $this->set('roles', stringToArray($this->input('roles', [])));
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
                'unique:users,email,'.request()->route('user')->id,
            ],
            'password' => [
                'nullable',
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
                'nullable',
            ],
            'settings' => [
                'string',
                'nullable',
            ],
        ];
    }
}
