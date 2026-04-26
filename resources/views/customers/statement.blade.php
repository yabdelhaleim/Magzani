@extends('layouts.app')

@section('title', 'كشف حساب العميل')
@section('page-title', 'كشف حساب العميل')

@push('styles')
<style>
    /* طباعة احترافية */
    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            background: white !important;
            font-size: 10pt !important;
            line-height: 1.3 !important;
        }

        .max-w-6xl {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .no-print {
            display: none !important;
        }

        .bg-white {
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
        }

        .rounded-xl {
            border-radius: 0 !important;
        }

        .shadow-sm {
            box-shadow: none !important;
        }

        .p-6 {
            padding: 10px !important;
        }

        .grid {
            display: block !important;
        }

        .grid-cols-1 {
            grid-template-columns: 1fr !important;
        }

        .md\\:grid-cols-4 {
            grid-template-columns: repeat(4, 1fr) !important;
            display: grid !important;
            gap: 10px !important;
            margin-bottom: 15px !important;
        }

        /* ترويسة الطباعة */
        .print-header {
            display: block !important;
            page-break-after: avoid;
            border-bottom: 3px solid #1e40af !important;
            padding-bottom: 15px !important;
            margin-bottom: 15px !important;
        }

        .print-footer {
            display: block !important;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding: 8px;
        }

        .print-logo {
            max-height: 60px !important;
            max-width: 120px !important;
        }

        .print-company-name {
            font-size: 20pt !important;
            font-weight: 900 !important;
            color: #1e40af !important;
            margin-bottom: 5px !important;
        }

        .print-title {
            font-size: 16pt !important;
            font-weight: 800 !important;
            text-align: center;
            margin: 10px 0 !important;
            color: #1e293b !important;
            text-decoration: underline;
        }

        /* تنسيق الجدول للطباعة */
        table {
            page-break-inside: avoid;
        }

        thead {
            display: table-header-group;
        }

        th {
            background: #1e40af !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color: white !important;
            border: 1px solid #000 !important;
            padding: 6px !important;
            font-size: 9pt !important;
        }

        td {
            border: 1px solid #000 !important;
            padding: 6px !important;
            font-size: 9pt !important;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .bg-gray-50 {
            background: #f8fafc !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .text-2xl {
            font-size: 14pt !important;
        }

        .text-red-600 {
            color: #dc2626 !important;
        }

        .text-green-600 {
            color: #16a34a !important;
        }

        .text-orange-600 {
            color: #ea580c !important;
        }

        .rounded-full {
            border-radius: 0 !important;
            border: 1px solid #000 !important;
            background: white !important;
            color: #000 !important;
        }
    }

    .print-header {
        display: none;
    }

    .print-footer {
        display: none;
    }
</style>
@endpush

@section('content')
@php
    $company = \App\Models\Company::first();
@endphp

<div class="max-w-6xl mx-auto">

    {{-- Print Header --}}
    <div class="print-header">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
            <div style="text-align: right; flex: 1;">
                @if($company && $company->logo)
                    <img src="{{ asset('storage/' . $company->logo) }}" class="print-logo" alt="شعار الشركة">
                @endif
            </div>

            <div style="text-align: center; flex: 2; padding: 0 15px;">
                <h1 class="print-company-name">{{ $company->name ?? 'شركة ماجزاني' }}</h1>
                @if($company->address)
                    <p style="font-size: 9pt; margin: 3px 0;">
                        <i class="fas fa-map-marker-alt"></i> {{ $company->address }}
                    </p>
                @endif
                @if($company->phone)
                    <p style="font-size: 9pt; margin: 3px 0;">
                        <i class="fas fa-phone"></i> {{ $company->phone }}
                    </p>
                @endif
                @if($company->tax_number)
                    <p style="font-size: 9pt; margin: 3px 0;">
                        <i class="fas fa-file-invoice"></i> الرقم الضريبي: {{ $company->tax_number }}
                    </p>
                @endif
            </div>

            <div style="text-align: left; flex: 1;">
                <p style="font-size: 9pt; margin: 2px 0;"><strong>التاريخ:</strong> {{ \Carbon\Carbon::now()->format('Y-m-d') }}</p>
                <p style="font-size: 9pt; margin: 2px 0;"><strong>الساعة:</strong> {{ \Carbon\Carbon::now()->format('H:i') }}</p>
            </div>
        </div>

        <h2 class="print-title">
            <i class="fas fa-file-invoice-dollar"></i> كشف حساب العميل
        </h2>

        <div style="display: flex; justify-content: space-between; align-items: center; background: #f0f4ff; padding: 10px; border-radius: 6px; margin-top: 10px;">
            <div style="text-align: center; flex: 1;">
                <p style="font-size: 9pt; margin: 0;"><strong>اسم العميل:</strong></p>
                <p style="font-size: 11pt; font-weight: 900; color: #1e40af;">{{ $customer->name }}</p>
            </div>
            <div style="text-align: center; flex: 1;">
                <p style="font-size: 9pt; margin: 0;"><strong>كود العميل:</strong></p>
                <p style="font-size: 11pt; font-weight: 900; color: #1e40af;">C-{{ str_pad($customer->id, 3, '0', STR_PAD_LEFT) }}</p>
            </div>
            @if($customer->phone)
            <div style="text-align: center; flex: 1;">
                <p style="font-size: 9pt; margin: 0;"><strong>الهاتف:</strong></p>
                <p style="font-size: 10pt; font-weight: 700;">{{ $customer->phone }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6 no-print">
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

    {{-- Print Footer --}}
    <div class="print-footer">
        <p>تم طباعة هذا الكشف من نظام إدارة المخازن - {{ $company->name ?? 'شركة ماجزاني' }} - {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</p>
    </div>
</div>
@endsection
