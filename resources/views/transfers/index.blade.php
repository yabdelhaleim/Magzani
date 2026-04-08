@extends('layouts.app')

@section('title', 'إدارة التحويلات')
@section('page-title', 'إدارة التحويلات')

@push('styles')
<style>
    /* ── Variables ── */
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
        --tf-orange:      #f97316;
        --tf-orange-soft: #fff7ed;

        --tf-text-h:      #1a2140;
        --tf-text-b:      #3d4f72;
        --tf-text-m:      #7e90b0;

        --tf-shadow-sm:   0 2px 12px rgba(79,99,210,0.07);
        --tf-shadow-card: 0 2px 0 0 rgba(0,0,0,0.04), 0 4px 20px rgba(79,99,210,0.08);
        --tf-shadow-lg:   0 8px 30px rgba(79,99,210,0.10);
    }

    /* ── Background ── */
    .tf-page {
        background: var(--tf-bg);
        background-image:
            radial-gradient(ellipse 80% 60% at 10% -10%,  rgba(79,99,210,0.12) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(58,142,240,0.1) 0%, transparent 50%);
        min-height: 100vh;
        padding: 26px 22px;
    }

    /* ── Animations ── */
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
    @keyframes badgePulse {
        0%,100% { transform: scale(1); }
        50%     { transform: scale(1.05); }
    }

    .tf-section { animation: tfFadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .tf-section:nth-child(1) { animation-delay: 0.04s; }
    .tf-section:nth-child(2) { animation-delay: 0.13s; }
    .tf-section:nth-child(3) { animation-delay: 0.22s; }

    /* ── Header ── */
    .tf-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 22px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .tf-title-row { display: flex; flex-direction: column; gap: 4px; }
    .tf-title { font-size: 24px; font-weight: 900; color: var(--tf-text-h); margin: 0; }
    .tf-subtitle { font-size: 13px; color: var(--tf-text-m); font-weight: 600; }

    .tf-btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 22px; border-radius: 14px;
        background: linear-gradient(135deg, var(--tf-blue), var(--tf-indigo-light));
        color: var(--tf-surface); border: none;
        font-size: 13px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif;
        box-shadow: 0 4px 16px rgba(58,142,240,0.35);
        transition: all 0.3s ease;
    }
    .tf-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(58,142,240,0.45);
    }
    .tf-btn-primary:hover .tf-btn-icon { animation: iconBounce .5s ease; }

    /* ── Stats Cards ── */
    .tf-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }
    @media (max-width: 900px) { .tf-stats-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .tf-stats-grid { grid-template-columns: 1fr; } }

    .tf-stat-card {
        background: var(--tf-surface);
        border-radius: 18px;
        border: 1px solid var(--tf-border);
        padding: 18px 20px;
        box-shadow: var(--tf-shadow-card);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
        transition: all .35s cubic-bezier(.22,1,.36,1);
    }
    .tf-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--tf-shadow-lg);
    }
    .tf-stat-card::after {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.4) 50%, transparent 60%);
        background-size: 600px 100%; opacity: 0; transition: opacity .3s;
    }
    .tf-stat-card:hover::after { opacity: 1; animation: tfShimmer .7s ease forwards; }

    .tf-stat-icon {
        width: 50px; height: 50px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .tf-stat-card.blue .tf-stat-icon   { background: var(--tf-blue-soft);   color: var(--tf-blue); }
    .tf-stat-card.green .tf-stat-icon  { background: var(--tf-green-soft);  color: var(--tf-green); }
    .tf-stat-card.amber .tf-stat-icon  { background: var(--tf-amber-soft);  color: var(--tf-amber); }
    .tf-stat-card.red .tf-stat-icon    { background: var(--tf-red-soft);    color: var(--tf-red); }

    .tf-stat-info { text-align: left; }
    .tf-stat-label { font-size: 11px; font-weight: 700; color: var(--tf-text-m); text-transform: uppercase; letter-spacing: .5px; }
    .tf-stat-value { font-size: 26px; font-weight: 900; color: var(--tf-text-h); line-height: 1.2; font-family: 'Cairo', sans-serif; }
    .tf-stat-card.blue .tf-stat-value   { color: var(--tf-blue); }
    .tf-stat-card.green .tf-stat-value  { color: var(--tf-green); }
    .tf-stat-card.amber .tf-stat-value  { color: var(--tf-amber); }
    .tf-stat-card.red .tf-stat-value    { color: var(--tf-red); }

    /* ── Alerts ── */
    .tf-alert {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px; border-radius: 16px;
        margin-bottom: 20px;
        animation: tfFadeUp 0.4s ease both;
        border: 1px solid;
    }
    .tf-alert-success {
        background: var(--tf-green-soft); border-color: rgba(15,170,126,0.2);
    }
    .tf-alert-success i { color: var(--tf-green); }
    .tf-alert-error {
        background: var(--tf-red-soft); border-color: rgba(232,75,90,0.2);
    }
    .tf-alert-error i { color: var(--tf-red); }
    .tf-alert-content { display: flex; align-items: center; gap: 12px; flex: 1; }
    .tf-alert-text { font-size: 14px; font-weight: 700; }
    .tf-alert-success .tf-alert-text { color: #065f46; }
    .tf-alert-error .tf-alert-text { color: #991b1b; }
    .tf-alert-close { cursor: pointer; opacity: 0.6; transition: opacity .2s; font-size: 14px; }
    .tf-alert-close:hover { opacity: 1; }

    /* ── Filters Card ── */
    .tf-card {
        background: var(--tf-surface);
        border-radius: 20px;
        border: 1px solid var(--tf-border);
        overflow: hidden;
        box-shadow: var(--tf-shadow-card);
        margin-bottom: 22px;
        position: relative;
        transition: transform .35s cubic-bezier(.22,1,.36,1), box-shadow .35s ease;
    }
    .tf-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--tf-shadow-lg);
    }
    .tf-card-head {
        padding: 16px 22px;
        border-bottom: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2);
    }
    .tf-card-head-title {
        font-size: 14px; font-weight: 800; color: var(--tf-text-h);
        display: flex; align-items: center; gap: 10px;
    }
    .tf-card-head-title i { color: var(--tf-indigo); }

    .tf-card-body { padding: 20px; }

    /* ── Form Controls ── */
    .tf-label {
        display: block; font-size: 12px; font-weight: 800;
        color: var(--tf-text-b); margin-bottom: 6px;
    }
    .tf-select, .tf-input {
        width: 100%; padding: 10px 14px;
        border: 1.5px solid var(--tf-border); border-radius: 12px;
        font-size: 13px; font-family: 'Cairo', sans-serif;
        color: var(--tf-text-h); background: var(--tf-surface);
        transition: all .25s; outline: none;
    }
    .tf-select:focus, .tf-input:focus {
        border-color: var(--tf-blue);
        box-shadow: 0 0 0 3px rgba(58,142,240,0.1);
    }
    .tf-select { cursor: pointer; }

    .tf-filter-row {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;
    }
    @media (max-width: 900px) { .tf-filter-row { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .tf-filter-row { grid-template-columns: 1fr; } }

    .tf-filter-actions {
        display: flex; gap: 10px; margin-top: 18px; flex-wrap: wrap;
    }
    .tf-btn-filter {
        padding: 10px 20px; border-radius: 12px;
        font-size: 13px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif;
        display: inline-flex; align-items: center; gap: 8px;
        border: 1.5px solid; transition: all .25s;
    }
    .tf-btn-search {
        background: var(--tf-blue); color: var(--tf-surface);
        border-color: var(--tf-blue);
    }
    .tf-btn-search:hover {
        background: var(--tf-indigo);
        box-shadow: 0 4px 14px rgba(58,142,240,0.35);
    }
    .tf-btn-reset {
        background: var(--tf-surface); color: var(--tf-text-b);
        border-color: var(--tf-border);
    }
    .tf-btn-reset:hover { background: var(--tf-surface2); }

    /* ── Transfer Item ── */
    .tf-transfer-card {
        background: var(--tf-surface);
        border-radius: 20px;
        border: 1px solid var(--tf-border);
        overflow: hidden;
        box-shadow: var(--tf-shadow-card);
        margin-bottom: 16px;
        transition: all .35s ease;
    }
    .tf-transfer-card:hover {
        box-shadow: var(--tf-shadow-lg);
    }

    .tf-transfer-header {
        padding: 20px 22px;
        border-bottom: 1px solid var(--tf-border-soft);
        display: flex; justify-content: space-between; align-items: flex-start;
        flex-wrap: wrap; gap: 16px;
    }

    .tf-transfer-info { flex: 1; }

    .tf-transfer-top {
        display: flex; align-items: center; gap: 12px;
        margin-bottom: 14px; flex-wrap: wrap;
    }

    .tf-transfer-number {
        font-size: 18px; font-weight: 900; color: var(--tf-text-h);
        font-family: 'Cairo', sans-serif;
    }

    .tf-status-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 50px;
        font-size: 12px; font-weight: 800;
    }
    .tf-status-badge.received { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-status-badge.pending { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .tf-status-badge.draft { background: var(--tf-surface2); color: var(--tf-text-m); }
    .tf-status-badge.cancelled { background: var(--tf-red-soft); color: var(--tf-red); }
    .tf-status-badge.reversed { background: var(--tf-orange-soft); color: var(--tf-orange); }

    .tf-items-count {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 50px;
        font-size: 12px; font-weight: 800;
        background: var(--tf-blue-soft); color: var(--tf-blue);
    }

    .tf-warehouses-row {
        display: flex; align-items: center; gap: 16px;
        flex-wrap: wrap; font-size: 13px;
    }
    .tf-warehouse-item {
        display: flex; align-items: center; gap: 8px;
    }
    .tf-warehouse-icon {
        width: 32px; height: 32px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px;
    }
    .tf-warehouse-item.from .tf-warehouse-icon { background: var(--tf-red-soft); color: var(--tf-red); }
    .tf-warehouse-item.to .tf-warehouse-icon { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-warehouse-item.date .tf-warehouse-icon { background: var(--tf-blue-soft); color: var(--tf-blue); }

    .tf-warehouse-label { font-size: 10px; color: var(--tf-text-m); font-weight: 700; text-transform: uppercase; }
    .tf-warehouse-name { font-size: 13px; font-weight: 800; color: var(--tf-text-h); }

    .tf-arrow { color: var(--tf-text-m); font-size: 12px; }

    .tf-transfer-actions {
        display: flex; gap: 8px; flex-shrink: 0;
        flex-wrap: wrap;
    }
    .tf-btn-action {
        padding: 9px 16px; border-radius: 11px;
        font-size: 12px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif;
        display: inline-flex; align-items: center; gap: 6px;
        transition: all .25s;
    }
    .tf-btn-toggle {
        background: var(--tf-surface2); color: var(--tf-text-b);
        border: 1px solid var(--tf-border);
    }
    .tf-btn-toggle:hover { background: var(--tf-border-soft); }
    .tf-btn-view {
        background: var(--tf-blue); color: var(--tf-surface);
        border: none;
    }
    .tf-btn-view:hover { background: var(--tf-indigo); }
    .tf-btn-reverse {
        background: var(--tf-orange); color: var(--tf-surface);
        border: none;
    }
    .tf-btn-reverse:hover { background: #ea580c; }

    /* ── Transfer Details ── */
    .tf-transfer-details {
        border-top: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2);
    }
    .tf-details-inner { padding: 22px; }

    .tf-summary-grid {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 20px;
    }
    @media (max-width: 600px) { .tf-summary-grid { grid-template-columns: 1fr; } }

    .tf-summary-item {
        text-align: center; padding: 16px; border-radius: 14px;
        border: 1.5px solid var(--tf-border);
        background: var(--tf-surface);
    }
    .tf-summary-label { font-size: 11px; font-weight: 700; color: var(--tf-text-m); margin-bottom: 6px; }
    .tf-summary-value { font-size: 24px; font-weight: 900; font-family: 'Cairo', sans-serif; }
    .tf-summary-item:nth-child(1) .tf-summary-value { color: var(--tf-blue); }
    .tf-summary-item:nth-child(2) .tf-summary-value { color: var(--tf-green); }
    .tf-summary-item:nth-child(3) .tf-summary-value { color: var(--tf-violet); }

    /* ── Table ── */
    .tf-table { width: 100%; border-collapse: collapse; }
    .tf-table thead th {
        padding: 12px 16px; text-align: right;
        font-size: 10px; font-weight: 800; color: var(--tf-text-m);
        text-transform: uppercase; letter-spacing: .7px;
        border-bottom: 1.5px solid var(--tf-border-soft);
        background: var(--tf-surface); white-space: nowrap;
    }
    .tf-table tbody tr {
        transition: background .18s;
    }
    .tf-table tbody tr:hover { background: var(--tf-surface2); }
    .tf-table tbody td {
        padding: 14px 16px; border-bottom: 1px solid var(--tf-border-soft);
        vertical-align: middle;
    }
    .tf-table tbody tr:last-child td { border-bottom: none; }

    .tf-row-num {
        width: 32px; height: 32px; border-radius: 10px;
        background: var(--tf-blue-soft); color: var(--tf-blue);
        font-size: 12px; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
    }

    .tf-product-info { display: flex; flex-direction: column; gap: 2px; }
    .tf-product-name { font-size: 14px; font-weight: 800; color: var(--tf-text-h); }
    .tf-product-code { font-size: 11px; color: var(--tf-text-m); }

    .tf-qty-pill {
        display: inline-block; padding: 6px 14px; border-radius: 50px;
        font-size: 14px; font-weight: 800;
        background: var(--tf-blue-soft); color: var(--tf-blue);
    }

    .tf-stock-cell { text-align: center; }
    .tf-stock-label { font-size: 10px; color: var(--tf-text-m); font-weight: 700; margin-bottom: 4px; }
    .tf-stock-value {
        display: inline-block; padding: 4px 10px; border-radius: 8px;
        font-size: 12px; font-weight: 800;
    }
    .tf-stock-before { background: var(--tf-surface2); color: var(--tf-text-b); }
    .tf-stock-after { background: var(--tf-red-soft); color: var(--tf-red); }
    .tf-stock-after.inc { background: var(--tf-green-soft); color: var(--tf-green); }

    .tf-stock-arrow {
        display: flex; justify-content: center; margin: 4px 0;
        font-size: 10px;
    }
    .tf-stock-arrow.down { color: var(--tf-red); }
    .tf-stock-arrow.up { color: var(--tf-green); }

    .tf-notes-cell { display: flex; align-items: flex-start; gap: 8px; font-size: 12px; color: var(--tf-text-b); }
    .tf-notes-cell i { color: var(--tf-text-m); margin-top: 2px; }
    .tf-no-notes { color: var(--tf-text-m); font-size: 12px; }

    /* ── Empty State ── */
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

    /* ── Pagination ── */
    .tf-pagination {
        display: flex; justify-content: space-between; align-items: center;
        padding: 16px 20px; background: var(--tf-surface);
        border-radius: 16px; border: 1px solid var(--tf-border);
        margin-top: 20px;
        flex-wrap: wrap; gap: 12px;
    }
    .tf-pagination-info { font-size: 13px; color: var(--tf-text-m); font-weight: 600; }
    .tf-pagination-links { display: flex; gap: 4px; }
    .tf-pagination-links a, .tf-pagination-links span {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 36px; height: 36px; padding: 0 10px;
        border-radius: 10px; font-size: 13px; font-weight: 700;
        text-decoration: none; transition: all .2s;
    }
    .tf-pagination-links a {
        background: var(--tf-surface2); color: var(--tf-text-b);
        border: 1px solid var(--tf-border);
    }
    .tf-pagination-links a:hover { background: var(--tf-blue-soft); color: var(--tf-blue); border-color: var(--tf-blue); }
    .tf-pagination-links span {
        background: var(--tf-blue); color: var(--tf-surface);
        border: 1px solid var(--tf-blue);
    }
</style>
@endpush

@section('content')
<div class="tf-page" x-data="{ transfers: {} }">

    {{-- ── Header ── --}}
    <div class="tf-header tf-section">
        <div class="tf-title-row">
            <h1 class="tf-title">📦 إدارة التحويلات</h1>
            <p class="tf-subtitle">إدارة ومتابعة تحويلات المخزون</p>
        </div>
        <a href="{{ route('transfers.create') }}" class="tf-btn-primary">
            <i class="tf-btn-icon fas fa-plus"></i>
            تحويل جديد
        </a>
    </div>

    {{-- ── Stats ── --}}
    <div class="tf-stats-grid tf-section">
        <div class="tf-stat-card blue">
            <div class="tf-stat-info">
                <span class="tf-stat-label">الإجمالي</span>
                <span class="tf-stat-value">{{ $transfers->total() }}</span>
            </div>
            <div class="tf-stat-icon"><i class="fas fa-exchange-alt"></i></div>
        </div>
        <div class="tf-stat-card green">
            <div class="tf-stat-info">
                <span class="tf-stat-label">مستلمة</span>
                <span class="tf-stat-value">{{ $transfers->where('status', 'received')->count() }}</span>
            </div>
            <div class="tf-stat-icon"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="tf-stat-card amber">
            <div class="tf-stat-info">
                <span class="tf-stat-label">معلقة</span>
                <span class="tf-stat-value">{{ $transfers->whereIn('status', ['draft', 'pending'])->count() }}</span>
            </div>
            <div class="tf-stat-icon"><i class="fas fa-clock"></i></div>
        </div>
        <div class="tf-stat-card red">
            <div class="tf-stat-info">
                <span class="tf-stat-label">ملغية</span>
                <span class="tf-stat-value">{{ $transfers->where('status', 'cancelled')->count() }}</span>
            </div>
            <div class="tf-stat-icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>

    {{-- ── Alerts ── --}}
    @if(session('success'))
    <div class="tf-alert tf-alert-success">
        <div class="tf-alert-content">
            <i class="fas fa-check-circle fa-lg"></i>
            <span class="tf-alert-text">{{ session('success') }}</span>
        </div>
        <i class="tf-alert-close fas fa-times" onclick="this.parentElement.remove()"></i>
    </div>
    @endif

    @if(session('error'))
    <div class="tf-alert tf-alert-error">
        <div class="tf-alert-content">
            <i class="fas fa-exclamation-circle fa-lg"></i>
            <span class="tf-alert-text">{{ session('error') }}</span>
        </div>
        <i class="tf-alert-close fas fa-times" onclick="this.parentElement.remove()"></i>
    </div>
    @endif

    {{-- ── Filters ── --}}
    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <h3 class="tf-card-head-title">
                <i class="fas fa-filter"></i>
                فلاتر البحث
            </h3>
        </div>
        <div class="tf-card-body">
            <form method="GET">
                <div class="tf-filter-row">
                    <div>
                        <label class="tf-label">المخزن المصدر</label>
                        <select name="from_warehouse" class="tf-select">
                            <option value="">الكل</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('from_warehouse') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="tf-label">المخزن الوجهة</label>
                        <select name="to_warehouse" class="tf-select">
                            <option value="">الكل</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('to_warehouse') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="tf-label">الحالة</label>
                        <select name="status" class="tf-select">
                            <option value="">الكل</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلقة</option>
                            <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>مستلمة</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغية</option>
                            <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>معكوسة</option>
                        </select>
                    </div>
                    <div>
                        <label class="tf-label">من تاريخ</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="tf-input">
                    </div>
                </div>
                <div class="tf-filter-actions">
                    <button type="submit" class="tf-btn-filter tf-btn-search">
                        <i class="fas fa-search"></i> بحث
                    </button>
                    <a href="{{ route('transfers.index') }}" class="tf-btn-filter tf-btn-reset">
                        <i class="fas fa-redo"></i> إعادة تعيين
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Transfers List ── --}}
    <div class="tf-section">
        @forelse($transfers as $transfer)
        <div class="tf-transfer-card" x-data="{ open: false }">
            <div class="tf-transfer-header">
                <div class="tf-transfer-info">
                    <div class="tf-transfer-top">
                        <span class="tf-transfer-number">#{{ $transfer->transfer_number }}</span>
                        <span class="tf-status-badge {{ $transfer->status }}">
                            @if($transfer->status == 'received') ✅ مستلمة
                            @elseif($transfer->status == 'pending') ⏳ معلقة
                            @elseif($transfer->status == 'draft') 📝 مسودة
                            @elseif($transfer->status == 'cancelled') ❌ ملغية
                            @elseif($transfer->status == 'reversed') 🔄 معكوسة
                            @endif
                        </span>
                        <span class="tf-items-count">
                            <i class="fas fa-boxes"></i> {{ $transfer->items_count ?? $transfer->items->count() }} منتج
                        </span>
                    </div>
                    <div class="tf-warehouses-row">
                        <div class="tf-warehouse-item from">
                            <div class="tf-warehouse-icon"><i class="fas fa-warehouse"></i></div>
                            <div>
                                <div class="tf-warehouse-label">من</div>
                                <div class="tf-warehouse-name">{{ $transfer->fromWarehouse->name }}</div>
                            </div>
                        </div>
                        <i class="tf-arrow fas fa-arrow-left"></i>
                        <div class="tf-warehouse-item to">
                            <div class="tf-warehouse-icon"><i class="fas fa-warehouse"></i></div>
                            <div>
                                <div class="tf-warehouse-label">إلى</div>
                                <div class="tf-warehouse-name">{{ $transfer->toWarehouse->name }}</div>
                            </div>
                        </div>
                        <div class="tf-warehouse-item date">
                            <div class="tf-warehouse-icon"><i class="fas fa-calendar"></i></div>
                            <div>
                                <div class="tf-warehouse-label">التاريخ</div>
                                <div class="tf-warehouse-name">{{ $transfer->transfer_date->format('Y-m-d') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tf-transfer-actions">
                    <button @click="open = !open; transfers[{{ $transfer->id }}] = open" class="tf-btn-action tf-btn-toggle">
                        <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        <span x-text="open ? 'إخفاء' : 'تفاصيل'"></span>
                    </button>
                    <a href="{{ route('transfers.show', $transfer->id) }}" class="tf-btn-action tf-btn-view">
                        <i class="fas fa-eye"></i> عرض
                    </a>
                    @if($transfer->status == 'received')
                    <form action="{{ route('transfers.reverse', $transfer->id) }}" method="POST" onsubmit="return confirm('⚠️ هل تريد عكس هذا التحويل؟')">
                        @csrf
                        <button type="submit" class="tf-btn-action tf-btn-reverse">
                            <i class="fas fa-undo"></i> عكس
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Transfer Details --}}
            <div x-show="transfers[{{ $transfer->id }}]" x-transition class="tf-transfer-details">
                <div class="tf-details-inner">
                    <div class="tf-summary-grid">
                        <div class="tf-summary-item">
                            <div class="tf-summary-label">عدد المنتجات</div>
                            <div class="tf-summary-value">{{ $transfer->items->count() }}</div>
                        </div>
                        <div class="tf-summary-item">
                            <div class="tf-summary-label">إجمالي الكمية</div>
                            <div class="tf-summary-value">{{ number_format($transfer->total_quantity_sent ?? $transfer->items->sum('quantity_sent'), 2) }}</div>
                        </div>
                        <div class="tf-summary-item">
                            <div class="tf-summary-label">الكمية المستلمة</div>
                            <div class="tf-summary-value">{{ number_format($transfer->total_quantity_received ?? $transfer->items->sum('quantity_received'), 2) }}</div>
                        </div>
                    </div>

                    <div class="tf-table-wrapper" style="overflow-x: auto;">
                        <table class="tf-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المنتج</th>
                                    <th style="text-align: center;">الكمية المحولة</th>
                                    <th style="text-align: center;">المخزن المصدر</th>
                                    <th style="text-align: center;">المخزن الوجهة</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfer->items as $index => $item)
                                @php
                                    $movements = \App\Models\InventoryMovement::where('reference_id', $transfer->id)
                                        ->where('product_id', $item->product_id)
                                        ->orderBy('movement_type', 'asc')
                                        ->get();
                                    $sourceMovement = $movements->firstWhere('movement_type', 'transfer_out');
                                    $destMovement = $movements->firstWhere('movement_type', 'transfer_in');
                                @endphp
                                <tr>
                                    <td><div class="tf-row-num">{{ $index + 1 }}</div></td>
                                    <td>
                                        <div class="tf-product-info">
                                            <span class="tf-product-name">{{ $item->product->name }}</span>
                                            <span class="tf-product-code">{{ $item->product->sku ?? $item->product->code }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="tf-qty-pill">{{ number_format($item->quantity_sent, 2) }}</span>
                                    </td>
                                    <td class="tf-stock-cell">
                                        @if($sourceMovement)
                                        <div class="tf-stock-label">قبل</div>
                                        <span class="tf-stock-value tf-stock-before">{{ number_format($sourceMovement->quantity_before, 2) }}</span>
                                        <div class="tf-stock-arrow down"><i class="fas fa-arrow-down"></i></div>
                                        <div class="tf-stock-label">بعد</div>
                                        <span class="tf-stock-value tf-stock-after">{{ number_format($sourceMovement->quantity_after, 2) }}</span>
                                        @else
                                        <span class="tf-no-notes">لا توجد بيانات</span>
                                        @endif
                                    </td>
                                    <td class="tf-stock-cell">
                                        @if($destMovement)
                                        <div class="tf-stock-label">قبل</div>
                                        <span class="tf-stock-value tf-stock-before">{{ number_format($destMovement->quantity_before, 2) }}</span>
                                        <div class="tf-stock-arrow up"><i class="fas fa-arrow-up"></i></div>
                                        <div class="tf-stock-label">بعد</div>
                                        <span class="tf-stock-value tf-stock-after inc">{{ number_format($destMovement->quantity_after, 2) }}</span>
                                        @else
                                        <span class="tf-no-notes">لا توجد بيانات</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->notes)
                                        <div class="tf-notes-cell">
                                            <i class="fas fa-sticky-note"></i>
                                            <span>{{ $item->notes }}</span>
                                        </div>
                                        @else
                                        <span class="tf-no-notes">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="tf-card">
            <div class="tf-empty">
                <div class="tf-empty-icon"><i class="fas fa-exchange-alt"></i></div>
                <h3 class="tf-empty-title">لا توجد تحويلات</h3>
                <p class="tf-empty-sub">لم يتم إنشاء أي تحويلات بعد</p>
                <a href="{{ route('transfers.create') }}" class="tf-btn-primary">
                    <i class="fas fa-plus"></i> إنشاء أول تحويل
                </a>
            </div>
        </div>
        @endforelse
    </div>

    {{-- ── Pagination ── --}}
    @if($transfers->hasPages())
    <div class="tf-pagination">
        <div class="tf-pagination-info">
            عرض {{ $transfers->firstItem() }} - {{ $transfers->lastItem() }} من {{ $transfers->total() }}
        </div>
        <div class="tf-pagination-links">
            {{ $transfers->links() }}
        </div>
    </div>
    @endif

</div>
@endsection