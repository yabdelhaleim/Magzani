@extends('layouts.app')

@section('title', 'تعديل المخزن')
@section('page-title', 'تعديل مخزن')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;900&family=Tajawal:wght@300;400;500;700&display=swap');

    :root {
        --primary:        #1e40af;
        --primary-light:  #3b82f6;
        --primary-glow:   rgba(59,130,246,0.18);
        --accent:         #f59e0b;
        --accent-light:   #fbbf24;
        --surface:        #ffffff;
        --surface-2:      #f8faff;
        --surface-3:      #eef2ff;
        --border:         rgba(99,102,241,0.12);
        --border-hover:   rgba(99,102,241,0.32);
        --text-primary:   #0f172a;
        --text-secondary: #475569;
        --text-muted:     #94a3b8;
        --success:        #059669;
        --danger:         #dc2626;
        --shadow-sm:      0 2px 8px rgba(30,64,175,0.07);
        --shadow-md:      0 8px 32px rgba(30,64,175,0.12);
        --shadow-lg:      0 20px 60px rgba(30,64,175,0.16);
        --radius:         16px;
        --radius-sm:      10px;
        --transition:     cubic-bezier(0.4,0,0.2,1);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Cairo', 'Tajawal', sans-serif;
        background: linear-gradient(135deg, #f0f4ff 0%, #e8eeff 40%, #f5f0ff 100%);
        min-height: 100vh;
        direction: rtl;
    }

    /* ── Page Wrapper ── */
    .wh-page {
        max-width: 960px;
        margin: 0 auto;
        padding: 2rem 1.25rem 4rem;
        animation: pageFadeIn 0.5s var(--transition) both;
    }

    @keyframes pageFadeIn {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Header ── */
    .wh-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2.5rem;
        animation: slideDown 0.5s var(--transition) 0.05s both;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .back-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: 12px;
        color: var(--text-secondary);
        text-decoration: none;
        transition: all 0.22s var(--transition);
        box-shadow: var(--shadow-sm);
        flex-shrink: 0;
    }

    .back-btn:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: #fff;
        transform: translateX(3px);
        box-shadow: var(--shadow-md);
    }

    .wh-header-text h2 {
        font-size: clamp(1.35rem, 3vw, 1.75rem);
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.3;
        letter-spacing: -0.02em;
    }

    .wh-header-text p {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-top: 3px;
    }

    /* ── Progress Steps ── */
    .progress-bar {
        display: flex;
        gap: 6px;
        margin-bottom: 2rem;
        animation: slideDown 0.5s var(--transition) 0.1s both;
    }

    .progress-bar span {
        height: 4px;
        border-radius: 99px;
        flex: 1;
        background: var(--surface-3);
        transition: background 0.4s ease;
    }

    .progress-bar span.active { background: var(--primary-light); }
    .progress-bar span.done  { background: var(--primary); }

    /* ── Section Card ── */
    .wh-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1.5px solid var(--border);
        padding: 1.75rem 2rem;
        margin-bottom: 1.25rem;
        box-shadow: var(--shadow-sm);
        transition: box-shadow 0.3s var(--transition), border-color 0.3s var(--transition);
        animation: cardIn 0.55s var(--transition) both;
    }

    .wh-card:nth-child(1) { animation-delay: 0.12s; }
    .wh-card:nth-child(2) { animation-delay: 0.20s; }
    .wh-card:nth-child(3) { animation-delay: 0.28s; }
    .wh-card:nth-child(4) { animation-delay: 0.36s; }
    .wh-card:nth-child(5) { animation-delay: 0.44s; }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(20px) scale(0.985); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .wh-card:focus-within {
        border-color: var(--border-hover);
        box-shadow: var(--shadow-md), 0 0 0 4px var(--primary-glow);
    }

    /* ── Card Header ── */
    .card-head {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        margin-bottom: 1.75rem;
        padding-bottom: 1.25rem;
        border-bottom: 1.5px solid var(--border);
    }

    .card-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .card-icon svg { width: 20px; height: 20px; }

    .card-icon.blue   { background: #eff6ff; color: #1d4ed8; }
    .card-icon.green  { background: #f0fdf4; color: #15803d; }
    .card-icon.purple { background: #faf5ff; color: #7c3aed; }
    .card-icon.orange { background: #fff7ed; color: #c2410c; }

    .card-head-text h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .card-head-text p {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-top: 2px;
    }

    /* ── Grid ── */
    .field-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
    }

    .field-grid .col-full { grid-column: 1 / -1; }

    /* ── Floating Label Fields ── */
    .field-wrap {
        position: relative;
    }

    .field-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.45rem;
        transition: color 0.2s;
    }

    .field-label .req {
        color: var(--danger);
        margin-right: 2px;
        font-weight: 700;
    }

    .field-wrap:focus-within .field-label { color: var(--primary); }

    .field-inner {
        position: relative;
    }

    .field-ico {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        width: 18px;
        height: 18px;
        pointer-events: none;
        transition: color 0.2s;
    }

    .field-wrap:focus-within .field-ico { color: var(--primary-light); }

    .field-ctrl {
        width: 100%;
        padding: 0.78rem 2.75rem 0.78rem 1rem;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        font-family: 'Cairo', sans-serif;
        font-size: 0.9rem;
        color: var(--text-primary);
        background: var(--surface-2);
        transition: border-color 0.22s var(--transition),
                    box-shadow 0.22s var(--transition),
                    background 0.22s var(--transition);
        outline: none;
        direction: rtl;
        appearance: none;
        -webkit-appearance: none;
    }

    .field-ctrl:hover {
        border-color: var(--border-hover);
        background: var(--surface);
    }

    .field-ctrl:focus {
        border-color: var(--primary-light);
        background: var(--surface);
        box-shadow: 0 0 0 4px var(--primary-glow);
    }

    .field-ctrl.is-error { border-color: var(--danger); }
    .field-ctrl.is-error:focus { box-shadow: 0 0 0 4px rgba(220,38,38,0.12); }

    textarea.field-ctrl {
        padding-top: 0.85rem;
        resize: vertical;
        min-height: 110px;
    }

    select.field-ctrl {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: left 12px center;
        background-size: 16px;
        padding-left: 2.5rem;
    }

    .field-error {
        font-size: 0.76rem;
        color: var(--danger);
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 4px;
        animation: errShake 0.3s ease;
    }

    @keyframes errShake {
        0%,100% { transform: translateX(0); }
        25%      { transform: translateX(-4px); }
        75%      { transform: translateX(4px); }
    }

    /* ── Status Badge Options ── */
    .status-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .status-opt { display: none; }

    .status-lbl {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 0.55rem 1rem;
        border: 1.5px solid var(--border);
        border-radius: 99px;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.2s var(--transition);
        user-select: none;
        white-space: nowrap;
    }

    .status-lbl:hover { border-color: var(--border-hover); }

    .status-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        transition: transform 0.2s;
    }

    .status-opt[value="active"]      + .status-lbl .status-dot { background: #10b981; }
    .status-opt[value="inactive"]    + .status-lbl .status-dot { background: #f59e0b; }
    .status-opt[value="maintenance"] + .status-lbl .status-dot { background: #ef4444; }

    .status-opt[value="active"]:checked      + .status-lbl { border-color: #10b981; background: #ecfdf5; color: #065f46; }
    .status-opt[value="inactive"]:checked    + .status-lbl { border-color: #f59e0b; background: #fffbeb; color: #78350f; }
    .status-opt[value="maintenance"]:checked + .status-lbl { border-color: #ef4444; background: #fef2f2; color: #7f1d1d; }

    /* ── Action Bar ── */
    .action-bar {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        flex-wrap: wrap;
        margin-top: 0.5rem;
        animation: cardIn 0.55s var(--transition) 0.5s both;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0.8rem 1.75rem;
        border-radius: var(--radius-sm);
        font-family: 'Cairo', sans-serif;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        border: none;
        text-decoration: none;
        transition: all 0.22s var(--transition);
        position: relative;
        overflow: hidden;
        white-space: nowrap;
    }

    .btn::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.15);
        opacity: 0;
        transition: opacity 0.2s;
    }

    .btn:active { transform: scale(0.97); }
    .btn:active::after { opacity: 1; }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: #fff;
        box-shadow: 0 4px 14px rgba(30,64,175,0.38);
    }

    .btn-primary:hover {
        box-shadow: 0 6px 22px rgba(30,64,175,0.50);
        transform: translateY(-1px);
    }

    .btn-primary svg { width: 18px; height: 18px; }

    .btn-secondary {
        background: var(--surface);
        color: var(--text-secondary);
        border: 1.5px solid var(--border);
        box-shadow: var(--shadow-sm);
    }

    .btn-secondary:hover {
        background: var(--surface-3);
        border-color: var(--border-hover);
        color: var(--text-primary);
    }

    .btn-secondary svg { width: 16px; height: 16px; }

    /* Spinner inside submit button */
    .btn-primary .spinner {
        display: none;
        width: 18px;
        height: 18px;
        border: 2.5px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    .btn-primary.loading .btn-icon { display: none; }
    .btn-primary.loading .spinner  { display: block; }
    .btn-primary.loading            { pointer-events: none; opacity: 0.85; }

    /* ── Tooltip / hint ── */
    .field-hint {
        font-size: 0.76rem;
        color: var(--text-muted);
        margin-top: 5px;
    }

    /* ── Two Column Layout ── */
    .wh-layout {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1.5rem;
        align-items: start;
    }

    .wh-main { min-width: 0; }

    /* ── Sidebar ── */
    .wh-sidebar {
        position: sticky;
        top: 90px;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    /* ── Live Preview Card ── */
    .preview-card {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        border-radius: var(--radius);
        padding: 1.5rem;
        color: #f1f5f9;
        box-shadow: var(--shadow-lg);
        animation: cardIn 0.55s var(--transition) 0.15s both;
    }

    .preview-card .preview-title {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .preview-card .preview-title i {
        color: #6366f1;
    }

    .preview-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .preview-row:last-child { border-bottom: none; }

    .preview-label {
        font-size: 0.72rem;
        font-weight: 600;
        color: #64748b;
        min-width: 70px;
        flex-shrink: 0;
    }

    .preview-value {
        font-size: 0.85rem;
        font-weight: 700;
        color: #f1f5f9;
        word-break: break-word;
    }

    .preview-value.empty {
        color: #475569;
        font-style: italic;
        font-weight: 500;
    }

    .preview-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 2px 10px;
        border-radius: 50px;
        font-size: 0.72rem;
        font-weight: 700;
    }

    /* ── Tips Card ── */
    .tips-card {
        background: #fff;
        border-radius: var(--radius);
        border: 1.5px solid var(--border);
        padding: 1.25rem 1.5rem;
        box-shadow: var(--shadow-sm);
        animation: cardIn 0.55s var(--transition) 0.25s both;
    }

    .tips-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tips-title .tip-icon {
        width: 28px; height: 28px;
        border-radius: 8px;
        background: #fffbeb;
        color: #f59e0b;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px;
    }

    .tip-item {
        display: flex;
        gap: 10px;
        padding: 8px 0;
    }
    .tip-item + .tip-item {
        border-top: 1px solid #f1f5f9;
    }

    .tip-num {
        width: 22px; height: 22px;
        border-radius: 6px;
        background: #eff6ff;
        color: #3b82f6;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .tip-text {
        font-size: 0.78rem;
        color: var(--text-secondary);
        line-height: 1.5;
    }

    /* ── Responsive ── */
    @media (max-width: 900px) {
        .wh-layout {
            grid-template-columns: 1fr;
        }
        .wh-sidebar {
            position: static;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
    }

    @media (max-width: 640px) {
        .wh-sidebar {
            grid-template-columns: 1fr;
        }
        .wh-page       { padding: 1.25rem 0.875rem 3rem; }
        .wh-card       { padding: 1.25rem 1.1rem; }
        .field-grid    { grid-template-columns: 1fr; }
        .action-bar    { flex-direction: column; }
        .action-bar .btn { width: 100%; justify-content: center; }
        .btn           { padding: 0.85rem 1.25rem; }
        .status-group  { gap: 7px; }
        .status-lbl    { padding: 0.5rem 0.8rem; }
    }

    @media (max-width: 380px) {
        .card-icon  { width: 36px; height: 36px; border-radius: 9px; }
        .card-head-text h3 { font-size: 0.92rem; }
    }

    /* ── Decoration dots bg ── */
    .page-bg-dots {
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: -1;
        background-image:
            radial-gradient(circle, rgba(99,102,241,0.065) 1px, transparent 1px);
        background-size: 32px 32px;
    }
</style>
@endpush

@section('content')
<div class="page-bg-dots"></div>

<div class="wh-page">

    <!-- Header -->
    <div class="wh-header">
        <a href="{{ route('warehouses.show', $warehouse->id) }}" class="back-btn" title="رجوع">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="wh-header-text">
            <h2>تعديل مخزن: {{ $warehouse->name }}</h2>
            <p>قم بمراجعة وتحديث بيانات المخزن بدقة</p>
        </div>
    </div>

    <!-- Progress indicator -->
    <div class="progress-bar" id="progressBar">
        <span class="done"></span>
        <span class="done"></span>
        <span class="active"></span>
        <span></span>
    </div>

    <form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST" id="warehouseForm">
        @csrf
        @method('PUT')

        <div class="wh-layout">
            <!-- Main Form Column -->
            <div class="wh-main">

        <!-- ══ المعلومات الأساسية ══ -->
        <div class="wh-card" data-section="1">
            <div class="card-head">
                <div class="card-icon blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="card-head-text">
                    <h3>المعلومات الأساسية</h3>
                    <p>الاسم، الكود، المسؤول، والحالة</p>
                </div>
            </div>

            <div class="field-grid">
                <!-- اسم المخزن -->
                <div class="field-wrap">
                    <label class="field-label">اسم المخزن <span class="req">*</span></label>
                    <div class="field-inner">
                        <svg class="field-ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 7h18M3 12h18M3 17h18"/>
                        </svg>
                        <input type="text" name="name"
                               value="{{ old('name', $warehouse->name) }}"
                               class="field-ctrl @error('name') is-error @enderror"
                               placeholder="مثال: المخزن الرئيسي" required>
                    </div>
                    @error('name')
                        <p class="field-error">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- كود المخزن -->
                <div class="field-wrap">
                    <label class="field-label">كود المخزن <span class="req">*</span></label>
                    <div class="field-inner">
                        <svg class="field-ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <input type="text" name="code"
                               value="{{ old('code', $warehouse->code) }}"
                               class="field-ctrl @error('code') is-error @enderror"
                               placeholder="مثال: WH-001"
                               style="text-transform:uppercase; letter-spacing:0.06em;"
                               required>
                    </div>
                    @error('code')
                        <p class="field-error">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="field-hint">يُستخدم كمعرّف فريد للمخزن في النظام</p>
                </div>

                <!-- المسؤول -->
                <div class="field-wrap">
                    <label class="field-label">مسؤول المخزن</label>
                    <div class="field-inner">
                        <svg class="field-ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <select name="manager_id"
                                class="field-ctrl @error('manager_id') is-error @enderror">
                            <option value="">— اختر المسؤول —</option>
                            @foreach(\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}"
                                    {{ old('manager_id', $warehouse->manager_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('manager_id')
                        <p class="field-error">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- الحالة -->
                <div class="field-wrap">
                    <label class="field-label">حالة المخزن <span class="req">*</span></label>
                    <div class="status-group">
                        @php $currentStatus = old('status', $warehouse->status); @endphp

                        <input type="radio" name="status" value="active" id="s_active"
                               class="status-opt" {{ $currentStatus == 'active' ? 'checked' : '' }}>
                        <label for="s_active" class="status-lbl">
                            <span class="status-dot"></span> نشط
                        </label>

                        <input type="radio" name="status" value="inactive" id="s_inactive"
                               class="status-opt" {{ $currentStatus == 'inactive' ? 'checked' : '' }}>
                        <label for="s_inactive" class="status-lbl">
                            <span class="status-dot"></span> غير نشط
                        </label>

                        <input type="radio" name="status" value="maintenance" id="s_maint"
                               class="status-opt" {{ $currentStatus == 'maintenance' ? 'checked' : '' }}>
                        <label for="s_maint" class="status-lbl">
                            <span class="status-dot"></span> قيد الصيانة
                        </label>
                    </div>
                    @error('status')
                        <p class="field-error">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- ══ معلومات الموقع ══ -->
        <div class="wh-card" data-section="2">
            <div class="card-head">
                <div class="card-icon green">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="card-head-text">
                    <h3>معلومات الموقع</h3>
                    <p>المدينة، المنطقة، والعنوان التفصيلي</p>
                </div>
            </div>

            <div class="field-grid">
                <div class="field-wrap">
                    <label class="field-label">المدينة</label>
                    <div class="field-inner">
                        <svg class="field-ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <input type="text" name="city"
                               value="{{ old('city', $warehouse->city) }}"
                               class="field-ctrl @error('city') is-error @enderror"
                               placeholder="مثال: القاهرة">
                    </div>
                    @error('city')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field-wrap">
                    <label class="field-label">المنطقة</label>
                    <div class="field-inner">
                        <svg class="field-ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        <input type="text" name="area"
                               value="{{ old('area', $warehouse->area) }}"
                               class="field-ctrl @error('area') is-error @enderror"
                               placeholder="مثال: مدينة نصر">
                    </div>
                    @error('area')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field-wrap col-full">
                    <label class="field-label">العنوان التفصيلي</label>
                    <div class="field-inner">
                        <svg class="field-ico" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="top:14px;transform:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <input type="text" name="address"
                               value="{{ old('address', $warehouse->address) }}"
                               class="field-ctrl @error('address') is-error @enderror"
                               placeholder="مثال: شارع مصطفى النحاس، الحي الثامن، أمام البنك الأهلي">
                    </div>
                    @error('address')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- ══ معلومات الاتصال ══ -->
        <div class="wh-card" data-section="3">
            <div class="card-head">
                <div class="card-icon purple">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <div class="card-head-text">
                    <h3>معلومات الاتصال</h3>
                    <p>رقم الهاتف والبريد الإلكتروني</p>
                </div>
            </div>

            <div class="field-grid">
                <div class="field-wrap">
                    <label class="field-label">رقم الهاتف</label>
                    <div class="field-inner">
                        <svg class="field-ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <input type="tel" name="phone"
                               value="{{ old('phone', $warehouse->phone) }}"
                               class="field-ctrl @error('phone') is-error @enderror"
                               placeholder="01012345678"
                               dir="ltr" style="text-align:right;">
                    </div>
                    @error('phone')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field-wrap">
                    <label class="field-label">البريد الإلكتروني</label>
                    <div class="field-inner">
                        <svg class="field-ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <input type="email" name="email"
                               value="{{ old('email', $warehouse->email) }}"
                               class="field-ctrl @error('email') is-error @enderror"
                               placeholder="warehouse@company.com"
                               dir="ltr" style="text-align:right;">
                    </div>
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- ══ ملاحظات ══ -->
        <div class="wh-card" data-section="4">
            <div class="card-head">
                <div class="card-icon orange">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div class="card-head-text">
                    <h3>معلومات إضافية</h3>
                    <p>وصف ومميزات المخزن</p>
                </div>
            </div>

            <div class="field-wrap">
                <label class="field-label">الوصف والملاحظات</label>
                <textarea name="description" rows="4"
                          class="field-ctrl @error('description') is-error @enderror"
                          placeholder="أضف وصفاً تفصيلياً، مميزات خاصة، أو ملاحظات مهمة عن هذا المخزن...">{{ old('description', $warehouse->description) }}</textarea>
                @error('description')
                    <p class="field-error">{{ $message }}</p>
                @enderror
                <p class="field-hint" id="descCount" style="text-align:left; direction:ltr;">
                    <span id="descLen">{{ strlen(old('description', $warehouse->description ?? '')) }}</span> / 500
                </p>
            </div>
        </div>

        <!-- ══ أزرار الإجراءات ══ -->
        <div class="action-bar">
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <div class="spinner"></div>
                تحديث بيانات المخزن
            </button>

            <a href="{{ route('warehouses.show', $warehouse->id) }}" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                إلغاء
            </a>
        </div>

            </div><!-- /wh-main -->

            <!-- Sidebar -->
            <div class="wh-sidebar">

                <!-- Live Preview -->
                <div class="preview-card">
                    <div class="preview-title">
                        <i class="fas fa-eye"></i>
                        معاينة مباشرة
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">الاسم</span>
                        <span class="preview-value" id="pv-name">{{ old('name', $warehouse->name) }}</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">الكود</span>
                        <span class="preview-value" id="pv-code">{{ old('code', $warehouse->code) }}</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">المدينة</span>
                        <span class="preview-value" id="pv-city">{{ old('city', $warehouse->city) ?: '—' }}</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">المسؤول</span>
                        <span class="preview-value" id="pv-manager">
                            @php $mid = old('manager_id', $warehouse->manager_id); @endphp
                            {{ $mid ? (\App\Models\User::find($mid)?->name ?? '—') : '—' }}
                        </span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">الهاتف</span>
                        <span class="preview-value" id="pv-phone">{{ old('phone', $warehouse->phone) ?: '—' }}</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">الحالة</span>
                        <span id="pv-status">
                            @php $cs = old('status', $warehouse->status); @endphp
                            @if($cs == 'active')
                                <span class="preview-status" style="background:rgba(16,185,129,0.15);color:#34d399;">
                                    <i class="fas fa-check-circle"></i> نشط
                                </span>
                            @elseif($cs == 'maintenance')
                                <span class="preview-status" style="background:rgba(239,68,68,0.15);color:#fb7185;">
                                    <i class="fas fa-tools"></i> صيانة
                                </span>
                            @else
                                <span class="preview-status" style="background:rgba(245,158,11,0.15);color:#fbbf24;">
                                    <i class="fas fa-pause-circle"></i> غير نشط
                                </span>
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Tips -->
                <div class="tips-card">
                    <div class="tips-title">
                        <div class="tip-icon"><i class="fas fa-lightbulb"></i></div>
                        نصائح مفيدة
                    </div>
                    <div class="tip-item">
                        <div class="tip-num">1</div>
                        <div class="tip-text">استخدم اسماً واضحاً يميّز المخزن عن غيره</div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-num">2</div>
                        <div class="tip-text">الكود يجب أن يكون فريداً مثل WH-001</div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-num">3</div>
                        <div class="tip-text">أضف المدينة والعنوان لتسهيل التوصيل</div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-num">4</div>
                        <div class="tip-text">غيّر الحالة إلى "صيانة" أثناء التحديث</div>
                    </div>
                </div>

            </div><!-- /wh-sidebar -->
        </div><!-- /wh-layout -->
    </form>
</div>

@push('scripts')
<script>
(function () {
    /* ── Description counter ── */
    const desc = document.querySelector('[name="description"]');
    const len  = document.getElementById('descLen');
    if (desc && len) {
        desc.addEventListener('input', () => {
            const c = desc.value.length;
            len.textContent = c;
            len.style.color = c > 450 ? '#dc2626' : '';
        });
    }

    /* ── Submit loading ── */
    const form = document.getElementById('warehouseForm');
    const btn  = document.getElementById('submitBtn');
    if (form && btn) {
        form.addEventListener('submit', () => btn.classList.add('loading'));
    }

    /* ── Uppercase code on input ── */
    const code = document.querySelector('[name="code"]');
    if (code) {
        code.addEventListener('input', function () {
            const sel = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(sel, sel);
        });
    }

    /* ── Live Preview ── */
    function bindPreview(inputName, previewId, fallback) {
        const input = document.querySelector('[name="' + inputName + '"]');
        const el = document.getElementById(previewId);
        if (!input || !el) return;
        input.addEventListener('input', function() {
            const v = this.value.trim();
            el.textContent = v || fallback || '—';
            el.classList.toggle('empty', !v);
        });
    }

    bindPreview('name', 'pv-name', '—');
    bindPreview('code', 'pv-code', '—');
    bindPreview('city', 'pv-city', '—');
    bindPreview('phone', 'pv-phone', '—');

    // Manager select
    const mgrSelect = document.querySelector('[name="manager_id"]');
    const pvMgr = document.getElementById('pv-manager');
    if (mgrSelect && pvMgr) {
        mgrSelect.addEventListener('change', function() {
            pvMgr.textContent = this.options[this.selectedIndex]?.text || '—';
            pvMgr.classList.toggle('empty', !this.value);
        });
    }

    // Status radios
    document.querySelectorAll('input[name="status"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const el = document.getElementById('pv-status');
            if (!el) return;
            const map = {
                active: { icon: 'fa-check-circle', label: 'نشط', bg: 'rgba(16,185,129,0.15)', color: '#34d399' },
                inactive: { icon: 'fa-pause-circle', label: 'غير نشط', bg: 'rgba(245,158,11,0.15)', color: '#fbbf24' },
                maintenance: { icon: 'fa-tools', label: 'صيانة', bg: 'rgba(239,68,68,0.15)', color: '#fb7185' }
            };
            const s = map[this.value] || map.inactive;
            el.innerHTML = '<span class="preview-status" style="background:' + s.bg + ';color:' + s.color + ';"><i class="fas ' + s.icon + '"></i> ' + s.label + '</span>';
        });
    });

    /* ── Intersection observer: subtle reveal ── */
    if ('IntersectionObserver' in window) {
        const cards = document.querySelectorAll('.wh-card');
        const io = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.style.opacity = 1;
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.08 });
        cards.forEach(c => io.observe(c));
    }
})();
</script>
@endpush

@endsection