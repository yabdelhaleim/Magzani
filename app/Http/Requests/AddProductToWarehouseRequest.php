<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddProductToWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $warehouseId = $this->route('warehouse');

        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('is_active', true),
                Rule::unique('product_warehouse', 'product_id')
                    ->where('warehouse_id', $warehouseId),
            ],
            'quantity' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.999',
            ],
            'min_stock' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.999',
            ],
            'max_stock' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.999',
                'gte:min_stock',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'يجب اختيار المنتج',
            'product_id.exists' => 'المنتج غير موجود أو غير نشط',
            'product_id.unique' => 'المنتج موجود بالفعل في هذا المخزن',
            
            'quantity.required' => 'يجب إدخال الكمية',
            'quantity.min' => 'الكمية يجب أن تكون صفر أو أكبر',
            'quantity.max' => 'الكمية أكبر من الحد المسموح',
            
            'min_stock.min' => 'الحد الأدنى يجب أن يكون صفر أو أكبر',
            'max_stock.gte' => 'الحد الأقصى يجب أن يكون أكبر من أو يساوي الحد الأدنى',
        ];
    }
}