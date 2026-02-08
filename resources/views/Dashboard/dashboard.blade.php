@extends('layouts.app')

@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')

@section('content')

<!-- الإحصائيات الرئيسية -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

    <!-- مبيعات اليوم -->
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-white/20 p-3 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <p class="text-blue-100 text-sm mb-1">مبيعات اليوم</p>
        <h3 class="text-2xl font-bold">{{ number_format($summary['today_sales'] ?? 0, 2) }} ج.م</h3>
    </div>

    <!-- مبيعات الشهر -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-white/20 p-3 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
        </div>
        <p class="text-green-100 text-sm mb-1">مبيعات الشهر</p>
        <h3 class="text-2xl font-bold">{{ number_format($summary['month_sales'] ?? 0, 2) }} ج.م</h3>
    </div>

    <!-- إجمالي العملاء -->
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-white/20 p-3 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
        </div>
        <p class="text-purple-100 text-sm mb-1">إجمالي العملاء</p>
        <h3 class="text-2xl font-bold">{{ number_format($summary['total_customers'] ?? 0) }}</h3>
    </div>

    <!-- إجمالي المنتجات -->
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-white/20 p-3 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>
        <p class="text-orange-100 text-sm mb-1">إجمالي المنتجات</p>
        <h3 class="text-2xl font-bold">{{ number_format($summary['total_products'] ?? 0) }}</h3>
    </div>

</div>

<!-- الإحصائيات الثانوية -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

    <!-- منتجات قليلة المخزون -->
    <div class="bg-white rounded-xl shadow-sm p-6 border-r-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">منتجات قليلة المخزون</p>
                <h3 class="text-2xl font-bold text-red-600">{{ $summary['low_stock_count'] ?? 0 }}</h3>
            </div>
            <div class="bg-red-100 p-3 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- تحويلات معلقة -->
    <div class="bg-white rounded-xl shadow-sm p-6 border-r-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">تحويلات معلقة</p>
                <h3 class="text-2xl font-bold text-yellow-600">{{ $summary['pending_transfers'] ?? 0 }}</h3>
            </div>
            <div class="bg-yellow-100 p-3 rounded-full">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- إجمالي الديون -->
    <div class="bg-white rounded-xl shadow-sm p-6 border-r-4 border-pink-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">إجمالي الديون</p>
                <h3 class="text-2xl font-bold text-pink-600">{{ number_format($summary['total_debt'] ?? 0, 2) }} ج.م</h3>
            </div>
            <div class="bg-pink-100 p-3 rounded-full">
                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- رصيد الخزينة -->
    <div class="bg-white rounded-xl shadow-sm p-6 border-r-4 border-teal-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">رصيد الخزينة</p>
                <h3 class="text-2xl font-bold text-teal-600">{{ number_format($summary['cash_balance'] ?? 0, 2) }} ج.م</h3>
            </div>
            <div class="bg-teal-100 p-3 rounded-full">
                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
    </div>

</div>

<!-- الجداول والرسوم البيانية -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    <!-- آخر الفواتير -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between bg-gray-50">
            <h3 class="font-bold text-lg text-gray-800">آخر الفواتير</h3>
            <a href="{{ route('invoices.sales.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center gap-1">
                عرض الكل
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الفاتورة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">العميل</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المبلغ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($summary['recent_invoices'] ?? [] as $invoice)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $invoice->reference ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $invoice->party_name ?? 'غير محدد' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($invoice->total ?? 0, 2) }} ج.م</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                @if(($invoice->status ?? '') == 'paid') bg-green-100 text-green-800
                                @elseif(($invoice->status ?? '') == 'pending') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $invoice->status ?? 'غير محدد' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $invoice->created_at ? $invoice->created_at->format('Y-m-d') : 'N/A' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            لا توجد فواتير حالياً
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- تنبيهات المخزون -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <h3 class="font-bold text-lg text-gray-800 flex items-center gap-2">
                <span class="bg-red-100 p-2 rounded-lg">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </span>
                تنبيهات المخزون
            </h3>
        </div>

        <div class="p-6 space-y-3 max-h-96 overflow-y-auto">
            @forelse($summary['low_stock_products'] ?? [] as $product)
            <div class="border border-red-200 bg-red-50 p-4 rounded-lg">
                <div class="flex items-start justify-between mb-2">
                    <h4 class="font-semibold text-gray-900">{{ $product->name ?? 'منتج غير محدد' }}</h4>
                    <span class="bg-red-600 text-white text-xs px-2 py-1 rounded-full">تنبيه</span>
                </div>
                <p class="text-sm text-gray-600 mb-2">{{ $product->warehouse->name ?? 'مخزن غير محدد' }}</p>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-red-700 font-medium">متوفر: {{ $product->qty ?? 0 }}</span>
                    <span class="text-gray-500">|</span>
                    <span class="text-gray-600">الحد الأدنى: {{ $product->min_qty ?? 0 }}</span>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="font-medium">لا توجد تنبيهات</p>
                <p class="text-sm">جميع المنتجات في المخزون كافية</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

@endsection