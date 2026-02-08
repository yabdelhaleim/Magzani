<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // يمكنك تفعيل نظام الصلاحيات لاحقاً
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // بيانات الفاتورة الأساسية
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'invoice_date' => ['required', 'date', 'before_or_equal:today'],
            
            // بيانات الأصناف
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            
            // حقول اختيارية
            'notes' => ['nullable', 'string', 'max:1000'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'supplier_id' => 'المورد',
            'warehouse_id' => 'المخزن',
            'invoice_date' => 'تاريخ الفاتورة',
            'items' => 'الأصناف',
            'items.*.product_id' => 'الصنف',
            'items.*.qty' => 'الكمية',
            'items.*.price' => 'السعر',
            'notes' => 'الملاحظات',
            'discount' => 'الخصم',
            'tax' => 'الضريبة',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'supplier_id.required' => 'يجب اختيار المورد',
            'supplier_id.exists' => 'المورد المحدد غير موجود',
            
            'warehouse_id.required' => 'يجب اختيار المخزن',
            'warehouse_id.exists' => 'المخزن المحدد غير موجود',
            
            'invoice_date.required' => 'يجب إدخال تاريخ الفاتورة',
            'invoice_date.date' => 'تاريخ الفاتورة غير صحيح',
            'invoice_date.before_or_equal' => 'لا يمكن إدخال تاريخ في المستقبل',
            
            'items.required' => 'يجب إضافة صنف واحد على الأقل',
            'items.array' => 'بيانات الأصناف غير صحيحة',
            'items.min' => 'يجب إضافة صنف واحد على الأقل',
            
            'items.*.product_id.required' => 'يجب اختيار الصنف',
            'items.*.product_id.exists' => 'الصنف المحدد غير موجود',
            
            'items.*.qty.required' => 'يجب إدخال الكمية',
            'items.*.qty.numeric' => 'الكمية يجب أن تكون رقم',
            'items.*.qty.min' => 'الكمية يجب أن تكون أكبر من صفر',
            
            'items.*.price.required' => 'يجب إدخال السعر',
            'items.*.price.numeric' => 'السعر يجب أن يكون رقم',
            'items.*.price.min' => 'السعر لا يمكن أن يكون سالب',
        ];
    }

    /**
     * Handle a passed validation attempt.
     * يمكنك إضافة validations إضافية هنا
     */
    protected function passedValidation(): void
    {
        // مثال: التحقق من أن المورد نشط
        // $supplier = Supplier::find($this->supplier_id);
        // if (!$supplier->is_active) {
        //     throw ValidationException::withMessages([
        //         'supplier_id' => 'المورد غير نشط'
        //     ]);
        // }
    }
}