<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
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
        $supplierId = $this->route('supplier') ? $this->route('supplier')->id : null;

        return [
            // البيانات الأساسية
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:suppliers,code,' . $supplierId],
            
            // معلومات الاتصال
            'phone' => ['nullable', 'string', 'max:20'],
            'phone2' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            
            // العنوان
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            
            // المالية
            'opening_balance' => ['nullable', 'numeric'],
            
            // الحالة
            'is_active' => ['nullable', 'boolean'],
            
            // ملاحظات
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم المورد',
            'code' => 'كود المورد',
            'phone' => 'رقم الهاتف',
            'phone2' => 'رقم الهاتف 2',
            'email' => 'البريد الإلكتروني',
            'contact_person' => 'اسم جهة الاتصال',
            'address' => 'العنوان',
            'city' => 'المدينة',
            'country' => 'الدولة',
            'opening_balance' => 'الرصيد الافتتاحي',
            'is_active' => 'الحالة',
            'notes' => 'الملاحظات',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'يجب إدخال اسم المورد',
            'name.max' => 'اسم المورد طويل جداً',
            
            'code.required' => 'يجب إدخال كود المورد',
            'code.unique' => 'كود المورد مستخدم من قبل',
            'code.max' => 'كود المورد طويل جداً',
            
            'phone.max' => 'رقم الهاتف طويل جداً',
            'phone2.max' => 'رقم الهاتف 2 طويل جداً',
            
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.max' => 'البريد الإلكتروني طويل جداً',
            
            'opening_balance.numeric' => 'الرصيد الافتتاحي يجب أن يكون رقم',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // توليد كود تلقائي إذا لم يتم إدخاله
        if (!$this->code && $this->isMethod('post')) {
            $this->merge([
                'code' => 'SUP-' . str_pad((\App\Models\Supplier::count() + 1), 5, '0', STR_PAD_LEFT)
            ]);
        }

        // تعيين قيم افتراضية
        $this->merge([
            'is_active' => $this->has('is_active') ? (bool) $this->is_active : true,
            'opening_balance' => $this->opening_balance ?? 0,
            'country' => $this->country ?? 'Egypt',
        ]);
    }
}