@extends('layouts.app')

@section('title', 'طباعة الباركود')
@section('page-title', 'طباعة الباركود')

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
    @keyframes iconBounce {
        0%,100% { transform: translateY(0) rotate(0deg); }
        30%     { transform: translateY(-4px) rotate(-8deg); }
        60%     { transform: translateY(-2px) rotate(4deg); }
    }

    .tf-section { animation: tfFadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }

    /* ── Header ── */
    .tf-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 22px; flex-wrap: wrap; gap: 16px;
    }
    .tf-title-row { display: flex; flex-direction: column; gap: 4px; }
    .tf-title { font-size: 22px; font-weight: 900; color: var(--tf-text-h); margin: 0; }
    .tf-subtitle { font-size: 13px; color: var(--tf-text-m); font-weight: 600; }

    .tf-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 20px; border-radius: 14px;
        font-size: 13px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif;
        text-decoration: none; border: none;
        transition: all .25s;
    }
    .tf-btn-primary {
        background: var(--tf-blue); color: var(--tf-surface);
        box-shadow: 0 4px 16px rgba(58,142,240,0.35);
    }
    .tf-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(58,142,240,0.45); }
    .tf-btn-secondary {
        background: var(--tf-surface2); color: var(--tf-text-b);
        border: 1.5px solid var(--tf-border);
    }
    .tf-btn-secondary:hover { background: var(--tf-border-soft); }

    /* ── Card ── */
    .tf-card {
        background: var(--tf-surface); border-radius: 20px;
        border: 1px solid var(--tf-border);
        overflow: hidden; box-shadow: var(--tf-shadow-card);
    }

    .tf-card-body { padding: 24px; }

    /* ── Barcode Grid ── */
    .tf-barcode-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
    @media (max-width: 1200px) { .tf-barcode-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) { .tf-barcode-grid { grid-template-columns: repeat(2, 1fr); } }

    .tf-barcode-item {
        border: 2px dashed var(--tf-border);
        border-radius: 14px;
        padding: 16px;
        text-align: center;
        transition: all .3s;
    }
    .tf-barcode-item:hover {
        border-color: var(--tf-indigo);
        box-shadow: var(--tf-shadow-md);
    }

    .tf-product-name { font-size: 13px; font-weight: 800; color: var(--tf-text-h); margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tf-product-sku { font-size: 10px; color: var(--tf-text-m); margin-bottom: 10px; }

    .tf-barcode-img {
        background: var(--tf-surface);
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 10px;
    }
    .tf-barcode-img img { height: 50px; margin: 0 auto; }
    .tf-barcode-code { font-size: 10px; font-family: monospace; color: var(--tf-text-b); margin-top: 4px; }

    .tf-no-barcode {
        height: 50px; background: var(--tf-surface2);
        border-radius: 10px; display: flex;
        align-items: center; justify-content: center;
        margin-bottom: 10px;
    }
    .tf-no-barcode span { font-size: 11px; color: var(--tf-text-m); }

    .tf-price {
        font-size: 18px; font-weight: 900;
        color: var(--tf-blue); font-family: 'Cairo', sans-serif;
    }

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

    /* ── Print Styles ── */
    @media print {
        body * { visibility: hidden; }
        #barcode-grid, #barcode-grid * { visibility: visible; }
        #barcode-grid { position: absolute; left: 0; top: 0; width: 100%; }
        .tf-barcode-item { page-break-inside: avoid; border: 1px solid #000; padding: 10px; margin: 5px; }
        header, .mb-6, button, a { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="tf-page">

    {{-- Header --}}
    <div class="tf-header tf-section">
        <div class="tf-title-row">
            <h2 class="tf-title">🏷️ طباعة الباركود</h2>
            <p class="tf-subtitle">اطبع باركود للمنتجات المحددة</p>
        </div>
        <div style="display:flex;gap:10px;">
            <button onclick="window.print()" class="tf-btn tf-btn-primary">
                <i class="fas fa-print"></i> طباعة
            </button>
            <a href="{{ route('products.index') }}" class="tf-btn tf-btn-secondary">
                <i class="fas fa-arrow-right"></i> رجوع
            </a>
        </div>
    </div>

    {{-- Barcode List --}}
    <div class="tf-card tf-section">
        <div class="tf-card-body">
            @forelse($products as $product)
            <div class="tf-barcode-item">
                <div>
                    <h3 class="tf-product-name">{{ $product->name }}</h3>
                    <p class="tf-product-sku">SKU: {{ $product->sku ?? 'N/A' }}</p>
                </div>
                
                <div class="tf-barcode-img">
                    @if($product->barcode)
                        <img src="https://barcode.tec-it.com/barcode.ashx?data={{ $product->barcode }}&code=Code128&translate-esc=on" alt="Barcode">
                        <p class="tf-barcode-code">{{ $product->barcode }}</p>
                    @else
                        <div class="tf-no-barcode">
                            <span>لا يوجد باركود</span>
                        </div>
                    @endif
                </div>
                
                <div>
                    <p class="tf-price">{{ number_format($product->sale_price ?? 0, 2) }} ج.م</p>
                </div>
            </div>
            @empty
            <div class="tf-empty">
                <div class="tf-empty-icon"><i class="fas fa-box-open"></i></div>
                <h3 class="tf-empty-title">لا توجد منتجات</h3>
                <p class="tf-empty-sub">اختر منتجات لطباعة الباركود الخاص بها</p>
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection