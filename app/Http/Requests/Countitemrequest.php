<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CountItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'actual_quantity' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.999',
                'regex:/^\d+(\.\d{1,3})?$/', // 3 decimal places max
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'actual_quantity.required' => 'يجب إدخال الكمية الفعلية',
            'actual_quantity.numeric' => 'الكمية يجب أن تكون رقم',
            'actual_quantity.min' => 'الكمية لا يمكن أن تكون سالبة',
            'actual_quantity.max' => 'الكمية كبيرة جداً',
            'actual_quantity.regex' => 'الكمية يجب أن تكون حتى 3 خانات عشرية',
            'notes.max' => 'الملاحظات طويلة جداً',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // التحقق من حالة الجرد
            $itemId = $this->route('itemId') ?? $this->route('item');
            
            if ($itemId) {
                $item = \App\Models\StockCountItem::find($itemId);
                
                if (!$item) {
                    $validator->errors()->add('item', 'عنصر الجرد غير موجود');
                    return;
                }
                
                if ($item->stockCount->status !== 'in_progress') {
                    $validator->errors()->add(
                        'actual_quantity',
                        'الجرد ليس في حالة التنفيذ'
                    );
                }
                
                if ($item->status !== 'pending') {
                    $validator->errors()->add(
                        'actual_quantity',
                        'تم جرد هذا المنتج مسبقاً'
                    );
                }
            }
        });
    }
}