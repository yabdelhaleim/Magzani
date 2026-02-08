@extends('layouts.app')

@section('title', 'تقرير الأرباح والخسائر')
@section('page-title', 'الأرباح والخسائر')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 rounded-2xl p-8 text-white shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full opacity-10">
            <div class="absolute top-10 right-10 w-32 h-32 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 left-10 w-40 h-40 bg-white rounded-full blur-3xl"></div>
        </div>
        <div class="relative z-10">
            <h2 class="text-4xl font-bold mb-2">تقرير الأرباح والخسائر</h2>
            <p class="text-white/80 text-lg">
                من {{ $startDate->format('d/m/Y') }} إلى {{ $endDate->format('d/m/Y') }}
            </p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-filter text-green-600"></i>
            تصفية التقرير
        </h3>
        <form method="GET" action="{{ route('reports.profit-loss') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            
            <div class="md:col-span-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt ml-1 text-gray-400"></i>
                    من تاريخ
                </label>
                <input type="date" 
                       name="start_date" 
                       value="{{ request('start_date', $startDate->toDateString()) }}"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
            </div>

            <div class="md:col-span-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar-check ml-1 text-gray-400"></i>
                    إلى تاريخ
                </label>
                <input type="date" 
                       name="end_date" 
                       value="{{ request('end_date', $endDate->toDateString()) }}"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
            </div>

            <div class="md:col-span-4 flex items-end gap-2">
                <button type="submit" class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-2.5 rounded-lg font-semibold transition hover-scale shadow-md">
                    <i class="fas fa-search ml-2"></i>
                    عرض التقرير
                </button>
                <button type="button" onclick="window.print()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg transition">
                    <i class="fas fa-print"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Gross Profit -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border-2 border-green-200 hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-chart-line text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full">إجمالي</span>
            </div>
            <h3 class="text-gray-500 text-sm font-semibold mb-2">إجمالي الربح</h3>
            <p class="text-4xl font-bold {{ $report['gross_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}" dir="ltr" style="text-align: right;">
                {{ number_format($report['gross_profit'], 2) }} 
                <span class="text-lg text-gray-500">ج.م</span>
            </p>
            <p class="text-sm text-gray-500 mt-2">قبل خصم المصروفات</p>
        </div>

        <!-- Total Expenses -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border-2 border-orange-200 hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-money-bill-wave text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-orange-600 bg-orange-100 px-3 py-1 rounded-full">مصروفات</span>
            </div>
            <h3 class="text-gray-500 text-sm font-semibold mb-2">إجمالي المصروفات</h3>
            <p class="text-4xl font-bold text-orange-600" dir="ltr" style="text-align: right;">
                {{ number_format($report['total_expenses'], 2) }} 
                <span class="text-lg text-gray-500">ج.م</span>
            </p>
            <p class="text-sm text-gray-500 mt-2">جميع المصروفات التشغيلية</p>
        </div>

        <!-- Net Profit -->
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 shadow-2xl hover:shadow-3xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur-lg rounded-xl flex items-center justify-center shadow-lg border border-white/30">
                    <i class="fas fa-trophy text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-white bg-white/20 px-3 py-1 rounded-full border border-white/30">صافي</span>
            </div>
            <h3 class="text-white/90 text-sm font-semibold mb-2">صافي الربح</h3>
            <p class="text-4xl font-bold text-white" dir="ltr" style="text-align: right;">
                {{ number_format($report['net_profit'], 2) }} 
                <span class="text-lg text-white/80">ج.م</span>
            </p>
            <p class="text-sm text-white/80 mt-2">
                <i class="fas fa-percent ml-1"></i>
                هامش ربح {{ number_format($report['profit_margin'], 1) }}%
            </p>
        </div>
    </div>

    <!-- Profit & Loss Statement -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar"></i>
                قائمة الأرباح والخسائر التفصيلية
            </h3>
        </div>

        <div class="p-6">
            <table class="w-full">
                <tbody class="divide-y divide-gray-200">
                    
                    <!-- Revenue Section -->
                    <tr class="bg-gradient-to-r from-blue-50 to-cyan-50">
                        <th colspan="2" class="px-6 py-4 text-right text-xl font-bold text-blue-900">
                            <i class="fas fa-arrow-up ml-2"></i>
                            الإيرادات
                        </th>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-700 font-medium">إجمالي المبيعات</td>
                        <td class="px-6 py-3 text-left font-bold text-gray-800 text-lg" dir="ltr">
                            {{ number_format($report['total_sales'], 2) }} ج.م
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-700 font-medium">مرتجع المبيعات</td>
                        <td class="px-6 py-3 text-left font-bold text-red-600 text-lg" dir="ltr">
                            ({{ number_format($report['sales_returns'], 2) }}) ج.م
                        </td>
                    </tr>
                    <tr class="bg-blue-100 border-t-2 border-blue-200">
                        <td class="px-6 py-4 text-blue-900 font-bold text-lg">صافي المبيعات</td>
                        <td class="px-6 py-4 text-left text-blue-900 font-bold text-2xl" dir="ltr">
                            {{ number_format($report['net_sales'], 2) }} ج.م
                        </td>
                    </tr>

                    <!-- Cost Section -->
                    <tr class="bg-gradient-to-r from-purple-50 to-pink-50">
                        <th colspan="2" class="px-6 py-4 text-right text-xl font-bold text-purple-900">
                            <i class="fas fa-box ml-2"></i>
                            التكاليف المباشرة
                        </th>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-700 font-medium">تكلفة البضاعة المباعة</td>
                        <td class="px-6 py-3 text-left font-bold text-gray-800 text-lg" dir="ltr">
                            {{ number_format($report['cost_of_sales'], 2) }} ج.م
                        </td>
                    </tr>
                    <tr class="bg-green-100 border-t-2 border-green-200">
                        <td class="px-6 py-4 text-green-900 font-bold text-lg">
                            <i class="fas fa-check-circle ml-2"></i>
                            إجمالي الربح (قبل المصروفات)
                        </td>
                        <td class="px-6 py-4 text-left text-green-900 font-bold text-2xl" dir="ltr">
                            {{ number_format($report['gross_profit'], 2) }} ج.م
                        </td>
                    </tr>

                    <!-- Expenses Section -->
                    <tr class="bg-gradient-to-r from-orange-50 to-amber-50">
                        <th colspan="2" class="px-6 py-4 text-right text-xl font-bold text-orange-900">
                            <i class="fas fa-money-bill-wave ml-2"></i>
                            المصروفات التشغيلية
                        </th>
                    </tr>
                    @forelse($expensesByCategory as $expense)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-gray-700 font-medium">
                                <i class="fas fa-circle text-orange-400 text-xs ml-2"></i>
                                {{ $expense->category ?? 'غير مصنف' }}
                                <span class="text-xs text-gray-500">({{ $expense->count }} عملية)</span>
                            </td>
                            <td class="px-6 py-3 text-left font-bold text-gray-800" dir="ltr">
                                {{ number_format($expense->total_amount, 2) }} ج.م
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-3 text-center text-gray-500">لا توجد مصروفات</td>
                        </tr>
                    @endforelse
                    <tr class="bg-orange-100 border-t-2 border-orange-200">
                        <td class="px-6 py-4 text-orange-900 font-bold text-lg">إجمالي المصروفات</td>
                        <td class="px-6 py-4 text-left text-orange-900 font-bold text-2xl" dir="ltr">
                            {{ number_format($report['total_expenses'], 2) }} ج.م
                        </td>
                    </tr>

                    <!-- Net Profit -->
                    <tr class="bg-gradient-to-r from-green-500 to-emerald-600 text-white border-t-4 border-green-700">
                        <td class="px-6 py-5 text-2xl font-bold">
                            <i class="fas fa-trophy ml-2"></i>
                            صافي الربح / (الخسارة)
                        </td>
                        <td class="px-6 py-5 text-left text-3xl font-bold" dir="ltr">
                            {{ number_format($report['net_profit'], 2) }} ج.م
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Profit Margin Chart -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <i class="fas fa-chart-pie text-green-600"></i>
            توزيع الإيرادات والتكاليف
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Revenue Bar -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-700">الإيرادات</span>
                    <span class="text-sm font-bold text-blue-600" dir="ltr">{{ number_format($report['net_sales'], 0) }} ج.م</span>
                </div>
                <div class="h-8 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-600" style="width: 100%"></div>
                </div>
            </div>

            <!-- Cost Bar -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-700">التكاليف</span>
                    <span class="text-sm font-bold text-purple-600" dir="ltr">{{ number_format($report['cost_of_sales'], 0) }} ج.م</span>
                </div>
                <div class="h-8 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-purple-500 to-pink-600" 
                         style="width: {{ $report['net_sales'] > 0 ? ($report['cost_of_sales'] / $report['net_sales'] * 100) : 0 }}%"></div>
                </div>
            </div>

            <!-- Expenses Bar -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-700">المصروفات</span>
                    <span class="text-sm font-bold text-orange-600" dir="ltr">{{ number_format($report['total_expenses'], 0) }} ج.م</span>
                </div>
                <div class="h-8 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-orange-500 to-amber-600" 
                         style="width: {{ $report['net_sales'] > 0 ? ($report['total_expenses'] / $report['net_sales'] * 100) : 0 }}%"></div>
                </div>
            </div>
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