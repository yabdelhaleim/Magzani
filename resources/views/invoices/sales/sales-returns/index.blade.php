@extends('layouts.app')

@section('title', 'مرتجعات المبيعات')
@section('page-title', 'مرتجعات المبيعات')

@push('styles')
<style>
    :root {
        --tf-bg:          #f4f7fe;
        --tf-surface:     #ffffff;
        --tf-surface2:    #f8faff;
        --tf-border:      #e4eaf7;
        --tf-border-soft: #edf0f9;

        --tf-indigo:      #4f63d2;
        --tf-indigo-light:#7088e8;
        --tf-indigo-soft: #eef0fc;

        --tf-blue:        #3a8ef0;
        --tf-blue-soft:   #e8f2ff;
        --tf-green:       #0faa7e;
        --tf-green-soft:  #e6f8f3;
        --tf-red:         #dc2626;
        --tf-red-soft:    #fee2e2;
        --tf-amber:       #e8930a;
        --tf-amber-soft:  #fff4e0;
        --tf-violet:      #7c5cec;
        --tf-violet-soft: #f0ecff;

        --tf-text-h:      #1a2140;
        --tf-text-b:      #3d4f72;
        --tf-text-m:      #7e90b0;
        --tf-text-d:      #94a3b8;

        --tf-shadow-sm:   0 2px 12px rgba(79,99,210,0.07);
        --tf-shadow-card: 0 2px 0 0 rgba(0,0,0,0.04), 0 4px 20px rgba(79,99,210,0.08);
        --tf-shadow-lg:   0 8px 30px rgba(79,99,210,0.10);
    }

    .tf-page {
        background: var(--tf-bg);
        background-image:
            radial-gradient(ellipse 80% 60% at 10% -10%,  rgba(79,99,210,0.12) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(232,75,90,0.1) 0%, transparent 50%);
        min-height: 100vh;
        padding: 26px 22px;
    }

    @keyframes tfFadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes tfShimmer {
        0%   { background-position: -600px 0; }
        100% { background-position: 600px 0; }
    }
    @keyframes iconBounce {
        0%,100% { transform: translateY(0) rotate(0deg); }
        30%     { transform: translateY(-4px) rotate(-8deg); }
        60%     { transform: translateY(-2px) rotate(4deg); }
    }

    .tf-section { animation: tfFadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .tf-section:nth-child(1) { animation-delay: 0.04s; }
    .tf-section:nth-child(2) { animation-delay: 0.12s; }
    .tf-section:nth-child(3) { animation-delay: 0.20s; }

    .tf-card {
        background: var(--tf-surface); border-radius: 20px;
        border: 1px solid var(--tf-border);
        overflow: hidden; box-shadow: var(--tf-shadow-card);
        margin-bottom: 20px; position: relative;
        transition: all .35s cubic-bezier(.22,1,.36,1);
    }
    .tf-card:hover { transform: translateY(-3px); box-shadow: var(--tf-shadow-lg); }
    .tf-card::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.45) 50%, transparent 60%);
        background-size: 600px 100%; opacity: 0; pointer-events: none; transition: opacity .3s;
    }
    .tf-card:hover::after { opacity: 1; animation: tfShimmer .7s ease forwards; }

    .tf-card-head {
        display: flex; justify-content: space-between; align-items: center;
        padding: 20px 24px; border-bottom: 1px solid var(--tf-border-soft);
        background: linear-gradient(135deg, var(--tf-red-soft), var(--tf-red-soft)); flex-wrap: wrap; gap: 12px;
    }
    .tf-card-title { display: flex; align-items: center; gap: 12px; }
    .tf-card-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        transition: transform .4s cubic-bezier(.34,1.56,.64,1);
    }
    .tf-card:hover .tf-card-icon { animation: iconBounce .6s ease; }
    .tf-card.red .tf-card-icon { background: var(--tf-red); color: var(--tf-surface); }
    .tf-card.orange .tf-card-icon { background: var(--tf-amber); color: var(--tf-surface); }
    .tf-card.yellow .tf-card-icon { background: #f59e0b; color: var(--tf-surface); }

    .tf-title-text { font-size: 18px; font-weight: 800; color: var(--tf-text-h); }
    .tf-title-sub { font-size: 12px; color: var(--tf-text-m); font-weight: 600; }

    .tf-card-body { padding: 20px; }

    .tf-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 20px; border-radius: 14px;
        font-size: 14px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif; text-decoration: none;
        transition: all .25s; border: none;
    }
    .tf-btn-primary {
        background: linear-gradient(135deg, var(--tf-red), #d63c4c);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(232,75,90,0.35);
    }
    .tf-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(232,75,90,0.45); }

    .tf-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    @media (max-width: 900px) { .tf-grid-3 { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px) { .tf-grid-3 { grid-template-columns: 1fr; } }

    .tf-table-wrapper { overflow-x: auto; }
    .tf-table { width: 100%; border-collapse: collapse; }
    .tf-table thead th {
        padding: 14px 16px; text-align: right;
        font-size: 11px; font-weight: 800; color: var(--tf-text-m);
        text-transform: uppercase; letter-spacing: .5px;
        border-bottom: 1.5px solid var(--tf-border-soft);
        background: var(--tf-surface2); white-space: nowrap;
    }
    .tf-table tbody tr { transition: background .18s; }
    .tf-table tbody tr:hover { background: var(--tf-surface2); }
    .tf-table tbody td { padding: 14px 16px; border-bottom: 1px solid var(--tf-border-soft); vertical-align: middle; }

    .tf-stat-card {
        padding: 18px 20px; border-radius: 16px;
        position: relative; overflow: hidden;
    }
    .tf-stat-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, var(--tf-red), var(--tf-violet));
    }
    .tf-stat-card { background: var(--tf-surface); border: 1px solid var(--tf-border); }
    .tf-stat-card.orange::before { background: linear-gradient(90deg, var(--tf-amber), var(--tf-red)); }
    .tf-stat-card.yellow::before { background: linear-gradient(90deg, #f59e0b, var(--tf-amber)); }

    .tf-stat-label { font-size: 11px; font-weight: 700; color: var(--tf-text-m); margin-bottom: 6px; text-transform: uppercase; }
    .tf-stat-value { font-size: 24px; font-weight: 900; color: var(--tf-red); font-family: 'Cairo', sans-serif; }
    .tf-stat-card.orange .tf-stat-value { color: var(--tf-amber); }
    .tf-stat-card.yellow .tf-stat-value { color: #f59e0b; }

    .tf-action-btn {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 8px 16px; border-radius: 10px;
        cursor: pointer; transition: all .2s; border: none;
        font-size: 13px; font-weight: 700;
    }
    .tf-action-btn.view { background: var(--tf-blue-soft); color: var(--tf-blue); }
    .tf-action-btn.view:hover { background: var(--tf-blue); color: var(--tf-surface); }

    .tf-code {
        display: inline-block; padding: 6px 12px;
        border-radius: 50px; font-size: 12px; font-weight: 800;
        background: var(--tf-red-soft); color: var(--tf-red);
    }

    .tf-empty {
        display: flex; flex-direction: column; align-items: center;
        padding: 50px 24px; text-align: center;
    }
    .tf-empty-icon {
        width: 80px; height: 80px; border-radius: 22px;
        display: flex; align-items: center; justify-content: center;
        font-size: 28px; margin-bottom: 16px;
        background: var(--tf-surface2); color: var(--tf-text-m);
    }
    .tf-empty-title { font-size: 16px; font-weight: 800; color: var(--tf-text-h); margin-bottom: 6px; }
    .tf-empty-sub { font-size: 13px; color: var(--tf-text-m); margin-bottom: 20px; }
</style>
@endpush

@section('content')
<div class="tf-page">
    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon red"><i class="fas fa-undo-alt"></i></div>
                <div>
                    <h2 class="tf-title-text">مرتجعات المبيعات</h2>
                    <p class="tf-title-sub">إدارة ومتابعة مرتجعات المبيعات</p>
                </div>
            </div>
            <a href="{{ route('invoices.sales-returns.create') }}" class="tf-btn tf-btn-primary">
                <i class="fas fa-plus"></i> إضافة مرتجع جديد
            </a>
        </div>
    </div>

    <div class="tf-stats-grid tf-section" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px;">
        <div class="tf-stat-card red">
            <div class="tf-stat-label">إجمالي المرتجعات</div>
            <div class="tf-stat-value">{{ $returns->total() }}</div>
        </div>
        <div class="tf-stat-card orange">
            <div class="tf-stat-label">قيمة المرتجعات</div>
            <div class="tf-stat-value">{{ number_format($returns->sum('total_amount'), 2) }}</div>
            <div style="font-size: 12px; color: var(--tf-text-m);">جنيه</div>
        </div>
        <div class="tf-stat-card yellow">
            <div class="tf-stat-label">مرتجعات اليوم</div>
            <div class="tf-stat-value">{{ $returns->where('created_at', '>=', today())->count() }}</div>
        </div>
    </div>

    <div class="tf-card tf-section">
        <div class="tf-table-wrapper">
            <table class="tf-table">
                <thead>
                    <tr>
                        <th>رقم المرتجع</th>
                        <th>رقم الفاتورة</th>
                        <th>التاريخ</th>
                        <th>العميل</th>
                        <th>القيمة</th>
                        <th>السبب</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                        <tr>
                            <td><span class="tf-code">{{ $return->code }}</span></td>
                            <td><span style="color: var(--tf-blue); font-weight: 700;">{{ $return->salesInvoice->invoice_number ?? '-' }}</span></td>
                            <td>{{ $return->created_at->format('Y-m-d') }}</td>
                            <td>{{ $return->salesInvoice->customer->name ?? '-' }}</td>
                            <td style="font-weight: 800;">{{ number_format($return->total_amount, 2) }}</td>
                            <td style="color: var(--tf-text-m); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $return->reason }}</td>
                            <td>
                                <a href="{{ route('invoices.sales-returns.show', $return->id) }}" class="tf-action-btn view">
                                    <i class="fas fa-eye"></i> عرض
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="tf-empty">
                                    <div class="tf-empty-icon"><i class="fas fa-undo-alt"></i></div>
                                    <h3 class="tf-empty-title">لا توجد مرتجعات</h3>
                                    <p class="tf-empty-sub">قم بإنشاء مرتجع جديد للبدء</p>
                                    <a href="{{ route('invoices.sales-returns.create') }}" class="tf-btn tf-btn-primary">
                                        <i class="fas fa-plus"></i> إنشاء مرتجع جديد
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div style="padding: 16px 24px; border-top: 1px solid var(--tf-border-soft); background: var(--tf-surface2); display: flex; justify-content: space-between; align-items: center;">
            {{ $returns->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
