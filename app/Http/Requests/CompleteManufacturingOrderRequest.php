<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteManufacturingOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'nullable|exists:products,id',
        ];
    }

    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'Warehouse is required to add inventory',
            'warehouse_id.exists' => 'Selected warehouse does not exist',
            'product_id.exists' => 'Selected product does not exist',
        ];
    }
}