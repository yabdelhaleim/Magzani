@extends('layouts.app')

@section('title', 'تفاصيل فاتورة شراء')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- رسائل النجاح --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-500 to-pink-500 px-6 py-4 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">تفاصيل فاتورة الشراء</h2>
            <div class="flex gap-2">
                <a href="{{ route('invoices.purchases.edit', $invoice->id) }}" 
                   class="px-4 py-2 bg-white text-purple-600 rounded-lg hover:bg-purple-50 transition">
                    <i class="fas fa-edit ml-2"></i>
                    تعديل
                </a>
                <button onclick="window.print()" 
                        class="px-4 py-2 bg-white text-purple-600 rounded-lg hover:bg-purple-50 transition">
                    <i class="fas fa-print ml-2"></i>
                    طباعة
                </button>
            </div>
        </div>

        <div class="p-6">
            {{-- Invoice Header Info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gray-50 p-4 rounded-lg border-r-4 border-purple-500">
                    <p class="text-sm text-gray-600 mb-1">رقم الفاتورة</p>
                    <p class="text-lg font-bold text-purple-600">{{ $invoice->invoice_number }}</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg border-r-4 border-blue-500">
                    <p class="text-sm text-gray-600 mb-1">المورد</p>
                    <p class="text-lg font-bold text-gray-900">{{ $invoice->supplier->name }}</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg border-r-4 border-green-500">
                    <p class="text-sm text-gray-600 mb-1">المخزن</p>
                    <p class="text-lg font-bold text-gray-900">{{ $invoice->warehouse->name }}</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg border-r-4 border-orange-500">
                    <p class="text-sm text-gray-600 mb-1">التاريخ</p>
                    <p class="text-lg font-bold text-gray-900">{{ $invoice->invoice_date->format('Y-m-d') }}</p>
                </div>
            </div>

            {{-- Status Badge --}}
            <div class="mb-6">
                <span class="text-sm text-gray-600 ml-2">الحالة:</span>
                @if($invoice->status == 'paid')
                    <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800 font-medium">
                        <i class="fas fa-check-circle ml-1"></i>
                        مدفوعة
                    </span>
                @elseif($invoice->status == 'pending')
                    <span class="px-3 py-1 text-sm rounded-full bg-yellow-100 text-yellow-800 font-medium">
                        <i class="fas fa-clock ml-1"></i>
                        معلقة
                    </span>
                @else
                    <span class="px-3 py-1 text-sm rounded-full bg-red-100 text-red-800 font-medium">
                        <i class="fas fa-times-circle ml-1"></i>
                        ملغاة
                    </span>
                @endif
            </div>

            {{-- Items Table --}}
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">الأصناف</h3>
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="w-full text-right">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-sm font-medium text-gray-700">#</th>
                                <th class="px-4 py-3 text-sm font-medium text-gray-700">الصنف</th>
                                <th class="px-4 py-3 text-sm font-medium text-gray-700">الكمية</th>
                                <th class="px-4 py-3 text-sm font-medium text-gray-700">سعر الوحدة</th>
                                <th class="px-4 py-3 text-sm font-medium text-gray-700">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($invoice->items as $index => $item)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ $item->product->name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ number_format($item->qty, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ number_format($item->price, 2) }} جنيه
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-gray-900">
                                        {{ number_format($item->total, 2) }} جنيه
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Totals Summary --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Notes --}}
                <div>
                    @if($invoice->notes)
                        <h3 class="text-lg font-bold text-gray-800 mb-3">الملاحظات</h3>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <p class="text-gray-700">{{ $invoice->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Financial Summary --}}
                <div>
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-6 rounded-lg border border-purple-200">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center pb-2 border-b border-purple-200">
                                <span class="text-gray-700">المجموع الفرعي:</span>
                                <span class="font-bold text-gray-900">{{ number_format($invoice->subtotal, 2) }} جنيه</span>
                            </div>

                            @if($invoice->discount > 0)
                                <div class="flex justify-between items-center pb-2 border-b border-purple-200">
                                    <span class="text-gray-700">الخصم:</span>
                                    <span class="font-bold text-red-600">- {{ number_format($invoice->discount, 2) }} جنيه</span>
                                </div>
                            @endif

                            @if($invoice->tax > 0)
                                <div class="flex justify-between items-center pb-2 border-b border-purple-200">
                                    <span class="text-gray-700">الضريبة:</span>
                                    <span class="font-bold text-green-600">+ {{ number_format($invoice->tax, 2) }} جنيه</span>
                                </div>
                            @endif

                            <div class="flex justify-between items-center pt-2">
                                <span class="text-lg font-bold text-gray-900">الإجمالي النهائي:</span>
                                <span class="text-2xl font-bold text-purple-600">
                                    {{ number_format($invoice->total, 2) }} جنيه
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Metadata --}}
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-sm text-gray-600">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <p>
                        <i class="fas fa-calendar-plus ml-2"></i>
                        <strong>تاريخ الإنشاء:</strong> 
                        {{ $invoice->created_at->format('Y-m-d h:i A') }}
                    </p>
                    <p>
                        <i class="fas fa-calendar-edit ml-2"></i>
                        <strong>آخر تحديث:</strong> 
                        {{ $invoice->updated_at->format('Y-m-d h:i A') }}
                    </p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 mt-6">
                <a href="{{ route('invoices.purchases.index') }}" 
                   class="px-6 py-3 bg-gray-500 text-white font-medium rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-arrow-right ml-2"></i>
                    رجوع للقائمة
                </a>

                <form action="{{ route('invoices.purchases.destroy', $invoice->id) }}" 
                      method="POST" 
                      class="inline"
                      onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟ سيتم تقليل الكميات من المخزن.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-6 py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-trash ml-2"></i>
                        حذف الفاتورة
                    </button>
                </form>
            </div>
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
}
</style>
@endpush
@endsection