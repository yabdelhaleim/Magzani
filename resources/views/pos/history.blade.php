@extends('layouts.app')

@section('title', 'سجل الورديات وتاريخ الكاشير')
@section('page-title', 'سجل الورديات')

@push('styles')
<style>
    :root {
        --tf-bg:          transparent;
        --tf-surface:     rgba(22, 33, 56, 0.6);
        --tf-surface2:    rgba(10, 16, 28, 0.55);
        --tf-border:      rgba(255, 255, 255, 0.06);
        --tf-border-soft: rgba(255, 255, 255, 0.04);
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
        --radius-sm:      10px;
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

    .history-page {
        background: var(--tf-bg);
        min-height: 100vh;
        padding: 24px;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .animated { animation: fadeUp 0.4s ease both; }

    /* Stats Bar */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    @media (max-width: 768px) { .stats-bar { grid-template-columns: 1fr 1fr; } }

    .stat-card {
        background: var(--tf-surface);
        border: 1px solid var(--tf-border);
        border-radius: var(--radius-md);
        box-shadow: var(--tf-shadow-card);
        padding: 20px;
        display: flex; align-items: center; gap: 14px;
    }
    .stat-card-icon {
        width: 46px; height: 46px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; font-size: 18px;
        flex-shrink: 0;
    }
    .stat-card-num { font-size: 22px; font-weight: 900; color: var(--tf-text-h); }
    .stat-card-label { font-size: 11px; font-weight: 700; color: var(--tf-text-m); margin-top: 2px; }

    /* Filter Bar */
    .filter-bar {
        background: var(--tf-surface);
        border: 1px solid var(--tf-border);
        border-radius: var(--radius-md);
        padding: 18px 22px;
        margin-bottom: 20px;
        display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        box-shadow: var(--tf-shadow-card);
    }
    .filter-bar input, .filter-bar select {
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 10px;
        padding: 9px 14px;
        font-size: 13px; font-weight: 700;
        color: #f1f5f9 !important;
        background: rgba(10, 16, 28, 0.65) !important;
        transition: all 0.2s;
        outline: none;
    }
    .filter-bar input:focus, .filter-bar select:focus {
        border-color: var(--tf-indigo) !important;
        box-shadow: 0 0 12px rgba(99, 102, 241, 0.3) !important;
    }
    .filter-label { font-size: 12px; font-weight: 800; color: var(--tf-text-m); white-space: nowrap; }
    .btn-filter {
        padding: 9px 18px; background: var(--tf-indigo); color: white;
        border: none; border-radius: 10px; font-size: 13px; font-weight: 800;
        cursor: pointer; transition: all 0.2s;
    }
    .btn-filter:hover { background: #3d51c5; }
    .btn-reset {
        padding: 9px 14px; background: rgba(255, 255, 255, 0.04) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important; border-radius: 10px;
        font-size: 12px; font-weight: 700; color: var(--tf-text-m) !important;
        cursor: pointer; text-decoration: none; transition: all 0.2s;
    }
    .btn-reset:hover { border-color: rgba(255, 255, 255, 0.2) !important; background: rgba(255, 255, 255, 0.08) !important; color: #f1f5f9 !important; }

    /* Table Card */
    .table-card {
        background: var(--tf-surface);
        border: 1px solid var(--tf-border);
        border-radius: var(--radius-md);
        box-shadow: var(--tf-shadow-card);
        overflow: hidden;
    }
    .table-card-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2);
        display: flex; align-items: center; justify-content: space-between;
    }
    .table-card-title { font-size: 15px; font-weight: 900; color: var(--tf-text-h); }

    table { width: 100%; border-collapse: collapse; }
    thead th {
        background: var(--tf-surface2);
        border-bottom: 2px solid var(--tf-border);
        padding: 12px 16px;
        font-size: 11px; font-weight: 800;
        color: var(--tf-text-m);
        text-align: right;
        white-space: nowrap;
    }
    tbody tr {
        border-bottom: 1px solid var(--tf-border-soft);
        transition: background 0.15s;
    }
    tbody tr:hover { background: rgba(255, 255, 255, 0.02) !important; }
    tbody td {
        padding: 14px 16px;
        font-size: 13px; font-weight: 700;
        color: var(--tf-text-b);
        white-space: nowrap;
    }
    .td-id { font-size: 12px; font-weight: 900; color: var(--tf-text-m); }
    .td-name { font-size: 13px; font-weight: 900; color: var(--tf-text-h); }
    .td-green { color: var(--tf-green); font-weight: 900; }
    .td-red   { color: var(--tf-red);   font-weight: 900; }
    .td-amber { color: var(--tf-amber); font-weight: 900; }

    /* Status Badge */
    .badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 10px; border-radius: 20px;
        font-size: 11px; font-weight: 800;
    }
    .badge-open   { background: var(--tf-green-soft); color: var(--tf-green); }
    .badge-closed { background: rgba(255, 255, 255, 0.08) !important; color: #94a3b8 !important; }
    .badge-diff-ok    { background: var(--tf-green-soft); color: var(--tf-green); }
    .badge-diff-over  { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .badge-diff-under { background: var(--tf-red-soft); color: var(--tf-red); }

    /* Empty State */
    .empty-state {
        padding: 64px 20px; text-align: center;
    }
    .empty-state .empty-icon {
        width: 80px; height: 80px; border-radius: 50%;
        background: var(--tf-surface2); border: 2px dashed var(--tf-border);
        display: flex; align-items: center; justify-content: center;
        font-size: 30px; color: var(--tf-text-m);
        margin: 0 auto 16px;
    }
    .empty-state h3 { font-size: 16px; font-weight: 900; color: var(--tf-text-h); margin: 0 0 6px; }
    .empty-state p  { font-size: 13px; font-weight: 600; color: var(--tf-text-m); margin: 0; }

    /* Pagination */
    .pagination-wrap {
        padding: 16px 20px;
        border-top: 1px solid var(--tf-border-soft);
        display: flex; justify-content: center;
    }
</style>
@endpush

@section('content')
<div class="history-page">

    {{-- Alerts --}}
    @if(session('success'))
        <div style="margin-bottom:20px; padding:14px 18px; background:var(--tf-green-soft); border:1px solid #6ee7b7; border-radius:12px; color:#065f46; font-weight:700; font-size:13px;" class="animated">
            <i class="fas fa-check-circle ml-2"></i>{{ session('success') }}
        </div>
    @endif

    {{-- Stats Bar --}}
    <div class="stats-bar">
        <div class="stat-card animated" style="animation-delay:0.05s">
            <div class="stat-card-icon" style="background:var(--tf-indigo-soft); color:var(--tf-indigo)">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <div class="stat-card-num">{{ $stats['total_shifts'] }}</div>
                <div class="stat-card-label">إجمالي الورديات</div>
            </div>
        </div>
        <div class="stat-card animated" style="animation-delay:0.08s">
            <div class="stat-card-icon" style="background:var(--tf-green-soft); color:var(--tf-green)">
                <i class="fas fa-door-open"></i>
            </div>
            <div>
                <div class="stat-card-num">{{ $stats['open_shifts'] }}</div>
                <div class="stat-card-label">ورديات مفتوحة حالياً</div>
            </div>
        </div>
        <div class="stat-card animated" style="animation-delay:0.11s">
            <div class="stat-card-icon" style="background:var(--tf-amber-soft); color:var(--tf-amber)">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div>
                <div class="stat-card-num">{{ $stats['today_shifts'] }}</div>
                <div class="stat-card-label">ورديات اليوم</div>
            </div>
        </div>
        <div class="stat-card animated" style="animation-delay:0.14s">
            <div class="stat-card-icon" style="background:var(--tf-green-soft); color:var(--tf-green)">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div>
                <div class="stat-card-num">{{ number_format($stats['today_sales'], 0) }}</div>
                <div class="stat-card-label">مبيعات اليوم (ج.م)</div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-bar animated" style="animation-delay:0.17s">
        <form action="{{ route('pos.history') }}" method="GET" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; width:100%;">
            <span class="filter-label">من:</span>
            <input type="date" name="date_from" value="{{ request('date_from') }}" style="width:150px;">

            <span class="filter-label">إلى:</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}" style="width:150px;">

            <span class="filter-label">الحالة:</span>
            <select name="status" style="width:140px;">
                <option value="">الكل</option>
                <option value="open"   {{ request('status') === 'open'   ? 'selected' : '' }}>مفتوحة</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>مغلقة</option>
            </select>

            <button type="submit" class="btn-filter">
                <i class="fas fa-search ml-2"></i> بحث
            </button>
            <a href="{{ route('pos.history') }}" class="btn-reset">
                <i class="fas fa-redo"></i> إعادة تعيين
            </a>
        </form>
    </div>

    {{-- Shifts Table --}}
    <div class="table-card animated" style="animation-delay:0.2s">
        <div class="table-card-header">
            <span class="table-card-title"><i class="fas fa-table ml-2 text-indigo-400"></i>الورديات</span>
            <span style="font-size:12px; color:var(--tf-text-m); font-weight:700">{{ $shifts->total() }} وردية</span>
        </div>

        @if($shifts->count() > 0)
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الكاشير</th>
                        <th>فتح الوردية</th>
                        <th>إغلاق الوردية</th>
                        <th>المدة</th>
                        <th>رصيد البداية</th>
                        <th>المبيعات</th>
                        <th>المرتجعات</th>
                        <th>فواتير</th>
                        <th>الرصيد المتوقع</th>
                        <th>الرصيد الفعلي</th>
                        <th>الفرق</th>
                        <th>الحالة</th>
                        <th>التقرير</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shifts as $shift)
                    <tr>
                        <td class="td-id">#{{ $shift->id }}</td>
                        <td class="td-name">{{ $shift->user->name ?? 'غير معروف' }}</td>
                        <td>{{ $shift->opened_at->format('d/m H:i') }}</td>
                        <td>
                            @if($shift->closed_at)
                                {{ $shift->closed_at->format('d/m H:i') }}
                            @else
                                <span style="color:var(--tf-text-m); font-style:italic;">مفتوحة</span>
                            @endif
                        </td>
                        <td>{{ $shift->duration }}</td>
                        <td>{{ number_format($shift->opening_balance, 2) }}</td>
                        <td class="td-green">{{ number_format($shift->total_sales, 2) }}</td>
                        <td class="td-red">{{ number_format($shift->total_returns, 2) }}</td>
                        <td style="text-align:center;">{{ $shift->sales_count }}</td>
                        <td>
                            @if($shift->closing_balance_expected !== null)
                                {{ number_format($shift->closing_balance_expected, 2) }}
                            @else
                                <span style="color:var(--tf-text-m)">—</span>
                            @endif
                        </td>
                        <td>
                            @if($shift->closing_balance_actual !== null)
                                {{ number_format($shift->closing_balance_actual, 2) }}
                            @else
                                <span style="color:var(--tf-text-m)">—</span>
                            @endif
                        </td>
                        <td>
                            @if($shift->difference !== null)
                                @php $diff = (float) $shift->difference; @endphp
                                <span class="badge {{ $diff == 0 ? 'badge-diff-ok' : ($diff > 0 ? 'badge-diff-over' : 'badge-diff-under') }}">
                                    {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                                </span>
                            @else
                                <span style="color:var(--tf-text-m)">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $shift->status === 'open' ? 'badge-open' : 'badge-closed' }}">
                                <i class="fas {{ $shift->status === 'open' ? 'fa-circle' : 'fa-check-circle' }}"></i>
                                {{ $shift->status === 'open' ? 'مفتوحة' : 'مغلقة' }}
                            </span>
                        </td>
                        <td>
                            @if($shift->status === 'closed')
                                <a href="{{ route('pos.shift.zreport', $shift->id) }}" class="btn-reset" style="padding: 4px 10px; font-size: 11px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; border-color: var(--tf-indigo); color: var(--tf-indigo);">
                                    <i class="fas fa-print"></i> تقرير Z
                                </a>
                            @else
                                <a href="{{ route('pos.xreport') }}" class="btn-reset" style="padding: 4px 10px; font-size: 11px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; border-color: var(--tf-green); color: var(--tf-green);">
                                    <i class="fas fa-chart-line"></i> تقرير X
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($shifts->hasPages())
            <div class="pagination-wrap">
                {{ $shifts->links() }}
            </div>
        @endif

        @else
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-history"></i></div>
            <h3>لا توجد ورديات بعد</h3>
            <p>سيظهر هنا سجل جميع ورديات الكاشير بعد البدء بالبيع</p>
        </div>
        @endif
    </div>

</div>
@endsection
