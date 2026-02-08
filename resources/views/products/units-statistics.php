@extends('layouts.app')

@section('title', 'إحصائيات الوحدات')
@section('page-title', 'إحصائيات الوحدات')

@section('content')
<div class="max-w-7xl mx-auto">
    
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">📊 إحصائيات الوحدات</h2>
            <p class="text-gray-600 mt-1">تحليل شامل لأسعار المنتجات حسب الوحدات الأساسية</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('products.bulk-price-update') }}" 
               class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                تحديث الأسعار
            </a>
            <a href="{{ route('products.index') }}" 
               class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                رجوع
            </a>
        </div>
    </div>

    {{-- إحصائيات عامة --}}
    @if(!empty($statistics))
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl border-2 border-blue-200 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-blue-700 font-semibold">عدد الوحدات المستخدمة</p>
                <div class="bg-blue-500 rounded-lg p-2">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-blue-900">{{ count($statistics) }}</p>
            <p class="text-xs text-blue-600 mt-1">وحدة مختلفة</p>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl border-2 border-green-200 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-green-700 font-semibold">إجمالي المنتجات</p>
                <div class="bg-green-500 rounded-lg p-2">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-green-900">{{ collect($statistics)->sum('products_count') }}</p>
            <p class="text-xs text-green-600 mt-1">منتج في النظام</p>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl border-2 border-purple-200 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-purple-700 font-semibold">متوسط سعر الشراء</p>
                <div class="bg-purple-500 rounded-lg p-2">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-purple-900">
                {{ number_format(collect($statistics)->avg('avg_purchase_price'), 2) }}
            </p>
            <p class="text-xs text-purple-600 mt-1">ج.م</p>
        </div>

        <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl border-2 border-orange-200 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-orange-700 font-semibold">متوسط هامش الربح</p>
                <div class="bg-orange-500 rounded-lg p-2">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-orange-900">
                {{ number_format(collect($statistics)->avg('avg_profit_margin'), 2) }}
            </p>
            <p class="text-xs text-orange-600 mt-1">ج.م</p>
        </div>
    </div>
    @endif

    {{-- جدول الإحصائيات --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-blue-50 border-b-2 border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                تفاصيل الإحصائيات حسب الوحدة
            </h3>
        </div>

        @if(empty($statistics))
        <div class="text-center py-12">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">لا توجد إحصائيات</h3>
            <p class="text-gray-600">لا توجد منتجات في النظام حالياً</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">#</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">الوحدة</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">عدد المنتجات</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">متوسط سعر الشراء</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">متوسط سعر البيع</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">متوسط هامش الربح</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">إجمالي القيمة</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($statistics as $index => $stat)
                    <tr class="hover:bg-blue-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-700">{{ $index + 1 }}</span>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ $stat['unit_icon'] }}</span>
                                <div>
                                    <p class="font-bold text-gray-800">{{ $stat['unit_label'] }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $stat['unit_code'] }}</p>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full font-bold text-sm">
                                    {{ $stat['products_count'] }}
                                </span>
                                <span class="text-xs text-gray-500">منتج</span>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="font-semibold text-green-600">
                                {{ number_format($stat['avg_purchase_price'], 2) }} ج.م
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="font-bold text-blue-600 text-lg">
                                {{ number_format($stat['avg_selling_price'], 2) }} ج.م
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="font-semibold text-purple-600">
                                {{ number_format($stat['avg_profit_margin'], 2) }} ج.م
                            </div>
                            @php
                                $profitPercentage = $stat['avg_purchase_price'] > 0 
                                    ? ($stat['avg_profit_margin'] / $stat['avg_purchase_price']) * 100 
                                    : 0;
                            @endphp
                            <div class="text-xs text-gray-500 mt-1">
                                ({{ number_format($profitPercentage, 1) }}%)
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="font-bold text-orange-600">
                                {{ number_format($stat['total_value'], 2) }} ج.م
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <a href="{{ route('products.bulk-price-update') }}?unit={{ $stat['unit_code'] }}" 
                               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                تحديث الأسعار
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                
                {{-- الإجمالي --}}
                <tfoot class="bg-gradient-to-r from-gray-100 to-blue-100 border-t-2 border-gray-300">
                    <tr class="font-bold">
                        <td colspan="2" class="px-6 py-4 text-gray-800">الإجمالي</td>
                        <td class="px-6 py-4 text-blue-700">
                            {{ collect($statistics)->sum('products_count') }} منتج
                        </td>
                        <td class="px-6 py-4 text-green-700">
                            {{ number_format(collect($statistics)->avg('avg_purchase_price'), 2) }} ج.م
                        </td>
                        <td class="px-6 py-4 text-blue-700">
                            {{ number_format(collect($statistics)->avg('avg_selling_price'), 2) }} ج.م
                        </td>
                        <td class="px-6 py-4 text-purple-700">
                            {{ number_format(collect($statistics)->avg('avg_profit_margin'), 2) }} ج.م
                        </td>
                        <td class="px-6 py-4 text-orange-700">
                            {{ number_format(collect($statistics)->sum('total_value'), 2) }} ج.م
                        </td>
                        <td class="px-6 py-4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>

    {{-- رسم بياني (اختياري) --}}
    @if(!empty($statistics) && count($statistics) > 0)
    <div class="mt-6 bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            توزيع المنتجات حسب الوحدات
        </h3>
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($statistics as $stat)
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-4 rounded-lg border-2 border-blue-200 text-center hover:shadow-lg transition-all">
                <div class="text-3xl mb-2">{{ $stat['unit_icon'] }}</div>
                <div class="text-sm font-semibold text-gray-700 mb-1">{{ $stat['unit_label'] }}</div>
                <div class="text-2xl font-bold text-blue-600">{{ $stat['products_count'] }}</div>
                <div class="text-xs text-gray-500 mt-1">منتج</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection