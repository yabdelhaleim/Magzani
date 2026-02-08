@extends('layouts.app')

@section('title', 'كشف حساب العميل')
@section('page-title', 'كشف حساب العميل')

@section('content')
<div class="max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($customer->name) }}&size=80"
                     class="w-20 h-20 rounded-full">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $customer->name }}</h2>
                    <p class="text-gray-600">
                        كود العميل: C-{{ str_pad($customer->id, 3, '0', STR_PAD_LEFT) }}
                    </p>
                    <div class="flex items-center gap-4 mt-2 text-sm">
                        <span class="text-gray-600">
                            <i class="fas fa-phone ml-1 text-blue-600"></i>{{ $customer->phone ?? '-' }}
                        </span>
                        <span class="text-gray-600">
                            <i class="fas fa-envelope ml-1 text-blue-600"></i>{{ $customer->email ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button onclick="window.print()"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-print ml-2"></i> طباعة
                </button>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    @php
        $totalInvoices = $customer->salesInvoices->sum('total');
        $totalPaid     = $customer->salesInvoices->sum('paid');
        $balance       = $totalInvoices - $totalPaid;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-600 mb-1">إجمالي المشتريات</p>
            <h3 class="text-2xl font-bold">{{ number_format($totalInvoices) }} ج.م</h3>
            <p class="text-xs text-gray-500 mt-2">
                {{ $customer->salesInvoices->count() }} فاتورة
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-600 mb-1">المبلغ المدفوع</p>
            <h3 class="text-2xl font-bold text-green-600">
                {{ number_format($totalPaid) }} ج.م
            </h3>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-600 mb-1">المتأخرات</p>
            <h3 class="text-2xl font-bold text-orange-600">
                {{ number_format(max($balance, 0)) }} ج.م
            </h3>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm text-gray-600 mb-1">الرصيد</p>
            <h3 class="text-2xl font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ number_format($balance) }} ج.م
            </h3>
        </div>
    </div>

    {{-- Statement Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b">
            <h3 class="text-lg font-bold text-gray-800">سجل الحركات</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4">التاريخ</th>
                        <th class="px-6 py-4">المرجع</th>
                        <th class="px-6 py-4">النوع</th>
                        <th class="px-6 py-4">مدين</th>
                        <th class="px-6 py-4">دائن</th>
                        <th class="px-6 py-4">الرصيد</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @php $runningBalance = 0; @endphp

                    @foreach($customer->salesInvoices as $invoice)
                        @php
                            $runningBalance += $invoice->total;
                        @endphp
                        <tr>
                            <td class="px-6 py-4">{{ $invoice->created_at->format('Y-m-d') }}</td>
                            <td class="px-6 py-4">INV-{{ $invoice->id }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs">
                                    فاتورة
                                </span>
                            </td>
                            <td class="px-6 py-4 text-red-600 font-bold">
                                {{ number_format($invoice->total) }}
                            </td>
                            <td class="px-6 py-4">-</td>
                            <td class="px-6 py-4 font-bold">
                                {{ number_format($runningBalance) }}
                            </td>
                        </tr>

                        @if($invoice->paid > 0)
                            @php $runningBalance -= $invoice->paid; @endphp
                            <tr class="bg-gray-50">
                                <td class="px-6 py-4">{{ $invoice->updated_at->format('Y-m-d') }}</td>
                                <td class="px-6 py-4">PAY-{{ $invoice->id }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs">
                                        دفعة
                                    </span>
                                </td>
                                <td class="px-6 py-4">-</td>
                                <td class="px-6 py-4 text-green-600 font-bold">
                                    {{ number_format($invoice->paid) }}
                                </td>
                                <td class="px-6 py-4 font-bold">
                                    {{ number_format($runningBalance) }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
