@extends('layouts.app')

@section('title', 'تفاصيل مرتجع المبيعات')
@section('page-title', 'تفاصيل مرتجع المبيعات')

@section('content')
<div class="max-w-6xl mx-auto">
    
    <!-- Header Card -->
    <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl shadow-lg p-6 mb-6 text-white">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold mb-2">{{ $salesReturn->return_number }}</h2>
                <p class="text-red-100">تاريخ المرتجع: {{ $salesReturn->return_date ? $salesReturn->return_date->format('Y-m-d') : '-' }}</p>
            </div>
            <div class="text-left">
                @if($salesReturn->status === 'confirmed')
                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-green-500 text-white">
                        ✓ مؤكد
                    </span>
                @elseif($salesReturn->status === 'cancelled')
                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-gray-500 text-white">
                        ✗ ملغي
                    </span>
                @else
                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-yellow-500 text-white">
                        ⊙ مسودة
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Info Grid -->
    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <!-- Invoice Info -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                بيانات الفاتورة
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">رقم الفاتورة:</span>
                    <span class="font-semibold text-blue-600">{{ $salesReturn->salesInvoice->invoice_number ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">تاريخ الفاتورة:</span>
                    <span class="font-semibold">{{ $salesReturn->salesInvoice && $salesReturn->salesInvoice->invoice_date ? $salesReturn->salesInvoice->invoice_date->format('Y-m-d') : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">المخزن:</span>
                    <span class="font-semibold">{{ $salesReturn->salesInvoice->warehouse->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                بيانات العميل
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">اسم العميل:</span>
                    <span class="font-semibold">{{ $salesReturn->salesInvoice->customer->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">رقم الهاتف:</span>
                    <span class="font-semibold">{{ $salesReturn->salesInvoice->customer->phone ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">العنوان:</span>
                    <span class="font-semibold text-sm">{{ $salesReturn->salesInvoice->customer->address ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
            <h3 class="text-lg font-bold text-white">الأصناف المرتجعة</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-4 text-right text-sm font-semibold text-gray-700">#</th>
                        <th class="p-4 text-right text-sm font-semibold text-gray-700">اسم الصنف</th>
                        <th class="p-4 text-right text-sm font-semibold text-gray-700">الكمية المرتجعة</th>
                        <th class="p-4 text-right text-sm font-semibold text-gray-700">سعر الوحدة</th>
                        <th class="p-4 text-right text-sm font-semibold text-gray-700">الإجمالي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($salesReturn->items as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="p-4 text-gray-600">{{ $index + 1 }}</td>
                        <td class="p-4">
                            <div class="font-semibold text-gray-800">{{ $item->product->name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $item->product->sku ?? '' }}</div>
                        </td>
                        <td class="p-4 text-gray-700">
                            <span class="font-semibold">{{ number_format($item->quantity_returned, 3) }}</span>
                            <span class="text-gray-500 text-sm">{{ $item->product->unit ?? '' }}</span>
                        </td>
                        <td class="p-4 text-gray-700">{{ number_format($item->unit_price, 2) }} ج.م</td>
                        <td class="p-4 font-bold text-green-600">{{ number_format($item->total, 2) }} ج.م</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t-2">
                    <tr>
                        <td colspan="4" class="p-4 text-right font-bold text-gray-800">الإجمالي:</td>
                        <td class="p-4 font-bold text-xl text-green-600">{{ number_format($salesReturn->total, 2) }} ج.م</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Notes -->
    @if($salesReturn->return_reason || $salesReturn->notes)
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            الملاحظات
        </h3>
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-gray-700 whitespace-pre-line">{{ $salesReturn->return_reason ?? $salesReturn->notes }}</p>
        </div>
    </div>
    @endif

    <!-- Created By Info -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="grid md:grid-cols-3 gap-6 text-sm text-gray-600">
            <div>
                <span class="font-semibold">تم الإنشاء بواسطة:</span>
                <p class="text-gray-800 mt-1">{{ $salesReturn->creator->name ?? '-' }}</p>
            </div>
            <div>
                <span class="font-semibold">تاريخ الإنشاء:</span>
                <p class="text-gray-800 mt-1">{{ $salesReturn->created_at ? $salesReturn->created_at->format('Y-m-d H:i') : '-' }}</p>
            </div>
            @if($salesReturn->confirmed_by)
            <div>
                <span class="font-semibold">تم التأكيد بواسطة:</span>
                <p class="text-gray-800 mt-1">{{ $salesReturn->confirmer->name ?? '-' }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-3">
        <a href="{{ route('invoices.sales-returns.index') }}" 
           class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-semibold text-center">
            رجوع للقائمة
        </a>
        
        @if($salesReturn->status !== 'cancelled')
        <form method="POST" 
              action="{{ route('invoices.sales-returns.destroy', $salesReturn->id) }}" 
              onsubmit="return confirm('هل أنت متأكد من إلغاء هذا المرتجع؟')"
              class="flex-1">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold">
                إلغاء المرتجع
            </button>
        </form>
        @endif
    </div>

</div>
@endsection