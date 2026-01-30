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
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            // Service prices validation
            'service_prices' => ['nullable', 'array'],
            'service_prices.*.service_id' => ['required_with:service_prices', 'integer', 'exists:services,id'],
            'service_prices.*.price' => ['nullable', 'numeric', 'min:0'],
            'service_prices.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Item name is required.',
            'price.required' => 'Default price is required.',
            'service_prices.*.service_id.exists' => 'Invalid service selected.',
            'service_prices.*.price.numeric' => 'Service price must be a number.',
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
