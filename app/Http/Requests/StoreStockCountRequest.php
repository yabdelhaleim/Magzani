<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockCountRequest extends FormRequest
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
        return [
            'warehouse_id' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],
            'count_date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after:' . now()->subYear()->format('Y-m-d'),
            ],
            'count_type' => [
                'required',
                Rule::in(['full', 'partial', 'random']),
            ],
            'product_ids' => [
                'required_if:count_type,partial',
                'nullable',
                'array',
                'min:1',
                'max:1000',
            ],
            'product_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('products', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],
            'random_count' => [
                'required_if:count_type,random',
                'nullable',
                'integer',
                'min:1',
                'max:500',
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
            'warehouse_id.required' => 'يجب اختيار المخزن',
            'warehouse_id.exists' => 'المخزن المحدد غير موجود أو غير نشط',
            
            'count_date.required' => 'يجب تحديد تاريخ الجرد',
            'count_date.before_or_equal' => 'تاريخ الجرد لا يمكن أن يكون في المستقبل',
            'count_date.after' => 'تاريخ الجرد قديم جداً',
            
            'count_type.required' => 'يجب اختيار نوع الجرد',
            'count_type.in' => 'نوع الجرد غير صحيح',
            
            'product_ids.required_if' => 'يجب اختيار المنتجات للجرد الجزئي',
            'product_ids.min' => 'يجب اختيار منتج واحد على الأقل',
            'product_ids.max' => 'عدد المنتجات يتجاوز الحد المسموح (1000 منتج)',
            'product_ids.*.exists' => 'أحد المنتجات المحددة غير موجود أو غير نشط',
            'product_ids.*.distinct' => 'يوجد منتجات مكررة',
            
            'random_count.required_if' => 'يجب تحديد عدد المنتجات للجرد العشوائي',
            'random_count.min' => 'يجب اختيار منتج واحد على الأقل',
            'random_count.max' => 'العدد العشوائي يتجاوز الحد المسموح (500 منتج)',
            
            'notes.max' => 'الملاحظات طويلة جداً',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'warehouse_id' => 'المخزن',
            'count_date' => 'تاريخ الجرد',
            'count_type' => 'نوع الجرد',
            'product_ids' => 'المنتجات',
            'random_count' => 'العدد العشوائي',
            'notes' => 'الملاحظات',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // تنظيف البيانات قبل التحقق
        if ($this->has('product_ids') && is_array($this->product_ids)) {
            $this->merge([
                'product_ids' => array_values(array_unique(array_filter($this->product_ids))),
            ]);
        }
        
        // تعيين تاريخ افتراضي إذا لم يحدد
        if (!$this->has('count_date')) {
            $this->merge([
                'count_date' => now()->format('Y-m-d'),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // التحقق من عدم وجود جرد نشط لنفس المخزن
            if ($this->warehouse_id) {
                $activeCount = \App\Models\StockCount::where('warehouse_id', $this->warehouse_id)
                    ->whereIn('status', ['draft', 'in_progress'])
                    ->exists();
                    
                if ($activeCount) {
                    $validator->errors()->add(
                        'warehouse_id',
                        'يوجد جرد نشط بالفعل لهذا المخزن. يجب إكماله أولاً'
                    );
                }
            }
            
            // التحقق من وجود منتجات في المخزن للجرد الكامل
            if ($this->count_type === 'full' && $this->warehouse_id) {
                $productsCount = \App\Models\ProductWarehouse::where('warehouse_id', $this->warehouse_id)
                    ->whereHas('product', function ($query) {
                        $query->where('is_active', true);
                    })
                    ->count();
                    
                if ($productsCount === 0) {
                    $validator->errors()->add(
                        'warehouse_id',
                        'لا يوجد منتجات نشطة في هذا المخزن'
                    );
                }
            }
            
            // التحقق من وجود منتجات كافية للجرد العشوائي
            if ($this->count_type === 'random' && $this->warehouse_id && $this->random_count) {
                $productsCount = \App\Models\ProductWarehouse::where('warehouse_id', $this->warehouse_id)
                    ->whereHas('product', function ($query) {
                        $query->where('is_active', true);
                    })
                    ->count();
                    
                if ($productsCount === 0) {
                    $validator->errors()->add(
                        'warehouse_id',
                        'لا يوجد منتجات نشطة في هذا المخزن'
                    );
                } elseif ($this->random_count > $productsCount) {
                    $validator->errors()->add(
                        'random_count',
                        "العدد المطلوب ({$this->random_count}) أكبر من عدد المنتجات المتاحة ({$productsCount})"
                    );
                }
            }
            
            // التحقق من أن المنتجات المحددة موجودة في المخزن (للجرد الجزئي)
            if ($this->count_type === 'partial' && $this->warehouse_id && $this->product_ids) {
                $validProducts = \App\Models\ProductWarehouse::where('warehouse_id', $this->warehouse_id)
                    ->whereIn('product_id', $this->product_ids)
                    ->whereHas('product', function ($query) {
                        $query->where('is_active', true);
                    })
                    ->pluck('product_id')
                    ->toArray();
                
                $invalidProducts = array_diff($this->product_ids, $validProducts);
                
                if (!empty($invalidProducts)) {
                    $validator->errors()->add(
                        'product_ids',
                        'بعض المنتجات المحددة غير موجودة في هذا المخزن أو غير نشطة'
                    );
                }
            }
        });
    }
}