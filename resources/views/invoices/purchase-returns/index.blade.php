@extends('layouts.app')

@section('page-title', 'مرتجعات المشتريات')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">مرتجعات المشتريات</h2>
            <p class="text-gray-500 mt-1">إدارة ومتابعة جميع مرتجعات المشتريات من الموردين</p>
        </div>
        <div class="bg-gradient-to-r from-red-500 to-pink-600 text-white px-6 py-3 rounded-xl shadow-lg">
            <div class="flex items-center gap-2">
                <i class="fas fa-undo-alt"></i>
                <div>
                    <p class="text-xs opacity-90">إجمالي المرتجعات</p>
                    <p class="text-2xl font-bold">{{ $returns->total() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('invoices.purchase-returns.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            
            <!-- Search Input -->
            <div class="md:col-span-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-search ml-1 text-gray-400"></i>
                    البحث
                </label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                       placeholder="ابحث باسم المورد أو الصنف...">
            </div>
            
            <!-- Date Input -->
            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar ml-1 text-gray-400"></i>
                    التاريخ
                </label>
                <input type="date" 
                       name="date" 
                       value="{{ request('date') }}" 
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>
            
            <!-- Buttons -->
            <div class="md:col-span-4 flex items-end gap-2">
                <button type="submit" 
                        class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-2.5 rounded-lg font-semibold transition hover-scale shadow-md">
                    <i class="fas fa-filter ml-2"></i>
                    تصفية
                </button>
                <a href="{{ route('invoices.purchase-returns.index') }}" 
                   class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg font-semibold transition text-center">
                    <i class="fas fa-redo ml-2"></i>
                    إعادة تعيين
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            #
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-truck ml-1"></i>
                            المورد
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-box ml-1"></i>
                            الصنف
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-cubes ml-1"></i>
                            الكمية المرتجعة
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-comment-alt ml-1"></i>
                            سبب الإرجاع
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-calendar-day ml-1"></i>
                            تاريخ الإرجاع
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                            الإجراءات
                        </th>
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
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center text-white font-bold shadow-md">
                                        {{ mb_substr($return->purchaseInvoice->supplier->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $return->purchaseInvoice->supplier->name }}</p>
                                        <p class="text-xs text-gray-500">مورد معتمد</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-tag text-gray-400"></i>
                                    <span class="font-medium text-gray-700">{{ $return->item_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 px-4 py-2 rounded-full text-sm font-bold">
                                    <i class="fas fa-undo text-xs"></i>
                                    {{ $return->quantity }} وحدة
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-600 line-clamp-2">
                                    {{ Str::limit($return->reason, 50) }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2 text-gray-600">
                                    <i class="fas fa-calendar text-blue-500"></i>
                                    <span class="text-sm font-medium">{{ $return->created_at->format('Y-m-d') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('invoices.purchase-returns.show', $return->id) }}" 
                                   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition hover-scale shadow-md">
                                    <i class="fas fa-eye"></i>
                                    عرض التفاصيل
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
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
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        عرض <span class="font-semibold text-gray-800">{{ $returns->firstItem() ?? 0 }}</span> 
                        إلى <span class="font-semibold text-gray-800">{{ $returns->lastItem() ?? 0 }}</span> 
                        من أصل <span class="font-semibold text-gray-800">{{ $returns->total() }}</span> مرتجع
                    </div>
                    <div>
                        {{ $returns->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush
@endsection