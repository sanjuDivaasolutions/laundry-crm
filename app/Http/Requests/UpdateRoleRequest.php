<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateRoleRequest extends FormRequest
{
    use CustomFormRequest;
    public function authorize()
    {
        return Gate::allows('role_edit');
    }

    public function prepareForValidation()
    {
        $this->set('permissions',stringToArray($this->input('permissions')));
    }

    public function rules()
    {
        return [
            'title' => [
                'string',
                'required',
            ],
            'permissions' => [
                'array',
                'exists:permissions,id',
            ],
        ];
    }
}
