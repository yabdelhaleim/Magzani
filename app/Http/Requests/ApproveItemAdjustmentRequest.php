<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveItemAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // يمكن إضافة التحقق من صلاحيات الموافقة هنا
        return true;
    }

    public function rules(): array
    {
        return [
            'approved' => [
                'required',
                'boolean',
            ],
            'approval_notes' => [
                'nullable',
                'string',
                'max:1000',
                'required_if:approved,false', // إلزامي عند الرفض
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'approved.required' => 'يجب تحديد حالة الموافقة',
            'approved.boolean' => 'حالة الموافقة غير صحيحة',
            'approval_notes.required_if' => 'يجب كتابة سبب الرفض',
            'approval_notes.max' => 'ملاحظات الموافقة طويلة جداً',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $itemId = $this->route('itemId') ?? $this->route('item');
            
            if ($itemId) {
                $item = \App\Models\StockCountItem::find($itemId);
                
                if (!$item) {
                    $validator->errors()->add('item', 'عنصر الجرد غير موجود');
                    return;
                }
                
                // التحقق من وجود فرق
                if ($item->variance == 0) {
                    $validator->errors()->add(
                        'approved',
                        'هذا المنتج ليس به فروقات'
                    );
                }
                
                // التحقق من عدم الموافقة مسبقاً
                if ($item->adjustment_approved && $item->approved_at) {
                    $validator->errors()->add(
                        'approved',
                        'تمت الموافقة على هذا المنتج مسبقاً'
                    );
                }
                
                // التحقق من حالة الجرد
                if (!in_array($item->status, ['counted', 'adjusted', 'skipped'])) {
                    $validator->errors()->add(
                        'approved',
                        'يجب جرد المنتج أولاً'
                    );
                }
            }
        });
    }
}