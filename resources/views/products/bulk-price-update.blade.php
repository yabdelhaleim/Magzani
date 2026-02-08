{{-- 
=====================================================================
🔧 الـ Blade المُصلح - bulk-price-update.blade.php
=====================================================================
المشكلة: القائمة المنسدلة للوحدات كانت فاضية أو مش بتظهر الوحدات صح
الحل: تحسين طريقة عرض الوحدات + إصلاح Alpine.js
===================================================================== 
--}}

@extends('layouts.app')

@section('title', 'التحديث الذكي للأسعار')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-8" x-data="smartPriceUpdate()">

    {{-- Alerts --}}
    @if(session('success'))
        <div class="mb-6 bg-green-100 border-r-4 border-green-500 p-4 rounded-lg">
            <p class="font-bold text-green-800">{!! nl2br(e(session('success'))) !!}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-100 border-r-4 border-red-500 p-4 rounded-lg">
            <p class="font-bold text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-xl p-8 mb-8 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold mb-2">🎯 التحديث الذكي للأسعار</h1>
                <p class="text-blue-100 text-lg">حدّث أسعار منتجات محددة بدقة عالية حسب الوحدة والتصنيف</p>
            </div>
            <a href="{{ route('products.index') }}" 
               class="bg-white text-blue-600 px-6 py-3 rounded-xl hover:bg-blue-50 transition-all font-bold shadow-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                </svg>
                رجوع للمنتجات
            </a>
        </div>
    </div>

    <form @submit.prevent="submitUpdate">
        {{-- Step 1: Filters --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b-2 border-blue-200 p-6">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                    <span class="bg-blue-600 text-white w-10 h-10 rounded-full flex items-center justify-center text-lg font-bold">1</span>
                    <span>🔍 اختر الوحدة والتصنيف</span>
                </h2>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    {{-- ✅ Unit Selection - المُصلح --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3">
                            الوحدة الأساسية <span class="text-red-500">*</span>
                        </label>
                        <select x-model="filters.base_unit" 
                                @change="loadCategories()"
                                class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-lg font-semibold bg-white"
                                required>
                            <option value="">-- اختر الوحدة الأساسية --</option>
                            
                            {{-- ✅ طريقة 1: إذا كان عندك unitsByCategory --}}
                            @if(isset($unitsByCategory) && !empty($unitsByCategory))
                                @foreach($unitsByCategory as $categoryKey => $categoryData)
                                    <optgroup label="{{ $categoryData['label'] ?? $categoryKey }}">
                                        @foreach($categoryData['units'] as $unitCode => $unitLabel)
                                            <option value="{{ $unitCode }}">{{ $unitLabel }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            
                            {{-- ✅ طريقة 2: إذا كان عندك activeUnits (Fallback) --}}
                            @elseif(isset($activeUnits) && !empty($activeUnits))
                                @foreach($activeUnits as $unitCode => $unitLabel)
                                    <option value="{{ $unitCode }}">{{ $unitLabel }}</option>
                                @endforeach
                            
                            {{-- ✅ طريقة 3: الوحدات الأكثر استخداماً --}}
                            @elseif(isset($mostUsedUnits) && !empty($mostUsedUnits))
                                @foreach($mostUsedUnits as $unit)
                                    <option value="{{ $unit['code'] }}">{{ $unit['label'] }}</option>
                                @endforeach
                            
                            {{-- ✅ طريقة 4: وحدات افتراضية كـ Fallback --}}
                            @else
                                <optgroup label="وحدات الوزن">
                                    <option value="TON">طن</option>
                                    <option value="KG">كيلوجرام</option>
                                    <option value="GM">جرام</option>
                                </optgroup>
                                <optgroup label="وحدات الحجم">
                                    <option value="LTR">لتر</option>
                                    <option value="ML">مليلتر</option>
                                </optgroup>
                                <optgroup label="وحدات العدد">
                                    <option value="UNIT">وحدة</option>
                                    <option value="PIECE">قطعة</option>
                                    <option value="BOX">صندوق</option>
                                    <option value="CARTON">كرتونة</option>
                                </optgroup>
                            @endif
                        </select>
                        
                        {{-- ✅ رسالة توضيحية --}}
                        <p class="text-xs text-gray-500 mt-2">
                            💡 اختر الوحدة الأساسية للمنتجات المراد تحديثها
                        </p>
                    </div>

                    {{-- Category Selection --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3">
                            التصنيف <span class="text-red-500">*</span>
                        </label>
                        <select x-model="filters.category" 
                                @change="onCategoryChange()"
                                class="w-full px-5 py-4 border-2 rounded-xl focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all text-lg font-semibold bg-white"
                                :class="{ 'border-gray-300': filters.base_unit, 'border-gray-200 bg-gray-50': !filters.base_unit }"
                                :disabled="!filters.base_unit || loadingCategories"
                                required>
                            <option value="">
                                <template x-if="!filters.base_unit">-- اختر الوحدة أولاً --</template>
                                <template x-if="filters.base_unit && loadingCategories">⏳ جاري التحميل...</template>
                                <template x-if="filters.base_unit && !loadingCategories">-- اختر التصنيف --</template>
                            </option>
                            <template x-for="category in categories" :key="category">
                                <option :value="category" x-text="category"></option>
                            </template>
                        </select>
                        
                        {{-- ✅ رسائل الحالة المُحسّنة --}}
                        <div class="mt-2">
                            <p class="text-xs text-gray-500" x-show="!filters.base_unit">
                                ⚠️ اختر الوحدة الأساسية أولاً
                            </p>
                            <p class="text-xs text-blue-600 animate-pulse" x-show="loadingCategories">
                                ⏳ جاري تحميل التصنيفات...
                            </p>
                            <p class="text-xs text-green-600" x-show="categories.length > 0 && !loadingCategories && filters.base_unit">
                                ✓ تم تحميل <span x-text="categories.length"></span> تصنيف
                            </p>
                            <p class="text-xs text-orange-600" x-show="categories.length === 0 && !loadingCategories && filters.base_unit">
                                ⚠️ لا توجد تصنيفات لهذه الوحدة في قاعدة البيانات
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Load Products Button --}}
                <div class="mt-6" x-show="filters.base_unit && filters.category">
                    <button type="button" 
                            @click="loadProducts()"
                            :disabled="loadingProducts"
                            class="w-full px-6 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:shadow-xl transition-all font-bold text-lg flex items-center justify-center gap-3"
                            :class="loadingProducts ? 'opacity-50 cursor-not-allowed' : ''">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span x-text="loadingProducts ? '⏳ جاري البحث...' : '🔍 عرض المنتجات'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Step 2: Products Table with Preview --}}
        <div x-show="products.length > 0" 
             x-transition
             class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
            
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-b-2 border-green-200 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                        <span class="bg-green-600 text-white w-10 h-10 rounded-full flex items-center justify-center text-lg font-bold">2</span>
                        <span>📦 المنتجات المتاحة</span>
                        <span class="text-sm bg-green-100 text-green-700 px-3 py-1 rounded-full" x-text="'(' + products.length + ' منتج)'"></span>
                    </h2>
                    
                    <div class="flex gap-3">
                        <button type="button" 
                                @click="selectAll()"
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm font-semibold">
                            ✓ تحديد الكل
                        </button>
                        <button type="button" 
                                @click="deselectAll()"
                                class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition text-sm font-semibold">
                            ✗ إلغاء التحديد
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-6">
                {{-- Pricing Inputs --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-xl border-2 border-purple-200">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            💰 سعر الشراء الجديد
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   x-model="newPricing.purchase_price"
                                   @input="calculatePreview()"
                                   step="0.01" 
                                   min="0"
                                   class="w-full px-5 py-3 pr-16 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all text-lg font-semibold"
                                   placeholder="0.00"
                                   required>
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">ج.م</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            📊 نوع هامش الربح
                        </label>
                        <select x-model="newPricing.profit_type"
                                @change="calculatePreview()"
                                class="w-full px-5 py-3 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-lg font-semibold bg-white"
                                required>
                            <option value="fixed">💰 مبلغ ثابت (ج.م)</option>
                            <option value="percentage">📊 نسبة مئوية (%)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            📈 قيمة هامش الربح
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   x-model="newPricing.profit_value"
                                   @input="calculatePreview()"
                                   step="0.01" 
                                   min="0"
                                   class="w-full px-5 py-3 pr-16 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all text-lg font-semibold"
                                   placeholder="0.00"
                                   required>
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold" x-text="newPricing.profit_type === 'percentage' ? '%' : 'ج.م'"></span>
                        </div>
                    </div>
                </div>

                {{-- Products Table --}}
                <div class="overflow-x-auto rounded-xl border-2 border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-100 to-gray-200">
                            <tr>
                                <th class="px-4 py-4 text-right text-sm font-bold text-gray-700">
                                    <input type="checkbox" 
                                           @change="toggleAllProducts($event.target.checked)"
                                           class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                                </th>
                                <th class="px-6 py-4 text-right text-sm font-bold text-gray-700">اسم المنتج</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700">الوحدة</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-red-50">السعر القديم</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-green-50">السعر الجديد</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-yellow-50">هامش الربح القديم</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-blue-50">هامش الربح الجديد</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700">التغيير</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="product in products" :key="product.id">
                                <tr class="hover:bg-blue-50 transition"
                                    :class="product.selected ? 'bg-blue-50' : ''">
                                    <td class="px-4 py-4">
                                        <input type="checkbox" 
                                               x-model="product.selected"
                                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900" x-text="product.name"></div>
                                        <div class="text-xs text-gray-500" x-text="product.category"></div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800"
                                              x-text="product.base_unit_label"></span>
                                    </td>
                                    
                                    {{-- Old Prices --}}
                                    <td class="px-6 py-4 text-center bg-red-50">
                                        <div class="font-bold text-red-700" x-text="formatPrice(product.old_purchase_price)"></div>
                                        <div class="text-xs text-red-600" x-text="'→ ' + formatPrice(product.old_selling_price)"></div>
                                    </td>
                                    
                                    {{-- New Prices --}}
                                    <td class="px-6 py-4 text-center bg-green-50">
                                        <div class="font-bold text-green-700" x-text="formatPrice(product.new_purchase_price)"></div>
                                        <div class="text-xs text-green-600" x-text="'→ ' + formatPrice(product.new_selling_price)"></div>
                                    </td>
                                    
                                    {{-- Old Profit --}}
                                    <td class="px-6 py-4 text-center bg-yellow-50">
                                        <div class="font-bold text-yellow-700" x-text="formatPrice(product.old_profit)"></div>
                                        <div class="text-xs text-yellow-600" x-text="product.old_profit_percentage.toFixed(1) + '%'"></div>
                                    </td>
                                    
                                    {{-- New Profit --}}
                                    <td class="px-6 py-4 text-center bg-blue-50">
                                        <div class="font-bold text-blue-700" x-text="formatPrice(product.new_profit)"></div>
                                        <div class="text-xs text-blue-600" x-text="product.new_profit_percentage.toFixed(1) + '%'"></div>
                                    </td>
                                    
                                    {{-- Change Indicator --}}
                                    <td class="px-6 py-4 text-center">
                                        <template x-if="product.new_selling_price > product.old_selling_price">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                ↑ <span x-text="formatPrice(product.new_selling_price - product.old_selling_price)"></span>
                                            </span>
                                        </template>
                                        <template x-if="product.new_selling_price < product.old_selling_price">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                ↓ <span x-text="formatPrice(product.old_selling_price - product.new_selling_price)"></span>
                                            </span>
                                        </template>
                                        <template x-if="product.new_selling_price === product.old_selling_price">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                                = بدون تغيير
                                            </span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Summary --}}
                <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-xl p-4 border-2 border-blue-200">
                        <p class="text-sm text-blue-600 font-semibold mb-1">المنتجات المحددة</p>
                        <p class="text-2xl font-bold text-blue-700" x-text="selectedProducts.length"></p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 border-2 border-green-200">
                        <p class="text-sm text-green-600 font-semibold mb-1">إجمالي القيمة القديمة</p>
                        <p class="text-2xl font-bold text-green-700" x-text="formatPrice(totalOldValue)"></p>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-4 border-2 border-purple-200">
                        <p class="text-sm text-purple-600 font-semibold mb-1">إجمالي القيمة الجديدة</p>
                        <p class="text-2xl font-bold text-purple-700" x-text="formatPrice(totalNewValue)"></p>
                    </div>
                    <div class="bg-orange-50 rounded-xl p-4 border-2 border-orange-200">
                        <p class="text-sm text-orange-600 font-semibold mb-1">الفرق</p>
                        <p class="text-2xl font-bold" 
                           :class="totalNewValue > totalOldValue ? 'text-green-700' : 'text-red-700'"
                           x-text="formatPrice(totalNewValue - totalOldValue)"></p>
                    </div>
                </div>

                {{-- Change Reason --}}
                <div class="mt-6">
                    <label class="block text-sm font-bold text-gray-700 mb-3">
                        📝 سبب التغيير (اختياري)
                    </label>
                    <input type="text" 
                           x-model="changeReason"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                           placeholder="مثال: تحديث الأسعار حسب السوق..."
                           maxlength="500">
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div x-show="selectedProducts.length > 0" 
             x-transition
             class="bg-white rounded-2xl shadow-xl p-6 sticky bottom-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-orange-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">⚡ تحديث ذكي انتقائي</p>
                        <p class="text-gray-800 font-bold">سيتم تحديث <span x-text="selectedProducts.length"></span> منتج فقط</p>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <button type="button" 
                            @click="resetForm()"
                            class="px-8 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 transition-all font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        إعادة تعيين
                    </button>
                    <button type="submit" 
                            :disabled="submitting"
                            class="px-10 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl hover:shadow-2xl hover:scale-105 transition-all font-bold text-lg flex items-center gap-3 shadow-xl"
                            :class="submitting ? 'opacity-50 cursor-not-allowed' : ''">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span x-text="submitting ? '⏳ جاري التحديث...' : '💾 حفظ وتطبيق التحديثات'"></span>
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>

@push('scripts')
<script>
function smartPriceUpdate() {
    return {
        filters: {
            base_unit: '',
            category: ''
        },
        categories: [],
        loadingCategories: false,
        loadingProducts: false,
        products: [],
        newPricing: {
            purchase_price: 0,
            profit_type: 'fixed',
            profit_value: 0
        },
        changeReason: '',
        submitting: false,
        
        init() {
            console.log('✅ Smart Price Update initialized');
            console.log('🔍 Available data:', {
                hasUnitsByCategory: {{ isset($unitsByCategory) ? 'true' : 'false' }},
                hasActiveUnits: {{ isset($activeUnits) ? 'true' : 'false' }},
                hasMostUsedUnits: {{ isset($mostUsedUnits) ? 'true' : 'false' }}
            });
        },
        
        async loadCategories() {
            if (!this.filters.base_unit) {
                this.categories = [];
                this.products = [];
                this.filters.category = '';
                return;
            }
            
            this.loadingCategories = true;
            this.filters.category = '';
            this.products = [];
            
            console.log('🔄 Loading categories for unit:', this.filters.base_unit);
            
            try {
                const url = `/products/ajax/categories-by-unit?base_unit=${encodeURIComponent(this.filters.base_unit)}`;
                console.log('📡 Request URL:', url);
                
                const response = await fetch(url);
                console.log('📨 Response Status:', response.status);
                
                const data = await response.json();
                console.log('📦 Full Response:', data);
                
                if (data.success) {
                    this.categories = data.categories || [];
                    console.log('✅ Categories loaded:', this.categories);
                    
                    if (this.categories.length === 0) {
                        alert('⚠️ لا توجد منتجات مخزنة بهذه الوحدة في قاعدة البيانات\n\nالوحدة: ' + this.filters.base_unit);
                    }
                } else {
                    this.categories = [];
                    console.warn('⚠️ No categories:', data.message);
                    alert(`⚠️ ${data.message}\n\nالوحدة: ${this.filters.base_unit}`);
                }
            } catch (error) {
                console.error('❌ Error loading categories:', error);
                alert('❌ حدث خطأ أثناء تحميل التصنيفات\n\nتفاصيل الخطأ: ' + error.message);
                this.categories = [];
            } finally {
                this.loadingCategories = false;
            }
        },
        
        onCategoryChange() {
            console.log('📂 Category changed to:', this.filters.category);
        },
        
        async loadProducts() {
            if (!this.filters.base_unit || !this.filters.category) {
                alert('⚠️ يجب اختيار الوحدة والتصنيف أولاً');
                return;
            }
            
            this.loadingProducts = true;
            this.products = [];
            
            console.log('🔄 Loading products for:', {
                base_unit: this.filters.base_unit,
                category: this.filters.category
            });
            
            try {
                const url = `/products/ajax/by-unit-category?base_unit=${encodeURIComponent(this.filters.base_unit)}&category=${encodeURIComponent(this.filters.category)}`;
                const response = await fetch(url);
                const data = await response.json();
                
                console.log('📦 Products response:', data);
                
                if (data.success) {
                    this.products = data.products.map(p => ({
                        ...p,
                        selected: true,
                        old_purchase_price: parseFloat(p.base_purchase_price),
                        old_selling_price: parseFloat(p.base_selling_price),
                        old_profit: parseFloat(p.base_selling_price) - parseFloat(p.base_purchase_price),
                        old_profit_percentage: ((parseFloat(p.base_selling_price) - parseFloat(p.base_purchase_price)) / parseFloat(p.base_purchase_price)) * 100,
                        new_purchase_price: 0,
                        new_selling_price: 0,
                        new_profit: 0,
                        new_profit_percentage: 0
                    }));
                    
                    console.log('✅ Products loaded:', this.products.length);
                    
                    if (this.products.length > 0) {
                        this.newPricing.purchase_price = this.products[0].old_purchase_price;
                        this.newPricing.profit_value = this.products[0].old_profit;
                        this.newPricing.profit_type = 'fixed';
                    }
                    
                    this.calculatePreview();
                } else {
                    alert('❌ ' + data.message);
                }
            } catch (error) {
                console.error('❌ Error loading products:', error);
                alert('❌ حدث خطأ أثناء تحميل المنتجات');
            } finally {
                this.loadingProducts = false;
            }
        },
        
        calculatePreview() {
            const purchasePrice = parseFloat(this.newPricing.purchase_price) || 0;
            const profitValue = parseFloat(this.newPricing.profit_value) || 0;
            
            this.products = this.products.map(product => {
                let newProfit = 0;
                
                if (this.newPricing.profit_type === 'percentage') {
                    newProfit = (purchasePrice * profitValue) / 100;
                } else {
                    newProfit = profitValue;
                }
                
                const newSellingPrice = purchasePrice + newProfit;
                const newProfitPercentage = purchasePrice > 0 ? (newProfit / purchasePrice) * 100 : 0;
                
                return {
                    ...product,
                    new_purchase_price: purchasePrice,
                    new_selling_price: newSellingPrice,
                    new_profit: newProfit,
                    new_profit_percentage: newProfitPercentage
                };
            });
        },
        
        selectAll() {
            this.products = this.products.map(p => ({ ...p, selected: true }));
        },
        
        deselectAll() {
            this.products = this.products.map(p => ({ ...p, selected: false }));
        },
        
        toggleAllProducts(checked) {
            this.products = this.products.map(p => ({ ...p, selected: checked }));
        },
        
        get selectedProducts() {
            return this.products.filter(p => p.selected);
        },
        
        get totalOldValue() {
            return this.selectedProducts.reduce((sum, p) => sum + p.old_selling_price, 0);
        },
        
        get totalNewValue() {
            return this.selectedProducts.reduce((sum, p) => sum + p.new_selling_price, 0);
        },
        
        async submitUpdate() {
            if (this.selectedProducts.length === 0) {
                alert('⚠️ يجب تحديد منتج واحد على الأقل');
                return;
            }
            
            if (!this.newPricing.purchase_price || !this.newPricing.profit_value) {
                alert('⚠️ يجب إدخال سعر الشراء وهامش الربح');
                return;
            }
            
            const confirmation = confirm(
                `🔄 هل أنت متأكد من تحديث ${this.selectedProducts.length} منتج؟\n\n` +
                `📦 الوحدة: ${this.filters.base_unit}\n` +
                `📂 التصنيف: ${this.filters.category}\n` +
                `💰 سعر الشراء: ${this.formatPrice(this.newPricing.purchase_price)}\n` +
                `📊 هامش الربح: ${this.newPricing.profit_value} ${this.newPricing.profit_type === 'percentage' ? '%' : 'ج.م'}\n\n` +
                `⚠️ التغييرات ستطبق فوراً!`
            );
            
            if (!confirmation) return;
            
            this.submitting = true;
            
            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('base_unit', this.filters.base_unit);
                formData.append('category', this.filters.category);
                formData.append('base_purchase_price', this.newPricing.purchase_price);
                formData.append('profit_type', this.newPricing.profit_type);
                formData.append('profit_value', this.newPricing.profit_value);
                formData.append('change_reason', this.changeReason);
                formData.append('selected_products', JSON.stringify(this.selectedProducts.map(p => p.id)));
                
                const response = await fetch('{{ route("products.bulk-price-update.apply") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✅ ' + data.message);
                    window.location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            } catch (error) {
                console.error('❌ Error submitting update:', error);
                alert('❌ حدث خطأ أثناء التحديث');
            } finally {
                this.submitting = false;
            }
        },
        
        resetForm() {
            if (confirm('🔄 هل تريد إعادة تعيين النموذج؟')) {
                this.filters = { base_unit: '', category: '' };
                this.categories = [];
                this.products = [];
                this.newPricing = { purchase_price: 0, profit_type: 'fixed', profit_value: 0 };
                this.changeReason = '';
            }
        },
        
        formatPrice(value) {
            return new Intl.NumberFormat('ar-EG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0) + ' ج.م';
        }
    }
}
</script>
@endpush

@endsection