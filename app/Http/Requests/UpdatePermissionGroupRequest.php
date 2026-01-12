<?php

namespace App\Http\Requests;

use App\Models\PermissionGroup;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

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
                'unique:permission_groups,name,' . request()->route('permission_group')->id,
            ],
        ];
    }
}
