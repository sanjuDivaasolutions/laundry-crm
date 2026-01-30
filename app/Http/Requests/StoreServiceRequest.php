<?php

namespace App\Http\Requests;

use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize(): bool
    {
        return Gate::allows('service_create');
    }

    public function prepareForValidation(): void
    {
        $this->generateCode();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'unique:services,code'],
            'description' => ['nullable', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Service name is required.',
        ];
    }

    private function generateCode(): void
    {
        $config = [
            'table' => 'services',
            'field' => 'code',
            'length' => 8,
            'prefix' => 'SVC-',
        ];
        $code = UtilityService::generateCode($config);
        $this->merge(['code' => $code]);
    }
}
