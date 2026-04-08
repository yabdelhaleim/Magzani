@extends('layouts.app')

@section('title', 'التحديث الذكي للأسعار')
@section('page-title', 'التحديث الذكي للأسعار')

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

        --tf-shadow-sm:   0 2px 12px rgba(79,99,210,0.07);
        --tf-shadow-card: 0 2px 0 0 rgba(0,0,0,0.04), 0 4px 20px rgba(79,99,210,0.08);
        --tf-shadow-lg:   0 8px 30px rgba(79,99,210,0.10);
    }

    .tf-page {
        background: var(--tf-bg);
        background-image:
            radial-gradient(ellipse 80% 60% at 10% -10%,  rgba(79,99,210,0.12) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(58,142,240,0.1) 0%, transparent 50%);
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
    @keyframes btnGlow {
        0%,100% { box-shadow: 0 0 0 rgba(58,142,240,0); }
        50%     { box-shadow: 0 0 20px rgba(58,142,240,0.4); }
    }

    .tf-section { animation: tfFadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .tf-section:nth-child(1) { animation-delay: 0.04s; }
    .tf-section:nth-child(2) { animation-delay: 0.13s; }
    .tf-section:nth-child(3) { animation-delay: 0.22s; }
    .tf-section:nth-child(4) { animation-delay: 0.31s; }

    /* ── Alerts ── */
    .tf-alert {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px; border-radius: 16px; margin-bottom: 20px;
        animation: tfFadeUp 0.4s ease both; border: 1px solid;
    }
    .tf-alert-success { background: var(--tf-green-soft); border-color: rgba(15,170,126,0.2); }
    .tf-alert-success i { color: var(--tf-green); }
    .tf-alert-error { background: var(--tf-red-soft); border-color: rgba(232,75,90,0.2); }
    .tf-alert-error i { color: var(--tf-red); }
    .tf-alert-content { display: flex; align-items: center; gap: 12px; flex: 1; }
    .tf-alert-text { font-size: 14px; font-weight: 700; }
    .tf-alert-success .tf-alert-text { color: #065f46; }
    .tf-alert-error .tf-alert-text { color: #991b1b; }
    .tf-alert-close { cursor: pointer; opacity: 0.6; transition: opacity .2s; }
    .tf-alert-close:hover { opacity: 1; }

    /* ── Header ── */
    .tf-header {
        background: linear-gradient(135deg, var(--tf-indigo), var(--tf-violet));
        border-radius: 20px; padding: 28px; margin-bottom: 22px;
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: wrap; gap: 16px; box-shadow: var(--tf-shadow-lg);
    }
    .tf-header-title { font-size: 26px; font-weight: 900; color: var(--tf-surface); margin-bottom: 6px; display: flex; align-items: center; gap: 10px; }
    .tf-header-subtitle { font-size: 14px; color: rgba(255,255,255,0.8); font-weight: 600; }
    .tf-header-features { font-size: 12px; color: rgba(255,255,255,0.7); margin-top: 8px; }

    .tf-btn-back {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 20px; border-radius: 14px;
        background: var(--tf-surface); color: var(--tf-indigo);
        border: none; font-size: 13px; font-weight: 800;
        font-family: 'Cairo', sans-serif; text-decoration: none;
        box-shadow: var(--tf-shadow-sm); transition: all .25s;
    }
    .tf-btn-back:hover { transform: scale(1.05); }

    /* ── Cards ── */
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
        padding: 18px 24px; border-bottom: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2); flex-wrap: wrap; gap: 12px;
    }
    .tf-card-title {
        display: flex; align-items: center; gap: 12px;
    }
    .tf-card-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        transition: transform .4s cubic-bezier(.34,1.56,.64,1);
    }
    .tf-card:hover .tf-card-icon { animation: iconBounce .6s ease; }
    .tf-card.blue .tf-card-icon   { background: var(--tf-blue-soft);   color: var(--tf-blue); }
    .tf-card.green .tf-card-icon  { background: var(--tf-green-soft);  color: var(--tf-green); }
    .tf-card.violet .tf-card-icon { background: var(--tf-violet-soft); color: var(--tf-violet); }
    .tf-card.amber .tf-card-icon  { background: var(--tf-amber-soft);  color: var(--tf-amber); }

    .tf-card-title-text { font-size: 17px; font-weight: 800; color: var(--tf-text-h); }
    .tf-card-title-sub { font-size: 11px; color: var(--tf-text-m); font-weight: 600; }

    .tf-card-body { padding: 24px; }

    /* ── Step Badge ── */
    .tf-step-number {
        width: 48px; height: 48px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; font-weight: 900;
    }
    .tf-card.blue .tf-step-number   { background: var(--tf-blue);   color: var(--tf-surface); }
    .tf-card.green .tf-step-number  { background: var(--tf-green);  color: var(--tf-surface); }
    .tf-card.violet .tf-step-number { background: var(--tf-violet); color: var(--tf-surface); }

    /* ── Form Controls ── */
    .tf-label {
        display: block; font-size: 13px; font-weight: 800;
        color: var(--tf-text-b); margin-bottom: 8px;
        display: flex; align-items: center; gap: 6px;
    }
    .tf-label .req { color: var(--tf-red); }
    .tf-label .opt { font-weight: 600; color: var(--tf-text-m); font-size: 11px; }

    .tf-input, .tf-select {
        width: 100%; padding: 12px 16px;
        border: 1.5px solid var(--tf-border); border-radius: 14px;
        font-size: 14px; font-family: 'Cairo', sans-serif;
        color: var(--tf-text-h); background: var(--tf-surface);
        transition: all .25s; outline: none;
    }
    .tf-input:focus, .tf-select:focus {
        border-color: var(--tf-blue);
        box-shadow: 0 0 0 3px rgba(58,142,240,0.12);
    }
    .tf-select { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%237e90b0'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: left 14px center; background-size: 16px; }

    .tf-hint {
        display: flex; align-items: flex-start; gap: 8px;
        padding: 10px 14px; border-radius: 10px; margin-top: 10px;
        font-size: 12px; font-weight: 600;
    }
    .tf-hint-blue { background: var(--tf-blue-soft); color: var(--tf-blue); }
    .tf-hint-green { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-hint-orange { background: var(--tf-amber-soft); color: var(--tf-amber); }

    /* ── Buttons ── */
    .tf-btn-primary {
        width: 100%; padding: 16px; border-radius: 16px;
        background: linear-gradient(135deg, var(--tf-green), #0d8a6e);
        color: var(--tf-surface); border: none;
        font-size: 15px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif;
        box-shadow: 0 4px 16px rgba(15,170,126,0.35);
        transition: all .3s ease; display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .tf-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15,170,126,0.45); }
    .tf-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

    .tf-btn-secondary {
        padding: 10px 20px; border-radius: 12px;
        background: var(--tf-surface); color: var(--tf-text-b);
        border: 1.5px solid var(--tf-border);
        font-size: 13px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif;
        transition: all .25s;
    }
    .tf-btn-secondary:hover { background: var(--tf-surface2); }

    .tf-btn-accent {
        padding: 14px 32px; border-radius: 16px;
        background: linear-gradient(135deg, var(--tf-indigo), var(--tf-violet));
        color: var(--tf-surface); border: none;
        font-size: 15px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif;
        box-shadow: 0 6px 24px rgba(79,99,210,0.4);
        transition: all .3s ease;
    }
    .tf-btn-accent:hover { transform: translateY(-2px); box-shadow: 0 10px 32px rgba(79,99,210,0.5); }
    .tf-btn-accent:disabled { opacity: 0.5; cursor: not-allowed; }

    /* ── Grid ── */
    .tf-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    @media (max-width: 768px) { .tf-grid { grid-template-columns: 1fr; } }

    /* ── Table ── */
    .tf-table-wrapper { overflow-x: auto; border-radius: 14px; border: 1px solid var(--tf-border); }
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

    .tf-checkbox {
        width: 20px; height: 20px; cursor: pointer;
    }

    .tf-product-info { display: flex; flex-direction: column; gap: 2px; }
    .tf-product-name { font-size: 14px; font-weight: 800; color: var(--tf-text-h); }
    .tf-product-cat { font-size: 11px; color: var(--tf-text-m); }

    .tf-price-cell { text-align: center; }
    .tf-price-old { font-size: 14px; font-weight: 800; color: var(--tf-red); }
    .tf-price-new { font-size: 14px; font-weight: 800; color: var(--tf-green); }
    .tf-profit-old { font-size: 12px; color: var(--tf-amber); font-weight: 700; }
    .tf-profit-new { font-size: 12px; color: var(--tf-blue); font-weight: 700; }

    .tf-badge { display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 800; }
    .tf-badge.up { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-badge.down { background: var(--tf-red-soft); color: var(--tf-red); }
    .tf-badge.same { background: var(--tf-surface2); color: var(--tf-text-m); }

    /* ── Stats Cards ── */
    .tf-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 20px; }
    @media (max-width: 900px) { .tf-stats-grid { grid-template-columns: repeat(2, 1fr); } }

    .tf-stat-card {
        padding: 18px; border-radius: 16px; border: 1.5px solid var(--tf-border);
        text-align: center; transition: all .3s;
    }
    .tf-stat-card:hover { transform: translateY(-3px); box-shadow: var(--tf-shadow-md); }
    .tf-stat-label { font-size: 11px; font-weight: 700; color: var(--tf-text-m); margin-bottom: 8px; }
    .tf-stat-value { font-size: 24px; font-weight: 900; font-family: 'Cairo', sans-serif; }
    .tf-stat-card:nth-child(1) { background: var(--tf-blue-soft); border-color: rgba(58,142,240,0.2); }
    .tf-stat-card:nth-child(1) .tf-stat-value { color: var(--tf-blue); }
    .tf-stat-card:nth-child(2) { background: var(--tf-green-soft); border-color: rgba(15,170,126,0.2); }
    .tf-stat-card:nth-child(2) .tf-stat-value { color: var(--tf-green); }
    .tf-stat-card:nth-child(3) { background: var(--tf-violet-soft); border-color: rgba(124,92,236,0.2); }
    .tf-stat-card:nth-child(3) .tf-stat-value { color: var(--tf-violet); }
    .tf-stat-card:nth-child(4) { background: var(--tf-amber-soft); border-color: rgba(232,147,10,0.2); }
    .tf-stat-card:nth-child(4) .tf-stat-value { color: var(--tf-amber); }

    /* ── Footer Submit ── */
    .tf-footer {
        display: flex; justify-content: space-between; align-items: center;
        padding: 20px 24px; background: var(--tf-surface);
        border-radius: 20px; border: 1px solid var(--tf-border);
        flex-wrap: wrap; gap: 12px;
    }
    .tf-footer-info { display: flex; align-items: center; gap: 12px; }
    .tf-footer-icon {
        width: 50px; height: 50px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; background: var(--tf-amber-soft); color: var(--tf-amber);
    }
    .tf-footer-text { font-size: 13px; color: var(--tf-text-b); }
    .tf-footer-text strong { font-size: 16px; color: var(--tf-text-h); }
    .tf-footer-actions { display: flex; gap: 10px; }

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
    .tf-empty-sub { font-size: 13px; color: var(--tf-text-m); }
</style>
@endpush

@section('content')
<div class="tf-page" x-data="smartPriceUpdate()">

    {{-- Alerts --}}
    @if(session('success'))
    <div class="tf-alert tf-alert-success">
        <div class="tf-alert-content">
            <i class="fas fa-check-circle fa-lg"></i>
            <span class="tf-alert-text">{!! nl2br(e(session('success'))) !!}</span>
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

    {{-- Header --}}
    <div class="tf-header tf-section">
        <div>
            <h1 class="tf-header-title">🎯 التحديث الذكي للأسعار</h1>
            <p class="tf-header-subtitle">💡 حدّث أسعار منتجات محددة بدقة عالية حسب الوحدة والتصنيف</p>
            <p class="tf-header-features">⚡ التحديث التلقائي لوحدات البيع • 📊 حفظ السجل التاريخي • 🔒 تتبع المستخدمين</p>
        </div>
        <a href="{{ route('products.index') }}" class="tf-btn-back">
            <i class="fas fa-arrow-right"></i> رجوع للمنتجات
        </a>
    </div>

    <form @submit.prevent="submitUpdate">
        {{-- Step 1: Filters --}}
        <div class="tf-card blue tf-section">
            <div class="tf-card-head">
                <div class="tf-card-title">
                    <div class="tf-step-number">1</div>
                    <div>
                        <h3 class="tf-card-title-text">🔍 اختر الوحدة والتصنيف</h3>
                        <p class="tf-card-title-sub">حدد الوحدة الأساسية والتصنيف للبحث عن المنتجات</p>
                    </div>
                </div>
            </div>
            <div class="tf-card-body">
                <div class="tf-grid">
                    <div>
                        <label class="tf-label"><i class="fas fa-balance-scale" style="color:var(--tf-blue);"></i> الوحدة الأساسية <span class="req">*</span></label>
                        <select x-model="filters.base_unit" @change="loadCategories()" class="tf-select" required>
                            <option value="">-- اختر الوحدة الأساسية --</option>
                            @if(isset($unitsByCategory) && !empty($unitsByCategory))
                                @foreach($unitsByCategory as $categoryKey => $categoryData)
                                    <optgroup label="{{ $categoryData['label'] ?? $categoryKey }}">
                                        @foreach($categoryData['units'] as $unitCode => $unitLabel)
                                            <option value="{{ $unitCode }}">{{ $unitLabel }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @elseif(isset($activeUnits) && !empty($activeUnits))
                                @foreach($activeUnits as $unitCode => $unitLabel)
                                    <option value="{{ $unitCode }}">{{ $unitLabel }}</option>
                                @endforeach
                            @else
                                <optgroup label="وحدات الوزن">
                                    <option value="TON">طن (TON)</option>
                                    <option value="KG">كيلوجرام (KG)</option>
                                    <option value="GM">جرام (GM)</option>
                                </optgroup>
                                <optgroup label="وحدات الحجم">
                                    <option value="LTR">لتر (LTR)</option>
                                    <option value="ML">مليلتر (ML)</option>
                                    <option value="M3">متر مكعب (M3)</option>
                                </optgroup>
                                <optgroup label="وحدات العدد">
                                    <option value="UNIT">وحدة (UNIT)</option>
                                    <option value="PIECE">قطعة (PIECE)</option>
                                    <option value="BOX">صندوق (BOX)</option>
                                    <option value="CARTON">كرتونة (CARTON)</option>
                                    <option value="BAG">شيكارة (BAG)</option>
                                </optgroup>
                            @endif
                        </select>
                        <div class="tf-hint tf-hint-blue">
                            <i class="fas fa-info-circle"></i>
                            <span>اختر الوحدة الأساسية التي تريد تحديث أسعار المنتجات المسجلة بها</span>
                        </div>
                    </div>

                    <div>
                        <label class="tf-label"><i class="fas fa-tags" style="color:var(--tf-green);"></i> التصنيف <span class="req">*</span></label>
                        <select x-model="filters.category" @change="onCategoryChange()" class="tf-select" :disabled="!filters.base_unit || loadingCategories" required>
                            <option value="">
                                <template x-if="!filters.base_unit">-- اختر الوحدة أولاً --</template>
                                <template x-if="filters.base_unit && loadingCategories">⏳ جاري التحميل...</template>
                                <template x-if="filters.base_unit && !loadingCategories">-- اختر التصنيف --</template>
                            </option>
                            <template x-for="category in categories" :key="category">
                                <option :value="category" x-text="category"></option>
                            </template>
                        </select>
                        <div class="tf-hint tf-hint-green" x-show="categories.length > 0 && !loadingCategories">
                            <i class="fas fa-check-circle"></i>
                            <span x-text="'تم تحميل ' + categories.length + ' تصنيف بنجاح'"></span>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 20px;" x-show="filters.base_unit && filters.category" x-transition>
                    <button type="button" @click="loadProducts()" :disabled="loadingProducts" class="tf-btn-primary">
                        <i class="fas" :class="loadingProducts ? 'fa-spinner fa-spin' : 'fa-search'"></i>
                        <span x-text="loadingProducts ? '⏳ جاري البحث...' : '🔍 عرض المنتجات المتاحة'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Step 2: Products --}}
        <div x-show="products.length > 0" x-transition class="tf-card green tf-section">
            <div class="tf-card-head">
                <div class="tf-card-title">
                    <div class="tf-step-number">2</div>
                    <div>
                        <h3 class="tf-card-title-text">📦 المنتجات المتاحة <span x-text="'(' + products.length + ' منتج)'" style="font-size:13px;color:var(--tf-green);font-weight:700;"></span></h3>
                        <p class="tf-card-title-sub">حدد المنتجات المراد تحديث أسعارها</p>
                    </div>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="button" @click="selectAll()" class="tf-btn-secondary">
                        <i class="fas fa-check-double"></i> تحديد الكل
                    </button>
                    <button type="button" @click="deselectAll()" class="tf-btn-secondary">
                        <i class="fas fa-times"></i> إلغاء
                    </button>
                </div>
            </div>
            <div class="tf-card-body">
                {{-- Pricing Inputs --}}
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;padding:20px;background:var(--tf-violet-soft);border-radius:16px;">
                    <div>
                        <label class="tf-label">سعر الشراء الجديد</label>
                        <div style="position:relative;">
                            <input type="number" x-model="newPricing.purchase_price" @input="calculatePreview()" step="0.01" min="0" class="tf-input" placeholder="0.00" required>
                            <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-weight:800;color:var(--tf-text-m);">ج.م</span>
                        </div>
                    </div>
                    <div>
                        <label class="tf-label">نوع هامش الربح</label>
                        <select x-model="newPricing.profit_type" @change="calculatePreview()" class="tf-select" required>
                            <option value="fixed">💰 مبلغ ثابت</option>
                            <option value="percentage">📊 نسبة مئوية</option>
                        </select>
                    </div>
                    <div>
                        <label class="tf-label">قيمة هامش الربح</label>
                        <div style="position:relative;">
                            <input type="number" x-model="newPricing.profit_value" @input="calculatePreview()" step="0.01" min="0" class="tf-input" placeholder="0.00" required>
                            <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-weight:800;color:var(--tf-text-m);" x-text="newPricing.profit_type === 'percentage' ? '%' : 'ج.م'"></span>
                        </div>
                    </div>
                </div>

                {{-- Table --}}
                <div class="tf-table-wrapper">
                    <table class="tf-table">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>اسم المنتج</th>
                                <th style="text-align:center;">الوحدة</th>
                                <th style="text-align:center;">السعر القديم</th>
                                <th style="text-align:center;">السعر الجديد</th>
                                <th style="text-align:center;">هامش الربح</th>
                                <th style="text-align:center;">التغيير</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(product, index) in products" :key="product.id">
                                <tr :class="product.selected ? 'background:var(--tf-blue-soft);' : ''">
                                    <td>
                                        <input type="checkbox" x-model="product.selected" class="tf-checkbox">
                                    </td>
                                    <td>
                                        <div class="tf-product-info">
                                            <span class="tf-product-name" x-text="product.name"></span>
                                            <span class="tf-product-cat" x-text="product.category"></span>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="tf-badge" style="background:var(--tf-violet-soft);color:var(--tf-violet);" x-text="product.base_unit_label"></span>
                                    </td>
                                    <td class="tf-price-cell">
                                        <div class="tf-price-old" x-text="formatPrice(product.old_purchase_price)"></div>
                                        <div style="font-size:11px;color:var(--tf-text-m);" x-text="'بيع: ' + formatPrice(product.old_selling_price)"></div>
                                    </td>
                                    <td class="tf-price-cell">
                                        <div class="tf-price-new" x-text="formatPrice(product.new_purchase_price)"></div>
                                        <div style="font-size:11px;color:var(--tf-text-m);" x-text="'بيع: ' + formatPrice(product.new_selling_price)"></div>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="tf-profit-old" x-text="formatPrice(product.old_profit) + ' (' + product.old_profit_percentage.toFixed(1) + '%)'"></span>
                                    </td>
                                    <td style="text-align:center;">
                                        <template x-if="product.new_selling_price > product.old_selling_price">
                                            <span class="tf-badge up"><i class="fas fa-arrow-up"></i> <span x-text="formatPrice(product.new_selling_price - product.old_selling_price)"></span></span>
                                        </template>
                                        <template x-if="product.new_selling_price < product.old_selling_price">
                                            <span class="tf-badge down"><i class="fas fa-arrow-down"></i> <span x-text="formatPrice(product.old_selling_price - product.new_selling_price)"></span></span>
                                        </template>
                                        <template x-if="product.new_selling_price === product.old_selling_price">
                                            <span class="tf-badge same">— بدون تغيير</span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Stats --}}
                <div class="tf-stats-grid">
                    <div class="tf-stat-card">
                        <div class="tf-stat-label">المنتجات المحددة</div>
                        <div class="tf-stat-value" x-text="selectedProducts.length"></div>
                    </div>
                    <div class="tf-stat-card">
                        <div class="tf-stat-label">القيمة القديمة</div>
                        <div class="tf-stat-value" x-text="formatPrice(totalOldValue)"></div>
                    </div>
                    <div class="tf-stat-card">
                        <div class="tf-stat-label">القيمة الجديدة</div>
                        <div class="tf-stat-value" x-text="formatPrice(totalNewValue)"></div>
                    </div>
                    <div class="tf-stat-card">
                        <div class="tf-stat-label">الفرق</div>
                        <div class="tf-stat-value" x-text="formatPrice(Math.abs(totalNewValue - totalOldValue))"></div>
                    </div>
                </div>

                {{-- Change Reason --}}
                <div style="margin-top:20px;padding:16px;background:var(--tf-surface2);border-radius:14px;">
                    <label class="tf-label">سبب التغيير (اختياري)</label>
                    <input type="text" x-model="changeReason" class="tf-input" placeholder="مثال: تحديث الأسعار حسب السوق، تغيير سعر المورد..." maxlength="500">
                </div>
            </div>
        </div>

        {{-- Footer Submit --}}
        <div x-show="selectedProducts.length > 0" x-transition class="tf-footer tf-section">
            <div class="tf-footer-info">
                <div class="tf-footer-icon"><i class="fas fa-bolt"></i></div>
                <div class="tf-footer-text">
                    سيتم تحديث <strong x-text="selectedProducts.length"></strong> منتج فقط
                </div>
            </div>
            <div class="tf-footer-actions">
                <button type="button" @click="resetForm()" class="tf-btn-secondary">
                    <i class="fas fa-redo"></i> إعادة تعيين
                </button>
                <button type="submit" :disabled="submitting" class="tf-btn-accent">
                    <i class="fas" :class="submitting ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                    <span x-text="submitting ? '⏳ جاري...' : '💾 حفظ التحديثات'"></span>
                </button>
            </div>
        </div>
    </form>

    {{-- Empty State --}}
    <div x-show="!products.length && filters.base_unit && filters.category && !loadingProducts" class="tf-card">
        <div class="tf-empty">
            <div class="tf-empty-icon"><i class="fas fa-box-open"></i></div>
            <h3 class="tf-empty-title">لا توجد منتجات</h3>
            <p class="tf-empty-sub">لا توجد منتجات مسجلة بهذه الوحدة والتصنيف</p>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function smartPriceUpdate() {
    return {
        filters: { base_unit: '', category: '' },
        categories: [],
        loadingCategories: false,
        loadingProducts: false,
        products: [],
        newPricing: { purchase_price: 0, profit_type: 'fixed', profit_value: 0 },
        changeReason: '',
        submitting: false,

        init() {},

        async loadCategories() {
            if (!this.filters.base_unit) { this.categories = []; this.products = []; this.filters.category = ''; return; }
            this.loadingCategories = true; this.filters.category = ''; this.products = [];
            try {
                const url = `/products/ajax/categories-by-unit?base_unit=${encodeURIComponent(this.filters.base_unit)}`;
                const response = await fetch(url);
                const data = await response.json();
                if (data.success) this.categories = data.categories || [];
                else this.categories = [];
            } catch (error) { this.categories = []; }
            finally { this.loadingCategories = false; }
        },

        onCategoryChange() { this.products = []; },

        async loadProducts() {
            if (!this.filters.base_unit || !this.filters.category) return;
            this.loadingProducts = true; this.products = [];
            try {
                const url = `/products/ajax/by-unit-category?base_unit=${encodeURIComponent(this.filters.base_unit)}&category=${encodeURIComponent(this.filters.category)}`;
                const response = await fetch(url);
                const data = await response.json();
                if (data.success) {
                    this.products = data.products.map(p => ({
                        ...p, selected: true,
                        old_purchase_price: parseFloat(p.base_purchase_price),
                        old_selling_price: parseFloat(p.base_selling_price),
                        old_profit: parseFloat(p.base_selling_price) - parseFloat(p.base_purchase_price),
                        old_profit_percentage: ((parseFloat(p.base_selling_price) - parseFloat(p.base_purchase_price)) / parseFloat(p.base_purchase_price)) * 100,
                        new_purchase_price: 0, new_selling_price: 0, new_profit: 0, new_profit_percentage: 0
                    }));
                    if (this.products.length > 0) {
                        this.newPricing.purchase_price = this.products[0].old_purchase_price;
                        this.newPricing.profit_value = this.products[0].old_profit;
                        this.newPricing.profit_type = 'fixed';
                    }
                    this.calculatePreview();
                }
            } catch (error) {}
            finally { this.loadingProducts = false; }
        },

        calculatePreview() {
            const purchasePrice = parseFloat(this.newPricing.purchase_price) || 0;
            const profitValue = parseFloat(this.newPricing.profit_value) || 0;
            this.products = this.products.map(product => {
                let newProfit = this.newPricing.profit_type === 'percentage' ? (purchasePrice * profitValue) / 100 : profitValue;
                let newSelling = purchasePrice + newProfit;
                let newProfitPercentage = purchasePrice > 0 ? (newProfit / purchasePrice) * 100 : 0;
                return { ...product, new_purchase_price: purchasePrice, new_selling_price: newSelling, new_profit: newProfit, new_profit_percentage: newProfitPercentage };
            });
        },

        get selectedProducts() { return this.products.filter(p => p.selected); },
        get totalOldValue() { return this.selectedProducts.reduce((sum, p) => sum + p.old_selling_price, 0); },
        get totalNewValue() { return this.selectedProducts.reduce((sum, p) => sum + p.new_selling_price, 0); },

        selectAll() { this.products.forEach(p => p.selected = true); },
        deselectAll() { this.products.forEach(p => p.selected = false); },
        resetForm() { this.products = []; this.filters = { base_unit: '', category: '' }; this.categories = []; },
        formatPrice(value) { return new Intl.NumberFormat('ar-EG', { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0) + ' ج.م'; },
        async submitUpdate() {
            if (this.selectedProducts.length === 0) { alert('⚠️ يرجى تحديد منتج واحد على الأقل'); return; }
            this.submitting = true;
            const formData = new FormData();
            formData.append('base_unit', this.filters.base_unit);
            formData.append('category', this.filters.category);
            formData.append('base_purchase_price', this.newPricing.purchase_price);
            formData.append('profit_value', this.newPricing.profit_value);
            formData.append('profit_type', this.newPricing.profit_type);
            formData.append('selected_products', JSON.stringify(this.selectedProducts.map(p => ({ id: p.id, new_purchase_price: p.new_purchase_price, new_selling_price: p.new_selling_price }))));
            formData.append('change_reason', this.changeReason);
            try {
                const response = await fetch('{{ route("products.bulk-price-update.apply") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: formData });
                const data = await response.json();
                if (data.success) { alert('✅ ' + data.message); window.location.href = '{{ route("products.index") }}'; }
                else { alert('❌ ' + data.message); }
            } catch (error) { alert('❌ حدث خطأ'); }
            finally { this.submitting = false; }
        }
    }
}
</script>
@endpush