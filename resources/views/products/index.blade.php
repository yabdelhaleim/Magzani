@extends('layouts.app')

@section('title', 'قائمة الأصناف')
@section('page-title', 'الأصناف')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ====== Header Section ====== --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    جميع الأصناف
                </h1>
                <p class="mt-2 text-gray-600">إدارة شاملة للمنتجات والمخزون</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('products.create') }}"
                   class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    إضافة صنف جديد
                </a>
            </div>
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

    {{-- ====== Products Table ====== --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300">

        {{-- Table Header --}}
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">قائمة المنتجات</h3>
                        <p class="text-sm text-gray-600">إجمالي: <span class="font-semibold text-blue-600">{{ $products->total() }}</span> منتج</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Content --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-100 to-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider w-24">الكود</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">الاسم</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider w-32">التصنيف</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider w-32">سعر الشراء</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider w-32">سعر البيع</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider w-36">
                            المخزون
                            <span class="text-gray-400 font-normal text-xs mr-1">(حوّم للتفاصيل)</span>
                        </th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider w-24">الحالة</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider w-40">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200">

                        {{-- الكود --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-lg flex items-center justify-center">
                                    <span class="text-xs font-bold text-blue-600">#</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">{{ $product->code }}</span>
                            </div>
                        </td>

                        {{-- الاسم --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}"
                                             alt="{{ $product->name }}"
                                             class="w-10 h-10 rounded-lg object-cover border-2 border-gray-200">
                                    @else
                                        <div class="w-10 h-10 bg-gradient-to-br from-gray-200 to-gray-300 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ $product->name }}</p>
                                    @if($product->sku)
                                        <p class="text-xs text-gray-500">SKU: {{ $product->sku }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- التصنيف --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 rounded-full text-xs font-semibold border border-purple-200">
                                {{ $product->category }}
                            </span>
                        </td>

                        {{-- سعر الشراء --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-900">{{ number_format($product->purchase_price, 2) }}</span>
                                    <span class="text-xs text-gray-500">ج.م</span>
                                </div>
                            </div>
                        </td>

                        {{-- سعر البيع --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-blue-600">{{ number_format($product->selling_price, 2) }}</span>
                                    <span class="text-xs text-gray-500">ج.م</span>
                                </div>
                            </div>
                        </td>

                        {{-- ✅ المخزون - كل مخزن على حدة --}}
                        <td class="px-6 py-4">
                            @php
                                $totalStock = $product->total_stock;
                                $status     = $product->getStockStatus();
                                $warehouses = $product->warehouses ?? collect();
                            @endphp

                            <div class="flex flex-col gap-1">
                                {{-- كل مخزن على حدة --}}
                                @forelse($warehouses as $warehouse)
                                    @php $wQty = (float)($warehouse->pivot->quantity ?? 0); @endphp
                                    <div class="flex items-center justify-between gap-2 px-2 py-1 rounded-lg
                                        {{ $wQty <= 0 ? 'bg-red-50' : ($wQty <= ($product->stock_alert_quantity ?? 10) ? 'bg-orange-50' : 'bg-green-50') }}">
                                        {{-- اسم المخزن --}}
                                        <span class="text-xs text-gray-500 truncate" style="max-width:80px">
                                            {{ $warehouse->name }}
                                        </span>
                                        {{-- الكمية --}}
                                        <span class="text-xs font-bold
                                            {{ $wQty <= 0 ? 'text-red-600' : ($wQty <= ($product->stock_alert_quantity ?? 10) ? 'text-orange-600' : 'text-green-700') }}">
                                            {{ number_format($wQty, 0) }}
                                        </span>
                                    </div>
                                @empty
                                    <span class="text-xs text-gray-400">لا يوجد مخزون</span>
                                @endforelse

                                {{-- الإجمالي --}}
                                @if($warehouses->count() > 1)
                                    <div class="flex items-center justify-between gap-2 px-2 py-1 rounded-lg bg-blue-50 border border-blue-100 mt-0.5">
                                        <span class="text-xs font-semibold text-blue-500">الإجمالي</span>
                                        <span class="text-xs font-bold text-blue-700">{{ number_format($totalStock, 0) }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- الحالة --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->is_active)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-green-100 to-emerald-100 text-green-700 rounded-full text-xs font-bold border border-green-200">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    نشط
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-gray-100 to-slate-100 text-gray-700 rounded-full text-xs font-bold border border-gray-200">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    غير نشط
                                </span>
                            @endif
                        </td>

                        {{-- الإجراءات --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('products.show', $product) }}"
                                   class="p-2 bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-110"
                                   title="عرض التفاصيل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>

                                <a href="{{ route('products.edit', $product) }}"
                                   class="p-2 bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-lg hover:from-amber-600 hover:to-orange-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-110"
                                   title="تعديل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>

                                <button type="button"
                                        onclick="confirmDelete({{ $product->id }}, '{{ $product->name }}')"
                                        class="p-2 bg-gradient-to-br from-red-500 to-rose-600 text-white rounded-lg hover:from-red-600 hover:to-rose-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-110"
                                        title="حذف">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>

                                <form id="delete-form-{{ $product->id }}"
                                      action="{{ route('products.destroy', $product) }}"
                                      method="POST"
                                      class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center gap-4">
                                <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-gray-600 font-semibold text-lg mb-2">لا توجد منتجات حالياً</p>
                                    <p class="text-gray-500 text-sm">ابدأ بإضافة منتج جديد لإدارة مخزونك</p>
                                </div>
                                <a href="{{ route('products.create') }}"
                                   class="mt-4 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    إضافة منتج جديد
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        عرض <span class="font-semibold text-gray-900">{{ $products->firstItem() }}</span>
                        إلى <span class="font-semibold text-gray-900">{{ $products->lastItem() }}</span>
                        من إجمالي <span class="font-semibold text-gray-900">{{ $products->total() }}</span> منتج
                    </div>
                    <div>{{ $products->links() }}</div>
                </div>
            </div>
        @endif

    </div>
</div>

@push('styles')
<style>
    @keyframes slideInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .animate-slideInDown { animation: slideInDown 0.5s ease-out; }
    table { table-layout: fixed; }
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(productId, productName) {
    const confirmed = confirm(
        `⚠️ هل أنت متأكد من حذف المنتج؟\n\n` +
        `📦 اسم المنتج: ${productName}\n` +
        `🔢 الرقم: ${productId}\n\n` +
        `⚡ تحذير: هذا الإجراء لا يمكن التراجع عنه!`
    );
    if (confirmed) {
        document.getElementById(`delete-form-${productId}`).submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.animate-slideInDown').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'all 0.5s ease-out';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});
</script>
@endpush

@endsection