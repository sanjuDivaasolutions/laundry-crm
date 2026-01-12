<?php

namespace App\Http\Requests;

use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateShelfRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize()
    {
        return Gate::allows('shelf_edit');
    }

    public function prepareForValidation()
    {
        $this->setObjectId('warehouse');
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
            'warehouse_id' => [
                'integer',
                'exists:warehouses,id',
                'required',
            ],
        ];
    }
}
