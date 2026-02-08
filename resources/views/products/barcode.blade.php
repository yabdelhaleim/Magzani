@extends('layouts.app')

@section('title', 'طباعة الباركود')
@section('page-title', 'طباعة الباركود')

@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">طباعة الباركود</h2>
        <p class="text-gray-600 text-sm mt-1">اطبع باركود للمنتجات المحددة</p>
    </div>
    <div class="flex gap-3">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            طباعة
        </button>
        <a href="{{ route('products.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            رجوع
        </a>
    </div>
</div>

<!-- قائمة الباركود -->
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="barcode-grid">
        @forelse($products as $product)
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center barcode-item">
            <div class="mb-3">
                <h3 class="font-bold text-gray-800 text-sm truncate">{{ $product->name }}</h3>
                <p class="text-xs text-gray-500">SKU: {{ $product->sku ?? 'N/A' }}</p>
            </div>
            
            <!-- Barcode Image -->
            <div class="bg-white p-2 rounded">
                @if($product->barcode)
                    <img src="https://barcode.tec-it.com/barcode.ashx?data={{ $product->barcode }}&code=Code128&translate-esc=on" 
                         alt="Barcode" 
                         class="mx-auto h-16">
                    <p class="text-xs font-mono mt-1">{{ $product->barcode }}</p>
                @else
                    <div class="h-16 bg-gray-100 rounded flex items-center justify-center">
                        <p class="text-xs text-gray-400">لا يوجد باركود</p>
                    </div>
                @endif
            </div>
            
            <div class="mt-3">
                <p class="text-lg font-bold text-blue-600">{{ number_format($product->sale_price ?? 0, 2) }} ج.م</p>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">لا توجد منتجات</h3>
            <p class="text-gray-600">اختر منتجات لطباعة الباركود الخاص بها</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #barcode-grid, #barcode-grid * {
        visibility: visible;
    }
    #barcode-grid {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .barcode-item {
        page-break-inside: avoid;
        border: 1px solid #000;
        padding: 10px;
        margin: 5px;
    }
    /* إخفاء العناصر غير المطلوبة في الطباعة */
    header, .mb-6, button, a {
        display: none !important;
    }
}
</style>

@endsection