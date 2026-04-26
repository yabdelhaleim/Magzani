@extends('layouts.app')

@section('title', 'التقارير المالية')
@section('page-title', 'التقارير المالية')

@push('styles')
<style>
    :root {
        --tf-bg:          #f8fafc;
        --tf-surface:     #ffffff;
        --tf-surface2:    #f1f5f9;
        --tf-border:      #e2e8f0;
        --tf-border-soft: #f1f5f9;

        --tf-indigo:      #4f46e5;
        --tf-indigo-light:#6366f1;
        --tf-indigo-soft: #eef2ff;

        --tf-blue:        #0ea5e9;
        --tf-blue-soft:   #e0f2fe;
        --tf-green:       #059669;
        --tf-green-soft:  #d1fae5;
        --tf-red:         #dc2626;
        --tf-red-soft:    #fef2f2;
        --tf-amber:       #d97706;
        --tf-amber-soft:  #fef3c7;
        --tf-violet:      #7c3aed;
        --tf-violet-soft: #ede9fe;
        --tf-slate:       #475569;
        --tf-slate-soft:  #f8fafc;

        --tf-text-h:      #1e293b;
        --tf-text-b:      #334155;
        --tf-text-m:      #64748b;
        --tf-text-d:      #94a3b8;

        --tf-shadow-sm:   0 1px 3px rgba(0,0,0,0.05);
        --tf-shadow-card: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.04);
        --tf-shadow-lg:   0 4px 20px rgba(0,0,0,0.08);
    }

    .tf-page {
        background: var(--tf-bg);
        background-image:
            radial-gradient(ellipse 100% 80% at 0% -10%,  rgba(79,70,229,0.06) 0%, transparent 50%),
            radial-gradient(ellipse 80% 60% at 100% 110%,  rgba(124,58,237,0.04) 0%, transparent 50%);
        min-height: 100vh;
        padding: 24px 20px;
    }

    @keyframes tfFadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .tf-section { animation: tfFadeUp 0.4s cubic-bezier(0.22,1,0.36,1) both; }
    .tf-section:nth-child(1) { animation-delay: 0.03s; }
    .tf-section:nth-child(2) { animation-delay: 0.08s; }
    .tf-section:nth-child(3) { animation-delay: 0.13s; }
    .tf-section:nth-child(4) { animation-delay: 0.18s; }
    .tf-section:nth-child(5) { animation-delay: 0.23s; }

    .tf-card {
        background: var(--tf-surface); border-radius: 16px;
        border: 1px solid var(--tf-border);
        overflow: hidden; box-shadow: var(--tf-shadow-card);
        margin-bottom: 20px;
    }

    .tf-card-head {
        padding: 16px 20px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: white;
    }
    .tf-card-head h3 { margin: 0; font-size: 1.1rem; font-weight: 600; }

    .tf-stat-card {
        background: var(--tf-surface); border-radius: 16px;
        padding: 1.25rem; border: 1px solid var(--tf-border);
        box-shadow: var(--tf-shadow-sm);
    }

    .tf-stat-icon {
        width: 44px; height: 44px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
    }

    .tf-stat-label { font-size: 12px; color: var(--tf-text-m); margin-bottom: 4px; }
    .tf-stat-value { font-size: 1.5rem; font-weight: 700; color: var(--tf-text-h); }

    .tf-input, .tf-select {
        width: 100%; padding: 10px 14px;
        background: var(--tf-surface2);
        border: 1px solid var(--tf-border);
        border-radius: 10px; font-size: 14px;
        color: var(--tf-text-h);
    }
    .tf-input:focus, .tf-select:focus {
        outline: none; border-color: var(--tf-indigo);
        box-shadow: 0 0 0 3px rgba(79,70,229,0.08);
    }

    .tf-label {
        display: block; font-size: 12px; font-weight: 600;
        color: var(--tf-text-b); margin-bottom: 6px;
    }
    .tf-label i { margin-left: 5px; color: var(--tf-indigo); }

    .tf-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        padding: 10px 18px; border-radius: 10px; font-size: 14px; font-weight: 600;
        cursor: pointer; border: none;
    }
    .tf-btn-primary {
        background: linear-gradient(135deg, var(--tf-indigo), var(--tf-violet));
        color: white;
    }
    .tf-btn-secondary {
        background: var(--tf-surface2); color: var(--tf-text-b);
        border: 1px solid var(--tf-border);
    }

    .tf-table { width: 100%; border-collapse: collapse; }
    .tf-table th {
        padding: 12px 16px; text-align: right;
        font-size: 11px; font-weight: 600; text-transform: uppercase;
        color: var(--tf-text-m);
    }
    .tf-table td {
        padding: 12px 16px; border-top: 1px solid var(--tf-border-soft);
        color: var(--tf-text-b); font-size: 14px;
    }
    .tf-table tbody tr:hover { background: var(--tf-surface2); }

    .tf-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; border-radius: 16px; font-size: 11px; font-weight: 600;
    }

    .tf-section-head {
        padding: 10px 16px;
        font-size: 13px; font-weight: 700;
        border-radius: 8px 8px 0 0;
    }

    .tf-header-gradient {
        background: linear-gradient(135deg, #4338ca 0%, #6366f1 50%, #8b5cf6 100%);
        border-radius: 16px; padding: 1.5rem; color: white;
    }

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

        .tf-page {
            background: white !important;
            padding: 0 !important;
            min-height: auto !important;
        }

        .no-print {
            display: none !important;
        }

        .tf-card {
            box-shadow: none !important;
            border: 1px solid #000 !important;
            border-radius: 0 !important;
            margin-bottom: 15px !important;
            page-break-inside: avoid;
        }

        .tf-card-head {
            background: #4338ca !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color: white !important;
            padding: 10px !important;
        }

        .tf-stat-card {
            border: 1px solid #000 !important;
            border-radius: 0 !important;
            padding: 10px !important;
            page-break-inside: avoid;
        }

        .tf-header-gradient {
            background: #4338ca !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            border-radius: 0 !important;
            padding: 15px !important;
            page-break-after: avoid;
        }

        /* ترويسة الطباعة */
        .print-header {
            display: block !important;
            page-break-after: avoid;
            border-bottom: 3px solid #4338ca !important;
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
            color: #4338ca !important;
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
            background: #4338ca !important;
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

        .tf-section-head {
            background: #e0f2fe !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .tf-badge {
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

<div class="tf-page">

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
                <p style="font-size: 9pt; margin: 2px 0;"><strong>الفترة:</strong>
                    {{ $startDate->format('Y-m-d') }} إلى {{ $endDate->format('Y-m-d') }}
                </p>
            </div>
        </div>

        <h2 class="print-title">
            <i class="fas fa-chart-line"></i> التقرير المالي الشامل
        </h2>
    </div>

    <!-- Header -->
    <div class="tf-header-gradient tf-section mb-5 no-print">
        <h2 class="text-3xl font-bold mb-1">التقارير المالية الشاملة</h2>
        <p class="text-white/80 text-sm">
            من {{ $startDate->format('d/m/Y') }} إلى {{ $endDate->format('d/m/Y') }}
        </p>
    </div>

    <!-- Filters -->
    <div class="tf-card tf-section">
        <div class="p-5">
            <h3 class="text-base font-bold mb-4" style="color: var(--tf-text-h);">
                <i class="fas fa-filter" style="color: var(--tf-indigo); margin-left: 6px;"></i>
                تصفية التقرير
            </h3>
            <form method="GET" action="{{ route('reports.financial') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-3">
                    <label class="tf-label"><i class="fas fa-calendar-alt"></i>من تاريخ</label>
                    <input type="date" name="start_date" value="{{ request('start_date', $startDate->toDateString()) }}" class="tf-input">
                </div>
                <div class="md:col-span-3">
                    <label class="tf-label"><i class="fas fa-calendar-check"></i>إلى تاريخ</label>
                    <input type="date" name="end_date" value="{{ request('end_date', $endDate->toDateString()) }}" class="tf-input">
                </div>
                <div class="md:col-span-3">
                    <label class="tf-label"><i class="fas fa-warehouse"></i>المخزن</label>
                    <select name="warehouse_id" class="tf-select">
                        <option value="">كل المخازن</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3 flex items-end gap-2">
                    <button type="submit" class="tf-btn tf-btn-primary flex-1">
                        <i class="fas fa-search"></i>عرض
                    </button>
                    <a href="{{ route('reports.financial.export') . '?' . request()->getQueryString() }}"
                       class="tf-btn tf-btn-secondary"
                       title="تصدير Excel">
                        <i class="fas fa-file-excel"></i>
                    </a>
                    <button type="button" onclick="window.print()" class="tf-btn tf-btn-secondary">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="tf-stat-card tf-section">
            <div class="tf-stat-icon" style="background: linear-gradient(135deg, #0ea5e9, #38bdf8);">
                <i class="fas fa-shopping-cart text-white text-lg"></i>
            </div>
            <div class="tf-stat-label">إجمالي المبيعات</div>
            <div class="tf-stat-value" dir="ltr" style="text-align: right;">{{ number_format($report['total_sales'], 0) }} <span style="font-size: 0.875rem; color: var(--tf-text-m);">ج.م</span></div>
            <div class="text-xs mt-1" style="color: var(--tf-text-m);"><i class="fas fa-file-invoice ml-1"></i>{{ $report['sales_count'] }} فاتورة</div>
        </div>
        <div class="tf-stat-card tf-section" style="animation-delay: 0.05s;">
            <div class="tf-stat-icon" style="background: linear-gradient(135deg, #7c3aed, #a78bfa);">
                <i class="fas fa-truck text-white text-lg"></i>
            </div>
            <div class="tf-stat-label">إجمالي المشتريات</div>
            <div class="tf-stat-value" dir="ltr" style="text-align: right;">{{ number_format($report['total_purchases'], 0) }} <span style="font-size: 0.875rem; color: var(--tf-text-m);">ج.م</span></div>
            <div class="text-xs mt-1" style="color: var(--tf-text-m);"><i class="fas fa-file-invoice ml-1"></i>{{ $report['purchases_count'] }} فاتورة</div>
        </div>
        <div class="tf-stat-card tf-section" style="animation-delay: 0.10s;">
            <div class="tf-stat-icon" style="background: linear-gradient(135deg, #059669, #34d399);">
                <i class="fas fa-chart-line text-white text-lg"></i>
            </div>
            <div class="tf-stat-label">صافي الربح</div>
            @php $profitColor = $report['net_profit'] >= 0 ? '#059669' : '#dc2626'; @endphp
            <div class="tf-stat-value" dir="ltr" style="text-align: right; color: {{ $profitColor }};">{{ number_format($report['net_profit'], 0) }} <span style="font-size: 0.875rem; color: var(--tf-text-m);">ج.م</span></div>
            <div class="text-xs mt-1" style="color: var(--tf-text-m);"><i class="fas fa-percent ml-1"></i>هامش {{ number_format($report['profit_margin'], 1) }}%</div>
        </div>
        <div class="tf-stat-card tf-section" style="animation-delay: 0.15s;">
            <div class="tf-stat-icon" style="background: linear-gradient(135deg, #dc2626, #f87171);">
                <i class="fas fa-money-bill-wave text-white text-lg"></i>
            </div>
            <div class="tf-stat-label">إجمالي المصروفات</div>
            <div class="tf-stat-value" dir="ltr" style="text-align: right;">{{ number_format($report['total_expenses'], 0) }} <span style="font-size: 0.875rem; color: var(--tf-text-m);">ج.م</span></div>
            <div class="text-xs mt-1" style="color: var(--tf-text-m);"><i class="fas fa-layer-group ml-1"></i>{{ count($report['expenses_by_category']) }} فئة</div>
        </div>
    </div>

    <!-- Profit & Loss Statement -->
    <div class="tf-card tf-section" style="animation-delay: 0.18s;">
        <div class="tf-card-head">
            <h3><i class="fas fa-file-invoice-dollar ml-2"></i>قائمة الأرباح والخسائر</h3>
        </div>
        <div class="p-4">
            <table class="tf-table">
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <th colspan="2" class="tf-section-head" style="background: var(--tf-blue-soft); color: #0369a1;">
                            <i class="fas fa-coins ml-2"></i>الإيرادات
                        </th>
                    </tr>
                    <tr>
                        <td class="text-gray-600">إجمالي المبيعات</td>
                        <td class="text-left font-semibold" dir="ltr">{{ number_format($report['total_sales'], 2) }} ج.م</td>
                    </tr>
                    <tr>
                        <td class="text-gray-600">مرتجع المبيعات</td>
                        <td class="text-left font-semibold" style="color: var(--tf-red);" dir="ltr">({{ number_format($report['sales_returns'], 2) }}) ج.م</td>
                    </tr>
                    <tr style="background: var(--tf-blue-soft);">
                        <td class="font-bold" style="color: #0369a1;">صافي المبيعات</td>
                        <td class="text-left font-bold" style="color: #0369a1; font-size: 1.1rem;" dir="ltr">{{ number_format($report['net_sales'], 2) }} ج.م</td>
                    </tr>

                    <tr>
                        <th colspan="2" class="tf-section-head" style="background: var(--tf-violet-soft); color: #5b21b6;">
                            <i class="fas fa-box ml-2"></i>التكاليف
                        </th>
                    </tr>
                    <tr>
                        <td class="text-gray-600">تكلفة البضاعة المباعة</td>
                        <td class="text-left font-semibold" dir="ltr">{{ number_format($report['cost_of_sales'], 2) }} ج.م</td>
                    </tr>
                    <tr style="background: var(--tf-green-soft);">
                        <td class="font-bold" style="color: #065f46;">إجمالي الربح</td>
                        <td class="text-left font-bold" style="color: #065f46; font-size: 1.1rem;" dir="ltr">{{ number_format($report['gross_profit'], 2) }} ج.م</td>
                    </tr>

                    <tr>
                        <th colspan="2" class="tf-section-head" style="background: var(--tf-amber-soft); color: #b45309;">
                            <i class="fas fa-money-bill-wave ml-2"></i>المصروفات التشغيلية
                        </th>
                    </tr>
                    @foreach($report['expenses_by_category'] as $category => $amount)
                    <tr>
                        <td class="text-gray-600"><i class="fas fa-circle text-orange-400 text-xs ml-2"></i>{{ $category ?? 'غير مصنف' }}</td>
                        <td class="text-left font-semibold" dir="ltr">{{ number_format($amount, 2) }} ج.م</td>
                    </tr>
                    @endforeach
                    <tr style="background: var(--tf-amber-soft);">
                        <td class="font-bold" style="color: #b45309;">إجمالي المصروفات</td>
                        <td class="text-left font-bold" style="color: #b45309; font-size: 1.1rem;" dir="ltr">{{ number_format($report['total_expenses'], 2) }} ج.م</td>
                    </tr>

                    <tr style="background: linear-gradient(135deg, #059669, #10b981); color: white;">
                        <td class="px-4 py-3 font-bold"><i class="fas fa-trophy ml-2"></i>صافي الربح / (الخسارة)</td>
                        <td class="px-4 py-3 text-left font-bold text-lg" dir="ltr">{{ number_format($report['net_profit'], 2) }} ج.م</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        <div class="tf-card tf-section" style="animation-delay: 0.21s;">
            <div class="tf-card-head" style="background: linear-gradient(135deg, #0ea5e9, #06b6d4);">
                <h3><i class="fas fa-star ml-2"></i>أفضل 5 منتجات</h3>
            </div>
            <div class="p-4">
                <div class="space-y-3">
                    @forelse($topProducts as $index => $product)
                        <div class="flex items-center gap-3 p-2 rounded-lg" style="background: var(--tf-surface2);">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm font-bold" style="background: linear-gradient(135deg, var(--tf-blue), #38bdf8);">{{ $index + 1 }}</div>
                            <div class="flex-1">
                                <p class="font-semibold text-sm" style="color: var(--tf-text-h);">{{ $product->name }}</p>
                                <p class="text-xs" style="color: var(--tf-text-m);">{{ $product->total_quantity }} وحدة • {{ $product->number_of_orders }} طلب</p>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-sm" style="color: var(--tf-blue);" dir="ltr">{{ number_format($product->total_revenue, 0) }} ج.م</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center py-6" style="color: var(--tf-text-m);">لا توجد بيانات</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="tf-card tf-section" style="animation-delay: 0.23s;">
            <div class="tf-card-head" style="background: linear-gradient(135deg, #7c3aed, #a78bfa);">
                <h3><i class="fas fa-users ml-2"></i>أفضل 5 عملاء</h3>
            </div>
            <div class="p-4">
                <div class="space-y-3">
                    @forelse($topCustomers as $index => $customer)
                        <div class="flex items-center gap-3 p-2 rounded-lg" style="background: var(--tf-surface2);">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold" style="background: linear-gradient(135deg, var(--tf-violet), #c4b5fd);">{{ mb_substr($customer->name, 0, 1) }}</div>
                            <div class="flex-1">
                                <p class="font-semibold text-sm" style="color: var(--tf-text-h);">{{ $customer->name }}</p>
                                <p class="text-xs" style="color: var(--tf-text-m);">{{ $customer->total_invoices }} فاتورة</p>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-sm" style="color: var(--tf-violet);" dir="ltr">{{ number_format($customer->total_spent, 0) }} ج.م</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center py-6" style="color: var(--tf-text-m);">لا توجد بيانات</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Sales -->
    <div class="tf-card tf-section" style="animation-delay: 0.26s;">
        <div class="tf-card-head" style="background: linear-gradient(135deg, #059669, #10b981);">
            <h3><i class="fas fa-chart-bar ml-2"></i>المبيعات اليومية</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="tf-table">
                <thead style="background: var(--tf-surface2);">
                    <tr>
                        <th class="text-xs">التاريخ</th>
                        <th class="text-xs text-center">الفواتير</th>
                        <th class="text-xs text-center">المبيعات</th>
                        <th class="text-xs text-center">المدفوع</th>
                        <th class="text-xs text-center">المتبقي</th>
                        <th class="text-xs text-center">متوسط</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailySales as $day)
                        <tr>
                            <td class="font-semibold" style="color: var(--tf-text-h);">{{ \Carbon\Carbon::parse($day->date)->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <span class="tf-badge" style="background: var(--tf-blue-soft); color: #0369a1;">{{ $day->total_invoices }}</span>
                            </td>
                            <td class="text-center font-semibold" dir="ltr">{{ number_format($day->total_sales, 0) }} ج.م</td>
                            <td class="text-center font-semibold" style="color: var(--tf-green);" dir="ltr">{{ number_format($day->total_paid, 0) }} ج.م</td>
                            <td class="text-center font-semibold" style="color: var(--tf-red);" dir="ltr">{{ number_format($day->total_remaining, 0) }} ج.م</td>
                            <td class="text-center" style="color: var(--tf-text-m);" dir="ltr">{{ number_format($day->average_invoice, 0) }} ج.م</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-6" style="color: var(--tf-text-m);">لا توجد مبيعات في هذه الفترة</td></tr>
                    @endforelse
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