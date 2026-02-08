@extends('layouts.app')

@section('title', 'تفاصيل المنتج')
@section('page-title', 'تفاصيل المنتج')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- ====== Header Section ====== --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    تفاصيل المنتج
                </h1>
                <p class="mt-2 text-gray-600">معلومات شاملة ومفصلة عن المنتج</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('products.edit', $product->id) }}" 
                   class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    تعديل المنتج
                </a>
                <a href="{{ route('products.index') }}" 
                   class="px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 rounded-xl font-semibold flex items-center gap-2 shadow-md hover:shadow-lg transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    رجوع للقائمة
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- ====== القسم الأيسر: الصورة والبطاقة ====== --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- صورة المنتج --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="aspect-square bg-gradient-to-br from-gray-50 to-gray-100 relative">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-32 h-32 mx-auto bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-16 h-16 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500 font-semibold">لا توجد صورة</p>
                            </div>
                        </div>
                    @endif
                    
                    {{-- Badge الحالة --}}
                    <div class="absolute top-4 right-4">
                        @if($product->is_active)
                            <span class="px-4 py-2 bg-green-500 text-white rounded-full text-xs font-bold shadow-lg flex items-center gap-2">
                                <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                                نشط ومتاح
                            </span>
                        @else
                            <span class="px-4 py-2 bg-red-500 text-white rounded-full text-xs font-bold shadow-lg flex items-center gap-2">
                                <div class="w-2 h-2 bg-white rounded-full"></div>
                                غير نشط
                            </span>
                        @endif
                    </div>
                </div>
                
                {{-- معلومات المنتج --}}
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $product->name }}</h2>
                    
                    <div class="space-y-3">
                        {{-- الكود --}}
                        <div class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
                            <span class="text-sm font-semibold text-blue-700">الكود</span>
                            <span class="text-sm font-bold text-blue-900 font-mono">{{ $product->code }}</span>
                        </div>
                        
                        {{-- SKU --}}
                        <div class="flex items-center justify-between p-3 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg border border-purple-100">
                            <span class="text-sm font-semibold text-purple-700">SKU</span>
                            <span class="text-sm font-bold text-purple-900 font-mono">{{ $product->sku }}</span>
                        </div>
                        
                        {{-- الباركود --}}
                        @if($product->barcode)
                        <div class="flex items-center justify-between p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-100">
                            <span class="text-sm font-semibold text-green-700">الباركود</span>
                            <span class="text-sm font-bold text-green-900 font-mono">{{ $product->barcode }}</span>
                        </div>
                        @endif
                        
                        {{-- التصنيف --}}
                        @if($product->category)
                        <div class="flex items-center justify-between p-3 bg-gradient-to-r from-orange-50 to-amber-50 rounded-lg border border-orange-100">
                            <span class="text-sm font-semibold text-orange-700">التصنيف</span>
                            <span class="px-3 py-1 bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-full text-xs font-bold shadow-sm">
                                {{ $product->category }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- إحصائيات المخزون --}}
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white">
                <h3 class="font-bold mb-4 flex items-center gap-2 text-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    حالة المخزون
                </h3>
                
                @php
                    $stock = $product->total_stock ?? 0;
                    $alertQuantity = $product->stock_alert_quantity ?? 10;
                    $isLowStock = $stock <= $alertQuantity && $stock > 0;
                    $isOutOfStock = $stock <= 0;
                @endphp
                
                <div class="bg-white bg-opacity-20 rounded-xl p-4 backdrop-blur-sm">
                    <p class="text-sm text-white text-opacity-90 mb-2">إجمالي المخزون</p>
                    <p class="text-4xl font-bold">{{ number_format($stock, 0) }}</p>
                    
                    @if($isOutOfStock)
                        <div class="mt-3 flex items-center gap-2 text-red-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="font-semibold">المخزون نفذ!</span>
                        </div>
                    @elseif($isLowStock)
                        <div class="mt-3 flex items-center gap-2 text-yellow-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="font-semibold">المخزون منخفض</span>
                        </div>
                    @else
                        <div class="mt-3 flex items-center gap-2 text-green-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="font-semibold">المخزون جيد</span>
                        </div>
                    @endif
                </div>
                
                @if($product->stock_alert_quantity)
                <div class="mt-4 text-sm text-white text-opacity-75">
                    <p>حد التنبيه: <span class="font-bold">{{ number_format($product->stock_alert_quantity, 0) }}</span></p>
                </div>
                @endif
            </div>
        </div>

        {{-- ====== القسم الأيمن: التفاصيل ====== --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- ====== بطاقات التسعير ====== --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 mb-6 flex items-center gap-3 text-xl border-b pb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    معلومات التسعير
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    {{-- الوحدة الأساسية --}}
                    <div class="group hover:scale-105 transition-transform duration-200">
                        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-6 rounded-xl shadow-lg text-white">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm font-semibold opacity-90">الوحدة الأساسية</p>
                                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-2xl font-bold">{{ $product->base_unit_label ?? $product->base_unit }}</p>
                        </div>
                    </div>

                    {{-- سعر الشراء --}}
                    <div class="group hover:scale-105 transition-transform duration-200">
                        <div class="bg-gradient-to-br from-green-500 to-emerald-600 p-6 rounded-xl shadow-lg text-white">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm font-semibold opacity-90">سعر الشراء</p>
                                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-2xl font-bold">{{ number_format($product->purchase_price, 2) }}</p>
                            <p class="text-sm opacity-75 mt-1">جنيه مصري</p>
                        </div>
                    </div>

                    {{-- سعر البيع --}}
                    <div class="group hover:scale-105 transition-transform duration-200">
                        <div class="bg-gradient-to-br from-purple-500 to-pink-600 p-6 rounded-xl shadow-lg text-white">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm font-semibold opacity-90">سعر البيع</p>
                                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-2xl font-bold">{{ number_format($product->selling_price, 2) }}</p>
                            <p class="text-sm opacity-75 mt-1">جنيه مصري</p>
                        </div>
                    </div>
                </div>

                {{-- هامش الربح --}}
                @php
                    $profit = $product->selling_price - $product->purchase_price;
                    $profitPercentage = $product->purchase_price > 0 ? ($profit / $product->purchase_price) * 100 : 0;
                @endphp
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gradient-to-r from-orange-50 to-amber-50 p-5 rounded-xl border-2 border-orange-200">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <p class="text-sm text-orange-700 font-semibold">هامش الربح</p>
                        </div>
                        <p class="text-2xl font-bold text-orange-900">{{ number_format($profit, 2) }} ج.م</p>
                        <p class="text-sm text-orange-600 mt-1">{{ number_format($profitPercentage, 1) }}% من سعر الشراء</p>
                    </div>

                    <div class="bg-gradient-to-r from-teal-50 to-cyan-50 p-5 rounded-xl border-2 border-teal-200">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm text-teal-700 font-semibold">قيمة المخزون</p>
                        </div>
                        <p class="text-2xl font-bold text-teal-900">{{ number_format($stock * $product->purchase_price, 2) }} ج.م</p>
                        <p class="text-sm text-teal-600 mt-1">بسعر الشراء الحالي</p>
                    </div>
                </div>
            </div>

            {{-- ====== الوصف ====== --}}
            @if($product->description)
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-3 text-xl border-b pb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                        </svg>
                    </div>
                    وصف المنتج
                </h3>
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-5 rounded-xl">
                    <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                </div>
            </div>
            @endif

            {{-- ====== وحدات البيع ====== --}}
            @if($product->sellingUnits && $product->sellingUnits->count() > 0)
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-3 text-xl border-b pb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    وحدات البيع المتاحة
                    <span class="text-sm font-normal text-gray-500">({{ $product->sellingUnits->count() }} وحدة)</span>
                </h3>

                <div class="overflow-hidden rounded-xl border-2 border-gray-200">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-gray-100 to-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">الوحدة</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">معامل التحويل</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">سعر الشراء</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">سعر البيع</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">الباركود</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($product->sellingUnits as $unit)
                            <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-gradient-to-br from-purple-100 to-pink-100 rounded-lg flex items-center justify-center">
                                            <span class="text-xs font-bold text-purple-600">{{ substr($unit->unit_code, 0, 2) }}</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">{{ $unit->unit_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-bold">
                                        ×{{ number_format($unit->conversion_factor, 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-green-600 font-bold">{{ number_format($unit->purchase_price, 2) }}</span>
                                    <span class="text-xs text-gray-500">ج.م</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-blue-600 font-bold text-lg">{{ number_format($unit->selling_price, 2) }}</span>
                                    <span class="text-xs text-gray-500">ج.م</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($unit->barcode)
                                        <span class="font-mono text-sm text-gray-700 bg-gray-100 px-3 py-1 rounded">{{ $unit->barcode }}</span>
                                    @else
                                        <span class="text-gray-400 text-sm">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($unit->is_default)
                                        <span class="px-3 py-1 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-full text-xs font-bold shadow-sm">
                                            افتراضي
                                        </span>
                                    @elseif($unit->is_base)
                                        <span class="px-3 py-1 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-full text-xs font-bold shadow-sm">
                                            أساسي
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-gray-200 text-gray-600 rounded-full text-xs font-semibold">
                                            إضافي
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- ====== المخازن ====== --}}
            @if($product->warehouses && $product->warehouses->count() > 0)
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-3 text-xl border-b pb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    توزيع المخزون
                    <span class="text-sm font-normal text-gray-500">({{ $product->warehouses->count() }} مخزن)</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($product->warehouses as $warehouse)
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-5 rounded-xl border-2 border-gray-200 hover:border-blue-300 transition-colors duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-gray-900 flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                </div>
                                {{ $warehouse->name }}
                            </h4>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-2 text-sm">
                            <div class="text-center bg-white rounded-lg p-2">
                                <p class="text-gray-500 text-xs">الكمية</p>
                                <p class="font-bold text-blue-600">{{ number_format($warehouse->pivot->quantity ?? 0, 0) }}</p>
                            </div>
                            <div class="text-center bg-white rounded-lg p-2">
                                <p class="text-gray-500 text-xs">محجوز</p>
                                <p class="font-bold text-orange-600">{{ number_format($warehouse->pivot->reserved_quantity ?? 0, 0) }}</p>
                            </div>
                            <div class="text-center bg-white rounded-lg p-2">
                                <p class="text-gray-500 text-xs">متاح</p>
                                <p class="font-bold text-green-600">{{ number_format($warehouse->pivot->available_quantity ?? 0, 0) }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ====== معلومات إضافية ====== --}}
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl shadow-lg border-2 border-gray-200 p-6">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-3 text-xl border-b border-gray-300 pb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-gray-600 to-gray-800 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    معلومات إضافية
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-4 rounded-xl border-2 border-gray-200">
                        <p class="text-gray-500 text-sm mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            تاريخ الإضافة
                        </p>
                        <p class="font-bold text-gray-900">{{ $product->created_at->format('Y/m/d') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $product->created_at->format('h:i A') }}</p>
                    </div>

                    <div class="bg-white p-4 rounded-xl border-2 border-gray-200">
                        <p class="text-gray-500 text-sm mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            آخر تحديث
                        </p>
                        <p class="font-bold text-gray-900">{{ $product->updated_at->format('Y/m/d') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $product->updated_at->format('h:i A') }}</p>
                    </div>

                    <div class="bg-white p-4 rounded-xl border-2 border-gray-200">
                        <p class="text-gray-500 text-sm mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                            مدة التواجد
                        </p>
                        <p class="font-bold text-gray-900">{{ $product->created_at->diffInDays(now()) }} يوم</p>
                        <p class="text-xs text-gray-500 mt-1">منذ الإضافة</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
@endpush

@endsection