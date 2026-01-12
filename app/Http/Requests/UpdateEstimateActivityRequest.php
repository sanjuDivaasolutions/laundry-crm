<?php

namespace App\Http\Requests;

use App\Models\EstimateActivity;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateEstimateActivityRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('estimate_activity_edit');
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
