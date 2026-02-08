@extends('layouts.app')

@section('title', 'التقارير المالية')
@section('page-title', 'التقارير المالية')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl p-8 text-white shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full opacity-10">
            <div class="absolute top-10 right-10 w-32 h-32 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 left-10 w-40 h-40 bg-white rounded-full blur-3xl"></div>
        </div>
        <div class="relative z-10">
            <h2 class="text-4xl font-bold mb-2">التقارير المالية الشاملة</h2>
            <p class="text-white/80 text-lg">
                من {{ $startDate->format('d/m/Y') }} إلى {{ $endDate->format('d/m/Y') }}
            </p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-filter text-indigo-600"></i>
            تصفية التقرير
        </h3>
        <form method="GET" action="{{ route('reports.financial') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            
            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt ml-1 text-gray-400"></i>
                    من تاريخ
                </label>
                <input type="date" 
                       name="start_date" 
                       value="{{ request('start_date', $startDate->toDateString()) }}"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
            </div>

            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar-check ml-1 text-gray-400"></i>
                    إلى تاريخ
                </label>
                <input type="date" 
                       name="end_date" 
                       value="{{ request('end_date', $endDate->toDateString()) }}"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
            </div>

            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-warehouse ml-1 text-gray-400"></i>
                    المخزن
                </label>
                <select name="warehouse_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    <option value="">كل المخازن</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3 flex items-end gap-2">
                <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-6 py-2.5 rounded-lg font-semibold transition hover-scale shadow-md">
                    <i class="fas fa-search ml-2"></i>
                    عرض التقرير
                </button>
                <button type="button" onclick="window.print()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg transition">
                    <i class="fas fa-print"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total Sales -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-shopping-cart text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-3 py-1 rounded-full">مبيعات</span>
            </div>
            <h3 class="text-gray-500 text-sm font-semibold mb-2">إجمالي المبيعات</h3>
            <p class="text-3xl font-bold text-gray-800" dir="ltr" style="text-align: right;">
                {{ number_format($report['total_sales'], 2) }} 
                <span class="text-lg text-gray-500">ج.م</span>
            </p>
            <p class="text-sm text-gray-500 mt-2">
                <i class="fas fa-file-invoice ml-1"></i>
                {{ $report['sales_count'] }} فاتورة
            </p>
        </div>

        <!-- Total Purchases -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-truck text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-purple-600 bg-purple-100 px-3 py-1 rounded-full">مشتريات</span>
            </div>
            <h3 class="text-gray-500 text-sm font-semibold mb-2">إجمالي المشتريات</h3>
            <p class="text-3xl font-bold text-gray-800" dir="ltr" style="text-align: right;">
                {{ number_format($report['total_purchases'], 2) }} 
                <span class="text-lg text-gray-500">ج.م</span>
            </p>
            <p class="text-sm text-gray-500 mt-2">
                <i class="fas fa-file-invoice ml-1"></i>
                {{ $report['purchases_count'] }} فاتورة
            </p>
        </div>

        <!-- Net Profit -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-chart-line text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full">ربح</span>
            </div>
            <h3 class="text-gray-500 text-sm font-semibold mb-2">صافي الربح</h3>
            <p class="text-3xl font-bold {{ $report['net_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}" dir="ltr" style="text-align: right;">
                {{ number_format($report['net_profit'], 2) }} 
                <span class="text-lg text-gray-500">ج.م</span>
            </p>
            <p class="text-sm text-gray-500 mt-2">
                <i class="fas fa-percent ml-1"></i>
                هامش ربح {{ number_format($report['profit_margin'], 1) }}%
            </p>
        </div>

        <!-- Total Expenses -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-red-400 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-money-bill-wave text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-red-600 bg-red-100 px-3 py-1 rounded-full">مصروفات</span>
            </div>
            <h3 class="text-gray-500 text-sm font-semibold mb-2">إجمالي المصروفات</h3>
            <p class="text-3xl font-bold text-gray-800" dir="ltr" style="text-align: right;">
                {{ number_format($report['total_expenses'], 2) }} 
                <span class="text-lg text-gray-500">ج.م</span>
            </p>
            <p class="text-sm text-gray-500 mt-2">
                <i class="fas fa-layer-group ml-1"></i>
                {{ count($report['expenses_by_category']) }} فئة
            </p>
        </div>
    </div>

    <!-- Profit & Loss Statement -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar"></i>
                قائمة الأرباح والخسائر التفصيلية
            </h3>
        </div>

        <div class="p-6">
            <table class="w-full">
                <tbody class="divide-y divide-gray-200">
                    
                    <!-- Revenue Section -->
                    <tr class="bg-blue-50">
                        <th colspan="2" class="px-6 py-3 text-right text-lg font-bold text-blue-900">
                            <i class="fas fa-coins ml-2"></i>
                            الإيرادات
                        </th>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-700">إجمالي المبيعات</td>
                        <td class="px-6 py-3 text-left font-semibold text-gray-800" dir="ltr">
                            {{ number_format($report['total_sales'], 2) }} ج.م
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-700">مرتجع المبيعات</td>
                        <td class="px-6 py-3 text-left font-semibold text-red-600" dir="ltr">
                            ({{ number_format($report['sales_returns'], 2) }}) ج.م
                        </td>
                    </tr>
                    <tr class="bg-blue-100 font-bold">
                        <td class="px-6 py-3 text-blue-900">صافي المبيعات</td>
                        <td class="px-6 py-3 text-left text-blue-900 text-lg" dir="ltr">
                            {{ number_format($report['net_sales'], 2) }} ج.م
                        </td>
                    </tr>

                    <!-- Cost Section -->
                    <tr class="bg-purple-50">
                        <th colspan="2" class="px-6 py-3 text-right text-lg font-bold text-purple-900">
                            <i class="fas fa-box ml-2"></i>
                            التكاليف
                        </th>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-700">تكلفة البضاعة المباعة</td>
                        <td class="px-6 py-3 text-left font-semibold text-gray-800" dir="ltr">
                            {{ number_format($report['cost_of_sales'], 2) }} ج.م
                        </td>
                    </tr>
                    <tr class="bg-green-100 font-bold">
                        <td class="px-6 py-3 text-green-900">إجمالي الربح (قبل المصروفات)</td>
                        <td class="px-6 py-3 text-left text-green-900 text-lg" dir="ltr">
                            {{ number_format($report['gross_profit'], 2) }} ج.م
                        </td>
                    </tr>

                    <!-- Expenses Section -->
                    <tr class="bg-orange-50">
                        <th colspan="2" class="px-6 py-3 text-right text-lg font-bold text-orange-900">
                            <i class="fas fa-money-bill-wave ml-2"></i>
                            المصروفات التشغيلية
                        </th>
                    </tr>
                    @foreach($report['expenses_by_category'] as $category => $amount)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-gray-700">
                                <i class="fas fa-circle text-orange-400 text-xs ml-2"></i>
                                {{ $category ?? 'غير مصنف' }}
                            </td>
                            <td class="px-6 py-3 text-left font-semibold text-gray-800" dir="ltr">
                                {{ number_format($amount, 2) }} ج.م
                            </td>
                        </tr>
                    @endforeach
                    <tr class="bg-orange-100 font-bold">
                        <td class="px-6 py-3 text-orange-900">إجمالي المصروفات</td>
                        <td class="px-6 py-3 text-left text-orange-900 text-lg" dir="ltr">
                            {{ number_format($report['total_expenses'], 2) }} ج.م
                        </td>
                    </tr>

                    <!-- Net Profit -->
                    <tr class="bg-gradient-to-r from-green-500 to-emerald-600 text-white">
                        <td class="px-6 py-4 text-xl font-bold">
                            <i class="fas fa-trophy ml-2"></i>
                            صافي الربح / (الخسارة)
                        </td>
                        <td class="px-6 py-4 text-left text-2xl font-bold" dir="ltr">
                            {{ number_format($report['net_profit'], 2) }} ج.م
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Top Selling Products -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-star"></i>
                    أفضل 5 منتجات مبيعاً
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($topProducts as $index => $product)
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">{{ $product->name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $product->total_quantity }} وحدة • 
                                    {{ $product->number_of_orders }} طلب
                                </p>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-blue-600" dir="ltr">{{ number_format($product->total_revenue, 0) }} ج.م</p>
                                <p class="text-xs text-green-600" dir="ltr">ربح: {{ number_format($product->total_profit, 0) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-8">لا توجد بيانات</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-users"></i>
                    أفضل 5 عملاء
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($topCustomers as $index => $customer)
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center text-white font-bold shadow-md">
                                {{ mb_substr($customer->name, 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">{{ $customer->name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $customer->total_invoices }} فاتورة
                                </p>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-purple-600" dir="ltr">{{ number_format($customer->total_spent, 0) }} ج.م</p>
                                <p class="text-xs text-gray-500" dir="ltr">معدل: {{ number_format($customer->average_invoice, 0) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-8">لا توجد بيانات</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Sales Chart -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-green-600 to-teal-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-chart-bar"></i>
                المبيعات اليومية
            </h3>
        </div>
        <div class="p-6 overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-600">التاريخ</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-600">عدد الفواتير</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-600">إجمالي المبيعات</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-600">المدفوع</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-600">المتبقي</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-600">متوسط الفاتورة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($dailySales as $day)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($day->date)->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">
                                    {{ $day->total_invoices }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-gray-800" dir="ltr">
                                {{ number_format($day->total_sales, 0) }} ج.م
                            </td>
                            <td class="px-4 py-3 text-center text-green-600 font-semibold" dir="ltr">
                                {{ number_format($day->total_paid, 0) }} ج.م
                            </td>
                            <td class="px-4 py-3 text-center text-red-600 font-semibold" dir="ltr">
                                {{ number_format($day->total_remaining, 0) }} ج.م
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600" dir="ltr">
                                {{ number_format($day->average_invoice, 0) }} ج.م
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                لا توجد مبيعات في هذه الفترة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
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
    body {
        background: white;
    }
}
</style>
@endpush

@endsection