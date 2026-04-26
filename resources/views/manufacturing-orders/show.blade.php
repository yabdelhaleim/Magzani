@extends('layouts.app')

@section('title', 'تفاصيل أمر التصنيع')
@section('page-title', 'تفاصيل أمر التصنيع')

@push('styles')
<style>
    :root {
        --tf-bg: #f4f7fe; --tf-surface: #ffffff; --tf-border: #e4eaf7;
        --tf-indigo: #4f63d2; --tf-blue: #3a8ef0; --tf-green: #0faa7e;
        --tf-red: #dc2626; --tf-amber: #e8930a;
        --tf-text-h: #1a2140; --tf-text-b: #3d4f72; --tf-text-m: #7e90b0;
    }

    .mfg-page {
        background: var(--tf-bg);
        min-height: 100vh;
        padding: 16px;
    }

    @media (min-width: 1024px) {
        .mfg-page { padding: 26px 22px; }
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .mfg-section { animation: fadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }

    .mfg-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }

    @media (min-width: 768px) {
        .mfg-header { margin-bottom: 24px; gap: 16px; }
    }

    .mfg-title {
        font-size: 18px;
        font-weight: 900;
        color: var(--tf-text-h);
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    @media (min-width: 768px) {
        .mfg-title { font-size: 24px; gap: 12px; }
    }

    .mfg-title i { color: var(--tf-indigo); }

    .mfg-card {
        background: var(--tf-surface);
        border-radius: 16px;
        border: 1px solid var(--tf-border);
        overflow: hidden;
        margin-bottom: 16px;
    }

    @media (min-width: 768px) {
        .mfg-card { border-radius: 18px; margin-bottom: 20px; }
    }

    .mfg-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid var(--tf-border);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    @media (min-width: 768px) {
        .mfg-card-header { padding: 16px 22px; }
    }

    .mfg-card-title {
        font-size: 14px;
        font-weight: 800;
        margin: 0;
    }

    @media (min-width: 768px) {
        .mfg-card-title { font-size: 16px; }
    }

    .mfg-card-body {
        padding: 16px;
    }

    @media (min-width: 768px) {
        .mfg-card-body { padding: 22px; }
    }

    /* Badge */
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    @media (min-width: 768px) {
        .badge { padding: 4px 12px; font-size: 12px; }
    }

    .badge-draft { background: #fff4e0; color: #b45309; }
    .badge-confirmed { background: #e6f8f3; color: #047857; }
    .badge-completed { background: #dbeafe; color: #1e40af; }

    /* Table */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .mfg-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    @media (min-width: 768px) {
        .mfg-table { font-size: 14px; }
    }

    .mfg-table th {
        padding: 10px 12px;
        font-size: 11px;
        font-weight: 700;
        color: var(--tf-text-m);
        background: #f8faff;
        text-align: right;
        border-bottom: 1px solid var(--tf-border);
        white-space: nowrap;
    }

    @media (min-width: 768px) {
        .mfg-table th { padding: 12px 16px; font-size: 12px; }
    }

    .mfg-table td {
        padding: 10px 12px;
        font-size: 13px;
        color: var(--tf-text-b);
        border-bottom: 1px solid #f0f4f8;
    }

    @media (min-width: 768px) {
        .mfg-table td { padding: 12px 16px; font-size: 14px; }
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        border: none;
        cursor: pointer;
        transition: all .3s;
        text-decoration: none;
    }

    @media (min-width: 768px) {
        .btn { padding: 10px 20px; font-size: 14px; }
    }

    .btn-primary { background: var(--tf-indigo); color: #fff; }
    .btn-amber { background: var(--tf-amber); color: #fff; }
    .btn-red { background: var(--tf-red); color: #fff; }
    .btn-green { background: var(--tf-green); color: #fff; }
    .btn-sm { padding: 6px 12px; font-size: 12px; }
    .btn-block { width: 100%; }

    /* Grid */
    .grid-2 { display: grid; gap: 12px; }
    .grid-3 { display: grid; gap: 12px; }
    .grid-4 { display: grid; gap: 12px; }

    @media (min-width: 640px) {
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(2, 1fr); }
        .grid-4 { grid-template-columns: repeat(2, 1fr); }
    }

    @media (min-width: 1024px) {
        .grid-2 { grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .grid-3 { grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .grid-4 { grid-template-columns: repeat(4, 1fr); gap: 20px; }
    }

    /* Info rows */
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid var(--tf-border);
        font-size: 13px;
    }

    @media (min-width: 768px) {
        .info-row { padding: 12px 0; font-size: 14px; }
    }

    .info-row:last-child { border-bottom: none; }
    .info-label { color: var(--tf-text-m); font-weight: 600; }
    .info-value { color: var(--tf-text-h); font-weight: 700; text-align: left; }

    /* Stat box */
    .stat-box {
        background: #f8faff;
        padding: 12px;
        border-radius: 12px;
        text-align: center;
        border: 1px solid var(--tf-border);
    }

    @media (min-width: 768px) {
        .stat-box { padding: 16px; border-radius: 14px; }
    }

    .stat-label {
        font-size: 12px;
        color: var(--tf-text-m);
        margin-bottom: 6px;
    }

    @media (min-width: 768px) {
        .stat-label { font-size: 13px; margin-bottom: 8px; }
    }

    .stat-value {
        font-size: 18px;
        font-weight: 900;
        color: var(--tf-indigo);
    }

    @media (min-width: 768px) {
        .stat-value { font-size: 24px; }
    }

    /* Action buttons mobile */
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 12px 16px;
        border-top: 1px solid var(--tf-border);
        box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
        z-index: 100;
    }

    @media (min-width: 768px) {
        .action-buttons {
            position: static;
            flex-direction: row;
            background: transparent;
            padding: 0;
            border: none;
            box-shadow: none;
        }
    }

    /* Summary box */
    .summary-box {
        background: linear-gradient(135deg, var(--tf-indigo), #3b52c0);
        color: white;
        padding: 16px;
        border-radius: 12px;
    }

    @media (min-width: 768px) {
        .summary-box { padding: 20px; border-radius: 16px; }
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 13px;
    }

    @media (min-width: 768px) {
        .summary-row { padding: 10px 0; font-size: 14px; }
    }

    .summary-row.total {
        border-top: 1px solid rgba(255,255,255,0.3);
        padding-top: 12px;
        margin-top: 8px;
        font-size: 16px;
        font-weight: 900;
    }

    @media (min-width: 768px) {
        .summary-row.total { font-size: 18px; }
    }
</style>
@endpush

@section('content')
<div class="mfg-page">
    <div class="mfg-header mfg-section">
        <div class="mfg-title">
            <i class="fas fa-industry"></i>
            <span>أمر تصنيع #{{ $manufacturingOrder->id }}</span>
            <span class="badge badge-{{ $manufacturingOrder->status }}">
                @if($manufacturingOrder->status === 'draft')
                    مسودة
                @elseif($manufacturingOrder->status === 'confirmed')
                    مؤكد
                @elseif($manufacturingOrder->status === 'completed')
                    مكتمل
                @endif
            </span>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid-4 mfg-section" style="margin-bottom: 20px;">
        <div class="stat-box">
            <div class="stat-label">المنتج</div>
            <div class="stat-value" style="font-size: 14px;">{{ $manufacturingOrder->product_name }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">العدد</div>
            <div class="stat-value">{{ number_format($manufacturingOrder->quantity_produced) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">تاريخ الإنشاء</div>
            <div class="stat-value" style="font-size: 14px;">{{ $manufacturingOrder->created_at->format('Y-m-d') }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">الحالة</div>
            <div class="stat-value" style="font-size: 14px;">
                @if($manufacturingOrder->status === 'draft')
                    <span style="color:var(--tf-amber);">مسودة</span>
                @elseif($manufacturingOrder->status === 'confirmed')
                    <span style="color:var(--tf-green);">مؤكد</span>
                @elseif($manufacturingOrder->status === 'completed')
                    <span style="color:var(--tf-blue);">مكتمل</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Basic Info -->
    <div class="mfg-card mfg-section">
        <div class="mfg-card-header">
            <i class="fas fa-info-circle" style="color: var(--tf-blue);"></i>
            <h3 class="mfg-card-title">المعلومات الأساسية</h3>
        </div>
        <div class="mfg-card-body">
            <div class="info-row">
                <span class="info-label">اسم المنتج:</span>
                <span class="info-value">{{ $manufacturingOrder->product_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">المستودع:</span>
                <span class="info-value">{{ $manufacturingOrder->warehouse->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">العدد المنتج:</span>
                <span class="info-value">{{ number_format($manufacturingOrder->quantity_produced, 2) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">تاريخ الإنشاء:</span>
                <span class="info-value">{{ $manufacturingOrder->created_at->format('Y-m-d H:i') }}</span>
            </div>
            @if($manufacturingOrder->notes)
            <div class="info-row">
                <span class="info-label">ملاحظات:</span>
                <span class="info-value" style="font-weight: 400;">{{ $manufacturingOrder->notes }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Wood Components -->
    <div class="mfg-card mfg-section">
        <div class="mfg-card-header">
            <i class="fas fa-cubes" style="color: var(--tf-green);"></i>
            <h3 class="mfg-card-title">مكونات الخشب</h3>
        </div>
        <div class="mfg-card-body">
            <div class="table-responsive">
                <table class="mfg-table">
                    <thead>
                        <tr>
                            <th>النوع</th>
                            <th>السمك</th>
                            <th>العرض</th>
                            <th>الطول</th>
                            <th>العدد</th>
                            <th>السعر</th>
                            <th>التكلفة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($manufacturingOrder->components as $component)
                        <tr>
                            <td>{{ $component->wood_type }}</td>
                            <td>{{ number_format($component->thickness, 1) }} سم</td>
                            <td>{{ number_format($component->width, 1) }} سم</td>
                            <td>{{ number_format($component->length, 2) }} م</td>
                            <td>{{ number_format($component->quantity) }}</td>
                            <td>{{ number_format($component->unit_price, 2) }} ج.م</td>
                            <td><strong>{{ number_format($component->total_cost, 2) }} ج.م</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Additional Components -->
    @if($manufacturingOrder->additionalComponents && $manufacturingOrder->additionalComponents->count() > 0)
    <div class="mfg-card mfg-section">
        <div class="mfg-card-header">
            <i class="fas fa-tools" style="color: var(--tf-amber);"></i>
            <h3 class="mfg-card-title">مكونات إضافية</h3>
        </div>
        <div class="mfg-card-body">
            <div class="table-responsive">
                <table class="mfg-table">
                    <thead>
                        <tr>
                            <th>المكون</th>
                            <th>العدد</th>
                            <th>السعر</th>
                            <th>التكلفة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($manufacturingOrder->additionalComponents as $component)
                        <tr>
                            <td>{{ $component->component_name }}</td>
                            <td>{{ number_format($component->quantity) }}</td>
                            <td>{{ number_format($component->unit_price, 2) }} ج.م</td>
                            <td><strong>{{ number_format($component->total_cost, 2) }} ج.م</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Cost Summary -->
    <div class="mfg-card mfg-section">
        <div class="mfg-card-header">
            <i class="fas fa-calculator" style="color: var(--tf-indigo);"></i>
            <h3 class="mfg-card-title">ملخص التكاليف</h3>
        </div>
        <div class="mfg-card-body">
            <div class="grid-2">
                <div class="summary-box">
                    <div class="summary-row">
                        <span>تكلفة الخشب:</span>
                        <strong>{{ number_format($manufacturingOrder->total_wood_cost, 2) }} ج.م</strong>
                    </div>
                    <div class="summary-row">
                        <span>المكونات الإضافية:</span>
                        <strong>{{ number_format($manufacturingOrder->total_additional_cost, 2) }} ج.م</strong>
                    </div>
                    <div class="summary-row">
                        <span>تكلفة البالة:</span>
                        <strong>{{ number_format($manufacturingOrder->cost_per_pallet, 2) }} ج.م</strong>
                    </div>
                    <div class="summary-row total">
                        <span>الإجمالي الكلي:</span>
                        <strong>{{ number_format($manufacturingOrder->total_cost, 2) }} ج.م</strong>
                    </div>
                </div>

                <div class="summary-box" style="background: linear-gradient(135deg, var(--tf-green), #059669);">
                    <div class="summary-row">
                        <span>عدد البالات:</span>
                        <strong>{{ number_format($manufacturingOrder->quantity_produced, 2) }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>التكلفة لكل بالة:</span>
                        <strong>{{ number_format($manufacturingOrder->cost_per_pallet, 2) }} ج.م</strong>
                    </div>
                    <div class="summary-row">
                        <span>نسبة هامش الربح الموصى:</span>
                        <strong>{{ number_format($manufacturingOrder->recommended_profit_margin, 1) }}%</strong>
                    </div>
                    <div class="summary-row total">
                        <span>سعر البيع الموصى:</span>
                        <strong>{{ number_format($manufacturingOrder->recommended_selling_price, 2) }} ج.م</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons mfg-section">
        @if($manufacturingOrder->status === 'draft')
            <form method="POST" action="{{ route('manufacturing-orders.confirm', $manufacturingOrder->id) }}" style="display: inline;">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-amber btn-block">
                    <i class="fas fa-check"></i> تأكيد أمر التصنيع
                </button>
            </form>
        @elseif($manufacturingOrder->status === 'confirmed')
            <form method="POST" action="{{ route('manufacturing-orders.complete', $manufacturingOrder->id) }}" style="display: inline;">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-green btn-block">
                    <i class="fas fa-check-double"></i> إكمال التصنيع
                </button>
            </form>
        @endif

        <a href="{{ route('manufacturing-orders.edit', $manufacturingOrder->id) }}" class="btn btn-primary btn-block">
            <i class="fas fa-edit"></i> تعديل
        </a>

        <a href="{{ route('manufacturing-orders.index') }}" class="btn btn-red btn-block">
            <i class="fas fa-arrow-left"></i> عودة للقائمة
        </a>
    </div>
</div>
@endsection
