<?php

namespace App\Http\Requests;

use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize(): bool
    {
        return Gate::allows('order_create');
    }

    public function prepareForValidation(): void
    {
        $this->generateOrderNumber();
    }

    public function rules(): array
    {
        return [
            'order_number' => ['required', 'string', 'max:50'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'order_date' => ['required', 'date'],
            'promised_date' => ['required', 'date', 'after_or_equal:order_date'],
            'urgent' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'hanger_number' => ['nullable', 'string', 'max:50'],
            'discount_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tip_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', 'exists:items,id'],
            'items.*.service_id' => ['required', 'integer', 'exists:services,id'],
            'items.*.pricing_type' => ['nullable', 'string', 'in:piece,weight'],
            'items.*.quantity' => ['required_unless:items.*.pricing_type,weight', 'integer', 'min:1'],
            'items.*.weight' => ['required_if:items.*.pricing_type,weight', 'nullable', 'numeric', 'min:0.1'],
            'items.*.weight_unit' => ['nullable', 'string', 'in:lb,kg'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.color' => ['nullable', 'string', 'max:50'],
            'items.*.brand' => ['nullable', 'string', 'max:100'],
            'items.*.defect_notes' => ['nullable', 'string', 'max:500'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'order_date.required' => 'Order date is required.',
            'promised_date.required' => 'Promised delivery date is required.',
            'promised_date.after_or_equal' => 'Promised date must be on or after the order date.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.item_id.required' => 'Each item must have a laundry item selected.',
            'items.*.service_id.required' => 'Each item must have a service selected.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_price.required' => 'Unit price is required for each item.',
        ];
    }

    private function generateOrderNumber(): void
    {
        $config = [
            'table' => 'orders',
            'field' => 'order_number',
            'length' => 10,
            'prefix' => 'ORD-',
        ];
        $code = UtilityService::generateCode($config);
        $this->merge(['order_number' => $code]);
    }
}
