<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateManufacturingCostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_name' => 'required|string|min:2|max:255',
            'product_id' => 'nullable|integer|exists:products,id',
            'price_per_cubic_meter' => 'required|numeric|min:0|max:999999999.99',
            'labor_cost' => 'nullable|numeric|min:0',
            'nails_hardware_cost' => 'nullable|numeric|min:0',
            'transportation_cost' => 'nullable|numeric|min:0',
            'tips_misc_cost' => 'nullable|numeric|min:0',
            'fumigation_cost' => 'nullable|numeric|min:0',
            'profit_percentage' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:2000',
            'status' => 'nullable|in:draft,confirmed',
            'components' => 'required|array|min:1',
            'components.*.component_name' => 'required|string|max:255',
            'components.*.quantity' => 'required|numeric|min:0.01|max:99999',
            'components.*.length_cm' => 'required|numeric|min:0.01|max:99999',
            'components.*.width_cm' => 'required|numeric|min:0.01|max:99999',
            'components.*.thickness_cm' => 'required|numeric|min:0.01|max:99999',
        ];
    }

    public function messages(): array
    {
        return (new StoreManufacturingCostRequest())->messages();
    }

    public function attributes(): array
    {
        return (new StoreManufacturingCostRequest())->attributes();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'labor_cost' => $this->input('labor_cost', 0) ?: 0,
            'nails_hardware_cost' => $this->input('nails_hardware_cost', 0) ?: 0,
            'transportation_cost' => $this->input('transportation_cost', 0) ?: 0,
            'tips_misc_cost' => $this->input('tips_misc_cost', 0) ?: 0,
            'fumigation_cost' => $this->input('fumigation_cost', 0) ?: 0,
        ]);
    }
}
