@extends('layouts.app')

@section('title', 'تفاصيل مرتجع المبيعات')
@section('page-title', 'مرتجع #' . $salesReturn->return_number)

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
            radial-gradient(ellipse 80% 60% at 10% -10%,  rgba(232,75,90,0.12) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(124,92,236,0.1) 0%, transparent 50%);
        min-height: 100vh;
        padding: 26px 22px;
    }

    @keyframes tfFadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .tf-section { animation: tfFadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .tf-section:nth-child(1) { animation-delay: 0.04s; }
    .tf-section:nth-child(2) { animation-delay: 0.12s; }
    .tf-section:nth-child(3) { animation-delay: 0.20s; }

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
        background: linear-gradient(135deg, var(--tf-red-soft), var(--tf-red-soft)); flex-wrap: wrap; gap: 12px;
    }
    .tf-card-title { display: flex; align-items: center; gap: 12px; }
    .tf-card-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
    }
    .tf-card.red .tf-card-icon { background: var(--tf-red); color: var(--tf-surface); }
    .tf-card.blue .tf-card-icon { background: var(--tf-blue); color: var(--tf-surface); }
    .tf-card.green .tf-card-icon { background: var(--tf-green); color: var(--tf-surface); }

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
        background: linear-gradient(135deg, var(--tf-red), #d63c4c);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(232,75,90,0.35);
    }
    .tf-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(232,75,90,0.45); }
    .tf-btn-secondary {
        background: var(--tf-surface); color: var(--tf-text-b);
        border: 1.5px solid var(--tf-border);
    }
    .tf-btn-secondary:hover { background: var(--tf-surface2); }
    .tf-btn-danger {
        background: linear-gradient(135deg, var(--tf-red), #d63c4c);
        color: var(--tf-surface); border: none;
    }

    .tf-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    @media (max-width: 600px) { .tf-grid-2 { grid-template-columns: 1fr; } }

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

    .tf-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 50px;
        font-size: 12px; font-weight: 800;
    }
    .tf-badge.green  { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-badge.amber  { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .tf-badge.gray   { background: var(--tf-surface2); color: var(--tf-text-m); }

    .tf-info-box {
        padding: 20px; border-radius: 16px; background: var(--tf-surface);
        border: 1px solid var(--tf-border);
    }
    .tf-info-label { font-size: 12px; color: var(--tf-text-m); font-weight: 600; margin-bottom: 4px; }
    .tf-info-value { font-size: 14px; font-weight: 800; color: var(--tf-text-h); }
    .tf-info-value.blue { color: var(--tf-blue); }
    .tf-info-value.green { color: var(--tf-green); }

    .tf-total-box {
        padding: 20px; border-radius: 16px;
        background: linear-gradient(135deg, var(--tf-red-soft), var(--tf-surface2));
        border: 1px solid var(--tf-red);
    }
    .tf-total-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 0; border-bottom: 1px solid var(--tf-border-soft);
    }
    .tf-total-row:last-child { border-bottom: none; }
    .tf-total-label { font-size: 14px; color: var(--tf-text-m); font-weight: 600; }
    .tf-total-value { font-size: 14px; font-weight: 800; color: var(--tf-text-h); }
    .tf-grand-total { font-size: 22px; font-weight: 900; color: var(--tf-red); }

    .tf-meta {
        padding: 16px; border-radius: 14px; background: var(--tf-surface2);
        font-size: 13px; color: var(--tf-text-m);
    }
    .tf-meta-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    @media (max-width: 600px) { .tf-meta-grid { grid-template-columns: 1fr; } }

    .tf-code {
        display: inline-block; padding: 8px 16px;
        border-radius: 50px; font-size: 14px; font-weight: 800;
        background: var(--tf-red-soft); color: var(--tf-red);
    }
</style>
@endpush

@section('content')
<div class="tf-page">
    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon red"><i class="fas fa-undo-alt"></i></div>
                <div>
                    <h2 class="tf-title-text">{{ $salesReturn->return_number }}</h2>
                    <p class="tf-title-sub">تاريخ المرتجع: {{ $salesReturn->return_date ? $salesReturn->return_date->format('Y-m-d') : '-' }}</p>
                </div>
            </div>
            <div>
                @if($salesReturn->status === 'confirmed')
                    <span class="tf-badge green">✓ مؤكد</span>
                @elseif($salesReturn->status === 'cancelled')
                    <span class="tf-badge gray">✗ ملغي</span>
                @else
                    <span class="tf-badge amber">⊙ مسودة</span>
                @endif
            </div>
        </div>
    </div>

    <div class="tf-grid-2 tf-section">
        <div class="tf-card">
            <div class="tf-card-head">
                <div class="tf-card-title">
                    <div class="tf-card-icon blue"><i class="fas fa-file-invoice"></i></div>
                    <div>
                        <h3 class="tf-title-text">بيانات الفاتورة</h3>
                    </div>
                </div>
            </div>
            <div class="tf-card-body">
                <div class="tf-info-box" style="margin-bottom: 12px;">
                    <div class="tf-info-label">رقم الفاتورة</div>
                    <div class="tf-info-value blue">{{ $salesReturn->salesInvoice->invoice_number ?? '-' }}</div>
                </div>
                <div class="tf-info-box" style="margin-bottom: 12px;">
                    <div class="tf-info-label">تاريخ الفاتورة</div>
                    <div class="tf-info-value">{{ $salesReturn->salesInvoice && $salesReturn->salesInvoice->invoice_date ? $salesReturn->salesInvoice->invoice_date->format('Y-m-d') : '-' }}</div>
                </div>
                <div class="tf-info-box">
                    <div class="tf-info-label">المخزن</div>
                    <div class="tf-info-value">{{ $salesReturn->salesInvoice->warehouse->name ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="tf-card">
            <div class="tf-card-head">
                <div class="tf-card-title">
                    <div class="tf-card-icon green"><i class="fas fa-user"></i></div>
                    <div>
                        <h3 class="tf-title-text">بيانات العميل</h3>
                    </div>
                </div>
            </div>
            <div class="tf-card-body">
                <div class="tf-info-box" style="margin-bottom: 12px;">
                    <div class="tf-info-label">اسم العميل</div>
                    <div class="tf-info-value">{{ $salesReturn->salesInvoice->customer->name ?? '-' }}</div>
                </div>
                <div class="tf-info-box" style="margin-bottom: 12px;">
                    <div class="tf-info-label">رقم الهاتف</div>
                    <div class="tf-info-value">{{ $salesReturn->salesInvoice->customer->phone ?? '-' }}</div>
                </div>
                <div class="tf-info-box">
                    <div class="tf-info-label">العنوان</div>
                    <div class="tf-info-value">{{ $salesReturn->salesInvoice->customer->address ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon red"><i class="fas fa-boxes"></i></div>
                <div>
                    <h3 class="tf-title-text">الأصناف المرتجعة</h3>
                </div>
            </div>
        </div>
        <div class="tf-table-wrapper">
            <table class="tf-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الصنف</th>
                        <th>الكمية المرتجعة</th>
                        <th>سعر الوحدة</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesReturn->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div style="font-weight: 700;">{{ $item->product->name ?? '-' }}</div>
                            <div style="font-size: 11px; color: var(--tf-text-m);">{{ $item->product->sku ?? '' }}</div>
                        </td>
                        <td>
                            <span style="font-weight: 700;">{{ number_format($item->quantity_returned, 3) }}</span>
                            <span style="font-size: 12px; color: var(--tf-text-m);">{{ $item->product->unit ?? '' }}</span>
                        </td>
                        <td>{{ number_format($item->unit_price, 2) }} ج.م</td>
                        <td style="font-weight: 800; color: var(--tf-green);">{{ number_format($item->total, 2) }} ج.م</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: var(--tf-surface2);">
                        <td colspan="4" style="font-weight: 800; text-align: left;">الإجمالي:</td>
                        <td style="font-weight: 900; font-size: 18px; color: var(--tf-red);">{{ number_format($salesReturn->total, 2) }} ج.م</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($salesReturn->return_reason || $salesReturn->notes)
    <div class="tf-card tf-section">
        <div class="tf-card-body">
            <h4 style="font-size: 14px; font-weight: 800; color: var(--tf-text-h); margin-bottom: 12px;">
                <i class="fas fa-sticky-note" style="color: var(--tf-amber);"></i> الملاحظات
            </h4>
            <div style="padding: 16px; border-radius: 14px; background: var(--tf-surface2);">
                <p style="color: var(--tf-text-m); font-size: 14px; line-height: 1.6;">{{ $salesReturn->return_reason ?? $salesReturn->notes }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="tf-card tf-section">
        <div class="tf-card-body">
            <div class="tf-meta">
                <div class="tf-meta-grid">
                    <div><i class="fas fa-user-plus" style="color: var(--tf-green);"></i> <strong>تم الإنشاء بواسطة:</strong> {{ $salesReturn->creator->name ?? '-' }}</div>
                    <div><i class="fas fa-calendar" style="color: var(--tf-blue);"></i> <strong>تاريخ الإنشاء:</strong> {{ $salesReturn->created_at ? $salesReturn->created_at->format('Y-m-d H:i') : '-' }}</div>
                    @if($salesReturn->confirmed_by)
                    <div><i class="fas fa-check-circle" style="color: var(--tf-green);"></i> <strong>تم التأكيد بواسطة:</strong> {{ $salesReturn->confirmer->name ?? '-' }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="tf-card tf-section">
        <div class="tf-card-body" style="display: flex; gap: 12px;">
            <a href="{{ route('invoices.sales-returns.index') }}" class="tf-btn tf-btn-secondary" style="flex: 1;">
                <i class="fas fa-arrow-right"></i> رجوع للقائمة
            </a>
            @if($salesReturn->status !== 'cancelled')
            <form method="POST" action="{{ route('invoices.sales-returns.destroy', $salesReturn->id) }}" onsubmit="return confirm('⚠️ هل أنت متأكد من إلغاء هذا المرتجع؟')" style="flex: 1;">
                @csrf @method('DELETE')
                <button type="submit" class="tf-btn tf-btn-danger" style="width: 100%;">
                    <i class="fas fa-times"></i> إلغاء المرتجع
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection