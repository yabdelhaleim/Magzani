<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkPriceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'base_unit' => 'required|string|max:50',
            'category' => 'required|string|max:255',
            'base_purchase_price' => 'required|numeric|min:0|max:9999999.99',
            'profit_type' => 'required|in:percentage,fixed',
            'profit_value' => 'required|numeric|min:0',
            'selected_products' => 'required|json',
        ];
    }

    public function messages(): array
    {
        return [
            'base_unit.required' => 'وحدة القياس مطلوبة',
            'category.required' => 'التصنيف مطلوب',
            'base_purchase_price.required' => 'سعر الشراء مطلوب',
            'profit_type.required' => 'نوع هامش الربح مطلوب',
            'profit_type.in' => 'نوع هامش الربح يجب أن يكون نسبة مئوية أو ثابت',
            'profit_value.required' => 'قيمة هامش الربح مطلوبة',
            'profit_value.min' => 'قيمة هامش الربح يجب أن تكون أكبر من أو تساوي صفر',
            'selected_products.required' => 'يجب تحديد منتجات للتحديث',
            'selected_products.json' => 'صيغة المنتجات المحددة غير صحيحة',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // ✅ التحقق من صحة JSON
            $selected = json_decode($this->selected_products, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $validator->errors()->add('selected_products', 'صيغة JSON غير صحيحة');
                return;
            }

            if (empty($selected) || !is_array($selected)) {
                $validator->errors()->add('selected_products', 'يجب تحديد منتج واحد على الأقل');
                return;
            }

            if (count($selected) > 5000) {
                $validator->errors()->add('selected_products', 'لا يمكن تحديث أكثر من 5000 منتج دفعة واحدة');
            }
        });
    }
}