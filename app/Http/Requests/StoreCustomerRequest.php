<?php

namespace App\Http\Requests;

use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize(): bool
    {
        return Gate::allows('customer_create');
    }

    public function prepareForValidation(): void
    {
        $this->generateCustomerCode();
    }

    public function rules(): array
    {
        return [
            'customer_code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Customer name is required.',
            'phone.required' => 'Phone number is required.',
        ];
    }

    private function generateCustomerCode(): void
    {
        $config = [
            'table' => 'customers',
            'field' => 'customer_code',
            'length' => 9,
            'prefix' => 'CUST-',
        ];
        $code = UtilityService::generateCode($config);
        $this->merge(['customer_code' => $code]);
    }
}
