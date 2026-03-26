@extends('layouts.app')

@section('title', 'فواتير المشتريات')

@section('content')
<div class="py-4 md:py-8 px-2 md:px-4">
    {{-- رسائل النجاح والخطأ --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-500 to-pink-500 px-4 md:px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="text-xl md:text-2xl font-bold text-white">فواتير المشتريات</h2>
            <a href="{{ route('invoices.purchases.create') }}" 
               class="px-4 py-2 bg-white text-purple-600 rounded-lg hover:bg-purple-50 transition text-sm">
                <i class="fas fa-plus ml-2"></i>
                <span class="hidden sm:inline">إضافة فاتورة جديدة</span>
            </a>
        </div>

        <div class="p-4 md:p-6">
            {{-- Statistics Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-4 md:mb-6">
                <div class="bg-gradient-to-br from-blue-50 to-cyan-100 p-3 md:p-4 rounded-lg border-r-2 md:border-r-4 border-blue-500">
                    <p class="text-xs md:text-sm text-gray-600">إجمالي الفواتير</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900 mt-1">{{ $statistics['total_invoices'] }}</p>
                </div>
                
                <div class="bg-gradient-to-br from-purple-50 to-pink-100 p-3 md:p-4 rounded-lg border-r-2 md:border-r-4 border-purple-500">
                    <p class="text-xs md:text-sm text-gray-600">إجمالي المشتريات</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900 mt-1">{{ number_format($statistics['total_amount']) }}</p>
                    <p class="text-xs text-gray-500 hidden md:block">جنيه</p>
                </div>
                
                <div class="bg-gradient-to-br from-red-50 to-orange-100 p-3 md:p-4 rounded-lg border-r-2 md:border-r-4 border-red-500">
                    <p class="text-xs md:text-sm text-gray-600">فواتير معلقة</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900 mt-1">{{ $statistics['pending_invoices'] }}</p>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-3 md:p-4 rounded-lg border-r-2 md:border-r-4 border-green-500">
                    <p class="text-xs md:text-sm text-gray-600">مشتريات اليوم</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900 mt-1">{{ number_format($statistics['today_amount']) }}</p>
                    <p class="text-xs text-gray-500 hidden md:block">جنيه</p>
                </div>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('invoices.purchases.index') }}" class="mb-4 md:mb-6">
                <div class="flex flex-col md:flex-row gap-2 md:gap-4">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="بحث برقم الفاتورة..."
                           class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    
                    <input type="date" 
                           name="date_from" 
                           value="{{ request('date_from') }}"
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    
                    <input type="date" 
                           name="date_to" 
                           value="{{ request('date_to') }}"
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    
                    <select name="status" 
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">جميع الحالات</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلقة</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                    </select>

                    <button type="submit" 
                            class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-search ml-2"></i>
                        بحث
                    </button>

                    <a href="{{ route('invoices.purchases.index') }}" 
                       class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-redo ml-2"></i>
                        إعادة تعيين
                    </a>

                    <a href="{{ route('invoices.purchases.export', request()->all()) }}" 
                       class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-file-excel ml-2"></i>
                        تصدير Excel
                    </a>
                </div>
            </form>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم الفاتورة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المورد</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المخزن</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجمالي</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($invoices as $invoice)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm font-medium text-purple-600">
                                    {{ $invoice->invoice_number }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $invoice->invoice_date->format('Y-m-d') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $invoice->supplier->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $invoice->warehouse->name }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                    {{ number_format($invoice->total, 2) }} جنيه
                                </td>
                                <td class="px-6 py-4">
                                    @if($invoice->status == 'paid')
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                            مدفوعة
                                        </span>
                                    @elseif($invoice->status == 'pending')
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                            معلقة
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                            ملغاة
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex space-x-2 space-x-reverse">
                                        <a href="{{ route('invoices.purchases.show', $invoice->id) }}" 
                                           class="text-blue-600 hover:text-blue-900 transition"
                                           title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('invoices.purchases.edit', $invoice->id) }}" 
                                           class="text-green-600 hover:text-green-900 transition"
                                           title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('invoices.purchases.print', $invoice->id) }}" 
                                           class="text-purple-600 hover:text-purple-900 transition"
                                           title="طباعة"
                                           target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <a href="{{ route('invoices.purchases.export.single', $invoice->id) }}" 
                                           class="text-teal-600 hover:text-teal-900 transition"
                                           title="تصدير Excel">
                                            <i class="fas fa-file-excel"></i>
                                        </a>
                                        <form action="{{ route('invoices.purchases.destroy', $invoice->id) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900 transition"
                                                    title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3"></i>
                                    <p>لا توجد فواتير</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</div>
@endsection