@extends('layouts.app')

@section('title', 'تفاصيل مرتجع الشراء')
@section('page-title', 'تفاصيل مرتجع الشراء')

@section('content')
<div class="max-w-6xl mx-auto">
    
    <!-- Header Card -->
    <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl shadow-lg p-6 mb-6 text-white">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold mb-2">{{ $purchaseReturn->return_number }}</h2>
                <p class="text-red-100">تاريخ المرتجع: {{ $purchaseReturn->return_date ? $purchaseReturn->return_date->format('Y-m-d') : '-' }}</p>
            </div>
            <div class="text-left">
                @if($purchaseReturn->status === 'confirmed')
                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-green-500 text-white">
                        ✓ مؤكد
                    </span>
                @elseif($purchaseReturn->status === 'cancelled')
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
                    <span class="font-semibold text-blue-600">{{ $purchaseReturn->purchaseInvoice->invoice_number ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">تاريخ الفاتورة:</span>
                    <span class="font-semibold">{{ $purchaseReturn->purchaseInvoice && $purchaseReturn->purchaseInvoice->invoice_date ? $purchaseReturn->purchaseInvoice->invoice_date->format('Y-m-d') : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">المخزن:</span>
                    <span class="font-semibold">{{ $purchaseReturn->purchaseInvoice->warehouse->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Supplier Info -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                بيانات المورد
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">اسم المورد:</span>
                    <span class="font-semibold">{{ $purchaseReturn->purchaseInvoice->supplier->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">رقم الهاتف:</span>
                    <span class="font-semibold">{{ $purchaseReturn->purchaseInvoice->supplier->phone ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">العنوان:</span>
                    <span class="font-semibold text-sm">{{ $purchaseReturn->purchaseInvoice->supplier->address ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            الملخص المالي
        </h3>
        <div class="grid md:grid-cols-4 gap-4">
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <p class="text-gray-500 text-sm">المجموع الفرعي</p>
                <p class="text-xl font-bold text-gray-800">{{ number_format($purchaseReturn->subtotal, 2) }} ج.م</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <p class="text-gray-500 text-sm">الخصم</p>
                <p class="text-xl font-bold text-red-600">{{ number_format($purchaseReturn->discount_amount, 2) }} ج.م</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <p class="text-gray-500 text-sm">الضريبة</p>
                <p class="text-xl font-bold text-blue-600">{{ number_format($purchaseReturn->tax_amount, 2) }} ج.م</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <p class="text-gray-500 text-sm">الإجمالي</p>
                <p class="text-xl font-bold text-green-600">{{ number_format($purchaseReturn->total, 2) }} ج.م</p>
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
                    @forelse($purchaseReturn->items as $index => $item)
                        <tr class="hover:bg-gray50 transition-colors">
                            <td class="p-4 text-gray-600">{{ $index + 1 }}</td>
                            <td class="p-4 font-semibold text-gray-800">
                                {{ $item->purchaseInvoiceItem->product->name ?? $item->product->name ?? '-' }}
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-semibold">
                                    <i class="fas fa-undo text-xs"></i>
                                    {{ $item->quantity }}
                                </span>
                            </td>
                            <td class="p-4 text-gray-600">{{ number_format($item->unit_price, 2) }} ج.م</td>
                            <td class="p-4 font-semibold text-gray-800">{{ number_format($item->total, 2) }} ج.م</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500">
                                لا توجد أصناف مرتجعة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 border-t">
                    <tr>
                        <td colspan="4" class="p-4 text-right font-bold text-gray-800">الإجمالي:</td>
                        <td class="p-4 font-bold text-xl text-green-600">{{ number_format($purchaseReturn->total, 2) }} ج.م</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Notes -->
    @if($purchaseReturn->return_reason || $purchaseReturn->notes)
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            الملاحظات
        </h3>
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-gray-700 whitespace-pre-line">{{ $purchaseReturn->return_reason ?? $purchaseReturn->notes }}</p>
        </div>
    </div>
    @endif

    <!-- Created By Info -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="grid md:grid-cols-3 gap-6 text-sm text-gray-600">
            <div>
                <span class="font-semibold">تم الإنشاء بواسطة:</span>
                <p class="text-gray-800 mt-1">{{ $purchaseReturn->creator->name ?? '-' }}</p>
            </div>
            <div>
                <span class="font-semibold">تاريخ الإنشاء:</span>
                <p class="text-gray-800 mt-1">{{ $purchaseReturn->created_at ? $purchaseReturn->created_at->format('Y-m-d H:i') : '-' }}</p>
            </div>
            @if($purchaseReturn->confirmed_by)
            <div>
                <span class="font-semibold">تم التأكيد بواسطة:</span>
                <p class="text-gray-800 mt-1">{{ $purchaseReturn->confirmer->name ?? '-' }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-3">
        <a href="{{ route('invoices.purchase-returns.index') }}" 
           class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-semibold text-center">
            رجوع للقائمة
        </a>
        
        @if($purchaseReturn->status !== 'cancelled')
        <form method="POST" 
              action="{{ route('invoices.purchase-returns.destroy', $purchaseReturn->id) }}" 
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
