<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserSettingsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
        // return Gate::allows('user_edit');
    }

    public function rules()
    {
        return [
            'settings' => [
                'required',
            ],
        ];
    }
}
