{{-- 
=====================================================================
🔧 الـ Blade المُحدث بالكامل - bulk-price-update.blade.php
=====================================================================
✅ دعم كامل لـ base_unit_id
✅ دعم is_base للوحدات الأساسية
✅ تكامل مع Observer للتحديث التلقائي
✅ واجهة محسّنة مع رسائل تفصيلية
=====================================================================
--}}

@extends('layouts.app')

@section('title', 'التحديث الذكي للأسعار')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-8" x-data="smartPriceUpdate()">

    {{-- 🎨 Success Alert --}}
    @if(session('success'))
        <div class="mb-6 bg-gradient-to-r from-green-100 to-emerald-100 border-r-4 border-green-500 p-6 rounded-xl shadow-lg">
            <div class="flex items-start gap-4">
                <div class="bg-green-500 rounded-full p-2">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-green-800 text-lg mb-2">✅ نجح التحديث</p>
                    <p class="text-green-700 whitespace-pre-line">{!! nl2br(e(session('success'))) !!}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- 🎨 Error Alert --}}
    @if(session('error'))
        <div class="mb-6 bg-gradient-to-r from-red-100 to-pink-100 border-r-4 border-red-500 p-6 rounded-xl shadow-lg">
            <div class="flex items-start gap-4">
                <div class="bg-red-500 rounded-full p-2">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-red-800 text-lg mb-2">❌ حدث خطأ</p>
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- 🎨 Header --}}
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl p-8 mb-8 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-4xl font-black mb-3 flex items-center gap-3">
                    <span class="animate-pulse">🎯</span>
                    التحديث الذكي للأسعار
                </h1>
                <p class="text-blue-50 text-lg font-medium">
                    💡 حدّث أسعار منتجات محددة بدقة عالية حسب الوحدة والتصنيف
                </p>
                <p class="text-blue-100 text-sm mt-2">
                    ⚡ التحديث التلقائي لوحدات البيع • 📊 حفظ السجل التاريخي • 🔒 تتبع المستخدمين
                </p>
            </div>
            <a href="{{ route('products.index') }}" 
               class="bg-white text-blue-600 px-6 py-3 rounded-xl hover:bg-blue-50 hover:scale-105 transition-all font-bold shadow-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                </svg>
                رجوع للمنتجات
            </a>
        </div>
    </div>

    <form @submit.prevent="submitUpdate">
        {{-- 📍 Step 1: Filters --}}
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6 border-2 border-gray-100">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b-2 border-blue-200 p-6">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                    <span class="bg-blue-600 text-white w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold shadow-lg">1</span>
                    <span>🔍 اختر الوحدة والتصنيف</span>
                </h2>
                <p class="text-gray-600 mt-2 mr-14">حدد الوحدة الأساسية والتصنيف للبحث عن المنتجات</p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    {{-- ✅ Unit Selection --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            الوحدة الأساسية 
                            <span class="text-red-500">*</span>
                        </label>
                        <select x-model="filters.base_unit" 
                                @change="loadCategories()"
                                class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-lg font-semibold bg-white shadow-sm hover:border-blue-400"
                                required>
                            <option value="">-- اختر الوحدة الأساسية --</option>
                            
                            {{-- ✅ طريقة 1: unitsByCategory (مفضّلة) --}}
                            @if(isset($unitsByCategory) && !empty($unitsByCategory))
                                @foreach($unitsByCategory as $categoryKey => $categoryData)
                                    <optgroup label="{{ $categoryData['label'] ?? $categoryKey }}">
                                        @foreach($categoryData['units'] as $unitCode => $unitLabel)
                                            <option value="{{ $unitCode }}">{{ $unitLabel }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            
                            {{-- ✅ طريقة 2: activeUnits (Fallback 1) --}}
                            @elseif(isset($activeUnits) && !empty($activeUnits))
                                @foreach($activeUnits as $unitCode => $unitLabel)
                                    <option value="{{ $unitCode }}">{{ $unitLabel }}</option>
                                @endforeach
                            
                            {{-- ✅ طريقة 3: mostUsedUnits (Fallback 2) --}}
                            @elseif(isset($mostUsedUnits) && !empty($mostUsedUnits))
                                @foreach($mostUsedUnits as $unit)
                                    <option value="{{ $unit['code'] }}">{{ $unit['label'] }}</option>
                                @endforeach
                            
                            {{-- ✅ طريقة 4: وحدات افتراضية (Last Fallback) --}}
                            @else
                                <optgroup label="⚖️ وحدات الوزن">
                                    <option value="TON">طن (TON)</option>
                                    <option value="KG">كيلوجرام (KG)</option>
                                    <option value="GM">جرام (GM)</option>
                                </optgroup>
                                <optgroup label="📏 وحدات الحجم">
                                    <option value="LTR">لتر (LTR)</option>
                                    <option value="ML">مليلتر (ML)</option>
                                    <option value="M3">متر مكعب (M3)</option>
                                </optgroup>
                                <optgroup label="🔢 وحدات العدد">
                                    <option value="UNIT">وحدة (UNIT)</option>
                                    <option value="PIECE">قطعة (PIECE)</option>
                                    <option value="BOX">صندوق (BOX)</option>
                                    <option value="CARTON">كرتونة (CARTON)</option>
                                    <option value="BAG">شيكارة (BAG)</option>
                                </optgroup>
                            @endif
                        </select>
                        
                        {{-- 💡 رسالة توضيحية --}}
                        <div class="mt-3 flex items-start gap-2 bg-blue-50 p-3 rounded-lg border border-blue-200">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm text-blue-700">
                                اختر الوحدة الأساسية التي تريد تحديث أسعار المنتجات المسجلة بها
                            </p>
                        </div>
                    </div>

                    {{-- ✅ Category Selection --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            التصنيف 
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select x-model="filters.category" 
                                    @change="onCategoryChange()"
                                    class="w-full px-5 py-4 border-2 rounded-xl focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all text-lg font-semibold bg-white shadow-sm"
                                    :class="{ 
                                        'border-gray-300 hover:border-green-400': filters.base_unit && !loadingCategories, 
                                        'border-gray-200 bg-gray-50 cursor-not-allowed': !filters.base_unit || loadingCategories 
                                    }"
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
                            
                            {{-- Loading Spinner --}}
                            <div x-show="loadingCategories" 
                                 class="absolute left-4 top-1/2 -translate-y-1/2">
                                <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        
                        {{-- 📊 رسائل الحالة المُحسّنة --}}
                        <div class="mt-3 space-y-2">
                            <div x-show="!filters.base_unit" 
                                 class="flex items-start gap-2 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <svg class="w-5 h-5 text-gray-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="text-sm text-gray-600">
                                    اختر الوحدة الأساسية أولاً لعرض التصنيفات المتاحة
                                </p>
                            </div>
                            
                            <div x-show="loadingCategories" 
                                 x-transition
                                 class="flex items-start gap-2 bg-blue-50 p-3 rounded-lg border border-blue-200 animate-pulse">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <p class="text-sm text-blue-700 font-medium">
                                    جاري تحميل التصنيفات من قاعدة البيانات...
                                </p>
                            </div>
                            
                            <div x-show="categories.length > 0 && !loadingCategories && filters.base_unit" 
                                 x-transition
                                 class="flex items-start gap-2 bg-green-50 p-3 rounded-lg border border-green-200">
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm text-green-700 font-medium">
                                    تم تحميل <span class="font-bold" x-text="categories.length"></span> تصنيف بنجاح
                                </p>
                            </div>
                            
                            <div x-show="categories.length === 0 && !loadingCategories && filters.base_unit" 
                                 x-transition
                                 class="flex items-start gap-2 bg-orange-50 p-3 rounded-lg border border-orange-200">
                                <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm text-orange-700">
                                    لا توجد تصنيفات مسجلة لهذه الوحدة في قاعدة البيانات
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 🔍 Load Products Button --}}
                <div class="mt-8" x-show="filters.base_unit && filters.category" x-transition>
                    <button type="button" 
                            @click="loadProducts()"
                            :disabled="loadingProducts"
                            class="w-full px-6 py-5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:shadow-2xl hover:scale-[1.02] transition-all font-bold text-lg flex items-center justify-center gap-3 shadow-lg"
                            :class="loadingProducts ? 'opacity-50 cursor-not-allowed' : 'hover:from-green-700 hover:to-emerald-700'">
                        <svg class="w-7 h-7" :class="loadingProducts ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span x-text="loadingProducts ? '⏳ جاري البحث عن المنتجات...' : '🔍 عرض المنتجات المتاحة'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- 📍 Step 2: Products Table with Preview --}}
        <div x-show="products.length > 0" 
             x-transition
             class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6 border-2 border-gray-100">
            
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-b-2 border-green-200 p-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                            <span class="bg-green-600 text-white w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold shadow-lg">2</span>
                            <span>📦 المنتجات المتاحة</span>
                            <span class="text-sm bg-green-100 text-green-700 px-4 py-1.5 rounded-full font-semibold" x-text="'(' + products.length + ' منتج)'"></span>
                        </h2>
                        <p class="text-gray-600 mt-2 mr-14">حدد المنتجات المراد تحديث أسعارها</p>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="button" 
                                @click="selectAll()"
                                class="px-5 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 hover:scale-105 transition text-sm font-bold shadow-md flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            تحديد الكل
                        </button>
                        <button type="button" 
                                @click="deselectAll()"
                                class="px-5 py-2.5 bg-gray-500 text-white rounded-lg hover:bg-gray-600 hover:scale-105 transition text-sm font-bold shadow-md flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            إلغاء التحديد
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-6">
                {{-- 💰 Pricing Inputs --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 bg-gradient-to-r from-purple-50 via-pink-50 to-rose-50 p-8 rounded-2xl border-2 border-purple-200 shadow-lg">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            سعر الشراء الجديد
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   x-model="newPricing.purchase_price"
                                   @input="calculatePreview()"
                                   step="0.01" 
                                   min="0"
                                   class="w-full px-5 py-4 pr-16 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all text-lg font-bold shadow-sm"
                                   placeholder="0.00"
                                   required>
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-lg">ج.م</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            نوع هامش الربح
                        </label>
                        <select x-model="newPricing.profit_type"
                                @change="calculatePreview()"
                                class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-lg font-bold bg-white shadow-sm"
                                required>
                            <option value="fixed">💰 مبلغ ثابت (ج.م)</option>
                            <option value="percentage">📊 نسبة مئوية (%)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            قيمة هامش الربح
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   x-model="newPricing.profit_value"
                                   @input="calculatePreview()"
                                   step="0.01" 
                                   min="0"
                                   class="w-full px-5 py-4 pr-16 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all text-lg font-bold shadow-sm"
                                   placeholder="0.00"
                                   required>
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-lg" x-text="newPricing.profit_type === 'percentage' ? '%' : 'ج.م'"></span>
                        </div>
                    </div>
                </div>

                {{-- 📊 Products Table --}}
                <div class="overflow-x-auto rounded-xl border-2 border-gray-200 shadow-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-4 py-4 text-right text-sm font-bold text-gray-700 w-12">
                                    <input type="checkbox" 
                                           @change="toggleAllProducts($event.target.checked)"
                                           class="w-5 h-5 text-blue-600 border-2 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                </th>
                                <th class="px-6 py-4 text-right text-sm font-bold text-gray-700 whitespace-nowrap">اسم المنتج</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 whitespace-nowrap">الوحدة</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-red-50 whitespace-nowrap">السعر القديم</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-green-50 whitespace-nowrap">السعر الجديد</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-yellow-50 whitespace-nowrap">هامش الربح القديم</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 bg-blue-50 whitespace-nowrap">هامش الربح الجديد</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 whitespace-nowrap">التغيير</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="product in products" :key="product.id">
                                <tr class="transition-all duration-200 hover:shadow-md"
                                    :class="product.selected ? 'bg-blue-50 border-l-4 border-blue-500' : 'hover:bg-gray-50'">
                                    <td class="px-4 py-5">
                                        <input type="checkbox" 
                                               x-model="product.selected"
                                               class="w-5 h-5 text-blue-600 border-2 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="font-bold text-gray-900 text-base" x-text="product.name"></div>
                                        <div class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                            <span x-text="product.category"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-purple-100 text-purple-800 border border-purple-200"
                                              x-text="product.base_unit_label"></span>
                                    </td>
                                    
                                    {{-- Old Prices --}}
                                    <td class="px-6 py-5 text-center bg-red-50">
                                        <div class="font-bold text-red-700 text-base" x-text="formatPrice(product.old_purchase_price)"></div>
                                        <div class="text-sm text-red-600 mt-1 flex items-center justify-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                            </svg>
                                            <span x-text="formatPrice(product.old_selling_price)"></span>
                                        </div>
                                    </td>
                                    
                                    {{-- New Prices --}}
                                    <td class="px-6 py-5 text-center bg-green-50">
                                        <div class="font-bold text-green-700 text-base" x-text="formatPrice(product.new_purchase_price)"></div>
                                        <div class="text-sm text-green-600 mt-1 flex items-center justify-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                            </svg>
                                            <span x-text="formatPrice(product.new_selling_price)"></span>
                                        </div>
                                    </td>
                                    
                                    {{-- Old Profit --}}
                                    <td class="px-6 py-5 text-center bg-yellow-50">
                                        <div class="font-bold text-yellow-700 text-base" x-text="formatPrice(product.old_profit)"></div>
                                        <div class="text-sm text-yellow-600 mt-1" x-text="product.old_profit_percentage.toFixed(1) + '%'"></div>
                                    </td>
                                    
                                    {{-- New Profit --}}
                                    <td class="px-6 py-5 text-center bg-blue-50">
                                        <div class="font-bold text-blue-700 text-base" x-text="formatPrice(product.new_profit)"></div>
                                        <div class="text-sm text-blue-600 mt-1" x-text="product.new_profit_percentage.toFixed(1) + '%'"></div>
                                    </td>
                                    
                                    {{-- Change Indicator --}}
                                    <td class="px-6 py-5 text-center">
                                        <template x-if="product.new_selling_price > product.old_selling_price">
                                            <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-bold bg-green-100 text-green-800 border border-green-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                                </svg>
                                                <span x-text="formatPrice(product.new_selling_price - product.old_selling_price)"></span>
                                            </span>
                                        </template>
                                        <template x-if="product.new_selling_price < product.old_selling_price">
                                            <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-bold bg-red-100 text-red-800 border border-red-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                                </svg>
                                                <span x-text="formatPrice(product.old_selling_price - product.new_selling_price)"></span>
                                            </span>
                                        </template>
                                        <template x-if="product.new_selling_price === product.old_selling_price">
                                            <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"></path>
                                                </svg>
                                                بدون تغيير
                                            </span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- 📊 Summary Statistics --}}
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border-2 border-blue-200 shadow-lg hover:scale-105 transition-transform">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm text-blue-600 font-bold">المنتجات المحددة</p>
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <p class="text-4xl font-black text-blue-700" x-text="selectedProducts.length"></p>
                        <p class="text-xs text-blue-500 mt-2">من إجمالي <span x-text="products.length"></span> منتج</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border-2 border-green-200 shadow-lg hover:scale-105 transition-transform">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm text-green-600 font-bold">إجمالي القيمة القديمة</p>
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-3xl font-black text-green-700" x-text="formatPrice(totalOldValue)"></p>
                        <p class="text-xs text-green-500 mt-2">للمنتجات المحددة</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 border-2 border-purple-200 shadow-lg hover:scale-105 transition-transform">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm text-purple-600 font-bold">إجمالي القيمة الجديدة</p>
                            <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <p class="text-3xl font-black text-purple-700" x-text="formatPrice(totalNewValue)"></p>
                        <p class="text-xs text-purple-500 mt-2">بعد التحديث</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl p-6 border-2 border-orange-200 shadow-lg hover:scale-105 transition-transform">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm text-orange-600 font-bold">الفرق الإجمالي</p>
                            <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                        </div>
                        <p class="text-3xl font-black" 
                           :class="totalNewValue > totalOldValue ? 'text-green-700' : totalNewValue < totalOldValue ? 'text-red-700' : 'text-gray-700'"
                           x-text="formatPrice(Math.abs(totalNewValue - totalOldValue))"></p>
                        <p class="text-xs mt-2" 
                           :class="totalNewValue > totalOldValue ? 'text-green-600' : totalNewValue < totalOldValue ? 'text-red-600' : 'text-gray-600'"
                           x-text="totalNewValue > totalOldValue ? 'زيادة ↑' : totalNewValue < totalOldValue ? 'انخفاض ↓' : 'بدون تغيير'"></p>
                    </div>
                </div>

                {{-- 📝 Change Reason --}}
                <div class="mt-8 bg-gray-50 p-6 rounded-xl border-2 border-gray-200">
                    <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        سبب التغيير (اختياري)
                    </label>
                    <input type="text" 
                           x-model="changeReason"
                           class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-base shadow-sm"
                           placeholder="مثال: تحديث الأسعار حسب السوق، تغيير سعر الموّرد، عروض خاصة..."
                           maxlength="500">
                    <p class="text-xs text-gray-500 mt-2">سيتم حفظ هذا السبب في سجل تغييرات الأسعار</p>
                </div>
            </div>
        </div>

        {{-- 💾 Submit Button --}}
        <div x-show="selectedProducts.length > 0" 
             x-transition
             class="bg-white rounded-2xl shadow-2xl p-8 sticky bottom-4 border-2 border-gray-200">
            <div class="flex items-center justify-between flex-wrap gap-6">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-br from-orange-100 to-orange-200 rounded-2xl p-4 shadow-lg">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            تحديث ذكي انتقائي
                        </p>
                        <p class="text-gray-800 font-black text-lg">
                            سيتم تحديث <span class="text-blue-600" x-text="selectedProducts.length"></span> منتج فقط
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            ✓ الوحدة الأساسية + وحدات البيع + السجل التاريخي
                        </p>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <button type="button" 
                            @click="resetForm()"
                            class="px-8 py-4 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 hover:scale-105 transition-all font-bold flex items-center gap-2 shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        إعادة تعيين
                    </button>
                    <button type="submit" 
                            :disabled="submitting"
                            class="px-12 py-4 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 text-white rounded-xl hover:shadow-2xl hover:scale-105 transition-all font-black text-xl flex items-center gap-3 shadow-xl"
                            :class="submitting ? 'opacity-50 cursor-not-allowed' : ''">
                        <svg class="w-7 h-7" :class="submitting ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            console.log('✅ Smart Price Update initialized - v2.0');
            console.log('🔍 Features:', {
                baseSellingUnits: true,
                autoObserver: true,
                priceHistory: true,
                multiUnits: true
            });
            console.log('📊 Available data:', {
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
                console.log('📦 Response:', data);
                
                if (data.success) {
                    this.categories = data.categories || [];
                    console.log('✅ Categories loaded:', this.categories.length);
                    
                    if (this.categories.length === 0) {
                        this.showNotification('⚠️ لا توجد منتجات مخزنة بهذه الوحدة\n\nالوحدة: ' + this.filters.base_unit, 'warning');
                    }
                } else {
                    this.categories = [];
                    console.warn('⚠️ No categories:', data.message);
                    this.showNotification(`⚠️ ${data.message}\n\nالوحدة: ${this.filters.base_unit}`, 'warning');
                }
            } catch (error) {
                console.error('❌ Error loading categories:', error);
                this.showNotification('❌ حدث خطأ أثناء تحميل التصنيفات\n\nالخطأ: ' + error.message, 'error');
                this.categories = [];
            } finally {
                this.loadingCategories = false;
            }
        },
        
        onCategoryChange() {
            console.log('📂 Category changed to:', this.filters.category);
            this.products = [];
        },
        
        async loadProducts() {
            if (!this.filters.base_unit || !this.filters.category) {
                this.showNotification('⚠️ يجب اختيار الوحدة والتصنيف أولاً', 'warning');
                return;
            }
            
            this.loadingProducts = true;
            this.products = [];
            
            console.log('🔄 Loading products:', {
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
                        // تعيين القيم الافتراضية من أول منتج
                        this.newPricing.purchase_price = this.products[0].old_purchase_price;
                        this.newPricing.profit_value = this.products[0].old_profit;
                        this.newPricing.profit_type = 'fixed';
                    }
                    
                    this.calculatePreview();
                } else {
                    this.showNotification('❌ ' + data.message, 'error');
                }
            } catch (error) {
                console.error('❌ Error loading products:', error);
                this.showNotification('❌ حدث خطأ أثناء تحميل المنتجات\n\nالخطأ: ' + error.message, 'error');
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
            // Validation
            if (this.selectedProducts.length === 0) {
                this.showNotification('⚠️ يجب تحديد منتج واحد على الأقل', 'warning');
                return;
            }
            
            if (!this.newPricing.purchase_price || this.newPricing.purchase_price <= 0) {
                this.showNotification('⚠️ يجب إدخال سعر شراء صحيح أكبر من صفر', 'warning');
                return;
            }
            
            if (this.newPricing.profit_value === '' || this.newPricing.profit_value < 0) {
                this.showNotification('⚠️ يجب إدخال هامش ربح صحيح (صفر أو أكثر)', 'warning');
                return;
            }
            
            // Calculate new selling price
            let newProfit = 0;
            if (this.newPricing.profit_type === 'percentage') {
                newProfit = (this.newPricing.purchase_price * this.newPricing.profit_value) / 100;
            } else {
                newProfit = this.newPricing.profit_value;
            }
            const newSellingPrice = this.newPricing.purchase_price + newProfit;
            
            // Confirmation
            const confirmation = confirm(
                `🔄 تأكيد التحديث الجماعي\n\n` +
                `━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n` +
                `📊 البيانات:\n` +
                `• عدد المنتجات: ${this.selectedProducts.length}\n` +
                `• الوحدة: ${this.filters.base_unit}\n` +
                `• التصنيف: ${this.filters.category}\n\n` +
                `💰 الأسعار الجديدة:\n` +
                `• سعر الشراء: ${this.formatPrice(this.newPricing.purchase_price)}\n` +
                `• هامش الربح: ${this.newPricing.profit_value} ${this.newPricing.profit_type === 'percentage' ? '%' : 'ج.م'}\n` +
                `• سعر البيع: ${this.formatPrice(newSellingPrice)}\n\n` +
                `━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n` +
                `⚡ سيتم تلقائياً:\n` +
                `✓ تحديث الوحدة الأساسية\n` +
                `✓ تحديث وحدات البيع (عبر Observer)\n` +
                `✓ حفظ السجل التاريخي\n` +
                `✓ تتبع المستخدم والتاريخ\n\n` +
                `⚠️ هذا الإجراء لا يمكن التراجع عنه!\n\n` +
                `هل أنت متأكد من المتابعة؟`
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
                formData.append('change_reason', this.changeReason || 'تحديث جماعي للأسعار');
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
                    const successMessage = 
                        `✅ ${data.message}\n\n` +
                        `━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n` +
                        `📊 التفاصيل:\n` +
                        `• المنتجات المحدثة: ${data.data.updated_count}\n` +
                        `• وقت التنفيذ: ${data.data.execution_time} ثانية\n\n` +
                        `💰 الأسعار الجديدة:\n` +
                        `• سعر الشراء: ${data.data.new_prices.purchase} ج.م\n` +
                        `• سعر البيع: ${data.data.new_prices.selling} ج.م\n` +
                        `• هامش الربح: ${data.data.new_prices.profit} ج.م\n` +
                        `• نسبة الربح: ${data.data.new_prices.profit_percentage}%\n\n` +
                        `━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n` +
                        `سيتم تحديث الصفحة خلال ثانية واحدة...`;
                    
                    alert(successMessage);
                    
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification('❌ فشل التحديث\n\n' + data.message, 'error');
                }
            } catch (error) {
                console.error('❌ Error:', error);
                this.showNotification('❌ حدث خطأ أثناء الاتصال بالخادم\n\nالخطأ: ' + error.message, 'error');
            } finally {
                this.submitting = false;
            }
        },
        
        resetForm() {
            if (confirm('🔄 هل تريد إعادة تعيين النموذج وحذف جميع البيانات المدخلة؟')) {
                this.filters = { base_unit: '', category: '' };
                this.categories = [];
                this.products = [];
                this.newPricing = { purchase_price: 0, profit_type: 'fixed', profit_value: 0 };
                this.changeReason = '';
                console.log('🔄 Form reset');
            }
        },
        
        formatPrice(value) {
            return new Intl.NumberFormat('ar-EG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0) + ' ج.م';
        },
        
        showNotification(message, type = 'info') {
            // يمكن استبدالها بـ toast notification library
            alert(message);
        }
    }
}
</script>
@endpush

@endsection