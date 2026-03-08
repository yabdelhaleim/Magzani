@extends('layouts.app')

@section('title', 'فواتير المبيعات')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4">
    <div class="container mx-auto max-w-7xl">
        
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center space-x-3 space-x-reverse">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">فواتير المبيعات</h2>
                        <p class="text-sm text-gray-500 mt-1">إدارة ومتابعة جميع فواتير المبيعات</p>
                    </div>
                </div>
                <a href="{{ route('invoices.sales.create') }}" 
                   class="flex items-center justify-center space-x-2 space-x-reverse bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>فاتورة جديدة</span>
                </a>
            </div>
        </div>

        <!-- 🔍 Smart Search Bar -->
        <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
            <form method="GET" action="{{ route('invoices.sales.index') }}">
                <div class="flex gap-3">
                    <div class="flex-1">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="🔍 بحث ذكي (رقم الفاتورة، اسم العميل، رقم الهاتف...)"
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all text-lg"
                        >
                    </div>
                    <button 
                        type="submit"
                        class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition-all duration-200 shadow-md hover:shadow-lg font-semibold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        بحث
                    </button>
                    @if(request()->hasAny(['search', 'invoice_number', 'customer_id', 'warehouse_id', 'status', 'date_from', 'date_to', 'amount_from', 'amount_to']))
                        <a 
                            href="{{ route('invoices.sales.index') }}"
                            class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition-all duration-200 font-semibold flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            مسح
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Advanced Filters (Collapsible) -->
        <div class="bg-white rounded-lg shadow-lg mb-6" x-data="{ showFilters: {{ request()->hasAny(['invoice_number', 'customer_id', 'warehouse_id', 'status', 'date_from', 'date_to', 'amount_from', 'amount_to']) ? 'true' : 'false' }} }">
            <div class="p-4 border-b border-gray-200">
                <button 
                    @click="showFilters = !showFilters"
                    class="flex items-center justify-between w-full text-left">
                    <span class="font-semibold text-gray-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        فلاتر متقدمة
                    </span>
                    <svg 
                        class="w-5 h-5 transition-transform duration-200" 
                        :class="showFilters ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            <div x-show="showFilters" x-transition>
                <form method="GET" action="{{ route('invoices.sales.index') }}" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        
                        <!-- رقم الفاتورة -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">رقم الفاتورة</label>
                            <input 
                                type="text" 
                                name="invoice_number" 
                                value="{{ request('invoice_number') }}"
                                placeholder="ابحث برقم الفاتورة"
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"
                            >
                        </div>

                        <!-- العميل -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">العميل</label>
                            <select 
                                name="customer_id" 
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"
                            >
                                <option value="">جميع العملاء</option>
                                @foreach($customers ?? [] as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- المخزن -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">المخزن</label>
                            <select 
                                name="warehouse_id" 
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"
                            >
                                <option value="">جميع المخازن</option>
                                @foreach($warehouses ?? [] as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- الحالة -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">الحالة</label>
                            <select 
                                name="status" 
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"
                            >
                                <option value="">جميع الحالات</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مكتملة (مدفوعة)</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلقة (لها مبلغ متبقي)</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                            </select>
                        </div>

                        <!-- من تاريخ -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">من تاريخ</label>
                            <input 
                                type="date" 
                                name="date_from" 
                                value="{{ request('date_from') }}"
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"
                            >
                        </div>

                        <!-- إلى تاريخ -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">إلى تاريخ</label>
                            <input 
                                type="date" 
                                name="date_to" 
                                value="{{ request('date_to') }}"
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"
                            >
                        </div>

                        <!-- من مبلغ -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">من مبلغ</label>
                            <input 
                                type="number" 
                                name="amount_from" 
                                value="{{ request('amount_from') }}"
                                placeholder="0.00"
                                step="0.01"
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"
                            >
                        </div>

                        <!-- إلى مبلغ -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">إلى مبلغ</label>
                            <input 
                                type="number" 
                                name="amount_to" 
                                value="{{ request('amount_to') }}"
                                placeholder="0.00"
                                step="0.01"
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"
                            >
                        </div>

                        <!-- الترتيب -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">الترتيب حسب</label>
                            <select 
                                name="sort_by" 
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"
                            >
                                <option value="invoice_date" {{ request('sort_by') == 'invoice_date' ? 'selected' : '' }}>التاريخ</option>
                                <option value="invoice_number" {{ request('sort_by') == 'invoice_number' ? 'selected' : '' }}>رقم الفاتورة</option>
                                <option value="total" {{ request('sort_by') == 'total' ? 'selected' : '' }}>المبلغ</option>
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>تاريخ الإنشاء</option>
                            </select>
                        </div>

                        <!-- اتجاه الترتيب -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">الاتجاه</label>
                            <select 
                                name="sort_order" 
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"
                            >
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>تنازلي</option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>تصاعدي</option>
                            </select>
                        </div>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="flex items-center gap-3 pt-4 mt-4 border-t border-gray-200">
                        <button 
                            type="submit"
                            class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-all duration-200 shadow-md hover:shadow-lg font-semibold flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            تطبيق الفلاتر
                        </button>
                        <a 
                            href="{{ route('invoices.sales.index') }}"
                            class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-all duration-200 font-semibold flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            إعادة تعيين
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Enhanced Statistics Cards -->
        @if(isset($statistics))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <!-- إجمالي الفواتير النشطة -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-semibold">نشطة</p>
                        <p class="text-3xl font-bold mt-2">{{ $statistics['total_count'] ?? 0 }}</p>
                    </div>
                    <svg class="w-12 h-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>

            <!-- فواتير مكتملة -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-semibold">مكتملة</p>
                        <p class="text-3xl font-bold mt-2">{{ $statistics['paid_count'] ?? 0 }}</p>
                    </div>
                    <svg class="w-12 h-12 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <!-- فواتير معلقة -->
            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm font-semibold">معلقة</p>
                        <p class="text-3xl font-bold mt-2">{{ $statistics['pending_count'] ?? 0 }}</p>
                    </div>
                    <svg class="w-12 h-12 text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <!-- إجمالي المبيعات -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-semibold">إجمالي المبيعات</p>
                        <p class="text-2xl font-bold mt-2">{{ number_format($statistics['total_amount'] ?? 0, 2) }}</p>
                        <p class="text-purple-100 text-xs mt-1">جنيه</p>
                    </div>
                    <svg class="w-12 h-12 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <!-- 💰 إجمالي الربح -->
            <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-pink-100 text-sm font-semibold">إجمالي الربح</p>
                        <p class="text-2xl font-bold mt-2">{{ number_format($statistics['total_profit'] ?? 0, 2) }}</p>
                        <p class="text-pink-100 text-xs mt-1">جنيه</p>
                    </div>
                    <svg class="w-12 h-12 text-pink-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Additional Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 border-r-4 border-indigo-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600">فواتير اليوم</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ $statistics['today_count'] ?? 0 }}</p>
                    </div>
                    <svg class="w-10 h-10 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border-r-4 border-teal-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600">فواتير الشهر</p>
                        <p class="text-2xl font-bold text-teal-600">{{ $statistics['month_count'] ?? 0 }}</p>
                    </div>
                    <svg class="w-10 h-10 text-teal-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border-r-4 border-orange-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600">المبلغ المتبقي</p>
                        <p class="text-2xl font-bold text-orange-600">{{ number_format($statistics['remaining_amount'] ?? 0, 2) }}</p>
                    </div>
                    <svg class="w-10 h-10 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        @endif

        <!-- Table Section -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                        <tr>
                            <th class="p-4 text-right text-sm font-bold text-gray-700">#</th>
                            <th class="p-4 text-right text-sm font-bold text-gray-700">رقم الفاتورة</th>
                            <th class="p-4 text-right text-sm font-bold text-gray-700">التاريخ</th>
                            <th class="p-4 text-right text-sm font-bold text-gray-700">العميل</th>
                            <th class="p-4 text-right text-sm font-bold text-gray-700">المخزن</th>
                            <th class="p-4 text-right text-sm font-bold text-gray-700">الإجمالي</th>
                            <th class="p-4 text-right text-sm font-bold text-gray-700">المدفوع</th>
                            <th class="p-4 text-right text-sm font-bold text-gray-700">المتبقي</th>
                            <th class="p-4 text-right text-sm font-bold text-gray-700">💰 الربح</th>
                            <th class="p-4 text-right text-sm font-bold text-gray-700">الحالة</th>
                            <th class="p-4 text-center text-sm font-bold text-gray-700">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($invoices as $index => $invoice)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="p-4 text-right text-gray-600">{{ $invoices->firstItem() + $index }}</td>
                            
                            <td class="p-4 text-right">
                                <span class="font-semibold text-gray-800">{{ $invoice->invoice_number }}</span>
                            </td>
                            
                            <td class="p-4 text-right text-gray-600">
                                <div class="flex items-center space-x-2 space-x-reverse">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>{{ $invoice->invoice_date->format('Y-m-d') }}</span>
                                </div>
                            </td>
                            
                            <td class="p-4 text-right">
                                <div class="flex items-center space-x-2 space-x-reverse">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                        {{ mb_substr($invoice->customer->name ?? 'غ', 0, 1) }}
                                    </div>
                                    <span class="font-medium text-gray-800">{{ $invoice->customer->name ?? 'غير محدد' }}</span>
                                </div>
                            </td>

                            <td class="p-4 text-right text-sm text-gray-600">
                                {{ $invoice->warehouse->name ?? '-' }}
                            </td>
                            
                            <td class="p-4 text-right">
                                <span class="font-bold text-gray-800">{{ number_format($invoice->calculated_details['net_total'] ?? 0, 2) }}</span>
                            </td>

                            <td class="p-4 text-right">
                                <span class="text-green-600 font-semibold">{{ number_format($invoice->calculated_details['paid'] ?? 0, 2) }}</span>
                            </td>

                            <td class="p-4 text-right">
                                <span class="text-blue-600 font-semibold">{{ number_format($invoice->calculated_details['remaining'] ?? 0, 2) }}</span>
                            </td>

                            <td class="p-4 text-right">
                                <div class="text-xs">
                                    <div class="font-bold text-purple-600">
                                        {{ number_format($invoice->calculated_details['total_profit'] ?? 0, 2) }}
                                    </div>
                                    <div class="text-gray-500">
                                        ({{ number_format($invoice->calculated_details['profit_margin'] ?? 0, 1) }}%)
                                    </div>
                                </div>
                            </td>
                            
                            <td class="p-4 text-right">
                                @php
                                    if ($invoice->status === 'cancelled') {
                                        $statusText = 'ملغاة';
                                        $statusClass = 'bg-red-100 text-red-800 border-red-200';
                                    } elseif ($invoice->payment_status === 'paid') {
                                        $statusText = 'مكتملة';
                                        $statusClass = 'bg-green-100 text-green-800 border-green-200';
                                    } else {
                                        $statusText = 'معلقة';
                                        $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                    }
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $statusClass }}">
                                    <span class="w-2 h-2 rounded-full bg-current mr-2"></span>
                                    {{ $statusText }}
                                </span>
                            </td>
                            
                            <td class="p-4">
                                <div class="flex items-center justify-center space-x-2 space-x-reverse">
                                    <!-- View -->
                                    <a href="{{ route('invoices.sales.show', $invoice->id) }}"
                                       class="inline-flex items-center px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-all duration-150 text-sm font-semibold"
                                       title="عرض">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    <!-- Edit -->
                                    @if($invoice->status !== 'cancelled')
                                    <a href="{{ route('invoices.sales.edit', $invoice->id) }}"
                                       class="inline-flex items-center px-3 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition-all duration-150 text-sm font-semibold"
                                       title="تعديل">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @endif

                                    <!-- Delete/Cancel -->
                                    @if($invoice->status !== 'cancelled' && $invoice->payment_status !== 'paid')
                                    <form action="{{ route('invoices.sales.destroy', $invoice->id) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('⚠️ هل أنت متأكد من إلغاء الفاتورة رقم {{ $invoice->invoice_number }}؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-all duration-150 text-sm font-semibold"
                                            title="إلغاء">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @else
                                    <span class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-400 rounded-lg text-sm font-semibold cursor-not-allowed" 
                                          title="{{ $invoice->status === 'cancelled' ? 'ملغاة' : 'لا يمكن إلغاء فاتورة مكتملة' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="p-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-4">
                                    <div class="bg-gray-100 rounded-full p-6">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-gray-600 font-semibold text-lg">لا توجد فواتير مبيعات</p>
                                        <p class="text-gray-400 text-sm mt-1">قم بإنشاء فاتورة جديدة للبدء</p>
                                    </div>
                                    <a href="{{ route('invoices.sales.create') }}" 
                                       class="flex items-center space-x-2 space-x-reverse bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-all duration-200 shadow-lg hover:shadow-xl font-semibold mt-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>إنشاء فاتورة جديدة</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($invoices->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        عرض 
                        <span class="font-semibold text-gray-800">{{ $invoices->firstItem() }}</span>
                        إلى
                        <span class="font-semibold text-gray-800">{{ $invoices->lastItem() }}</span>
                        من
                        <span class="font-semibold text-gray-800">{{ $invoices->total() }}</span>
                        نتيجة
                    </div>
                    <div>
                        {{ $invoices->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
<div class="fixed bottom-6 left-6 bg-green-500 text-white px-6 py-4 rounded-lg shadow-2xl flex items-center space-x-3 space-x-reverse animate-slide-up z-50">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span class="font-semibold">{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div class="fixed bottom-6 left-6 bg-red-500 text-white px-6 py-4 rounded-lg shadow-2xl flex items-center space-x-3 space-x-reverse animate-slide-up z-50">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span class="font-semibold">{{ session('error') }}</span>
</div>
@endif

@push('styles')
<style>
    @keyframes slide-up {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    .animate-slide-up {
        animation: slide-up 0.3s ease-out;
    }
</style>
@endpush
@endsection