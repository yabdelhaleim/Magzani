@extends('layouts.app')

@section('title', 'تقرير الأرباح والخسائر')
@section('page-title', 'الأرباح والخسائر')

@push('styles')
<style>
    .tf-page { background: #f8fafc; min-height: 100vh; padding: 24px 20px; }
    .tf-card { background: white; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 20px; }
    .tf-card-head { padding: 16px 20px; background: linear-gradient(135deg, #059669, #10b981); color: white; border-radius: 16px 16px 0 0; }
    .tf-btn { padding: 10px 20px; border-radius: 10px; font-weight: 600; border: none; cursor: pointer; }
    .tf-btn-primary { background: linear-gradient(135deg, #059669, #10b981); color: white; }
    .tf-table { width: 100%; border-collapse: collapse; }
    .tf-table th { padding: 12px 16px; text-align: right; font-size: 11px; font-weight: 600; color: #64748b; }
    .tf-table td { padding: 12px 16px; border-top: 1px solid #e2e8f0; }
    .tf-stat { padding: 20px; text-align: center; }
    .tf-stat-value { font-size: 1.5rem; font-weight: 700; }
    .tf-input, .tf-select { width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 10px; }
    .tf-row-income { background: #d1fae5; }
    .tf-row-expense { background: #fef3c7; }
    .tf-row-profit { background: linear-gradient(135deg, #059669, #10b981); color: white; }
    .tf-row-loss { background: linear-gradient(135deg, #dc2626, #f87171); color: white; }

    /* طباعة احترافية */
    @media print {
        @page { size: A4 landscape; margin: 8mm; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { background: white !important; font-size: 10pt !important; }
        .tf-page { background: white !important; padding: 0 !important; }
        .no-print { display: none !important; }
        .tf-card { box-shadow: none !important; border: 1px solid #000 !important; border-radius: 0 !important; page-break-inside: avoid; }
        .tf-card-head { background: #059669 !important; padding: 10px !important; border-radius: 0 !important; }
        .tf-stat { border: 1px solid #000 !important; padding: 10px !important; }
        .tf-header-gradient { background: #059669 !important; border-radius: 0 !important; padding: 15px !important; }

        .print-header { display: block !important; page-break-after: avoid; border-bottom: 3px solid #059669 !important; padding-bottom: 15px !important; }
        .print-footer { display: block !important; position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 8pt; border-top: 1px solid #ddd; padding: 8px; }
        .print-logo { max-height: 60px !important; }
        .print-company-name { font-size: 20pt !important; font-weight: 900 !important; color: #059669 !important; }
        .print-title { font-size: 16pt !important; font-weight: 800 !important; text-align: center; text-decoration: underline; }

        th { background: #059669 !important; color: white !important; border: 1px solid #000 !important; padding: 6px !important; font-size: 9pt !important; }
        td { border: 1px solid #000 !important; padding: 6px !important; font-size: 9pt !important; }
        tbody tr:nth-child(even) { background: #f8fafc !important; }
        .tf-row-income { background: #d1fae5 !important; }
        .tf-row-expense { background: #fef3c7 !important; }
        .tf-row-profit, .tf-row-loss { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }

    .print-header, .print-footer { display: none; }
</style>
@endpush

@section('content')
@php
    $company = \App\Models\Company::first();
@endphp

<div class="tf-page">
    {{-- Print Header --}}
    <div class="print-header">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
            <div style="text-align: right; flex: 1;">
                @if($company && $company->logo)
                    <img src="{{ asset('storage/' . $company->logo) }}" class="print-logo" alt="شعار الشركة">
                @endif
            </div>
            <div style="text-align: center; flex: 2;">
                <h1 class="print-company-name">{{ $company->name ?? 'شركة ماجزاني' }}</h1>
                @if($company->address ?? null)
                    <p style="font-size: 9pt; margin: 3px 0;"><i class="fas fa-map-marker-alt"></i> {{ $company->address }}</p>
                @endif
                @if($company->phone ?? null)
                    <p style="font-size: 9pt; margin: 3px 0;"><i class="fas fa-phone"></i> {{ $company->phone }}</p>
                @endif
                @if($company->tax_number ?? null)
                    <p style="font-size: 9pt; margin: 3px 0;"><i class="fas fa-file-invoice"></i> الرقم الضريبي: {{ $company->tax_number }}</p>
                @endif
            </div>
            <div style="text-align: left; flex: 1;">
                <p style="font-size: 9pt; margin: 2px 0;"><strong>التاريخ:</strong> {{ \Carbon\Carbon::now()->format('Y-m-d') }}</p>
                <p style="font-size: 9pt; margin: 2px 0;"><strong>الساعة:</strong> {{ \Carbon\Carbon::now()->format('H:i') }}</p>
                <p style="font-size: 9pt; margin: 2px 0;"><strong>الفترة:</strong>
                    {{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('Y-m-d') : '-' }}
                    إلى
                    {{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('Y-m-d') : '-' }}
                </p>
            </div>
        </div>
        <h2 class="print-title"><i class="fas fa-chart-line"></i> تقرير الأرباح والخسائر</h2>
    </div>

    <!-- Header -->
    <div class="tf-header-gradient tf-section mb-6 no-print" style="background: linear-gradient(135deg, #059669, #10b981); border-radius: 16px; padding: 1.5rem; color: white;">
        <h2 class="text-3xl font-bold mb-1">تقرير الأرباح والخسائر</h2>
        <p class="text-white/80 text-sm">قائمة الدخل الشاملة</p>
    </div>

    <!-- Filters -->
    <div class="tf-card p-5 no-print">
        <form method="GET" action="{{ route('reports.profit-loss') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">من تاريخ</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="tf-input">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">إلى تاريخ</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="tf-input">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">المخزن</label>
                <select name="warehouse_id" class="tf-select">
                    <option value="">كل المخازن</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="tf-btn tf-btn-primary flex-1"><i class="fas fa-search"></i> عرض</button>
                <button type="button" onclick="window.print()" class="tf-btn bg-gray-200"><i class="fas fa-print"></i></button>
            </div>
        </form>
    </div>

    <!-- Profit & Loss Statement -->
    <div class="tf-card">
        <div class="tf-card-head">
            <h3><i class="fas fa-file-invoice-dollar ml-2"></i>قائمة الأرباح والخسائر</h3>
        </div>
        <div class="p-4">
            <table class="tf-table">
                <tbody>
                    <tr>
                        <th colspan="2" class="text-xs font-semibold py-2 px-4" style="background: #e0f2fe; color: #0369a1;">
                            <i class="fas fa-coins ml-2"></i>الإيرادات
                        </th>
                    </tr>
                    <tr>
                        <td class="text-gray-600">إجمالي المبيعات</td>
                        <td class="text-left font-semibold" dir="ltr">{{ number_format($report['total_sales'], 2) }} ج.م</td>
                    </tr>
                    <tr>
                        <td class="text-gray-600">مرتجع المبيعات</td>
                        <td class="text-left font-semibold text-red-600" dir="ltr">({{ number_format($report['sales_returns'], 2) }}) ج.م</td>
                    </tr>
                    <tr class="tf-row-income">
                        <td class="font-bold">صافي المبيعات</td>
                        <td class="text-left font-bold" dir="ltr">{{ number_format($report['net_sales'], 2) }} ج.م</td>
                    </tr>

                    <tr>
                        <th colspan="2" class="text-xs font-semibold py-2 px-4" style="background: #ede9fe; color: #5b21b6;">
                            <i class="fas fa-box ml-2"></i>التكاليف
                        </th>
                    </tr>
                    <tr>
                        <td class="text-gray-600">تكلفة البضاعة المباعة</td>
                        <td class="text-left font-semibold" dir="ltr">{{ number_format($report['cost_of_sales'], 2) }} ج.م</td>
                    </tr>
                    <tr class="tf-row-income">
                        <td class="font-bold">إجمالي الربح</td>
                        <td class="text-left font-bold" dir="ltr">{{ number_format($report['gross_profit'], 2) }} ج.م</td>
                    </tr>

                    <tr>
                        <th colspan="2" class="text-xs font-semibold py-2 px-4" style="background: #fef3c7; color: #b45309;">
                            <i class="fas fa-money-bill-wave ml-2"></i>المصروفات التشغيلية
                        </th>
                    </tr>
                    @foreach($report['expenses_by_category'] as $category => $amount)
                    <tr>
                        <td class="text-gray-600"><i class="fas fa-circle text-orange-400 text-xs ml-2"></i>{{ $category ?? 'غير مصنف' }}</td>
                        <td class="text-left font-semibold" dir="ltr">{{ number_format($amount, 2) }} ج.م</td>
                    </tr>
                    @endforeach
                    <tr class="tf-row-expense">
                        <td class="font-bold">إجمالي المصروفات</td>
                        <td class="text-left font-bold" dir="ltr">{{ number_format($report['total_expenses'], 2) }} ج.م</td>
                    </tr>

                    <tr class="{{ $report['net_profit'] >= 0 ? 'tf-row-profit' : 'tf-row-loss' }}">
                        <td class="px-4 py-3 font-bold"><i class="fas fa-trophy ml-2"></i>صافي الربح / (الخسارة)</td>
                        <td class="px-4 py-3 text-left font-bold text-lg" dir="ltr">{{ number_format($report['net_profit'], 2) }} ج.م</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Print Footer --}}
    <div class="print-footer">
        <p>تم طباعة هذا التقرير من نظام إدارة المخازن - {{ $company->name ?? 'شركة ماجزاني' }} - {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</p>
    </div>
</div>
@endsection
