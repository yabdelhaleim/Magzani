<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // ✅ مهم جداً
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'unique:warehouses,name',
            ],
            'code' => [
                'nullable', // اختياري - سيتم توليده تلقائياً
                'string',
                'max:50',
                'unique:warehouses,code',
                'regex:/^[A-Z0-9\-_]+$/', // فقط أحرف كبيرة وأرقام
            ],
            'status' => [
                'required',
                Rule::in(['active', 'inactive', 'maintenance']),
            ],
            'city' => [
                'nullable',
                'string',
                'max:50',
            ],
            'area' => [
                'nullable',
                'string',
                'max:50',
            ],
            'location' => [
                'nullable',
                'string',
                'max:255',
            ],
            'address' => [
                'nullable',
                'string',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],
            'email' => [
                'nullable',
                'email', // بدون rfc,dns عشان ميعملش مشاكل
                'max:100',
            ],
            'manager_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'manager_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المخزن مطلوب',
            'name.min' => 'اسم المخزن يجب أن يكون 3 أحرف على الأقل',
            'name.max' => 'اسم المخزن طويل جداً (الحد الأقصى 100 حرف)',
            'name.unique' => 'اسم المخزن مستخدم من قبل',
            
            'code.unique' => 'كود المخزن مستخدم من قبل',
            'code.regex' => 'كود المخزن يجب أن يحتوي على أحرف كبيرة وأرقام فقط',
            
            'status.required' => 'حالة المخزن مطلوبة',
            'status.in' => 'حالة المخزن غير صحيحة',
            
            'phone.regex' => 'رقم الهاتف غير صحيح',
            
            'email.email' => 'البريد الإلكتروني غير صحيح',
            
            'manager_id.exists' => 'المدير المختار غير موجود',
            
            'is_active.required' => 'يجب تحديد حالة النشاط',
            'is_active.boolean' => 'حالة النشاط غير صحيحة',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم المخزن',
            'code' => 'كود المخزن',
            'status' => 'الحالة',
            'city' => 'المدينة',
            'area' => 'المنطقة',
            'location' => 'الموقع',
            'address' => 'العنوان',
            'phone' => 'رقم الهاتف',
            'email' => 'البريد الإلكتروني',
            'manager_name' => 'اسم المسؤول',
            'manager_id' => 'المدير',
            'description' => 'الوصف',
            'is_active' => 'حالة النشاط',
            'notes' => 'الملاحظات',
        ];
    }

    /**
     * ✅ تحضير البيانات قبل الـ Validation
     */
    protected function prepareForValidation(): void
    {
        // تنظيف الكود - تحويل لأحرف كبيرة
        if ($this->has('code') && !empty($this->code)) {
            $this->merge([
                'code' => strtoupper(trim($this->code)),
            ]);
        }

        // تنظيف رقم الهاتف
        if ($this->has('phone') && !empty($this->phone)) {
            $this->merge([
                'phone' => preg_replace('/[^0-9+\-\s()]/', '', $this->phone),
            ]);
        }

        // تحويل is_active لـ boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /**
     * ✅ Validation إضافي بعد القواعد الأساسية
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // التحقق من وجود manager_name أو manager_id
            if ($this->has('manager_id') && !empty($this->manager_id)) {
                if (empty($this->manager_name)) {
                    // جلب اسم المدير تلقائياً
                    $user = \App\Models\User::find($this->manager_id);
                    if ($user) {
                        $this->merge(['manager_name' => $user->name]);
                    }
                }
            }
        });
    }
}
