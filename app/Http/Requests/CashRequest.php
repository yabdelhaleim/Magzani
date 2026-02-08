<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashRequest extends FormRequest
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
        $rules = [
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0.01',
        ];

        // Add conditional rules based on transaction type
        if ($this->isMethod('post')) {
            // For create operations
            switch ($this->route()->getName()) {
                case 'accounting.treasury.income.store':
                    $rules['account_id'] = 'required|exists:accounts,id';
                    $rules['source'] = 'nullable|string|max:255';
                    $rules['category'] = 'nullable|string|max:100';
                    break;

                case 'accounting.treasury.expense.store':
                    $rules['account_id'] = 'required|exists:accounts,id';
                    $rules['category'] = 'nullable|string|max:100';
                    $rules['beneficiary'] = 'nullable|string|max:255';
                    break;

                case 'accounting.treasury.transfer.store':
                    $rules['from_account_id'] = 'required|exists:accounts,id';
                    $rules['to_account_id'] = 'required|exists:accounts,id|different:from_account_id';
                    break;
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'description.required' => 'الوصف مطلوب',
            'description.string' => 'الوصف يجب أن يكون نصاً',
            'description.max' => 'الوصف يجب ألا يتجاوز 500 حرف',
            
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            
            'account_id.required' => 'الحساب مطلوب',
            'account_id.exists' => 'الحساب المحدد غير موجود',
            
            'from_account_id.required' => 'حساب المصدر مطلوب',
            'from_account_id.exists' => 'حساب المصدر غير موجود',
            
            'to_account_id.required' => 'حساب الوجهة مطلوب',
            'to_account_id.exists' => 'حساب الوجهة غير موجود',
            'to_account_id.different' => 'حساب الوجهة يجب أن يكون مختلفاً عن حساب المصدر',
            
            'source.string' => 'مصدر الإيراد يجب أن يكون نصاً',
            'source.max' => 'مصدر الإيراد يجب ألا يتجاوز 255 حرف',
            
            'category.string' => 'الفئة يجب أن تكون نصاً',
            'category.max' => 'الفئة يجب ألا تتجاوز 100 حرف',
            
            'beneficiary.string' => 'المستفيد يجب أن يكون نصاً',
            'beneficiary.max' => 'المستفيد يجب ألا يتجاوز 255 حرف',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'description' => 'الوصف',
            'amount' => 'المبلغ',
            'account_id' => 'الحساب',
            'from_account_id' => 'حساب المصدر',
            'to_account_id' => 'حساب الوجهة',
            'source' => 'المصدر',
            'category' => 'الفئة',
            'beneficiary' => 'المستفيد',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional custom validation
            if ($this->has('from_account_id') && $this->has('to_account_id')) {
                if ($this->from_account_id == $this->to_account_id) {
                    $validator->errors()->add('to_account_id', 'لا يمكن التحويل إلى نفس الحساب');
                }
            }
        });
    }
}