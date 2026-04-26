@extends('layouts.app')

@section('title', 'تفاصيل المخزن')
@section('page-title', 'تفاصيل المخزن')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;900&family=Tajawal:wght@300;400;500;700&display=swap');

    :root {
        --primary:        #1e40af;
        --primary-light:  #3b82f6;
        --primary-glow:   rgba(59,130,246,0.15);
        --surface:        #ffffff;
        --surface-2:      #f8faff;
        --surface-3:      #eef2ff;
        --border:         rgba(99,102,241,0.12);
        --border-hover:   rgba(99,102,241,0.28);
        --text-primary:   #0f172a;
        --text-secondary: #475569;
        --text-muted:     #94a3b8;
        --danger:         #dc2626;
        --shadow-sm:      0 2px 8px rgba(30,64,175,0.07);
        --shadow-md:      0 8px 32px rgba(30,64,175,0.12);
        --radius:         16px;
        --radius-sm:      10px;
        --ease:           cubic-bezier(0.4,0,0.2,1);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Cairo', 'Tajawal', sans-serif;
        background: linear-gradient(135deg, #f0f4ff 0%, #e8eeff 40%, #f5f0ff 100%);
        min-height: 100vh;
        direction: rtl;
    }

    /* ── BG Dots ── */
    .bg-dots {
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: -1;
        background-image: radial-gradient(circle, rgba(99,102,241,.06) 1px, transparent 1px);
        background-size: 32px 32px;
    }

    /* ── Page ── */
    .wh-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 26px 22px;
        animation: fadeUp .5s var(--ease) both;
    }

    /* هيدر الشركة الاحترافي */
    .company-invoice-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 25px 35px;
        border-radius: 20px;
        margin-bottom: 25px;
        border: 1px solid rgba(99,102,241,0.12);
        box-shadow: 0 2px 12px rgba(79,99,210,0.07);
        position: relative;
        overflow: hidden;
    }
    .company-invoice-header::before {
        content: '';
        position: absolute;
        top: 0; right: 0; width: 6px; height: 100%;
        background: #4f63d2;
    }
    .header-info h1 {
        font-size: 24px;
        font-weight: 900;
        color: #1a2140;
        margin: 0 0 5px 0;
    }
    .header-info p {
        font-size: 13px;
        color: #7e90b0;
        margin: 0;
        font-weight: 600;
    }
    .header-logo img {
        max-height: 70px;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.05));
    }
    .header-badge {
        background: #eef0fc;
        color: #4f63d2;
        padding: 6px 15px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(20px) scale(.985); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    @keyframes countUp {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Header ── */
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2rem;
        animation: fadeUp .5s var(--ease) .05s both;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: .875rem;
        flex-wrap: wrap;
    }

    .back-btn {
        width: 44px; height: 44px;
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: var(--text-secondary);
        text-decoration: none;
        flex-shrink: 0;
        transition: all .22s var(--ease);
        box-shadow: var(--shadow-sm);
    }

    .back-btn:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: #fff;
        transform: translateX(3px);
        box-shadow: var(--shadow-md);
    }

    .header-title h2 {
        font-size: clamp(1.2rem, 2.8vw, 1.65rem);
        font-weight: 700;
        color: var(--text-primary);
        letter-spacing: -.02em;
        line-height: 1.25;
    }

    .header-title p {
        font-size: .8rem;
        color: var(--text-muted);
        margin-top: 3px;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 99px;
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-badge .dot {
        width: 7px; height: 7px;
        border-radius: 50%;
    }

    .status-badge.active   { background: #ecfdf5; color: #065f46; border: 1.5px solid #10b981; }
    .status-badge.inactive { background: #fef2f2; color: #7f1d1d; border: 1.5px solid #ef4444; }
    .status-badge.maintenance { background: #fffbeb; color: #78350f; border: 1.5px solid #f59e0b; }
    .status-badge.active .dot   { background: #10b981; }
    .status-badge.inactive .dot { background: #ef4444; }
    .status-badge.maintenance .dot { background: #f59e0b; }

    /* Action Buttons */
    .header-actions { display: flex; gap: .625rem; flex-wrap: wrap; }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: .7rem 1.4rem;
        border-radius: var(--radius-sm);
        font-family: 'Cairo', sans-serif;
        font-size: .85rem;
        font-weight: 700;
        cursor: pointer;
        border: none;
        text-decoration: none;
        transition: all .22s var(--ease);
        white-space: nowrap;
    }

    .btn svg { width: 16px; height: 16px; flex-shrink: 0; }
    .btn:active { transform: scale(.97); }

    .btn-edit {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: #fff;
        box-shadow: 0 4px 14px rgba(30,64,175,.35);
    }

    .btn-edit:hover {
        box-shadow: 0 6px 20px rgba(30,64,175,.48);
        transform: translateY(-1px);
    }

    .btn-delete {
        background: var(--surface);
        color: #dc2626;
        border: 1.5px solid rgba(220,38,38,.2);
        box-shadow: var(--shadow-sm);
    }

    .btn-delete:hover {
        background: #fef2f2;
        border-color: #dc2626;
        box-shadow: 0 4px 14px rgba(220,38,38,.18);
    }

    /* ── Layout Grid ── */
    .main-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }

    /* ── Card Base ── */
    .wh-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1.5px solid var(--border);
        box-shadow: var(--shadow-sm);
        transition: box-shadow .3s var(--ease), border-color .3s var(--ease);
        animation: cardIn .55s var(--ease) both;
        overflow: hidden;
    }

    .wh-card:nth-child(1) { animation-delay: .10s; }
    .wh-card:nth-child(2) { animation-delay: .18s; }

    .card-inner { padding: 1.75rem 2rem; }

    /* ── Card Head ── */
    .card-head {
        display: flex;
        align-items: center;
        gap: .875rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.1rem;
        border-bottom: 1.5px solid var(--border);
    }

    .card-ico {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .card-ico svg { width: 18px; height: 18px; }
    .card-ico.blue   { background: #eff6ff; color: #1d4ed8; }
    .card-ico.purple { background: #faf5ff; color: #7c3aed; }

    .card-head h3 { font-size: .95rem; font-weight: 700; color: var(--text-primary); }
    .card-head p  { font-size: .75rem; color: var(--text-muted); margin-top: 2px; }

    /* ── Info Items ── */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: .875rem 1rem;
        background: var(--surface-2);
        border-radius: var(--radius-sm);
        border: 1.5px solid var(--border);
        transition: border-color .2s, box-shadow .2s;
    }

    .info-item:hover {
        border-color: var(--border-hover);
        box-shadow: var(--shadow-sm);
    }

    .info-item.full { grid-column: 1 / -1; }

    .info-ico {
        width: 36px; height: 36px;
        background: var(--surface-3);
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        color: var(--primary);
    }

    .info-ico svg { width: 17px; height: 17px; }

    .info-label {
        font-size: .72rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .info-val {
        font-size: .92rem;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.3;
    }

    .info-val.code {
        font-family: 'Courier New', monospace;
        font-size: .88rem;
        letter-spacing: .06em;
        color: var(--primary);
        background: var(--surface-3);
        padding: 2px 8px;
        border-radius: 6px;
        display: inline-block;
    }

    .info-val.muted { color: var(--text-muted); font-weight: 400; font-style: italic; }

    /* ── Stat Cards ── */
    .stats-col {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        animation: cardIn .55s var(--ease) .18s both;
    }

    .stat-card {
        border-radius: var(--radius);
        padding: 1.25rem 1.4rem;
        position: relative;
        overflow: hidden;
        transition: transform .22s var(--ease), box-shadow .22s var(--ease);
        animation: cardIn .55s var(--ease) both;
    }

    .stat-card:nth-child(1) { animation-delay: .14s; }
    .stat-card:nth-child(2) { animation-delay: .22s; }
    .stat-card:nth-child(3) { animation-delay: .30s; }
    .stat-card:nth-child(4) { animation-delay: .38s; }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: -40px; left: -40px;
        width: 120px; height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,.1);
        pointer-events: none;
    }

    .stat-card.blue   { background: linear-gradient(135deg, #1e40af, #3b82f6); }
    .stat-card.green  { background: linear-gradient(135deg, #059669, #10b981); }
    .stat-card.purple { background: linear-gradient(135deg, #6d28d9, #8b5cf6); }
    .stat-card.orange { background: linear-gradient(135deg, #c2410c, #f97316); }

    .stat-icon {
        width: 42px; height: 42px;
        background: rgba(255,255,255,.18);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: .875rem;
    }

    .stat-icon svg { width: 22px; height: 22px; color: #fff; }

    .stat-label {
        font-size: .75rem;
        color: rgba(255,255,255,.8);
        font-weight: 600;
        margin-bottom: 4px;
    }

    .stat-val {
        font-size: 2rem;
        font-weight: 900;
        color: #fff;
        line-height: 1;
        letter-spacing: -.03em;
        animation: countUp .5s var(--ease) .4s both;
    }

    .stat-val small {
        font-size: 1rem;
        font-weight: 600;
        opacity: .85;
    }

    /* ── Table Section ── */
    .table-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1.5px solid var(--border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        animation: cardIn .55s var(--ease) .28s both;
    }

    .table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: .75rem;
        padding: 1.25rem 1.75rem;
        background: var(--surface-2);
        border-bottom: 1.5px solid var(--border);
    }

    .table-head-left { display: flex; align-items: center; gap: .875rem; }

    .table-head h3 { font-size: .95rem; font-weight: 700; color: var(--text-primary); }

    .count-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        background: var(--surface-3);
        border: 1.5px solid var(--border);
        border-radius: 99px;
        font-size: .78rem;
        font-weight: 700;
        color: var(--primary);
    }

    .table-wrap { overflow-x: auto; }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    thead tr {
        background: var(--surface-2);
        border-bottom: 1.5px solid var(--border);
    }

    th {
        padding: .75rem 1.25rem;
        font-size: .72rem;
        font-weight: 700;
        color: var(--text-muted);
        text-align: right;
        letter-spacing: .05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background .18s;
    }

    tbody tr:last-child { border-bottom: none; }

    tbody tr:hover { background: var(--surface-2); }

    td {
        padding: .875rem 1.25rem;
        font-size: .855rem;
        color: var(--text-secondary);
        text-align: right;
        vertical-align: middle;
    }

    .product-name {
        font-weight: 700;
        color: var(--text-primary);
        font-size: .88rem;
    }

    .product-cat {
        font-size: .72rem;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .sku-badge {
        font-family: 'Courier New', monospace;
        font-size: .75rem;
        color: var(--primary);
        background: var(--surface-3);
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 700;
        letter-spacing: .04em;
    }

    .qty {
        font-size: 1.05rem;
        font-weight: 900;
        letter-spacing: -.01em;
    }

    .qty.ok  { color: #059669; }
    .qty.low { color: #dc2626; }
    .qty.zero{ color: var(--text-muted); }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: .72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .pill .pdot { width: 6px; height: 6px; border-radius: 50%; }

    .pill.available    { background: #ecfdf5; color: #065f46; }
    .pill.available .pdot { background: #10b981; }
    .pill.low-stock    { background: #fef2f2; color: #7f1d1d; }
    .pill.low-stock .pdot { background: #ef4444; }
    .pill.out-of-stock { background: #f1f5f9; color: #475569; }
    .pill.out-of-stock .pdot { background: #94a3b8; }

    /* ── Empty State ── */
    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
    }

    .empty-circle {
        width: 72px; height: 72px;
        background: var(--surface-3);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.1rem;
        border: 2px dashed var(--border-hover);
    }

    .empty-circle svg { width: 32px; height: 32px; color: var(--text-muted); }

    .empty-state h4 { font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: .4rem; }
    .empty-state p  { font-size: .84rem; color: var(--text-muted); }

    /* ── Description block ── */
    .desc-block {
        margin-top: 1.25rem;
        padding: 1rem 1.25rem;
        background: var(--surface-2);
        border-radius: var(--radius-sm);
        border-right: 3px solid var(--primary-light);
    }

    .desc-block p { font-size: .875rem; color: var(--text-secondary); line-height: 1.7; }

    /* ── Responsive ── */
    @media (max-width: 900px) {
        .main-grid { grid-template-columns: 1fr; }
        .stats-col { flex-direction: row; flex-wrap: wrap; }
        .stat-card { flex: 1 1 200px; }
    }

    @media (max-width: 640px) {
        .wh-page       { padding: 1.25rem .875rem 3rem; }
        .card-inner    { padding: 1.25rem 1.1rem; }
        .info-grid     { grid-template-columns: 1fr; }
        .info-item.full { grid-column: 1; }
        .page-header   { flex-direction: column; align-items: flex-start; }
        .header-actions { width: 100%; }
        .header-actions .btn { flex: 1; justify-content: center; }
        .table-head    { flex-direction: column; align-items: flex-start; }
        .stat-card     { flex: 1 1 140px; }
        .stat-val      { font-size: 1.6rem; }
    }
</style>
@endpush

@section('content')
<div class="bg-dots"></div>

<div class="wh-page">

    <!-- هيدر الشركة الاحترافي -->
    <div class="company-invoice-header">
        <div class="header-info">
            <h1>{{ $company->name ?? 'نظام ماجزني لإدارة المخازن' }}</h1>
            <p><i class="fas fa-map-marker-alt"></i> {{ $company->address ?? 'العنوان غير مسجل' }}</p>
            <p><i class="fas fa-phone"></i> {{ $company->phone ?? '01XXXXXXXXX' }}</p>
            <div class="header-badge">
                <i class="fas fa-warehouse"></i>
                نظام إدارة المخازن الذكي
            </div>
        </div>
        <div class="header-logo">
            @if(isset($company->logo) && $company->logo)
                <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo">
            @else
                <div style="width: 70px; height: 70px; background: #eef0fc; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-weight: 900; color: #4f63d2; font-size: 24px;">
                    M
                </div>
            @endif
        </div>
    </div>

    <!-- ══ Header ══ -->
    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('warehouses.index') }}" class="back-btn" title="رجوع للقائمة">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="header-title">
                <h2>{{ $warehouse->name }}</h2>
                <p>عرض التفاصيل الكاملة للمخزن</p>
            </div>

            @php
                $statusMap = [
                    'active'      => ['label' => 'نشط',          'class' => 'active'],
                    'inactive'    => ['label' => 'غير نشط',       'class' => 'inactive'],
                    'maintenance' => ['label' => 'قيد الصيانة',   'class' => 'maintenance'],
                ];
                $st = $statusMap[$warehouse->status ?? ''] ?? ($warehouse->is_active ? $statusMap['active'] : $statusMap['inactive']);
            @endphp

            <span class="status-badge {{ $st['class'] }}">
                <span class="dot"></span>
                {{ $st['label'] }}
            </span>
        </div>

        <div class="header-actions">
            <a href="{{ route('warehouses.edit', $warehouse->id) }}" class="btn btn-edit">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                تعديل المخزن
            </a>

            <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST" class="inline"
                  onsubmit="return confirm('هل أنت متأكد من حذف هذا المخزن؟ لا يمكن التراجع عن هذا الإجراء.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-delete">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    حذف
                </button>
            </form>
        </div>
    </div>

    <!-- ══ Main Grid ══ -->
    <div class="main-grid">

        <!-- Info Card -->
        <div class="wh-card">
            <div class="card-inner">
                <div class="card-head">
                    <div class="card-ico blue">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h3>معلومات المخزن</h3>
                        <p>البيانات الأساسية والموقع وطرق الاتصال</p>
                    </div>
                </div>

                <div class="info-grid">
                    <!-- كود المخزن -->
                    <div class="info-item">
                        <div class="info-ico">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="info-label">كود المخزن</p>
                            @if($warehouse->code)
                                <span class="info-val code">{{ $warehouse->code }}</span>
                            @else
                                <p class="info-val muted">غير محدد</p>
                            @endif
                        </div>
                    </div>

                    <!-- المسؤول -->
                    <div class="info-item">
                        <div class="info-ico">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="info-label">المسؤول</p>
                            <p class="info-val">{{ optional($warehouse->manager)->name ?? 'غير محدد' }}</p>
                        </div>
                    </div>

                    <!-- الموقع -->
                    @if($warehouse->city || $warehouse->area)
                    <div class="info-item">
                        <div class="info-ico">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="info-label">الموقع</p>
                            <p class="info-val">
                                {{ $warehouse->city }}{{ $warehouse->area ? ' — ' . $warehouse->area : '' }}
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- الهاتف -->
                    @if($warehouse->phone)
                    <div class="info-item">
                        <div class="info-ico">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="info-label">رقم الهاتف</p>
                            <p class="info-val" dir="ltr" style="text-align:right">{{ $warehouse->phone }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- البريد -->
                    @if($warehouse->email)
                    <div class="info-item">
                        <div class="info-ico">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="info-label">البريد الإلكتروني</p>
                            <p class="info-val" dir="ltr" style="text-align:right; font-size:.82rem">{{ $warehouse->email }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- العنوان -->
                    @if($warehouse->address)
                    <div class="info-item full">
                        <div class="info-ico">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <div>
                            <p class="info-label">العنوان التفصيلي</p>
                            <p class="info-val" style="font-weight:500">{{ $warehouse->address }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- الوصف -->
                @if($warehouse->description)
                <div class="desc-block">
                    <p>{{ $warehouse->description }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Stats Column -->
        <div class="stats-col">

            <div class="stat-card blue">
                <div class="stat-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <p class="stat-label">إجمالي المنتجات</p>
                <p class="stat-val">{{ $products->count() }}</p>
            </div>

            <div class="stat-card green">
                <div class="stat-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="stat-label">إجمالي الكمية</p>
                <p class="stat-val">{{ number_format($stats['total_quantity'] ?? 0) }}</p>
            </div>

            <div class="stat-card purple">
                <div class="stat-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="stat-label">إجمالي القيمة</p>
                <p class="stat-val">{{ number_format($stats['total_value'] ?? 0, 0) }} <small>ج.م</small></p>
            </div>

            <div class="stat-card orange">
                <div class="stat-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="stat-label">قريبة من النفاد</p>
                <p class="stat-val">{{ $stats['low_stock_items'] ?? 0 }}</p>
            </div>

        </div>
    </div>

    <!-- ══ Products Table ══ -->
    <div class="table-card">
        <div class="table-head">
            <div class="table-head-left">
                <div class="card-ico purple" style="width:38px;height:38px;border-radius:9px">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:17px;height:17px">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <h3>المنتجات بالمخزن</h3>
                </div>
            </div>
            <span class="count-pill">{{ $products->count() }} منتج</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:48px">#</th>
                        <th>اسم المنتج</th>
                        <th>كود المنتج</th>
                        <th>الكمية</th>
                        <th>متوسط التكلفة</th>
                        <th>القيمة الإجمالية</th>
                        <th>الحد الأدنى</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $index => $item)
                    <tr>
                        <td style="color:var(--text-muted);font-size:.78rem;font-weight:600">
                            {{ $index + 1 }}
                        </td>
                        <td>
                            <p class="product-name">{{ $item->product?->name ?? '[محذوف]' }}</p>
                            @if($item->product && $item->product->category)
                                <p class="product-cat">{{ $item->product->category }}</p>
                            @endif
                        </td>
                        <td>
                            <span class="sku-badge">
                                {{ ($item->product?->code ?? $item->product?->sku) ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $qtyClass = $item->quantity <= 0
                                    ? 'zero'
                                    : ($item->quantity <= ($item->min_stock ?? 0) ? 'low' : 'ok');
                            @endphp
                            <span class="qty {{ $qtyClass }}">{{ number_format($item->quantity) }}</span>
                        </td>
                        <td style="font-weight:500">
                            {{ number_format($item->average_cost, 2) }}
                            <small style="color:var(--text-muted)">ج.م</small>
                        </td>
                        <td style="font-weight:700;color:var(--text-primary)">
                            {{ number_format($item->quantity * $item->average_cost, 2) }}
                            <small style="color:var(--text-muted)">ج.م</small>
                        </td>
                        <td style="font-weight:500">
                            {{ $item->min_stock ?? '—' }}
                        </td>
                        <td>
                            @if($item->quantity <= 0)
                                <span class="pill out-of-stock">
                                    <span class="pdot"></span> غير متوفر
                                </span>
                            @elseif($item->quantity <= ($item->min_stock ?? 0))
                                <span class="pill low-stock">
                                    <span class="pdot"></span> قريب من النفاد
                                </span>
                            @else
                                <span class="pill available">
                                    <span class="pdot"></span> متوفر
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-circle">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <h4>لا توجد منتجات في هذا المخزن</h4>
                                <p>لم يتم إضافة أي منتجات بعد</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    /* ── Animate stat numbers on load ── */
    const vals = document.querySelectorAll('.stat-val');
    vals.forEach(el => {
        const raw = el.textContent.replace(/[^\d.]/g, '');
        const num = parseFloat(raw);
        if (isNaN(num) || num === 0) return;

        const small = el.querySelector('small');
        const smallTxt = small ? small.outerHTML : '';
        const suffix   = el.textContent.replace(/[\d,.\s]/g, '').replace('ج.م','').trim();
        const hasDecimal = raw.includes('.');
        const duration = 700;
        const start = performance.now();

        function tick(now) {
            const p = Math.min((now - start) / duration, 1);
            const ease = 1 - Math.pow(1 - p, 3);
            const cur = num * ease;
            const fmt = hasDecimal
                ? cur.toLocaleString('ar-EG', {minimumFractionDigits: 0, maximumFractionDigits: 0})
                : Math.round(cur).toLocaleString('ar-EG');
            el.innerHTML = fmt + (small ? ' ' + smallTxt : '');
            if (p < 1) requestAnimationFrame(tick);
        }

        el.textContent = '0';
        setTimeout(() => requestAnimationFrame(tick), 400);
    });

    /* ── Table row reveal ── */
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((r, i) => {
        r.style.opacity = '0';
        r.style.transform = 'translateY(8px)';
        r.style.transition = 'opacity .3s ease, transform .3s ease';
        setTimeout(() => {
            r.style.opacity = '1';
            r.style.transform = 'none';
        }, 350 + i * 40);
    });
})();
</script>
@endpush

@endsection