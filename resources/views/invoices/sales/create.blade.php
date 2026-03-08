@extends('layouts.app')

@section('title', 'فاتورة مبيعات جديدة')
@section('page-title', 'فاتورة مبيعات جديدة')

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('invoiceForm', () => ({
        warehouseId: '',
        customerId: '',
        
        // جميع بيانات المنتجات مع المخزون والوحدات والوحدة الأساسية
        productsData: {!! json_encode($products->mapWithKeys(function($p) { 
            return [$p->id => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code ?? '',
                'base_unit' => $p->base_unit ?? 'piece',
                'base_purchase_price' => (float)($p->base_purchase_price ?? 0),
                'base_selling_price' => (float)($p->base_selling_price ?? 0),
                'tax_rate' => (float)($p->tax_rate ?? 0),
                'discount' => (float)($p->default_discount ?? 0),
                // ✅ بيانات الوحدة الأساسية من product_base_units
                'base_unit_type' => $p->baseunit->base_unit_type ?? 'count',
                'base_unit_code' => $p->baseunit->base_unit_code ?? ($p->base_unit ?? 'piece'),
                'base_unit_label' => $p->baseunit->base_unit_label ?? ($p->base_unit_label ?? 'قطعة'),
                'base_unit_weight_kg' => (float)($p->baseunit->base_unit_weight_kg ?? 0),
                'base_unit_purchase_price' => (float)($p->baseunit->base_purchase_price ?? $p->purchase_price ?? 0),
                'base_unit_selling_price' => (float)($p->baseunit->base_selling_price ?? $p->selling_price ?? 0),
                // مخزون المنتج في كل مخزن (بالوحدة الأساسية)
                'stock' => $p->warehouses->mapWithKeys(function($w) {
                    return [$w->id => (float)$w->pivot->quantity];
                })->toArray(),
                // وحدات البيع المتاحة للمنتج
                'selling_units' => $p->activeSellingUnits->map(function($su) {
                    return [
                        'id' => $su->id,
                        'unit_code' => $su->unit_code,
                        'unit_label' => $su->unit_label,
                        'conversion_factor' => (float)($su->conversion_factor ?? $su->quantity_in_base_unit ?? 1),
                        'selling_price' => (float)($su->unit_selling_price ?? 0),
                        'purchase_price' => (float)($su->unit_purchase_price ?? 0),
                        'is_default' => (bool)$su->is_default
                    ];
                })->toArray()
            ]];
        })->toArray()) !!},
        
        items: [
            { 
                product_id: '', 
                product_name: '',
                selling_unit_id: '',
                unit_code: '',
                unit_label: '',
                conversion_factor: 1,
                quantity: 1, 
                weight: '',
                price: 0, 
                base_unit_price: 0,
                base_unit_type: 'count',
                base_unit_code: '',
                base_unit_label: '',
                tax_rate: 0,
                discount: 0,
                total: 0,
                available_stock: 0,
                available_stock_in_unit: 0,
                show_stock_warning: false
            }
        ],
        subtotal: 0,
        totalDiscount: 0,
        totalTax: 0,
        shipping: 0,
        grandTotal: 0,
        paid: 0,
        remaining: 0,
        
        // ✅ متغيرات تحذير تجاوز الدفع
        showOverpaymentWarning: false,
        overpaymentMessage: '',

        // ✅ التحقق هل المنتج يُباع بالوزن
        isWeightBased(productId) {
            if (!productId || !this.productsData[productId]) return false;
            return this.productsData[productId].base_unit_type === 'weight';
        },

        // جلب الكمية المتاحة للمنتج في المخزن المختار (بالوحدة الأساسية)
        getAvailableStock(productId) {
            if (!productId || !this.warehouseId || !this.productsData[productId]) {
                return 0;
            }
            const stock = this.productsData[productId].stock;
            return stock[this.warehouseId] || 0;
        },

        // جلب وحدات البيع المتاحة للمنتج
        getSellingUnits(productId) {
            if (!productId || !this.productsData[productId]) {
                return [];
            }
            return this.productsData[productId].selling_units || [];
        },

        // تحميل بيانات المنتج عند اختياره
        loadProductData(index) {
            const item = this.items[index];
            
            if (!item.product_id || !this.productsData[item.product_id]) {
                this.resetItem(index);
                return;
            }

            const productData = this.productsData[item.product_id];
            item.product_name = productData.name;
            
            // ✅ تحميل بيانات الوحدة الأساسية
            item.base_unit_type = productData.base_unit_type;
            item.base_unit_code = productData.base_unit_code;
            item.base_unit_label = productData.base_unit_label;
            item.base_unit_price = productData.base_unit_selling_price;
            
            // تحديث الكمية المتاحة بالوحدة الأساسية
            item.available_stock = this.getAvailableStock(item.product_id);
            
            // ✅ إعادة تعيين الوزن
            item.weight = '';
            
            // تحديد الوحدة الافتراضية
            const defaultUnit = productData.selling_units.find(u => u.is_default);
            const firstUnit = productData.selling_units[0];
            
            // ✅ حساب الكمية المتاحة بالوحدة المناسبة
            if (productData.base_unit_type === 'weight') {
                // للمنتجات المرجحة، الكمية المتاحة تكون كما هي (بالوحدة الأساسية)
                item.available_stock_in_unit = item.available_stock;
                // ✅ للمنتجات المرجحة، الكمية الافتراضية تكون 1
                item.quantity = 1;
            } else {
                item.available_stock_in_unit = Math.floor(item.available_stock / (firstUnit?.conversion_factor || 1));
            }
            
            if (defaultUnit) {
                this.selectUnit(index, defaultUnit.id);
            } else if (firstUnit) {
                this.selectUnit(index, firstUnit.id);
            } else {
                // إذا لم توجد وحدات بيع، استخدم الوحدة الأساسية
                item.unit_code = productData.base_unit_code;
                item.unit_label = productData.base_unit_label;
                item.conversion_factor = 1;
                item.price = productData.base_unit_selling_price;
                item.tax_rate = productData.tax_rate;
                item.discount = productData.discount;
                item.available_stock_in_unit = item.available_stock;
            }
            
            this.checkStockAvailability(index);
            this.calculateItemTotal(index);
        },

        // تحديد وحدة البيع
        selectUnit(index, sellingUnitId) {
            const item = this.items[index];
            
            if (!item.product_id || !sellingUnitId) {
                return;
            }

            const productData = this.productsData[item.product_id];
            const sellingUnit = productData.selling_units.find(u => u.id === parseInt(sellingUnitId));
            
            if (!sellingUnit) {
                return;
            }

            item.selling_unit_id = sellingUnit.id;
            item.unit_code = sellingUnit.unit_code;
            item.unit_label = sellingUnit.unit_label;
            item.conversion_factor = sellingUnit.conversion_factor;
            item.price = sellingUnit.selling_price;
            item.tax_rate = productData.tax_rate;
            item.discount = productData.discount;
            
            // حساب الكمية المتاحة بالوحدة المختارة
            // ✅ بالنسبة للمنتجات المرجحة (weight)، الكمية المتاحة تكون كما هي بدون قسمة
            if (item.base_unit_type === 'weight') {
                item.available_stock_in_unit = item.available_stock;
                // ✅ للمنتجات المرجحة، الكمية تكون 1
                item.quantity = 1;
            } else {
                item.available_stock_in_unit = Math.floor(item.available_stock / item.conversion_factor);
            }
            
            this.checkStockAvailability(index);
            this.calculateItemTotal(index);
        },

        // ✅ حساب السعر تلقائياً عند إدخال الكمية
        autoCalculatePrice(index) {
            const item = this.items[index];
            if (!item.product_id) return;
            
            const productData = this.productsData[item.product_id];
            const qty = parseFloat(item.quantity) || 0;
            
            // ✅ إذا كان المنتج يُباع بالوزن، نحسب السعر = الكمية × سعر الوحدة الأساسية
            if (item.base_unit_type === 'weight') {
                const weight = parseFloat(item.weight) || 0;
                if (weight > 0) {
                    // السعر = الوزن × سعر الوحدة الأساسية (مثلاً: 50 كجم × 30 ج.م/كجم = 1500 ج.م)
                    item.price = Math.round(weight * item.base_unit_price * 100) / 100;
                }
            } else {
                // ✅ للمنتجات العادية (بالقطعة/الكمية): السعر = سعر الوحدة المختارة
                // السعر يبقى كما هو (سعر الوحدة المختارة)
                // لا نغير السعر هنا لأنه يتحدد من الوحدة المختارة
            }
            
            this.checkStockAvailability(index);
            this.calculateItemTotal(index);
        },

        // ✅ حساب السعر عند تغيير الوزن
        onWeightChange(index) {
            const item = this.items[index];
            if (!item.product_id) return;
            
            const weight = parseFloat(item.weight) || 0;
            
            if (weight > 0 && item.base_unit_price > 0) {
                // ✅ السعر = الوزن × سعر الوحدة الأساسية
                // مثال: 5 طن × 10000 ج.م/طن = 50000 ج.م
                item.price = Math.round(weight * item.base_unit_price * 100) / 100;
                
                // ✅ Untuk produk tertimbang, pastikan quantity = 1
                if (item.base_unit_type === 'weight') {
                    item.quantity = 1;
                }
            }
            
            this.checkStockAvailability(index);
            this.calculateItemTotal(index);
        },

        // التحقق من توفر الكمية
        checkStockAvailability(index) {
            const item = this.items[index];
            if (!item.product_id || !this.warehouseId) {
                item.show_stock_warning = false;
                return;
            }

            // ✅ بالنسبة للمنتجات المرجحة (weight)، نتحقق من الوزن المدخل وليس الكمية
            if (item.base_unit_type === 'weight') {
                const weight = parseFloat(item.weight) || 0;
                item.show_stock_warning = weight > item.available_stock;
            } else {
                // للمنتجات العادية، نتحقق من الكمية
                const requiredInBaseUnit = item.quantity * item.conversion_factor;
                item.show_stock_warning = requiredInBaseUnit > item.available_stock;
            }
        },

        // تحديث كل الأصناف عند تغيير المخزن
        updateAllItemsStock() {
            this.items.forEach((item, index) => {
                if (item.product_id) {
                    item.available_stock = this.getAvailableStock(item.product_id);
                    // ✅ بالنسبة للمنتجات المرجحة (weight)، الكمية المتاحة تكون كما هي
                    if (item.base_unit_type === 'weight') {
                        item.available_stock_in_unit = item.available_stock;
                    } else if (item.conversion_factor) {
                        item.available_stock_in_unit = Math.floor(item.available_stock / item.conversion_factor);
                    }
                    this.checkStockAvailability(index);
                }
            });
        },

        // إعادة تعيين الصنف
        resetItem(index) {
            const item = this.items[index];
            item.product_name = '';
            item.selling_unit_id = '';
            item.unit_code = '';
            item.unit_label = '';
            item.conversion_factor = 1;
            item.price = 0;
            item.base_unit_price = 0;
            item.base_unit_type = 'count';
            item.base_unit_code = '';
            item.base_unit_label = '';
            item.weight = '';
            item.tax_rate = 0;
            item.discount = 0;
            item.available_stock = 0;
            item.available_stock_in_unit = 0;
            item.show_stock_warning = false;
        },

        addItem() {
            this.items.push({ 
                product_id: '', 
                product_name: '',
                selling_unit_id: '',
                unit_code: '',
                unit_label: '',
                conversion_factor: 1,
                quantity: 1, 
                weight: '',
                price: 0, 
                base_unit_price: 0,
                base_unit_type: 'count',
                base_unit_code: '',
                base_unit_label: '',
                tax_rate: 0,
                discount: 0,
                total: 0,
                available_stock: 0,
                available_stock_in_unit: 0,
                show_stock_warning: false
            });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.calculateTotals();
            }
        },

        calculateItemTotal(index) {
            const item = this.items[index];
            
            // ✅ التأكد من أن القيم الرقمية صحيحة
            const qty = parseFloat(item.quantity) || 0;
            const priceValue = parseFloat(item.price) || 0;
            
            // الإجمالي = الكمية × السعر
            const subtotal = qty * priceValue;
            const discount = subtotal * ((parseFloat(item.discount) || 0) / 100);
            const afterDiscount = subtotal - discount;
            const tax = afterDiscount * ((parseFloat(item.tax_rate) || 0) / 100);
            item.total = Math.round((afterDiscount + tax) * 100) / 100;
            
            this.checkStockAvailability(index);
            this.calculateTotals();
        },

        calculateTotals() {
            // ✅ التأكد من أن القيم الرقمية صحيحة
            this.subtotal = Math.round(this.items.reduce((s, i) => {
                const qty = parseFloat(i.quantity) || 0;
                const priceValue = parseFloat(i.price) || 0;
                return s + (qty * priceValue);
            }, 0) * 100) / 100;
            
            this.totalDiscount = Math.round(this.items.reduce((s, i) => {
                const qty = parseFloat(i.quantity) || 0;
                const priceValue = parseFloat(i.price) || 0;
                const discount = parseFloat(i.discount) || 0;
                return s + ((qty * priceValue) * (discount / 100));
            }, 0) * 100) / 100;
            
            this.totalTax = Math.round(this.items.reduce((s, i) => {
                const qty = parseFloat(i.quantity) || 0;
                const priceValue = parseFloat(i.price) || 0;
                const discount = parseFloat(i.discount) || 0;
                const taxRate = parseFloat(i.tax_rate) || 0;
                const afterDiscount = (qty * priceValue) - ((qty * priceValue) * (discount / 100));
                return s + (afterDiscount * (taxRate / 100));
            }, 0) * 100) / 100;
            
            this.grandTotal = Math.round((this.subtotal - this.totalDiscount + this.totalTax + Number(this.shipping || 0)) * 100) / 100;
            this.remaining = Math.round((this.grandTotal - (Number(this.paid) || 0)) * 100) / 100;
        },

        updatePaid() {
            this.remaining = Math.round((this.grandTotal - (Number(this.paid) || 0)) * 100) / 100;
            
            // ✅ إظهار تحذير عند تجاوز المبلغ المدفوع للمطلوب
            if (this.remaining < 0) {
                const excessAmount = Math.abs(this.remaining);
                this.showOverpaymentWarning = true;
                this.overpaymentMessage = '⚠️ تحذير: المبلغ المدفوع أكبر من الإجمالي المطلوب بمبلغ ' + excessAmount.toFixed(2) + ' ج.م';
            } else {
                this.showOverpaymentWarning = false;
                this.overpaymentMessage = '';
            }
        },

        // التحقق قبل الحفظ
        validateForm() {
            if (!this.warehouseId) {
                alert('⚠️ يجب اختيار المخزن أولاً');
                return false;
            }

            if (!this.customerId) {
                alert('⚠️ يجب اختيار العميل');
                return false;
            }

            // التحقق من وجود أصناف
            if (this.items.length === 0 || !this.items[0].product_id) {
                alert('⚠️ يجب إضافة صنف واحد على الأقل');
                return false;
            }

            // ✅ التحقق من إدخال الوزن للمنتجات التي تُباع بالوزن
            const missingWeight = this.items.filter(item => {
                if (!item.product_id) return false;
                return item.base_unit_type === 'weight' && (!item.weight || parseFloat(item.weight) <= 0);
            });

            if (missingWeight.length > 0) {
                const itemsList = missingWeight.map(item => `- ${item.product_name}`).join('\n');
                alert(`⚠️ يجب إدخال الوزن للأصناف التالية:\n\n${itemsList}`);
                return false;
            }

            // التحقق من وجود أصناف تتجاوز المخزون المتاح
            const outOfStockItems = this.items.filter(item => {
                if (!item.product_id) return false;
                
                // ✅ بالنسبة للمنتجات المرجحة (weight)، نتحقق من الوزن المدخل
                if (item.base_unit_type === 'weight') {
                    const requiredWeight = parseFloat(item.weight) || 0;
                    return requiredWeight > item.available_stock;
                } else {
                    const requiredInBaseUnit = item.quantity * item.conversion_factor;
                    return requiredInBaseUnit > item.available_stock;
                }
            });

            if (outOfStockItems.length > 0) {
                const itemsList = outOfStockItems.map(item => {
                    // ✅ للوزن نعرض الوزن المطلوب والمتاح
                    if (item.base_unit_type === 'weight') {
                        const requiredWeight = parseFloat(item.weight) || 0;
                        return `- ${item.product_name} (${item.base_unit_label}): مطلوب ${requiredWeight} ${item.base_unit_label}، متوفر ${item.available_stock} ${item.base_unit_label}`;
                    } else {
                        const requiredInBaseUnit = item.quantity * item.conversion_factor;
                        return `- ${item.product_name} (${item.unit_label}): مطلوب ${item.quantity}، متوفر ${item.available_stock_in_unit}`;
                    }
                }).join('\n');
                
                alert(`⚠️ الأصناف التالية تتجاوز الكمية المتاحة:\n\n${itemsList}\n\nيرجى تعديل الكميات قبل الحفظ.`);
                return false;
            }

            return true;
        },

        // دالة مساعدة لجلب اسم الوحدة
        getUnitLabel(unitCode) {
            const unitLabels = {
                'piece': 'قطعة',
                'kg': 'كيلو جرام',
                'ton': 'طن',
                'gram': 'جرام',
                'quintal': 'قنطار',
                'liter': 'لتر',
                'milliliter': 'مللتر',
                'gallon': 'جالون',
                'meter': 'متر',
                'cm': 'سنتيمتر',
                'box': 'صندوق',
                'carton': 'كرتونة',
                'bag': 'شيكارة',
                'sack': 'جوال',
                'roll': 'لفة',
                'pack': 'عبوة',
                'dozen': 'دستة',
                'bundle': 'ربطة',
                'bottle': 'زجاجة',
                'can': 'علبة',
                'unit': 'وحدة'
            };
            return unitLabels[unitCode] || unitCode;
        },

        // ✅ أيقونة نوع الوحدة
        getUnitTypeIcon(unitType) {
            const icons = {
                'weight': '⚖️',
                'volume': '🧪',
                'length': '📏',
                'count': '🔢',
                'packaging': '📦'
            };
            return icons[unitType] || '📦';
        }
    }));
});
</script>
@endpush

