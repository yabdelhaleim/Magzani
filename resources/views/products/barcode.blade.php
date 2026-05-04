@extends('layouts.app')

@section('title', 'طباعة باركود المنتجات')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="no-print flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">طباعة باركود المنتجات</h2>
        <div class="flex gap-3">
            <button onclick="window.print()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-print ml-1"></i> طباعة
            </button>
            <a href="{{ route('products.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">رجوع</a>
        </div>
    </div>

    @if($products->count() > 0)
    <div id="barcode-container" class="grid grid-cols-3 gap-4">
        @foreach($products as $product)
        <div class="border p-3 text-center" style="page-break-inside: avoid;">
            <p class="font-bold text-sm mb-1">{{ $product->name }}</p>
            <p class="text-xs text-gray-500 mb-1">SKU: {{ $product->sku }}</p>
            @if($product->barcode)
            <svg class="barcode" data-value="{{ $product->barcode }}"></svg>
            <p class="text-xs mt-1">{{ $product->barcode }}</p>
            @else
            <p class="text-xs text-gray-400">لا يوجد باركود</p>
            @endif
            <p class="font-bold text-sm mt-1">{{ number_format($product->selling_price, 2) }}</p>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-12 text-gray-500">
        <i class="fas fa-barcode text-4xl mb-3"></i>
        <p>لا توجد منتجات</p>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        JsBarcode(".barcode").init();
    });
</script>
@endpush
@endsection
