<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStateRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('state_edit');
    }

    protected function prepareForValidation()
    {
        $country_id = null;
        if ($this->country) {
            $country_id = $this->country['id'];
        }
        $this->merge([
            'country_id' => $country_id,
        ]);
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'active' => [
                'boolean',
            ],
            'country_id' => [
                'integer',
                'exists:countries,id',
                'required',
            ],
        ];
    }
}
