<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateManufacturingOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'nullable|exists:products,id',
            'product_name' => 'sometimes|required|string|max:255',
            'quantity_produced' => 'sometimes|required|numeric|min:0.01',
            'cost_per_unit' => 'sometimes|required|numeric|min:0',
            'total_cost' => 'sometimes|required|numeric|min:0',
            'selling_price_per_unit' => 'sometimes|required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'components' => 'sometimes|array|min:1',
            'components.*.component_name' => 'required|string|max:255',
            'components.*.quantity' => 'required|numeric|min:0.0001',
            'components.*.unit' => 'nullable|string|max:50',
            'components.*.unit_cost' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'product_name.required' => 'Product name is required',
            'quantity_produced.required' => 'Quantity produced is required',
            'quantity_produced.min' => 'Quantity produced must be greater than 0',
            'cost_per_unit.required' => 'Cost per unit is required',
            'cost_per_unit.min' => 'Cost per unit cannot be negative',
            'selling_price_per_unit.required' => 'Selling price per unit is required',
            'selling_price_per_unit.min' => 'Selling price per unit cannot be negative',
            'components.required' => 'At least one component is required',
            'components.min' => 'At least one component is required',
            'components.*.component_name.required' => 'Component name is required',
            'components.*.quantity.required' => 'Component quantity is required',
            'components.*.quantity.min' => 'Component quantity must be greater than 0',
            'components.*.unit_cost.required' => 'Component unit cost is required',
            'components.*.unit_cost.min' => 'Component unit cost cannot be negative',
        ];
    }
}