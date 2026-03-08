@extends('layouts.app')

@section('title', 'إدارة المخازن')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@300;400;500;700;800&display=swap');

    :root {
        --primary: #1a1f3a;
        --accent: #4f6ef7;
        --accent-glow: #6b84ff;
        --accent-soft: rgba(79, 110, 247, 0.12);
        --success: #059669;
        --success-soft: rgba(5, 150, 105, 0.10);
        --danger: #ff4d6d;
        --danger-soft: rgba(255, 77, 109, 0.12);
        --warning: #f59e0b;
        --surface: #ffffff;
        --surface-2: #f8f9fd;
        --border: rgba(0, 0, 0, 0.07);
        --text-main: #1a1f3a;
        --text-muted: #8b92a5;
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
        --shadow-md: 0 8px 24px rgba(0,0,0,0.10);
        --shadow-lg: 0 20px 48px rgba(0,0,0,0.13);
        --radius: 18px;
        --radius-sm: 10px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Cairo', 'Tajawal', sans-serif;
        background: var(--surface-2);
        color: var(--text-main);
        direction: rtl;
    }

    /* ── PAGE WRAPPER ── */
    .wh-page {
        max-width: 1280px;
        margin: 0 auto;
        padding: 24px 16px 80px;
    }

    /* ── TOP BAR ── */
    .wh-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }

    .wh-title-block h2 {
        font-size: 1.7rem;
        font-weight: 800;
        color: var(--primary);
        letter-spacing: -0.5px;
        line-height: 1.2;
    }

    .wh-title-block p {
        font-size: 0.82rem;
        color: var(--text-muted);
        margin-top: 3px;
        font-weight: 400;
    }

    .btn-new {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--accent), #6b84ff);
        color: #fff;
        padding: 12px 22px;
        border-radius: 50px;
        font-size: 0.88rem;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 6px 20px rgba(79,110,247,0.35);
        transition: transform 0.2s, box-shadow 0.2s;
        white-space: nowrap;
    }

    .btn-new:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(79,110,247,0.45);
    }

    .btn-new svg { width: 16px; height: 16px; }

    /* ── ALERT MESSAGES ── */
    .alert {
        padding: 14px 18px;
        border-radius: var(--radius-sm);
        margin-bottom: 18px;
        font-size: 0.88rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success { background: var(--success-soft); color: #065f46; border: 1px solid rgba(5,150,105,0.25); }
    .alert-error   { background: var(--danger-soft);  color: #d63352; border: 1px solid rgba(255,77,109,0.25); }

    /* ── STATS BAR ── */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: var(--surface);
        border-radius: var(--radius);
        padding: 18px 16px;
        text-align: center;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
        transition: transform 0.2s;
    }

    .stat-card:hover { transform: translateY(-2px); }

    .stat-card .stat-icon {
        width: 42px; height: 42px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 10px;
    }

    .stat-card .stat-icon svg { width: 22px; height: 22px; }
    .stat-card .stat-val { font-size: 1.5rem; font-weight: 800; line-height: 1; margin-bottom: 4px; }
    .stat-card .stat-label { font-size: 0.72rem; color: var(--text-muted); font-weight: 500; }

    .stat-card.blue .stat-icon  { background: var(--accent-soft); color: var(--accent); }
    .stat-card.blue .stat-val   { color: var(--accent); }
    .stat-card.green .stat-icon { background: var(--success-soft); color: var(--success); }
    .stat-card.green .stat-val  { color: #047857; }
    .stat-card.red .stat-icon   { background: var(--danger-soft); color: var(--danger); }
    .stat-card.red .stat-val    { color: var(--danger); }

    /* ── SEARCH / FILTER ── */
    .search-row {
        display: flex;
        gap: 10px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }

    .search-wrap {
        flex: 1;
        min-width: 200px;
        position: relative;
    }

    .search-wrap svg {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px; height: 18px;
        color: var(--text-muted);
        pointer-events: none;
    }

    .search-input {
        width: 100%;
        padding: 12px 42px 12px 16px;
        border: 1.5px solid var(--border);
        border-radius: 50px;
        font-family: 'Cairo', sans-serif;
        font-size: 0.88rem;
        background: var(--surface);
        color: var(--text-main);
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        direction: rtl;
    }

    .search-input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(79,110,247,0.1);
    }

    .filter-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 18px;
        border-radius: 50px;
        border: 1.5px solid var(--border);
        background: var(--surface);
        font-family: 'Cairo', sans-serif;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .filter-btn:hover, .filter-btn.active {
        background: var(--accent-soft);
        border-color: var(--accent);
        color: var(--accent);
    }

    .filter-btn svg { width: 16px; height: 16px; }

    /* ── GRID ── */
    .wh-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 18px;
    }

    /* ── WAREHOUSE CARD ── */
    .wh-card {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
        overflow: hidden;
        transition: transform 0.25s, box-shadow 0.25s;
        position: relative;
    }

    .wh-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    /* Card accent strip */
    .wh-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--accent), #6b84ff);
        border-radius: var(--radius) var(--radius) 0 0;
    }

    .wh-card-header {
        padding: 20px 20px 0;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .wh-icon-wrap {
        width: 52px; height: 52px;
        background: var(--accent-soft);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .wh-icon-wrap svg { width: 26px; height: 26px; color: var(--accent); }

    .wh-name-block { flex: 1; min-width: 0; }

    .wh-name-block h3 {
        font-size: 1rem;
        font-weight: 800;
        color: var(--primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .wh-code {
        font-size: 0.74rem;
        color: var(--text-muted);
        font-weight: 500;
        margin-top: 2px;
        letter-spacing: 0.5px;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 700;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .badge-active   { background: var(--success-soft); color: #047857; }
    .badge-inactive { background: var(--danger-soft);  color: #d63352; }

    /* Divider */
    .wh-divider {
        height: 1px;
        background: var(--border);
        margin: 16px 20px;
    }

    /* Stats row */
    .wh-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        padding: 0 20px;
    }

    .wh-stat {
        border-radius: 12px;
        padding: 12px 14px;
    }

    .wh-stat.blue  { background: var(--accent-soft); }
    .wh-stat.green { background: var(--success-soft); }

    .wh-stat .s-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .wh-stat.blue  .s-label { color: var(--accent); }
    .wh-stat.green .s-label { color: #047857; }

    .wh-stat .s-val {
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1;
    }

    .wh-stat.blue  .s-val { color: var(--accent); }
    .wh-stat.green .s-val { color: #047857; }

    /* Meta info */
    .wh-meta {
        padding: 12px 20px 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .wh-meta-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .wh-meta-row svg { width: 15px; height: 15px; flex-shrink: 0; }
    .wh-meta-row span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* Actions */
    .wh-actions {
        padding: 16px 20px 20px;
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .btn-view {
        flex: 1;
        text-align: center;
        padding: 11px 0;
        background: linear-gradient(135deg, var(--accent), #6b84ff);
        color: #fff;
        border-radius: 12px;
        font-size: 0.83rem;
        font-weight: 700;
        text-decoration: none;
        transition: opacity 0.2s, transform 0.2s;
        box-shadow: 0 4px 14px rgba(79,110,247,0.3);
    }

    .btn-view:hover { opacity: 0.9; transform: translateY(-1px); }

    .btn-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: background 0.2s, transform 0.2s;
        border: none; cursor: pointer;
        text-decoration: none;
        flex-shrink: 0;
    }

    .btn-icon:hover { transform: scale(1.08); }
    .btn-icon svg { width: 18px; height: 18px; }

    .btn-edit   { background: #f1f3f9; color: #4b5563; }
    .btn-edit:hover   { background: #e5e7eb; }
    .btn-delete { background: var(--danger-soft); color: var(--danger); border: none; }
    .btn-delete:hover { background: rgba(255,77,109,0.2); }

    /* ── EMPTY STATE ── */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
    }

    .empty-icon {
        width: 80px; height: 80px;
        background: var(--accent-soft);
        border-radius: 24px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
    }

    .empty-icon svg { width: 40px; height: 40px; color: var(--accent); }

    .empty-state h3 { font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-bottom: 8px; }
    .empty-state p  { font-size: 0.88rem; color: var(--text-muted); margin-bottom: 24px; }

    /* ── PAGINATION ── */
    .wh-pagination { margin-top: 28px; display: flex; justify-content: center; }

    /* ── MOBILE BOTTOM NAV ── */
    .mobile-fab {
        display: none;
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 100;
        background: linear-gradient(135deg, var(--accent), #6b84ff);
        color: #fff;
        padding: 14px 28px;
        border-radius: 50px;
        font-family: 'Cairo', sans-serif;
        font-size: 0.9rem;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 8px 28px rgba(79,110,247,0.45);
        align-items: center;
        gap: 10px;
        white-space: nowrap;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .mobile-fab:hover { transform: translateX(-50%) translateY(-3px); box-shadow: 0 14px 36px rgba(79,110,247,0.5); }
    .mobile-fab svg { width: 20px; height: 20px; }

    /* ── MOBILE CARD MODE ── */
    @media (max-width: 640px) {

        .wh-page { padding: 16px 12px 100px; }

        .wh-topbar { margin-bottom: 20px; }
        .wh-title-block h2 { font-size: 1.35rem; }

        /* Hide desktop new-button on mobile, show FAB */
        .btn-new { display: none; }
        .mobile-fab { display: inline-flex; }

        .stats-bar {
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .stat-card { padding: 14px 10px; border-radius: 14px; }
        .stat-card .stat-icon { width: 36px; height: 36px; border-radius: 10px; }
        .stat-card .stat-icon svg { width: 18px; height: 18px; }
        .stat-card .stat-val { font-size: 1.25rem; }
        .stat-card .stat-label { font-size: 0.65rem; }

        /* Full-width swipeable card on mobile */
        .wh-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        /* Mobile card: horizontal compact header */
        .wh-card::before { height: 3px; }

        .wh-card-header { padding: 16px 16px 0; }

        .wh-icon-wrap { width: 44px; height: 44px; border-radius: 12px; }
        .wh-icon-wrap svg { width: 22px; height: 22px; }

        .wh-name-block h3 { font-size: 0.95rem; }
        .wh-code { font-size: 0.7rem; }

        .wh-divider { margin: 14px 16px; }

        .wh-stats { padding: 0 16px; gap: 8px; }
        .wh-stat { padding: 10px 12px; border-radius: 10px; }
        .wh-stat .s-val { font-size: 1.1rem; }

        .wh-meta { padding: 10px 16px 0; }
        .wh-meta-row { font-size: 0.75rem; }

        .wh-actions {
            padding: 14px 16px 16px;
            gap: 6px;
        }

        .btn-view { padding: 10px 0; font-size: 0.8rem; border-radius: 10px; }
        .btn-icon { width: 36px; height: 36px; border-radius: 8px; }
        .btn-icon svg { width: 16px; height: 16px; }

        .empty-state { padding: 40px 16px; }
        .empty-icon { width: 64px; height: 64px; border-radius: 18px; }
        .empty-icon svg { width: 32px; height: 32px; }
        .empty-state h3 { font-size: 1rem; }
    }

    /* ── TABLET ── */
    @media (min-width: 641px) and (max-width: 1023px) {
        .wh-grid { grid-template-columns: repeat(2, 1fr); }
        .stats-bar { grid-template-columns: repeat(3, 1fr); }
    }

    /* ── ANIMATION ── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .wh-card {
        animation: fadeUp 0.4s ease both;
    }

    .wh-card:nth-child(1)  { animation-delay: 0.05s; }
    .wh-card:nth-child(2)  { animation-delay: 0.10s; }
    .wh-card:nth-child(3)  { animation-delay: 0.15s; }
    .wh-card:nth-child(4)  { animation-delay: 0.20s; }
    .wh-card:nth-child(5)  { animation-delay: 0.25s; }
    .wh-card:nth-child(6)  { animation-delay: 0.30s; }
    .wh-card:nth-child(n+7){ animation-delay: 0.35s; }
</style>
@endpush

@section('content')
<div class="wh-page">

    {{-- ── TOP BAR ── --}}
    <div class="wh-topbar">
        <div class="wh-title-block">
            <h2>إدارة المخازن</h2>
            <p>عرض وإدارة جميع المخازن</p>
        </div>
        <a href="{{ route('warehouses.create') }}" class="btn-new">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            مخزن جديد
        </a>
    </div>

    {{-- ── ALERTS ── --}}
    @if(session('success'))
    <div class="alert alert-success">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ── STATS BAR ── --}}
    <div class="stats-bar">
        <div class="stat-card blue">
            <div class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
            </div>
            <div class="stat-val">{{ $warehouses->total() }}</div>
            <div class="stat-label">إجمالي المخازن</div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="stat-val">{{ $warehouses->where('is_active', true)->count() }}</div>
            <div class="stat-label">المخازن النشطة</div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="stat-val">{{ $warehouses->where('is_active', false)->count() }}</div>
            <div class="stat-label">غير نشطة</div>
        </div>
    </div>

    {{-- ── SEARCH / FILTER ── --}}
    <div class="search-row">
        <div class="search-wrap">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" class="search-input" placeholder="بحث عن مخزن..." id="warehouseSearch">
        </div>
        <button class="filter-btn active" onclick="filterWarehouses('all', this)">
            الكل
        </button>
        <button class="filter-btn" onclick="filterWarehouses('active', this)">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
            </svg>
            نشط
        </button>
        <button class="filter-btn" onclick="filterWarehouses('inactive', this)">
            غير نشط
        </button>
    </div>

    {{-- ── WAREHOUSE GRID ── --}}
    <div class="wh-grid" id="warehouseGrid">
        @forelse($warehouses as $warehouse)
        <div class="wh-card" data-status="{{ $warehouse->is_active ? 'active' : 'inactive' }}" data-name="{{ strtolower($warehouse->name) }}">

            {{-- Header --}}
            <div class="wh-card-header">
                <div class="wh-icon-wrap">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                    </svg>
                </div>
                <div class="wh-name-block">
                    <h3>{{ $warehouse->name }}</h3>
                    <div class="wh-code">{{ $warehouse->code }}</div>
                </div>
                <span class="badge {{ $warehouse->is_active ? 'badge-active' : 'badge-inactive' }}">
                    {{ $warehouse->is_active ? 'نشط' : 'غير نشط' }}
                </span>
            </div>

            <div class="wh-divider"></div>

            {{-- Stats --}}
            <div class="wh-stats">
                <div class="wh-stat blue">
                    <div class="s-label">الأصناف</div>
                    <div class="s-val">{{ number_format($warehouse->total_products ?? 0) }}</div>
                </div>
                <div class="wh-stat green">
                    <div class="s-label">القيمة</div>
                    <div class="s-val">{{ number_format($warehouse->total_value ?? 0) }}</div>
                </div>
            </div>

            {{-- Meta --}}
            <div class="wh-meta">
                @if($warehouse->address)
                <div class="wh-meta-row">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>{{ Str::limit($warehouse->address, 45) }}</span>
                </div>
                @endif

                @if($warehouse->manager_name)
                <div class="wh-meta-row">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>{{ $warehouse->manager_name }}</span>
                </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="wh-actions">
                <a href="{{ route('warehouses.show', $warehouse->id) }}" class="btn-view">
                    عرض التفاصيل
                </a>
                <a href="{{ route('warehouses.edit', $warehouse->id) }}" class="btn-icon btn-edit" title="تعديل">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
                <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST"
                      onsubmit="return confirm('⚠️ هل أنت متأكد من حذف هذا المخزن؟')" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-icon btn-delete" title="حذف">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>

        </div>
        @empty
        <div class="empty-state">
            <div class="empty-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
            </div>
            <h3>لا توجد مخازن</h3>
            <p>لم يتم إنشاء أي مخازن بعد</p>
            <a href="{{ route('warehouses.create') }}" class="btn-new" style="display:inline-flex">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                إنشاء مخزن جديد
            </a>
        </div>
        @endforelse
    </div>

    {{-- ── PAGINATION ── --}}
    @if($warehouses->hasPages())
    <div class="wh-pagination">
        {{ $warehouses->links() }}
    </div>
    @endif

</div>

{{-- ── MOBILE FAB ── --}}
<a href="{{ route('warehouses.create') }}" class="mobile-fab">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
    </svg>
    مخزن جديد
</a>

@push('scripts')
<script>
    // Live search
    document.getElementById('warehouseSearch').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('#warehouseGrid .wh-card').forEach(card => {
            const name = card.dataset.name || '';
            card.style.display = (!q || name.includes(q)) ? '' : 'none';
        });
    });

    // Status filter
    function filterWarehouses(status, btn) {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('#warehouseGrid .wh-card').forEach(card => {
            if (status === 'all' || card.dataset.status === status) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection