@extends('layouts.app')

@section('title', 'تفاصيل العميل - ' . $customer->name)

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap');

    :root {
        --primary:       #1e40af;
        --primary-light: #3b82f6;
        --primary-soft:  #eff6ff;
        --accent:        #f59e0b;
        --accent-soft:   #fffbeb;
        --danger:        #ef4444;
        --danger-soft:   #fef2f2;
        --success:       #10b981;
        --success-soft:  #ecfdf5;
        --surface:       #ffffff;
        --surface-2:     #f8fafc;
        --border:        #e2e8f0;
        --text-primary:  #0f172a;
        --text-secondary:#475569;
        --text-muted:    #94a3b8;
        --shadow-sm:     0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
        --shadow-md:     0 4px 16px rgba(0,0,0,.08);
        --radius:        14px;
        --radius-sm:     8px;
        --radius-lg:     20px;
        --transition:    all .22s cubic-bezier(.4,0,.2,1);
    }

    * { font-family: 'Cairo', sans-serif; box-sizing: border-box; }
    body { background: #f1f5f9; color: var(--text-primary); }

    /* ═══ PAGE WRAPPER ═══ */
    .page-wrap { max-width: 860px; margin: 0 auto; padding-bottom: 40px; }

    /* ═══ PAGE HEADER ═══ */
    .page-header {
        background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 55%, #2563eb 100%);
        border-radius: var(--radius-lg);
        padding: 28px 32px 80px;
        margin-bottom: -56px;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(30,64,175,.35);
    }
    .page-header::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .breadcrumb-nav {
        display: flex; align-items: center; gap: 8px;
        font-size: .8rem; opacity: .75; margin-bottom: 18px;
    }
    .breadcrumb-nav a { color: white; text-decoration: none; }
    .breadcrumb-nav a:hover { opacity: 1; }
    .breadcrumb-sep { opacity: .5; }
    .header-inner { position: relative; display: flex; align-items: center; gap: 14px; }
    .header-badge {
        width: 52px; height: 52px;
        background: rgba(255,255,255,.15);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        border: 1px solid rgba(255,255,255,.25);
        flex-shrink: 0;
        backdrop-filter: blur(8px);
    }
    .page-header h1 { font-size: 1.45rem; font-weight: 800; margin: 0 0 4px; }
    .page-header .sub  { font-size: .875rem; opacity: .75; margin: 0; }
    .header-actions {
        position: absolute; top: 0; left: 0;
        display: flex; gap: 8px;
    }

    /* ═══ PROFILE CARD ═══ */
    .profile-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-md);
        padding: 0;
        overflow: hidden;
        margin-bottom: 20px;
        position: relative;
    }
    .profile-card-top {
        height: 56px;
        background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
    }
    .profile-card-body {
        padding: 0 28px 28px;
        display: flex;
        align-items: flex-start;
        gap: 24px;
    }
    .avatar-wrap {
        margin-top: -32px;
        flex-shrink: 0;
    }
    .avatar {
        width: 72px; height: 72px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.75rem; font-weight: 800; color: white;
        border: 4px solid white;
        box-shadow: 0 4px 16px rgba(30,64,175,.25);
    }
    .profile-info { flex: 1; padding-top: 14px; }
    .profile-name { font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin: 0 0 4px; }
    .profile-meta { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 8px; }
    .profile-tag {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: .78rem; color: var(--text-secondary);
        background: var(--surface-2); border: 1px solid var(--border);
        padding: 3px 10px; border-radius: 20px;
    }
    .status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: .78rem; font-weight: 700;
        padding: 3px 12px; border-radius: 20px;
    }
    .status-active   { background: var(--success-soft); color: #065f46; border: 1px solid #6ee7b7; }
    .status-inactive { background: #f1f5f9; color: var(--text-muted); border: 1px solid var(--border); }

    /* ═══ STATS ROW ═══ */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }
    @media(max-width: 640px) { .stats-row { grid-template-columns: 1fr 1fr; } }
    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 18px 20px;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        display: flex; align-items: center; gap: 14px;
    }
    .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .stat-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .stat-label { font-size: .75rem; color: var(--text-muted); font-weight: 600; margin-bottom: 2px; }
    .stat-value { font-size: 1.15rem; font-weight: 800; color: var(--text-primary); }
    .stat-value.green { color: var(--success); }
    .stat-value.blue  { color: var(--primary); }
    .stat-value.amber { color: #d97706; }

    /* ═══ DETAILS CARD ═══ */
    .detail-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px;
        overflow: hidden;
    }
    .detail-card-header {
        padding: 16px 22px;
        border-bottom: 1px solid var(--border);
        background: var(--surface-2);
        display: flex; align-items: center; gap: 10px;
    }
    .detail-card-header-icon {
        width: 34px; height: 34px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .detail-card-header h3 { font-size: .95rem; font-weight: 700; margin: 0; color: var(--text-primary); }

    .detail-list { padding: 0; margin: 0; list-style: none; }
    .detail-item {
        display: flex; align-items: flex-start;
        padding: 15px 22px;
        border-bottom: 1px solid var(--border);
        gap: 14px;
        transition: var(--transition);
    }
    .detail-item:last-child { border-bottom: none; }
    .detail-item:hover { background: var(--surface-2); }
    .detail-item-icon {
        width: 34px; height: 34px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .detail-item-label {
        font-size: .78rem; font-weight: 700;
        color: var(--text-muted);
        margin-bottom: 3px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .detail-item-value {
        font-size: .95rem; font-weight: 600;
        color: var(--text-primary);
        direction: rtl;
    }
    .detail-item-value.empty { color: var(--text-muted); font-weight: 400; font-style: italic; }
    .detail-item-value a { color: var(--primary-light); text-decoration: none; direction: ltr; display: inline-block; }
    .detail-item-value a:hover { text-decoration: underline; }

    /* ═══ ACTION BUTTONS ═══ */
    .action-row {
        display: flex; gap: 10px; flex-wrap: wrap;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 16px 22px;
        box-shadow: var(--shadow-sm);
    }
    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 20px;
        border-radius: var(--radius-sm);
        font-family: 'Cairo', sans-serif;
        font-size: .875rem; font-weight: 700;
        cursor: pointer; border: none;
        text-decoration: none;
        transition: var(--transition);
        white-space: nowrap;
    }
    .btn-primary {
        background: var(--primary); color: white;
        box-shadow: 0 4px 14px rgba(30,64,175,.3);
    }
    .btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(30,64,175,.4); }
    .btn-warning {
        background: #f59e0b; color: white;
        box-shadow: 0 4px 12px rgba(245,158,11,.3);
    }
    .btn-warning:hover { background: #d97706; transform: translateY(-1px); }
    .btn-outline {
        background: transparent; color: var(--text-secondary);
        border: 1.5px solid var(--border);
    }
    .btn-outline:hover { border-color: var(--primary-light); color: var(--primary); background: var(--primary-soft); }
    .btn-danger-outline {
        background: transparent; color: var(--danger);
        border: 1.5px solid #fca5a5;
        margin-right: auto;
    }
    .btn-danger-outline:hover { background: var(--danger-soft); }
</style>
@endpush

@section('content')
<div class="page-wrap">

    {{-- ══ HEADER ══ --}}
    <div class="page-header">
        <nav class="breadcrumb-nav">
            <a href="{{ route('customers.index') }}">العملاء</a>
            <span class="breadcrumb-sep">›</span>
            <span>{{ $customer->name }}</span>
        </nav>
        <div class="header-inner">
            <div class="header-badge">
                <svg width="24" height="24" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h1>{{ $customer->name }}</h1>
                <p class="sub">عميل منذ {{ $customer->created_at->format('Y') }} &nbsp;·&nbsp; #{{ $customer->id }}</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning" style="font-size:.8rem; padding:7px 16px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    تعديل
                </a>
            </div>
        </div>
    </div>

    {{-- ══ PROFILE CARD ══ --}}
    <div class="profile-card">
        <div class="profile-card-top"></div>
        <div class="profile-card-body">
            <div class="avatar-wrap">
                <div class="avatar">{{ mb_substr($customer->name, 0, 1) }}</div>
            </div>
            <div class="profile-info">
                <p class="profile-name">{{ $customer->name }}</p>
                <div class="profile-meta">
                    @if($customer->phone)
                        <span class="profile-tag">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $customer->phone }}
                        </span>
                    @endif
                    @if($customer->email)
                        <span class="profile-tag">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $customer->email }}
                        </span>
                    @endif
                    <span class="status-badge {{ $customer->is_active ?? true ? 'status-active' : 'status-inactive' }}">
                        <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                        {{ $customer->is_active ?? true ? 'عميل نشط' : 'غير نشط' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ STATS ROW ══ --}}
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff">
                <svg width="20" height="20" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <div class="stat-label">إجمالي الطلبات</div>
                <div class="stat-value blue">{{ $customer->orders_count ?? $customer->orders?->count() ?? '—' }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4">
                <svg width="20" height="20" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="stat-label">إجمالي المشتريات</div>
                <div class="stat-value green">{{ number_format($customer->total_purchases ?? 0, 2) }} ج.م</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fffbeb">
                <svg width="20" height="20" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <div class="stat-label">تاريخ التسجيل</div>
                <div class="stat-value amber" style="font-size:.95rem;">{{ $customer->created_at->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    {{-- ══ CONTACT INFO ══ --}}
    <div class="detail-card">
        <div class="detail-card-header">
            <div class="detail-card-header-icon" style="background:#eff6ff">
                <svg width="16" height="16" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3>بيانات التواصل</h3>
        </div>
        <ul class="detail-list">

            {{-- الاسم --}}
            <li class="detail-item">
                <div class="detail-item-icon" style="background:#eff6ff">
                    <svg width="16" height="16" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <div class="detail-item-label">اسم العميل</div>
                    <div class="detail-item-value">{{ $customer->name }}</div>
                </div>
            </li>

            {{-- الهاتف --}}
            <li class="detail-item">
                <div class="detail-item-icon" style="background:#f0fdf4">
                    <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <div>
                    <div class="detail-item-label">رقم الهاتف</div>
                    @if($customer->phone)
                        <div class="detail-item-value">
                            <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                        </div>
                    @else
                        <div class="detail-item-value empty">غير محدد</div>
                    @endif
                </div>
            </li>

            {{-- البريد --}}
            <li class="detail-item">
                <div class="detail-item-icon" style="background:#fdf4ff">
                    <svg width="16" height="16" fill="none" stroke="#9333ea" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="detail-item-label">البريد الإلكتروني</div>
                    @if($customer->email)
                        <div class="detail-item-value">
                            <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                        </div>
                    @else
                        <div class="detail-item-value empty">غير محدد</div>
                    @endif
                </div>
            </li>

            {{-- العنوان --}}
            <li class="detail-item">
                <div class="detail-item-icon" style="background:#fff7ed">
                    <svg width="16" height="16" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="detail-item-label">العنوان</div>
                    @if($customer->address)
                        <div class="detail-item-value">{{ $customer->address }}</div>
                    @else
                        <div class="detail-item-value empty">غير محدد</div>
                    @endif
                </div>
            </li>

            {{-- تاريخ التسجيل --}}
            <li class="detail-item">
                <div class="detail-item-icon" style="background:#f1f5f9">
                    <svg width="16" height="16" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="detail-item-label">تاريخ التسجيل</div>
                    <div class="detail-item-value">
                        {{ $customer->created_at->format('d/m/Y') }}
                        <span style="font-size:.8rem; color:var(--text-muted); font-weight:400; margin-right:6px;">
                            ({{ $customer->created_at->diffForHumans() }})
                        </span>
                    </div>
                </div>
            </li>

            {{-- ملاحظات إن وُجدت --}}
            @if($customer->notes)
            <li class="detail-item">
                <div class="detail-item-icon" style="background:#fffbeb">
                    <svg width="16" height="16" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </div>
                <div>
                    <div class="detail-item-label">ملاحظات</div>
                    <div class="detail-item-value" style="line-height:1.6;">{{ $customer->notes }}</div>
                </div>
            </li>
            @endif

        </ul>
    </div>

    {{-- ══ ACTION ROW ══ --}}
    <div class="action-row">
        <a href="{{ route('customers.index') }}" class="btn btn-outline">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            رجوع للقائمة
        </a>
        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            تعديل البيانات
        </a>
        @can('create', App\Models\Order::class)
        <a href="{{ route('orders.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            طلب جديد
        </a>
        @endcan
        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST"
              onsubmit="return confirm('هل أنت متأكد من حذف هذا العميل؟ لا يمكن التراجع عن هذا الإجراء.')"
              style="margin-right:auto;">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger-outline">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                حذف العميل
            </button>
        </form>
    </div>

</div>
@endsection