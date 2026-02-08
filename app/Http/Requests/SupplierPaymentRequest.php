<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierPaymentRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_date' => 'required|date|before_or_equal:today',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'method' => 'nullable|string|max:100|in:نقدي,تحويل بنكي,شيك,آجل',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'supplier_id' => 'المورد',
            'payment_date' => 'تاريخ السداد',
            'amount' => 'المبلغ',
            'method' => 'طريقة الدفع',
            'notes' => 'الملاحظات',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'supplier_id.required' => 'يجب اختيار المورد',
            'supplier_id.exists' => 'المورد المحدد غير موجود',
            
            'payment_date.required' => 'يجب إدخال تاريخ السداد',
            'payment_date.date' => 'تاريخ السداد غير صحيح',
            'payment_date.before_or_equal' => 'تاريخ السداد يجب أن لا يكون في المستقبل',
            
            'amount.required' => 'يجب إدخال المبلغ',
            'amount.numeric' => 'المبلغ يجب أن يكون رقم',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'amount.max' => 'المبلغ يجب أن لا يتجاوز 999,999,999.99',
            
            'method.in' => 'طريقة الدفع غير صحيحة',
            'method.max' => 'طريقة الدفع يجب أن لا تتجاوز 100 حرف',
            
            'notes.max' => 'الملاحظات يجب أن لا تتجاوز 1000 حرف',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // التحقق من أن المورد نشط
            if ($this->supplier_id) {
                $supplier = \App\Models\Supplier::find($this->supplier_id);
                
                if ($supplier && !$supplier->is_active) {
                    $validator->errors()->add(
                        'supplier_id',
                        'لا يمكن إضافة سداد لمورد غير نشط'
                    );
                }
            }
        });
    }
}