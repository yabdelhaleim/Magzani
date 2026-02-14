<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ProductWarehouse;

class TransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    // protected function prepareForValidation()
    // {
    //     // ✅ التأكد من أن items دائماً array حتى لو فارغة
    //     if (!$this->has('items') || !is_array($this->items)) {
    //         $this->merge([
    //             'items' => []
    //         ]);
    //     }
    // }

    /**
     * Get the validation rules that apply to the request.
     */
public function rules(): array
{
    return [
        'from_warehouse_id' => [
            'required',
            'integer',
            'exists:warehouses,id',
            'different:to_warehouse_id',
        ],
        'to_warehouse_id' => [
            'required',
            'integer',
            'exists:warehouses,id',
        ],
        'transfer_date' => 'required|date|before_or_equal:today',
        'notes' => 'nullable|string|max:1000',
        
        // ✅ تأكد إن items موجودة أولاً
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|integer|distinct|exists:products,id',
        'items.*.quantity' => 'required|numeric|min:0.01|max:999999.99',
        'items.*.notes' => 'nullable|string|max:500',
    ];
}                                                                                                                                                                           

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'from_warehouse_id.required' => 'يجب اختيار المخزن المصدر',
            'from_warehouse_id.exists' => 'المخزن المصدر غير موجود',
            'from_warehouse_id.different' => 'لا يمكن التحويل إلى نفس المخزن',
            
            'to_warehouse_id.required' => 'يجب اختيار المخزن الوجهة',
            'to_warehouse_id.exists' => 'المخزن الوجهة غير موجود',
            
            'transfer_date.required' => 'يجب تحديد تاريخ التحويل',
            'transfer_date.date' => 'تاريخ التحويل غير صحيح',
            'transfer_date.before_or_equal' => 'تاريخ التحويل لا يمكن أن يكون في المستقبل',
            
            'expected_date.date' => 'تاريخ الاستلام المتوقع غير صحيح',
            'expected_date.after_or_equal' => 'تاريخ الاستلام يجب أن يكون بعد تاريخ التحويل',
            
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف',
            
            'items.required' => 'يجب إضافة منتج واحد على الأقل',
            'items.min' => 'يجب إضافة منتج واحد على الأقل',
            'items.array' => 'صيغة المنتجات غير صحيحة',
            
            'items.*.product_id.required' => 'يجب اختيار المنتج',
            'items.*.product_id.exists' => 'المنتج غير موجود',
            'items.*.product_id.distinct' => 'المنتج مكرر في القائمة',
            
            'items.*.quantity.required' => 'يجب تحديد الكمية',
            'items.*.quantity.numeric' => 'الكمية يجب أن تكون رقم',
            'items.*.quantity.min' => 'الكمية يجب أن تكون أكبر من صفر',
            'items.*.quantity.max' => 'الكمية تتجاوز الحد المسموح',
            
            'items.*.notes.max' => 'ملاحظات المنتج يجب ألا تتجاوز 500 حرف',
        ];
    }

    /**
     * Configure the validator instance with additional rules
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // التحقق من توفر المخزون لكل منتج - فقط إذا لم يكن هناك أخطاء سابقة
            if (!$validator->errors()->hasAny(['from_warehouse_id', 'items', 'items.*'])) {
                $this->validateStock($validator);
            }
        });
    }

    /**
     * التحقق من توفر المخزون
     */
    protected function validateStock($validator)
    {
        $fromWarehouseId = $this->input('from_warehouse_id');
        $items = $this->input('items', []);

        if (empty($items)) {
            return;
        }

        // جلب المخزون دفعة واحدة
        $productIds = array_column($items, 'product_id');
        
        $stocks = ProductWarehouse::where('warehouse_id', $fromWarehouseId)
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');

        foreach ($items as $index => $item) {
            $productId = $item['product_id'] ?? null;
            $quantity = $item['quantity'] ?? 0;

            if (!$productId) {
                continue;
            }

            // التحقق من وجود المنتج في المخزن
            if (!isset($stocks[$productId])) {
                $validator->errors()->add(
                    "items.{$index}.product_id",
                    "المنتج غير موجود في المخزن المصدر"
                );
                continue;
            }

            $stock = $stocks[$productId];
            $available = $stock->quantity - ($stock->reserved_quantity ?? 0);

            // التحقق من الكمية المتاحة
            if ($available < $quantity) {
                $validator->errors()->add(
                    "items.{$index}.quantity",
                    "الكمية المتاحة: " . number_format($available, 2) . "، المطلوبة: " . number_format($quantity, 2)
                );
            }
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'from_warehouse_id' => 'المخزن المصدر',
            'to_warehouse_id' => 'المخزن الوجهة',
            'transfer_date' => 'تاريخ التحويل',
            'expected_date' => 'تاريخ الاستلام المتوقع',
            'notes' => 'الملاحظات',
            'items' => 'المنتجات',
        ];
    }
}