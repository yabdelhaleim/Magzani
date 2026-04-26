@extends('layouts.app')

@section('title', 'كشف حساب المورد')
@section('page-title', 'كشف حساب المورد')

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

        .space-y-6 {
            space-y: 0 !important;
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

        .shadow-sm, .shadow-lg {
            box-shadow: none !important;
        }

        .p-6, .p-8 {
            padding: 10px !important;
        }

        .grid {
            display: block !important;
        }

        .grid-cols-1 {
            grid-template-columns: 1fr !important;
        }

        .md\\:grid-cols-3 {
            grid-template-columns: repeat(3, 1fr) !important;
            display: grid !important;
            gap: 10px !important;
            margin-bottom: 15px !important;
        }

        /* ترويسة الطباعة */
        .print-header {
            display: block !important;
            page-break-after: avoid;
            border-bottom: 3px solid #7c3aed !important;
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
            color: #7c3aed !important;
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
            background: #7c3aed !important;
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

        .text-3xl {
            font-size: 14pt !important;
        }

        .text-red-600 {
            color: #dc2626 !important;
        }

        .text-green-600 {
            color: #16a34a !important;
        }

        .text-purple-600 {
            color: #7c3aed !important;
        }

        .rounded-full {
            border-radius: 0 !important;
            border: 1px solid #000 !important;
            background: white !important;
            color: #000 !important;
        }

        .bg-gradient-to-r {
            background: #7c3aed !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
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

<div class="space-y-6 max-w-6xl mx-auto">

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
                @if($company && $company->address)
                    <p style="font-size: 9pt; margin: 3px 0;">
                        <i class="fas fa-map-marker-alt"></i> {{ $company->address }}
                    </p>
                @endif
                @if($company && $company->phone)
                    <p style="font-size: 9pt; margin: 3px 0;">
                        <i class="fas fa-phone"></i> {{ $company->phone }}
                    </p>
                @endif
                @if($company && $company->tax_number)
                    <p style="font-size: 9pt; margin: 3px 0;">
                        <i class="fas fa-file-invoice"></i> الرقم الضريبي: {{ $company->tax_number }}
                    </p>
                @endif
            </div>

            <div style="text-align: left; flex: 1;">
                <p style="font-size: 9pt; margin: 2px 0;"><strong>التاريخ:</strong> {{ \Carbon\Carbon::now()->format('Y-m-d') }}</p>
                <p style="font-size: 9pt; margin: 2px 0;"><strong>الساعة:</strong> {{ \Carbon\Carbon::now()->format('H:i') }}</p>
                @if(request('from_date') || request('to_date'))
                    <p style="font-size: 9pt; margin: 2px 0;"><strong>الفترة:</strong>
                        {{ request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->format('Y-m-d') : '-' }}
                        إلى
                        {{ request('to_date') ? \Carbon\Carbon::parse(request('to_date'))->format('Y-m-d') : '-' }}
                    </p>
                @endif
            </div>
        </div>

        <h2 class="print-title">
            <i class="fas fa-file-invoice"></i> كشف حساب المورد
        </h2>

        <div style="display: flex; justify-content: space-between; align-items: center; background: #f3e8ff; padding: 10px; border-radius: 6px; margin-top: 10px;">
            <div style="text-align: center; flex: 1;">
                <p style="font-size: 9pt; margin: 0;"><strong>اسم المورد:</strong></p>
                <p style="font-size: 11pt; font-weight: 900; color: #7c3aed;">{{ $supplier->name }}</p>
            </div>
            <div style="text-align: center; flex: 1;">
                <p style="font-size: 9pt; margin: 0;"><strong>كود المورد:</strong></p>
                <p style="font-size: 11pt; font-weight: 900; color: #7c3aed;">S-{{ str_pad($supplier->id, 3, '0', STR_PAD_LEFT) }}</p>
            </div>
            @if($supplier->phone)
            <div style="text-align: center; flex: 1;">
                <p style="font-size: 9pt; margin: 0;"><strong>الهاتف:</strong></p>
                <p style="font-size: 10pt; font-weight: 700;">{{ $supplier->phone }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Breadcrumb (no print) -->
    <nav class="flex items-center gap-2 text-sm text-gray-600 no-print">
        <a href="{{ route('suppliers.index') }}" class="hover:text-blue-600">الموردين</a>
        <i class="fas fa-chevron-left text-xs"></i>
        <a href="{{ route('suppliers.show', $supplier->id) }}" class="hover:text-blue-600">{{ $supplier->name }}</a>
        <i class="fas fa-chevron-left text-xs"></i>
        <span class="text-gray-900 font-medium">كشف الحساب</span>
    </nav>

    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl shadow-lg p-8 text-white no-print">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <i class="fas fa-file-invoice text-5xl opacity-75"></i>
                <div>
                    <h2 class="text-3xl font-bold mb-1">كشف حساب المورد</h2>
                    <p class="text-purple-100">{{ $supplier->name }} - #{{ $supplier->id }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="px-4 py-2 bg-white text-purple-700 hover:bg-gray-100 rounded-lg font-semibold">
                    <i class="fas fa-print ml-2"></i> طباعة
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 text-center">
            <div class="w-16 h-16 bg-purple-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                <i class="fas fa-shopping-cart text-2xl text-purple-600"></i>
            </div>
            <p class="text-sm text-gray-600 mb-2">إجمالي المشتريات (مدين)</p>
            <h3 class="text-3xl font-bold text-purple-600">{{ number_format($summary['total_purchases'] ?? 0, 2) }}</h3>
            <p class="text-xs text-gray-500 mt-1">جنيه مصري</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-2xl text-green-600"></i>
            </div>
            <p class="text-sm text-gray-600 mb-2">إجمالي المدفوعات (دائن)</p>
            <h3 class="text-3xl font-bold text-green-600">{{ number_format($summary['total_paid'] ?? 0, 2) }}</h3>
            <p class="text-xs text-gray-500 mt-1">جنيه مصري</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border-2 border-red-200 text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                <i class="fas fa-wallet text-2xl text-red-600"></i>
            </div>
            <p class="text-sm text-gray-600 mb-2">الرصيد المستحق</p>
            <h3 class="text-3xl font-bold text-red-600">{{ number_format($summary['balance'] ?? 0, 2) }}</h3>
            <p class="text-xs text-gray-500 mt-1">جنيه مصري</p>
        </div>
    </div>

    <!-- Filters (no print) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 no-print">
        <form method="GET" action="{{ route('suppliers.statement', $supplier->id) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">من تاريخ</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">نوع الحركة</label>
                <select name="type" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">الكل</option>
                    <option value="invoice" {{ request('type') == 'invoice' ? 'selected' : '' }}>فواتير فقط</option>
                    <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>سداد فقط</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-filter ml-2"></i>تطبيق
                </button>
            </div>
        </form>
    </div>

    <!-- Statement Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b bg-gray-50">
            <h3 class="font-bold text-lg"><i class="fas fa-list text-purple-600 ml-2"></i>حركات الحساب</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">التاريخ</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">المستند</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">البيان</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">مدين</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">دائن</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">الرصيد</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @php
                        $runningBalance = 0;
                        $totalDebit = 0;
                        $totalCredit = 0;
                    @endphp

                    @forelse($statement as $transaction)
                        @php
                            $runningBalance += ($transaction['debit'] ?? 0) - ($transaction['credit'] ?? 0);
                            $totalDebit += $transaction['debit'] ?? 0;
                            $totalCredit += $transaction['credit'] ?? 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm">
                                <i class="fas fa-calendar text-gray-400 ml-2"></i>{{ $transaction['date'] ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4"><span class="px-2 py-1 bg-gray-100 rounded text-xs">#{{ $transaction['id'] ?? 'N/A' }}</span></td>
                            <td class="px-6 py-4">
                                @if(($transaction['type'] ?? '') == 'فاتورة شراء')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                                        <i class="fas fa-shopping-cart"></i>{{ $transaction['type'] ?? '' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                        <i class="fas fa-money-bill-wave"></i>{{ $transaction['type'] ?? '' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if(($transaction['debit'] ?? 0) > 0)
                                    <span class="text-lg font-bold text-red-600">{{ number_format($transaction['debit'] ?? 0, 2) }}</span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if(($transaction['credit'] ?? 0) > 0)
                                    <span class="text-lg font-bold text-green-600">{{ number_format($transaction['credit'] ?? 0, 2) }}</span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-lg font-bold {{ $runningBalance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($runningBalance, 2) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <i class="fas fa-inbox text-6xl text-gray-300 mb-4 block"></i>
                                <p class="text-gray-500">لا توجد حركات في الفترة المحددة</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($statement->count() > 0)
                <tfoot class="bg-gray-50 border-t-2">
                    <tr>
                        <td colspan="3" class="px-6 py-4"><strong class="text-lg">الإجمالي</strong></td>
                        <td class="px-6 py-4 text-center"><strong class="text-lg text-red-600">{{ number_format($totalDebit, 2) }}</strong></td>
                        <td class="px-6 py-4 text-center"><strong class="text-lg text-green-600">{{ number_format($totalCredit, 2) }}</strong></td>
                        <td class="px-6 py-4 text-center">
                            <strong class="text-lg {{ $runningBalance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($runningBalance, 2) }}
                            </strong>
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Print Footer -->
    <div class="print-footer">
        <p>تم طباعة هذا الكشف من نظام إدارة المخازن - {{ $company->name ?? 'شركة ماجزاني' }} - {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</p>
    </div>
</div>
@endsection
