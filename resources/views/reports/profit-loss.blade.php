@extends('layouts.app')

@section('title', 'تقرير الأرباح والخسائر')
@section('page-title', 'الأرباح والخسائر')

@push('styles')
<style>
    :root {
        --tf-bg:          #f8fafc;
        --tf-surface:     #ffffff;
        --tf-surface2:    #f1f5f9;
        --tf-border:      #e2e8f0;
        --tf-border-soft: #f1f5f9;

        --tf-green:       #059669;
        --tf-green-soft:  #d1fae5;
        --tf-red:         #dc2626;
        --tf-red-soft:    #fef2f2;
        --tf-amber:       #d97706;
        --tf-amber-soft:  #fef3c7;
        --tf-blue:        #0284c7;
        --tf-blue-soft:   #e0f2fe;
        --tf-violet:      #7c3aed;
        --tf-violet-soft: #ede9fe;

        --tf-text-h:      #1e293b;
        --tf-text-b:      #334155;
        --tf-text-m:      #64748b;
        --tf-text-d:      #94a3b8;

        --tf-shadow-sm:   0 1px 3px rgba(0,0,0,0.05);
        --tf-shadow-card: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.04);
    }

    .tf-page {
        background: var(--tf-bg);
        background-image:
            radial-gradient(ellipse 100% 80% at 0% -10%,  rgba(5,150,105,0.06) 0%, transparent 50%),
            radial-gradient(ellipse 80% 60% at 100% 110%,  rgba(20,184,166,0.04) 0%, transparent 50%);
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

    .tf-card {
        background: var(--tf-surface); border-radius: 16px;
        border: 1px solid var(--tf-border);
        overflow: hidden; box-shadow: var(--tf-shadow-card);
        margin-bottom: 20px;
    }

    .tf-card-head {
        padding: 16px 20px;
        background: linear-gradient(135deg, #059669, #10b981);
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

    .tf-input {
        width: 100%; padding: 10px 14px;
        background: var(--tf-surface2);
        border: 1px solid var(--tf-border);
        border-radius: 10px; font-size: 14px;
        color: var(--tf-text-h);
    }
    .tf-input:focus {
        outline: none; border-color: var(--tf-green);
        box-shadow: 0 0 0 3px rgba(5,150,105,0.08);
    }

    .tf-label {
        display: block; font-size: 12px; font-weight: 600;
        color: var(--tf-text-b); margin-bottom: 6px;
    }
    .tf-label i { margin-left: 5px; color: var(--tf-green); }

    .tf-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        padding: 10px 18px; border-radius: 10px; font-size: 14px; font-weight: 600;
        cursor: pointer; border: none;
    }
    .tf-btn-primary {
        background: linear-gradient(135deg, var(--tf-green), #10b981);
        color: white;
    }
    .tf-btn-secondary {
        background: var(--tf-surface2); color: var(--tf-text-b);
        border: 1px solid var(--tf-border);
    }

    .tf-table { width: 100%; border-collapse: collapse; }
    .tf-table td {
        padding: 12px 16px; border-top: 1px solid var(--tf-border-soft);
        color: var(--tf-text-b); font-size: 14px;
    }
    .tf-table tbody tr:hover { background: var(--tf-surface2); }

    .tf-section-head {
        padding: 12px 16px;
        font-size: 13px; font-weight: 700;
    }

    .tf-bar {
        height: 32px;
        background: var(--tf-surface2);
        border-radius: 8px;
        overflow: hidden;
    }
    .tf-bar-fill { height: 100%; border-radius: 8px; }

    .tf-header-gradient {
        background: linear-gradient(135deg, #047857 0%, #059669 50%, #14b8a6 100%);
        border-radius: 16px; padding: 1.5rem; color: white;
    }

    @media print {
        .no-print { display: none !important; }
        body { background: white; }
    }
</style>
@endpush

@section('content')
<div class="tf-page">
    <!-- Header -->
    <div class="tf-header-gradient tf-section mb-5">
        <h2 class="text-3xl font-bold mb-1">تقرير الأرباح والخسائر</h2>
        <p class="text-white/80 text-sm">من {{ $startDate->format('d/m/Y') }} إلى {{ $endDate->format('d/m/Y') }}</p>
    </div>

    <!-- Filters -->
    <div class="tf-card tf-section">
        <div class="p-5">
            <h3 class="text-base font-bold mb-4" style="color: var(--tf-text-h);">
                <i class="fas fa-filter" style="color: var(--tf-green); margin-left: 6px;"></i>
                تصفية التقرير
            </h3>
            <form method="GET" action="{{ route('reports.profit-loss') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                    <label class="tf-label"><i class="fas fa-calendar-alt"></i>من تاريخ</label>
                    <input type="date" name="start_date" value="{{ request('start_date', $startDate->toDateString()) }}" class="tf-input">
                </div>
                <div class="md:col-span-4">
                    <label class="tf-label"><i class="fas fa-calendar-check"></i>إلى تاريخ</label>
                    <input type="date" name="end_date" value="{{ request('end_date', $endDate->toDateString()) }}" class="tf-input">
                </div>
                <div class="md:col-span-4 flex items-end gap-2">
                    <button type="submit" class="tf-btn tf-btn-primary flex-1">
                        <i class="fas fa-search"></i>عرض
                    </button>
                    <button type="button" onclick="window.print()" class="tf-btn tf-btn-secondary">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <div class="tf-stat-card tf-section" style="border-color: var(--tf-green-soft);">
            <div class="flex items-start justify-between mb-3">
                <div class="tf-stat-icon" style="background: linear-gradient(135deg, var(--tf-green), #34d399);">
                    <i class="fas fa-chart-line text-white text-lg"></i>
                </div>
                <span class="tf-badge" style="background: var(--tf-green-soft); color: var(--tf-green);">إجمالي</span>
            </div>
            <div class="tf-stat-label">إجمالي الربح</div>
            @php $grossColor = $report['gross_profit'] >= 0 ? 'var(--tf-green)' : 'var(--tf-red)'; @endphp
            <div class="tf-stat-value" dir="ltr" style="color: {{ $grossColor }};">
                {{ number_format($report['gross_profit'], 0) }} <span style="font-size: 0.875rem; color: var(--tf-text-m);">ج.م</span>
            </div>
            <div class="text-xs mt-1" style="color: var(--tf-text-m);">قبل خصم المصروفات</div>
        </div>

        <div class="tf-stat-card tf-section" style="animation-delay: 0.05s; border-color: var(--tf-amber-soft);">
            <div class="flex items-start justify-between mb-3">
                <div class="tf-stat-icon" style="background: linear-gradient(135deg, var(--tf-amber), #fbbf24);">
                    <i class="fas fa-money-bill-wave text-white text-lg"></i>
                </div>
                <span class="tf-badge" style="background: var(--tf-amber-soft); color: var(--tf-amber);">مصروفات</span>
            </div>
            <div class="tf-stat-label">إجمالي المصروفات</div>
            <div class="tf-stat-value" dir="ltr" style="color: var(--tf-amber);">
                {{ number_format($report['total_expenses'], 0) }} <span style="font-size: 0.875rem; color: var(--tf-text-m);">ج.م</span>
            </div>
            <div class="text-xs mt-1" style="color: var(--tf-text-m);">جميع المصروفات التشغيلية</div>
        </div>

        <div class="tf-stat-card tf-section" style="animation-delay: 0.10s; background: linear-gradient(135deg, #047857, #10b981); color: white;">
            <div class="flex items-start justify-between mb-3">
                <div class="tf-stat-icon" style="background: rgba(255,255,255,0.2);">
                    <i class="fas fa-trophy text-white text-lg"></i>
                </div>
                <span class="tf-badge" style="background: rgba(255,255,255,0.2); color: white;">صافي</span>
            </div>
            <div class="tf-stat-label" style="color: rgba(255,255,255,0.9);">صافي الربح</div>
            <div class="tf-stat-value" dir="ltr" style="color: white; font-size: 1.75rem;">
                {{ number_format($report['net_profit'], 0) }} <span style="font-size: 0.875rem; opacity: 0.8;">ج.م</span>
            </div>
            <div class="text-xs mt-1" style="color: rgba(255,255,255,0.8);"><i class="fas fa-percent ml-1"></i>هامش {{ number_format($report['profit_margin'], 1) }}%</div>
        </div>
    </div>

    <!-- Profit & Loss Statement -->
    <div class="tf-card tf-section" style="animation-delay: 0.15s;">
        <div class="tf-card-head">
            <h3><i class="fas fa-file-invoice-dollar ml-2"></i>قائمة الأرباح والخسائر</h3>
        </div>
        <div class="p-4">
            <table class="tf-table">
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <th colspan="2" class="tf-section-head" style="background: var(--tf-blue-soft); color: #0369a1;">
                            <i class="fas fa-arrow-up ml-2"></i>الإيرادات
                        </th>
                    </tr>
                    <tr>
                        <td class="text-gray-600">إجمالي المبيعات</td>
                        <td class="text-left font-bold" dir="ltr">{{ number_format($report['total_sales'], 2) }} ج.م</td>
                    </tr>
                    <tr>
                        <td class="text-gray-600">مرتجع المبيعات</td>
                        <td class="text-left font-bold" style="color: var(--tf-red);" dir="ltr">({{ number_format($report['sales_returns'], 2) }}) ج.م</td>
                    </tr>
                    <tr style="background: var(--tf-blue-soft);">
                        <td class="font-bold" style="color: #0369a1;">صافي المبيعات</td>
                        <td class="text-left font-bold" style="color: #0369a1; font-size: 1.1rem;" dir="ltr">{{ number_format($report['net_sales'], 2) }} ج.م</td>
                    </tr>

                    <tr>
                        <th colspan="2" class="tf-section-head" style="background: var(--tf-violet-soft); color: #5b21b6;">
                            <i class="fas fa-box ml-2"></i>التكاليف المباشرة
                        </th>
                    </tr>
                    <tr>
                        <td class="text-gray-600">تكلفة البضاعة المباعة</td>
                        <td class="text-left font-bold" dir="ltr">{{ number_format($report['cost_of_sales'], 2) }} ج.م</td>
                    </tr>
                    <tr style="background: var(--tf-green-soft);">
                        <td class="font-bold" style="color: #065f46;"><i class="fas fa-check-circle ml-2"></i>إجمالي الربح</td>
                        <td class="text-left font-bold" style="color: #065f46; font-size: 1.1rem;" dir="ltr">{{ number_format($report['gross_profit'], 2) }} ج.م</td>
                    </tr>

                    <tr>
                        <th colspan="2" class="tf-section-head" style="background: var(--tf-amber-soft); color: #b45309;">
                            <i class="fas fa-money-bill-wave ml-2"></i>المصروفات التشغيلية
                        </th>
                    </tr>
                    @forelse($expensesByCategory as $expense)
                    <tr>
                        <td class="text-gray-600"><i class="fas fa-circle text-orange-400 text-xs ml-2"></i>{{ $expense->category ?? 'غير مصنف' }} <span class="text-xs text-gray-400">({{ $expense->count }} عملية)</span></td>
                        <td class="text-left font-bold" dir="ltr">{{ number_format($expense->total_amount, 2) }} ج.م</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center text-gray-500 py-3">لا توجد مصروفات</td></tr>
                    @endforelse
                    <tr style="background: var(--tf-amber-soft);">
                        <td class="font-bold" style="color: #b45309;">إجمالي المصروفات</td>
                        <td class="text-left font-bold" style="color: #b45309; font-size: 1.1rem;" dir="ltr">{{ number_format($report['total_expenses'], 2) }} ج.م</td>
                    </tr>

                    <tr style="background: linear-gradient(135deg, #047857, #10b981); color: white;">
                        <td class="px-4 py-4 font-bold text-lg"><i class="fas fa-trophy ml-2"></i>صافي الربح / (الخسارة)</td>
                        <td class="px-4 py-4 text-left font-bold text-xl" dir="ltr">{{ number_format($report['net_profit'], 2) }} ج.م</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Profit Distribution -->
    <div class="tf-card tf-section" style="animation-delay: 0.20s;">
        <div class="p-5">
            <h3 class="text-base font-bold mb-4" style="color: var(--tf-text-h);">
                <i class="fas fa-chart-pie" style="color: var(--tf-green); margin-left: 6px;"></i>
                توزيع الإيرادات والتكاليف
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold" style="color: var(--tf-text-b);">الإيرادات</span>
                        <span class="text-sm font-bold" style="color: var(--tf-blue);" dir="ltr">{{ number_format($report['net_sales'], 0) }} ج.م</span>
                    </div>
                    <div class="tf-bar">
                        <div class="tf-bar-fill" style="background: linear-gradient(135deg, #0284c7, #38bdf8); width: 100%;"></div>
                    </div>
                </div>
                @php 
                    $costPercent = $report['net_sales'] > 0 ? ($report['cost_of_sales'] / $report['net_sales'] * 100) : 0;
                    $expensePercent = $report['net_sales'] > 0 ? ($report['total_expenses'] / $report['net_sales'] * 100) : 0;
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold" style="color: var(--tf-text-b);">التكاليف</span>
                        <span class="text-sm font-bold" style="color: var(--tf-violet);" dir="ltr">{{ number_format($report['cost_of_sales'], 0) }} ج.م</span>
                    </div>
                    <div class="tf-bar">
                        <div class="tf-bar-fill" style="background: linear-gradient(135deg, #7c3aed, #a78bfa); width: {{ $costPercent }}%;"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold" style="color: var(--tf-text-b);">المصروفات</span>
                        <span class="text-sm font-bold" style="color: var(--tf-amber);" dir="ltr">{{ number_format($report['total_expenses'], 0) }} ج.م</span>
                    </div>
                    <div class="tf-bar">
                        <div class="tf-bar-fill" style="background: linear-gradient(135deg, #d97706, #fbbf24); width: {{ $expensePercent }}%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection