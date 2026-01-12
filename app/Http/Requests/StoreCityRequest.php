<?php

namespace App\Http\Requests;

use App\Models\City;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreCityRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('city_create');
    }

    protected function prepareForValidation()
    {
        $state_id = null;
        if($this->state) {
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
