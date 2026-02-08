@extends('layouts.app')

@section('title', 'إضافة منتج جديد')
@section('page-title', 'إضافة منتج جديد')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="productCreateApp()">
    
    {{-- ====== Header Section ====== --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    إضافة منتج جديد
                </h1>
                <p class="mt-2 text-gray-600">أدخل بيانات المنتج بدقة لضمان سير العمل بشكل صحيح</p>
            </div>
            <a href="{{ route('products.index') }}" 
               class="px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 font-semibold flex items-center gap-2 shadow-sm hover:shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                رجوع للقائمة
            </a>
        </div>
    </div>

    {{-- ====== Alert Messages ====== --}}
    @if(session('success'))
        <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-5 rounded-xl shadow-sm animate-slideInDown">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-green-800">{{ session('success') }}</p>
                </div>
                <button type="button" class="text-green-400 hover:text-green-600" onclick="this.parentElement.parentElement.remove()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 p-5 rounded-xl shadow-sm animate-slideInDown">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-red-800">{{ session('error') }}</p>
                </div>
                <button type="button" class="text-red-400 hover:text-red-600" onclick="this.parentElement.parentElement.remove()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 p-5 rounded-xl shadow-sm animate-shake">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-red-800 mb-3 text-lg">يوجد أخطاء في النموذج:</p>
                    <ul class="space-y-2">
                        @foreach($errors->all() as $error)
                            <li class="flex items-start gap-2 text-red-700">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="validateAndSubmit">
        @csrf
        
        {{-- ====== 1. المعلومات الأساسية ====== --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-6 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-8 pb-4 border-b-2 border-gray-100">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    المعلومات الأساسية
                </h3>
                <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-full text-sm font-semibold">الخطوة 1 من 4</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- اسم المنتج --}}
                <div class="md:col-span-2">
                    <label class="block text-gray-800 font-bold mb-3 text-lg">
                        اسم المنتج 
                        <span class="text-red-500 text-xl">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           value="{{ old('name') }}" 
                           class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 @error('name') border-red-500 bg-red-50 @enderror text-lg" 
                           placeholder="مثال: أسمنت بورتلاند أبيض" 
                           required>
                    @error('name')
                        <p class="text-red-600 text-sm mt-2 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- SKU --}}
                <div>
                    <label class="block text-gray-800 font-bold mb-3">
                        كود المنتج (SKU)
                        <span class="text-xs text-gray-500 font-normal ml-1">(يتم توليده تلقائياً)</span>
                    </label>
                    <input type="text" 
                           name="sku" 
                           value="{{ old('sku') }}" 
                           class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 bg-gray-50 @error('sku') border-red-500 @enderror" 
                           placeholder="اتركه فارغاً للتوليد التلقائي">
                    @error('sku')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- الباركود --}}
                <div>
                    <label class="block text-gray-800 font-bold mb-3">
                        الباركود
                        <span class="text-xs text-gray-500 font-normal">(اختياري)</span>
                    </label>
                    <input type="text" 
                           name="barcode" 
                           value="{{ old('barcode') }}" 
                           class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 @error('barcode') border-red-500 @enderror" 
                           placeholder="1234567890123">
                    @error('barcode')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- التصنيف --}}
                <div>
                    <label class="block text-gray-800 font-bold mb-3">
                        التصنيف 
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="category" 
                           value="{{ old('category') }}" 
                           x-model="category"
                           @input="loadPricingSuggestions()"
                           class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 @error('category') border-red-500 @enderror" 
                           placeholder="مثال: أسمنت، حديد، رمل"
                           required>
                    @error('category')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- الوحدة الأساسية --}}
                <div>
                    <label class="block text-gray-800 font-bold mb-3">
                        الوحدة الأساسية 
                        <span class="text-red-500">*</span>
                    </label>
                    <select name="base_unit" 
                            x-model="baseUnit"
                            @change="updateBaseUnitLabel(); loadPricingSuggestions()"
                            class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 @error('base_unit') border-red-500 @enderror appearance-none bg-white cursor-pointer" 
                            required>
                        <option value="">-- اختر الوحدة الأساسية --</option>
                        
                        @if(isset($unitsByCategory))
                            @foreach($unitsByCategory as $categoryKey => $categoryData)
                                <optgroup label="{{ $categoryData['label'] }}" class="font-bold">
                                    @foreach($categoryData['units'] as $unitCode => $unitLabel)
                                        <option value="{{ $unitCode }}" {{ old('base_unit') == $unitCode ? 'selected' : '' }}>
                                            {{ $unitLabel }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        @endif
                    </select>
                    @error('base_unit')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- الوصف --}}
                <div class="md:col-span-2">
                    <label class="block text-gray-800 font-bold mb-3">
                        الوصف
                        <span class="text-xs text-gray-500 font-normal">(اختياري)</span>
                    </label>
                    <textarea name="description" 
                              rows="4" 
                              class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 resize-none" 
                              placeholder="وصف تفصيلي للمنتج...">{{ old('description') }}</textarea>
                </div>
            </div>

            {{-- Hidden field للوحدة --}}
            <input type="hidden" name="base_unit_label" :value="baseUnitLabel || ''">
        </div>

        {{-- ====== 2. التسعير الذكي ====== --}}
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl shadow-lg border-2 border-green-200 p-8 mb-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-8 pb-4 border-b-2 border-green-200">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    التسعير الذكي
                    <span class="text-sm bg-white/80 text-green-700 px-3 py-1.5 rounded-full font-semibold border border-green-300" x-show="baseUnit" x-text="'الوحدة: ' + baseUnitLabel"></span>
                </h3>
                <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold">الخطوة 2 من 4</span>
            </div>

            {{-- اقتراحات ذكية --}}
            <div x-show="suggestions" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 class="mb-8 bg-white rounded-xl border-2 border-blue-300 p-6 shadow-md">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-blue-900 mb-4 text-lg">
                            💡 اقتراحات ذكية بناءً على <span class="text-blue-600" x-text="suggestions?.sample_size || 0"></span> منتج مشابه
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-5 rounded-xl border-2 border-green-200">
                                <p class="text-gray-700 font-semibold mb-2 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                    سعر الشراء المقترح:
                                </p>
                                <p class="font-black text-3xl text-green-600" x-text="formatPrice(suggestions?.suggested_purchase_price)"></p>
                            </div>
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-5 rounded-xl border-2 border-blue-200">
                                <p class="text-gray-700 font-semibold mb-2 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                    سعر البيع المقترح:
                                </p>
                                <p class="font-black text-3xl text-blue-600" x-text="formatPrice(suggestions?.suggested_selling_price)"></p>
                            </div>
                        </div>
                        <button type="button" 
                                @click="applySuggestions()"
                                class="mt-5 w-full px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 font-bold text-lg flex items-center justify-center gap-3 shadow-lg hover:shadow-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            تطبيق الاقتراحات الذكية
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                {{-- سعر الشراء --}}
                <div>
                    <label class="block text-gray-800 font-bold mb-3 text-lg">
                        سعر الشراء 
                        <span class="text-red-500 text-xl">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               name="purchase_price" 
                               x-model.number="purchasePrice"
                               @input="calculatePrices()"
                               step="0.01" 
                               min="0"
                               class="w-full px-5 py-4 pl-16 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-200 @error('purchase_price') border-red-500 @enderror text-lg font-semibold" 
                               placeholder="0.00"
                               required>
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-lg">ج.م</span>
                    </div>
                    @error('purchase_price')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- سعر البيع --}}
                <div>
                    <label class="block text-gray-800 font-bold mb-3 text-lg">
                        سعر البيع 
                        <span class="text-red-500 text-xl">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               name="selling_price" 
                               x-model.number="sellingPrice"
                               @input="calculateProfitFromSelling()"
                               step="0.01" 
                               min="0"
                               class="w-full px-5 py-4 pl-16 border-2 border-green-400 rounded-xl focus:ring-4 focus:ring-green-500/20 focus:border-green-500 bg-green-50 transition-all duration-200 @error('selling_price') border-red-500 @enderror text-lg font-bold" 
                               placeholder="0.00"
                               required>
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-lg">ج.م</span>
                    </div>
                    @error('selling_price')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-green-600 mt-2 font-semibold">✏️ يمكنك تعديله مباشرة</p>
                </div>

                {{-- هامش الربح --}}
                <div>
                    <label class="block text-gray-800 font-bold mb-3 text-lg">
                        هامش الربح
                        <span class="text-xs text-gray-500 font-normal">(تلقائي)</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               :value="formatPrice(profit)"
                               class="w-full px-5 py-4 border-2 border-blue-400 rounded-xl bg-blue-50 cursor-not-allowed text-lg font-bold text-blue-600" 
                               readonly>
                    </div>
                    <p class="text-sm text-blue-600 mt-2 font-semibold" x-show="profitPercentage > 0">
                        📊 نسبة الربح: <span class="font-black" x-text="profitPercentage.toFixed(1) + '%'"></span>
                    </p>
                </div>
            </div>

            {{-- أسعار إضافية --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-3">
                        الحد الأدنى لسعر البيع
                        <span class="text-xs text-gray-500 font-normal">(اختياري)</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               name="min_selling_price" 
                               value="{{ old('min_selling_price') }}" 
                               step="0.01" 
                               min="0"
                               class="w-full px-5 py-4 pl-16 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200" 
                               placeholder="0.00">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-500 font-bold">ج.م</span>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-3">
                        سعر الجملة
                        <span class="text-xs text-gray-500 font-normal">(اختياري)</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               name="wholesale_price" 
                               value="{{ old('wholesale_price') }}" 
                               step="0.01" 
                               min="0"
                               class="w-full px-5 py-4 pl-16 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200" 
                               placeholder="0.00">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-500 font-bold">ج.م</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====== 3. الضرائب والخصومات ====== --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-6 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-8 pb-4 border-b-2 border-gray-100">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    الضرائب والخصومات
                </h3>
                <span class="px-4 py-2 bg-purple-50 text-purple-700 rounded-full text-sm font-semibold">الخطوة 3 من 4</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-3">
                        نسبة الضريبة (%)
                        <span class="text-xs text-gray-500 font-normal">(اختياري)</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               name="tax_rate" 
                               value="{{ old('tax_rate', 0) }}" 
                               step="0.01" 
                               min="0" 
                               max="100"
                               class="w-full px-5 py-4 pl-12 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-200" 
                               placeholder="0">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-500 font-bold">%</span>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-3">
                        الخصم الافتراضي (%)
                        <span class="text-xs text-gray-500 font-normal">(اختياري)</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               name="default_discount" 
                               value="{{ old('default_discount', 0) }}" 
                               step="0.01" 
                               min="0" 
                               max="100"
                               class="w-full px-5 py-4 pl-12 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-200" 
                               placeholder="0">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-500 font-bold">%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====== 4. المخزون والحالة ====== --}}
        <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl shadow-lg border-2 border-orange-200 p-8 mb-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-8 pb-4 border-b-2 border-orange-200">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    المخزون والحالة
                </h3>
                <span class="px-4 py-2 bg-orange-100 text-orange-700 rounded-full text-sm font-semibold">الخطوة 4 من 4</span>
            </div>

            {{-- معلومات المخزون --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-3">المخزن</label>
                    <select name="warehouses[0][warehouse_id]" 
                            id="warehouse_id" 
                            class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-200 appearance-none bg-white">
                        <option value="">-- لا تضيف لمخزن الآن --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouses.0.warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-3">الكمية المبدئية</label>
                    <input type="number" 
                           name="warehouses[0][quantity]" 
                           id="initial_quantity" 
                           value="{{ old('warehouses.0.quantity', 0) }}" 
                           class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-200" 
                           min="0" 
                           step="0.01"
                           placeholder="0" 
                           disabled>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-3">الحد الأدنى للتنبيه</label>
                    <input type="number" 
                           name="warehouses[0][min_stock]" 
                           id="stock_alert_quantity" 
                           value="{{ old('warehouses.0.min_stock', 10) }}" 
                           class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-200" 
                           min="0" 
                           step="0.01"
                           placeholder="10">
                </div>
            </div>

            {{-- حالة المنتج --}}
            <div class="bg-white rounded-xl p-6 border-2 border-orange-200">
                <label class="flex items-center gap-4 cursor-pointer group">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-6 h-6 text-orange-600 border-2 border-gray-300 rounded-lg focus:ring-4 focus:ring-orange-500/20 transition-all duration-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="text-gray-900 font-bold text-lg group-hover:text-orange-600 transition-colors">المنتج نشط ومتاح للبيع</span>
                            <p class="text-sm text-gray-500">سيظهر المنتج في القوائم ويمكن إضافته للفواتير</p>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        {{-- ====== أزرار التحكم ====== --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
            <div class="flex items-center gap-4 justify-between">
                <a href="{{ route('products.index') }}" 
                   class="px-8 py-4 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-bold text-lg flex items-center gap-3 shadow-md hover:shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    إلغاء
                </a>
                
                <button type="submit" 
                        class="px-12 py-4 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white rounded-xl hover:from-blue-700 hover:via-indigo-700 hover:to-purple-700 transition-all duration-200 font-bold text-lg shadow-xl hover:shadow-2xl flex items-center gap-3 transform hover:scale-105">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    حفظ المنتج
                    <svg class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </button>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    .animate-slideInDown {
        animation: slideInDown 0.5s ease-out;
    }
    
    .animate-shake {
        animation: shake 0.5s ease-in-out;
    }
</style>
@endpush

@push('scripts')
<script>
function productCreateApp() {
    return {
        baseUnit: '{{ old("base_unit", "") }}',
        baseUnitLabel: '',
        category: '{{ old("category", "") }}',
        purchasePrice: {{ old('purchase_price', 0) }},
        sellingPrice: {{ old('selling_price', 0) }},
        profit: 0,
        profitPercentage: 0,
        suggestions: null,
        
        init() {
            console.log('✅ Alpine.js initialized');
            this.updateBaseUnitLabel();
            this.calculatePrices();
            this.setupWarehouseToggle();
            
            if (this.baseUnit && this.category) {
                this.loadPricingSuggestions();
            }
        },
        
        validateAndSubmit(e) {
            console.log('🔍 Form Validation Started');
            
            // التحقق من الحقول الأساسية
            if (!this.baseUnit) {
                alert('⚠️ يجب اختيار الوحدة الأساسية');
                e.preventDefault();
                return false;
            }
            
            if (!this.category) {
                alert('⚠️ يجب إدخال التصنيف');
                e.preventDefault();
                return false;
            }
            
            if (!this.purchasePrice || this.purchasePrice <= 0) {
                alert('⚠️ يجب إدخال سعر شراء صحيح\n\nالقيمة الحالية: ' + this.purchasePrice);
                e.preventDefault();
                return false;
            }
            
            if (!this.sellingPrice || this.sellingPrice <= 0) {
                alert('⚠️ يجب إدخال سعر بيع صحيح\n\nالقيمة الحالية: ' + this.sellingPrice);
                e.preventDefault();
                return false;
            }
            
            if (this.sellingPrice < this.purchasePrice) {
                if (!confirm('⚠️ تحذير: سعر البيع أقل من سعر الشراء!\n\nهل تريد المتابعة؟')) {
                    e.preventDefault();
                    return false;
                }
            }
            
            console.log('✅ Validation passed - submitting form');
            e.target.submit();
        },
        
        updateBaseUnitLabel() {
            const select = document.querySelector('[name="base_unit"]');
            if (select && select.selectedOptions[0]) {
                this.baseUnitLabel = select.selectedOptions[0].text;
            }
        },
        
        async loadPricingSuggestions() {
            if (!this.baseUnit) {
                this.suggestions = null;
                return;
            }
            
            try {
                const url = new URL('/products/ajax/suggested-pricing', window.location.origin);
                url.searchParams.append('base_unit', this.baseUnit);
                if (this.category) {
                    url.searchParams.append('category', this.category);
                }
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success && data.suggestions) {
                    this.suggestions = data.suggestions;
                    console.log('💡 Suggestions loaded:', this.suggestions);
                } else {
                    this.suggestions = null;
                }
            } catch (error) {
                console.error('❌ Error loading suggestions:', error);
                this.suggestions = null;
            }
        },
        
        applySuggestions() {
            if (!this.suggestions) return;
            
            this.purchasePrice = this.suggestions.suggested_purchase_price;
            this.sellingPrice = this.suggestions.suggested_selling_price;
            
            this.calculatePrices();
            
            // تأثير بصري
            const inputs = document.querySelectorAll('[name="purchase_price"], [name="selling_price"]');
            inputs.forEach(input => {
                input.classList.add('ring-4', 'ring-green-400');
                setTimeout(() => {
                    input.classList.remove('ring-4', 'ring-green-400');
                }, 1500);
            });
        },
        
        calculatePrices() {
            const purchase = parseFloat(this.purchasePrice) || 0;
            const selling = parseFloat(this.sellingPrice) || 0;
            
            this.profit = selling - purchase;
            
            if (purchase > 0) {
                this.profitPercentage = (this.profit / purchase) * 100;
            } else {
                this.profitPercentage = 0;
            }
        },
        
        calculateProfitFromSelling() {
            this.calculatePrices();
        },
        
        setupWarehouseToggle() {
            const warehouseSelect = document.getElementById('warehouse_id');
            const quantityInput = document.getElementById('initial_quantity');
            
            warehouseSelect?.addEventListener('change', function() {
                const hasWarehouse = this.value !== '';
                
                quantityInput.disabled = !hasWarehouse;
                
                if (hasWarehouse) {
                    quantityInput.classList.add('ring-4', 'ring-orange-300');
                    quantityInput.focus();
                    
                    setTimeout(() => {
                        quantityInput.classList.remove('ring-4', 'ring-orange-300');
                    }, 1500);
                } else {
                    quantityInput.value = 0;
                }
            });
        },
        
        formatPrice(value) {
            return new Intl.NumberFormat('ar-EG', {
                style: 'decimal',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0) + ' ج.م';
        }
    }
}
</script>
@endpush
@endsection