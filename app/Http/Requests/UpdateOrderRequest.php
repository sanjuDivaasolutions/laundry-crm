<?php

namespace App\Http\Requests;

use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize(): bool
    {
        return Gate::allows('order_edit');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['sometimes', 'integer', 'exists:customers,id'],
            'order_date' => ['sometimes', 'date'],
            'promised_date' => ['sometimes', 'date', 'after_or_equal:order_date'],
            'urgent' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'hanger_number' => ['nullable', 'string', 'max:50'],
            'discount_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', 'exists:order_items,id'],
            'items.*.item_id' => ['required_with:items', 'integer', 'exists:items,id'],
            'items.*.service_id' => ['required_with:items', 'integer', 'exists:services,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.color' => ['nullable', 'string', 'max:50'],
            'items.*.brand' => ['nullable', 'string', 'max:100'],
            'items.*.defect_notes' => ['nullable', 'string', 'max:500'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.exists' => 'Selected customer does not exist.',
            'promised_date.after_or_equal' => 'Promised date must be on or after the order date.',
            'items.min' => 'At least one item is required.',
            'items.*.item_id.required_with' => 'Each item must have a laundry item selected.',
            'items.*.service_id.required_with' => 'Each item must have a service selected.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
        ];
    }
}
