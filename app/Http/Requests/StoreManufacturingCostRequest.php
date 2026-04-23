<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreManufacturingCostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_name' => 'required|string|min:2|max:255',
            'product_id' => 'nullable|integer|exists:products,id',
            'price_per_cubic_meter' => 'required|numeric|min:0|max:999999999.99',
            'labor_cost' => 'nullable|numeric|min:0',
            'nails_hardware_cost' => 'nullable|numeric|min:0',
            'transportation_cost' => 'nullable|numeric|min:0',
            'tips_misc_cost' => 'nullable|numeric|min:0',
            'fumigation_cost' => 'nullable|numeric|min:0',
            'profit_percentage' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:2000',
            'components' => 'required|array|min:1',
            'components.*.component_name' => 'required|string|max:255',
            'components.*.quantity' => 'required|numeric|min:0.01|max:99999',
            'components.*.length_cm' => 'required|numeric|min:0.01|max:99999',
            'components.*.width_cm' => 'required|numeric|min:0.01|max:99999',
            'components.*.thickness_cm' => 'required|numeric|min:0.01|max:99999',
        ];
    }

    public function messages(): array
    {
        return [
            'product_name.required' => 'اسم المنتج مطلوب',
            'product_name.min' => 'اسم المنتج يجب أن يكون حرفين على الأقل',
            'price_per_cubic_meter.required' => 'سعر المتر المكعب مطلوب',
            'price_per_cubic_meter.numeric' => 'سعر المتر المكعب يجب أن يكون رقماً',
            'price_per_cubic_meter.min' => 'سعر المتر المكعب يجب أن يكون قيمة موجبة',
            'labor_cost.numeric' => 'تكلفة العمالة يجب أن تكون رقماً',
            'nails_hardware_cost.numeric' => 'تكلفة المسامير يجب أن تكون رقماً',
            'transportation_cost.numeric' => 'تكلفة النقل يجب أن تكون رقماً',
            'tips_misc_cost.numeric' => 'التكلفة الإضافية يجب أن تكون رقماً',
            'fumigation_cost.numeric' => 'تكلفة التطهير يجب أن تكون رقماً',
            'profit_percentage.required' => 'نسبة الربح مطلوبة',
            'profit_percentage.numeric' => 'نسبة الربح يجب أن تكون رقماً',
            'profit_percentage.min' => 'نسبة الربح يجب أن تكون قيمة موجبة',
            'profit_percentage.max' => 'نسبة الربح لا يمكن أن تتجاوز 100',
            'notes.max' => 'الملاحظات لا يمكن أن تتجاوز 2000 حرف',
            'components.required' => 'يجب إضافة قطعة واحدة على الأقل',
            'components.min' => 'يجب إضافة قطعة واحدة على الأقل',
            'components.*.component_name.required' => 'اسم القطعة مطلوب',
            'components.*.quantity.required' => 'الكمية مطلوبة',
            'components.*.quantity.min' => 'الكمية يجب أن تكون أكبر من صفر',
            'components.*.length_cm.required' => 'الطول مطلوب',
            'components.*.length_cm.min' => 'الطول يجب أن يكون أكبر من صفر',
            'components.*.width_cm.required' => 'العرض مطلوب',
            'components.*.width_cm.min' => 'العرض يجب أن يكون أكبر من صفر',
            'components.*.thickness_cm.required' => 'السمك مطلوب',
            'components.*.thickness_cm.min' => 'السمك يجب أن يكون أكبر من صفر',
        ];
    }

    public function attributes(): array
    {
        return [
            'product_name' => 'اسم المنتج',
            'price_per_cubic_meter' => 'سعر المتر المكعب',
            'labor_cost' => 'تكلفة العمالة',
            'nails_hardware_cost' => 'تكلفة المسامير',
            'transportation_cost' => 'تكلفة النقل',
            'tips_misc_cost' => 'تكاليف متنوعة',
            'fumigation_cost' => 'تكلفة التطهير',
            'profit_percentage' => 'نسبة الربح',
            'notes' => 'ملاحظات',
            'components' => 'القطع',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'labor_cost' => $this->input('labor_cost', 0) ?: 0,
            'nails_hardware_cost' => $this->input('nails_hardware_cost', 0) ?: 0,
            'transportation_cost' => $this->input('transportation_cost', 0) ?: 0,
            'tips_misc_cost' => $this->input('tips_misc_cost', 0) ?: 0,
            'fumigation_cost' => $this->input('fumigation_cost', 0) ?: 0,
        ]);
    }
}
