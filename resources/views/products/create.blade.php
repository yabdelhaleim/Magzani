@extends('layouts.app')

@section('title', 'إضافة منتج جديد')
@section('page-title', 'إضافة منتج جديد')

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
    .tf-section:nth-child(5) { animation-delay: 0.40s; }

    /* ── Header ── */
    .tf-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 22px; flex-wrap: wrap; gap: 16px;
    }
    .tf-title-row { display: flex; flex-direction: column; gap: 4px; }
    .tf-title { font-size: 24px; font-weight: 900; color: var(--tf-text-h); margin: 0; display: flex; align-items: center; gap: 12px; }
    .tf-subtitle { font-size: 13px; color: var(--tf-text-m); font-weight: 600; }

    .tf-btn-back {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 11px 20px; border-radius: 14px;
        background: var(--tf-surface); color: var(--tf-text-b);
        border: 1.5px solid var(--tf-border);
        font-size: 13px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif;
        transition: all .25s ease;
    }
    .tf-btn-back:hover { background: var(--tf-surface2); border-color: var(--tf-indigo); color: var(--tf-indigo); }
    .tf-btn-back:hover i { animation: iconBounce .5s ease; }

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

    .tf-errors-list { margin: 12px 0 0; padding-right: 20px; }
    .tf-errors-list li { font-size: 13px; color: #991b1b; font-weight: 600; margin-bottom: 4px; display: flex; align-items: center; gap: 6px; }

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

    .tf-card-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 18px 24px; border-bottom: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2); flex-wrap: wrap; gap: 12px;
    }
    .tf-card-title {
        display: flex; align-items: center; gap: 12px;
    }
    .tf-card-icon {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
        transition: transform .4s cubic-bezier(.34,1.56,.64,1);
    }
    .tf-card:hover .tf-card-icon { animation: iconBounce .6s ease; }
    .tf-card-title-text { font-size: 16px; font-weight: 800; color: var(--tf-text-h); }
    .tf-card-title-sub { font-size: 11px; color: var(--tf-text-m); font-weight: 600; }

    .tf-card.blue .tf-card-icon   { background: var(--tf-blue-soft);   color: var(--tf-blue); }
    .tf-card.green .tf-card-icon  { background: var(--tf-green-soft);  color: var(--tf-green); }
    .tf-card.violet .tf-card-icon { background: var(--tf-violet-soft); color: var(--tf-violet); }
    .tf-card.amber .tf-card-icon  { background: var(--tf-amber-soft);  color: var(--tf-amber); }

    .tf-step-badge {
        padding: 6px 14px; border-radius: 50px;
        font-size: 11px; font-weight: 800;
    }
    .tf-card.blue .tf-step-badge   { background: var(--tf-blue-soft);   color: var(--tf-blue); }
    .tf-card.green .tf-step-badge  { background: var(--tf-green-soft);  color: var(--tf-green); }
    .tf-card.violet .tf-step-badge { background: var(--tf-violet-soft); color: var(--tf-violet); }
    .tf-card.amber .tf-step-badge { background: var(--tf-amber-soft);  color: var(--tf-amber); }

    .tf-card-body { padding: 24px; }

    /* ── Form Controls ── */
    .tf-label {
        display: block; font-size: 13px; font-weight: 800;
        color: var(--tf-text-b); margin-bottom: 8px;
    }
    .tf-label .req { color: var(--tf-red); font-size: 14px; }
    .tf-label .opt { font-weight: 600; color: var(--tf-text-m); font-size: 11px; }

    .tf-input, .tf-select, .tf-textarea {
        width: 100%; padding: 12px 16px;
        border: 1.5px solid var(--tf-border); border-radius: 14px;
        font-size: 14px; font-family: 'Cairo', sans-serif;
        color: var(--tf-text-h); background: var(--tf-surface);
        transition: all .25s; outline: none;
    }
    .tf-input:focus, .tf-select:focus, .tf-textarea:focus {
        border-color: var(--tf-blue);
        box-shadow: 0 0 0 3px rgba(58,142,240,0.12);
    }
    .tf-input.error { border-color: var(--tf-red); background: var(--tf-red-soft); }
    .tf-select { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%237e90b0'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: left 14px center; background-size: 16px; }
    .tf-textarea { resize: vertical; min-height: 100px; }

    /* ── Grid ── */
    .tf-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .tf-grid-full { grid-column: span 2; }
    @media (max-width: 768px) { .tf-grid { grid-template-columns: 1fr; } .tf-grid-full { grid-column: span 1; } }

    /* ── Price Inputs ── */
    .tf-price-wrapper {
        position: relative;
    }
    .tf-price-symbol {
        position: absolute; left: 14px; top: 50%;
        transform: translateY(-50%);
        font-size: 13px; font-weight: 800; color: var(--tf-text-m);
    }
    .tf-input-price {
        padding-right: 40px !important;
        padding-left: 40px !important;
    }

    /* ── Suggestion Box ── */
    .tf-suggestion {
        display: flex; align-items: flex-start; gap: 16px;
        padding: 20px; border-radius: 16px; margin-bottom: 20px;
        background: var(--tf-surface); border: 1px solid var(--tf-blue-soft);
    }
    .tf-suggestion-icon {
        width: 56px; height: 56px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; background: var(--tf-blue-soft); color: var(--tf-blue);
        flex-shrink: 0;
    }
    .tf-suggestion-title {
        font-size: 15px; font-weight: 800; color: var(--tf-text-h); margin-bottom: 12px;
    }
    .tf-suggestion-grid {
        display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px;
    }
    .tf-suggestion-item {
        padding: 14px; border-radius: 12px; border: 1.5px solid;
    }
    .tf-suggestion-item label { display: block; font-size: 11px; font-weight: 700; margin-bottom: 6px; }
    .tf-suggestion-item span { font-size: 22px; font-weight: 900; font-family: 'Cairo', sans-serif; }
    .tf-suggestion-item.green { background: var(--tf-green-soft); border-color: rgba(15,170,126,0.2); }
    .tf-suggestion-item.green span { color: var(--tf-green); }
    .tf-suggestion-item.blue { background: var(--tf-blue-soft); border-color: rgba(58,142,240,0.2); }
    .tf-suggestion-item.blue span { color: var(--tf-blue); }

    .tf-btn-apply {
        width: 100%; padding: 14px; border-radius: 14px;
        background: linear-gradient(135deg, var(--tf-blue), var(--tf-indigo-light));
        color: var(--tf-surface); border: none;
        font-size: 14px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif;
        box-shadow: 0 4px 16px rgba(58,142,240,0.35);
        transition: all .3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .tf-btn-apply:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(58,142,240,0.45); }
    .tf-btn-apply:hover i { animation: iconBounce .5s ease; }

    /* ── Checkbox ── */
    .tf-checkbox {
        display: flex; align-items: center; gap: 12px;
        padding: 16px; border-radius: 14px;
        background: var(--tf-surface); border: 1.5px solid var(--tf-border);
        cursor: pointer; transition: all .25s;
    }
    .tf-checkbox:hover { border-color: var(--tf-amber); }
    .tf-checkbox input {
        width: 22px; height: 22px; cursor: pointer;
    }
    .tf-checkbox-info { display: flex; align-items: center; gap: 10px; flex: 1; }
    .tf-checkbox-icon {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; background: var(--tf-green-soft); color: var(--tf-green);
    }
    .tf-checkbox-title { font-size: 14px; font-weight: 800; color: var(--tf-text-h); }
    .tf-checkbox-desc { font-size: 11px; color: var(--tf-text-m); margin-top: 2px; }

    /* ── Footer Buttons ── */
    .tf-footer {
        display: flex; justify-content: space-between; align-items: center;
        padding: 20px 24px; background: var(--tf-surface);
        border-radius: 20px; border: 1px solid var(--tf-border);
        flex-wrap: wrap; gap: 12px;
    }
    .tf-btn-cancel {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 14px 24px; border-radius: 14px;
        background: var(--tf-surface2); color: var(--tf-text-b);
        border: 1.5px solid var(--tf-border);
        font-size: 14px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif; text-decoration: none;
        transition: all .25s;
    }
    .tf-btn-cancel:hover { background: var(--tf-border-soft); }

    .tf-btn-submit {
        display: inline-flex; align-items: center; gap: 10px;
        padding: 14px 32px; border-radius: 16px;
        background: linear-gradient(135deg, var(--tf-blue), var(--tf-indigo));
        color: var(--tf-surface); border: none;
        font-size: 15px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif;
        box-shadow: 0 6px 24px rgba(58,142,240,0.4);
        transition: all .3s ease;
    }
    .tf-btn-submit:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 32px rgba(58,142,240,0.5);
    }
    .tf-btn-submit i { transition: transform .3s; }
    .tf-btn-submit:hover i { animation: iconBounce .5s ease; }
