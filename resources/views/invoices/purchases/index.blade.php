@extends('layouts.app')

@section('title', 'فواتير المشتريات')
@section('page-title', 'فواتير المشتريات')

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
            radial-gradient(ellipse 80% 60% at 10% -10%,  rgba(124,92,236,0.12) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(79,99,210,0.1) 0%, transparent 50%);
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
        background: linear-gradient(135deg, var(--tf-violet-soft), var(--tf-indigo-soft)); flex-wrap: wrap; gap: 12px;
    }
    .tf-card-title { display: flex; align-items: center; gap: 12px; }
    .tf-card-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        transition: transform .4s cubic-bezier(.34,1.56,.64,1);
    }
    .tf-card:hover .tf-card-icon { animation: iconBounce .6s ease; }
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
        background: linear-gradient(135deg, var(--tf-violet), #6a4dd0);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(124,92,236,0.35);
    }
    .tf-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(124,92,236,0.45); }
    .tf-btn-secondary {
        background: var(--tf-surface); color: var(--tf-text-b);
        border: 1.5px solid var(--tf-border);
    }
    .tf-btn-secondary:hover { background: var(--tf-surface2); }
    .tf-btn-green {
        background: linear-gradient(135deg, var(--tf-green), #0d8a6e);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(15,170,126,0.35);
    }
    .tf-btn-green:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15,170,126,0.45); }

    .tf-input, .tf-select {
        width: 100%; padding: 12px 16px;
        border: 1.5px solid var(--tf-border); border-radius: 14px;
        font-size: 14px; font-family: 'Cairo', sans-serif;
        color: var(--tf-text-h); background: var(--tf-surface);
        transition: all .25s; outline: none;
    }
    .tf-input:focus, .tf-select:focus {
        border-color: var(--tf-violet);
        box-shadow: 0 0 0 3px rgba(124,92,236,0.12);
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
    .tf-stat-card.blue::before   { background: linear-gradient(90deg, var(--tf-indigo), var(--tf-blue)); }
    .tf-stat-card.violet::before { background: linear-gradient(90deg, var(--tf-violet), var(--tf-indigo)); }
    .tf-stat-card.amber::before  { background: linear-gradient(90deg, var(--tf-amber), var(--tf-red)); }
    .tf-stat-card.green::before  { background: linear-gradient(90deg, var(--tf-green), var(--tf-indigo)); }

    .tf-stat-card { background: var(--tf-surface); border: 1px solid var(--tf-border); }
    .tf-stat-card.blue   { border-color: rgba(58,142,240,0.2); }
    .tf-stat-card.violet { border-color: rgba(124,92,236,0.2); }
    .tf-stat-card.amber  { border-color: rgba(232,147,10,0.2); }
    .tf-stat-card.green  { border-color: rgba(15,170,126,0.2); }

    .tf-stat-label { font-size: 11px; font-weight: 700; color: var(--tf-text-m); margin-bottom: 6px; text-transform: uppercase; }
    .tf-stat-value { font-size: 24px; font-weight: 900; color: var(--tf-text-h); font-family: 'Cairo', sans-serif; }
    .tf-stat-card.blue .tf-stat-value   { color: var(--tf-blue); }
    .tf-stat-card.violet .tf-stat-value { color: var(--tf-violet); }
    .tf-stat-card.amber .tf-stat-value  { color: var(--tf-amber); }
    .tf-stat-card.green .tf-stat-value  { color: var(--tf-green); }

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

    .tf-action-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border-radius: 10px;
        cursor: pointer; transition: all .2s; border: none;
    }
    .tf-action-btn.view  { background: var(--tf-blue-soft); color: var(--tf-blue); }
    .tf-action-btn.view:hover { background: var(--tf-blue); color: var(--tf-surface); }
    .tf-action-btn.edit  { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-action-btn.edit:hover { background: var(--tf-green); color: var(--tf-surface); }
    .tf-action-btn.print { background: var(--tf-violet-soft); color: var(--tf-violet); }
    .tf-action-btn.print:hover { background: var(--tf-violet); color: var(--tf-surface); }
    .tf-action-btn.export { background: var(--tf-indigo-soft); color: var(--tf-indigo); }
    .tf-action-btn.export:hover { background: var(--tf-indigo); color: var(--tf-surface); }
    .tf-action-btn.del   { background: var(--tf-red-soft); color: var(--tf-red); }
    .tf-action-btn.del:hover { background: var(--tf-red); color: var(--tf-surface); }

    .tf-invoice-num {
        display: inline-block; padding: 6px 14px;
        border-radius: 50px; font-size: 12px; font-weight: 800;
        background: var(--tf-violet-soft); color: var(--tf-violet);
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

    .tf-filters { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; align-items: center; }
    .tf-filters .tf-input, .tf-filters .tf-select { width: auto; min-width: 150px; flex: 1; }
    .tf-filters .tf-input:first-child { flex: 2; min-width: 200px; }

    .tf-alert {
        position: fixed; bottom: 24px; left: 24px;
        display: flex; align-items: center; gap: 12px;
        padding: 16px 20px; border-radius: 16px;
        z-index: 50; animation: tfFadeUp 0.4s ease;
        box-shadow: var(--tf-shadow-lg);
    }
    .tf-alert-success { background: var(--tf-green); color: var(--tf-surface); }
    .tf-alert-error { background: var(--tf-red); color: var(--tf-surface); }
</style>
@endpush

@section('content')
<div class="tf-page" x-data="{ showFilters: false }">
    @if(session('success'))
    <div class="tf-alert tf-alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="tf-alert tf-alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon violet"><i class="fas fa-shopping-cart"></i></div>
                <div>
                    <h2 class="tf-title-text">فواتير المشتريات</h2>
                    <p class="tf-title-sub">إدارة ومتابعة فواتير المشتريات</p>
                </div>
            </div>
            <a href="{{ route('invoices.purchases.create') }}" class="tf-btn tf-btn-primary">
                <i class="fas fa-plus"></i> إضافة فاتورة جديدة
            </a>
        </div>
    </div>

    <div class="tf-stats-grid tf-section">
        <div class="tf-stat-card blue">
            <div class="tf-stat-label">إجمالي الفواتير</div>
            <div class="tf-stat-value">{{ $statistics['total_invoices'] }}</div>
        </div>
        <div class="tf-stat-card violet">
            <div class="tf-stat-label">إجمالي المشتريات</div>
            <div class="tf-stat-value">{{ number_format($statistics['total_amount']) }}</div>
        </div>
        <div class="tf-stat-card amber">
            <div class="tf-stat-label">فواتير معلقة</div>
            <div class="tf-stat-value">{{ $statistics['draft_invoices'] ?? ($statistics['pending_invoices'] ?? 0) }}</div>
        </div>
        <div class="tf-stat-card green">
            <div class="tf-stat-label">مشتريات اليوم</div>
            <div class="tf-stat-value">{{ number_format($statistics['today_amount']) }}</div>
        </div>
    </div>

    <div class="tf-card tf-section">
        <div class="tf-card-body" style="padding-bottom: 0;">
            <form method="GET" action="{{ route('invoices.purchases.index') }}">
                <div class="tf-filters">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 بحث برقم الفاتورة..." class="tf-input">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="tf-input">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="tf-input">
                    <select name="status" class="tf-select">
                        <option value="">جميع الحالات</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلقة</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                    </select>
                    <button type="submit" class="tf-btn tf-btn-primary">
                        <i class="fas fa-search"></i> بحث
                    </button>
                    <a href="{{ route('invoices.purchases.index') }}" class="tf-btn tf-btn-secondary">
                        <i class="fas fa-redo"></i> إعادة
                    </a>
                    <a href="{{ route('invoices.purchases.export', request()->all()) }}" class="tf-btn tf-btn-green">
                        <i class="fas fa-file-excel"></i> تصدير
                    </a>
                </div>
            </form>
        </div>

        <div class="tf-table-wrapper">
            <table class="tf-table">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>التاريخ</th>
                        <th>المورد</th>
                        <th>المخزن</th>
                        <th>الإجمالي</th>
                        <th>الحالة</th>
                        <th style="text-align: center;">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td><span class="tf-invoice-num">{{ $invoice->invoice_number }}</span></td>
                            <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                            <td style="font-weight: 700;">{{ $invoice->supplier->name }}</td>
                            <td>{{ $invoice->warehouse->name }}</td>
                            <td style="font-weight: 800;">{{ number_format($invoice->total, 2) }} ج.م</td>
                            <td>
                                @if($invoice->status == 'paid')
                                    <span class="tf-badge green">مدفوعة</span>
                                @elseif($invoice->status == 'pending')
                                    <span class="tf-badge amber">معلقة</span>
                                @else
                                    <span class="tf-badge red">ملغاة</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; justify-content: center; gap: 6px;">
                                    <a href="{{ route('invoices.purchases.show', $invoice->id) }}" class="tf-action-btn view" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('invoices.purchases.edit', $invoice->id) }}" class="tf-action-btn edit" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('invoices.purchases.print', $invoice->id) }}" class="tf-action-btn print" title="طباعة" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="{{ route('invoices.purchases.export.single', $invoice->id) }}" class="tf-action-btn export" title="تصدير">
                                        <i class="fas fa-file-excel"></i>
                                    </a>
                                    <form action="{{ route('invoices.purchases.destroy', $invoice->id) }}" method="POST" onsubmit="return confirm('⚠️ هل أنت متأكد من حذف هذه الفاتورة؟')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="tf-action-btn del" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="tf-empty">
                                    <div class="tf-empty-icon"><i class="fas fa-inbox"></i></div>
                                    <h3 class="tf-empty-title">لا توجد فواتير</h3>
                                    <p class="tf-empty-sub">قم بإنشاء فاتورة شراء جديدة للبدء</p>
                                    <a href="{{ route('invoices.purchases.create') }}" class="tf-btn tf-btn-primary">
                                        <i class="fas fa-plus"></i> إنشاء فاتورة جديدة
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div style="padding: 16px 24px; border-top: 1px solid var(--tf-border-soft); background: var(--tf-surface2);">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tf-alert').forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'all 0.4s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(10px)';
            setTimeout(function() { alert.remove(); }, 400);
        }, 5000);
    });
});
</script>
@endpush
@endsection