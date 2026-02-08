<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ===== المعلومات الأساسية =====
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                'unique:products,code',
            ],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                'unique:products,sku',
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                'unique:products,barcode',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            // ===== التصنيف والعلامة التجارية =====
            'category' => [
                'nullable',
                'string',
                'max:255',
            ],
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
            'brand_id' => [
                'nullable',
                'integer',
                'exists:brands,id',
            ],

            // ===== الوحدات =====
            'base_unit' => [
                'required',
                'string',
                'max:50',
            ],
            'base_unit_label' => [
                'required',
                'string',
                'max:50',
            ],
            'unit_id' => [
                'nullable',
                'integer',
                'exists:units,id',
            ],

            // ===== الأسعار ===== ✅ التعديل هنا
            'purchase_price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],
            'selling_price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999.99',
                // ✅ إزالة gte:min_selling_price
            ],
            'min_selling_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
                // ✅ سيتم التحقق منه في withValidator
            ],
            'wholesale_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],
            'tax_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'default_discount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            // ===== المخزون =====
            'stock_alert_quantity' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.999',
            ],
            'reorder_level' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.999',
            ],
            'reorder_quantity' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.999',
            ],
            'min_stock' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.999',
            ],
            'max_stock' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.999',
            ],

            // ===== المواصفات الفيزيائية =====
            'weight' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.999',
            ],
            'dimensions' => [
                'nullable',
                'string',
                'max:100',
            ],

            // ===== الصورة =====
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:5120', // 5MB
            ],

            // ===== الحالة =====
            'is_active' => [
                'required',
                'boolean',
            ],
            'is_featured' => [
                'nullable',
                'boolean',
            ],

            // ===== SEO (اختياري) =====
            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'meta_description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'meta_keywords' => [
                'nullable',
                'string',
                'max:255',
            ],

            // ===== وحدات البيع المتعددة =====
            'selling_units' => [
                'nullable',
                'array',
                'min:0',
                'max:20',
            ],
            'selling_units.*.unit_name' => [
                'required_with:selling_units',
                'string',
                'max:100',
            ],
            'selling_units.*.unit_code' => [
                'required_with:selling_units',
                'string',
                'max:50',
            ],
            'selling_units.*.conversion_factor' => [
                'required_with:selling_units',
                'numeric',
                'min:0.001',
                'max:999999.999',
            ],
            'selling_units.*.selling_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],
            'selling_units.*.purchase_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],
            'selling_units.*.is_default' => [
                'nullable',
                'boolean',
            ],
            'selling_units.*.is_active' => [
                'nullable',
                'boolean',
            ],
            'selling_units.*.display_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],

            // ===== المخازن والكميات =====
            'warehouses' => [
                'nullable',
                'array',
            ],
            'warehouses.*.warehouse_id' => [
                'required_with:warehouses',
                'integer',
                'exists:warehouses,id',
            ],
            'warehouses.*.quantity' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.999',
            ],
            'warehouses.*.min_stock' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.999',
            ],
            'warehouses.*.average_cost' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // المعلومات الأساسية
            'name.required' => 'اسم المنتج مطلوب',
            'name.min' => 'اسم المنتج يجب أن يكون حرفين على الأقل',
            'name.max' => 'اسم المنتج طويل جداً',
            
            'code.unique' => 'كود المنتج مستخدم من قبل',
            'sku.unique' => 'رمز SKU مستخدم من قبل',
            'barcode.unique' => 'الباركود مستخدم من قبل',
            
            // الوحدات
            'base_unit.required' => 'الوحدة الأساسية مطلوبة',
            'base_unit_label.required' => 'اسم الوحدة الأساسية مطلوب',
            
            // الأسعار
            'purchase_price.required' => 'سعر الشراء مطلوب',
            'purchase_price.min' => 'سعر الشراء يجب أن يكون صفر أو أكبر',
            'purchase_price.numeric' => 'سعر الشراء يجب أن يكون رقماً',
            
            'selling_price.required' => 'سعر البيع مطلوب',
            'selling_price.min' => 'سعر البيع يجب أن يكون صفر أو أكبر',
            'selling_price.numeric' => 'سعر البيع يجب أن يكون رقماً',
            
            'min_selling_price.min' => 'الحد الأدنى للبيع يجب أن يكون صفر أو أكبر',
            'min_selling_price.lte' => 'الحد الأدنى للبيع يجب أن يكون أقل من أو يساوي سعر البيع',
            'wholesale_price.min' => 'سعر الجملة يجب أن يكون صفر أو أكبر',
            
            'tax_rate.min' => 'نسبة الضريبة يجب أن تكون بين 0 و 100',
            'tax_rate.max' => 'نسبة الضريبة يجب أن تكون بين 0 و 100',
            
            'default_discount.min' => 'الخصم الافتراضي يجب أن يكون بين 0 و 100',
            'default_discount.max' => 'الخصم الافتراضي يجب أن يكون بين 0 و 100',
            
            // الصورة
            'image.image' => 'الملف يجب أن يكون صورة',
            'image.mimes' => 'صيغة الصورة يجب أن تكون: jpeg, jpg, png, gif, webp',
            'image.max' => 'حجم الصورة يجب أن لا يتجاوز 5 ميجابايت',
            
            // الحالة
            'is_active.required' => 'يجب تحديد حالة النشاط',
            'is_active.boolean' => 'حالة النشاط غير صحيحة',
            
            // وحدات البيع
            'selling_units.array' => 'وحدات البيع يجب أن تكون مصفوفة',
            'selling_units.max' => 'لا يمكن إضافة أكثر من 20 وحدة بيع',
            'selling_units.*.unit_name.required_with' => 'اسم الوحدة مطلوب',
            'selling_units.*.unit_code.required_with' => 'كود الوحدة مطلوب',
            'selling_units.*.conversion_factor.required_with' => 'معامل التحويل مطلوب',
            'selling_units.*.conversion_factor.min' => 'معامل التحويل يجب أن يكون أكبر من صفر',
            
            // المخازن
            'warehouses.array' => 'المخازن يجب أن تكون مصفوفة',
            'warehouses.*.warehouse_id.required_with' => 'المخزن مطلوب',
            'warehouses.*.warehouse_id.exists' => 'المخزن المختار غير موجود',
            'warehouses.*.quantity.min' => 'الكمية يجب أن تكون صفر أو أكبر',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'اسم المنتج',
            'code' => 'الكود',
            'sku' => 'SKU',
            'barcode' => 'الباركود',
            'description' => 'الوصف',
            'category' => 'التصنيف',
            'category_id' => 'التصنيف',
            'brand_id' => 'العلامة التجارية',
            'base_unit' => 'الوحدة الأساسية',
            'base_unit_label' => 'اسم الوحدة',
            'purchase_price' => 'سعر الشراء',
            'selling_price' => 'سعر البيع',
            'min_selling_price' => 'الحد الأدنى للبيع',
            'wholesale_price' => 'سعر الجملة',
            'tax_rate' => 'نسبة الضريبة',
            'default_discount' => 'الخصم الافتراضي',
            'stock_alert_quantity' => 'كمية التنبيه',
            'reorder_level' => 'مستوى إعادة الطلب',
            'reorder_quantity' => 'كمية إعادة الطلب',
            'min_stock' => 'الحد الأدنى للمخزون',
            'max_stock' => 'الحد الأقصى للمخزون',
            'weight' => 'الوزن',
            'dimensions' => 'الأبعاد',
            'image' => 'الصورة',
            'is_active' => 'حالة النشاط',
            'is_featured' => 'مميز',
            'meta_title' => 'عنوان SEO',
            'meta_description' => 'وصف SEO',
            'meta_keywords' => 'كلمات مفتاحية',
        ];
    }

    protected function prepareForValidation(): void
    {
        // تنظيف الكود
        if ($this->has('code') && !empty($this->code)) {
            $this->merge([
                'code' => strtoupper(trim($this->code)),
            ]);
        }

        // تنظيف SKU
        if ($this->has('sku') && !empty($this->sku)) {
            $this->merge([
                'sku' => strtoupper(trim($this->sku)),
            ]);
        }

        // تحويل is_active لـ boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // تحويل is_featured لـ boolean
        if ($this->has('is_featured')) {
            $this->merge([
                'is_featured' => filter_var($this->is_featured, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // إزالة الفواصل من الأسعار
        $priceFields = ['purchase_price', 'selling_price', 'min_selling_price', 'wholesale_price'];
        foreach ($priceFields as $field) {
            if ($this->has($field) && is_string($this->$field)) {
                $this->merge([
                    $field => str_replace(',', '', $this->$field)
                ]);
            }
        }
    }

    /**
     * ✅ Validation إضافي بعد القواعد الأساسية
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            
            // ✅ التحقق من min_selling_price فقط إذا كان موجود
            if ($this->filled('min_selling_price')) {
                $minSellingPrice = (float) $this->min_selling_price;
                $sellingPrice = (float) $this->selling_price;
                
                if ($sellingPrice < $minSellingPrice) {
                    $validator->errors()->add(
                        'selling_price',
                        'سعر البيع يجب أن يكون أكبر من أو يساوي الحد الأدنى للبيع (' . number_format($minSellingPrice, 2) . ' ج.م)'
                    );
                }
            }
            
            // ✅ تحذير: سعر البيع أقل من سعر الشراء (اختياري)
            if ($this->filled('selling_price') && $this->filled('purchase_price')) {
                $sellingPrice = (float) $this->selling_price;
                $purchasePrice = (float) $this->purchase_price;
                
                if ($sellingPrice < $purchasePrice) {
                    // يمكنك جعلها warning أو error حسب الحاجة
                    // $validator->errors()->add('selling_price', '⚠️ تحذير: سعر البيع أقل من سعر الشراء');
                }
            }

            // ✅ التحقق من max_stock أكبر من min_stock
            if ($this->filled('min_stock') && $this->filled('max_stock')) {
                $minStock = (float) $this->min_stock;
                $maxStock = (float) $this->max_stock;
                
                if ($maxStock > 0 && $maxStock < $minStock) {
                    $validator->errors()->add(
                        'max_stock',
                        'الحد الأقصى للمخزون يجب أن يكون أكبر من الحد الأدنى'
                    );
                }
            }

            // ✅ التحقق من وجود وحدة بيع افتراضية واحدة فقط
            if ($this->has('selling_units') && is_array($this->selling_units)) {
                $defaultCount = 0;
                foreach ($this->selling_units as $unit) {
                    if (isset($unit['is_default']) && $unit['is_default']) {
                        $defaultCount++;
                    }
                }
                
                if ($defaultCount > 1) {
                    $validator->errors()->add(
                        'selling_units',
                        'يجب اختيار وحدة بيع افتراضية واحدة فقط'
                    );
                }
            }

            // ✅ التحقق من عدم تكرار المخازن
            if ($this->has('warehouses') && is_array($this->warehouses)) {
                $warehouseIds = array_column($this->warehouses, 'warehouse_id');
                $uniqueIds = array_unique($warehouseIds);
                
                if (count($warehouseIds) !== count($uniqueIds)) {
                    $validator->errors()->add(
                        'warehouses',
                        'لا يمكن إضافة المخزن نفسه أكثر من مرة'
                    );
                }
            }
        });
    }
}