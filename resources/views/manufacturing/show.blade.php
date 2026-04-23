@extends('layouts.app')

@section('title', 'تفاصيل حساب التكلفة')
@section('page-title', 'تفاصيل حساب التكلفة')

@push('styles')
<style>
    :root {
        --tf-bg: #f4f7fe; --tf-surface: #ffffff; --tf-border: #e4eaf7;
        --tf-indigo: #4f63d2; --tf-blue: #3a8ef0; --tf-green: #0faa7e;
        --tf-red: #dc2626; --tf-amber: #e8930a; --tf-violet: #7c5cec;
        --tf-text-h: #1a2140; --tf-text-b: #3d4f72; --tf-text-m: #7e90b0;
        --tf-shadow-card: 0 2px 0 0 rgba(0,0,0,0.04), 0 4px 20px rgba(79,99,210,0.08);
    }
    .mfg-page { background: var(--tf-bg); min-height: 100vh; padding: 26px 22px; }
    @keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    .mfg-section { animation: fadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .mfg-section:nth-child(1) { animation-delay: 0.04s; }
    .mfg-section:nth-child(2) { animation-delay: 0.10s; }
    .mfg-section:nth-child(3) { animation-delay: 0.16s; }
    .mfg-section:nth-child(4) { animation-delay: 0.22s; }

    .mfg-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px; }
    .mfg-title { font-size:24px; font-weight:900; color:var(--tf-text-h); display:flex; align-items:center; gap:12px; }
    .mfg-title i { color:var(--tf-indigo); }
    .mfg-header-actions { display:flex; gap:8px; }

    .mfg-card {
        background: var(--tf-surface); border-radius: 18px;
        border: 1px solid var(--tf-border); overflow: hidden; margin-bottom: 20px;
    }
    .mfg-card-top {
        padding: 16px 22px; border-bottom: 1px solid var(--tf-border);
        display: flex; align-items: center; gap: 10px;
    }
    .mfg-card-top .icon-wrap {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; font-size: 16px;
    }
    .mfg-card-top h3 { font-size: 16px; font-weight: 800; margin: 0; }
    .mfg-card-body { padding: 22px; }
    .icon-blue { background: #e8f2ff; color: var(--tf-blue); }
    .icon-green { background: #e6f8f3; color: var(--tf-green); }
    .icon-violet { background: #f0ecff; color: var(--tf-violet); }
    .icon-amber { background: #fff4e0; color: var(--tf-amber); }

    .badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; }
    .badge-draft { background:#fff4e0; color:#b45309; }
    .badge-confirmed { background:#e6f8f3; color:#047857; }

    .mfg-table { width:100%; border-collapse:collapse; }
    .mfg-table th {
        padding:12px 16px; font-size:12px; font-weight:700; color:var(--tf-text-m);
        background:#f8faff; text-align:right; border-bottom:1px solid var(--tf-border);
    }
    .mfg-table td {
        padding:12px 16px; font-size:14px; color:var(--tf-text-b);
        border-bottom:1px solid #f0f4f8;
    }

    .btn {
        display:inline-flex; align-items:center; gap:8px;
        padding:10px 20px; border-radius:12px; font-weight:700;
        font-size:14px; border:none; cursor:pointer;
        transition:all .3s; text-decoration:none;
    }
    .btn-primary { background:var(--tf-indigo); color:#fff; }
    .btn-primary:hover { background:#3b52c0; }
    .btn-secondary { background:#f0f4f8; color:var(--tf-text-b); }
    .btn-secondary:hover { background:#e4eaf7; }
    .btn-green { background:var(--tf-green); color:#fff; }
    .btn-green:hover { background:#0d946d; }
    .btn-red { background:#fee2e2; color:#dc2626; }
    .btn-red:hover { background:#fecaca; }
    .btn-sm { padding:6px 12px; font-size:12px; border-radius:8px; }

    .detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .detail-item { display:flex; flex-direction:column; gap:4px; }
    .detail-label { font-size:12px; font-weight:700; color:var(--tf-text-m); }
    .detail-value { font-size:15px; font-weight:800; color:var(--tf-text-h); }

    .cost-table { width:100%; border-collapse:collapse; }
    .cost-table td {
        padding:12px 18px; font-size:14px; color:var(--tf-text-b);
        border-bottom:1px solid #f0f4f8;
    }
    .cost-table td:first-child { font-weight:600; }
    .cost-table td:last-child { font-weight:800; text-align:left; direction:ltr; }
    .cost-table tr.total-row { background:linear-gradient(90deg,#eef0fc,#e8f2ff); }
    .cost-table tr.total-row td { font-size:16px; color:var(--tf-indigo); border-bottom:none; }
    .cost-table tr.final-row { background:var(--tf-indigo); }
    .cost-table tr.final-row td { color:#fff; font-size:18px; border-bottom:none; }

    .price-val { font-weight:800; color:var(--tf-indigo); }
</style>
@endpush

@section('content')
<div class="mfg-page">
    <div class="mfg-section">
        <div class="mfg-header">
            <h1 class="mfg-title">
                <i class="fas fa-industry"></i>
                {{ $manufacturingCost->product_name }}
                @if($manufacturingCost->status === 'draft')
                <span class="badge badge-draft">مسودة</span>
                @else
                <span class="badge badge-confirmed">مؤكد</span>
                @endif
            </h1>
            <div class="mfg-header-actions">
                <a href="{{ route('manufacturing.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> رجوع
                </a>
                @if($manufacturingCost->status === 'draft')
                <a href="{{ route('manufacturing.edit', $manufacturingCost) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <form method="POST" action="{{ route('manufacturing.confirm', $manufacturingCost) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-green" onclick="return confirm('هل أنت متأكد من تأكيد هذا الحساب؟')">
                        <i class="fas fa-check"></i> تأكيد
                    </button>
                </form>
                @endif
                <form method="POST" action="{{ route('manufacturing.destroy', $manufacturingCost) }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-red" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                        <i class="fas fa-trash"></i> حذف
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#e6f8f3; color:#047857; padding:14px 20px; border-radius:12px; margin-bottom:20px; font-weight:700; font-size:14px; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <!-- Components Table -->
    <div class="mfg-section">
        <div class="mfg-card">
            <div class="mfg-card-top">
                <div class="icon-wrap icon-green"><i class="fas fa-cubes"></i></div>
                <h3>قطع المنتج</h3>
            </div>
            <div class="mfg-card-body" style="padding:0;">
                <table class="mfg-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم القطعة</th>
                            <th>الكمية</th>
                            <th>الطول (سم)</th>
                            <th>العرض (سم)</th>
                            <th>السمك (سم)</th>
                            <th>الحجم (سم³)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($manufacturingCost->components as $i => $comp)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td style="font-weight:700;">{{ $comp->component_name }}</td>
                            <td>{{ $comp->quantity }}</td>
                            <td>{{ $comp->length_cm }}</td>
                            <td>{{ $comp->width_cm }}</td>
                            <td>{{ $comp->thickness_cm }}</td>
                            <td class="price-val">{{ number_format($comp->volume_cm3, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8faff; font-weight:800;">
                            <td colspan="6" style="text-align:left;">الإجمالي</td>
                            <td class="price-val">{{ number_format($manufacturingCost->total_volume_cm3, 2) }} سم³</td>
                        </tr>
                        <tr style="background:#f8faff; font-weight:800;">
                            <td colspan="6" style="text-align:left;">بالأمتار المكعبة</td>
                            <td class="price-val">{{ number_format($manufacturingCost->total_volume_m3, 6) }} م³</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Cost Breakdown -->
    <div class="mfg-section">
        <div class="mfg-card">
            <div class="mfg-card-top">
                <div class="icon-wrap icon-amber"><i class="fas fa-calculator"></i></div>
                <h3>تفاصيل التكاليف</h3>
            </div>
            <div class="mfg-card-body" style="padding:0;">
                <table class="cost-table">
                    <tr>
                        <td>سعر المتر المكعب</td>
                        <td>{{ number_format($manufacturingCost->price_per_cubic_meter, 2) }} ج.م / م³</td>
                    </tr>
                    <tr>
                        <td>تكلفة الخامات ({{ number_format($manufacturingCost->total_volume_m3, 6) }} م³ × {{ number_format($manufacturingCost->price_per_cubic_meter, 2) }})</td>
                        <td>{{ number_format($manufacturingCost->material_cost, 2) }} ج.م</td>
                    </tr>
                    <tr>
                        <td>تكلفة العمالة</td>
                        <td>{{ number_format($manufacturingCost->labor_cost, 2) }} ج.م</td>
                    </tr>
                    <tr>
                        <td>المسامير والعتاد</td>
                        <td>{{ number_format($manufacturingCost->nails_hardware_cost, 2) }} ج.م</td>
                    </tr>
                    <tr>
                        <td>تكلفة النقل</td>
                        <td>{{ number_format($manufacturingCost->transportation_cost, 2) }} ج.م</td>
                    </tr>
                    <tr>
                        <td>تكلفة التطهير</td>
                        <td>{{ number_format($manufacturingCost->fumigation_cost, 2) }} ج.م</td>
                    </tr>
                    <tr>
                        <td>إكراميات ومتنوعة</td>
                        <td>{{ number_format($manufacturingCost->tips_misc_cost, 2) }} ج.م</td>
                    </tr>
                    <tr style="background:#f8faff;">
                        <td>إجمالي التكاليف الإضافية</td>
                        <td>{{ number_format($manufacturingCost->additional_costs_total, 2) }} ج.م</td>
                    </tr>
                    <tr class="total-row">
                        <td>التكلفة الإجمالية</td>
                        <td>{{ number_format($manufacturingCost->total_cost, 2) }} ج.م</td>
                    </tr>
                    <tr style="background:#e6f8f3;">
                        <td>الربح ({{ number_format($manufacturingCost->profit_percentage, 2) }}%)</td>
                        <td style="color:#047857;">{{ number_format($manufacturingCost->profit_amount, 2) }} ج.م</td>
                    </tr>
                    <tr class="final-row">
                        <td>السعر النهائي للبيع</td>
                        <td>{{ number_format($manufacturingCost->final_price, 2) }} ج.م</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Metadata -->
    <div class="mfg-section">
        <div class="mfg-card">
            <div class="mfg-card-top">
                <div class="icon-wrap icon-blue"><i class="fas fa-info-circle"></i></div>
                <h3>معلومات إضافية</h3>
            </div>
            <div class="mfg-card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">تاريخ الإنشاء</span>
                        <span class="detail-value">{{ $manufacturingCost->created_at->format('Y/m/d - H:i') }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">آخر تحديث</span>
                        <span class="detail-value">{{ $manufacturingCost->updated_at->format('Y/m/d - H:i') }}</span>
                    </div>
                    @if($manufacturingCost->creator)
                    <div class="detail-item">
                        <span class="detail-label">أنشئ بواسطة</span>
                        <span class="detail-value">{{ $manufacturingCost->creator->name }}</span>
                    </div>
                    @endif
                    @if($manufacturingCost->notes)
                    <div class="detail-item" style="grid-column:1/-1;">
                        <span class="detail-label">ملاحظات</span>
                        <span class="detail-value" style="font-weight:500;">{{ $manufacturingCost->notes }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
