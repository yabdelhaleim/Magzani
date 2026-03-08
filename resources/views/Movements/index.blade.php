@extends('layouts.app')

@section('title', 'سجل حركات المخزون')
@section('page-title', 'سجل حركات المخزون')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@300;400;500;700;800&display=swap');

    :root {
        --primary:       #1a1f3a;
        --accent:        #4f6ef7;
        --accent-light:  #6b84ff;
        --accent-soft:   rgba(79,110,247,0.10);
        --success:       #059669;
        --success-soft:  rgba(5,150,105,0.10);
        --danger:        #e03355;
        --danger-soft:   rgba(224,51,85,0.10);
        --warning:       #d97706;
        --warning-soft:  rgba(217,119,6,0.10);
        --purple:        #7c3aed;
        --purple-soft:   rgba(124,58,237,0.10);
        --orange:        #ea580c;
        --orange-soft:   rgba(234,88,12,0.10);
        --surface:       #ffffff;
        --surface-2:     #f8f9fd;
        --border:        rgba(0,0,0,0.07);
        --text-main:     #1a1f3a;
        --text-muted:    #8b92a5;
        --shadow-sm:     0 2px 8px rgba(0,0,0,0.06);
        --shadow-md:     0 8px 24px rgba(0,0,0,0.10);
        --radius:        18px;
        --radius-sm:     10px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Cairo', 'Tajawal', sans-serif;
        background: var(--surface-2);
        color: var(--text-main);
        direction: rtl;
    }

    /* ── PAGE ── */
    .mv-page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 24px 16px 80px;
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    /* ── PAGE HEADER ── */
    .mv-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .mv-header-title h2 {
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--primary);
        letter-spacing: -0.5px;
        line-height: 1.2;
    }

    .mv-header-title p {
        font-size: 0.82rem;
        color: var(--text-muted);
        margin-top: 3px;
    }

    .btn-export {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--success), #10b981);
        color: #fff;
        padding: 11px 20px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 5px 16px rgba(5,150,105,0.30);
        transition: transform 0.2s, box-shadow 0.2s;
        white-space: nowrap;
    }

    .btn-export:hover { transform: translateY(-2px); box-shadow: 0 9px 24px rgba(5,150,105,0.40); }
    .btn-export svg { width: 16px; height: 16px; }

    /* ── STATS GRID ── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
    }

    .stat-card {
        background: var(--surface);
        border-radius: var(--radius);
        padding: 20px 18px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 14px;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
        overflow: hidden;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0; right: 0;
        width: 60px; height: 60px;
        border-radius: 50%;
        opacity: 0.06;
    }

    .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }

    .stat-icon {
        width: 48px; height: 48px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon svg { width: 22px; height: 22px; }

    .stat-body { min-width: 0; }
    .stat-label { font-size: 0.72rem; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; white-space: nowrap; }
    .stat-val   { font-size: 1.55rem; font-weight: 800; line-height: 1; }

    /* color variants */
    .stat-card.blue   .stat-icon { background: var(--accent-soft);   color: var(--accent); }
    .stat-card.blue   .stat-val  { color: var(--accent); }
    .stat-card.green  .stat-icon { background: var(--success-soft);  color: var(--success); }
    .stat-card.green  .stat-val  { color: var(--success); }
    .stat-card.red    .stat-icon { background: var(--danger-soft);   color: var(--danger); }
    .stat-card.red    .stat-val  { color: var(--danger); }
    .stat-card.purple .stat-icon { background: var(--purple-soft);   color: var(--purple); }
    .stat-card.purple .stat-val  { color: var(--purple); }

    /* ── FILTER PANEL ── */
    .filter-panel {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .filter-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 22px;
        cursor: pointer;
        user-select: none;
        gap: 12px;
    }

    .filter-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-header-left svg { width: 18px; height: 18px; color: var(--accent); }

    .filter-header h3 {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--primary);
    }

    .filter-toggle-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 50px;
        border: 1.5px solid var(--border);
        background: var(--surface-2);
        font-family: 'Cairo', sans-serif;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-toggle-btn:hover { background: var(--accent-soft); border-color: var(--accent); color: var(--accent); }
    .filter-toggle-btn svg { width: 14px; height: 14px; transition: transform 0.3s; }
    .filter-toggle-btn.open svg { transform: rotate(180deg); }

    .filter-body {
        padding: 0 22px 22px;
        border-top: 1px solid var(--border);
        display: none;
    }

    .filter-body.open { display: block; }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 14px;
        margin-top: 18px;
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 7px;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        font-family: 'Cairo', sans-serif;
        font-size: 0.85rem;
        background: var(--surface);
        color: var(--text-main);
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        direction: rtl;
        appearance: none;
        -webkit-appearance: none;
    }

    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(79,110,247,0.10);
    }

    .filter-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-search {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 10px 22px;
        background: linear-gradient(135deg, var(--accent), var(--accent-light));
        color: #fff;
        border: none; border-radius: 50px;
        font-family: 'Cairo', sans-serif;
        font-size: 0.85rem; font-weight: 700;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(79,110,247,0.30);
        transition: transform 0.2s, box-shadow 0.2s;
        text-decoration: none;
    }

    .btn-search:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(79,110,247,0.38); }
    .btn-search svg { width: 15px; height: 15px; }

    .btn-reset {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 10px 18px;
        background: var(--surface-2); color: var(--text-muted);
        border: 1.5px solid var(--border); border-radius: 50px;
        font-family: 'Cairo', sans-serif;
        font-size: 0.85rem; font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-reset:hover { background: var(--danger-soft); border-color: var(--danger); color: var(--danger); }
    .btn-reset svg { width: 15px; height: 15px; }

    /* ── TABLE CARD ── */
    .table-card {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .table-card-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .table-card-header h3 {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-card-header h3 svg { width: 17px; height: 17px; color: var(--accent); }

    .table-count {
        font-size: 0.78rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* ── DESKTOP TABLE ── */
    .mv-table-wrap { overflow-x: auto; }

    .mv-table {
        width: 100%;
        border-collapse: collapse;
    }

    .mv-table thead tr {
        background: var(--surface-2);
        border-bottom: 2px solid var(--border);
    }

    .mv-table th {
        padding: 13px 16px;
        text-align: right;
        font-size: 0.7rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        white-space: nowrap;
    }

    .mv-table tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background 0.15s;
    }

    .mv-table tbody tr:last-child { border-bottom: none; }
    .mv-table tbody tr:hover { background: #fafbff; }

    .mv-table td {
        padding: 13px 16px;
        vertical-align: middle;
        font-size: 0.85rem;
    }

    /* id cell */
    .cell-id { font-size: 0.78rem; font-weight: 700; color: var(--text-muted); }

    /* date cell */
    .cell-date-main { font-weight: 600; color: var(--primary); font-size: 0.83rem; }
    .cell-date-sub  { font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; }

    /* entity cell */
    .entity-cell { display: flex; align-items: center; gap: 9px; }
    .entity-icon {
        width: 34px; height: 34px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .entity-icon svg { width: 15px; height: 15px; }
    .entity-icon.wh   { background: var(--purple-soft); color: var(--purple); }
    .entity-icon.prod { background: var(--accent-soft); color: var(--accent); }

    .entity-name { font-weight: 600; color: var(--primary); font-size: 0.83rem; white-space: nowrap; max-width: 130px; overflow: hidden; text-overflow: ellipsis; }
    .entity-sub  { font-size: 0.7rem; color: var(--text-muted); margin-top: 1px; white-space: nowrap; max-width: 130px; overflow: hidden; text-overflow: ellipsis; }

    /* type badge */
    .type-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 11px;
        border-radius: 50px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .type-badge svg { width: 11px; height: 11px; }

    .type-purchase    { background: var(--success-soft); color: var(--success); }
    .type-sale        { background: var(--accent-soft);  color: var(--accent); }
    .type-transfer_in { background: var(--purple-soft);  color: var(--purple); }
    .type-transfer_out{ background: var(--orange-soft);  color: var(--orange); }
    .type-adjustment  { background: var(--warning-soft); color: var(--warning); }
    .type-return      { background: var(--danger-soft);  color: var(--danger); }

    /* qty badge */
    .qty-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 11px;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .qty-badge svg { width: 11px; height: 11px; }
    .qty-in  { background: var(--success-soft); color: var(--success); }
    .qty-out { background: var(--danger-soft);  color: var(--danger); }

    /* balance */
    .cell-balance { font-size: 0.88rem; font-weight: 700; color: var(--primary); }

    /* user cell */
    .user-cell { display: flex; align-items: center; gap: 8px; }
    .user-avatar { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border); }
    .user-name { font-size: 0.8rem; font-weight: 600; color: var(--primary); white-space: nowrap; }

    /* notes */
    .cell-notes { max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-muted); font-size: 0.8rem; }

    /* ── MOBILE CARDS ── */
    .mv-mobile-list { display: none; flex-direction: column; gap: 12px; padding: 14px; }

    .mv-mobile-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 14px 15px;
        box-shadow: var(--shadow-sm);
        transition: box-shadow 0.2s;
        position: relative;
        overflow: hidden;
        animation: fadeUp 0.35s ease both;
    }

    .mv-mobile-card::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 4px; height: 100%;
        border-radius: 0 14px 14px 0;
    }

    .mv-mobile-card.in::before  { background: var(--success); }
    .mv-mobile-card.out::before { background: var(--danger); }

    .mv-mobile-card:hover { box-shadow: var(--shadow-md); }

    .mv-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .mv-card-product { font-size: 0.9rem; font-weight: 700; color: var(--primary); }
    .mv-card-sku     { font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; }

    .mv-card-mid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 10px;
    }

    .mv-card-field { display: flex; flex-direction: column; gap: 2px; }
    .mv-card-field-label { font-size: 0.66rem; text-transform: uppercase; letter-spacing: 0.4px; font-weight: 700; color: var(--text-muted); }
    .mv-card-field-val   { font-size: 0.82rem; font-weight: 600; color: var(--primary); }

    .mv-card-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding-top: 10px;
        border-top: 1px solid var(--border);
    }

    .mv-card-user { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--text-muted); }
    .mv-card-user img { width: 22px; height: 22px; border-radius: 50%; }

    /* ── PAGINATION ── */
    .mv-pagination {
        padding: 16px 20px;
        border-top: 1px solid var(--border);
        background: var(--surface-2);
    }

    /* ── EMPTY STATE ── */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }

    .empty-icon {
        width: 72px; height: 72px;
        background: var(--accent-soft);
        border-radius: 22px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 18px;
    }

    .empty-icon svg { width: 36px; height: 36px; color: var(--accent); }
    .empty-state h3 { font-size: 1.1rem; font-weight: 800; color: var(--primary); margin-bottom: 6px; }
    .empty-state p  { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 22px; }

    /* ── ANIMATIONS ── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .stat-card { animation: fadeUp 0.4s ease both; }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.10s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .stat-card:nth-child(4) { animation-delay: 0.20s; }

    /* ── MOBILE FAB ── */
    .mobile-fab-export {
        display: none;
        position: fixed;
        bottom: 24px; left: 50%;
        transform: translateX(-50%);
        z-index: 100;
        background: linear-gradient(135deg, var(--success), #10b981);
        color: #fff;
        padding: 13px 26px;
        border-radius: 50px;
        font-family: 'Cairo', sans-serif;
        font-size: 0.88rem; font-weight: 700;
        text-decoration: none;
        box-shadow: 0 8px 26px rgba(5,150,105,0.40);
        align-items: center; gap: 9px;
        white-space: nowrap;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .mobile-fab-export:hover { transform: translateX(-50%) translateY(-3px); }
    .mobile-fab-export svg { width: 18px; height: 18px; }

    /* ── RESPONSIVE ── */

    /* Tablet */
    @media (max-width: 1024px) {
        .filter-grid { grid-template-columns: repeat(3, 1fr); }
        .stats-grid  { grid-template-columns: repeat(2, 1fr); }
    }

    /* Mobile */
    @media (max-width: 640px) {
        .mv-page { padding: 14px 12px 90px; gap: 16px; }

        .mv-header-title h2 { font-size: 1.3rem; }
        .btn-export { display: none; }
        .mobile-fab-export { display: inline-flex; }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .stat-card { padding: 14px 13px; border-radius: 14px; }
        .stat-icon { width: 40px; height: 40px; border-radius: 12px; }
        .stat-icon svg { width: 18px; height: 18px; }
        .stat-val { font-size: 1.3rem; }
        .stat-label { font-size: 0.68rem; }

        .filter-header { padding: 15px 16px; }
        .filter-body { padding: 0 16px 16px; }
        .filter-grid { grid-template-columns: 1fr 1fr; gap: 10px; }

        /* Hide desktop table, show mobile cards */
        .mv-table-wrap { display: none; }
        .mv-mobile-list { display: flex; }

        .table-card-header { padding: 14px 16px; }
    }
</style>
@endpush

@section('content')
<div class="mv-page">

    {{-- ── HEADER ── --}}
    <div class="mv-header">
        <div class="mv-header-title">
            <h2>سجل حركات المخزون</h2>
            <p>متابعة جميع حركات الإدخال والإخراج والتحويلات</p>
        </div>
        <a href="{{ route('movements.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
           class="btn-export">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            تصدير Excel
        </a>
    </div>

    {{-- ── STATS ── --}}
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <div class="stat-body">
                <div class="stat-label">إجمالي الحركات</div>
                <div class="stat-val">{{ number_format($movements->total()) }}</div>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
            </div>
            <div class="stat-body">
                <div class="stat-label">حركات إدخال</div>
                <div class="stat-val">{{ number_format($movements->where('quantity', '>', 0)->count()) }}</div>
            </div>
        </div>

        <div class="stat-card red">
            <div class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7M12 3v18"/>
                </svg>
            </div>
            <div class="stat-body">
                <div class="stat-label">حركات إخراج</div>
                <div class="stat-val">{{ number_format($movements->where('quantity', '<', 0)->count()) }}</div>
            </div>
        </div>

        <div class="stat-card purple">
            <div class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
            </div>
            <div class="stat-body">
                <div class="stat-label">المخازن النشطة</div>
                <div class="stat-val">{{ number_format($warehouses->count()) }}</div>
            </div>
        </div>
    </div>

    {{-- ── FILTER PANEL ── --}}
    <div class="filter-panel">
        <div class="filter-header" onclick="toggleFilter()">
            <div class="filter-header-left">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                <h3>الفلاتر والبحث</h3>
            </div>
            <button type="button" class="filter-toggle-btn" id="filterToggleBtn">
                <span id="filterToggleText">عرض الفلاتر</span>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" id="filterChevron">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        <div class="filter-body" id="filterBody">
            <form method="GET" action="{{ route('movements.index') }}">
                <div class="filter-grid">
                    <div class="form-group">
                        <label>نوع الحركة</label>
                        <select name="movement_type" class="form-control">
                            <option value="">الكل</option>
                            <option value="purchase"     {{ request('movement_type')=='purchase'     ? 'selected':'' }}>شراء</option>
                            <option value="sale"         {{ request('movement_type')=='sale'         ? 'selected':'' }}>بيع</option>
                            <option value="transfer_in"  {{ request('movement_type')=='transfer_in'  ? 'selected':'' }}>تحويل وارد</option>
                            <option value="transfer_out" {{ request('movement_type')=='transfer_out' ? 'selected':'' }}>تحويل صادر</option>
                            <option value="adjustment"   {{ request('movement_type')=='adjustment'   ? 'selected':'' }}>تسوية</option>
                            <option value="return"       {{ request('movement_type')=='return'       ? 'selected':'' }}>مرتجع</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>المخزن</label>
                        <select name="warehouse_id" class="form-control">
                            <option value="">كل المخازن</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ request('warehouse_id')==$warehouse->id ? 'selected':'' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>المنتج</label>
                        <select name="product_id" class="form-control">
                            <option value="">كل المنتجات</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ request('product_id')==$product->id ? 'selected':'' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>من تاريخ</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>إلى تاريخ</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-search">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        بحث
                    </button>
                    <a href="{{ route('movements.index') }}" class="btn-reset">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        إعادة تعيين
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── TABLE CARD ── --}}
    <div class="table-card">
        <div class="table-card-header">
            <h3>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                جميع الحركات
            </h3>
            <span class="table-count">{{ number_format($movements->total()) }} حركة</span>
        </div>

        @if($movements->count() > 0)

        {{-- ── DESKTOP TABLE ── --}}
        <div class="mv-table-wrap">
            <table class="mv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>التاريخ</th>
                        <th>المخزن</th>
                        <th>المنتج</th>
                        <th>نوع الحركة</th>
                        <th>الكمية</th>
                        <th>الرصيد بعد</th>
                        <th>المستخدم</th>
                        <th>الملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $movement)
                    @php
                        $typeConfig = [
                            'purchase'     => ['label'=>'شراء',         'cls'=>'type-purchase',     'icon'=>'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                            'sale'         => ['label'=>'بيع',           'cls'=>'type-sale',         'icon'=>'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                            'transfer_in'  => ['label'=>'تحويل وارد',   'cls'=>'type-transfer_in',  'icon'=>'M19 14l-7 7m0 0l-7-7m7 7V3'],
                            'transfer_out' => ['label'=>'تحويل صادر',   'cls'=>'type-transfer_out', 'icon'=>'M5 10l7-7m0 0l7 7M12 3v18'],
                            'adjustment'   => ['label'=>'تسوية',         'cls'=>'type-adjustment',   'icon'=>'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'],
                            'return'       => ['label'=>'مرتجع',         'cls'=>'type-return',       'icon'=>'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
                        ];
                        $cfg = $typeConfig[$movement->movement_type] ?? ['label'=>$movement->movement_type,'cls'=>'','icon'=>'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
                        $isIn = $movement->quantity > 0;
                    @endphp
                    <tr>
                        <td><span class="cell-id">#{{ $movement->id }}</span></td>
                        <td>
                            <div class="cell-date-main">{{ \Carbon\Carbon::parse($movement->movement_date)->format('Y/m/d') }}</div>
                            <div class="cell-date-sub">{{ \Carbon\Carbon::parse($movement->movement_date)->format('h:i A') }}</div>
                        </td>
                        <td>
                            <div class="entity-cell">
                                <div class="entity-icon wh">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="entity-name">{{ $movement->warehouse->name }}</div>
                                    <div class="entity-sub">{{ $movement->warehouse->location ?? 'لا يوجد' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="entity-cell">
                                <div class="entity-icon prod">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="entity-name">{{ $movement->product->name }}</div>
                                    <div class="entity-sub">{{ $movement->product->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="type-badge {{ $cfg['cls'] }}">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cfg['icon'] }}"/>
                                </svg>
                                {{ $cfg['label'] }}
                            </span>
                        </td>
                        <td>
                            <span class="qty-badge {{ $isIn ? 'qty-in' : 'qty-out' }}">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $isIn ? 'M12 4v16m0-16l-4 4m4-4l4 4' : 'M12 20V4m0 16l-4-4m4 4l4-4' }}"/>
                                </svg>
                                {{ number_format(abs($movement->quantity)) }}
                            </span>
                        </td>
                        <td><span class="cell-balance">{{ number_format($movement->balance_after) }}</span></td>
                        <td>
                            <div class="user-cell">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($movement->creator->name ?? 'م') }}&background=4f6ef7&color=fff&size=60"
                                     class="user-avatar" alt="">
                                <span class="user-name">{{ $movement->creator->name ?? 'غير محدد' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="cell-notes" title="{{ $movement->notes }}">{{ $movement->notes ?? '—' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── MOBILE CARD LIST ── --}}
        <div class="mv-mobile-list">
            @foreach($movements as $i => $movement)
            @php
                $typeLabels = ['purchase'=>'شراء','sale'=>'بيع','transfer_in'=>'تحويل وارد','transfer_out'=>'تحويل صادر','adjustment'=>'تسوية','return'=>'مرتجع'];
                $typeClsMap = ['purchase'=>'type-purchase','sale'=>'type-sale','transfer_in'=>'type-transfer_in','transfer_out'=>'type-transfer_out','adjustment'=>'type-adjustment','return'=>'type-return'];
                $isIn = $movement->quantity > 0;
                $typeLabel = $typeLabels[$movement->movement_type] ?? $movement->movement_type;
                $typeCls   = $typeClsMap[$movement->movement_type] ?? '';
            @endphp
            <div class="mv-mobile-card {{ $isIn ? 'in' : 'out' }}" style="animation-delay: {{ $i * 0.04 }}s">
                <div class="mv-card-top">
                    <div>
                        <div class="mv-card-product">{{ $movement->product->name }}</div>
                        <div class="mv-card-sku">{{ $movement->product->sku }}</div>
                    </div>
                    <div style="display:flex;gap:6px;align-items:flex-start;flex-direction:column">
                        <span class="type-badge {{ $typeCls }}">{{ $typeLabel }}</span>
                        <span class="qty-badge {{ $isIn ? 'qty-in' : 'qty-out' }}" style="font-size:0.78rem">
                            {{ $isIn ? '+' : '-' }}{{ number_format(abs($movement->quantity)) }}
                        </span>
                    </div>
                </div>
                <div class="mv-card-mid">
                    <div class="mv-card-field">
                        <span class="mv-card-field-label">المخزن</span>
                        <span class="mv-card-field-val">{{ $movement->warehouse->name }}</span>
                    </div>
                    <div class="mv-card-field">
                        <span class="mv-card-field-label">التاريخ</span>
                        <span class="mv-card-field-val">{{ \Carbon\Carbon::parse($movement->movement_date)->format('Y/m/d') }}</span>
                    </div>
                    <div class="mv-card-field">
                        <span class="mv-card-field-label">الرصيد بعد</span>
                        <span class="mv-card-field-val" style="color:var(--primary);font-weight:800">{{ number_format($movement->balance_after) }}</span>
                    </div>
                    <div class="mv-card-field">
                        <span class="mv-card-field-label">الوقت</span>
                        <span class="mv-card-field-val">{{ \Carbon\Carbon::parse($movement->movement_date)->format('h:i A') }}</span>
                    </div>
                </div>
                <div class="mv-card-bottom">
                    <div class="mv-card-user">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($movement->creator->name ?? 'م') }}&background=4f6ef7&color=fff&size=40" alt="">
                        {{ $movement->creator->name ?? 'غير محدد' }}
                    </div>
                    @if($movement->notes)
                    <span style="font-size:0.72rem;color:var(--text-muted);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        {{ $movement->notes }}
                    </span>
                    @endif
                    <span style="font-size:0.72rem;color:var(--text-muted)">#{{ $movement->id }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mv-pagination">
            {{ $movements->links() }}
        </div>

        @else
        <div class="empty-state">
            <div class="empty-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3>لا توجد حركات</h3>
            <p>لم يتم العثور على أي حركات مخزنية بالفلاتر المحددة</p>
            <a href="{{ route('movements.index') }}" class="btn-search" style="text-decoration:none;display:inline-flex">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                إعادة تعيين الفلاتر
            </a>
        </div>
        @endif
    </div>

</div>

{{-- ── MOBILE FAB ── --}}
<a href="{{ route('movements.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
   class="mobile-fab-export">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
    </svg>
    تصدير Excel
</a>

@push('scripts')
<script>
    function toggleFilter() {
        const body = document.getElementById('filterBody');
        const btn  = document.getElementById('filterToggleBtn');
        const txt  = document.getElementById('filterToggleText');
        const open = body.classList.toggle('open');
        btn.classList.toggle('open', open);
        txt.textContent = open ? 'إخفاء الفلاتر' : 'عرض الفلاتر';
    }

    // Auto-open filters if any are active
    (function() {
        const params = new URLSearchParams(window.location.search);
        const keys = ['movement_type','warehouse_id','product_id','date_from','date_to'];
        if (keys.some(k => params.get(k))) toggleFilter();
    })();
</script>
@endpush
@endsection