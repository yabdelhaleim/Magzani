@extends('layouts.app')

@section('title', 'تعديل بيانات العميل - ' . $customer->name)

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap');

    :root {
        --primary:       #1e40af;
        --primary-light: #3b82f6;
        --primary-soft:  #eff6ff;
        --danger:        #ef4444;
        --danger-soft:   #fef2f2;
        --success:       #10b981;
        --success-soft:  #ecfdf5;
        --surface:       #ffffff;
        --surface-2:     #f8fafc;
        --border:        #e2e8f0;
        --border-focus:  #93c5fd;
        --text-primary:  #0f172a;
        --text-secondary:#475569;
        --text-muted:    #94a3b8;
        --shadow-sm:     0 1px 3px rgba(0,0,0,.06);
        --shadow-md:     0 4px 16px rgba(0,0,0,.08);
        --radius:        14px;
        --radius-sm:     8px;
        --radius-lg:     20px;
        --transition:    all .22s cubic-bezier(.4,0,.2,1);
    }

    * { font-family: 'Cairo', sans-serif; box-sizing: border-box; }
    body { background: #f1f5f9; color: var(--text-primary); }

    .page-wrap { max-width: 720px; margin: 0 auto; padding-bottom: 40px; }

    /* ═══ HEADER ═══ */
    .page-header {
        background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 55%, #2563eb 100%);
        border-radius: var(--radius-lg);
        padding: 28px 32px;
        margin-bottom: 24px;
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
        font-size: .8rem; opacity: .75; margin-bottom: 16px;
        position: relative;
    }
    .breadcrumb-nav a { color: white; text-decoration: none; }
    .breadcrumb-nav a:hover { opacity: 1; }
    .breadcrumb-sep { opacity: .5; }
    .header-inner { position: relative; display: flex; align-items: center; gap: 14px; }
    .header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,.15);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        border: 1px solid rgba(255,255,255,.25);
        backdrop-filter: blur(8px);
        flex-shrink: 0;
    }
    .page-header h1 { font-size: 1.4rem; font-weight: 800; margin: 0 0 4px; }
    .page-header p  { font-size: .875rem; opacity: .75; margin: 0; }

    /* ═══ ALERT ═══ */
    .alert {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 14px 18px;
        border-radius: var(--radius-sm);
        margin-bottom: 20px;
        border: 1px solid transparent;
        animation: slideDown .3s ease;
    }
    .alert-danger { background: var(--danger-soft); border-color: #fca5a5; color: #991b1b; }
    .alert-icon { flex-shrink: 0; margin-top: 1px; }
    .alert ul { margin: 6px 0 0 16px; padding: 0; font-size: .85rem; }
    .alert ul li { margin-bottom: 3px; }
    @keyframes slideDown { from { opacity:0; transform:translateY(-8px) } to { opacity:1; transform:none } }

    /* ═══ CARD ═══ */
    .card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px;
        overflow: hidden;
    }
    .card-header {
        padding: 16px 22px;
        border-bottom: 1px solid var(--border);
        background: var(--surface-2);
        display: flex; align-items: center; gap: 10px;
    }
    .card-header-icon {
        width: 34px; height: 34px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .card-header h3 { font-size: .95rem; font-weight: 700; margin: 0; color: var(--text-primary); }
    .card-header p  { font-size: .78rem; color: var(--text-muted); margin: 2px 0 0; }
    .card-body { padding: 22px 24px; }

    /* ═══ FORM ═══ */
    .form-group { margin-bottom: 20px; }
    .form-group:last-child { margin-bottom: 0; }

    .form-label {
        display: flex; align-items: center; gap: 8px;
        font-size: .85rem; font-weight: 700;
        color: var(--text-secondary);
        margin-bottom: 8px;
    }
    .form-label-icon {
        width: 26px; height: 26px; border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .form-label .required { color: var(--danger); }
    .form-label .optional {
        font-size: .72rem; font-weight: 400; color: var(--text-muted);
        background: var(--surface-2); border: 1px solid var(--border);
        padding: 1px 7px; border-radius: 10px;
        margin-right: auto;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        font-size: .9rem;
        font-family: 'Cairo', sans-serif;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--surface);
        color: var(--text-primary);
        transition: var(--transition);
        outline: none;
        direction: rtl;
    }
    .form-control::placeholder { color: var(--text-muted); }
    .form-control:hover  { border-color: var(--border-focus); }
    .form-control:focus  { border-color: var(--primary-light); box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    .form-control.is-invalid { border-color: var(--danger); background: #fff8f8; }
    .form-control.is-invalid:focus { box-shadow: 0 0 0 3px rgba(239,68,68,.1); }
    .invalid-msg { font-size: .8rem; color: var(--danger); margin-top: 5px; display: flex; align-items: center; gap: 4px; }

    /* phone/email ltr */
    input[name="phone"], input[name="email"] { direction: ltr; text-align: right; }

    /* ═══ TOGGLE ═══ */
    .toggle-wrap { display: flex; align-items: center; gap: 12px; padding: 14px 0 4px; }
    .toggle { position: relative; width: 46px; height: 26px; flex-shrink: 0; }
    .toggle input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; inset: 0;
        background: #cbd5e1; border-radius: 26px;
        cursor: pointer; transition: var(--transition);
    }
    .toggle-slider::before {
        content: ''; position: absolute;
        left: 3px; bottom: 3px;
        width: 20px; height: 20px;
        background: white; border-radius: 50%;
        transition: var(--transition);
        box-shadow: 0 1px 4px rgba(0,0,0,.2);
    }
    .toggle input:checked + .toggle-slider { background: var(--success); }
    .toggle input:checked + .toggle-slider::before { transform: translateX(20px); }
    .toggle-label { font-size: .9rem; font-weight: 600; color: var(--text-primary); }
    .toggle-desc  { font-size: .78rem; color: var(--text-muted); }

    /* ═══ CUSTOMER MINI-BADGE ═══ */
    .customer-badge {
        display: flex; align-items: center; gap: 12px;
        background: var(--primary-soft);
        border: 1px solid var(--border-focus);
        border-radius: var(--radius);
        padding: 14px 18px;
        margin-bottom: 20px;
    }
    .customer-badge-avatar {
        width: 44px; height: 44px; border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; font-weight: 800; color: white;
        flex-shrink: 0;
    }
    .customer-badge-name  { font-size: .95rem; font-weight: 700; color: var(--primary); }
    .customer-badge-meta  { font-size: .78rem; color: var(--text-muted); margin-top: 2px; }

    /* ═══ ACTION BAR ═══ */
    .action-bar {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 16px 22px;
        display: flex; align-items: center; justify-content: space-between;
        gap: 10px; flex-wrap: wrap;
        box-shadow: var(--shadow-md);
        position: sticky; bottom: 16px; z-index: 10;
    }
    .action-bar-info { display: flex; align-items: center; gap: 7px; font-size: .82rem; color: var(--text-muted); }
    .action-bar-btns { display: flex; gap: 9px; }

    /* ═══ BUTTONS ═══ */
    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 20px; border-radius: var(--radius-sm);
        font-family: 'Cairo', sans-serif;
        font-size: .875rem; font-weight: 700;
        cursor: pointer; border: none;
        text-decoration: none; transition: var(--transition);
        white-space: nowrap;
    }
    .btn-primary {
        background: var(--primary); color: white;
        box-shadow: 0 4px 14px rgba(30,64,175,.3);
    }
    .btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(30,64,175,.4); }
    .btn-primary:disabled { opacity: .7; cursor: not-allowed; transform: none; }
    .btn-outline {
        background: transparent; color: var(--text-secondary);
        border: 1.5px solid var(--border);
    }
    .btn-outline:hover { border-color: var(--border-focus); color: var(--primary); background: var(--primary-soft); }

    /* ═══ DIVIDER ═══ */
    .form-divider {
        height: 1px; background: var(--border);
        margin: 20px 0;
    }

    /* ═══ SPINNER ═══ */
    .spinner {
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,.35);
        border-top-color: white;
        border-radius: 50%;
        animation: spin .7s linear infinite;
        flex-shrink: 0;
    }
    @keyframes spin { to { transform: rotate(360deg) } }
</style>
@endpush

@section('content')
<div class="page-wrap" x-data="{ submitting: false }">

    {{-- ══ PAGE HEADER ══ --}}
    <div class="page-header">
        <nav class="breadcrumb-nav">
            <a href="{{ route('customers.index') }}">العملاء</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('customers.show', $customer->id) }}">{{ $customer->name }}</a>
            <span class="breadcrumb-sep">›</span>
            <span>تعديل</span>
        </nav>
        <div class="header-inner">
            <div class="header-icon">
                <svg width="24" height="24" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <h1>تعديل بيانات العميل</h1>
                <p>آخر تحديث: {{ $customer->updated_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>

    {{-- ══ ERRORS ══ --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <div class="alert-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <strong>يرجى تصحيح الأخطاء التالية:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- ══ CURRENT CUSTOMER BADGE ══ --}}
    <div class="customer-badge">
        <div class="customer-badge-avatar">{{ mb_substr($customer->name, 0, 1) }}</div>
        <div>
            <div class="customer-badge-name">{{ $customer->name }}</div>
            <div class="customer-badge-meta">
                عميل #{{ $customer->id }}
                @if($customer->phone) &nbsp;·&nbsp; {{ $customer->phone }} @endif
            </div>
        </div>
    </div>

    {{-- ══ FORM ══ --}}
    <form action="{{ route('customers.update', $customer->id) }}" method="POST"
          @submit.prevent="submitting = true; $el.submit()">
        @csrf
        @method('PUT')

        {{-- ┌────────────────────────────┐ --}}
        {{-- │  بيانات التعريف           │ --}}
        {{-- └────────────────────────────┘ --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon" style="background:#eff6ff">
                    <svg width="16" height="16" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3>البيانات الشخصية</h3>
                    <p>الاسم والحالة</p>
                </div>
            </div>
            <div class="card-body">

                {{-- الاسم --}}
                <div class="form-group">
                    <label class="form-label" for="name">
                        <span class="form-label-icon" style="background:#eff6ff">
                            <svg width="14" height="14" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        اسم العميل
                        <span class="required">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $customer->name) }}"
                           placeholder="الاسم الكامل للعميل"
                           required>
                    @error('name')
                        <div class="invalid-msg">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- حالة العميل --}}
                <div class="form-divider"></div>
                <div class="toggle-wrap">
                    <label class="toggle">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $customer->is_active ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <div>
                        <div class="toggle-label">حساب العميل نشط</div>
                        <div class="toggle-desc">يسمح بإنشاء طلبات جديدة لهذا العميل</div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ┌────────────────────────────┐ --}}
        {{-- │  بيانات التواصل           │ --}}
        {{-- └────────────────────────────┘ --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon" style="background:#f0fdf4">
                    <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <div>
                    <h3>بيانات التواصل</h3>
                    <p>الهاتف والبريد والعنوان</p>
                </div>
            </div>
            <div class="card-body">

                {{-- رقم الهاتف --}}
                <div class="form-group">
                    <label class="form-label" for="phone">
                        <span class="form-label-icon" style="background:#f0fdf4">
                            <svg width="14" height="14" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </span>
                        رقم الهاتف
                        <span class="optional">اختياري</span>
                    </label>
                    <input type="text"
                           name="phone"
                           id="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $customer->phone) }}"
                           placeholder="01xxxxxxxxx"
                           dir="ltr"
                           style="text-align:right">
                    @error('phone')
                        <div class="invalid-msg">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- البريد الإلكتروني --}}
                <div class="form-group">
                    <label class="form-label" for="email">
                        <span class="form-label-icon" style="background:#fdf4ff">
                            <svg width="14" height="14" fill="none" stroke="#9333ea" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        البريد الإلكتروني
                        <span class="optional">اختياري</span>
                    </label>
                    <input type="email"
                           name="email"
                           id="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $customer->email) }}"
                           placeholder="example@mail.com"
                           dir="ltr"
                           style="text-align:right">
                    @error('email')
                        <div class="invalid-msg">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- العنوان --}}
                <div class="form-group">
                    <label class="form-label" for="address">
                        <span class="form-label-icon" style="background:#fff7ed">
                            <svg width="14" height="14" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </span>
                        العنوان
                        <span class="optional">اختياري</span>
                    </label>
                    <textarea name="address"
                              id="address"
                              class="form-control @error('address') is-invalid @enderror"
                              rows="3"
                              placeholder="المدينة، الحي، الشارع...">{{ old('address', $customer->address) }}</textarea>
                    @error('address')
                        <div class="invalid-msg">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

            </div>
        </div>

        {{-- ┌────────────────────────────┐ --}}
        {{-- │  ملاحظات                  │ --}}
        {{-- └────────────────────────────┘ --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon" style="background:#fffbeb">
                    <svg width="16" height="16" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </div>
                <div>
                    <h3>ملاحظات</h3>
                    <p>أي معلومات إضافية عن العميل</p>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label" for="notes">
                        <span class="form-label-icon" style="background:#fffbeb">
                            <svg width="14" height="14" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </span>
                        ملاحظات داخلية
                        <span class="optional">اختياري</span>
                    </label>
                    <textarea name="notes"
                              id="notes"
                              class="form-control @error('notes') is-invalid @enderror"
                              rows="3"
                              placeholder="مثال: عميل VIP، يفضل التواصل مساءً...">{{ old('notes', $customer->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-msg">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ══ ACTION BAR ══ --}}
        <div class="action-bar">
            <div class="action-bar-info">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                آخر تعديل: {{ $customer->updated_at->diffForHumans() }}
            </div>
            <div class="action-bar-btns">
                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-outline">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    إلغاء
                </a>
                <button type="submit" class="btn btn-primary" :disabled="submitting">
                    <span x-show="submitting" class="spinner"></span>
                    <svg x-show="!submitting" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span x-text="submitting ? 'جاري الحفظ...' : 'حفظ التعديلات'">حفظ التعديلات</span>
                </button>
            </div>
        </div>

    </form>
</div>
@endsection