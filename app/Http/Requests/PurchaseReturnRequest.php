<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseReturnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // بيانات المرتجع الأساسية
            'purchase_invoice_id' => ['required', 'exists:purchase_invoices,id'],
            'return_date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['nullable', 'in:draft,confirmed,cancelled'],
            
            // بيانات الأصناف المرتجعة
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_invoice_item_id' => ['required', 'exists:purchase_invoice_items,id'],
            'items.*.quantity_returned' => ['required', 'numeric', 'min:0.001'],
            'items.*.item_condition' => ['required', 'in:good,damaged,defective'],
            'items.*.return_reason' => ['required', 'string', 'max:500'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
            
            // المبالغ (اختيارية)
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            
            // حقول اختيارية
            'return_reason' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'purchase_invoice_id' => 'فاتورة الشراء',
            'return_date' => 'تاريخ المرتجع',
            'status' => 'الحالة',
            'items' => 'الأصناف',
            'items.*.purchase_invoice_item_id' => 'الصنف',
            'items.*.quantity_returned' => 'الكمية المرتجعة',
            'items.*.item_condition' => 'حالة الصنف',
            'items.*.return_reason' => 'سبب الإرجاع',
            'items.*.notes' => 'ملاحظات الصنف',
            'discount_amount' => 'الخصم',
            'tax_amount' => 'الضريبة',
            'return_reason' => 'سبب الإرجاع العام',
            'notes' => 'الملاحظات',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'purchase_invoice_id.required' => 'يجب اختيار فاتورة الشراء',
            'purchase_invoice_id.exists' => 'فاتورة الشراء المحددة غير موجودة',
            
            'return_date.required' => 'يجب إدخال تاريخ المرتجع',
            'return_date.date' => 'تاريخ المرتجع غير صحيح',
            'return_date.before_or_equal' => 'لا يمكن إدخال تاريخ في المستقبل',
            
            'status.in' => 'حالة المرتجع غير صحيحة',
            
            'items.required' => 'يجب إضافة صنف واحد على الأقل للإرجاع',
            'items.array' => 'بيانات الأصناف غير صحيحة',
            'items.min' => 'يجب إضافة صنف واحد على الأقل',
            
            'items.*.purchase_invoice_item_id.required' => 'يجب اختيار الصنف',
            'items.*.purchase_invoice_item_id.exists' => 'الصنف المحدد غير موجود',
            
            'items.*.quantity_returned.required' => 'يجب إدخال الكمية المرتجعة',
            'items.*.quantity_returned.numeric' => 'الكمية يجب أن تكون رقم',
            'items.*.quantity_returned.min' => 'الكمية يجب أن تكون أكبر من صفر',
            
            'items.*.item_condition.required' => 'يجب تحديد حالة الصنف',
            'items.*.item_condition.in' => 'حالة الصنف غير صحيحة',
            
            'items.*.return_reason.required' => 'يجب إدخال سبب الإرجاع',
            'items.*.return_reason.max' => 'سبب الإرجاع طويل جداً',
        ];
    }

    /**
     * تحقق إضافي: الكمية المرتجعة لا تتجاوز الكمية الأصلية
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->has('items')) {
                return;
            }

            foreach ($this->items as $index => $item) {
                // جلب بيانات الصنف من الفاتورة الأصلية
                $originalItem = \App\Models\PurchaseInvoiceItem::find($item['purchase_invoice_item_id']);
                
                if ($originalItem) {
                    // حساب المرتجع السابق
                    $previousReturns = \App\Models\PurchaseReturnItem::where('purchase_invoice_item_id', $originalItem->id)
                        ->sum('quantity_returned');
                    
                    $availableQty = $originalItem->qty - $previousReturns;
                    
                    if ($item['quantity_returned'] > $availableQty) {
                        $validator->errors()->add(
                            "items.{$index}.quantity_returned",
                            "الكمية المرتجعة ({$item['quantity_returned']}) أكبر من الكمية المتاحة ({$availableQty})"
                        );
                    }
                }
            }
        });
    }
}