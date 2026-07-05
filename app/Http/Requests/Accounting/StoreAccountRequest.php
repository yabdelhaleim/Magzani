<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * إصلاح #16: اسم الحقل name_ar يطابق ChartOfAccountsService::create() و Account model
     */
    public function rules(): array
    {
        return [
            'code'               => 'required|string|max:20|unique:accounts,code',
            'name_ar'            => 'required|string|max:200',   // ✅ name_ar بدلاً من name
            'name_en'            => 'nullable|string|max:200',
            'account_type_id'    => 'required|exists:account_types,id',
            'parent_id'          => 'nullable|exists:accounts,id',
            'is_active'          => 'boolean',
            'allow_sub_accounts' => 'boolean',
            'description'        => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'           => 'رمز الحساب مطلوب.',
            'code.unique'             => 'رمز الحساب مستخدم مسبقاً.',
            'name_ar.required'        => 'اسم الحساب بالعربية مطلوب.',
            'account_type_id.exists'  => 'نوع الحساب غير صحيح.',
        ];
    }
}
