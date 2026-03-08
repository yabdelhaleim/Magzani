@extends('layouts.app')

@section('page-title', 'مرتجعات المشتريات')

@section('content')
<div class="space-y-6 p-1 sm:p-0">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-800">مرتجعات المشتريات</h2>
            <p class="text-gray-500 mt-1 text-sm md:text-base">إدارة ومتابعة جميع مرتجعات المشتريات من الموردين</p>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('invoices.purchase-returns.create') }}" 
               class="flex-1 sm:flex-none bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-5 py-2.5 rounded-lg font-semibold transition shadow-md text-sm">
                <i class="fas fa-plus ml-2"></i>
                إنشاء مرتجع جديد
            </a>
            <div class="bg-gradient-to-r from-red-500 to-pink-600 text-white px-4 py-3 rounded-xl shadow-lg">
                <div class="flex items-center gap-3 justify-center">
                    <i class="fas fa-undo-alt text-xl opacity-80"></i>
                    <div class="text-right">
                        <p class="text-[10px] opacity-90">إجمالي</p>
                        <p class="text-lg font-bold">{{ $statistics['total_returns'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="{{ route('invoices.purchase-returns.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
            
            <!-- Search Input -->
            <div class="lg:col-span-4">
                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-search ml-1 text-gray-400"></i>
                    البحث
                </label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm"
                       placeholder="ابحث برقم المرتجع أو اسم المورد...">
            </div>
            
            <!-- Status Filter -->
            <div class="lg:col-span-3">
                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-filter ml-1 text-gray-400"></i>
                    الحالة
                </label>
                <select name="status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                    <option value="">كل الحالات</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>مؤكد</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغى</option>
                </select>
            </div>
            
            <!-- Date Input -->
            <div class="lg:col-span-3">
                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar ml-1 text-gray-400"></i>
                    التاريخ
                </label>
                <input type="date" 
                       name="date" 
                       value="{{ request('date') }}" 
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
            </div>
            
            <!-- Buttons -->
            <div class="lg:col-span-2 flex items-end gap-2 sm:gap-3">
                <button type="submit" 
                        class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2.5 rounded-lg font-semibold transition shadow-md text-sm">
                    <i class="fas fa-filter ml-2"></i>
                    تصفية
                </button>
                <a href="{{ route('invoices.purchase-returns.index') }}" 
                   class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg font-semibold transition text-center text-sm">
                    <i class="fas fa-redo ml-2"></i>
                    إعادة
                </a>
            </div>
        </form>
    </div>

    <!-- ======================= -->
    <!-- عرض الجدول (للأجهزة المتوسطة والكبيرة) -->
    <!-- ======================= -->
    <div class="hidden md:block bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">#</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-file-invoice ml-1"></i> رقم المرتجع
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-file-invoice ml-1"></i> الفاتورة
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-truck ml-1"></i> المورد
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-calendar-day ml-1"></i> التاريخ
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-money-bill ml-1"></i> الإجمالي
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-info-circle ml-1"></i> الحالة
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($returns as $index => $return)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 text-gray-700 rounded-full text-sm font-semibold">
                                    {{ $returns->firstItem() + $index }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-800">{{ $return->return_number }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($return->purchaseInvoice)
                                <a href="{{ route('invoices.purchases.show', $return->purchaseInvoice->id) }}" 
                                   class="flex items-center gap-2 text-blue-600 hover:text-blue-800 font-semibold">
                                    <i class="fas fa-file-invoice"></i>
                                    {{ $return->purchaseInvoice->invoice_number }}
                                </a>
                                @else
                                <span class="text-gray-400">---</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center text-white font-bold shadow-md">
                                        {{ mb_substr($return->supplier->name ?? '?', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $return->supplier->name ?? 'غير محدد' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2 text-gray-600">
                                    <i class="fas fa-calendar text-blue-500"></i>
                                    <span class="text-sm font-medium">{{ $return->return_date->format('Y-m-d') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-left">
                                <span class="font-bold text-red-600">{{ number_format($return->total, 2) }}</span>
                                <span class="text-gray-500 text-sm">ج.م</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @switch($return->status)
                                    @case('draft')
                                        <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-full text-sm font-bold">
                                            <i class="fas fa-edit text-xs"></i>
                                            مسودة
                                        </span>
                                        @break
                                    @case('confirmed')
                                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm font-bold">
                                            <i class="fas fa-check text-xs"></i>
                                            مؤكد
                                        </span>
                                        @break
                                    @case('cancelled')
                                        <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 px-4 py-2 rounded-full text-sm font-bold">
                                            <i class="fas fa-times text-xs"></i>
                                            ملغى
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('invoices.purchase-returns.show', $return->id) }}" 
                                   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition shadow-md">
                                    <i class="fas fa-eye"></i>
                                    عرض
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-inbox text-5xl text-gray-300"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-800 mb-2">لا توجد بيانات</h3>
                                    <p class="text-gray-500">لم يتم العثور على أي مرتجعات مشتريات</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($returns->hasPages())
            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-600 order-2 sm:order-1">
                        عرض <span class="font-semibold text-gray-800">{{ $returns->firstItem() ?? 0 }}</span> 
                        إلى <span class="font-semibold text-gray-800">{{ $returns->lastItem() ?? 0 }}</span> 
                        من أصل <span class="font-semibold text-gray-800">{{ $returns->total() }}</span> مرتجع
                    </div>
                    <div class="order-1 sm:order-2">
                        {{ $returns->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- ======================= -->
    <!-- عرض البطاقات (للموبايل فقط) -->
    <!-- ======================= -->
    <div class="block md:hidden space-y-4">
        @forelse($returns as $return)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- هيدر البطاقة -->
            <div class="bg-gray-50 p-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center text-white font-bold shadow-sm">
                            {{ mb_substr($return->supplier->name ?? '?', 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-gray-800 text-sm">{{ $return->return_number }}</p>
                            <p class="text-xs text-gray-500">{{ $return->supplier->name ?? 'غير محدد' }}</p>
                        </div>
                    </div>
                    @switch($return->status)
                        @case('draft')
                            <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold">مسودة</span>
                            @break
                        @case('confirmed')
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">مؤكد</span>
                            @break
                        @case('cancelled')
                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">ملغى</span>
                            @break
                    @endswitch
                </div>
            </div>

            <!-- تفاصيل البطاقة -->
            <div class="p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500 text-xs flex items-center gap-1"><i class="fas fa-file-invoice"></i> الفاتورة:</span>
                    @if($return->purchaseInvoice)
                    <a href="{{ route('invoices.purchases.show', $return->purchaseInvoice->id) }}" 
                       class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                        {{ $return->purchaseInvoice->invoice_number }}
                    </a>
                    @else
                    <span class="text-gray-400 text-sm">---</span>
                    @endif
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500 text-xs flex items-center gap-1"><i class="fas fa-calendar"></i> التاريخ:</span>
                    <span class="font-semibold text-gray-800 text-sm">{{ $return->return_date->format('Y-m-d') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500 text-xs flex items-center gap-1"><i class="fas fa-money-bill"></i> الإجمالي:</span>
                    <span class="font-bold text-red-600 text-sm">{{ number_format($return->total, 2) }} ج.م</span>
                </div>
                @if($return->return_reason)
                <div class="flex items-start gap-2 bg-yellow-50 p-2 rounded-lg">
                    <i class="fas fa-comment-alt text-yellow-600 mt-0.5"></i>
                    <p class="text-xs text-gray-700 flex-1">{{ Str::limit($return->return_reason, 100) }}</p>
                </div>
                @endif
            </div>

            <!-- إجراءات البطاقة -->
            <div class="px-4 pb-4">
                <a href="{{ route('invoices.purchase-returns.show', $return->id) }}" 
                   class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-md">
                    <i class="fas fa-eye"></i>
                    عرض التفاصيل
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
            <h3 class="text-lg font-bold text-gray-800">لا توجد بيانات</h3>
            <p class="text-gray-500 text-sm">لم يتم العثور على مرتجعات</p>
        </div>
        @endforelse

        <!-- Pagination Mobile -->
        @if($returns->hasPages())
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex justify-center">
                    {{ $returns->withQueryString()->links() }}
                </div>
                <p class="text-center text-xs text-gray-500 mt-2">
                    إجمالي النتائج: {{ $returns->total() }}
                </p>
            </div>
        @endif
    </div>

</div>
@endsection
