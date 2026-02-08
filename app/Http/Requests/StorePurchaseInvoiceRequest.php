<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // بيانات الفاتورة
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'nullable|string|max:100|unique:purchase_invoices,invoice_number',
            'invoice_date' => 'required|date|before_or_equal:today',
            'due_date' => 'nullable|date|after:invoice_date',
            
            // الخصومات والضرائب
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0|max:999999',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0|max:999999',
            'shipping_cost' => 'nullable|numeric|min:0|max:999999',
            'other_charges' => 'nullable|numeric|min:0|max:999999',
            
            // الدفع
            'paid' => 'nullable|numeric|min:0|max:9999999',
            
            // ملاحظات
            'notes' => 'nullable|string|max:2000',
            
            // الأصناف
            'items' => 'required|array|min:1|max:100',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.purchase_unit_id' => 'required|exists:product_purchase_units,id',
            'items.*.quantity' => 'required|numeric|min:0.001|max:999999',
            'items.*.cost' => 'required|numeric|min:0.01|max:9999999',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'يجب اختيار المخزن',
            'supplier_id.required' => 'يجب اختيار المورد',
            'invoice_date.required' => 'يجب تحديد تاريخ الفاتورة',
            'items.required' => 'يجب إضافة صنف واحد على الأقل',
            'items.*.product_id.required' => 'يجب اختيار المنتج',
            'items.*.purchase_unit_id.required' => 'يجب اختيار وحدة الشراء',
            'items.*.quantity.required' => 'يجب إدخال الكمية',
            'items.*.cost.required' => 'يجب إدخال التكلفة',
        ];
    }
}