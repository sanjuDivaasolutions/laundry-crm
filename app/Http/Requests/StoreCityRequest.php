<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreCityRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('city_create');
    }

    protected function prepareForValidation()
    {
        $state_id = null;
        if ($this->state) {
            $state_id = $this->state['id'];
        }
        $this->merge([
            'state_id' => $state_id,
        ]);
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'state_id' => [
                'integer',
                'exists:states,id',
                'required',
            ],
            'active' => [
                'boolean',
            ],
        ];
    }
}
