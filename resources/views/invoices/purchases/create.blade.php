@extends('layouts.app')

@section('title', 'فاتورة شراء جديدة')
@section('page-title', 'فاتورة شراء جديدة')

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('invoiceForm', () => ({
        warehouseId: '',
        supplierId: '',
        
        // متغير لإظهار/إخفاء نموذج إضافة منتج جديد
        showNewProductForm: false,
        
        // بيانات المنتج الجديد
        newProduct: {
            name: '',
            base_unit: 'piece',
            base_unit_label: 'قطعة',
            base_unit_type: 'count',
            purchase_price: 0,
            selling_price: 0
        },

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
                // ✅ بيانات الوحدة الأساسية
                'base_unit_type' => $p->baseunit->base_unit_type ?? 'count',
                'base_unit_code' => $p->baseunit->base_unit_code ?? ($p->base_unit ?? 'piece'),
                'base_unit_label' => $p->baseunit->base_unit_label ?? ($p->base_unit_label ?? 'قطعة'),
                'base_unit_weight_kg' => (float)($p->baseunit->base_unit_weight_kg ?? 0),
                'base_unit_purchase_price' => (float)($p->baseunit->base_purchase_price ?? $p->purchase_price ?? 0),
                'base_unit_selling_price' => (float)($p->baseunit->base_selling_price ?? $p->selling_price ?? 0),
                // مخزون المنتج في كل مخزن
                'stock' => $p->warehouses->mapWithKeys(function($w) {
                    return [$w->id => (float)$w->pivot->quantity];
                })->toArray(),
                // وحدات البيع المتاحة
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
                total: 0,
                available_stock: 0,
                available_stock_in_unit: 0
            }
        ],
        subtotal: 0,
        discount: 0,
        tax: 0,
        grandTotal: 0,

        // ✅ هل المنتج يُباع بالوزن
        isWeightBased(productId) {
            if (!productId || !this.productsData[productId]) return false;
            return this.productsData[productId].base_unit_type === 'weight';
        },

        // جلب الكمية المتاحة
        getAvailableStock(productId) {
            if (!productId || !this.warehouseId || !this.productsData[productId]) {
                return 0;
            }
            const stock = this.productsData[productId].stock;
            return stock[this.warehouseId] || 0;
        },

        // جلب وحدات البيع المتاحة
        getSellingUnits(productId) {
            if (!productId || !this.productsData[productId]) {
                return [];
            }
            return this.productsData[productId].selling_units || [];
        },

        // تحميل بيانات المنتج
        loadProductData(index) {
            const item = this.items[index];
            
            if (!item.product_id || !this.productsData[item.product_id]) {
                this.resetItem(index);
                return;
            }

            const productData = this.productsData[item.product_id];
            item.product_name = productData.name;
            
            item.base_unit_type = productData.base_unit_type;
            item.base_unit_code = productData.base_unit_code;
            item.base_unit_label = productData.base_unit_label;
            item.base_unit_price = productData.base_unit_purchase_price;
            
            item.available_stock = this.getAvailableStock(item.product_id);
            item.weight = '';
            
            // تحديد الوحدة الافتراضية
            const defaultUnit = productData.selling_units.find(u => u.is_default);
            const firstUnit = productData.selling_units[0];
            
            // حساب الكمية المتاحة
            if (productData.base_unit_type === 'weight') {
                item.available_stock_in_unit = item.available_stock;
                item.quantity = 1;
            } else {
                item.available_stock_in_unit = Math.floor(item.available_stock / (firstUnit?.conversion_factor || 1));
            }
            
            if (defaultUnit) {
                this.selectUnit(index, defaultUnit.id);
            } else if (firstUnit) {
                this.selectUnit(index, firstUnit.id);
            } else {
                item.unit_code = productData.base_unit_code;
                item.unit_label = productData.base_unit_label;
                item.conversion_factor = 1;
                item.price = productData.base_unit_purchase_price;
                item.tax_rate = productData.tax_rate;
            }
            
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
            item.price = sellingUnit.purchase_price;
            item.tax_rate = productData.tax_rate;
            
            // حساب الكمية المتاحة
            if (item.base_unit_type === 'weight') {
                item.available_stock_in_unit = item.available_stock;
                item.quantity = 1;
            } else {
                item.available_stock_in_unit = Math.floor(item.available_stock / item.conversion_factor);
            }
            
            this.calculateItemTotal(index);
        },

        // حساب السعر عند تغيير الوزن
        onWeightChange(index) {
            const item = this.items[index];
            if (!item.product_id) return;
            
            const weight = parseFloat(item.weight) || 0;
            
            if (weight > 0 && item.base_unit_price > 0) {
                // السعر = الوزن × سعر الوحدة الأساسية
                item.price = Math.round(weight * item.base_unit_price * 100) / 100;
                // للوزن، الكمية تظل 1
                if (item.base_unit_type === 'weight') {
                    item.quantity = 1;
                }
            }
            
            this.calculateItemTotal(index);
        },

        // حساب الإجمالي
        calculateItemTotal(index) {
            const item = this.items[index];
            const qty = parseFloat(item.quantity) || 0;
            const priceVal = parseFloat(item.price) || 0;
            
            item.total = Math.round(qty * priceVal * 100) / 100;
            this.calculateTotals();
        },

        calculateTotals() {
            this.subtotal = Math.round(this.items.reduce((s, i) => {
                const qty = parseFloat(i.quantity) || 0;
                const priceVal = parseFloat(i.price) || 0;
                return s + (qty * priceVal);
            }, 0) * 100) / 100;
            
            this.grandTotal = Math.round((this.subtotal - this.discount + this.tax) * 100) / 100;
        },

        // إضافة صنف
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
                total: 0,
                available_stock: 0,
                available_stock_in_unit: 0
            });
        },

        // حذف صنف
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.calculateTotals();
            }
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
            item.total = 0;
            item.available_stock = 0;
            item.available_stock_in_unit = 0;
        },

        // التحقق قبل الحفظ
        validateForm() {
            if (!this.warehouseId) {
                alert('⚠️ يجب اختيار المخزن أولاً');
                return false;
            }

            if (!this.supplierId) {
                alert('⚠️ يجب اختيار المورد');
                return false;
            }

            if (this.items.length === 0 || !this.items[0].product_id) {
                alert('⚠️ يجب إضافة صنف واحد على الأقل');
                return false;
            }

            return true;
        },

        // أيقونة نوع الوحدة
        getUnitTypeIcon(unitType) {
            const icons = {
                'weight': '⚖️',
                'volume': '🧪',
                'length': '📏',
                'count': '🔢',
                'packaging': '📦'
            };
            return icons[unitType] || '📦';
        },
        
        // تحديث label الوحدة الأساسية
        updateNewProductUnitLabel() {
            const unitLabels = {
                'piece': 'قطعة',
                'kg': 'كيلو',
                'ton': 'طن',
                'gram': 'جرام',
                'meter': 'متر',
                'cm': 'سم',
                'liter': 'لتر',
                'ml': 'ملليلتر',
                'box': 'صندوق',
                'pack': 'حزمة'
            };
            this.newProduct.base_unit_label = unitLabels[this.newProduct.base_unit] || 'قطعة';
        },
        
        // حفظ منتج جديد عبر AJAX
        async saveNewProduct() {
            if (!this.newProduct.name) {
                alert('يرجى إدخال اسم المنتج');
                return;
            }
            
            try {
                const response = await fetch('/products/quick-store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.newProduct)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // إضافة المنتج الجديد لبيانات المنتجات
                    this.productsData[data.product.id] = data.product;
                    
                    // إضافة المنتج الجديد لقائمة الاختيار
                    const select = document.querySelector('select[name^="items"][name*="product_id"]');
                    if (select) {
                        const option = document.createElement('option');
                        option.value = data.product.id;
                        option.text = data.product.name;
                        select.add(option);
                    }
                    
                    // إغلاق النموذج
                    this.showNewProductForm = false;
                    this.newProduct = {
                        name: '',
                        base_unit: 'piece',
                        base_unit_label: 'قطعة',
                        base_unit_type: 'count',
                        purchase_price: 0,
                        selling_price: 0
                    };
                    
                    // إضافة صنف جديد بالمنتج
                    this.addItem();
                    const lastIndex = this.items.length - 1;
                    this.items[lastIndex].product_id = data.product.id;
                    this.items[lastIndex].product_name = data.product.name;
                    this.items[lastIndex].base_unit_type = data.product.base_unit_type;
                    this.items[lastIndex].base_unit_code = data.product.base_unit_code;
                    this.items[lastIndex].base_unit_label = data.product.base_unit_label;
                    this.items[lastIndex].base_unit_price = data.product.base_unit_purchase_price;
                    this.items[lastIndex].price = data.product.base_unit_purchase_price;
                    this.items[lastIndex].available_stock = 0;
                    this.items[lastIndex].available_stock_in_unit = 0;
                    this.items[lastIndex].quantity = 1;
                    this.calculateItemTotal(lastIndex);
                    
                    alert('✅ تم إضافة المنتج بنجاح!');
                } else {
                    alert('❌ خطأ: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('❌ حدث خطأ أثناء حفظ المنتج');
            }
        }
    }));
});
</script>
@endpush

@section('content')
<div x-data="invoiceForm" class="max-w-7xl mx-auto">
    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-xl">
            <p class="font-bold text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-xl">
            <h4 class="font-bold text-red-800 mb-2">يرجى تصحيح الأخطاء التالية:</h4>
            <ul class="list-disc list-inside text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('invoices.purchases.store') }}" 
          @submit="if (!validateForm()) { $event.preventDefault(); }">
        @csrf

        <!-- Header Info -->
        <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
            <div class="grid md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">رقم الفاتورة</label>
                    <input type="text" readonly class="w-full bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-300 rounded-lg px-4 py-3 font-bold text-blue-600"
                           value="PUR-{{ now()->format('YmdHis') }}">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">التاريخ *</label>
                    <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}"
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">المخزن *</label>
                    <select name="warehouse_id" x-model="warehouseId" 
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-semibold" required>
                        <option value="">اختر المخزن</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">المورد *</label>
                    <select name="supplier_id" x-model="supplierId" 
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all font-semibold" required>
                        <option value="">اختر المورد</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-b-2 border-green-200 p-6">
                <div class="flex justify-between items-center flex-wrap gap-4">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        الأصناف
                    </h3>
                    <div class="flex gap-2">
                        <button type="button" @click="showNewProductForm = true" 
                                class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white px-4 py-2 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            + منتج جديد
                        </button>
                        <button type="button" @click="addItem()" 
                                class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            إضافة صنف
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gradient-to-r from-gray-100 to-gray-200 border-b-2 border-gray-300">
                        <tr>
                            <th class="px-3 py-4 text-right font-bold text-gray-700">#</th>
                            <th class="px-3 py-4 text-right font-bold text-gray-700">الصنف</th>
                            <th class="px-3 py-4 text-right font-bold text-gray-700">الوحدة</th>
                            <th class="px-3 py-4 text-center font-bold text-gray-700 bg-amber-50">الوزن</th>
                            <th class="px-3 py-4 text-center font-bold text-gray-700 bg-green-50">المتاح</th>
                            <th class="px-3 py-4 text-center font-bold text-gray-700">الكمية</th>
                            <th class="px-3 py-4 text-center font-bold text-gray-700">سعر الشراء</th>
                            <th class="px-3 py-4 text-center font-bold text-gray-700 bg-blue-50">الإجمالي</th>
                            <th class="px-3 py-4 text-center font-bold text-gray-700">حذف</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-green-50 transition-colors">
                                
                                <td class="px-3 py-3 font-bold text-gray-600" x-text="index + 1"></td>
                                
                                <!-- اختيار المنتج -->
                                <td class="px-3 py-3">
                                    <select :name="'items[' + index + '][product_id]'"
                                            x-model="item.product_id"
                                            @change="loadProductData(index)"
                                            class="w-full border-2 rounded-lg px-3 py-2 text-sm font-semibold focus:ring-2 focus:ring-green-500 transition-all">
                                        <option value="">اختر الصنف</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    <div x-show="item.product_id && item.base_unit_label" class="mt-1 flex items-center gap-1">
                                        <span class="text-xs" x-text="getUnitTypeIcon(item.base_unit_type)"></span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold"
                                              :class="{
                                                  'bg-amber-100 text-amber-800': item.base_unit_type === 'weight',
                                                  'bg-blue-100 text-blue-800': item.base_unit_type === 'volume',
                                                  'bg-green-100 text-green-800': item.base_unit_type === 'count'
                                              }"
                                              x-text="item.base_unit_label">
                                        </span>
                                    </div>
                                </td>

                                <!-- اختيار الوحدة -->
                                <td class="px-3 py-3">
                                    <select x-show="item.product_id && getSellingUnits(item.product_id).length > 0"
                                            :name="'items[' + index + '][selling_unit_id]'"
                                            x-model="item.selling_unit_id"
                                            @change="selectUnit(index, item.selling_unit_id)"
                                            class="w-full border-2 border-purple-300 rounded-lg px-3 py-2 text-sm font-semibold bg-purple-50 focus:ring-2 focus:ring-purple-500">
                                        <template x-for="unit in getSellingUnits(item.product_id)" :key="unit.id">
                                            <option :value="unit.id" x-text="unit.unit_label + ' (' + unit.conversion_factor + 'x)'"></option>
                                        </template>
                                    </select>
                                </td>

                                <!-- الوزن -->
                                <td class="px-3 py-3 bg-amber-50/50">
                                    <div x-show="item.product_id && item.base_unit_type === 'weight'">
                                        <div class="flex items-center gap-1">
                                            <input type="number" 
                                                   :name="'items[' + index + '][weight]'"
                                                   x-model="item.weight" 
                                                   @input="onWeightChange(index)"
                                                   class="w-24 border-2 border-amber-300 rounded-lg px-2 py-2 text-sm font-bold text-center bg-amber-50 focus:ring-2 focus:ring-amber-500" 
                                                   min="0.001" step="0.001">
                                            <span class="text-xs font-bold text-amber-700" x-text="item.base_unit_label"></span>
                                        </div>
                                    </div>
                                    <div x-show="item.product_id && item.base_unit_type !== 'weight'" class="text-center">
                                        <span class="text-xs text-gray-400">—</span>
                                    </div>
                                </td>

                                <!-- الكمية المتاحة -->
                                <td class="px-3 py-3 text-center bg-green-50">
                                    <span x-show="item.product_id && warehouseId"
                                          x-text="item.available_stock_in_unit"
                                          class="font-bold text-lg text-green-600">
                                    </span>
                                    <span x-show="item.unit_label" class="text-xs text-gray-600" x-text="item.unit_label"></span>
                                </td>

                                <!-- الكمية -->
                                <td class="px-3 py-3">
                                    <input type="number" 
                                           :name="'items[' + index + '][qty]'"
                                           x-model="item.quantity" 
                                           @input="calculateItemTotal(index)"
                                           class="w-24 border-2 rounded-lg px-3 py-2 text-sm font-bold text-center focus:ring-2 focus:ring-green-500" 
                                           min="0.001" step="0.001" required>
                                </td>

                                <!-- السعر -->
                                <td class="px-3 py-3">
                                    <input type="number" 
                                           :name="'items[' + index + '][price]'"
                                           x-model="item.price" 
                                           @input="calculateItemTotal(index)"
                                           class="w-28 border-2 border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-center focus:ring-2 focus:ring-green-500" 
                                           min="0" step="0.01" required>
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

                                <!-- Hidden inputs -->
                                <input type="hidden" :name="'items[' + index + '][conversion_factor]'" x-model="item.conversion_factor">
                                <input type="hidden" :name="'items[' + index + '][unit_code]'" x-model="item.unit_code">
                                <input type="hidden" :name="'items[' + index + '][base_unit_type]'" x-model="item.base_unit_type">
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals -->
        <div class="grid md:grid-cols-3 gap-6 mb-6">
            <!-- الإجماليات -->
            <div class="md:col-span-2 bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 p-6 rounded-2xl shadow-lg">
                <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">الحسابات</h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-2 border-b border-green-200">
                        <span class="text-sm text-gray-700">الإجمالي:</span>
                        <span x-text="subtotal.toFixed(2) + ' ج.م'" class="font-bold text-gray-800"></span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-green-200">
                        <span class="text-sm text-red-600">الخصم:</span>
                        <input type="number" name="discount" x-model="discount" @input="calculateTotals()" 
                               value="{{ old('discount', 0) }}"
                               class="border-2 border-gray-300 rounded-lg px-3 py-1 text-sm font-bold text-right w-32" min="0" step="0.01">
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-green-200">
                        <span class="text-sm text-blue-600">الضريبة:</span>
                        <input type="number" name="tax" x-model="tax" @input="calculateTotals()" 
                               value="{{ old('tax', 0) }}"
                               class="border-2 border-gray-300 rounded-lg px-3 py-1 text-sm font-bold text-right w-32" min="0" step="0.01">
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-lg font-bold text-green-700">الإجمالي النهائي:</span>
                        <span x-text="grandTotal.toFixed(2) + ' ج.م'" class="text-2xl font-bold text-green-700"></span>
                    </div>
                </div>
            </div>

            <!-- الملاحظات -->
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 border-2 border-purple-200 p-6 rounded-2xl shadow-lg">
                <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">ملاحظات</h4>
                <textarea name="notes" 
                          class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 h-32 text-sm focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all resize-none" 
                          placeholder="أضف ملاحظات إضافية هنا..."></textarea>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex gap-4 items-center justify-end bg-white rounded-xl shadow-lg p-6">
            <a href="{{ route('invoices.purchases.index') }}" 
               class="px-8 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all font-bold flex items-center gap-2 border-2 border-gray-300">
                إلغاء
            </a>
            <button type="submit" 
                    class="px-10 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition-all flex items-center gap-3">
                💾 حفظ الفاتورة
            </button>
        </div>
    </form>
    
    <!-- Modal إضافة منتج جديد -->
    <div x-show="showNewProductForm" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showNewProductForm = false"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full"
                 x-show="showNewProductForm"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        إضافة منتج جديد
                    </h3>
                    <p class="text-purple-100 text-sm mt-1">سيتم إضافة المنتج للفاتورة تلقائياً</p>
                </div>

                <div class="px-6 py-6">
                    <div class="space-y-4">
                        <!-- اسم المنتج -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">اسم المنتج *</label>
                            <input type="text" x-model="newProduct.name" 
                                   class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500"
                                   placeholder="مثال: أسمنت بورتلاند">
                        </div>

                        <!-- الوحدة الأساسية -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">الوحدة الأساسية *</label>
                            <select x-model="newProduct.base_unit" @change="updateNewProductUnitLabel()"
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500">
                                <option value="piece">قطعة</option>
                                <option value="kg">كيلو</option>
                                <option value="ton">طن</option>
                                <option value="gram">جرام</option>
                                <option value="meter">متر</option>
                                <option value="liter">لتر</option>
                                <option value="box">صندوق</option>
                                <option value="pack">حزمة</option>
                            </select>
                        </div>

                        <!-- نوع الوحدة (للنظام) -->
                        <input type="hidden" x-model="newProduct.base_unit_type">
                        <input type="hidden" x-model="newProduct.base_unit_label">

                        <!-- أسعار المنتج -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">سعر الشراء</label>
                                <input type="number" x-model="newProduct.purchase_price" 
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500"
                                       placeholder="0" min="0" step="0.01">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">سعر البيع</label>
                                <input type="number" x-model="newProduct.selling_price" 
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500"
                                       placeholder="0" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
                    <button type="button" @click="showNewProductForm = false"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-bold">
                        إلغاء
                    </button>
                    <button type="button" @click="saveNewProduct()"
                            class="px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 font-bold">
                        ✅ إضافة المنتج
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
