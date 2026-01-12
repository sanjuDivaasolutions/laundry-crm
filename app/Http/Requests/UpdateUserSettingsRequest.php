<?php

namespace App\Http\Requests;

use App\Models\User;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class UpdateUserSettingsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
        //return Gate::allows('user_edit');
    }

    public function rules()
    {
        return [
            'settings'       => [
                'required',
            ],
        ];
    }
}
