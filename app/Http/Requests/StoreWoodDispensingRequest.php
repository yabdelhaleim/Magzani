<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWoodDispensingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wood_stock_id' => 'required|exists:wood_stocks,id',
            'client_id' => 'nullable|exists:customers,id',
            'manufacturing_order_id' => 'nullable|exists:manufacturing_orders,id',
            'volume_cm3_taken' => 'required|numeric|min:0.01',
            'dispensed_at' => 'required|date',
            'notes' => 'nullable|string',
        ];
    }
}
