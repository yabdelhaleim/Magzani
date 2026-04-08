@extends('layouts.app')

@section('title', 'تحويل جديد بين المخازن')
@section('page-title', 'تحويل جديد بين المخازن')

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

        --tf-blue-dark:   #1d4ed8;
        --tf-red-dark:    #991b1b;

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
    @keyframes tfSpin {
        to { transform: rotate(360deg); }
    }
    @keyframes tfBeat {
        0%,100% { transform: scale(1); }
        50%     { transform: scale(1.15); }
    }
    @keyframes iconBounce {
        0%,100% { transform: translateY(0) rotate(0deg); }
        30%     { transform: translateY(-4px) rotate(-8deg); }
        60%     { transform: translateY(-2px) rotate(4deg); }
    }

    .tf-section { animation: tfFadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .tf-section:nth-child(1) { animation-delay: 0.04s; }
    .tf-section:nth-child(2) { animation-delay: 0.13s; }
    .tf-section:nth-child(3) { animation-delay: 0.22s; }

    /* ── Cards ── */
    .tf-card {
        background: var(--tf-surface);
        border-radius: 20px;
        border: 1px solid var(--tf-border);
        overflow: hidden;
        box-shadow: var(--tf-shadow-card);
        margin-bottom: 20px;
        position: relative;
        transition: transform .35s cubic-bezier(.22,1,.36,1), box-shadow .35s ease;
    }
    .tf-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--tf-shadow-lg);
    }
    .tf-card::after {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.45) 50%, transparent 60%);
        background-size: 600px 100%; opacity: 0; pointer-events: none; transition: opacity .3s;
    }
    .tf-card:hover::after { opacity: 1; animation: tfShimmer .7s ease forwards; }

    /* ── Card Header ── */
    .tf-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 22px;
        border-bottom: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2);
        position: relative;
    }
    .tf-card-head-left { display: flex; align-items: center; gap: 14px; }
    .tf-head-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
        transition: transform .4s cubic-bezier(.34,1.56,.64,1);
        position: relative;
    }
    .tf-card:hover .tf-head-icon { animation: iconBounce .6s ease; }
    .tf-head-icon.blue   { background: var(--tf-blue-soft);   color: var(--tf-blue); }
    .tf-head-icon.green  { background: var(--tf-green-soft);  color: var(--tf-green); }
    .tf-head-icon.violet { background: var(--tf-violet-soft); color: var(--tf-violet); }
    .tf-head-icon.amber  { background: var(--tf-amber-soft);  color: var(--tf-amber); }

    .tf-head-title { font-size: 15px; font-weight: 800; color: var(--tf-text-h); margin: 0; }
    .tf-head-sub   { font-size: 11px; color: var(--tf-text-m); margin: 3px 0 0; font-weight: 600; background: linear-gradient(90deg, var(--tf-text-m), var(--tf-text-b)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    /* ── Form Controls ── */
    .tf-label {
        display: block;
        font-size: 12px;
        font-weight: 800;
        color: var(--tf-text-b);
        margin-bottom: 7px;
        letter-spacing: 0.2px;
    }
    .tf-label .req { color: var(--tf-red); margin-right: 2px; }

    .tf-input, .tf-select, .tf-textarea {
        width: 100%;
        padding: 11px 14px;
        border: 1.5px solid var(--tf-border);
        border-radius: 12px;
        font-size: 13px;
        font-family: 'Cairo', sans-serif;
        color: var(--tf-text-h);
        background: var(--tf-surface);
        transition: border-color 0.25s, box-shadow 0.25s, transform 0.2s;
        outline: none;
    }
    .tf-input:focus, .tf-select:focus, .tf-textarea:focus {
        border-color: var(--tf-blue);
        box-shadow: 0 0 0 3px rgba(58,142,240,0.1);
        transform: scale(1.005);
    }
    .tf-select { cursor: pointer; }
    .tf-textarea { resize: vertical; min-height: 88px; }

    .tf-hint {
        font-size: 11px;
        color: var(--tf-text-m);
        margin-top: 5px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Focus states per color */
    .tf-input.green:focus, .tf-select.green:focus {
        border-color: var(--tf-green);
        box-shadow: 0 0 0 3px rgba(15,170,126,0.1);
    }
    .tf-input.violet:focus, .tf-select.violet:focus {
        border-color: var(--tf-violet);
        box-shadow: 0 0 0 3px rgba(124,92,236,0.1);
    }
    .tf-input.error { border-color: var(--tf-red); background: var(--tf-red-soft); }
    .tf-input.success { border-color: var(--tf-green); }

    /* ── Warehouse Warning ── */
    .tf-warn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        background: var(--tf-red-soft);
        border: 1px solid rgba(232,75,90,0.2);
        border-radius: 14px;
        margin-top: 16px;
        font-size: 13px;
        font-weight: 700;
        color: var(--tf-red);
        animation: tfBeat 1.5s ease-in-out infinite;
    }

    /* ── Table ── */
    .tf-table { width: 100%; border-collapse: collapse; }
    .tf-table thead th {
        padding: 12px 16px;
        text-align: right;
        font-size: 10px;
        font-weight: 800;
        color: var(--tf-text-m);
        text-transform: uppercase;
        letter-spacing: 0.7px;
        border-bottom: 1.5px solid var(--tf-border-soft);
        background: var(--tf-surface2);
        white-space: nowrap;
    }
    .tf-table tbody tr { transition: background 0.18s; }
    .tf-table tbody tr:hover { background: var(--tf-surface2); }
    .tf-table tbody td {
        padding: 13px 16px;
        border-bottom: 1px solid var(--tf-border-soft);
        vertical-align: middle;
    }
    .tf-table tbody tr:last-child td { border-bottom: none; }

    .row-num {
        width: 30px; height: 30px; border-radius: 8px;
        background: var(--tf-blue-soft); color: var(--tf-blue);
        font-size: 12px; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    /* Available qty badge */
    .qty-badge {
        display: inline-flex; align-items: center;
        padding: 5px 12px; border-radius: 50px;
        font-size: 13px; font-weight: 800;
        border: 1px solid transparent;
    }
    .qty-badge.high   { background: var(--tf-green-soft);  color: var(--tf-green); border-color: rgba(15,170,126,0.2); }
    .qty-badge.medium { background: var(--tf-amber-soft);  color: var(--tf-amber); border-color: rgba(232,147,10,0.2); }
    .qty-badge.low    { background: var(--tf-red-soft);    color: var(--tf-red);   border-color: rgba(232,75,90,0.2); }
    .qty-badge.empty  { background: var(--tf-surface2); color: var(--tf-text-m); }

    /* Qty input */
    .tf-qty-input {
        width: 100px; padding: 9px 12px;
        border: 1.5px solid var(--tf-border); border-radius: 10px;
        text-align: center; font-size: 14px; font-weight: 800;
        font-family: 'Cairo', sans-serif; color: var(--tf-text-h);
        outline: none; transition: all 0.25s;
    }
    .tf-qty-input:focus { border-color: var(--tf-blue); box-shadow: 0 0 0 3px rgba(58,142,240,0.1); }
    .tf-qty-input.over  { border-color: var(--tf-red) !important; background: var(--tf-red-soft) !important; }
    .tf-qty-input.ok    { border-color: var(--tf-green); }

    .qty-warn {
        font-size: 10px; font-weight: 800; color: var(--tf-red);
        margin-top: 4px; display: flex; align-items: center; gap: 4px;
    }

    /* Delete btn */
    .tf-del-btn {
        width: 34px; height: 34px; border-radius: 9px;
        background: var(--tf-red-soft); color: var(--tf-red);
        border: 1px solid rgba(232,75,90,0.18);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 13px;
        transition: all 0.25s; flex-shrink: 0;
    }
    .tf-del-btn:hover {
        background: var(--tf-surface);
        color: var(--tf-surface);
        box-shadow: 0 4px 14px rgba(232,75,90,0.35);
        transform: scale(1.08);
    }

    /* ── Add Product Btn ── */
    .tf-add-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 9px 18px; border-radius: 12px;
        background: var(--tf-green-soft); color: var(--tf-green);
        border: 1.5px solid rgba(15,170,126,0.25);
        font-size: 13px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif;
        transition: all 0.25s ease;
    }
    .tf-add-btn:hover:not(:disabled) {
        background: var(--tf-green); color: var(--tf-surface);
        box-shadow: 0 6px 20px rgba(15,170,126,0.3);
        transform: translateY(-1px);
    }
    .tf-add-btn:disabled { opacity: 0.45; cursor: not-allowed; }

    /* ── Empty State ── */
    .tf-empty {
        display: flex; flex-direction: column; align-items: center;
        padding: 44px 24px; gap: 12px;
    }
    .tf-empty-icon {
        width: 64px; height: 64px; border-radius: 18px;
        display: flex; align-items: center; justify-content: center; font-size: 24px;
    }
    .tf-empty-title { font-size: 14px; font-weight: 800; color: var(--tf-text-b); }
    .tf-empty-sub   { font-size: 12px; color: var(--tf-text-m); font-weight: 600; text-align: center; }

    /* ── Summary Row ── */
    .tf-summary {
        display: flex; align-items: center; gap: 16px;
        flex-wrap: wrap;
    }
    .tf-summary-pill {
        display: flex; flex-direction: column; align-items: center;
        padding: 12px 22px; border-radius: 16px;
        border: 1.5px solid var(--tf-border);
        min-width: 100px;
        transition: all 0.25s;
    }
    .tf-summary-pill:hover { border-color: rgba(79,99,210,0.3); box-shadow: 0 4px 14px rgba(79,99,210,0.1); }
    .tf-summary-num {
        font-size: 26px; font-weight: 900; line-height: 1;
        font-family: 'Cairo', sans-serif;
    }
    .tf-summary-lbl { font-size: 11px; font-weight: 700; color: var(--tf-text-m); margin-top: 4px; }

    /* Validation hint */
    .tf-val-hint {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 16px; border-radius: 12px;
        background: var(--tf-red-soft); color: var(--tf-red);
        border: 1px solid rgba(232,75,90,0.2);
        font-size: 12px; font-weight: 700; flex: 1;
    }

    /* Buttons */
    .tf-btn-cancel {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 11px 22px; border-radius: 12px;
        background: var(--tf-surface2); color: var(--tf-text-b);
        border: 1.5px solid var(--tf-border);
        font-size: 13px; font-weight: 800; text-decoration: none;
        font-family: 'Cairo', sans-serif; cursor: pointer;
        transition: all 0.25s;
    }
    .tf-btn-cancel:hover { background: var(--tf-border-soft); color: var(--tf-text-h); }

    .tf-btn-submit {
        display: inline-flex; align-items: center; gap: 10px;
        padding: 12px 28px; border-radius: 14px;
        background: linear-gradient(135deg, var(--tf-green), #0d8a6e);
        color: var(--tf-surface);
        font-size: 14px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif;
        box-shadow: 0 6px 22px rgba(15,170,126,0.35);
        transition: all 0.3s ease;
    }
    .tf-btn-submit:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 32px rgba(15,170,126,0.45);
    }
    .tf-btn-submit:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

    .tf-spinner {
        width: 18px; height: 18px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: var(--tf-surface);
        border-radius: 50%;
        animation: tfSpin 0.7s linear infinite;
        flex-shrink: 0;
    }

    /* ── Info box ── */
    .tf-info-box {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 14px 18px; border-radius: 14px;
        background: var(--tf-blue-soft);
        border: 1px solid rgba(58,142,240,0.2);
        margin-top: 16px;
        font-size: 13px; color: var(--tf-blue-dark); font-weight: 600;
    }
    .tf-info-box i { margin-top: 1px; flex-shrink: 0; font-size: 16px; color: var(--tf-blue); }

    /* ── Alert Errors ── */
    .tf-errors {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 16px 18px; border-radius: 16px;
        background: var(--tf-red-soft);
        border: 1px solid rgba(232,75,90,0.2);
        margin-bottom: 20px;
        animation: tfFadeUp 0.4s ease both;
    }
    .tf-errors i { color: var(--tf-red); margin-top: 2px; flex-shrink: 0; }
    .tf-errors ul { margin: 8px 0 0; padding-right: 18px; }
    .tf-errors ul li { font-size: 13px; color: var(--tf-red-dark); font-weight: 600; margin-bottom: 3px; }

    /* ── Table total footer ── */
    .tf-tfoot td {
        padding: 13px 16px;
        background: var(--tf-surface);
        border-top: 1.5px solid var(--tf-border-soft);
        font-weight: 700;
        font-size: 13px;
        color: var(--tf-text-b);
    }
    .tf-total-badge {
        display: inline-block;
        background: linear-gradient(135deg, var(--tf-blue), var(--tf-indigo-light));
        color: var(--tf-surface); padding: 5px 16px; border-radius: 50px;
        font-size: 14px; font-weight: 900;
        box-shadow: 0 4px 12px rgba(58,142,240,0.35);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .tf-summary { gap: 10px; }
        .tf-summary-pill { padding: 10px 14px; min-width: 80px; }
        .tf-summary-num { font-size: 20px; }
        .tf-btn-submit { padding: 10px 20px; font-size: 13px; }
    }
</style>
@endpush

@section('content')
<div x-data="transferForm()">

    {{-- ── Errors ── --}}
    @if($errors->any())
    <div class="tf-errors">
        <i class="fas fa-exclamation-circle fa-lg"></i>
        <div>
            <p style="font-weight:800;color:var(--tf-red-dark);font-size:14px;margin:0;">يوجد أخطاء في النموذج</p>
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form action="{{ route('transfers.store') }}"
          method="POST"
          @submit.prevent="validateForm"
          x-ref="transferForm"
          id="transferForm">
        @csrf

        {{-- ════════════════════════════
             1. معلومات التحويل
        ════════════════════════════ --}}
        <div class="tf-card tf-section">
            <div class="tf-card-head">
                <div class="tf-card-head-left">
                    <div class="tf-head-icon blue"><i class="fas fa-info-circle"></i></div>
                    <div>
                        <h3 class="tf-head-title">معلومات التحويل</h3>
                        <p class="tf-head-sub">حدد المخازن والتاريخ</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                    {{-- المخزن المصدر --}}
                    <div>
                        <label class="tf-label">
                            <i class="fas fa-warehouse" style="color:var(--tf-blue);margin-left:5px;"></i>
                            المخزن المصدر <span class="req">*</span>
                        </label>
                        <select name="from_warehouse_id"
                                x-model="fromWarehouse"
                                @change="onWarehouseChange('from')"
                                required
                                class="tf-select">
                            <option value="">— اختر المخزن المصدر —</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                            @endforeach
                        </select>
                        <p class="tf-hint"><i class="fas fa-arrow-up" style="font-size:9px;"></i> سيتم السحب منه</p>
                    </div>

                    {{-- المخزن الوجهة --}}
                    <div>
                        <label class="tf-label">
                            <i class="fas fa-warehouse" style="color:var(--tf-green);margin-left:5px;"></i>
                            المخزن الوجهة <span class="req">*</span>
                        </label>
                        <select name="to_warehouse_id"
                                x-model="toWarehouse"
                                required
                                class="tf-select green">
                            <option value="">— اختر المخزن الوجهة —</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                            @endforeach
                        </select>
                        <p class="tf-hint"><i class="fas fa-arrow-down" style="font-size:9px;"></i> سيتم الإضافة إليه</p>
                    </div>

                    {{-- التاريخ --}}
                    <div>
                        <label class="tf-label">
                            <i class="fas fa-calendar-alt" style="color:var(--tf-violet);margin-left:5px;"></i>
                            تاريخ التحويل <span class="req">*</span>
                        </label>
                        <input type="date"
                               name="transfer_date"
                               value="{{ old('transfer_date', date('Y-m-d')) }}"
                               max="{{ date('Y-m-d') }}"
                               required
                               class="tf-input violet">
                        <p class="tf-hint"><i class="fas fa-clock" style="font-size:9px;"></i> تاريخ تنفيذ التحويل</p>
                    </div>

                </div>

                {{-- Warning: same warehouse --}}
                <div x-show="fromWarehouse && toWarehouse && fromWarehouse === toWarehouse"
                     x-cloak x-transition
                     class="tf-warn">
                    <i class="fas fa-exclamation-triangle fa-lg" style="flex-shrink:0;"></i>
                    <span>لا يمكن التحويل من نفس المخزن إلى نفسه!</span>
                </div>

                {{-- ملاحظات --}}
                <div class="mt-5">
                    <label class="tf-label">
                        <i class="fas fa-sticky-note" style="color:var(--tf-amber);margin-left:5px;"></i>
                        ملاحظات
                        <span style="font-weight:500;color:var(--tf-text-m);font-size:11px;"> (اختياري)</span>
                    </label>
                    <textarea name="notes"
                              class="tf-textarea"
                              placeholder="أضف أي ملاحظات إضافية حول هذا التحويل...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════
             2. المنتجات
        ════════════════════════════ --}}
        <div class="tf-card tf-section">
            <div class="tf-card-head">
                <div class="tf-card-head-left">
                    <div class="tf-head-icon green"><i class="fas fa-boxes"></i></div>
                    <div>
                        <h3 class="tf-head-title">المنتجات</h3>
                        <p class="tf-head-sub">حدد المنتجات والكميات المراد نقلها</p>
                    </div>
                </div>
                <button type="button"
                        @click="addProduct()"
                        :disabled="!canAddProduct()"
                        class="tf-add-btn">
                    <i class="fas fa-plus"></i>
                    إضافة منتج
                </button>
            </div>

            <div class="p-6">

                {{-- اختر المخزن أولاً --}}
                <div x-show="!fromWarehouse" x-cloak x-transition>
                    <div class="tf-empty">
                        <div class="tf-empty-icon" style="background:var(--tf-amber-soft);color:var(--tf-amber);">
                            <i class="fas fa-hand-point-up"></i>
                        </div>
                        <p class="tf-empty-title">اختر المخزن المصدر أولاً</p>
                        <p class="tf-empty-sub">لا يمكن إضافة منتجات قبل<br>اختيار المخزن المصدر</p>
                    </div>
                </div>

                {{-- لا توجد منتجات --}}
                <div x-show="fromWarehouse && getAvailableProductsCount() === 0" x-cloak x-transition>
                    <div class="tf-empty">
                        <div class="tf-empty-icon" style="background:var(--tf-red-soft);color:var(--tf-red);">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <p class="tf-empty-title">لا توجد منتجات متاحة</p>
                        <p class="tf-empty-sub">المخزن المصدر لا يحتوي على<br>أي منتجات بكمية متاحة</p>
                    </div>
                </div>

                {{-- Products area --}}
                <div x-show="fromWarehouse && getAvailableProductsCount() > 0" x-cloak>

                    {{-- لم تضف منتجات بعد --}}
                    <div x-show="items.length === 0" x-transition>
                        <div class="tf-empty">
                            <div class="tf-empty-icon" style="background:var(--tf-blue-soft);color:var(--tf-blue);">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <p class="tf-empty-title">لم تضف أي منتجات بعد</p>
                            <p class="tf-empty-sub">ابدأ بإضافة المنتجات المراد نقلها</p>
                            <button type="button" @click="addProduct()" class="tf-add-btn" style="margin-top:4px;">
                                <i class="fas fa-plus"></i> إضافة أول منتج
                            </button>
                        </div>
                    </div>

                    {{-- جدول المنتجات --}}
                    <div x-show="items.length > 0" x-transition class="overflow-x-auto">
                        <table class="tf-table">
                            <thead>
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>المنتج</th>
                                    <th style="text-align:center;width:140px;">المتاح في المخزن</th>
                                    <th style="text-align:center;width:150px;">الكمية المنقولة</th>
                                    <th>ملاحظات</th>
                                    <th style="text-align:center;width:60px;">حذف</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="'item-'+index">
                                    <tr>
                                        {{-- # --}}
                                        <td>
                                            <div class="row-num" x-text="index + 1"></div>
                                        </td>

                                        {{-- المنتج --}}
                                        <td>
                                            <select :name="'items['+index+'][product_id]'"
                                                    x-model="item.product_id"
                                                    @change="updateProductInfo(index)"
                                                    required
                                                    class="tf-select"
                                                    style="min-width:180px;">
                                                <option value="">— اختر المنتج —</option>
                                                <template x-for="product in getAvailableProducts()" :key="product.id">
                                                    <option :value="product.id"
                                                            x-text="product.name + (product.sku ? ' · ' + product.sku : '')"
                                                            :disabled="isProductSelected(product.id, index)">
                                                    </option>
                                                </template>
                                            </select>
                                        </td>

                                        {{-- المتاح --}}
                                        <td style="text-align:center;">
                                            <span class="qty-badge"
                                                  :class="{
                                                    'high':   item.available !== null && item.available >= 10,
                                                    'medium': item.available !== null && item.available > 0 && item.available < 10,
                                                    'low':    item.available !== null && item.available <= 0,
                                                    'empty':  item.available === null
                                                  }"
                                                  x-text="item.available !== null ? parseFloat(item.available).toFixed(2) : '—'">
                                            </span>
                                        </td>

                                        {{-- الكمية --}}
                                        <td style="text-align:center;">
                                            <input type="number"
                                                   :name="'items['+index+'][quantity]'"
                                                   x-model.number="item.quantity"
                                                   :max="item.available"
                                                   min="0.01" step="0.01" required
                                                   class="tf-qty-input"
                                                   :class="{
                                                     'over': item.available !== null && item.quantity > item.available,
                                                     'ok':   item.available !== null && item.quantity > 0 && item.quantity <= item.available
                                                   }">
                                            <div class="qty-warn"
                                                 x-show="item.available !== null && item.quantity > item.available"
                                                 x-cloak x-transition>
                                                <i class="fas fa-exclamation-circle"></i> تتجاوز المتاح!
                                            </div>
                                        </td>

                                        {{-- ملاحظات --}}
                                        <td>
                                            <input type="text"
                                                   :name="'items['+index+'][notes]'"
                                                   x-model="item.notes"
                                                   placeholder="ملاحظات..."
                                                   class="tf-input"
                                                   style="min-width:130px;">
                                        </td>

                                        {{-- حذف --}}
                                        <td style="text-align:center;">
                                            <button type="button"
                                                    @click="removeProduct(index)"
                                                    class="tf-del-btn">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>

                            {{-- Footer total --}}
                            <tfoot x-show="items.length > 0" class="tf-tfoot">
                                <tr>
                                    <td colspan="3" style="text-align:right;">
                                        <i class="fas fa-calculator" style="margin-left:6px;color:var(--tf-text-m);"></i>
                                        إجمالي الكميات
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="tf-total-badge" x-text="totalQuantity()"></span>
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        {{-- ════════════════════════════
             3. الملخص وأزرار
        ════════════════════════════ --}}
        <div class="tf-card tf-section">
            <div class="p-6">

                <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">

                    {{-- Summary pills --}}
                    <div class="tf-summary">
                        <div class="tf-summary-pill">
                            <span class="tf-summary-num" style="color:var(--tf-blue);" x-text="items.length">0</span>
                            <span class="tf-summary-lbl">منتج</span>
                        </div>
                        <div class="tf-summary-pill">
                            <span class="tf-summary-num" style="color:var(--tf-green);" x-text="totalQuantity()">0</span>
                            <span class="tf-summary-lbl">إجمالي الكمية</span>
                        </div>
                    </div>

                    {{-- Validation message --}}
                    <div class="flex-1" style="min-width:180px;">
                        <div x-show="!canSubmit()" x-cloak x-transition class="tf-val-hint">
                            <i class="fas fa-exclamation-triangle"></i>
                            <template x-if="!fromWarehouse || !toWarehouse">
                                <span>اختر المخازن أولاً</span>
                            </template>
                            <template x-if="fromWarehouse && toWarehouse && fromWarehouse === toWarehouse">
                                <span>لا يمكن التحويل لنفس المخزن</span>
                            </template>
                            <template x-if="fromWarehouse && toWarehouse && fromWarehouse !== toWarehouse && items.length === 0">
                                <span>أضف منتجاً واحداً على الأقل</span>
                            </template>
                            <template x-if="items.length > 0 && items.some(i => i.available !== null && i.quantity > i.available)">
                                <span>بعض الكميات تتجاوز المتاح</span>
                            </template>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div style="display:flex;gap:10px;align-items:center;flex-shrink:0;">
                        <a href="{{ route('transfers.index') }}" class="tf-btn-cancel">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                        <button type="submit"
                                :disabled="!canSubmit() || isSubmitting"
                                class="tf-btn-submit">
                            <template x-if="!isSubmitting">
                                <i class="fas fa-check-circle"></i>
                            </template>
                            <template x-if="isSubmitting">
                                <div class="tf-spinner"></div>
                            </template>
                            <span x-text="isSubmitting ? 'جاري التنفيذ...' : 'تنفيذ التحويل'"></span>
                        </button>
                    </div>

                </div>

                {{-- Info box --}}
                <div class="tf-info-box">
                    <i class="fas fa-lightbulb"></i>
                    <div>
                        <strong style="display:block;margin-bottom:3px;">ملاحظة هامة</strong>
                        سيتم تنفيذ التحويل مباشرةً وتحديث المخزون في كلا المخزنين فوراً. تأكد من صحة البيانات قبل التنفيذ.
                    </div>
                </div>

            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
