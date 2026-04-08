@extends('layouts.app')

@section('title', 'تفاصيل فاتورة شراء')
@section('page-title', 'فاتورة شراء #' . $invoice->invoice_number)

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
        --tf-text-d:      #94a3b8;

        --tf-shadow-sm:   0 2px 12px rgba(79,99,210,0.07);
        --tf-shadow-card: 0 2px 0 0 rgba(0,0,0,0.04), 0 4px 20px rgba(79,99,210,0.08);
        --tf-shadow-lg:   0 8px 30px rgba(79,99,210,0.10);
    }

    .tf-page {
        background: var(--tf-bg);
        background-image:
            radial-gradient(ellipse 80% 60% at 10% -10%,  rgba(124,92,236,0.12) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(79,99,210,0.1) 0%, transparent 50%);
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

    .tf-section { animation: tfFadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .tf-section:nth-child(1) { animation-delay: 0.04s; }
    .tf-section:nth-child(2) { animation-delay: 0.12s; }

    .tf-card {
        background: var(--tf-surface); border-radius: 20px;
        border: 1px solid var(--tf-border);
        overflow: hidden; box-shadow: var(--tf-shadow-card);
        margin-bottom: 20px; position: relative;
        transition: all .35s cubic-bezier(.22,1,.36,1);
    }
    .tf-card:hover { transform: translateY(-3px); box-shadow: var(--tf-shadow-lg); }

    .tf-card-head {
        display: flex; justify-content: space-between; align-items: center;
        padding: 20px 24px; border-bottom: 1px solid var(--tf-border-soft);
        background: linear-gradient(135deg, var(--tf-violet-soft), var(--tf-indigo-soft)); flex-wrap: wrap; gap: 12px;
    }
    .tf-card-title { display: flex; align-items: center; gap: 12px; }
    .tf-card-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
    }
    .tf-card.violet .tf-card-icon { background: var(--tf-violet); color: var(--tf-surface); }
    .tf-card.blue .tf-card-icon { background: var(--tf-blue); color: var(--tf-surface); }
    .tf-card.green .tf-card-icon { background: var(--tf-green); color: var(--tf-surface); }
    .tf-card.amber .tf-card-icon { background: var(--tf-amber); color: var(--tf-surface); }

    .tf-title-text { font-size: 18px; font-weight: 800; color: var(--tf-text-h); }
    .tf-title-sub { font-size: 12px; color: var(--tf-text-m); font-weight: 600; }

    .tf-card-body { padding: 24px; }

    .tf-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 20px; border-radius: 14px;
        font-size: 14px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif; text-decoration: none;
        transition: all .25s; border: none;
    }
    .tf-btn-primary {
        background: linear-gradient(135deg, var(--tf-violet), #6a4dd0);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(124,92,236,0.35);
    }
    .tf-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(124,92,236,0.45); }
    .tf-btn-secondary {
        background: var(--tf-surface); color: var(--tf-text-b);
        border: 1.5px solid var(--tf-border);
    }
    .tf-btn-secondary:hover { background: var(--tf-surface2); }
    .tf-btn-danger {
        background: linear-gradient(135deg, var(--tf-red), #d63c4c);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(232,75,90,0.35);
    }
    .tf-btn-danger:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(232,75,90,0.45); }

    .tf-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    @media (max-width: 1000px) { .tf-grid-4 { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px) { .tf-grid-4 { grid-template-columns: 1fr; } }

    .tf-info-box {
        padding: 16px; border-radius: 14px; background: var(--tf-surface2);
        border-right: 4px solid;
    }
    .tf-info-box.violet { border-color: var(--tf-violet); }
    .tf-info-box.blue { border-color: var(--tf-blue); }
    .tf-info-box.green { border-color: var(--tf-green); }
    .tf-info-box.amber { border-color: var(--tf-amber); }

    .tf-info-label { font-size: 12px; color: var(--tf-text-m); font-weight: 600; margin-bottom: 4px; }
    .tf-info-value { font-size: 16px; font-weight: 800; color: var(--tf-text-h); }
    .tf-info-box.violet .tf-info-value { color: var(--tf-violet); }

    .tf-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 50px;
        font-size: 12px; font-weight: 800;
    }
    .tf-badge.green  { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-badge.amber  { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .tf-badge.red    { background: var(--tf-red-soft); color: var(--tf-red); }

    .tf-table-wrapper { overflow-x: auto; }
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

    .tf-total-box {
        padding: 20px; border-radius: 16px;
        background: linear-gradient(135deg, var(--tf-violet-soft), var(--tf-indigo-soft));
        border: 1px solid var(--tf-violet);
    }
    .tf-total-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 0; border-bottom: 1px solid var(--tf-border-soft);
    }
    .tf-total-row:last-child { border-bottom: none; }
    .tf-total-label { font-size: 14px; color: var(--tf-text-m); font-weight: 600; }
    .tf-total-value { font-size: 14px; font-weight: 800; color: var(--tf-text-h); }
    .tf-total-value.red { color: var(--tf-red); }
    .tf-total-value.green { color: var(--tf-green); }
    .tf-total-value.violet { color: var(--tf-violet); }
    .tf-grand-total { font-size: 22px; font-weight: 900; color: var(--tf-violet); }

    .tf-meta {
        padding: 16px; border-radius: 14px; background: var(--tf-surface2);
        font-size: 13px; color: var(--tf-text-m);
    }
    .tf-meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    @media (max-width: 600px) { .tf-meta-grid { grid-template-columns: 1fr; } }

    .tf-invoice-num {
        display: inline-block; padding: 8px 16px;
        border-radius: 50px; font-size: 14px; font-weight: 800;
        background: var(--tf-violet-soft); color: var(--tf-violet);
    }

    @media print {
        body * { visibility: hidden; }
        .tf-page, .tf-page * { visibility: visible; }
        .tf-page { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
    }

    .tf-alert {
        position: fixed; bottom: 24px; left: 24px;
        display: flex; align-items: center; gap: 12px;
        padding: 16px 20px; border-radius: 16px;
        z-index: 50; animation: tfFadeUp 0.4s ease;
        box-shadow: var(--tf-shadow-lg);
    }
    .tf-alert-success { background: var(--tf-green); color: var(--tf-surface); }
</style>
@endpush

@section('content')
<div class="tf-page">
    @if(session('success'))
    <div class="tf-alert tf-alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon violet"><i class="fas fa-shopping-cart"></i></div>
                <div>
                    <h2 class="tf-title-text">تفاصيل فاتورة الشراء</h2>
                    <p class="tf-title-sub">{{ $invoice->invoice_date->format('Y-m-d') }}</p>
                </div>
            </div>
            <div style="display: flex; gap: 10px;" class="no-print">
                <a href="{{ route('invoices.purchases.edit', $invoice->id) }}" class="tf-btn tf-btn-primary">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <button onclick="window.print()" class="tf-btn tf-btn-primary">
                    <i class="fas fa-print"></i> طباعة
                </button>
            </div>
        </div>
    </div>

    <div class="tf-grid-4 tf-section">
        <div class="tf-info-box violet">
            <div class="tf-info-label">رقم الفاتورة</div>
            <div class="tf-info-value">{{ $invoice->invoice_number }}</div>
        </div>
        <div class="tf-info-box blue">
            <div class="tf-info-label">المورد</div>
            <div class="tf-info-value">{{ $invoice->supplier->name }}</div>
        </div>
        <div class="tf-info-box green">
            <div class="tf-info-label">المخزن</div>
            <div class="tf-info-value">{{ $invoice->warehouse->name }}</div>
        </div>
        <div class="tf-info-box amber">
            <div class="tf-info-label">التاريخ</div>
            <div class="tf-info-value">{{ $invoice->invoice_date->format('Y-m-d') }}</div>
        </div>
    </div>

    <div class="tf-card tf-section">
        <div class="tf-card-body">
            <div style="margin-bottom: 16px;">
                <span style="font-size: 14px; color: var(--tf-text-m); margin-left: 8px;">الحالة:</span>
                @if($invoice->status == 'paid')
                    <span class="tf-badge green"><i class="fas fa-check-circle"></i> مدفوعة</span>
                @elseif($invoice->status == 'pending')
                    <span class="tf-badge amber"><i class="fas fa-clock"></i> معلقة</span>
                @else
                    <span class="tf-badge red"><i class="fas fa-times-circle"></i> ملغاة</span>
                @endif
            </div>

            <h3 style="font-size: 16px; font-weight: 800; color: var(--tf-text-h); margin-bottom: 16px;">الأصناف</h3>
            <div class="tf-table-wrapper">
                <table class="tf-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الصنف</th>
                            <th>الكمية</th>
                            <th>سعر الوحدة</th>
                            <th>الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td style="font-weight: 700;">{{ $item->product->name }}</td>
                            <td>{{ number_format($item->qty, 2) }}</td>
                            <td>{{ number_format($item->price, 2) }} ج.م</td>
                            <td style="font-weight: 800;">{{ number_format($item->total, 2) }} ج.م</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tf-grid-2 tf-section" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
        @if($invoice->notes)
        <div class="tf-card">
            <div class="tf-card-body">
                <h4 style="font-size: 14px; font-weight: 800; color: var(--tf-text-h); margin-bottom: 12px;">
                    <i class="fas fa-sticky-note" style="color: var(--tf-violet);"></i> الملاحظات
                </h4>
                <p style="color: var(--tf-text-m); font-size: 14px; line-height: 1.6;">{{ $invoice->notes }}</p>
            </div>
        </div>
        @endif

        <div class="tf-total-box">
            <div class="tf-total-row">
                <span class="tf-total-label">المجموع الفرعي:</span>
                <span class="tf-total-value">{{ number_format($invoice->subtotal, 2) }} ج.م</span>
            </div>
            @if($invoice->discount > 0)
            <div class="tf-total-row">
                <span class="tf-total-label" style="color: var(--tf-red);">الخصم:</span>
                <span class="tf-total-value red">- {{ number_format($invoice->discount, 2) }} ج.م</span>
            </div>
            @endif
            @if($invoice->tax > 0)
            <div class="tf-total-row">
                <span class="tf-total-label" style="color: var(--tf-green);">الضريبة:</span>
                <span class="tf-total-value green">+ {{ number_format($invoice->tax, 2) }} ج.م</span>
            </div>
            @endif
            <div class="tf-total-row" style="border-top: 2px solid var(--tf-border); margin-top: 8px; padding-top: 12px;">
                <span class="tf-total-label" style="font-size: 16px;">الإجمالي النهائي:</span>
                <span class="tf-grand-total">{{ number_format($invoice->total, 2) }} ج.م</span>
            </div>
        </div>
    </div>

    <div class="tf-card tf-section">
        <div class="tf-card-body">
            <div class="tf-meta">
                <div class="tf-meta-grid">
                    <div><i class="fas fa-calendar-plus" style="color: var(--tf-violet);"></i> <strong>تاريخ الإنشاء:</strong> {{ $invoice->created_at->format('Y-m-d h:i A') }}</div>
                    <div><i class="fas fa-calendar-edit" style="color: var(--tf-blue);"></i> <strong>آخر تحديث:</strong> {{ $invoice->updated_at->format('Y-m-d h:i A') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="tf-card tf-section no-print">
        <div class="tf-card-body" style="display: flex; gap: 12px;">
            <a href="{{ route('invoices.purchases.index') }}" class="tf-btn tf-btn-secondary">
                <i class="fas fa-arrow-right"></i> رجوع للقائمة
            </a>
            <form action="{{ route('invoices.purchases.destroy', $invoice->id) }}" method="POST" onsubmit="return confirm('⚠️ هل أنت متأكد من حذف هذه الفاتورة؟ سيتم تقليل الكميات من المخزن.')">
                @csrf @method('DELETE')
                <button type="submit" class="tf-btn tf-btn-danger">
                    <i class="fas fa-trash"></i> حذف الفاتورة
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tf-alert').forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'all 0.4s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(10px)';
            setTimeout(function() { alert.remove(); }, 400);
        }, 5000);
    });
});
</script>
@endpush
@endsection