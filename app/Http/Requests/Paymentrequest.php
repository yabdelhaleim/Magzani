<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => 'required|string|in:supplier,customer',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'method' => 'required|string|in:cash,bank,check,card',
            'date' => 'nullable|date|before_or_equal:today',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'customer_id' => 'nullable|exists:customers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'account_id' => 'nullable|exists:accounts,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'نوع الدفعة مطلوب',
            'type.in' => 'نوع الدفعة غير صحيح',
            
            'name.required' => 'الاسم مطلوب',
            'name.string' => 'الاسم يجب أن يكون نصاً',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرف',
            
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'amount.max' => 'المبلغ كبير جداً',
            
            'method.required' => 'طريقة الدفع مطلوبة',
            'method.in' => 'طريقة الدفع غير صحيحة',
            
            'date.date' => 'التاريخ غير صحيح',
            'date.before_or_equal' => 'التاريخ لا يمكن أن يكون في المستقبل',
            
            'reference.string' => 'رقم المرجع يجب أن يكون نصاً',
            'reference.max' => 'رقم المرجع يجب ألا يتجاوز 100 حرف',
            
            'notes.string' => 'الملاحظات يجب أن تكون نصاً',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف',
            
            'supplier_id.exists' => 'المورد المحدد غير موجود',
            'customer_id.exists' => 'العميل المحدد غير موجود',
            'invoice_id.exists' => 'الفاتورة المحددة غير موجودة',
            'account_id.exists' => 'الحساب المحدد غير موجود',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'type' => 'نوع الدفعة',
            'name' => 'الاسم',
            'amount' => 'المبلغ',
            'method' => 'طريقة الدفع',
            'date' => 'التاريخ',
            'reference' => 'رقم المرجع',
            'notes' => 'الملاحظات',
            'supplier_id' => 'المورد',
            'customer_id' => 'العميل',
            'invoice_id' => 'الفاتورة',
            'account_id' => 'الحساب',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Set default date to today if not provided
        if (!$this->has('date') || empty($this->date)) {
            $this->merge(['date' => now()->toDateString()]);
        }

        // Convert empty strings to null
        if ($this->reference === '') {
            $this->merge(['reference' => null]);
        }

        if ($this->notes === '') {
            $this->merge(['notes' => null]);
        }

        // Ensure amount is properly formatted
        if ($this->has('amount')) {
            $this->merge([
                'amount' => floatval(str_replace(',', '', $this->amount))
            ]);
        }

        // Auto-set supplier_id or customer_id based on type if not provided
        // This would require additional logic based on your system
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate payment date is not too old
            if ($this->has('date')) {
                $date = \Carbon\Carbon::parse($this->date);
                $oneYearAgo = \Carbon\Carbon::now()->subYear();
                
                if ($date->lt($oneYearAgo)) {
                    $validator->errors()->add('date', 'لا يمكن تسجيل دفعة أقدم من سنة');
                }
            }

            // Large payments require reference
            if ($this->has('amount') && $this->amount > 50000 && empty($this->reference)) {
                $validator->errors()->add('reference', 'المدفوعات الكبيرة (أكثر من 50,000) تتطلب رقم مرجع');
            }

            // Bank transfers and checks require reference
            if (in_array($this->method, ['bank', 'check']) && empty($this->reference)) {
                $validator->errors()->add('reference', 'التحويلات البنكية والشيكات تتطلب رقم مرجع');
            }

            // Validate type-specific requirements
            if ($this->type === 'supplier' && $this->has('supplier_id') && empty($this->supplier_id)) {
                // Optional: warn if supplier_id is missing
            }

            if ($this->type === 'customer' && $this->has('customer_id') && empty($this->customer_id)) {
                // Optional: warn if customer_id is missing
            }
        });
    }

    /**
     * Get the payment type labels in Arabic
     */
    public static function getTypeLabels(): array
    {
        return [
            'supplier' => 'دفع لمورد',
            'customer' => 'قبض من عميل',
        ];
    }

    /**
     * Get the payment method labels in Arabic
     */
    public static function getMethodLabels(): array
    {
        return [
            'cash' => 'نقدي',
            'bank' => 'تحويل بنكي',
            'check' => 'شيك',
            'card' => 'بطاقة',
        ];
    }

    /**
     * Get validation rules for bulk operations
     */
    public static function bulkRules(): array
    {
        return [
            'payment_ids' => 'required|array|min:1',
            'payment_ids.*' => 'required|exists:payments,id',
            'action' => 'required|string|in:verify,cancel,delete',
            'reason' => 'required_if:action,cancel|string|max:500',
        ];
    }

    /**
     * Get bulk operation messages
     */
    public static function bulkMessages(): array
    {
        return [
            'payment_ids.required' => 'يجب تحديد دفعة واحدة على الأقل',
            'payment_ids.array' => 'صيغة البيانات غير صحيحة',
            'payment_ids.min' => 'يجب تحديد دفعة واحدة على الأقل',
            'payment_ids.*.exists' => 'إحدى الدفعات المحددة غير موجودة',
            'action.required' => 'يجب تحديد الإجراء المطلوب',
            'action.in' => 'الإجراء المحدد غير صحيح',
            'reason.required_if' => 'سبب الإلغاء مطلوب',
            'reason.string' => 'السبب يجب أن يكون نصاً',
            'reason.max' => 'السبب يجب ألا يتجاوز 500 حرف',
        ];
    }
}