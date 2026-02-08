<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // أو حسب الصلاحيات
    }

    public function rules(): array
    {
        return [
            'from_warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
                'different:to_warehouse_id',
                Rule::exists('warehouses', 'id')->where('is_active', true),
            ],
            'to_warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
                Rule::exists('warehouses', 'id')->where('is_active', true),
            ],
            'transfer_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'expected_date' => [
                'nullable',
                'date',
                'after_or_equal:transfer_date',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
                'max:100', // حد أقصى للعناصر
            ],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct', // منع التكرار
                Rule::exists('products', 'id')->where('is_active', true),
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.001',
                'max:999999.999',
            ],
            'items.*.notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'from_warehouse_id.required' => 'يجب اختيار المخزن المصدر',
            'from_warehouse_id.exists' => 'المخزن المصدر غير موجود',
            'from_warehouse_id.different' => 'لا يمكن التحويل إلى نفس المخزن',
            
            'to_warehouse_id.required' => 'يجب اختيار المخزن الهدف',
            'to_warehouse_id.exists' => 'المخزن الهدف غير موجود',
            
            'transfer_date.required' => 'يجب تحديد تاريخ التحويل',
            'transfer_date.date' => 'تاريخ التحويل غير صحيح',
            'transfer_date.before_or_equal' => 'تاريخ التحويل لا يمكن أن يكون في المستقبل',
            
            'expected_date.date' => 'تاريخ الاستلام المتوقع غير صحيح',
            'expected_date.after_or_equal' => 'تاريخ الاستلام يجب أن يكون بعد تاريخ التحويل',
            
            'items.required' => 'يجب إضافة منتج واحد على الأقل',
            'items.min' => 'يجب إضافة منتج واحد على الأقل',
            'items.max' => 'الحد الأقصى 100 منتج في التحويل الواحد',
            
            'items.*.product_id.required' => 'يجب اختيار المنتج',
            'items.*.product_id.exists' => 'المنتج غير موجود أو غير نشط',
            'items.*.product_id.distinct' => 'المنتج مكرر في القائمة',
            
            'items.*.quantity.required' => 'يجب إدخال الكمية',
            'items.*.quantity.min' => 'الكمية يجب أن تكون أكبر من صفر',
            'items.*.quantity.max' => 'الكمية أكبر من الحد المسموح',
        ];
    }

    /**
     * ✅ Validation إضافي بعد Rules
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // التحقق من عدم وجود منتجات مكررة
            if ($this->has('items')) {
                $productIds = array_column($this->items, 'product_id');
                if (count($productIds) !== count(array_unique($productIds))) {
                    $validator->errors()->add(
                        'items',
                        'يوجد منتجات مكررة في القائمة'
                    );
                }
            }
        });
    }
}