<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_date'        => 'required|date',
            'description'       => 'nullable|string|max:500',
            'reference'         => 'nullable|string|max:100',
            'amount'            => 'required|numeric|min:0.01',
            // سند قبض: حساب نقدية/بنك (مدين) + حساب دائن
            'cash_account_id'   => 'required|exists:accounts,id',
            'credit_account_id' => 'required_without:debit_account_id|exists:accounts,id',
            // سند صرف: حساب مدين + حساب نقدية/بنك (دائن)
            'debit_account_id'  => 'required_without:credit_account_id|exists:accounts,id',
        ];
    }

    public function messages(): array
    {
        return [
            'entry_date.required'       => 'تاريخ السند مطلوب.',
            'amount.required'           => 'المبلغ مطلوب.',
            'amount.min'                => 'المبلغ يجب أن يكون أكبر من صفر.',
            'cash_account_id.required'  => 'حساب الصندوق/البنك مطلوب.',
            'cash_account_id.exists'    => 'حساب الصندوق/البنك غير موجود.',
            'credit_account_id.exists'  => 'الحساب الدائن غير موجود.',
            'debit_account_id.exists'   => 'الحساب المدين غير موجود.',
        ];
    }
}
