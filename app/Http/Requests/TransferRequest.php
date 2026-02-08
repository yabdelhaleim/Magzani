<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'from_warehouse_id.required' => 'يجب اختيار المخزن المصدر',
            'from_warehouse_id.different' => 'لا يمكن التحويل إلى نفس المخزن',
            'to_warehouse_id.required' => 'يجب اختيار المخزن المستهدف',
            'transfer_date.required' => 'يجب تحديد تاريخ التحويل',
            'items.required' => 'يجب إضافة منتج واحد على الأقل',
            'items.min' => 'يجب إضافة منتج واحد على الأقل',
            'items.*.product_id.required' => 'يجب اختيار المنتج',
            'items.*.product_id.exists' => 'المنتج المختار غير موجود',
            'items.*.quantity.required' => 'يجب إدخال الكمية',
            'items.*.quantity.min' => 'الكمية يجب أن تكون أكبر من صفر',
        ];
    }
}