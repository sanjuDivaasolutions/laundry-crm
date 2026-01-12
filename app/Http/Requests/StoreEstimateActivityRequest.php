<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreEstimateActivityRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('estimate_activity_create');
    }

    public function rules()
    {
        return [
            'estimate_id' => [
                'integer',
                'exists:estimates,id',
                'required',
            ],
            'title' => [
                'string',
                'required',
            ],
            'description' => [
                'string',
                'nullable',
            ],
            'user_id' => [
                'integer',
                'exists:users,id',
                'required',
            ],
            'is_active' => [
                'boolean',
            ],
        ];
    }
}
