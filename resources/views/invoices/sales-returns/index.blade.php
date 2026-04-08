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
            radial-gradient(ellipse 80% 60% at 10% -10%,  rgba(232,75,90,0.12) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(124,92,236,0.1) 0%, transparent 50%);
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
    .tf-section:nth-child(4) { animation-delay: 0.28s; }

    .tf-card {
        background: var(--tf-surface); border-radius: 20px;
        border: 1px solid var(--tf-border);
        overflow: hidden; box-shadow: var(--tf-shadow-card);
        margin-bottom: 20px; position: relative;
        transition: all .35s cubic-bezier(.22,1,.36,1);
    }
    .tf-card:hover { transform: translateY(-3px); box-shadow: var(--tf-shadow-lg); }

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
    .tf-card.blue .tf-card-icon { background: var(--tf-blue); color: var(--tf-surface); }
    .tf-card.green .tf-card-icon { background: var(--tf-green); color: var(--tf-surface); }
    .tf-card.violet .tf-card-icon { background: var(--tf-violet); color: var(--tf-surface); }

    .tf-title-text { font-size: 18px; font-weight: 800; color: var(--tf-text-h); }
    .tf-title-sub { font-size: 12px; color: var(--tf-text-m); font-weight: 600; }

    .tf-card-body { padding: 24px; }

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
    .tf-btn-secondary {
        background: var(--tf-surface); color: var(--tf-text-b);
        border: 1.5px solid var(--tf-border);
    }
    .tf-btn-secondary:hover { background: var(--tf-surface2); }

    .tf-input, .tf-select {
        width: 100%; padding: 12px 16px;
        border: 1.5px solid var(--tf-border); border-radius: 14px;
        font-size: 14px; font-family: 'Cairo', sans-serif;
        color: var(--tf-text-h); background: var(--tf-surface);
        transition: all .25s; outline: none;
    }
    .tf-input:focus, .tf-select:focus {
        border-color: var(--tf-red);
        box-shadow: 0 0 0 3px rgba(232,75,90,0.12);
    }

    .tf-label {
        display: block; font-size: 12px; font-weight: 700;
        color: var(--tf-text-m); margin-bottom: 6px;
    }

    .tf-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
    @media (max-width: 1100px) { .tf-stats-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px) { .tf-stats-grid { grid-template-columns: 1fr; } }

    .tf-stat-card {
        padding: 18px 20px; border-radius: 16px;
        position: relative; overflow: hidden;
    }
    .tf-stat-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
    }
    .tf-stat-card.red::before   { background: linear-gradient(90deg, var(--tf-red), var(--tf-violet)); }
    .tf-stat-card.blue::before  { background: linear-gradient(90deg, var(--tf-blue), var(--tf-indigo)); }
    .tf-stat-card.green::before { background: linear-gradient(90deg, var(--tf-green), var(--tf-indigo)); }
    .tf-stat-card.violet::before{ background: linear-gradient(90deg, var(--tf-violet), var(--tf-indigo)); }

    .tf-stat-card { background: var(--tf-surface); border: 1px solid var(--tf-border); }
    .tf-stat-card.red   { border-color: rgba(232,75,90,0.2); }
    .tf-stat-card.blue  { border-color: rgba(58,142,240,0.2); }
    .tf-stat-card.green { border-color: rgba(15,170,126,0.2); }
    .tf-stat-card.violet{ border-color: rgba(124,92,236,0.2); }

    .tf-stat-label { font-size: 11px; font-weight: 700; color: var(--tf-text-m); margin-bottom: 6px; text-transform: uppercase; }
    .tf-stat-value { font-size: 24px; font-weight: 900; color: var(--tf-text-h); font-family: 'Cairo', sans-serif; }
    .tf-stat-card.red .tf-stat-value   { color: var(--tf-red); }
    .tf-stat-card.blue .tf-stat-value  { color: var(--tf-blue); }
    .tf-stat-card.green .tf-stat-value { color: var(--tf-green); }
    .tf-stat-card.violet .tf-stat-value{ color: var(--tf-violet); }

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

    .tf-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 50px;
        font-size: 11px; font-weight: 800;
    }
    .tf-badge.green  { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-badge.amber  { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .tf-badge.red    { background: var(--tf-red-soft); color: var(--tf-red); }
    .tf-badge.gray   { background: var(--tf-surface2); color: var(--tf-text-m); }

    .tf-action-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 36px; height: 36px; border-radius: 10px;
        cursor: pointer; transition: all .2s; border: none;
    }
    .tf-action-btn.view { background: var(--tf-blue-soft); color: var(--tf-blue); }
    .tf-action-btn.view:hover { background: var(--tf-blue); color: var(--tf-surface); }
    .tf-action-btn.del { background: var(--tf-red-soft); color: var(--tf-red); }
    .tf-action-btn.del:hover { background: var(--tf-red); color: var(--tf-surface); }

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

    .tf-filters { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
    @media (max-width: 900px) { .tf-filters { grid-template-columns: repeat(2, 1fr); } }
</style>
@endpush

@section('content')
<div class="tf-page">
    <div class="tf-stats-grid tf-section">
        <div class="tf-stat-card red">
            <div class="tf-stat-label">إجمالي المرتجعات</div>
            <div class="tf-stat-value">{{ $statistics['total_count'] ?? 0 }}</div>
        </div>
        <div class="tf-stat-card blue">
            <div class="tf-stat-label">مرتجعات اليوم</div>
            <div class="tf-stat-value">{{ $statistics['today_count'] ?? 0 }}</div>
        </div>
        <div class="tf-stat-card green">
            <div class="tf-stat-label">إجمالي القيمة</div>
            <div class="tf-stat-value">{{ number_format($statistics['total_amount'] ?? 0, 2) }}</div>
        </div>
        <div class="tf-stat-card violet">
            <div class="tf-stat-label">إجمالي الأصناف</div>
            <div class="tf-stat-value">{{ $statistics['total_items'] ?? 0 }}</div>
        </div>
    </div>

    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon red"><i class="fas fa-undo-alt"></i></div>
                <div>
                    <h2 class="tf-title-text">قائمة المرتجعات</h2>
                    <p class="tf-title-sub">إدارة ومتابعة مرتجعات المبيعات</p>
                </div>
            </div>
            <a href="{{ route('invoices.sales-returns.create') }}" class="tf-btn tf-btn-primary">
                <i class="fas fa-plus"></i> إضافة مرتجع جديد
            </a>
        </div>
    </div>

    <div class="tf-card tf-section">
        <div class="tf-card-body" style="padding-bottom: 0;">
            <form method="GET" action="{{ route('invoices.sales-returns.index') }}">
                <div class="tf-filters">
                    <div>
                        <label class="tf-label">رقم المرتجع</label>
                        <input type="text" name="return_number" value="{{ request('return_number') }}" placeholder="SR20260126..." class="tf-input">
                    </div>
                    <div>
                        <label class="tf-label">من تاريخ</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="tf-input">
                    </div>
                    <div>
                        <label class="tf-label">إلى تاريخ</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="tf-input">
                    </div>
                    <div style="display: flex; gap: 8px; align-items: flex-end;">
                        <button type="submit" class="tf-btn tf-btn-primary" style="flex: 1;">
                            <i class="fas fa-search"></i> بحث
                        </button>
                        <a href="{{ route('invoices.sales-returns.index') }}" class="tf-btn tf-btn-secondary">
                            <i class="fas fa-times"></i> مسح
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="tf-table-wrapper">
            <table class="tf-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>رقم المرتجع</th>
                        <th>رقم الفاتورة</th>
                        <th>التاريخ</th>
                        <th>العميل</th>
                        <th>القيمة</th>
                        <th>الحالة</th>
                        <th style="text-align: center;">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $index => $return)
                    <tr>
                        <td>{{ $returns->firstItem() + $index }}</td>
                        <td><span class="tf-code">{{ $return->return_number }}</span></td>
                        <td><span style="color: var(--tf-blue); font-weight: 700;">{{ $return->salesInvoice->invoice_number ?? '-' }}</span></td>
                        <td>{{ $return->return_date->format('Y-m-d') }}</td>
                        <td>{{ $return->salesInvoice->customer->name ?? '-' }}</td>
                        <td><span style="font-weight: 800; color: var(--tf-green);">{{ number_format($return->total, 2) }} ج.م</span></td>
                        <td>
                            @if($return->status === 'confirmed')
                                <span class="tf-badge green">✓ مؤكد</span>
                            @elseif($return->status === 'cancelled')
                                <span class="tf-badge red">✗ ملغي</span>
                            @else
                                <span class="tf-badge gray">⊙ مسودة</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; justify-content: center; gap: 6px;">
                                <a href="{{ route('invoices.sales-returns.show', $return->id) }}" class="tf-action-btn view" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($return->status !== 'cancelled')
                                <form method="POST" action="{{ route('invoices.sales-returns.destroy', $return->id) }}" onsubmit="return confirm('⚠️ هل أنت متأكد من إلغاء هذا المرتجع؟')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="tf-action-btn del" title="إلغاء">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="tf-empty">
                                <div class="tf-empty-icon"><i class="fas fa-undo-alt"></i></div>
                                <h3 class="tf-empty-title">لا توجد مرتجعات</h3>
                                <p class="tf-empty-sub">ابدأ بإضافة مرتجع مبيعات جديد</p>
                                <a href="{{ route('invoices.sales-returns.create') }}" class="tf-btn tf-btn-primary">
                                    <i class="fas fa-plus"></i> إضافة مرتجع
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div style="padding: 16px 24px; border-top: 1px solid var(--tf-border-soft); background: var(--tf-surface2);">
            {{ $returns->links() }}
        </div>
        @endif
    </div>
</div>
@endsection