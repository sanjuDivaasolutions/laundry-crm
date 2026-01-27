<?php

namespace App\Http\Requests;

use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize(): bool
    {
        return Gate::allows('item_create');
    }

    public function prepareForValidation(): void
    {
        $this->generateCode();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'unique:items,code'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Item name is required.',
            'price.required' => 'Price is required.',
        ];
    }

    private function generateCode(): void
    {
        $config = [
            'table' => 'items',
            'field' => 'code',
            'length' => 8,
            'prefix' => 'ITM-',
        ];
        $code = UtilityService::generateCode($config);
        $this->merge(['code' => $code]);
    }
}
