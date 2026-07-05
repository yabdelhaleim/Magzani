@extends('layouts.app')

@section('title', 'X Report — تقرير الوردية الحالية')
@section('page-title', 'X Report — تقرير الوردية')

@push('styles')
<style>
        :root {
        --tf-bg:          transparent;
        --tf-surface:     #ffffff;
        --tf-surface2:    #f8fafc;
        --tf-border:      #e2e8f0;
        --tf-border-soft: #f1f5f9;
        --tf-indigo:      #2563eb;
        --tf-indigo-light:#3b82f6;
        --tf-indigo-soft: rgba(37, 99, 235, 0.1);
        --tf-blue:        #3b82f6;
        --tf-blue-soft:   rgba(37, 99, 235, 0.1);
        --tf-green:       #2563eb;
        --tf-green-soft:  rgba(37, 99, 235, 0.1);
        --tf-red:         #ef4444;
        --tf-red-soft:    rgba(239, 68, 68, 0.15);
        --tf-amber:       #f59e0b;
        --tf-amber-soft:  rgba(245, 158, 11, 0.15);
        --tf-text-h:      #0f172a;
        --tf-text-b:      #334155;
        --tf-text-m:      #64748b;
        --tf-text-d:      #94a3b8;
        --tf-text-s:      #475569;
        --tf-shadow-sm:   0 2px 12px rgba(0,0,0,0.05);
        --tf-shadow-card: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        --tf-shadow-lg:   0 10px 25px -5px rgba(0, 0, 0, 0.05);
        --radius-lg:      20px;
        --radius-md:      14px;
        --radius-sm:      8px;
    }

    

    @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
    .animated { animation: fadeUp 0.35s ease both; }

    .xreport-page {
        max-width: 820px;
        margin: 0 auto;
        padding: 28px 20px;
        
        -webkit-
    }

    /* Header Banner */
    .report-banner {
        background: linear-gradient(135deg, var(--navy) 0%, var(--navy-3) 100%) !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 20px; padding: 24px 28px;
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 24px; gap: 16px; flex-wrap: wrap;
        box-shadow: var(--tf-shadow-card);
    }
    .report-banner h1 {
        color: white; font-size: 22px; font-weight: 900; margin: 0;
        display: flex; align-items: center; gap: 10px;
    }
    .report-banner h1 .x-badge {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white; font-size: 13px; padding: 4px 12px;
        border-radius: 20px; font-weight: 900; letter-spacing: 1px;
    }
    .report-meta { color: rgba(255,255,255,0.55); font-size: 12px; font-weight: 600; margin-top: 6px; }

    /* Print button */
    .btn-print {
        background: var(--tf-indigo) !important; color: white; border: none;
        border-radius: 12px; padding: 10px 20px; font-size: 13px; font-weight: 800;
        cursor: pointer; transition: all 0.2s; text-decoration: none; display: flex; align-items: center; gap: 8px;
    }
    .btn-print:hover {
        background: #4f46e5 !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    }

    /* Stats grid */
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
    @media(max-width:700px) { .stats-grid { grid-template-columns: 1fr 1fr; } }
    .stat-box {
        background: var(--tf-surface); border: 1px solid var(--tf-border);
        border-radius: var(--radius-md); padding: 18px 16px;
        box-shadow: var(--tf-shadow-card); text-align: center;
    }
    .stat-box .num { font-size: 26px; font-weight: 900; color: var(--tf-text-h); }
    .stat-box .lbl { font-size: 11px; font-weight: 700; color: var(--tf-text-m); margin-top: 4px; }

    /* Cards */
    .report-card {
        background: var(--tf-surface); border: 1px solid var(--tf-border);
        border-radius: var(--radius-md); box-shadow: var(--tf-shadow-card);
        margin-bottom: 16px; overflow: hidden;
    }
    .report-card-header {
        background: var(--tf-surface2); padding: 14px 20px;
        border-bottom: 1px solid var(--tf-border);
        display: flex; align-items: center; gap: 10px;
        font-size: 13px; font-weight: 900; color: var(--tf-text-h);
    }
    .report-card-header i { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; }
    .icon-indigo { background: var(--tf-indigo-soft); color: var(--tf-indigo); }
    .icon-green  { background: var(--tf-green-soft);  color: var(--tf-green);  }
    .icon-amber  { background: var(--tf-amber-soft);  color: var(--tf-amber);  }

    .report-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 20px; border-bottom: 1px solid var(--tf-border);
        font-size: 13px;
    }
    .report-row:last-child { border-bottom: none; }
    .report-row .rlabel { font-weight: 700; color: var(--tf-text-m); }
    .report-row .rvalue { font-weight: 900; color: var(--tf-text-h); }
    .rvalue.green { color: var(--tf-green); }
    .rvalue.red   { color: var(--tf-red);   }
    .rvalue.amber { color: var(--tf-amber); }
    .rvalue.big   { font-size: 17px; }

    /* Payment breakdown table */
    table { width: 100%; border-collapse: collapse; }
    thead th { padding: 10px 20px; text-align: right; font-size: 11px; font-weight: 800; color: var(--tf-text-m); background: var(--tf-surface2); border-bottom: 2px solid var(--tf-border); }
    tbody td { padding: 12px 20px; font-size: 13px; font-weight: 700; color: var(--tf-text-b); border-bottom: 1px solid var(--tf-border); }
    tbody tr:last-child td { border-bottom: none; }

    /* Live timer */
    .live-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--tf-green-soft); color: var(--tf-green);
        border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 20px;
        padding: 4px 12px; font-size: 11px; font-weight: 800;
    }
    .live-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--tf-green); animation: pulse 1.5s infinite; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.3} }

    /* Back button */
    .btn-back {
        display: inline-flex; align-items: center; gap: 8px;
        background: #f8fafc !important; border: 1px solid #cbd5e1 !important;
        border-radius: 12px; padding: 10px 18px; font-size: 13px; font-weight: 800;
        color: var(--tf-text-b); text-decoration: none; transition: all 0.2s;
        margin-bottom: 20px;
    }
    .btn-back:hover {
        border-color: rgba(255, 255, 255, 0.2) !important;
        background: #cbd5e1 !important;
        color: #0f172a !important;
    }

    @media print {
        .no-print { display: none !important; }
        body, .main-content, #mainContent { background: white !important; color: black !important; }
        .xreport-page { padding: 0; max-width: 100%; }
        .report-banner { background: #e2e8f0 !important; color: black !important; -webkit-print-color-adjust: exact; border: 1px solid #cbd5e1 !important; }
        .report-banner h1, .report-banner .report-meta, .report-banner .live-badge { color: black !important; }
        .stat-box { background: white !important; border: 1px solid #cbd5e1 !important; color: black !important; }
        .stat-box .num, .stat-box .lbl { color: black !important; }
        .report-card { background: white !important; border: 1px solid #cbd5e1 !important; }
        .report-card-header { background: #f1f5f9 !important; color: black !important; border-bottom: 1px solid #cbd5e1 !important; }
        .report-row { border-bottom: 1px solid #e2e8f0 !important; }
        .report-row .rlabel, .report-row .rvalue { color: black !important; }
        thead th { background: #f1f5f9 !important; color: black !important; border-bottom: 1px solid #cbd5e1 !important; }
        tbody td { color: black !important; border-bottom: 1px solid #e2e8f0 !important; }
    }
</style>
@endpush

@section('content')
<div class="xreport-page">

    {{-- Back & Print --}}
    <div class="no-print" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <a href="{{ route('pos.index') }}" class="btn-back">
            <i class="fas fa-arrow-right"></i> رجوع للكاشير
        </a>
        <button onclick="window.print()" class="btn-print" style="background:var(--tf-indigo); border-color:var(--tf-indigo); color:white; border-radius:12px; padding:10px 20px; font-size:13px; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:8px;">
            <i class="fas fa-print"></i> طباعة التقرير
        </button>
    </div>

    {{-- Report Header --}}
    <div class="report-banner animated" style="animation-delay:0.05s">
        <div>
            <h1>
                <span class="x-badge">X REPORT</span>
                تقرير الوردية الحالية
            </h1>
            <div class="report-meta">
                {{ $shift->user->name ?? 'غير معروف' }} &nbsp;•&nbsp;
                فتحت: {{ $shift->opened_at->format('d/m/Y — H:i') }} &nbsp;•&nbsp;
                المدة: {{ $shift->duration }}
            </div>
        </div>
        <div style="text-align:center;">
            <div class="live-badge">
                <span class="live-dot"></span> مباشر — بدون إغلاق
            </div>
            <div style="color:rgba(255,255,255,0.5); font-size:11px; font-weight:600; margin-top:6px;">
                {{ now()->format('H:i:s — d/m/Y') }}
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="stats-grid animated" style="animation-delay:0.08s">
        <div class="stat-box">
            <div class="num" style="color:var(--tf-green)">{{ $shift->sales_count }}</div>
            <div class="lbl">فاتورة بيع</div>
        </div>
        <div class="stat-box">
            <div class="num" style="color:var(--tf-red)">{{ $shift->returns_count }}</div>
            <div class="lbl">مرتجع</div>
        </div>
        <div class="stat-box">
            <div class="num" style="color:var(--tf-indigo)">{{ number_format($shift->total_sales - $shift->total_returns, 0) }}</div>
            <div class="lbl">صافي المبيعات (ج.م)</div>
        </div>
        <div class="stat-box">
            <div class="num" style="color:var(--tf-amber)">{{ number_format($expectedBalance, 0) }}</div>
            <div class="lbl">الرصيد المتوقع (ج.م)</div>
        </div>
    </div>

    {{-- Financial Summary --}}
    <div class="report-card animated" style="animation-delay:0.11s">
        <div class="report-card-header">
            <i class="fas fa-calculator icon-indigo"></i>
            الملخص المالي للوردية
        </div>
        <div class="report-row">
            <span class="rlabel">رصيد افتتاح الصندوق</span>
            <span class="rvalue">{{ number_format($shift->opening_balance, 2) }} ج.م</span>
        </div>
        <div class="report-row">
            <span class="rlabel">إجمالي المبيعات</span>
            <span class="rvalue green">+ {{ number_format($shift->total_sales, 2) }} ج.م</span>
        </div>
        <div class="report-row">
            <span class="rlabel">إجمالي المرتجعات</span>
            <span class="rvalue red">- {{ number_format($shift->total_returns, 2) }} ج.م</span>
        </div>
        <div class="report-row">
            <span class="rlabel">صافي المبيعات</span>
            <span class="rvalue" style="color:var(--tf-indigo);">{{ number_format($shift->total_sales - $shift->total_returns, 2) }} ج.م</span>
        </div>
        <div class="report-row" style="background:var(--tf-surface2);">
            <span class="rlabel" style="font-weight:900; color:var(--tf-text-h);">رصيد النقدية المتوقع (المبيعات النقدية فقط)</span>
            <span class="rvalue big amber">{{ number_format($expectedBalance, 2) }} ج.م</span>
        </div>
    </div>

    {{-- Payment Breakdown --}}
    <div class="report-card animated" style="animation-delay:0.14s">
        <div class="report-card-header">
            <i class="fas fa-credit-card icon-green"></i>
            توزيع المبيعات حسب طريقة الدفع
        </div>
        <table>
            <thead>
                <tr>
                    <th>طريقة الدفع</th>
                    <th>عدد الفواتير</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $methodLabels = [
                        'cash'     => ['label' => 'نقدي',   'icon' => 'fa-money-bill',  'color' => 'green'],
                        'card'     => ['label' => 'شبكة',   'icon' => 'fa-credit-card', 'color' => 'indigo'],
                        'credit'   => ['label' => 'آجل',    'icon' => 'fa-file-invoice','color' => 'amber'],
                        'multiple' => ['label' => 'متعدد',  'icon' => 'fa-layer-group', 'color' => 'amber'],
                    ];
                    $grandSales = 0;
                    $grandCount = 0;
                @endphp
                @forelse($salesByMethod as $method => $row)
                    @php
                        $info = $methodLabels[$method] ?? ['label' => $method, 'icon' => 'fa-circle', 'color' => ''];
                        $grandSales += $row->total;
                        $grandCount += $row->count;
                    @endphp
                    <tr>
                        <td>
                            <i class="fas {{ $info['icon'] }}" style="margin-left:6px; color:var(--tf-{{ $info['color'] }});"></i>
                            {{ $info['label'] }}
                        </td>
                        <td>{{ $row->count }} فاتورة</td>
                        <td style="font-weight:900; color:var(--tf-text-h);">{{ number_format($row->total, 2) }} ج.م</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align:center; color:var(--tf-text-m); padding:24px;">لا توجد مبيعات في هذه الوردية حتى الآن</td>
                    </tr>
                @endforelse
                @if($grandCount > 0)
                    <tr style="background:var(--tf-surface2); font-weight:900;">
                        <td style="color:var(--tf-text-h); font-weight:900;"><i class="fas fa-sigma" style="margin-left:6px;"></i> الإجمالي</td>
                        <td style="color:var(--tf-text-h);">{{ $grandCount }} فاتورة</td>
                        <td style="color:var(--tf-indigo); font-size:15px; font-weight:900;">{{ number_format($grandSales, 2) }} ج.م</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Returns Summary --}}
    @if($shift->returns_count > 0)
    <div class="report-card animated" style="animation-delay:0.17s">
        <div class="report-card-header">
            <i class="fas fa-undo-alt icon-amber"></i>
            ملخص المرتجعات
        </div>
        <div class="report-row">
            <span class="rlabel">عدد المرتجعات</span>
            <span class="rvalue red">{{ $shift->returns_count }}</span>
        </div>
        <div class="report-row">
            <span class="rlabel">إجمالي المرتجعات</span>
            <span class="rvalue red">- {{ number_format($shift->total_returns, 2) }} ج.م</span>
        </div>
    </div>
    @endif

    {{-- Footer Note --}}
    <div style="text-align:center; color:var(--tf-text-m); font-size:11px; font-weight:600; margin-top:20px;" class="animated no-print">
        <i class="fas fa-info-circle" style="margin-left:4px;"></i>
        هذا التقرير لحظي — لا يُغلق الوردية. لإغلاق الوردية اذهب إلى
        <a href="{{ route('pos.shift.close-view') }}" style="color:var(--tf-indigo); font-weight:800;">إغلاق الوردية</a>
    </div>

</div>
@endsection
