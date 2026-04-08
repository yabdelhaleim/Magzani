@extends('layouts.app')

@section('title', 'تعديل المنتج - ' . $product->name)
@section('page-title', 'تعديل المنتج')

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
        --border-focus:  #93c5fd;
        --text-primary:  #0f172a;
        --text-secondary:#475569;
        --text-muted:    #94a3b8;
        --shadow-sm:     0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
        --shadow-md:     0 4px 16px rgba(0,0,0,.08);
        --shadow-lg:     0 12px 40px rgba(0,0,0,.12);
        --radius:        14px;
        --radius-sm:     8px;
        --radius-lg:     20px;
        --transition:    all .2s cubic-bezier(.4,0,.2,1);
    }

    * { font-family: 'Cairo', sans-serif; box-sizing: border-box; }
    body { background: #f1f5f9; color: var(--text-primary); }

    /* ═══════════════════ PAGE HEADER ═══════════════════ */
    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 50%, #2563eb 100%);
        border-radius: var(--radius-lg);
        padding: 28px 32px;
        margin-bottom: 28px;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(30,64,175,.35);
    }
    .page-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .page-header-content { position: relative; display: flex; align-items: center; gap: 16px; }
    .page-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,.15);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,.2);
        flex-shrink: 0;
    }
    .page-header h1 { font-size: 1.5rem; font-weight: 800; margin: 0 0 4px; }
    .page-header p  { font-size: .875rem; opacity: .8; margin: 0; }
    .breadcrumb-nav {
        position: absolute; top: 20px; left: 32px;
        display: flex; align-items: center; gap: 8px;
        font-size: .8rem; opacity: .75;
    }
    .breadcrumb-nav a { color: white; text-decoration: none; }
    .breadcrumb-nav a:hover { opacity: 1; }
    .breadcrumb-sep { opacity: .5; }

    /* ═══════════════════ ALERTS ═══════════════════ */
    .alert {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 14px 18px;
        border-radius: var(--radius-sm);
        margin-bottom: 20px;
        border: 1px solid transparent;
        font-size: .9rem;
        animation: slideDown .3s ease;
    }
    .alert-success { background: var(--success-soft); border-color: #6ee7b7; color: #065f46; }
    .alert-danger   { background: var(--danger-soft);  border-color: #fca5a5; color: #991b1b; }
    .alert-icon { flex-shrink: 0; margin-top: 1px; }
    .alert ul { margin: 6px 0 0 16px; padding: 0; font-size: .85rem; }
    @keyframes slideDown { from { opacity:0; transform:translateY(-8px) } to { opacity:1; transform:none } }

    /* ═══════════════════ CARDS ═══════════════════ */
    .card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px;
        overflow: hidden;
    }
    .card-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: 12px;
        background: var(--surface-2);
    }
    .card-header-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .card-header h3 { font-size: 1rem; font-weight: 700; margin: 0; color: var(--text-primary); }
    .card-header p  { font-size: .8rem; color: var(--text-muted); margin: 2px 0 0; }
    .card-body { padding: 24px; }

    /* ═══════════════════ FORM CONTROLS ═══════════════════ */
    .form-group { margin-bottom: 20px; }
    .form-label {
        display: block;
        font-size: .85rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 7px;
    }
    .form-label .required { color: var(--danger); margin-right: 2px; }
    .form-label .hint { font-weight: 400; color: var(--text-muted); font-size: .78rem; }

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
    .form-control:hover  { border-color: var(--border-focus); }
    .form-control:focus  { border-color: var(--primary-light); box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    .form-control.is-invalid { border-color: var(--danger); background: var(--danger-soft); }
    .form-control.is-invalid:focus { box-shadow: 0 0 0 3px rgba(239,68,68,.12); }
    .invalid-feedback { font-size: .8rem; color: var(--danger); margin-top: 5px; display: flex; align-items: center; gap: 4px; }

    .input-group { position: relative; }
    .input-group .form-control { padding-left: 44px; }
    .input-group-append {
        position: absolute; left: 0; top: 0; bottom: 0;
        display: flex; align-items: center; justify-content: center;
        width: 42px;
        color: var(--text-muted);
        font-size: .85rem;
        border-right: 1.5px solid var(--border);
        pointer-events: none;
        border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    }

    .form-control[type="file"] {
        padding: 8px 14px;
        cursor: pointer;
        font-size: .85rem;
    }
    .form-control[type="file"]::-webkit-file-upload-button {
        background: var(--primary-soft);
        color: var(--primary);
        border: 1px solid var(--border-focus);
        border-radius: 6px;
        padding: 4px 12px;
        font-family: 'Cairo', sans-serif;
        font-size: .8rem;
        cursor: pointer;
        margin-left: 10px;
    }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
    @media(max-width: 768px) { .form-row, .form-row-3 { grid-template-columns: 1fr; } }

    /* ═══════════════════ TOGGLE SWITCH ═══════════════════ */
    .toggle-wrap { display: flex; align-items: center; gap: 12px; }
    .toggle { position: relative; width: 46px; height: 26px; flex-shrink: 0; }
    .toggle input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; inset: 0;
        background: #cbd5e1;
        border-radius: 26px;
        cursor: pointer;
        transition: var(--transition);
    }
    .toggle-slider::before {
        content: '';
        position: absolute;
        left: 3px; bottom: 3px;
        width: 20px; height: 20px;
        background: white;
        border-radius: 50%;
        transition: var(--transition);
        box-shadow: 0 1px 4px rgba(0,0,0,.2);
    }
    .toggle input:checked + .toggle-slider { background: var(--success); }
    .toggle input:checked + .toggle-slider::before { transform: translateX(20px); }
    .toggle-label { font-size: .9rem; font-weight: 600; color: var(--text-primary); cursor: pointer; }
    .toggle-desc { font-size: .8rem; color: var(--text-muted); }

    /* ═══════════════════ PRICE CALCULATOR ═══════════════════ */
    .price-calculator {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1.5px solid #86efac;
        border-radius: var(--radius);
        padding: 20px;
        margin-top: 4px;
    }
    .price-calculator-title {
        font-size: .85rem; font-weight: 700;
        color: #166534; margin-bottom: 14px;
        display: flex; align-items: center; gap: 6px;
    }
    .price-result-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
    @media(max-width: 640px) { .price-result-grid { grid-template-columns: 1fr; } }
    .price-result-item { text-align: center; }
    .price-result-item .label { font-size: .75rem; color: #374151; font-weight: 600; margin-bottom: 4px; }
    .price-result-item .value {
        font-size: 1.25rem; font-weight: 800;
        color: var(--primary);
        background: white; border-radius: 8px;
        padding: 8px 12px;
        border: 1px solid #d1fae5;
    }
    .price-result-item.profit .value { color: var(--success); }
    .price-result-item.selling .value {
        color: var(--primary);
        background: var(--primary-soft);
        border-color: var(--border-focus);
    }

    /* ═══════════════════ IMAGE PREVIEW ═══════════════════ */
    .image-preview-wrap {
        border: 2px dashed var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        transition: var(--transition);
        cursor: pointer;
        min-height: 180px;
        display: flex; align-items: center; justify-content: center;
        position: relative;
        background: var(--surface-2);
    }
    .image-preview-wrap:hover { border-color: var(--primary-light); background: var(--primary-soft); }
    .image-preview-wrap img { width: 100%; height: 100%; object-fit: cover; position: absolute; inset: 0; }
    .image-preview-placeholder { text-align: center; padding: 24px; }
    .image-preview-placeholder svg { opacity: .3; margin-bottom: 10px; }
    .image-preview-placeholder p { font-size: .85rem; color: var(--text-muted); margin: 0; }

    .current-image-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--success-soft); color: #065f46;
        border: 1px solid #6ee7b7;
        border-radius: 20px; padding: 4px 12px;
        font-size: .78rem; font-weight: 600; margin-top: 8px;
    }

    /* ═══════════════════ ACTION BAR ═══════════════════ */
    .action-bar {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 18px 24px;
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px;
        box-shadow: var(--shadow-md);
        position: sticky; bottom: 16px;
        z-index: 10;
    }
    .action-bar-left { display: flex; align-items: center; gap: 8px; font-size: .85rem; color: var(--text-muted); }

    .btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 22px; border-radius: var(--radius-sm);
        font-family: 'Cairo', sans-serif; font-size: .9rem; font-weight: 700;
        cursor: pointer; border: none; text-decoration: none;
        transition: var(--transition);
        white-space: nowrap;
    }
    .btn-primary {
        background: var(--primary);
        color: white;
        box-shadow: 0 4px 14px rgba(30,64,175,.35);
    }
    .btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(30,64,175,.4); }
    .btn-primary:active { transform: translateY(0); }
    .btn-outline {
        background: transparent; color: var(--text-secondary);
        border: 1.5px solid var(--border);
    }
    .btn-outline:hover { border-color: var(--primary-light); color: var(--primary); background: var(--primary-soft); }
    .btn-danger-outline {
        background: transparent; color: var(--danger);
        border: 1.5px solid #fca5a5;
    }
    .btn-danger-outline:hover { background: var(--danger-soft); }

    /* ═══════════════════ SECTION DIVIDER ═══════════════════ */
    .section-divider {
        display: flex; align-items: center; gap: 12px;
        margin: 6px 0 18px;
    }
    .section-divider span { font-size: .78rem; color: var(--text-muted); white-space: nowrap; font-weight: 600; }
    .section-divider::before, .section-divider::after {
        content: ''; flex: 1; height: 1px; background: var(--border);
    }

    /* ═══════════════════ BADGE ═══════════════════ */
    .badge {
        display: inline-flex; align-items: center;
        padding: 3px 10px; border-radius: 20px;
        font-size: .75rem; font-weight: 700;
    }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-blue    { background: var(--primary-soft); color: var(--primary); }

    /* ═══════════════════ LOADING SPINNER ═══════════════════ */
    .spinner {
        width: 18px; height: 18px;
        border: 2.5px solid rgba(255,255,255,.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg) } }

    /* ═══════════════════ PROFIT TYPE SELECTOR ═══════════════════ */
    .profit-type-selector { display: flex; border: 1.5px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; }
    .profit-type-option { flex: 1; }
    .profit-type-option input { display: none; }
    .profit-type-option label {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        padding: 9px 16px;
        font-size: .85rem; font-weight: 600;
        cursor: pointer; transition: var(--transition);
        color: var(--text-secondary);
        background: var(--surface-2);
        border-left: 1.5px solid var(--border);
    }
    .profit-type-option:first-child label { border-left: none; }
    .profit-type-option input:checked + label {
        background: var(--primary); color: white;
    }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto" x-data="productEditApp()" x-init="init()">

    {{-- ══════════════ PAGE HEADER ══════════════ --}}
    <div class="page-header">
        <nav class="breadcrumb-nav">
            <a href="{{ route('products.index') }}">المنتجات</a>
            <span class="breadcrumb-sep">›</span>
            <span>تعديل</span>
        </nav>
        <div class="page-header-content">
            <div class="page-header-icon">
                <svg width="24" height="24" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <h1>تعديل المنتج</h1>
                <p>{{ $product->name }} &nbsp;·&nbsp; كود: {{ $product->sku ?? 'غير محدد' }}</p>
            </div>
        </div>
    </div>

    {{-- ══════════════ ALERTS ══════════════ --}}
    @if(session('success'))
        <div class="alert alert-success">
            <div class="alert-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <div class="alert-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <div class="alert-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <strong>يوجد أخطاء في النموذج:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- ══════════════ FORM ══════════════ --}}
    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data"
          @submit.prevent="handleSubmit">
        @csrf
        @method('PUT')

        {{-- ┌─────────────────────────────────────┐ --}}
        {{-- │  SECTION 1 – المعلومات الأساسية      │ --}}
        {{-- └─────────────────────────────────────┘ --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon" style="background:#eff6ff">
                    <svg width="18" height="18" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h3>المعلومات الأساسية</h3>
                    <p>اسم المنتج والتصنيف والوصف</p>
                </div>
            </div>
            <div class="card-body">

                <div class="form-row">
                    {{-- اسم المنتج --}}
                    <div class="form-group">
                        <label class="form-label" for="name">
                            اسم المنتج <span class="required">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $product->name) }}"
                               placeholder="أدخل اسم المنتج"
                               required>
                        @error('name')
                            <div class="invalid-feedback">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- كود المنتج (SKU) --}}
                    <div class="form-group">
                        <label class="form-label" for="sku">
                            كود المنتج (SKU)
                            <span class="hint"> — اتركه فارغاً للتوليد التلقائي</span>
                        </label>
                        <input type="text"
                               name="sku"
                               id="sku"
                               class="form-control @error('sku') is-invalid @enderror"
                               value="{{ old('sku', $product->sku) }}"
                               placeholder="مثال: PROD-001"
                               dir="ltr">
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    {{-- التصنيف --}}
                    <div class="form-group">
                        <label class="form-label" for="category_id">
                            التصنيف <span class="required">*</span>
                        </label>
                        <select name="category_id"
                                id="category_id"
                                class="form-control @error('category_id') is-invalid @enderror"
                                required>
                            <option value="">— اختر التصنيف —</option>
                            @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- الوحدة الأساسية --}}
                    <div class="form-group">
                        <label class="form-label" for="base_unit">
                            الوحدة الأساسية <span class="required">*</span>
                        </label>
                        <select name="base_unit"
                                id="base_unit"
                                class="form-control @error('base_unit') is-invalid @enderror"
                                x-model="baseUnit"
                                @change="updateBaseUnitLabel()"
                                required>
                            <option value="">— اختر الوحدة —</option>
                            <option value="piece"   {{ old('base_unit', $product->basePricing?->base_unit ?? $product->base_unit) == 'piece'   ? 'selected' : '' }}>قطعة</option>
                            <option value="kg"      {{ old('base_unit', $product->basePricing?->base_unit ?? $product->base_unit) == 'kg'      ? 'selected' : '' }}>كيلوجرام</option>
                            <option value="liter"   {{ old('base_unit', $product->basePricing?->base_unit ?? $product->base_unit) == 'liter'   ? 'selected' : '' }}>لتر</option>
                            <option value="meter"   {{ old('base_unit', $product->basePricing?->base_unit ?? $product->base_unit) == 'meter'   ? 'selected' : '' }}>متر</option>
                            <option value="box"     {{ old('base_unit', $product->basePricing?->base_unit ?? $product->base_unit) == 'box'     ? 'selected' : '' }}>صندوق</option>
                            <option value="carton"  {{ old('base_unit', $product->basePricing?->base_unit ?? $product->base_unit) == 'carton'  ? 'selected' : '' }}>كرتون</option>
                            <option value="dozen"   {{ old('base_unit', $product->basePricing?->base_unit ?? $product->base_unit) == 'dozen'   ? 'selected' : '' }}>دستة</option>
                        </select>
                        @error('base_unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- الوصف --}}
                <div class="form-group">
                    <label class="form-label" for="description">الوصف</label>
                    <textarea name="description"
                              id="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="3"
                              placeholder="وصف مختصر للمنتج...">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- الحالة --}}
                <div class="toggle-wrap">
                    <label class="toggle">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <div>
                        <div class="toggle-label">المنتج نشط</div>
                        <div class="toggle-desc">يظهر في قوائم البيع والمخزون</div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ┌─────────────────────────────────────┐ --}}
        {{-- │  SECTION 2 – التسعير والأرباح        │ --}}
        {{-- └─────────────────────────────────────┘ --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon" style="background:#f0fdf4">
                    <svg width="18" height="18" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3>التسعير والأرباح</h3>
                    <p>سعر الشراء وهامش الربح وسعر البيع المحسوب</p>
                </div>
            </div>
            <div class="card-body">

                <div class="form-row">
                    {{-- سعر الشراء --}}
                    <div class="form-group">
                        <label class="form-label" for="base_purchase_price">
                            سعر الشراء <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number"
                                   name="base_purchase_price"
                                   id="base_purchase_price"
                                   class="form-control @error('base_purchase_price') is-invalid @enderror"
                                   x-model="basePurchasePrice"
                                   @input="calculatePrices()"
                                   value="{{ old('base_purchase_price', $product->basePricing?->base_purchase_price ?? $product->purchase_price ?? '') }}"
                                   step="0.01"
                                   min="0"
                                   placeholder="0.00"
                                   required>
                            <span class="input-group-append">ج.م</span>
                        </div>
                        @error('base_purchase_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- نوع هامش الربح --}}
                    <div class="form-group">
                        <label class="form-label">نوع هامش الربح <span class="required">*</span></label>
                        <div class="profit-type-selector">
                            <div class="profit-type-option">
                                <input type="radio" name="profit_type" id="pt_fixed" value="fixed"
                                       x-model="profitType" @change="calculatePrices()"
                                       {{ old('profit_type', $product->basePricing?->profit_type ?? 'fixed') === 'fixed' ? 'checked' : '' }}>
                                <label for="pt_fixed">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                                    مبلغ ثابت
                                </label>
                            </div>
                            <div class="profit-type-option">
                                <input type="radio" name="profit_type" id="pt_percent" value="percentage"
                                       x-model="profitType" @change="calculatePrices()"
                                       {{ old('profit_type', $product->basePricing?->profit_type ?? '') === 'percentage' ? 'checked' : '' }}>
                                <label for="pt_percent">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14L15 8M9 9h.01M15 14h.01"/><circle cx="12" cy="12" r="10"/></svg>
                                    نسبة مئوية
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- قيمة الربح --}}
                <div class="form-group">
                    <label class="form-label" for="profit_value">
                        قيمة الربح <span class="required">*</span>
                        <span class="hint" x-text="profitType === 'percentage' ? '— نسبة % من سعر الشراء' : '— مبلغ يُضاف لسعر الشراء'"></span>
                    </label>
                    <div class="input-group">
                        <input type="number"
                               name="profit_value"
                               id="profit_value"
                               class="form-control @error('profit_value') is-invalid @enderror"
                               x-model="profitValue"
                               @input="calculatePrices()"
                               value="{{ old('profit_value', $product->basePricing?->profit_value ?? 0) }}"
                               step="0.01"
                               min="0"
                               placeholder="0.00"
                               required>
                        <span class="input-group-append" x-text="profitType === 'percentage' ? '%' : 'ج.م'">ج.م</span>
                    </div>
                    @error('profit_value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Price Calculator Result --}}
                <div class="price-calculator" x-show="basePurchasePrice > 0">
                    <div class="price-calculator-title">
                        <svg width="16" height="16" fill="none" stroke="#166534" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        حساب الأسعار التلقائي
                    </div>
                    <div class="price-result-grid">
                        <div class="price-result-item">
                            <div class="label">سعر الشراء</div>
                            <div class="value" x-text="formatPrice(basePurchasePrice)">0.00 ج.م</div>
                        </div>
                        <div class="price-result-item profit">
                            <div class="label">الربح المحقق</div>
                            <div class="value" x-text="formatPrice(calculatedProfit)">0.00 ج.م</div>
                        </div>
                        <div class="price-result-item selling">
                            <div class="label">سعر البيع</div>
                            <div class="value" x-text="formatPrice(calculatedSellingPrice)">0.00 ج.م</div>
                        </div>
                    </div>
                    {{-- Hidden field to send selling price --}}
                    <input type="hidden" name="selling_price" :value="calculatedSellingPrice">
                    <input type="hidden" name="purchase_price" :value="basePurchasePrice">
                </div>

            </div>
        </div>

        {{-- ┌─────────────────────────────────────┐ --}}
        {{-- │  SECTION 3 – المخزون                │ --}}
        {{-- └─────────────────────────────────────┘ --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon" style="background:#fff7ed">
                    <svg width="18" height="18" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V11"/>
                    </svg>
                </div>
                <div>
                    <h3>المخزون</h3>
                    <p>الكمية الحالية والحد الأدنى للتنبيه</p>
                </div>
                <span class="badge badge-warning" style="margin-right: auto;">
                    الرصيد الحالي: {{ $product->stock_quantity ?? 0 }}
                </span>
            </div>
            <div class="card-body">
                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label" for="stock_quantity">الكمية في المخزون</label>
                        <input type="number"
                               name="stock_quantity"
                               id="stock_quantity"
                               class="form-control @error('stock_quantity') is-invalid @enderror"
                               value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}"
                               min="0"
                               step="1">
                        @error('stock_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="min_stock">
                            الحد الأدنى للمخزون
                            <span class="hint"> — للتنبيه</span>
                        </label>
                        <input type="number"
                               name="min_stock"
                               id="min_stock"
                               class="form-control @error('min_stock') is-invalid @enderror"
                               value="{{ old('min_stock', $product->min_stock ?? 0) }}"
                               min="0"
                               step="1">
                        @error('min_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="barcode">
                            الباركود
                            <span class="hint"> — اختياري</span>
                        </label>
                        <input type="text"
                               name="barcode"
                               id="barcode"
                               class="form-control @error('barcode') is-invalid @enderror"
                               value="{{ old('barcode', $product->barcode) }}"
                               placeholder="اسكن أو أدخل يدوياً"
                               dir="ltr">
                        @error('barcode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ┌─────────────────────────────────────┐ --}}
        {{-- │  SECTION 4 – الصورة                 │ --}}
        {{-- └─────────────────────────────────────┘ --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon" style="background:#fdf4ff">
                    <svg width="18" height="18" fill="none" stroke="#9333ea" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3>صورة المنتج</h3>
                    <p>JPG أو PNG، الحجم الأقصى 2MB</p>
                </div>
            </div>
            <div class="card-body">
                <div class="form-row" style="align-items: start;">

                    {{-- Preview Box --}}
                    <div>
                        <label class="form-label">معاينة الصورة</label>
                        <div class="image-preview-wrap" @click="$refs.imageInput.click()">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}"
                                     alt="{{ $product->name }}"
                                     x-ref="currentImg"
                                     id="currentProductImg">
                            @endif
                            <img x-ref="previewImg" id="previewImg" style="display:none; position:absolute; inset:0; width:100%; height:100%; object-fit:cover;">
                            <div class="image-preview-placeholder" x-show="!hasNewImage && !hasExistingImage">
                                <svg width="48" height="48" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p>انقر لاختيار صورة</p>
                            </div>
                        </div>

                        @if($product->image)
                            <div class="current-image-badge">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                                توجد صورة حالية
                            </div>
                        @endif
                    </div>

                    {{-- Upload Control --}}
                    <div>
                        <div class="form-group">
                            <label class="form-label" for="image">رفع صورة جديدة</label>
                            <input type="file"
                                   name="image"
                                   id="image"
                                   class="form-control @error('image') is-invalid @enderror"
                                   accept="image/jpeg,image/png,image/webp"
                                   x-ref="imageInput"
                                   @change="handleImageChange($event)">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <p style="font-size:.78rem; color:var(--text-muted); margin-top:6px;">
                                اترك الحقل فارغاً للاحتفاظ بالصورة الحالية
                            </p>
                        </div>

                        @if($product->image)
                            <div class="form-group">
                                <label class="toggle-wrap">
                                    <label class="toggle" style="flex-shrink:0">
                                        <input type="checkbox" name="remove_image" value="1"
                                               id="removeImage"
                                               @change="toggleRemoveImage($event)">
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <div>
                                        <div class="toggle-label" style="color:var(--danger)">حذف الصورة الحالية</div>
                                        <div class="toggle-desc">سيتم حذف الصورة نهائياً</div>
                                    </div>
                                </label>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        {{-- ══════════════ ACTION BAR ══════════════ --}}
        <div class="action-bar">
            <div class="action-bar-left">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>آخر تعديل: {{ $product->updated_at->diffForHumans() }}</span>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                <a href="{{ route('products.index') }}" class="btn btn-outline">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    إلغاء
                </a>
                <a href="{{ route('products.show', $product) }}" class="btn btn-outline">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    عرض
                </a>
                <button type="submit" class="btn btn-primary" :disabled="submitting">
                    <span x-show="submitting" class="spinner"></span>
                    <svg x-show="!submitting" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span x-text="submitting ? 'جاري الحفظ...' : 'حفظ التعديلات'">حفظ التعديلات</span>
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
function productEditApp() {
    return {
        // State
        baseUnit:              '{{ old("base_unit", $product->basePricing?->base_unit ?? $product->base_unit ?? "") }}',
        baseUnitLabel:         '',
        basePurchasePrice:     parseFloat('{{ old("base_purchase_price", $product->basePricing?->base_purchase_price ?? $product->purchase_price ?? 0) }}') || 0,
        profitType:            '{{ old("profit_type", $product->basePricing?->profit_type ?? "fixed") }}',
        profitValue:           parseFloat('{{ old("profit_value", $product->basePricing?->profit_value ?? 0) }}') || 0,
        calculatedSellingPrice: 0,
        calculatedProfit:      0,
        profitPercentage:      0,
        hasNewImage:           false,
        hasExistingImage:      {{ $product->image ? 'true' : 'false' }},
        submitting:            false,

        // Init
        init() {
            this.updateBaseUnitLabel();
            this.calculatePrices();
        },

        // Prices
        calculatePrices() {
            const purchase = parseFloat(this.basePurchasePrice) || 0;
            const profit   = parseFloat(this.profitValue) || 0;

            this.calculatedProfit = this.profitType === 'percentage'
                ? (purchase * profit) / 100
                : profit;

            this.calculatedSellingPrice = purchase + this.calculatedProfit;
            this.profitPercentage = purchase > 0
                ? (this.calculatedProfit / purchase) * 100
                : 0;
        },

        formatPrice(value) {
            return new Intl.NumberFormat('ar-EG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0) + ' ج.م';
        },

        // Image
        handleImageChange(event) {
            const file = event.target.files[0];
            if (!file) { this.hasNewImage = false; return; }

            const reader = new FileReader();
            reader.onload = (e) => {
                const preview = this.$refs.previewImg;
                preview.src = e.target.result;
                preview.style.display = 'block';

                if (this.$refs.currentImg)
                    this.$refs.currentImg.style.display = 'none';

                this.hasNewImage = true;
            };
            reader.readAsDataURL(file);
        },

        toggleRemoveImage(event) {
            if (this.$refs.currentImg) {
                this.$refs.currentImg.style.opacity = event.target.checked ? '0.3' : '1';
            }
        },

        updateBaseUnitLabel() {
            const select = document.querySelector('[name="base_unit"]');
            if (select?.selectedOptions[0])
                this.baseUnitLabel = select.selectedOptions[0].text;
        },

        // Validation
        validateForm() {
            if (!this.baseUnit) {
                alert('⚠️ يجب اختيار الوحدة الأساسية');
                return false;
            }
            if (this.basePurchasePrice <= 0) {
                alert('⚠️ يجب إدخال سعر شراء صحيح (أكبر من صفر)');
                return false;
            }
            if (this.profitValue < 0) {
                alert('⚠️ قيمة الربح لا يمكن أن تكون سالبة');
                return false;
            }
            return true;
        },

        // Submit
        handleSubmit(event) {
            if (!this.validateForm()) return;
            this.submitting = true;
            event.target.submit();
        }
    };
}
</script>
@endpush