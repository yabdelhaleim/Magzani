@extends('layouts.app')

@section('title', 'مرتجعات المبيعات')
@section('page-title', 'مرتجعات المبيعات')

@section('content')
<div class="max-w-7xl mx-auto">
    
    <!-- Header with Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- إجمالي المرتجعات -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">إجمالي المرتجعات</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $statistics['total_count'] ?? 0 }}</p>
                </div>
                <div class="bg-red-100 p-4 rounded-lg">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- مرتجعات اليوم -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">مرتجعات اليوم</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $statistics['today_count'] ?? 0 }}</p>
                </div>
                <div class="bg-blue-100 p-4 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- إجمالي القيمة -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">إجمالي القيمة</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($statistics['total_amount'] ?? 0, 2) }} ج.م</p>
                </div>
                <div class="bg-green-100 p-4 rounded-lg">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- إجمالي الأصناف -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">إجمالي الأصناف</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $statistics['total_items'] ?? 0 }}</p>
                </div>
                <div class="bg-purple-100 p-4 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Bar -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="text-2xl font-bold text-gray-800">قائمة المرتجعات</h2>
            
            <div class="flex gap-3">
                <a href="{{ route('invoices.sales-returns.create') }}" 
                   class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة مرتجع جديد
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <form method="GET" action="{{ route('invoices.sales-returns.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">رقم المرتجع</label>
                <input type="text" 
                       name="return_number" 
                       value="{{ request('return_number') }}"
                       placeholder="SR20260126..."
                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">من تاريخ</label>
                <input type="date" 
                       name="date_from" 
                       value="{{ request('date_from') }}"
                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" 
                       name="date_to" 
                       value="{{ request('date_to') }}"
                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" 
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                    بحث
                </button>
                <a href="{{ route('invoices.sales-returns.index') }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold">
                    مسح
                </a>
            </div>
        </form>
    </div>

    <!-- Returns Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                    <tr>
                        <th class="p-4 text-right text-sm font-bold text-gray-700">#</th>
                        <th class="p-4 text-right text-sm font-bold text-gray-700">رقم المرتجع</th>
                        <th class="p-4 text-right text-sm font-bold text-gray-700">رقم الفاتورة</th>
                        <th class="p-4 text-right text-sm font-bold text-gray-700">التاريخ</th>
                        <th class="p-4 text-right text-sm font-bold text-gray-700">العميل</th>
                        <th class="p-4 text-right text-sm font-bold text-gray-700">القيمة</th>
                        <th class="p-4 text-right text-sm font-bold text-gray-700">الحالة</th>
                        <th class="p-4 text-center text-sm font-bold text-gray-700">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($returns as $index => $return)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="p-4 text-right text-gray-600">{{ $returns->firstItem() + $index }}</td>
                        
                        <td class="p-4 text-right">
                            <span class="font-semibold text-red-600">{{ $return->return_number }}</span>
                        </td>
                        
                        <td class="p-4 text-right">
                            <span class="font-semibold text-blue-600">
                                {{ $return->salesInvoice->invoice_number ?? '-' }}
                            </span>
                        </td>
                        
                        <td class="p-4 text-right text-gray-600">
                            {{ $return->return_date->format('Y-m-d') }}
                        </td>
                        
                        <td class="p-4 text-right text-gray-600">
                            {{ $return->salesInvoice->customer->name ?? '-' }}
                        </td>
                        
                        <td class="p-4 text-right">
                            <span class="font-bold text-green-600">
                                {{ number_format($return->total, 2) }} ج.م
                            </span>
                        </td>

                        <td class="p-4 text-right">
                            @if($return->status === 'confirmed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    ✓ مؤكد
                                </span>
                            @elseif($return->status === 'cancelled')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                    ✗ ملغي
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                    ⊙ مسودة
                                </span>
                            @endif
                        </td>
                        
                        <td class="p-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('invoices.sales-returns.show', $return->id) }}" 
                                   class="bg-blue-100 hover:bg-blue-200 text-blue-600 p-2 rounded-lg transition-colors"
                                   title="عرض">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                
                                @if($return->status !== 'cancelled')
                                <form method="POST" 
                                      action="{{ route('invoices.sales-returns.destroy', $return->id) }}" 
                                      onsubmit="return confirm('هل أنت متأكد من إلغاء هذا المرتجع؟')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="bg-red-100 hover:bg-red-200 text-red-600 p-2 rounded-lg transition-colors"
                                            title="إلغاء">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-20 h-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-gray-500 text-lg font-semibold">لا توجد مرتجعات</p>
                                <p class="text-gray-400 text-sm mt-2">ابدأ بإضافة مرتجع مبيعات جديد</p>
                                <a href="{{ route('invoices.sales-returns.create') }}" 
                                   class="mt-4 bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold">
                                    إضافة مرتجع
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($returns->hasPages())
        <div class="p-6 border-t border-gray-200">
            {{ $returns->links() }}
        </div>
        @endif
    </div>

</div>
@endsection