@section('content')
<div x-data="invoiceForm" class="max-w-7xl mx-auto">
    <form method="POST" action="{{ route('invoices.sales.store') }}" 
          @submit="if (!validateForm()) { $event.preventDefault(); }">
        @csrf

        <!-- Header Info -->
        <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
            <div class="grid md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">رقم الفاتورة</label>
                    <input type="text" readonly class="w-full bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-300 rounded-lg px-4 py-3 font-bold text-blue-600"
                           value="SALE-{{ now()->format('YmdHis') }}">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">التاريخ *</label>
                    <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}"
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            المخزن *
                        </span>
                    </label>
                    <select name="warehouse_id" x-model="warehouseId" 
                            @change="updateAllItemsStock()"
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-semibold" required>
                        <option value="">اختر المخزن</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <p class="text-red-600 text-sm mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            العميل *
                        </span>
                    </label>
                    <select name="customer_id" x-model="customerId" 
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all font-semibold" required>
                        <option value="">اختر العميل</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <p class="text-red-600 text-sm mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b-2 border-blue-200 p-6">
                <div class="flex justify-between items-center flex-wrap gap-4">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        الأصناف
                    </h3>
                    <button type="button" @click="addItem()" 
                            class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        إضافة صنف
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gradient-to-r from-gray-100 to-gray-200 border-b-2 border-gray-300">
                        <tr>
                            <th class="px-3 py-4 text-right font-bold text-gray-700">#</th>
                            <th class="px-3 py-4 text-right font-bold text-gray-700">الصنف</th>
                            <th class="px-3 py-4 text-right font-bold text-gray-700">الوحدة</th>
                            <th class="px-3 py-4 text-center font-bold text-gray-700 bg-amber-50">
                                <span class="flex items-center justify-center gap-1">⚖️ الوزن</span>
                            </th>
                            <th class="px-3 py-4 text-center font-bold text-gray-700 bg-green-50">المتاح</th>
                            <th class="px-3 py-4 text-center font-bold text-gray-700">الكمية</th>
                            <th class="px-3 py-4 text-center font-bold text-gray-700">
                                <span class="flex items-center justify-center gap-1">💰 السعر</span>
                            </th>
                            <th class="px-3 py-4 text-center font-bold text-gray-700">خصم %</th>
                            <th class="px-3 py-4 text-center font-bold text-gray-700">ضريبة %</th>
                            <th class="px-3 py-4 text-center font-bold text-gray-700 bg-blue-50">الإجمالي</th>
                            <th class="px-3 py-4 text-center font-bold text-gray-700">حذف</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-blue-50 transition-colors" 
                                :class="{ 'bg-red-50 border-l-4 border-red-500': item.show_stock_warning }">
                                
                                <td class="px-3 py-3 font-bold text-gray-600" x-text="index + 1"></td>
                                
                                <!-- اختيار المنتج -->
                                <td class="px-3 py-3">
                                    <select :name="'items[' + index + '][product_id]'" 
                                            x-model="item.product_id"
                                            @change="loadProductData(index)"
                                            class="w-full border-2 rounded-lg px-3 py-2 text-sm font-semibold focus:ring-2 focus:ring-blue-500 transition-all"
                                            :class="{ 'border-red-500 bg-red-50': !warehouseId && item.product_id }">
                                        <option value="">اختر الصنف</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    <!-- ✅ عرض نوع الوحدة الأساسية -->
                                    <div x-show="item.product_id && item.base_unit_label" class="mt-1 flex items-center gap-1">
                                        <span class="text-xs" x-text="getUnitTypeIcon(item.base_unit_type)"></span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold"
                                              :class="{
                                                  'bg-amber-100 text-amber-800': item.base_unit_type === 'weight',
                                                  'bg-blue-100 text-blue-800': item.base_unit_type === 'volume',
                                                  'bg-green-100 text-green-800': item.base_unit_type === 'count',
                                                  'bg-purple-100 text-purple-800': item.base_unit_type === 'length',
                                                  'bg-gray-100 text-gray-800': !['weight','volume','count','length'].includes(item.base_unit_type)
                                              }"
                                              x-text="item.base_unit_label">
                                        </span>
                                        <span x-show="item.base_unit_price > 0" 
                                              class="text-xs text-gray-500"
                                              x-text="'(' + item.base_unit_price.toFixed(2) + ' ج.م/' + item.base_unit_label + ')'">
                                        </span>
                                    </div>
                                    <p x-show="!warehouseId && item.product_id" 
                                       class="text-red-600 text-xs mt-1 font-semibold">
                                        ⚠️ اختر المخزن أولاً
                                    </p>
                                </td>

                                <!-- اختيار الوحدة -->
                                <td class="px-3 py-3">
                                    <select x-show="item.product_id && getSellingUnits(item.product_id).length > 0"
                                            :name="'items[' + index + '][selling_unit_id]'"
                                            x-model="item.selling_unit_id"
                                            @change="selectUnit(index, item.selling_unit_id)"
                                            class="w-full border-2 border-purple-300 rounded-lg px-3 py-2 text-sm font-semibold bg-purple-50 focus:ring-2 focus:ring-purple-500 transition-all">
                                        <template x-for="unit in getSellingUnits(item.product_id)" :key="unit.id">
                                            <option :value="unit.id" x-text="unit.unit_label + ' (' + unit.conversion_factor + 'x)'"></option>
                                        </template>
                                    </select>
                                    <span x-show="item.product_id && item.unit_label" 
                                          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800"
                                          x-text="item.unit_label"></span>
                                </td>

                                <!-- ✅ الوزن (يظهر فقط للمنتجات التي تُباع بالوزن) -->
                                <td class="px-3 py-3 bg-amber-50/50">
                                    <div x-show="item.product_id && item.base_unit_type === 'weight'">
                                        <div class="flex items-center gap-1">
                                            <input type="number" 
                                                   :name="'items[' + index + '][weight]'"
                                                   x-model="item.weight" 
                                                   @input="onWeightChange(index)"
                                                   class="w-24 border-2 border-amber-300 rounded-lg px-2 py-2 text-sm font-bold text-center bg-amber-50 focus:ring-2 focus:ring-amber-500 transition-all" 
                                                   min="0.001" step="0.001" 
                                                   :placeholder="item.base_unit_label">
                                            <span class="text-xs font-bold text-amber-700" x-text="item.base_unit_label"></span>
                                        </div>
                                        <div x-show="item.weight > 0 && item.base_unit_price > 0" class="mt-1">
                                            <span class="text-xs text-amber-600 font-semibold"
                                                  x-text="'= ' + (parseFloat(item.weight) * item.base_unit_price).toFixed(2) + ' ج.م'">
                                            </span>
                                        </div>
                                    </div>
                                    <div x-show="item.product_id && item.base_unit_type !== 'weight'" class="text-center">
                                        <span class="text-xs text-gray-400">—</span>
                                    </div>
                                    <div x-show="!item.product_id" class="text-center">
                                        <span class="text-xs text-gray-300">—</span>
                                    </div>
                                </td>

                                <!-- الكمية المتاحة -->
                                <td class="px-3 py-3 text-center bg-green-50">
                                    <div class="flex flex-col items-center gap-1">
                                        <span x-show="item.product_id && warehouseId"
                                              x-text="item.available_stock_in_unit"
                                              class="font-bold text-lg"
                                              :class="{
                                                  'text-green-600': item.available_stock_in_unit >= item.quantity,
                                                  'text-orange-600': item.available_stock_in_unit < item.quantity && item.available_stock_in_unit > 0,
                                                  'text-red-600': item.available_stock_in_unit < item.quantity
                                              }">
                                        </span>
                                        <span x-show="item.unit_label" 
                                              class="text-xs text-gray-600"
                                              x-text="item.unit_label"></span>
                                        <svg x-show="item.show_stock_warning" 
                                             class="w-5 h-5 text-red-600 animate-pulse" 
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </td>

                                <!-- الكمية -->
                                <td class="px-3 py-3">
                                    <input type="number" 
                                           :name="'items[' + index + '][quantity]'"
                                           x-model="item.quantity" 
                                           @input="autoCalculatePrice(index)"
                                           class="w-24 border-2 rounded-lg px-3 py-2 text-sm font-bold text-center focus:ring-2 focus:ring-blue-500 transition-all" 
                                           :class="{ 'border-red-500 bg-red-50': item.show_stock_warning }"
                                           min="0.001" step="0.001" required>
                                </td>

                                <!-- السعر -->
                                <td class="px-3 py-3">
                                    <input type="number" 
                                           :name="'items[' + index + '][price]'"
                                           x-model="item.price" 
                                           @input="calculateItemTotal(index)"
                                           class="w-28 border-2 border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-center focus:ring-2 focus:ring-green-500 transition-all" 
                                           :class="{ 'bg-amber-50 border-amber-300': item.base_unit_type === 'weight' }"
                                           min="0" step="0.01" required>
                                    <!-- ✅ عرض سعر الوحدة الأساسية -->
                                    <div x-show="item.product_id && item.base_unit_price > 0" class="mt-1 text-center">
                                        <span class="text-xs text-gray-500"
                                              x-text="item.base_unit_price.toFixed(2) + ' ج.م/' + item.base_unit_label">
                                        </span>
                                    </div>
                                </td>

                                <!-- الخصم -->
                                <td class="px-3 py-3">
                                    <input type="number" 
                                           :name="'items[' + index + '][discount]'"
                                           x-model="item.discount" 
                                           @input="calculateItemTotal(index)"
                                           class="w-20 border-2 border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-center focus:ring-2 focus:ring-orange-500 transition-all" 
                                           min="0" max="100" placeholder="%">
                                </td>

                                <!-- الضريبة -->
                                <td class="px-3 py-3">
                                    <input type="number" 
                                           :name="'items[' + index + '][tax_rate]'"
                                           x-model="item.tax_rate" 
                                           @input="calculateItemTotal(index)"
                                           class="w-20 border-2 border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-center focus:ring-2 focus:ring-blue-500 transition-all" 
                                           min="0" max="100" placeholder="%">
                                </td>

                                <!-- الإجمالي -->
                                <td class="px-3 py-3 text-center bg-blue-50">
                                    <span class="font-bold text-lg text-blue-700" x-text="item.total.toFixed(2) + ' ج.م'"></span>
                                </td>

                                <!-- حذف -->
                                <td class="px-3 py-3 text-center">
                                    <button type="button" 
                                            @click="removeItem(index)"
                                            class="text-red-600 hover:text-red-800 hover:bg-red-100 rounded-lg p-2 transition-all font-bold text-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </td>

                                <!-- Hidden inputs للوحدة والوزن -->
                                <input type="hidden" :name="'items[' + index + '][conversion_factor]'" x-model="item.conversion_factor">
                                <input type="hidden" :name="'items[' + index + '][unit_code]'" x-model="item.unit_code">
                                <input type="hidden" :name="'items[' + index + '][base_unit_type]'" x-model="item.base_unit_type">
                                <input type="hidden" :name="'items[' + index + '][base_unit_code]'" x-model="item.base_unit_code">
                                <input type="hidden" :name="'items[' + index + '][base_unit_label]'" x-model="item.base_unit_label">
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- تحذير عام -->
            <div x-show="!warehouseId" 
                 class="m-4 bg-gradient-to-r from-yellow-50 to-orange-50 border-l-4 border-yellow-500 rounded-lg p-5 flex items-start gap-4 shadow-md">
                <svg class="w-7 h-7 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-bold text-yellow-900 text-lg">⚠️ يجب اختيار المخزن أولاً</p>
                    <p class="text-sm text-yellow-800 mt-2">لن تتمكن من رؤية الكميات المتاحة والوحدات للأصناف حتى تختار المخزن</p>
                </div>
            </div>
        </div>

        <!-- Totals -->
        <div class="grid md:grid-cols-3 gap-6 mb-6">
            <!-- إجمالي الحساب -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 p-6 rounded-2xl shadow-lg">
                <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    الحسابات
                </h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-2 border-b border-green-200">
                        <span class="text-sm text-gray-700">الإجمالي الجزئي:</span>
                        <span x-text="subtotal.toFixed(2) + ' ج.م'" class="font-bold text-gray-800"></span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-green-200">
                        <span class="text-sm text-red-600">الخصم:</span>
                        <span x-text="'-' + totalDiscount.toFixed(2) + ' ج.م'" class="font-bold text-red-600"></span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-green-200">
                        <span class="text-sm text-blue-600">الضريبة:</span>
                        <span x-text="'+' + totalTax.toFixed(2) + ' ج.م'" class="font-bold text-blue-600"></span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-lg font-bold text-green-700">الإجمالي النهائي:</span>
                        <span x-text="grandTotal.toFixed(2) + ' ج.م'" class="text-2xl font-bold text-green-700"></span>
                    </div>
                </div>
            </div>

            <!-- الدفع -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 p-6 rounded-2xl shadow-lg">
                <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    الدفع
                </h4>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">المبلغ المدفوع</label>
                        <input type="number" name="paid" x-model="paid" @input="updatePaid()"
                               class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 font-bold text-lg focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all" 
                               min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">المتبقي</label>
                        <div class="text-3xl font-bold px-4 py-3 rounded-lg" 
                             :class="remaining > 0 ? 'text-orange-600 bg-orange-50' : 'text-green-600 bg-green-50'"
                             x-text="remaining.toFixed(2) + ' ج.م'"></div>
                    </div>
                    
                    <!-- ✅ تحذير تجاوز الدفع -->
                    <div x-show="showOverpaymentWarning" 
                         x-transition.duration.300ms
                         class="mt-3 p-3 bg-red-100 border-2 border-red-400 rounded-lg">
                        <p class="text-red-700 font-semibold text-sm" x-text="overpaymentMessage"></p>
                    </div>
                </div>
            </div>

            <!-- الملاحظات -->
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 border-2 border-purple-200 p-6 rounded-2xl shadow-lg">
                <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    ملاحظات
                </h4>
                <textarea name="notes" 
                          class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 h-32 text-sm focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all resize-none" 
                          placeholder="أضف ملاحظات إضافية هنا..."></textarea>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex gap-4 items-center justify-end bg-white rounded-xl shadow-lg p-6">
            <a href="{{ route('invoices.sales.index') }}" 
               class="px-8 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all font-bold flex items-center gap-2 border-2 border-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                إلغاء
            </a>
            <button type="submit" 
                    class="px-10 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition-all flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                💾 حفظ الفاتورة
            </button>
        </div>
    </form>
</div>
@endsection
