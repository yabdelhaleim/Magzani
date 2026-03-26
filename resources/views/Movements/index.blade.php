@extends('layouts.app')

@section('title', 'سجل حركات المخزون')
@section('page-title', 'سجل حركات المخزون')

@section('content')
<div class="space-y-4 md:space-y-6" x-data="{ 
    showFilters: false,
    selectedType: '{{ request('movement_type') }}',
    selectedWarehouse: '{{ request('warehouse_id') }}',
    selectedProduct: '{{ request('product_id') }}'
}">

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4">
        <!-- إجمالي الحركات -->
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border-r-2 md:border-r-4 border-blue-500 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-xs md:text-sm mb-1">إجمالي الحركات</p>
                    <h3 class="text-xl md:text-2xl font-bold text-gray-800">{{ number_format($movements->total()) }}</h3>
                </div>
                <div class="w-8 h-8 md:w-12 md:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-right-arrow-left text-blue-600 text-sm md:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- حركات الإدخال -->
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border-r-2 md:border-r-4 border-green-500 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-xs md:text-sm mb-1">حركات إدخال</p>
                    <h3 class="text-xl md:text-2xl font-bold text-gray-800">{{ number_format($movements->where('quantity', '>', 0)->count()) }}</h3>
                </div>
                <div class="w-8 h-8 md:w-12 md:h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-down text-green-600 text-sm md:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- حركات الإخراج -->
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border-r-2 md:border-r-4 border-red-500 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-xs md:text-sm mb-1">حركات إخراج</p>
                    <h3 class="text-xl md:text-2xl font-bold text-gray-800">{{ number_format($movements->where('quantity', '<', 0)->count()) }}</h3>
                </div>
                <div class="w-8 h-8 md:w-12 md:h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-up text-red-600 text-sm md:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- المخازن النشطة -->
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border-r-2 md:border-r-4 border-purple-500 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-xs md:text-sm mb-1">المخازن النشطة</p>
                    <h3 class="text-xl md:text-2xl font-bold text-gray-800">{{ number_format($warehouses->count()) }}</h3>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-warehouse text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-filter text-blue-600"></i>
                الفلاتر والبحث
            </h3>
            <button @click="showFilters = !showFilters" 
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-all flex items-center gap-2">
                <i class="fas" :class="showFilters ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                <span x-text="showFilters ? 'إخفاء الفلاتر' : 'عرض الفلاتر'"></span>
            </button>
        </div>

        <form method="GET" action="{{ route('movements.index') }}" x-show="showFilters" x-collapse>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- نوع الحركة -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-tags text-gray-400"></i>
                        نوع الحركة
                    </label>
                    <select name="movement_type" x-model="selectedType"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">الكل</option>
                        <option value="purchase">شراء</option>
                        <option value="sale">بيع</option>
                        <option value="transfer_in">تحويل وارد</option>
                        <option value="transfer_out">تحويل صادر</option>
                        <option value="adjustment">تسوية</option>
                        <option value="return">مرتجع</option>
                    </select>
                </div>

                <!-- المخزن -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-warehouse text-gray-400"></i>
                        المخزن
                    </label>
                    <select name="warehouse_id" x-model="selectedWarehouse"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">كل المخازن</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- المنتج -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-box text-gray-400"></i>
                        المنتج
                    </label>
                    <select name="product_id" x-model="selectedProduct"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">كل المنتجات</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- من تاريخ -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar text-gray-400"></i>
                        من تاريخ
                    </label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <!-- إلى تاريخ -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-check text-gray-400"></i>
                        إلى تاريخ
                    </label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" 
                        class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all flex items-center gap-2 shadow-md hover:shadow-lg">
                    <i class="fas fa-search"></i>
                    <span>بحث</span>
                </button>
                <a href="{{ route('movements.index') }}" 
                   class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-all flex items-center gap-2">
                    <i class="fas fa-redo"></i>
                    <span>إعادة تعيين</span>
                </a>
                <a href="{{ route('movements.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                   class="mr-auto px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all flex items-center gap-2 shadow-md hover:shadow-lg">
                    <i class="fas fa-file-excel"></i>
                    <span>تصدير Excel</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Movements Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b bg-gradient-to-r from-gray-50 to-gray-100">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-list text-blue-600"></i>
                جميع الحركات
                <span class="text-sm font-normal text-gray-600">({{ number_format($movements->total()) }} حركة)</span>
            </h3>
        </div>

        @if($movements->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            #
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            التاريخ
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            المخزن
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            المنتج
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            نوع الحركة
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            الكمية
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            الرصيد بعد
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            المستخدم
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            الملاحظات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($movements as $movement)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-900">#{{ $movement->id }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($movement->movement_date)->format('Y-m-d') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($movement->movement_date)->format('h:i A') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-warehouse text-purple-600 text-xs"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $movement->warehouse->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $movement->warehouse->location ?? 'لا يوجد' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-box text-blue-600 text-xs"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $movement->product->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $movement->product->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $typeConfig = [
                                    'purchase' => ['label' => 'شراء', 'bg' => 'bg-green-100', 'text' => 'text-green-700', 'icon' => 'fa-shopping-cart'],
                                    'sale' => ['label' => 'بيع', 'bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'fa-cash-register'],
                                    'transfer_in' => ['label' => 'تحويل وارد', 'bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'icon' => 'fa-arrow-down'],
                                    'transfer_out' => ['label' => 'تحويل صادر', 'bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'icon' => 'fa-arrow-up'],
                                    'adjustment' => ['label' => 'تسوية', 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'icon' => 'fa-balance-scale'],
                                    'return' => ['label' => 'مرتجع', 'bg' => 'bg-red-100', 'text' => 'text-red-700', 'icon' => 'fa-undo'],
                                ];
                                $config = $typeConfig[$movement->movement_type] ?? ['label' => $movement->movement_type, 'bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'icon' => 'fa-question'];
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $config['bg'] }} {{ $config['text'] }}">
                                <i class="fas {{ $config['icon'] }}"></i>
                                {{ $config['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($movement->quantity > 0)
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-sm font-bold">
                                    <i class="fas fa-plus"></i>
                                    {{ number_format(abs($movement->quantity)) }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-sm font-bold">
                                    <i class="fas fa-minus"></i>
                                    {{ number_format(abs($movement->quantity)) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-bold text-gray-900">{{ number_format($movement->balance_after) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($movement->creator->name ?? 'مستخدم') }}&background=667eea&color=fff" 
                                     class="w-8 h-8 rounded-full ring-2 ring-gray-200">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $movement->creator->name ?? 'غير محدد' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-600 max-w-xs truncate" title="{{ $movement->notes }}">
                                {{ $movement->notes ?? '-' }}
                            </p>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50 border-t">
            {{ $movements->links() }}
        </div>
        @else
        <div class="p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                <i class="fas fa-inbox text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">لا توجد حركات</h3>
            <p class="text-gray-600 mb-4">لم يتم العثور على أي حركات مخزنية</p>
            <a href="{{ route('movements.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all">
                <i class="fas fa-redo"></i>
                <span>إعادة تعيين الفلاتر</span>
            </a>
        </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
    // Auto-hide success messages after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('[class*="bg-green-100"], [class*="bg-blue-100"]');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
</script>
@endpush
@endsection