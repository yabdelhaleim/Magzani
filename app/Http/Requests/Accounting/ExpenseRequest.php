<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
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
            'type' => 'required|string|in:rent,salaries,utilities,maintenance,supplies,marketing,transportation,communication,insurance,taxes,other',
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|string|in:cash,bank,credit_card,check',
            'beneficiary' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'account_id' => 'nullable|exists:accounts,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'نوع المصروف مطلوب',
            'type.in' => 'نوع المصروف غير صحيح',
            
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'amount.max' => 'المبلغ كبير جداً',
            
            'date.required' => 'التاريخ مطلوب',
            'date.date' => 'التاريخ غير صحيح',
            'date.before_or_equal' => 'التاريخ لا يمكن أن يكون في المستقبل',
            
            'payment_method.required' => 'طريقة الدفع مطلوبة',
            'payment_method.in' => 'طريقة الدفع غير صحيحة',
            
            'beneficiary.string' => 'اسم المستفيد يجب أن يكون نصاً',
            'beneficiary.max' => 'اسم المستفيد يجب ألا يتجاوز 255 حرف',
            
            'invoice_number.string' => 'رقم الفاتورة يجب أن يكون نصاً',
            'invoice_number.max' => 'رقم الفاتورة يجب ألا يتجاوز 100 حرف',
            
            'notes.string' => 'الملاحظات يجب أن تكون نصاً',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف',
            
            'account_id.exists' => 'الحساب المحدد غير موجود',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'type' => 'نوع المصروف',
            'amount' => 'المبلغ',
            'date' => 'التاريخ',
            'payment_method' => 'طريقة الدفع',
            'beneficiary' => 'المستفيد',
            'invoice_number' => 'رقم الفاتورة',
            'notes' => 'الملاحظات',
            'account_id' => 'الحساب',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convert empty strings to null
        if ($this->beneficiary === '') {
            $this->merge(['beneficiary' => null]);
        }

        if ($this->invoice_number === '') {
            $this->merge(['invoice_number' => null]);
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
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if the expense date is not too old
            if ($this->has('date')) {
                $date = \Carbon\Carbon::parse($this->date);
                $sixMonthsAgo = \Carbon\Carbon::now()->subMonths(6);
                
                if ($date->lt($sixMonthsAgo)) {
                    $validator->errors()->add('date', 'لا يمكن إضافة مصروف أقدم من 6 أشهر');
                }
            }

            // Validate large amounts require notes
            if ($this->has('amount') && $this->amount > 10000 && empty($this->notes)) {
                $validator->errors()->add('notes', 'المصروفات الكبيرة (أكثر من 10,000) تتطلب ملاحظات توضيحية');
            }
        });
    }

    /**
     * Get the expense type labels in Arabic
     */
    public static function getTypeLabels(): array
    {
        return [
            'rent' => 'إيجار',
            'salaries' => 'رواتب',
            'utilities' => 'مرافق',
            'maintenance' => 'صيانة',
            'supplies' => 'مستلزمات',
            'marketing' => 'تسويق',
            'transportation' => 'مواصلات',
            'communication' => 'اتصالات',
            'insurance' => 'تأمينات',
            'taxes' => 'ضرائب',
            'other' => 'أخرى',
        ];
    }

    /**
     * Get the payment method labels in Arabic
     */
    public static function getPaymentMethodLabels(): array
    {
        return [
            'cash' => 'نقدي',
            'bank' => 'تحويل بنكي',
            'credit_card' => 'بطاقة ائتمان',
            'check' => 'شيك',
        ];
    }
}