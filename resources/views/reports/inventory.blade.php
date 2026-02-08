@extends('layouts.app')

@section('title', 'تقرير المخزون')
@section('page-title', 'تقرير المخزون')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 via-cyan-600 to-teal-600 rounded-2xl p-8 text-white shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full opacity-10">
            <div class="absolute top-10 right-10 w-32 h-32 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 left-10 w-40 h-40 bg-white rounded-full blur-3xl"></div>
        </div>
        <div class="relative z-10">
            <h2 class="text-4xl font-bold mb-2">تقرير المخزون الشامل</h2>
            <p class="text-white/80 text-lg">عرض تفصيلي لجميع الأصناف والكميات المتاحة</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-filter text-blue-600"></i>
            تصفية التقرير
        </h3>
        <form method="GET" action="{{ route('reports.inventory') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            
            <div class="md:col-span-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-warehouse ml-1 text-gray-400"></i>
                    المخزن
                </label>
                <select name="warehouse_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <option value="">كل المخازن</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-search ml-1 text-gray-400"></i>
                    بحث
                </label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="اسم المنتج أو الكود..."
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>

            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-layer-group ml-1 text-gray-400"></i>
                    الحالة
                </label>
                <select name="status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <option value="">الكل</option>
                    <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>مخزون منخفض</option>
                    <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>مخزون طبيعي</option>
                    <option value="zero" {{ request('status') == 'zero' ? 'selected' : '' }}>نفذ من المخزون</option>
                </select>
            </div>

            <div class="md:col-span-2 flex items-end gap-2">
                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white px-6 py-2.5 rounded-lg font-semibold transition hover-scale shadow-md">
                    <i class="fas fa-search ml-2"></i>
                    بحث
                </button>
                <button type="button" onclick="window.print()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg transition">
                    <i class="fas fa-print"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total Products -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-boxes text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-3 py-1 rounded-full">منتجات</span>
            </div>
            <h3 class="text-gray-500 text-sm font-semibold mb-2">إجمالي الأصناف</h3>
            <p class="text-3xl font-bold text-gray-800">{{ $totalProducts }}</p>
        </div>

        <!-- Total Value -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-dollar-sign text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full">قيمة</span>
            </div>
            <h3 class="text-gray-500 text-sm font-semibold mb-2">إجمالي القيمة</h3>
            <p class="text-3xl font-bold text-gray-800" dir="ltr" style="text-align: right;">
                {{ number_format($totalValue, 0) }} 
                <span class="text-lg text-gray-500">ج.م</span>
            </p>
        </div>

        <!-- Low Stock -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-exclamation-triangle text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-orange-600 bg-orange-100 px-3 py-1 rounded-full">تحذير</span>
            </div>
            <h3 class="text-gray-500 text-sm font-semibold mb-2">مخزون منخفض</h3>
            <p class="text-3xl font-bold text-orange-600">{{ $lowStockCount }}</p>
        </div>

        <!-- Warehouses -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-warehouse text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-purple-600 bg-purple-100 px-3 py-1 rounded-full">مخازن</span>
            </div>
            <h3 class="text-gray-500 text-sm font-semibold mb-2">عدد المخازن</h3>
            <p class="text-3xl font-bold text-gray-800">{{ $warehouses->count() }}</p>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-list-alt"></i>
                تفاصيل المخزون
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-barcode ml-1"></i>
                            الكود / الباركود
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-box ml-1"></i>
                            اسم الصنف
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-warehouse ml-1"></i>
                            المخزن
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-cubes ml-1"></i>
                            الكمية
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-chart-line ml-1"></i>
                            الحد الأدنى
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-money-bill ml-1"></i>
                            سعر الشراء
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-tag ml-1"></i>
                            سعر البيع
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-calculator ml-1"></i>
                            القيمة الإجمالية
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                            الحالة
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($inventory as $item)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $item->code }}</p>
                                    @if($item->barcode)
                                        <p class="text-xs text-gray-500">{{ $item->barcode }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md">
                                        {{ mb_substr($item->name, 0, 1) }}
                                    </div>
                                    <span class="font-medium text-gray-700">{{ $item->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 px-3 py-1 rounded-lg text-sm font-semibold">
                                    <i class="fas fa-warehouse text-xs"></i>
                                    {{ $item->warehouse_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-100 to-cyan-100 text-blue-700 rounded-xl text-xl font-bold shadow-sm">
                                    {{ $item->quantity }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-gray-600 font-semibold">{{ $item->min_stock }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-gray-700 font-semibold" dir="ltr">{{ number_format($item->purchase_price, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-green-600 font-bold" dir="ltr">{{ number_format($item->selling_price, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div>
                                    <p class="font-bold text-blue-600" dir="ltr">{{ number_format($item->total_value, 2) }} ج.م</p>
                                    <p class="text-xs text-gray-500" dir="ltr">ربح: {{ number_format($item->potential_profit, 0) }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($item->quantity == 0)
                                    <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 px-3 py-2 rounded-full text-xs font-bold">
                                        <i class="fas fa-times-circle"></i>
                                        نفذ
                                    </span>
                                @elseif($item->quantity <= $item->min_stock)
                                    <span class="inline-flex items-center gap-1 bg-orange-100 text-orange-700 px-3 py-2 rounded-full text-xs font-bold">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        منخفض
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 px-3 py-2 rounded-full text-xs font-bold">
                                        <i class="fas fa-check-circle"></i>
                                        متوفر
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-inbox text-5xl text-gray-300"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-800 mb-2">لا توجد بيانات</h3>
                                    <p class="text-gray-500">لم يتم العثور على أي منتجات في المخزون</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gradient-to-r from-blue-50 to-cyan-50 border-t-2 border-blue-200">
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-right font-bold text-blue-900 text-lg">
                            الإجمالي الكلي
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-blue-900 text-xl" dir="ltr">
                            {{ number_format($totalValue, 2) }} ج.م
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>

@push('styles')
<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
@endpush

@endsection