function transferForm() {
    return {
        fromWarehouse:    '',
        toWarehouse:      '',
        items:            [],
        oldFromWarehouse: '',
        isInitializing:   true,
        isSubmitting:     false,

        init() {
            const oldFrom = '{{ old("from_warehouse_id", "") }}';
            const oldTo   = '{{ old("to_warehouse_id", "") }}';
            if (oldFrom) { this.fromWarehouse = oldFrom; this.oldFromWarehouse = oldFrom; }
            if (oldTo)   { this.toWarehouse   = oldTo; }

            @if(old('items'))
            try {
                const oldItems = @json(old('items'));
                if (Array.isArray(oldItems) && oldItems.length > 0) {
                    oldItems.forEach(item => {
                        this.items.push({
                            product_id: String(item.product_id || ''),
                            quantity:   parseFloat(item.quantity) || 1,
                            available:  null,
                            notes:      String(item.notes || '')
                        });
                    });
                    this.$nextTick(() => {
                        this.items.forEach((item, index) => {
                            if (item.product_id) this.updateProductInfo(index);
                        });
                    });
                }
            } catch(e) { console.error(e); }
            @endif

            setTimeout(() => { this.isInitializing = false; }, 200);
        },

        onWarehouseChange(type) {
            if (this.isInitializing) return;
            if (type === 'from' && this.items.length > 0) {
                if (!confirm('⚠️ تغيير المخزن المصدر سيؤدي لحذف جميع المنتجات المضافة.\n\nهل تريد المتابعة؟')) {
                    this.$nextTick(() => { this.fromWarehouse = this.oldFromWarehouse; });
                    return;
                }
                this.items = [];
            }
            this.oldFromWarehouse = this.fromWarehouse;
        },

        addProduct() {
            if (!this.canAddProduct()) { alert('يرجى اختيار المخزن المصدر أولاً'); return; }
            this.items.push({ product_id: '', quantity: 1, available: null, notes: '' });
        },

        removeProduct(index) {
            if (!confirm('هل تريد حذف هذا المنتج من قائمة التحويل؟')) return;
            this.items.splice(index, 1);
        },

        canAddProduct() {
            return this.fromWarehouse && this.getAvailableProductsCount() > 0;
        },

        getAvailableProducts() {
            if (!this.fromWarehouse) return [];
            try {
                const stock    = @json($warehousesStock ?? []);
                const products = @json($products ?? []);
                const wStock   = stock[this.fromWarehouse] || {};
                return products
                    .map(p => {
                        const s = wStock[p.id];
                        return (s && parseFloat(s.available) > 0)
                            ? { id: p.id, name: p.name || 'منتج', sku: p.sku || p.code || '', available: parseFloat(s.available) }
                            : null;
                    })
                    .filter(Boolean);
            } catch(e) { return []; }
        },

        getAvailableProductsCount() { return this.getAvailableProducts().length; },

        isProductSelected(productId, currentIndex) {
            return this.items.some((item, idx) => idx !== currentIndex && String(item.product_id) === String(productId));
        },

        updateProductInfo(index) {
            if (!this.items[index]) return;
            const productId = this.items[index].product_id;
            if (!this.fromWarehouse || !productId) { this.items[index].available = null; return; }
            try {
                const stock  = @json($warehousesStock ?? []);
                const wStock = stock[this.fromWarehouse] || {};
                if (wStock[productId]) {
                    const avail = parseFloat(wStock[productId].available);
                    this.items[index].available = avail;
                    const cur = parseFloat(this.items[index].quantity) || 0;
                    if (cur === 0 || cur > avail) this.items[index].quantity = Math.min(1, avail);
                } else {
                    this.items[index].available = 0;
                }
            } catch(e) { this.items[index].available = 0; }
        },

        totalQuantity() {
            return this.items.reduce((s, i) => s + (parseFloat(i.quantity) || 0), 0).toFixed(2);
        },

        canSubmit() {
            if (!this.fromWarehouse || !this.toWarehouse) return false;
            if (this.fromWarehouse === this.toWarehouse) return false;
            if (this.items.length === 0) return false;
            return this.items.every(item => {
                const qty   = parseFloat(item.quantity) || 0;
                const avail = parseFloat(item.available);
                return item.product_id && qty > 0 && item.available !== null && qty <= avail;
            });
        },

        validateForm() {
            if (this.isSubmitting) return;
            if (!this.canSubmit()) {
                alert('يرجى التأكد من إدخال جميع البيانات المطلوبة بشكل صحيح');
                return;
            }
            const confirmed = confirm(
                `هل أنت متأكد من تنفيذ التحويل؟\n\n` +
                `عدد المنتجات: ${this.items.length}\n` +
                `إجمالي الكمية: ${this.totalQuantity()}\n` +
                `من: ${this.getWarehouseName(this.fromWarehouse)}\n` +
                `إلى: ${this.getWarehouseName(this.toWarehouse)}\n\n` +
                `سيتم تحديث المخزون مباشرة بعد التنفيذ.`
            );
            if (!confirmed) return;
            this.isSubmitting = true;
            this.$refs.transferForm.submit();
        },

        getWarehouseName(id) {
            if (!id) return '';
            const list = @json($warehouses ?? []);
            const w    = list.find(w => String(w.id) === String(id));
            return w ? w.name : 'غير معروف';
        }
    };
}
</script>
@endpush