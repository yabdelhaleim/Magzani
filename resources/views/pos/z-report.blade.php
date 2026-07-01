@extends('layouts.app')

@section('title', 'Z Report — تقرير إغلاق الوردية')
@section('page-title', 'Z Report — تقرير إغلاق الوردية')

@push('styles')
<style>
    :root {
        --tf-bg:          transparent;
        --tf-surface:     rgba(22, 33, 56, 0.6);
        --tf-surface2:    rgba(10, 16, 28, 0.55);
        --tf-border:      rgba(255, 255, 255, 0.06);
        --tf-indigo:      #6366f1;
        --tf-indigo-soft: rgba(99, 102, 241, 0.15);
        --tf-green:       #10b981;
        --tf-green-soft:  rgba(16, 185, 129, 0.15);
        --tf-red:         #ef4444;
        --tf-red-soft:    rgba(239, 68, 68, 0.15);
        --tf-amber:       #f59e0b;
        --tf-amber-soft:  rgba(245, 158, 11, 0.15);
        --tf-text-h:      #f1f5f9;
        --tf-text-b:      #cbd5e1;
        --tf-text-m:      #94a3b8;
        --tf-shadow-card: 0 8px 32px 0 rgba(0, 0, 0, 0.25);
        --radius-md:      16px;
    }

    /* Scoped Dark Mode Overrides for Immersive Cashier Experience */
    body, .main-content, #mainContent {
        background: radial-gradient(circle at top right, #131e35, #080d1a) !important;
        color: #e2e8f0 !important;
    }
    .sidebar {
        background: #070b14 !important;
        border-left: 1px solid rgba(255, 255, 255, 0.03) !important;
    }
    .sidebar * {
        color: rgba(226, 232, 240, 0.65) !important;
    }
    .sidebar .nav-item.active, .sidebar .nav-item.active * {
        background: rgba(16, 185, 129, 0.1) !important;
        color: #10b981 !important;
        border-right: 3px solid #10b981 !important;
    }
    .sidebar .nav-section-label {
        color: rgba(226, 232, 240, 0.3) !important;
    }
    .sidebar .nav-divider {
        border-color: rgba(255, 255, 255, 0.03) !important;
    }
    .main-header {
        background: #070b14 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
    }
    .main-header * {
        color: #e2e8f0 !important;
    }
    .main-footer {
        background: #070b14 !important;
        border-top: 1px solid rgba(255, 255, 255, 0.03) !important;
        color: rgba(226, 232, 240, 0.35) !important;
    }

    @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
    .animated { animation: fadeUp 0.35s ease both; }

    .zreport-page {
        max-width: 820px;
        margin: 0 auto;
        padding: 28px 20px;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
    }

    /* Header Banner */
    .report-banner {
        background: radial-gradient(circle at top right, #1f2937, #111827) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 20px; padding: 24px 28px;
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 24px; gap: 16px; flex-wrap: wrap;
        box-shadow: var(--tf-shadow-card);
    }
    .report-banner h1 {
        color: white; font-size: 22px; font-weight: 900; margin: 0;
        display: flex; align-items: center; gap: 10px;
    }
    .report-banner h1 .z-badge {
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        color: white; font-size: 13px; padding: 4px 12px;
        border-radius: 20px; font-weight: 900; letter-spacing: 1px;
    }
    .report-meta { color: rgba(255,255,255,0.6); font-size: 12px; font-weight: 600; margin-top: 6px; }

    /* Print button */
    .btn-print {
        background: var(--tf-indigo) !important; color: white; border: none;
        border-radius: 12px; padding: 10px 20px; font-size: 13px; font-weight: 800;
        cursor: pointer; transition: all 0.2s; text-decoration: none; display: flex; align-items: center; gap: 8px;
    }
    .btn-print:hover {
        background: #4f46e5 !important;
        box-shadow: 0 0 12px rgba(99, 102, 241, 0.3) !important;
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
    .icon-red    { background: var(--tf-red-soft);    color: var(--tf-red);    }
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
    .status-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255, 255, 255, 0.08) !important; color: #94a3b8 !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important; border-radius: 20px;
        padding: 4px 12px; font-size: 11px; font-weight: 800;
    }

    /* Back button */
    .btn-back {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(255, 255, 255, 0.04) !important; border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 12px; padding: 10px 18px; font-size: 13px; font-weight: 800;
        color: var(--tf-text-b); text-decoration: none; transition: all 0.2s;
        margin-bottom: 20px;
    }
    .btn-back:hover {
        border-color: rgba(255, 255, 255, 0.2) !important;
        background: rgba(255, 255, 255, 0.08) !important;
        color: #f1f5f9 !important;
    }

    @media print {
        .no-print { display: none !important; }
        body, .main-content, #mainContent { background: white !important; color: black !important; }
        .zreport-page { padding: 0; max-width: 100%; }
        .report-banner { background: #e2e8f0 !important; color: black !important; -webkit-print-color-adjust: exact; border: 1px solid #cbd5e1 !important; }
        .report-banner h1, .report-banner .report-meta, .report-banner .status-badge { color: black !important; }
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
<div class="zreport-page">

    {{-- Back & Print --}}
    <div class="no-print" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <a href="{{ route('pos.history') }}" class="btn-back">
            <i class="fas fa-arrow-right"></i> سجل الورديات
        </a>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('pos.index') }}" class="btn-back" style="margin-bottom:0; background:var(--tf-surface2);">
                <i class="fas fa-cash-register"></i> العودة للبيع
            </a>
            <button onclick="window.print()" class="btn-print" style="background:var(--tf-indigo); border-color:var(--tf-indigo); color:white; border-radius:12px; padding:10px 20px; font-size:13px; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-print"></i> طباعة التقرير (تقرير Z)
            </button>
        </div>
    </div>

    {{-- Success Message if closed --}}
    @if(session('success'))
        <div class="no-print" style="margin-bottom:20px; padding:14px 18px; background:var(--tf-green-soft); border:1px solid #6ee7b7; border-radius:12px; color:#065f46; font-weight:700; font-size:13px;" class="animated">
            <i class="fas fa-check-circle ml-2"></i>{{ session('success') }}
        </div>
    @endif

    {{-- Report Header --}}
    <div class="report-banner animated" style="animation-delay:0.05s">
        <div>
            <h1>
                <span class="z-badge">Z REPORT</span>
                تقرير إغلاق الوردية النهائي
            </h1>
            <div class="report-meta">
                المستخدم: {{ $shift->user->name ?? 'غير معروف' }} &nbsp;•&nbsp;
                رقم الوردية: #{{ $shift->id }} &nbsp;•&nbsp;
                المدة: {{ $shift->duration }}
            </div>
        </div>
        <div style="text-align:left; color:white;">
            <div class="status-badge" style="background:rgba(255,255,255,0.15); color:white; border:none;">
                <i class="fas fa-check-circle text-emerald-400"></i> وردية مغلقة
            </div>
            <div style="color:rgba(255,255,255,0.6); font-size:11px; font-weight:600; margin-top:6px; text-align:right;">
                مفتوح: {{ $shift->opened_at->format('d/m H:i') }} <br>
                مغلق: {{ $shift->closed_at ? $shift->closed_at->format('d/m H:i') : 'غير مغلق' }}
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
            <div class="num" style="color:var(--tf-indigo)">{{ number_format($shift->net_sales, 2) }}</div>
            <div class="lbl">صافي المبيعات (ج.م)</div>
        </div>
        <div class="stat-box">
            <div class="num" style="color:var(--tf-amber)">{{ number_format($shift->expected_cash, 2) }}</div>
            <div class="lbl">النقدية المتوقعة (ج.م)</div>
        </div>
    </div>

    {{-- Financial Reconciliation (Expected vs Actual) --}}
    <div class="report-card animated" style="animation-delay:0.11s">
        <div class="report-card-header">
            <i class="fas fa-balance-scale icon-indigo"></i>
            مطابقة الخزينة والصندوق (Reconciliation)
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
            <span class="rlabel">صافي المبيعات (Net Sales)</span>
            <span class="rvalue" style="color:var(--tf-indigo);">{{ number_format($shift->net_sales, 2) }} ج.م</span>
        </div>
        <div class="report-row" style="background:var(--tf-surface2); border-top:1.5px solid var(--tf-border);">
            <span class="rlabel" style="font-weight:800; color:var(--tf-text-h);">رصيد النقدية المتوقع (المبيعات النقدية فقط)</span>
            <span class="rvalue amber">{{ number_format($shift->expected_cash, 2) }} ج.م</span>
        </div>
        <div class="report-row" style="background:var(--tf-surface2);">
            <span class="rlabel" style="font-weight:900; color:var(--tf-text-h);">رصيد النقدية الفعلي (المُسلّم)</span>
            <span class="rvalue big green" style="color:var(--tf-indigo);">{{ number_format($shift->actual_cash, 2) }} ج.م</span>
        </div>
        @php $diff = (float) $shift->cash_difference; @endphp
        <div class="report-row" style="background: {{ $diff == 0 ? 'var(--tf-green-soft)' : ($diff > 0 ? 'var(--tf-amber-soft)' : 'var(--tf-red-soft)') }}">
            <span class="rlabel" style="font-weight:900; color:var(--tf-text-h);">العجز / الزيادة في النقدية</span>
            <span class="rvalue big {{ $diff == 0 ? 'green' : ($diff > 0 ? 'amber' : 'red') }}">
                {{ $diff == 0 ? 'مطابق تماماً' : ($diff > 0 ? '+' . number_format($diff, 2) . ' زيادة' : number_format($diff, 2) . ' عجز') }}
            </span>
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
                        <td colspan="3" style="text-align:center; color:var(--tf-text-m); padding:24px;">لا توجد مبيعات في هذه الوردية</td>
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

    {{-- Notes & Signatures --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 animated" style="animation-delay:0.17s">
        @if($shift->notes)
        <div class="report-card" style="margin-bottom:0;">
            <div class="report-card-header">
                <i class="fas fa-sticky-note icon-amber"></i>
                ملاحظات إغلاق الوردية
            </div>
            <div style="padding:16px; font-size:13px; color:var(--tf-text-b); font-weight:700; line-height:1.6;">
                {{ $shift->notes }}
            </div>
        </div>
        @endif

        <div class="report-card" style="margin-bottom:0;">
            <div class="report-card-header">
                <i class="fas fa-signature icon-indigo"></i>
                اعتماد وتوقيع التقرير
            </div>
            <div style="padding:16px; display:flex; justify-content:space-between; align-items:center; gap:20px; min-height:100px;">
                <div style="text-align:center; flex:1;">
                    <div style="border-bottom:1.5px dashed var(--tf-border); height:40px; margin-bottom:8px;"></div>
                    <span style="font-size:11px; font-weight:800; color:var(--tf-text-m);">توقيع الكاشير</span>
                </div>
                <div style="text-align:center; flex:1;">
                    <div style="border-bottom:1.5px dashed var(--tf-border); height:40px; margin-bottom:8px;"></div>
                    <span style="font-size:11px; font-weight:800; color:var(--tf-text-m);">توقيع المشرف / المدير</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div style="text-align:center; color:var(--tf-text-m); font-size:11px; font-weight:600; margin-top:30px;" class="animated no-print">
        <i class="fas fa-info-circle" style="margin-left:4px;"></i>
        تقرير Z هو تقرير نهائي يتم توليده وتوقيعه عند إقفال الصندوق.
    </div>

</div>
@endsection
