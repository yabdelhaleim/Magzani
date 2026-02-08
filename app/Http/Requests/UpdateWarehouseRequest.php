<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $warehouseId = $this->route('warehouse');

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100',
                Rule::unique('warehouses', 'name')->ignore($warehouseId),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('warehouses', 'code')->ignore($warehouseId),
                'regex:/^[A-Z0-9\-_]+$/',
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
                'email:rfc,dns',
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

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المخزن مطلوب',
            'name.min' => 'اسم المخزن يجب أن يكون 3 أحرف على الأقل',
            'name.max' => 'اسم المخزن طويل جداً',
            'name.unique' => 'اسم المخزن مستخدم من قبل',
            
            'code.required' => 'كود المخزن مطلوب',
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

    protected function prepareForValidation()
    {
        if ($this->has('code') && !empty($this->code)) {
            $this->merge([
                'code' => strtoupper(trim($this->code)),
            ]);
        }

        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/[^0-9+\-\s()]/', '', $this->phone),
            ]);
        }

        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // التحقق من تغيير الحالة
            $warehouse = \App\Models\Warehouse::find($this->route('warehouse'));
            
            if ($warehouse && $this->is_active === false && $warehouse->is_active === true) {
                // التحقق من عدم وجود تحويلات معلقة
                $hasPendingTransfers = \Illuminate\Support\Facades\DB::table('warehouse_transfers')
                    ->where(function($q) use ($warehouse) {
                        $q->where('from_warehouse_id', $warehouse->id)
                          ->orWhere('to_warehouse_id', $warehouse->id);
                    })
                    ->whereIn('status', ['draft', 'pending', 'in_transit'])
                    ->exists();

                if ($hasPendingTransfers) {
                    $validator->errors()->add(
                        'is_active',
                        'لا يمكن تعطيل المخزن لوجود تحويلات معلقة'
                    );
                }
            }

            // تحديث اسم المدير
            if ($this->has('manager_id') && !empty($this->manager_id)) {
                if (empty($this->manager_name)) {
                    $user = \App\Models\User::find($this->manager_id);
                    if ($user) {
                        $this->merge(['manager_name' => $user->name]);
                    }
                }
            }
        });
    }
}