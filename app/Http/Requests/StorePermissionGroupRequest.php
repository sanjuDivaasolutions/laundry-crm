<?php

namespace App\Http\Requests;

use App\Models\PermissionGroup;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StorePermissionGroupRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('permission_group_create');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
                'unique:permission_groups',
            ],
        ];
    }
}
