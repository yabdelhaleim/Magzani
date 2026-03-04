@extends('layouts.app')

@section('title', 'عرض فاتورة مبيعات #' . $invoice->invoice_number)

@section('content')
<div class="container mx-auto px-4 py-6">
    
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">فاتورة مبيعات #{{ $invoice->invoice_number }}</h2>
            <p class="text-gray-600 mt-1">تاريخ: {{ $invoice->invoice_date->format('Y-m-d') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('invoices.sales.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                عودة للقائمة
            </a>
            <a href="{{ route('invoices.sales.edit', $invoice->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                تعديل
            </a>
            <button onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                طباعة
            </button>
        </div>
    </div>

    {{-- Invoice Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        
        {{-- Customer Info --}}
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-bold text-gray-700 mb-3 border-b pb-2">بيانات العميل</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">الاسم:</span>
                    <span class="font-semibold">{{ $invoice->customer->name ?? 'غير محدد' }}</span>
                </div>
                @if($invoice->customer && $invoice->customer->phone)
                <div class="flex justify-between">
                    <span class="text-gray-600">الهاتف:</span>
                    <span class="font-semibold">{{ $invoice->customer->phone }}</span>
                </div>
                @endif
                @if($invoice->customer && $invoice->customer->email)
                <div class="flex justify-between">
                    <span class="text-gray-600">البريد:</span>
                    <span class="font-semibold">{{ $invoice->customer->email }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Invoice Status --}}
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-bold text-gray-700 mb-3 border-b pb-2">حالة الفاتورة</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">الحالة:</span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        {{ $invoice->payment_status == 'paid' ? 'bg-green-100 text-green-800' : ($invoice->payment_status == 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ $invoice->payment_status == 'paid' ? 'مدفوعة بالكامل' : ($invoice->payment_status == 'partial' ? 'مدفوعة جزئياً' : 'غير مدفوعة') }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">تاريخ الإنشاء:</span>
                    <span class="font-semibold">{{ $invoice->created_at->format('Y-m-d H:i') }}</span>
                </div>
                @if($invoice->creator)
                <div class="flex justify-between">
                    <span class="text-gray-600">أنشأها:</span>
                    <span class="font-semibold">{{ $invoice->creator->name }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Financial Summary --}}
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-bold text-gray-700 mb-3 border-b pb-2">الملخص المالي</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">الإجمالي:</span>
                    <span class="font-semibold">{{ number_format($invoice->total, 2) }} جنيه</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">الخصم:</span>
                    <span class="font-semibold text-red-600">{{ number_format($invoice->discount_amount ?? 0, 2) }} جنيه</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">الضريبة:</span>
                    <span class="font-semibold text-blue-600">{{ number_format($invoice->tax_amount ?? 0, 2) }} جنيه</span>
                </div>
                <div class="flex justify-between border-t pt-2">
                    <span class="text-gray-600">المدفوع:</span>
                    <span class="font-semibold text-green-600">{{ number_format($invoice->paid ?? 0, 2) }} جنيه</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">الباقي:</span>
                    <span class="font-semibold {{ $invoice->remaining > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($invoice->remaining ?? 0, 2) }} جنيه
                    </span>
                </div>
                <div class="flex justify-between border-t pt-2 mt-2">
                    <span class="text-gray-800 font-bold">الصافي:</span>
                    <span class="font-bold text-lg text-green-600">{{ number_format($invoice->total, 2) }} جنيه</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Invoice Items Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 bg-gray-100 border-b">
            <h3 class="font-bold text-gray-700">تفاصيل الفاتورة</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-3 text-right text-sm font-semibold text-gray-700">#</th>
                        <th class="p-3 text-right text-sm font-semibold text-gray-700">المنتج</th>
                        <th class="p-3 text-right text-sm font-semibold text-gray-700">الوحدة</th>
                        <th class="p-3 text-right text-sm font-semibold text-gray-700">⚖️ الوزن</th>
                        <th class="p-3 text-right text-sm font-semibold text-gray-700">الكمية</th>
                        <th class="p-3 text-right text-sm font-semibold text-gray-700">السعر</th>
                        <th class="p-3 text-right text-sm font-semibold text-gray-700">الخصم</th>
                        <th class="p-3 text-right text-sm font-semibold text-gray-700">الضريبة</th>
                        <th class="p-3 text-right text-sm font-semibold text-gray-700">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $index => $item)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 text-right text-sm">{{ $index + 1 }}</td>
                        <td class="p-3 text-right">
                            <div class="font-semibold">{{ $item->product->name ?? 'منتج محذوف' }}</div>
                            @if($item->product && $item->product->sku)
                            <div class="text-xs text-gray-500">كود: {{ $item->product->sku }}</div>
                            @endif
                        </td>
                        {{-- ✅ الوحدة --}}
                        <td class="p-3 text-right text-sm">
                            @if($item->base_unit_label)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold
                                    {{ $item->base_unit_type === 'weight' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $item->base_unit_type === 'weight' ? '⚖️' : '📦' }}
                                    {{ $item->base_unit_label }}
                                </span>
                            @elseif($item->sellingUnit)
                                <span class="text-sm text-gray-600">{{ $item->sellingUnit->unit_label ?? $item->unit_code }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        {{-- ✅ الوزن --}}
                        <td class="p-3 text-right text-sm">
                            @if($item->weight && $item->weight > 0)
                                <span class="font-semibold text-amber-700">
                                    {{ number_format($item->weight, 3) }}
                                    {{ $item->base_unit_label ?? 'كجم' }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="p-3 text-right text-sm">{{ number_format($item->quantity, 2) }}</td>
                        <td class="p-3 text-right text-sm">{{ number_format($item->price, 2) }} جنيه</td>
                        <td class="p-3 text-right text-sm text-red-600">{{ number_format($item->discount ?? 0, 2) }} جنيه</td>
                        <td class="p-3 text-right text-sm text-blue-600">{{ number_format($item->tax ?? 0, 2) }} جنيه</td>
                        <td class="p-3 text-right text-sm font-semibold">{{ number_format($item->total, 2) }} جنيه</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-4 text-center text-gray-500">
                            لا توجد عناصر في هذه الفاتورة
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 border-t-2">
                    <tr>
                        <td colspan="8" class="p-3 text-left font-bold text-gray-700">الإجمالي:</td>
                        <td class="p-3 text-right font-bold text-lg text-green-600">
                            {{ number_format($invoice->total, 2) }} جنيه
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Payment Summary Card --}}
    <div class="bg-gradient-to-br from-blue-50 to-green-50 rounded-lg shadow-lg p-6 mt-6 border-2 border-green-200">
        <h3 class="font-bold text-gray-800 mb-4 text-lg">ملخص الدفع</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg p-4 shadow">
                <div class="text-gray-600 text-sm mb-1">إجمالي الفاتورة</div>
                <div class="text-2xl font-bold text-gray-800">{{ number_format($invoice->total, 2) }} <span class="text-sm">جنيه</span></div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow">
                <div class="text-gray-600 text-sm mb-1">المبلغ المدفوع</div>
                <div class="text-2xl font-bold text-green-600">{{ number_format($invoice->paid ?? 0, 2) }} <span class="text-sm">جنيه</span></div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow">
                <div class="text-gray-600 text-sm mb-1">المبلغ المتبقي</div>
                <div class="text-2xl font-bold {{ $invoice->remaining > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ number_format($invoice->remaining ?? 0, 2) }} <span class="text-sm">جنيه</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Payments Section (if exists) --}}
    @if($invoice->payments && $invoice->payments->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden mt-6">
        <div class="px-4 py-3 bg-gray-100 border-b">
            <h3 class="font-bold text-gray-700">المدفوعات</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-3 text-right text-sm font-semibold text-gray-700">التاريخ</th>
                        <th class="p-3 text-right text-sm font-semibold text-gray-700">المبلغ</th>
                        <th class="p-3 text-right text-sm font-semibold text-gray-700">طريقة الدفع</th>
                        <th class="p-3 text-right text-sm font-semibold text-gray-700">ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $payment)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 text-right text-sm">{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                        <td class="p-3 text-right text-sm font-semibold text-green-600">{{ number_format($payment->amount, 2) }} جنيه</td>
                        <td class="p-3 text-right text-sm">{{ $payment->payment_method ?? 'نقدي' }}</td>
                        <td class="p-3 text-right text-sm text-gray-600">{{ $payment->notes ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Notes Section --}}
    @if($invoice->notes)
    <div class="bg-white rounded-lg shadow p-4 mt-6">
        <h3 class="font-bold text-gray-700 mb-2">ملاحظات</h3>
        <p class="text-gray-600 text-sm">{{ $invoice->notes }}</p>
    </div>
    @endif

</div>

{{-- Print Styles --}}
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .container, .container * {
        visibility: visible;
    }
    .container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    button, a {
        display: none !important;
    }
}
</style>
@endsection