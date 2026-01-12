<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionGroupRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('permission_group_edit');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
                'unique:permission_groups,name,'.request()->route('permission_group')->id,
            ],
        ];
    }
}
