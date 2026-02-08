<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'name' => 'required|string|max:255',
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId)
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'barcode')->ignore($productId)
            ],
            'category' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'base_unit' => 'required|string|max:50',
            'base_purchase_price' => 'required|numeric|min:0|max:9999999.99',
            'base_selling_price' => 'required|numeric|min:0|max:9999999.99|gte:base_purchase_price',
            'price_change_reason' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المنتج مطلوب',
            'sku.unique' => 'رمز SKU مستخدم بالفعل',
            'barcode.unique' => 'الباركود مستخدم بالفعل',
            'category.required' => 'التصنيف مطلوب',
            'base_unit.required' => 'وحدة القياس مطلوبة',
            'base_purchase_price.required' => 'سعر الشراء مطلوب',
            'base_selling_price.required' => 'سعر البيع مطلوب',
            'base_selling_price.gte' => 'سعر البيع يجب أن يكون أكبر من أو يساوي سعر الشراء',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}