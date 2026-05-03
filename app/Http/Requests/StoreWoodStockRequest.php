<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWoodStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'nullable|exists:suppliers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'purchase_reference' => 'nullable|string|max:255',
            'length_cm' => 'required|numeric|min:0.01',
            'width_cm' => 'required|numeric|min:0.01',
            'thickness_cm' => 'required|numeric|min:0.01',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'received_at' => 'required|date',
            'notes' => 'nullable|string',
        ];
    }
}
