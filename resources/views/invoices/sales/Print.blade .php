@extends('layouts.app')

@section('title', 'طباعة فاتورة شراء')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Print Button --}}
    <div class="no-print mb-4 flex gap-3">
        <button onclick="window.print()" 
                class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
            <i class="fas fa-print ml-2"></i>
            طباعة
        </button>
        <a href="{{ route('invoices.purchases.show', $invoice->id) }}" 
           class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
            <i class="fas fa-arrow-right ml-2"></i>
            رجوع
        </a>
    </div>

    {{-- Invoice Print Layout --}}
    <div class="bg-white" id="invoice-print">
        {{-- Header --}}
        <div class="text-center mb-6 pb-4 border-b-2 border-gray-300">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">فاتورة شراء</h1>
            <p class="text-lg text-gray-600">{{ config('app.name', 'اسم الشركة') }}</p>
            <p class="text-sm text-gray-500">العنوان - رقم الهاتف - البريد الإلكتروني</p>
        </div>

        {{-- Invoice Info --}}
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <table class="w-full text-sm">
                    <tr>
                        <td class="font-bold text-gray-700 py-1">رقم الفاتورة:</td>
                        <td class="text-gray-900">{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold text-gray-700 py-1">التاريخ:</td>
                        <td class="text-gray-900">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold text-gray-700 py-1">المخزن:</td>
                        <td class="text-gray-900">{{ $invoice->warehouse->name }}</td>
                    </tr>
                </table>
            </div>
            <div>
                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <p class="font-bold text-gray-700 mb-2">بيانات المورد:</p>
                    <p class="text-gray-900 font-medium">{{ $invoice->supplier->name }}</p>
                    @if($invoice->supplier->phone)
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-phone ml-1"></i>
                            {{ $invoice->supplier->phone }}
                        </p>
                    @endif
                    @if($invoice->supplier->email)
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-envelope ml-1"></i>
                            {{ $invoice->supplier->email }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="mb-6">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-2 text-center text-sm font-bold">#</th>
                        <th class="border border-gray-300 px-4 py-2 text-right text-sm font-bold">الصنف</th>
                        <th class="border border-gray-300 px-4 py-2 text-center text-sm font-bold">الكمية</th>
                        <th class="border border-gray-300 px-4 py-2 text-center text-sm font-bold">سعر الوحدة</th>
                        <th class="border border-gray-300 px-4 py-2 text-center text-sm font-bold">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                        <tr>
                            <td class="border border-gray-300 px-4 py-2 text-center">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-4 py-2 text-right font-medium">
                                {{ $item->product->name }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-center">
                                {{ number_format($item->qty, 2) }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-center">
                                {{ number_format($item->price, 2) }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-center font-bold">
                                {{ number_format($item->total, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="flex justify-end mb-6">
            <div class="w-64">
                <table class="w-full text-sm">
                    <tr class="border-b border-gray-200">
                        <td class="py-2 font-medium text-gray-700">المجموع الفرعي:</td>
                        <td class="py-2 text-left font-bold">{{ number_format($invoice->subtotal, 2) }} جنيه</td>
                    </tr>
                    @if($invoice->discount > 0)
                        <tr class="border-b border-gray-200">
                            <td class="py-2 font-medium text-gray-700">الخصم:</td>
                            <td class="py-2 text-left font-bold text-red-600">- {{ number_format($invoice->discount, 2) }} جنيه</td>
                        </tr>
                    @endif
                    @if($invoice->tax > 0)
                        <tr class="border-b border-gray-200">
                            <td class="py-2 font-medium text-gray-700">الضريبة:</td>
                            <td class="py-2 text-left font-bold text-green-600">+ {{ number_format($invoice->tax, 2) }} جنيه</td>
                        </tr>
                    @endif
                    <tr class="bg-gray-100">
                        <td class="py-3 font-bold text-gray-900">الإجمالي النهائي:</td>
                        <td class="py-3 text-left font-bold text-xl text-purple-600">
                            {{ number_format($invoice->total, 2) }} جنيه
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Notes --}}
        @if($invoice->notes)
            <div class="mb-6">
                <p class="font-bold text-gray-700 mb-2">ملاحظات:</p>
                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                    <p class="text-gray-700">{{ $invoice->notes }}</p>
                </div>
            </div>
        @endif

        {{-- Footer --}}
        <div class="mt-8 pt-4 border-t border-gray-300">
            <div class="grid grid-cols-3 gap-4 text-center text-sm">
                <div>
                    <p class="font-bold text-gray-700 mb-1">المستلم</p>
                    <div class="border-t-2 border-gray-400 mt-8 pt-1">التوقيع</div>
                </div>
                <div>
                    <p class="font-bold text-gray-700 mb-1">المحاسب</p>
                    <div class="border-t-2 border-gray-400 mt-8 pt-1">التوقيع</div>
                </div>
                <div>
                    <p class="font-bold text-gray-700 mb-1">المدير</p>
                    <div class="border-t-2 border-gray-400 mt-8 pt-1">التوقيع</div>
                </div>
            </div>
        </div>

        {{-- Print Date --}}
        <div class="text-center mt-6 text-xs text-gray-500">
            تاريخ الطباعة: {{ now()->format('Y-m-d h:i A') }}
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        background: white;
    }
    
    #invoice-print {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 20px;
    }
    
    @page {
        size: A4;
        margin: 1cm;
    }
    
    table {
        page-break-inside: auto;
    }
    
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
}
</style>
@endpush
@endsection