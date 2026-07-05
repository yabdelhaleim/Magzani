<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * إصلاح #16: name_ar بدلاً من name
     */
    public function rules(): array
    {
        $accountId = $this->route('account')?->id ?? $this->route('account');

        return [
            'code'               => "required|string|max:20|unique:accounts,code,{$accountId}",
            'name_ar'            => 'required|string|max:200',   // ✅ name_ar
            'name_en'            => 'nullable|string|max:200',
            'account_type_id'    => 'required|exists:account_types,id',
            'parent_id'          => "nullable|exists:accounts,id",
            'is_active'          => 'boolean',
            'allow_sub_accounts' => 'boolean',
            'description'        => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique'      => 'رمز الحساب مستخدم من حساب آخر.',
            'name_ar.required' => 'اسم الحساب بالعربية مطلوب.',
        ];
    }
}
