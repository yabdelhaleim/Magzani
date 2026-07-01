@extends('layouts.app')

@section('title', 'تفاصيل الخامة')
@section('page-title', 'تفاصيل الخامة')

@push('styles')
<style>
    :root {
        --tf-bg: #f4f7fe; --tf-surface: #ffffff; --tf-border: #e4eaf7;
        --tf-indigo: #4f63d2; --tf-blue: #3a8ef0; --tf-green: #0faa7e;
        --tf-red: #dc2626; --tf-amber: #e8930a;
        --tf-text-h: #1a2140; --tf-text-b: #3d4f72; --tf-text-m: #7e90b0;
    }

    .mfg-page { background: var(--tf-bg); min-height: 100vh; padding: 16px; }
    @media (min-width: 1024px) { .mfg-page { padding: 26px 22px; } }

    .mfg-title {
        font-size: 20px; font-weight: 900; color: var(--tf-text-h);
        display: flex; align-items: center; gap: 12px; margin-bottom: 20px;
    }
    @media (min-width: 768px) { .mfg-title { font-size: 24px; } }
    .mfg-title i { color: var(--tf-indigo); }

    .mfg-card {
        background: var(--tf-surface); border-radius: 16px;
        border: 1px solid var(--tf-border); overflow: hidden; margin-bottom: 16px;
    }
    .mfg-card-header {
        padding: 12px 16px; border-bottom: 1px solid var(--tf-border);
        display: flex; align-items: center; gap: 10px;
    }
    .mfg-card-title { font-size: 14px; font-weight: 800; margin: 0; }
    .mfg-card-body { padding: 16px; }
    @media (min-width: 768px) { .mfg-card-body { padding: 22px; } }

    .btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; padding: 8px 16px; border-radius: 10px; font-weight: 700;
        font-size: 13px; border: none; cursor: pointer; transition: all .3s;
        text-decoration: none;
    }
    .btn-primary { background: var(--tf-indigo); color: #fff; }
    .btn-amber { background: var(--tf-amber); color: #fff; }

    .detail-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 0; border-bottom: 1px solid var(--tf-border);
        font-size: 14px;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { color: var(--tf-text-m); font-weight: 700; }
    .detail-value { color: var(--tf-text-h); font-weight: 800; }

    .grid-2 { display: grid; gap: 12px; }
    @media (min-width: 768px) { .grid-2 { grid-template-columns: repeat(2, 1fr); gap: 20px; } }
</style>
@endpush

@section('content')
<div class="mfg-page">
    <div class="mfg-title">
        <i class="fas fa-boxes-stacked"></i>
        تفاصيل الخامة
    </div>

    <div class="grid-2">
        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-box" style="color:var(--tf-blue);"></i>
                <h3 class="mfg-card-title">بيانات الخامة</h3>
            </div>
            <div class="mfg-card-body">
                <div class="detail-row">
                    <span class="detail-label">اسم الخامة</span>
                    <span class="detail-value">{{ $template->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">المخزن</span>
                    <span class="detail-value">
                        @if($template->warehouse)
                            <a href="{{ route('warehouses.show', $template->warehouse_id) }}" style="color:var(--tf-indigo);">
                                {{ $template->warehouse->name }}
                            </a>
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">الكمية</span>
                    <span class="detail-value">{{ number_format($template->quantity) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">سعر الشراء</span>
                    <span class="detail-value" style="color:var(--tf-green);">{{ number_format($template->buy_price, 2) }} ج.م</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">سعر البيع</span>
                    <span class="detail-value" style="color:var(--tf-red);">{{ number_format($template->sale_price, 2) }} ج.م</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">تاريخ الإنشاء</span>
                    <span class="detail-value">{{ $template->created_at->format('Y-m-d') }}</span>
                </div>
            </div>
        </div>

        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-cog" style="color:var(--tf-amber);"></i>
                <h3 class="mfg-card-title">إجراءات</h3>
            </div>
            <div class="mfg-card-body" style="display:flex; flex-direction:column; gap:10px;">
                <a href="{{ route('manufacturing-orders.raw-materials.edit', $template->id) }}" class="btn btn-amber" style="width:100%;">
                    <i class="fas fa-edit"></i> تعديل الخامة
                </a>
                <a href="{{ route('manufacturing-orders.raw-materials.index') }}" class="btn btn-primary" style="width:100%;">
                    <i class="fas fa-arrow-right"></i> العودة للقائمة
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
