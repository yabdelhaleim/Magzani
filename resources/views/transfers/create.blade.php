<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تحويل جديد بين المخازن</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { 
            font-family: 'Cairo', sans-serif; 
        }
        .card-shadow { 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); 
        }
        [x-cloak] { 
            display: none !important; 
        }
        
        /* تحسين الـ transitions */
        .smooth-transition {
            transition: all 0.3s ease;
        }
        
        /* تحسين شكل الـ inputs */
        input:focus, select:focus, textarea:focus {
            outline: none;
            transform: scale(1.01);
        }
        
        /* Loading spinner */
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-6 max-w-7xl" x-data="transferForm()">
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">🔄 تحويل جديد بين المخازن</h1>
                    <p class="text-gray-600 text-sm mt-1">انقل المنتجات من مخزن إلى آخر بسهولة وأمان</p>
                </div>
                <a href="{{ route('transfers.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg flex items-center gap-2 smooth-transition">
                    <i class="fas fa-arrow-right"></i>
                    <span>رجوع</span>
                </a>
            </div>
        </div>

        <!-- Errors Alert -->
        @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg smooth-transition">
            <div class="flex items-start">
                <i class="fas fa-exclamation-circle text-red-500 mt-1 ml-3"></i>
                <div class="flex-1">
                    <h3 class="text-red-800 font-bold">يوجد أخطاء في النموذج</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- Success Message -->
        @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg smooth-transition">
            <div class="flex items-start">
                <i class="fas fa-check-circle text-green-500 mt-1 ml-3"></i>
                <div class="flex-1">
                    <h3 class="text-green-800 font-bold">{{ session('success') }}</h3>
                </div>
            </div>
        </div>
        @endif

        <!-- Main Form -->
        <form action="{{ route('transfers.store') }}" 
              method="POST" 
              @submit.prevent="validateForm"
              x-ref="transferForm"
              id="transferForm">
            @csrf
            
            <!-- ============================================ -->
            <!-- 1️⃣ معلومات التحويل -->
            <!-- ============================================ -->
            <div class="bg-white rounded-lg card-shadow mb-6 smooth-transition">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 rounded-t-lg">
                    <h2 class="text-lg font-bold flex items-center gap-2">
                        <i class="fas fa-info-circle"></i>
                        معلومات التحويل
                    </h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <!-- المخزن المصدر -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                📦 المخزن المصدر <span class="text-red-500">*</span>
                            </label>
                            <select name="from_warehouse_id" 
                                    x-model="fromWarehouse"
                                    @change="onWarehouseChange('from')"
                                    required
                                    class="w-full px-4 py-3 border-2 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 smooth-transition">
                                <option value="">-- اختر المخزن المصدر --</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">
                                    {{ $warehouse->name }} ({{ $warehouse->code }})
                                </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">المخزن الذي سيتم السحب منه</p>
                        </div>

                        <!-- المخزن الوجهة -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                📥 المخزن الوجهة <span class="text-red-500">*</span>
                            </label>
                            <select name="to_warehouse_id" 
                                    x-model="toWarehouse"
                                    required
                                    class="w-full px-4 py-3 border-2 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 smooth-transition">
                                <option value="">-- اختر المخزن الوجهة --</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">
                                    {{ $warehouse->name }} ({{ $warehouse->code }})
                                </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">المخزن الذي سيتم الإضافة إليه</p>
                        </div>

                        <!-- تاريخ التحويل -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                📅 تاريخ التحويل <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="transfer_date" 
                                   value="{{ old('transfer_date', date('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}"
                                   required
                                   class="w-full px-4 py-3 border-2 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 smooth-transition">
                            <p class="text-xs text-gray-500 mt-1">تاريخ تنفيذ التحويل</p>
                        </div>
                    </div>

                    <!-- Warehouse Warning -->
                    <div x-show="fromWarehouse && toWarehouse && fromWarehouse === toWarehouse" 
                         x-cloak
                         x-transition
                         class="mt-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                        <div class="flex items-center gap-2 text-red-700">
                            <i class="fas fa-exclamation-triangle text-xl"></i>
                            <strong>تحذير:</strong> لا يمكن التحويل من نفس المخزن إلى نفسه!
                        </div>
                    </div>

                    <!-- ملاحظات -->
                    <div class="mt-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            📝 ملاحظات (اختياري)
                        </label>
                        <textarea name="notes" 
                                  rows="3"
                                  class="w-full px-4 py-3 border-2 rounded-lg focus:border-gray-400 focus:ring-2 focus:ring-gray-200 smooth-transition"
                                  placeholder="أضف أي ملاحظات إضافية حول التحويل...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- 2️⃣ المنتجات -->
            <!-- ============================================ -->
            <div class="bg-white rounded-lg card-shadow mb-6 smooth-transition">
                <div class="bg-gradient-to-r from-green-600 to-green-700 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                    <h2 class="text-lg font-bold flex items-center gap-2">
                        <i class="fas fa-boxes"></i>
                        المنتجات
                    </h2>
                    <button type="button" 
                            @click="addProduct()"
                            :disabled="!canAddProduct()"
                            class="px-4 py-2 bg-white text-green-600 rounded-lg font-bold hover:bg-green-50 disabled:opacity-50 disabled:cursor-not-allowed smooth-transition">
                        <i class="fas fa-plus ml-1"></i> إضافة منتج
                    </button>
                </div>
                
                <div class="p-6">
                    
                    <!-- تحذير: اختر المخزن أولاً -->
                    <div x-show="!fromWarehouse" x-cloak x-transition class="text-center py-12">
                        <div class="inline-block p-6 bg-yellow-50 rounded-lg border-2 border-yellow-200">
                            <i class="fas fa-exclamation-triangle text-5xl text-yellow-500 mb-3"></i>
                            <h3 class="text-lg font-bold text-gray-700">اختر المخزن المصدر أولاً</h3>
                            <p class="text-gray-600 text-sm mt-2">لا يمكن إضافة منتجات قبل اختيار المخزن المصدر</p>
                        </div>
                    </div>

                    <!-- لا توجد منتجات متاحة -->
                    <div x-show="fromWarehouse && getAvailableProductsCount() === 0" x-cloak x-transition class="text-center py-12">
                        <div class="inline-block p-6 bg-red-50 rounded-lg border-2 border-red-200">
                            <i class="fas fa-box-open text-5xl text-red-500 mb-3"></i>
                            <h3 class="text-lg font-bold text-gray-700">لا توجد منتجات متاحة</h3>
                            <p class="text-gray-600 text-sm mt-2">المخزن المصدر لا يحتوي على أي منتجات بكمية متاحة</p>
                        </div>
                    </div>

                    <!-- قائمة المنتجات -->
                    <div x-show="fromWarehouse && getAvailableProductsCount() > 0" x-cloak>
                        
                        <!-- رسالة: لم يتم إضافة منتجات -->
                        <div x-show="items.length === 0" x-transition class="text-center py-12">
                            <div class="inline-block p-6 bg-gray-50 rounded-lg border-2 border-gray-200">
                                <i class="fas fa-box text-5xl text-gray-400 mb-3"></i>
                                <h3 class="text-lg font-bold text-gray-700 mb-2">لم تضف أي منتجات بعد</h3>
                                <p class="text-gray-600 text-sm mb-4">ابدأ بإضافة المنتجات المراد نقلها من المخزن المصدر إلى المخزن الوجهة</p>
                                <button type="button" 
                                        @click="addProduct()"
                                        class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-bold smooth-transition">
                                    <i class="fas fa-plus ml-1"></i> إضافة أول منتج
                                </button>
                            </div>
                        </div>

                        <!-- جدول المنتجات -->
                        <div x-show="items.length > 0" x-transition class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gradient-to-r from-gray-100 to-gray-200 border-b-2 border-gray-300">
                                        <th class="px-4 py-3 text-right text-sm font-bold text-gray-700">#</th>
                                        <th class="px-4 py-3 text-right text-sm font-bold text-gray-700">المنتج</th>
                                        <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">المتاح في المخزن</th>
                                        <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">الكمية المنقولة</th>
                                        <th class="px-4 py-3 text-right text-sm font-bold text-gray-700">ملاحظات</th>
                                        <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">حذف</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, index) in items" :key="'item-' + index">
                                        <tr class="border-b hover:bg-gray-50 smooth-transition">
                                            <!-- الرقم -->
                                            <td class="px-4 py-4 text-center">
                                                <span class="inline-block w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center" x-text="index + 1"></span>
                                            </td>

                                            <!-- المنتج -->
                                            <td class="px-4 py-4">
                                                <select :name="'items['+index+'][product_id]'" 
                                                        x-model="item.product_id"
                                                        @change="updateProductInfo(index)"
                                                        required
                                                        class="w-full px-3 py-2 border-2 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 smooth-transition">
                                                    <option value="">-- اختر المنتج --</option>
                                                    <template x-for="product in getAvailableProducts()" :key="product.id">
                                                        <option :value="product.id" 
                                                                x-text="product.name + ' - ' + (product.sku || 'بدون كود')"
                                                                :disabled="isProductSelected(product.id, index)">
                                                        </option>
                                                    </template>
                                                </select>
                                            </td>

                                            <!-- المتاح -->
                                            <td class="px-4 py-4 text-center">
                                                <div class="inline-block px-4 py-2 rounded-lg font-bold text-lg smooth-transition"
                                                     :class="{
                                                         'bg-red-100 text-red-700': item.available !== null && item.available <= 0,
                                                         'bg-yellow-100 text-yellow-700': item.available !== null && item.available > 0 && item.available < 10,
                                                         'bg-green-100 text-green-700': item.available !== null && item.available >= 10,
                                                         'bg-gray-100 text-gray-500': item.available === null
                                                     }">
                                                    <span x-text="item.available !== null ? parseFloat(item.available).toFixed(2) : '--'"></span>
                                                </div>
                                            </td>

                                            <!-- الكمية -->
                                            <td class="px-4 py-4">
                                                <input type="number" 
                                                       :name="'items['+index+'][quantity]'" 
                                                       x-model.number="item.quantity"
                                                       :max="item.available"
                                                       min="0.01"
                                                       step="0.01"
                                                       required
                                                       class="w-32 px-3 py-2 border-2 rounded-lg text-center font-bold focus:ring-2 smooth-transition"
                                                       :class="{
                                                           'border-red-500 bg-red-50 focus:border-red-600 focus:ring-red-200': item.available !== null && item.quantity > item.available,
                                                           'border-green-500 focus:border-green-600 focus:ring-green-200': item.available !== null && item.quantity > 0 && item.quantity <= item.available
                                                       }">
                                                
                                                <!-- تحذير: الكمية أكبر من المتاح -->
                                                <div x-show="item.available !== null && item.quantity > item.available" 
                                                     x-cloak 
                                                     x-transition
                                                     class="mt-1 text-xs text-red-600 font-bold">
                                                    ⚠️ الكمية أكبر من المتاح!
                                                </div>
                                            </td>

                                            <!-- ملاحظات -->
                                            <td class="px-4 py-4">
                                                <input type="text" 
                                                       :name="'items['+index+'][notes]'" 
                                                       x-model="item.notes"
                                                       placeholder="ملاحظات اختيارية..."
                                                       class="w-full px-3 py-2 border-2 rounded-lg focus:border-gray-400 focus:ring-2 focus:ring-gray-200 smooth-transition">
                                            </td>

                                            <!-- حذف -->
                                            <td class="px-4 py-4 text-center">
                                                <button type="button" 
                                                        @click="removeProduct(index)"
                                                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg smooth-transition">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                
                                <!-- Footer: الإجمالي -->
                                <tfoot x-show="items.length > 0">
                                    <tr class="bg-gradient-to-r from-gray-100 to-gray-200 border-t-2 border-gray-300">
                                        <td colspan="3" class="px-4 py-3 text-left font-bold text-gray-700">
                                            <i class="fas fa-calculator ml-1"></i> الإجمالي
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg font-bold text-lg" 
                                                  x-text="totalQuantity()"></span>
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- 3️⃣ ملخص وأزرار -->
            <!-- ============================================ -->
            <div class="bg-white rounded-lg card-shadow smooth-transition">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        
                        <!-- الملخص -->
                        <div class="flex gap-6">
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <div class="text-3xl font-bold text-blue-600" x-text="items.length">0</div>
                                <div class="text-sm text-gray-600 mt-1">منتج</div>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <div class="text-3xl font-bold text-green-600" x-text="totalQuantity()">0</div>
                                <div class="text-sm text-gray-600 mt-1">إجمالي الكمية</div>
                            </div>
                        </div>

                        <!-- Validation Alerts -->
                        <div class="flex-1">
                            <div x-show="!canSubmit()" x-cloak x-transition class="text-sm text-red-600 font-bold text-center bg-red-50 p-3 rounded-lg">
                                <template x-if="!fromWarehouse || !toWarehouse">
                                    <div><i class="fas fa-exclamation-triangle ml-1"></i> اختر المخازن أولاً</div>
                                </template>
                                <template x-if="fromWarehouse && toWarehouse && fromWarehouse === toWarehouse">
                                    <div><i class="fas fa-exclamation-triangle ml-1"></i> لا يمكن التحويل لنفس المخزن</div>
                                </template>
                                <template x-if="fromWarehouse && toWarehouse && fromWarehouse !== toWarehouse && items.length === 0">
                                    <div><i class="fas fa-exclamation-triangle ml-1"></i> أضف منتج واحد على الأقل</div>
                                </template>
                                <template x-if="items.length > 0 && items.some(item => item.available !== null && item.quantity > item.available)">
                                    <div><i class="fas fa-exclamation-triangle ml-1"></i> بعض الكميات تتجاوز المتاح</div>
                                </template>
                            </div>
                        </div>

                        <!-- أزرار -->
                        <div class="flex gap-3">
                            <a href="{{ route('transfers.index') }}" 
                               class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-bold smooth-transition">
                                <i class="fas fa-times ml-1"></i> إلغاء
                            </a>
                            
                            <button type="submit" 
                                    :disabled="!canSubmit() || isSubmitting"
                                    class="px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg font-bold text-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 smooth-transition">
                                <template x-if="!isSubmitting">
                                    <i class="fas fa-check-circle"></i>
                                </template>
                                <template x-if="isSubmitting">
                                    <div class="spinner"></div>
                                </template>
                                <span x-text="isSubmitting ? 'جاري التنفيذ...' : 'تنفيذ التحويل'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- معلومة مهمة -->
                    <div class="mt-4 p-4 bg-blue-50 rounded-lg border-2 border-blue-200">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-500 text-xl mt-1"></i>
                            <div>
                                <p class="text-blue-800 font-bold">💡 ملاحظة هامة</p>
                                <p class="text-blue-700 text-sm mt-1">سيتم تنفيذ التحويل مباشرة وتحديث المخزون في كلا المخزنين فوراً. تأكد من صحة البيانات قبل التنفيذ.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
    function transferForm() {
        return {
            // Properties
            fromWarehouse: '',
            toWarehouse: '',
            items: [],
            oldFromWarehouse: '',
            isInitializing: true,
            isSubmitting: false,

            // ============================================
            // Initialization
            // ============================================
            init() {
                console.log('🚀 Form Initialization Started');
                
                // Set old values from Laravel old() helper
                const oldFrom = '{{ old("from_warehouse_id", "") }}';
                const oldTo = '{{ old("to_warehouse_id", "") }}';
                
                if (oldFrom) {
                    this.fromWarehouse = oldFrom;
                    this.oldFromWarehouse = oldFrom;
                    console.log('📦 Restored from warehouse:', oldFrom);
                }
                
                if (oldTo) {
                    this.toWarehouse = oldTo;
                    console.log('📦 Restored to warehouse:', oldTo);
                }
                
                // Restore old items if validation failed
                @if(old('items'))
                try {
                    const oldItems = @json(old('items'));
                    console.log('📦 Old items found:', oldItems);
                    
                    if (Array.isArray(oldItems) && oldItems.length > 0) {
                        oldItems.forEach((item, idx) => {
                            this.items.push({
                                product_id: String(item.product_id || ''),
                                quantity: parseFloat(item.quantity) || 1,
                                available: null,
                                notes: String(item.notes || '')
                            });
                            console.log(`✅ Restored item ${idx + 1}:`, this.items[idx]);
                        });
                        
                        // Update product info after DOM is ready
                        this.$nextTick(() => {
                            this.items.forEach((item, index) => {
                                if (item.product_id) {
                                    this.updateProductInfo(index);
                                }
                            });
                            console.log('✅ Product info updated for all items');
                        });
                    }
                } catch (e) {
                    console.error('❌ Error restoring items:', e);
                }
                @endif

                // Mark initialization complete
                setTimeout(() => {
                    this.isInitializing = false;
                    console.log('✅ Initialization Complete');
                }, 200);
            },

            // ============================================
            // Warehouse Management
            // ============================================
            onWarehouseChange(type) {
                if (this.isInitializing) {
                    console.log('⏳ Skipping warehouse change during init');
                    return;
                }
                
                console.log(`🔄 Warehouse changed (${type}):`, this.fromWarehouse);
                
                if (type === 'from' && this.items.length > 0) {
                    if (!confirm('⚠️ تغيير المخزن المصدر سيؤدي لحذف جميع المنتجات المضافة.\n\nهل تريد المتابعة؟')) {
                        // Revert to old warehouse
                        this.$nextTick(() => {
                            this.fromWarehouse = this.oldFromWarehouse;
                        });
                        console.log('↩️ Warehouse change cancelled');
                        return;
                    }
                    
                    // Clear all items
                    this.items = [];
                    console.log('🗑️ All items cleared');
                }
                
                this.oldFromWarehouse = this.fromWarehouse;
            },

            // ============================================
            // Product Management
            // ============================================
            addProduct() {
                if (!this.canAddProduct()) {
                    console.warn('⚠️ Cannot add product - conditions not met');
                    alert('⚠️ يرجى اختيار المخزن المصدر أولاً');
                    return;
                }
                
                const newItem = {
                    product_id: '',
                    quantity: 1,
                    available: null,
                    notes: ''
                };
                
                this.items.push(newItem);
                console.log('➕ Product added. Total items:', this.items.length);
            },

            removeProduct(index) {
                if (!confirm('🗑️ هل تريد حذف هذا المنتج من قائمة التحويل؟')) {
                    return;
                }
                
                const removedItem = this.items[index];
                this.items.splice(index, 1);
                console.log('🗑️ Product removed:', removedItem, 'Remaining:', this.items.length);
            },

            canAddProduct() {
                const canAdd = this.fromWarehouse && this.getAvailableProductsCount() > 0;
                return canAdd;
            },

            // ============================================
            // Products Data
            // ============================================
            getAvailableProducts() {
                if (!this.fromWarehouse) {
                    return [];
                }
                
                try {
                    const warehousesStock = @json($warehousesStock ?? []);
                    const allProducts = @json($products ?? []);
                    const warehouseStock = warehousesStock[this.fromWarehouse] || {};
                    
                    const availableProducts = allProducts
                        .map(product => {
                            const stock = warehouseStock[product.id];
                            if (stock && parseFloat(stock.available) > 0) {
                                return {
                                    id: product.id,
                                    name: product.name || 'منتج',
                                    sku: product.sku || product.code || '',
                                    available: parseFloat(stock.available)
                                };
                            }
                            return null;
                        })
                        .filter(p => p !== null);
                    
                    return availableProducts;
                } catch (e) {
                    console.error('❌ Error getting available products:', e);
                    return [];
                }
            },

            getAvailableProductsCount() {
                return this.getAvailableProducts().length;
            },

            isProductSelected(productId, currentIndex) {
                return this.items.some((item, idx) => 
                    idx !== currentIndex && String(item.product_id) === String(productId)
                );
            },

            updateProductInfo(index) {
                if (!this.items[index]) {
                    console.warn('⚠️ Item not found at index:', index);
                    return;
                }
                
                const productId = this.items[index].product_id;
                
                if (!this.fromWarehouse || !productId) {
                    this.items[index].available = null;
                    return;
                }

                try {
                    const warehousesStock = @json($warehousesStock ?? []);
                    const warehouseStock = warehousesStock[this.fromWarehouse] || {};
                    
                    if (warehouseStock[productId]) {
                        const availableQty = parseFloat(warehouseStock[productId].available);
                        this.items[index].available = availableQty;
                        console.log(`📊 Product ${productId} - Available: ${availableQty}`);
                        
                        // Auto-adjust quantity if needed
                        const currentQty = parseFloat(this.items[index].quantity) || 0;
                        if (currentQty === 0 || currentQty > availableQty) {
                            this.items[index].quantity = Math.min(1, availableQty);
                            console.log(`🔧 Auto-adjusted quantity to: ${this.items[index].quantity}`);
                        }
                    } else {
                        this.items[index].available = 0;
                        console.warn('⚠️ No stock found for product:', productId);
                    }
                } catch (e) {
                    console.error('❌ Error updating product info:', e);
                    this.items[index].available = 0;
                }
            },

            // ============================================
            // Calculations
            // ============================================
            totalQuantity() {
                const total = this.items.reduce((sum, item) => {
                    return sum + (parseFloat(item.quantity) || 0);
                }, 0);
                return total.toFixed(2);
            },

            // ============================================
            // Validation
            // ============================================
            canSubmit() {
                // Check warehouses selected
                if (!this.fromWarehouse || !this.toWarehouse) {
                    return false;
                }
                
                // Check same warehouse
                if (this.fromWarehouse === this.toWarehouse) {
                    return false;
                }
                
                // Check at least one item
                if (this.items.length === 0) {
                    return false;
                }
                
                // Check all items are valid
                const allItemsValid = this.items.every(item => {
                    const qty = parseFloat(item.quantity) || 0;
                    const avail = parseFloat(item.available);
                    
                    // Must have product selected
                    if (!item.product_id) return false;
                    
                    // Must have positive quantity
                    if (qty <= 0) return false;
                    
                    // Must have available info
                    if (item.available === null) return false;
                    
                    // Quantity must not exceed available
                    if (qty > avail) return false;
                    
                    return true;
                });
                
                return allItemsValid;
            },

            // ============================================
            // Form Submission
            // ============================================
            validateForm() {
                console.log('📝 Starting form validation...');
                console.log('📦 From Warehouse:', this.fromWarehouse);
                console.log('📦 To Warehouse:', this.toWarehouse);
                console.log('📦 Items count:', this.items.length);
                console.log('📦 Items data:', JSON.stringify(this.items, null, 2));
                
                // Prevent double submission
                if (this.isSubmitting) {
                    console.warn('⚠️ Form is already being submitted');
                    return;
                }
                
                // Check if form can be submitted
                if (!this.canSubmit()) {
                    let errorMessage = '⚠️ يرجى التأكد من:\n\n';
                    
                    if (!this.fromWarehouse || !this.toWarehouse) {
                        errorMessage += '• اختيار المخازن المصدر والوجهة\n';
                    }
                    
                    if (this.fromWarehouse === this.toWarehouse) {
                        errorMessage += '• عدم التحويل من نفس المخزن إلى نفسه\n';
                    }
                    
                    if (this.items.length === 0) {
                        errorMessage += '• إضافة منتج واحد على الأقل\n';
                    }
                    
                    const invalidItems = this.items.filter(item => 
                        item.available !== null && item.quantity > item.available
                    );
                    
                    if (invalidItems.length > 0) {
                        errorMessage += '• التأكد من أن جميع الكميات لا تتجاوز المتاح\n';
                    }
                    
                    alert(errorMessage);
                    console.error('❌ Validation failed');
                    return;
                }
                
                // Confirmation dialog
                const confirmed = confirm(
                    `✅ هل أنت متأكد من تنفيذ التحويل؟\n\n` +
                    `📦 عدد المنتجات: ${this.items.length}\n` +
                    `📊 إجمالي الكمية: ${this.totalQuantity()}\n` +
                    `🏭 من: ${this.getWarehouseName(this.fromWarehouse)}\n` +
                    `🏢 إلى: ${this.getWarehouseName(this.toWarehouse)}\n\n` +
                    `سيتم تحديث المخزون مباشرة بعد التنفيذ.`
                );
                
                if (!confirmed) {
                    console.log('↩️ User cancelled submission');
                    return;
                }
                
                console.log('✅ Validation passed, submitting form...');
                
                // Set submitting state
                this.isSubmitting = true;
                
                // Log form data for debugging
                const formData = new FormData(this.$refs.transferForm);
                console.log('📤 Form data entries:');
                for (let [key, value] of formData.entries()) {
                    console.log(`  ${key}: ${value}`);
                }
                
                // Submit the form
                this.$refs.transferForm.submit();
            },

            // ============================================
            // Helper Methods
            // ============================================
            getWarehouseName(warehouseId) {
                if (!warehouseId) return '';
                
                const warehouses = @json($warehouses ?? []);
                const warehouse = warehouses.find(w => String(w.id) === String(warehouseId));
                return warehouse ? warehouse.name : 'غير معروف';
            }
        };
    }
    </script>
</body>
</html>