</style>
@endpush

@section('content')
<div class="tf-page" x-data="productCreateApp()">

    {{-- Header --}}
    <div class="tf-header tf-section">
        <div class="tf-title-row">
            <h1 class="tf-title">
                <span class="tf-card-icon" style="background:var(--tf-blue-soft);color:var(--tf-blue);width:48px;height:48px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;">
                    <i class="fas fa-plus"></i>
                </span>
                إضافة منتج جديد
            </h1>
            <p class="tf-subtitle">أدخل بيانات المنتج بدقة لضمان سير العمل بشكل صحيح</p>
        </div>
        <a href="{{ route('products.index') }}" class="tf-btn-back">
            <i class="fas fa-arrow-right"></i> رجوع للقائمة
        </a>
    </div>

    {{-- Alerts --}}
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

    @if($errors->any())
    <div class="tf-alert tf-alert-error">
        <div class="tf-alert-content">
            <i class="fas fa-exclamation-triangle fa-lg"></i>
            <div>
                <span class="tf-alert-text">يوجد أخطاء في النموذج:</span>
                <ul class="tf-errors-list">
                    @foreach($errors->all() as $error)
                    <li><i class="fas fa-times-circle" style="font-size:10px;"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <i class="tf-alert-close fas fa-times" onclick="this.parentElement.remove()"></i>
    </div>
    @endif

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="validateAndSubmit">
        @csrf

        {{-- Step 1: Basic Info --}}
        <div class="tf-card blue tf-section">
            <div class="tf-card-header">
                <div class="tf-card-title">
                    <div class="tf-card-icon"><i class="fas fa-info-circle"></i></div>
                    <div>
                        <h3 class="tf-card-title-text">المعلومات الأساسية</h3>
                        <p class="tf-card-title-sub">الخطوة 1 من 4</p>
                    </div>
                </div>
                <span class="tf-step-badge">مطلوب</span>
            </div>
            <div class="tf-card-body">
                <div class="tf-grid">
                    <div class="tf-grid-full">
                        <label class="tf-label">اسم المنتج <span class="req">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="tf-input" placeholder="مثال: أسمنت بورتلاند أبيض" required>
                    </div>

                    <div>
                        <label class="tf-label">كود المنتج (SKU) <span class="opt">(يتم توليده تلقائياً)</span></label>
                        <input type="text" name="sku" value="{{ old('sku') }}" class="tf-input" placeholder="اتركه فارغاً للتوليد التلقائي">
                    </div>

                    <div>
                        <label class="tf-label">الباركود <span class="opt">(اختياري)</span></label>
                        <input type="text" name="barcode" value="{{ old('barcode') }}" class="tf-input" placeholder="1234567890123">
                    </div>

                    <div>
                        <label class="tf-label">التصنيف <span class="req">*</span></label>
                        <input type="text" name="category" value="{{ old('category') }}" x-model="category" @input="loadPricingSuggestions()" class="tf-input" placeholder="مثال: أسمنت، حديد، رمل" required>
                    </div>

                    <div>
                        <label class="tf-label">الوحدة الأساسية <span class="req">*</span></label>
                        <select name="base_unit" x-model="baseUnit" @change="updateBaseUnitLabel(); loadPricingSuggestions()" class="tf-select" required>
                            <option value="">-- اختر الوحدة الأساسية --</option>
                            @if(isset($unitsByCategory))
                                @foreach($unitsByCategory as $categoryKey => $categoryData)
                                    <optgroup label="{{ $categoryData['label'] }}">
                                        @foreach($categoryData['units'] as $unitCode => $unitLabel)
                                            <option value="{{ $unitCode }}" {{ old('base_unit') == $unitCode ? 'selected' : '' }}>{{ $unitLabel }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="tf-grid-full">
                        <label class="tf-label">الوصف <span class="opt">(اختياري)</span></label>
                        <textarea name="description" class="tf-textarea" placeholder="وصف تفصيلي للمنتج...">{{ old('description') }}</textarea>
                    </div>
                </div>
                <input type="hidden" name="base_unit_label" :value="baseUnitLabel || ''">
            </div>
        </div>

        {{-- Step 2: Pricing --}}
        <div class="tf-card green tf-section">
            <div class="tf-card-header">
                <div class="tf-card-title">
                    <div class="tf-card-icon"><i class="fas fa-coins"></i></div>
                    <div>
                        <h3 class="tf-card-title-text">التسعير الذكي</h3>
                        <p class="tf-card-title-sub">الخطوة 2 من 4</p>
                    </div>
                </div>
                <span class="tf-step-badge" x-show="baseUnit" x-text="'الوحدة: ' + baseUnitLabel"></span>
            </div>
            <div class="tf-card-body">
                {{-- Suggestions --}}
                <div x-show="suggestions" x-transition class="tf-suggestion">
                    <div class="tf-suggestion-icon"><i class="fas fa-lightbulb"></i></div>
                    <div style="flex:1;">
                        <p class="tf-suggestion-title">💡 اقتراحات ذكية بناءً على <span x-text="suggestions?.sample_size || 0"></span> منتج مشابه</p>
                        <div class="tf-suggestion-grid">
                            <div class="tf-suggestion-item green">
                                <label>سعر الشراء المقترح</label>
                                <span x-text="formatPrice(suggestions?.suggested_purchase_price)"></span>
                            </div>
                            <div class="tf-suggestion-item blue">
                                <label>سعر البيع المقترح</label>
                                <span x-text="formatPrice(suggestions?.suggested_selling_price)"></span>
                            </div>
                        </div>
                        <button type="button" @click="applySuggestions()" class="tf-btn-apply">
                            <i class="fas fa-check-circle"></i> تطبيق الاقتراحات الذكية
                        </button>
                    </div>
                </div>

                <div class="tf-grid" style="margin-bottom: 20px;">
                    <div>
                        <label class="tf-label">سعر الشراء <span class="req">*</span></label>
                        <div class="tf-price-wrapper">
                            <input type="number" name="purchase_price" x-model.number="purchasePrice" @input="calculatePrices()" step="0.01" min="0" class="tf-input tf-input-price" placeholder="0.00" required>
                            <span class="tf-price-symbol">ج.م</span>
                        </div>
                    </div>

                    <div>
                        <label class="tf-label">سعر البيع <span class="req">*</span></label>
                        <div class="tf-price-wrapper">
                            <input type="number" name="selling_price" x-model.number="sellingPrice" @input="calculateProfitFromSelling()" step="0.01" min="0" class="tf-input tf-input-price" placeholder="0.00" required>
                            <span class="tf-price-symbol">ج.م</span>
                        </div>
                    </div>

                    <div>
                        <label class="tf-label">هامش الربح <span class="opt">(تلقائي)</span></label>
                        <input type="text" :value="formatPrice(profit)" class="tf-input" readonly style="background:var(--tf-blue-soft);color:var(--tf-blue);font-weight:800;">
                        <p class="tf-label" style="margin-top:6px;color:var(--tf-blue);font-size:11px;" x-show="profitPercentage > 0">📊 نسبة الربح: <span x-text="profitPercentage.toFixed(1) + '%'"></span></p>
                    </div>
                </div>

                <div class="tf-grid">
                    <div>
                        <label class="tf-label">الحد الأدنى لسعر البيع <span class="opt">(اختياري)</span></label>
                        <div class="tf-price-wrapper">
                            <input type="number" name="min_selling_price" value="{{ old('min_selling_price') }}" step="0.01" min="0" class="tf-input tf-input-price" placeholder="0.00">
                            <span class="tf-price-symbol">ج.م</span>
                        </div>
                    </div>

                    <div>
                        <label class="tf-label">سعر الجملة <span class="opt">(اختياري)</span></label>
                        <div class="tf-price-wrapper">
                            <input type="number" name="wholesale_price" value="{{ old('wholesale_price') }}" step="0.01" min="0" class="tf-input tf-input-price" placeholder="0.00">
                            <span class="tf-price-symbol">ج.م</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Tax & Discount --}}
        <div class="tf-card violet tf-section">
            <div class="tf-card-header">
                <div class="tf-card-title">
                    <div class="tf-card-icon"><i class="fas fa-percentage"></i></div>
                    <div>
                        <h3 class="tf-card-title-text">الضرائب والخصومات</h3>
                        <p class="tf-card-title-sub">الخطوة 3 من 4</p>
                    </div>
                </div>
                <span class="tf-step-badge">اختياري</span>
            </div>
            <div class="tf-card-body">
                <div class="tf-grid">
                    <div>
                        <label class="tf-label">نسبة الضريبة (%)</label>
                        <div class="tf-price-wrapper">
                            <input type="number" name="tax_rate" value="{{ old('tax_rate', 0) }}" step="0.01" min="0" max="100" class="tf-input tf-input-price" placeholder="0">
                            <span class="tf-price-symbol" style="left:auto;right:14px;">%</span>
                        </div>
                    </div>

                    <div>
                        <label class="tf-label">الخصم الافتراضي (%)</label>
                        <div class="tf-price-wrapper">
                            <input type="number" name="default_discount" value="{{ old('default_discount', 0) }}" step="0.01" min="0" max="100" class="tf-input tf-input-price" placeholder="0">
                            <span class="tf-price-symbol" style="left:auto;right:14px;">%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 4: Stock --}}
        <div class="tf-card amber tf-section">
            <div class="tf-card-header">
                <div class="tf-card-title">
                    <div class="tf-card-icon"><i class="fas fa-boxes"></i></div>
                    <div>
                        <h3 class="tf-card-title-text">المخزون والحالة</h3>
                        <p class="tf-card-title-sub">الخطوة 4 من 4</p>
                    </div>
                </div>
                <span class="tf-step-badge">مطلوب</span>
            </div>
            <div class="tf-card-body">
                <div class="tf-grid" style="margin-bottom: 20px;">
                    <div>
                        <label class="tf-label">المخزن</label>
                        <select name="warehouses[0][warehouse_id]" id="warehouse_id" class="tf-select">
                            <option value="">-- لا تضيف لمخزن الآن --</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouses.0.warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="tf-label">الكمية المبدئية</label>
                        <input type="number" name="warehouses[0][quantity]" id="initial_quantity" value="{{ old('warehouses.0.quantity', 0) }}" class="tf-input" min="0" step="0.01" placeholder="0" disabled>
                    </div>

                    <div>
                        <label class="tf-label">الحد الأدنى للتنبيه</label>
                        <input type="number" name="warehouses[0][min_stock]" id="stock_alert_quantity" value="{{ old('warehouses.0.min_stock', 10) }}" class="tf-input" min="0" step="0.01" placeholder="10">
                    </div>
                </div>

                <label class="tf-checkbox">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <div class="tf-checkbox-info">
                        <div class="tf-checkbox-icon"><i class="fas fa-check"></i></div>
                        <div>
                            <span class="tf-checkbox-title">المنتج نشط ومتاح للبيع</span>
                            <p class="tf-checkbox-desc">سيظهر المنتج في القوائم ويمكن إضافته للفواتير</p>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        {{-- Footer Buttons --}}
        <div class="tf-footer tf-section">
            <a href="{{ route('products.index') }}" class="tf-btn-cancel">
                <i class="fas fa-times"></i> إلغاء
            </a>
            <button type="submit" class="tf-btn-submit">
                <i class="fas fa-save"></i>
                حفظ المنتج
                <i class="fas fa-arrow-left" style="margin-right:4px;"></i>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function productCreateApp() {
    return {
        baseUnit: '{{ old("base_unit", "") }}',
        baseUnitLabel: '',
        category: '{{ old("category", "") }}',
        purchasePrice: {{ old('purchase_price', 0) }},
        sellingPrice: {{ old('selling_price', 0) }},
        profit: 0,
        profitPercentage: 0,
        suggestions: null,

        init() {
            this.updateBaseUnitLabel();
            this.calculatePrices();
            this.setupWarehouseToggle();

            if (this.baseUnit && this.category) {
                this.loadPricingSuggestions();
            }
        },

        validateAndSubmit(e) {
            if (!this.baseUnit) { alert('⚠️ يجب اختيار الوحدة الأساسية'); e.preventDefault(); return false; }
            if (!this.category) { alert('⚠️ يجب إدخال التصنيف'); e.preventDefault(); return false; }
            if (!this.purchasePrice || this.purchasePrice <= 0) { alert('⚠️ يجب إدخال سعر شراء صحيح'); e.preventDefault(); return false; }
            if (!this.sellingPrice || this.sellingPrice <= 0) { alert('⚠️ يجب إدخال سعر بيع صحيح'); e.preventDefault(); return false; }
            if (this.sellingPrice < this.purchasePrice) {
                if (!confirm('⚠️ تحذير: سعر البيع أقل من سعر الشراء!\n\nهل تريد المتابعة؟')) { e.preventDefault(); return false; }
            }
            e.target.submit();
        },

        updateBaseUnitLabel() {
            const select = document.querySelector('[name="base_unit"]');
            if (select && select.selectedOptions[0]) {
                this.baseUnitLabel = select.selectedOptions[0].text;
            }
        },

        async loadPricingSuggestions() {
            if (!this.baseUnit) { this.suggestions = null; return; }
            try {
                const url = new URL('/products/ajax/suggested-pricing', window.location.origin);
                url.searchParams.append('base_unit', this.baseUnit);
                if (this.category) url.searchParams.append('category', this.category);
                const response = await fetch(url);
                const data = await response.json();
                if (data.success && data.suggestions) this.suggestions = data.suggestions;
                else this.suggestions = null;
            } catch (error) { this.suggestions = null; }
        },

        applySuggestions() {
            if (!this.suggestions) return;
            this.purchasePrice = this.suggestions.suggested_purchase_price;
            this.sellingPrice = this.suggestions.suggested_selling_price;
            this.calculatePrices();
        },

        calculatePrices() {
            const purchase = parseFloat(this.purchasePrice) || 0;
            const selling = parseFloat(this.sellingPrice) || 0;
            this.profit = selling - purchase;
            this.profitPercentage = purchase > 0 ? (this.profit / purchase) * 100 : 0;
        },

        calculateProfitFromSelling() { this.calculatePrices(); },

        setupWarehouseToggle() {
            const warehouseSelect = document.getElementById('warehouse_id');
            const quantityInput = document.getElementById('initial_quantity');
            warehouseSelect?.addEventListener('change', function() {
                quantityInput.disabled = !this.value;
                if (!this.value) quantityInput.value = 0;
            });
        },

        formatPrice(value) {
            return new Intl.NumberFormat('ar-EG', { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0) + ' ج.م';
        }
    }
}
</script>
@endpush
