@extends('layouts.app')

@section('title', 'عرض فاتورة مبيعات #' . $invoice->invoice_number)
@section('page-title', 'فاتورة مبيعات #' . $invoice->invoice_number)

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
    .tf-card::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.45) 50%, transparent 60%);
        background-size: 600px 100%; opacity: 0; pointer-events: none; transition: opacity .3s;
    }
    .tf-card:hover::after { opacity: 1; animation: tfShimmer .7s ease forwards; }

    .tf-card-head {
        display: flex; justify-content: space-between; align-items: center;
        padding: 20px 24px; border-bottom: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2); flex-wrap: wrap; gap: 12px;
    }
    .tf-card-title { display: flex; align-items: center; gap: 12px; }
    .tf-card-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        transition: transform .4s cubic-bezier(.34,1.56,.64,1);
    }
    .tf-card:hover .tf-card-icon { animation: iconBounce .6s ease; }
    .tf-card.blue .tf-card-icon { background: var(--tf-blue-soft); color: var(--tf-blue); }
    .tf-card.green .tf-card-icon { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-card.indigo .tf-card-icon { background: var(--tf-indigo-soft); color: var(--tf-indigo); }
    .tf-card.violet .tf-card-icon { background: var(--tf-violet-soft); color: var(--tf-violet); }

    .tf-title-text { font-size: 18px; font-weight: 800; color: var(--tf-text-h); }
    .tf-title-sub { font-size: 12px; color: var(--tf-text-m); font-weight: 600; }

    .tf-card-body { padding: 20px; }

    .tf-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 20px; border-radius: 14px;
        font-size: 14px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif; text-decoration: none;
        transition: all .25s; border: none;
    }
    .tf-btn-primary {
        background: linear-gradient(135deg, var(--tf-green), #0d8a6e);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(15,170,126,0.35);
    }
    .tf-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15,170,126,0.45); }
    .tf-btn-secondary {
        background: var(--tf-surface); color: var(--tf-text-b);
        border: 1.5px solid var(--tf-border);
    }
    .tf-btn-secondary:hover { background: var(--tf-surface2); }
    .tf-btn-blue {
        background: linear-gradient(135deg, var(--tf-blue), #2d7de0);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(58,142,240,0.35);
    }
    .tf-btn-blue:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(58,142,240,0.45); }

    .tf-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    @media (max-width: 900px) { .tf-grid-3 { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px) { .tf-grid-3 { grid-template-columns: 1fr; } }

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
        font-size: 11px; font-weight: 800;
    }
    .tf-badge.green  { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-badge.amber  { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .tf-badge.red    { background: var(--tf-red-soft); color: var(--tf-red); }
    .tf-badge.blue   { background: var(--tf-blue-soft); color: var(--tf-blue); }

    .tf-info-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 0; border-bottom: 1px solid var(--tf-border-soft);
    }
    .tf-info-row:last-child { border-bottom: none; }
    .tf-info-label { font-size: 14px; color: var(--tf-text-m); font-weight: 600; }
    .tf-info-value { font-size: 14px; font-weight: 800; color: var(--tf-text-h); }

    .tf-total-card {
        padding: 20px; border-radius: 16px;
        background: var(--tf-surface); border: 1px solid var(--tf-border);
    }
    .tf-total-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 0; border-bottom: 1px solid var(--tf-border-soft);
    }
    .tf-total-row:last-child { border-bottom: none; }
    .tf-total-label { font-size: 14px; color: var(--tf-text-m); font-weight: 600; }
    .tf-total-value { font-size: 14px; font-weight: 800; color: var(--tf-text-h); }
    .tf-total-value.green { color: var(--tf-green); }
    .tf-total-value.red { color: var(--tf-red); }
    .tf-total-value.blue { color: var(--tf-blue); }
    .tf-grand-total {
        font-size: 22px; font-weight: 900; color: var(--tf-green);
    }

    .tf-invoice-num {
        display: inline-block; padding: 8px 16px;
        border-radius: 50px; font-size: 14px; font-weight: 800;
        background: var(--tf-indigo-soft); color: var(--tf-indigo);
    }

    .tf-avatar {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 800; color: var(--tf-surface);
        background: linear-gradient(135deg, var(--tf-blue), var(--tf-indigo));
    }

    @media print {
        body * { visibility: hidden; }
        .tf-page, .tf-page * { visibility: visible; }
        .tf-page { position: absolute; left: 0; top: 0; width: 100%; }
        .tf-btn, .no-print { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="tf-page">
    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon blue"><i class="fas fa-file-invoice"></i></div>
                <div>
                    <h2 class="tf-title-text">فاتورة مبيعات #{{ $invoice->invoice_number }}</h2>
                    <p class="tf-title-sub">تاريخ: {{ $invoice->invoice_date->format('Y-m-d') }}</p>
                </div>
            </div>
            <div style="display: flex; gap: 10px;" class="no-print">
                <a href="{{ route('invoices.sales.index') }}" class="tf-btn tf-btn-secondary">
                    <i class="fas fa-arrow-right"></i> عودة
                </a>
                <a href="{{ route('invoices.sales.edit', $invoice->id) }}" class="tf-btn tf-btn-secondary">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <button onclick="window.print()" class="tf-btn tf-btn-blue">
                    <i class="fas fa-print"></i> طباعة
                </button>
            </div>
        </div>
    </div>

    <div class="tf-grid-3 tf-section">
        <div class="tf-card">
            <div class="tf-card-head">
                <div class="tf-card-title">
                    <div class="tf-card-icon green"><i class="fas fa-user"></i></div>
                    <div>
                        <h3 class="tf-title-text">بيانات العميل</h3>
                        <p class="tf-title-sub">معلومات العميل</p>
                    </div>
                </div>
            </div>
            <div class="tf-card-body">
                <div class="tf-info-row">
                    <span class="tf-info-label">الاسم:</span>
                    <span class="tf-info-value">{{ $invoice->customer->name ?? 'غير محدد' }}</span>
                </div>
                @if($invoice->customer && $invoice->customer->phone)
                <div class="tf-info-row">
                    <span class="tf-info-label">الهاتف:</span>
                    <span class="tf-info-value">{{ $invoice->customer->phone }}</span>
                </div>
                @endif
                @if($invoice->customer && $invoice->customer->email)
                <div class="tf-info-row">
                    <span class="tf-info-label">البريد:</span>
                    <span class="tf-info-value">{{ $invoice->customer->email }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="tf-card">
            <div class="tf-card-head">
                <div class="tf-card-title">
                    <div class="tf-card-icon indigo"><i class="fas fa-info-circle"></i></div>
                    <div>
                        <h3 class="tf-title-text">حالة الفاتورة</h3>
                        <p class="tf-title-sub">معلومات الفاتورة</p>
                    </div>
                </div>
            </div>
            <div class="tf-card-body">
                <div class="tf-info-row">
                    <span class="tf-info-label">الحالة:</span>
                    <span class="tf-badge {{ $invoice->payment_status == 'paid' ? 'green' : ($invoice->payment_status == 'partial' ? 'amber' : 'red') }}">
                        {{ $invoice->payment_status == 'paid' ? 'مدفوعة بالكامل' : ($invoice->payment_status == 'partial' ? 'مدفوعة جزئياً' : 'غير مدفوعة') }}
                    </span>
                </div>
                <div class="tf-info-row">
                    <span class="tf-info-label">تاريخ الإنشاء:</span>
                    <span class="tf-info-value">{{ $invoice->created_at->format('Y-m-d H:i') }}</span>
                </div>
                @if($invoice->creator)
                <div class="tf-info-row">
                    <span class="tf-info-label">أنشأها:</span>
                    <span class="tf-info-value">{{ $invoice->creator->name }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="tf-card">
            <div class="tf-card-head">
                <div class="tf-card-title">
                    <div class="tf-card-icon violet"><i class="fas fa-calculator"></i></div>
                    <div>
                        <h3 class="tf-title-text">الملخص المالي</h3>
                        <p class="tf-title-sub">تفاصيل المبالغ</p>
                    </div>
                </div>
            </div>
            <div class="tf-card-body">
                <div class="tf-info-row">
                    <span class="tf-info-label">الإجمالي:</span>
                    <span class="tf-info-value">{{ number_format($invoice->total, 2) }} ج.م</span>
                </div>
                <div class="tf-info-row">
                    <span class="tf-info-label">الخصم:</span>
                    <span class="tf-info-value red">{{ number_format($invoice->discount_amount ?? 0, 2) }} ج.م</span>
                </div>
                <div class="tf-info-row">
                    <span class="tf-info-label">الضريبة:</span>
                    <span class="tf-info-value blue">{{ number_format($invoice->tax_amount ?? 0, 2) }} ج.م</span>
                </div>
                <div class="tf-info-row">
                    <span class="tf-info-label">المدفوع:</span>
                    <span class="tf-info-value green">{{ number_format($invoice->paid ?? 0, 2) }} ج.م</span>
                </div>
                <div class="tf-info-row">
                    <span class="tf-info-label">الباقي:</span>
                    <span class="tf-info-value {{ $invoice->remaining > 0 ? 'red' : 'green' }}">
                        {{ number_format($invoice->remaining ?? 0, 2) }} ج.م
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon blue"><i class="fas fa-list"></i></div>
                <div>
                    <h3 class="tf-title-text">تفاصيل الفاتورة</h3>
                    <p class="tf-title-sub">الأصناف المُنتجة</p>
                </div>
            </div>
        </div>
        <div class="tf-table-wrapper">
            <table class="tf-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المنتج</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>الخصم</th>
                        <th>الضريبة</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div style="font-weight: 700;">{{ $item->product->name ?? 'منتج محذوف' }}</div>
                            @if($item->product && $item->product->sku)
                            <div style="font-size: 11px; color: var(--tf-text-m);">كود: {{ $item->product->sku }}</div>
                            @endif
                        </td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ number_format($item->unit_price, 2) }} ج.م</td>
                        <td style="color: var(--tf-red);">{{ number_format($item->discount_value ?? 0, 2) }} ج.م</td>
                        <td style="color: var(--tf-blue);">{{ number_format($item->tax_amount ?? 0, 2) }} ج.م</td>
                        <td style="font-weight: 800;">{{ number_format($item->total, 2) }} ج.م</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--tf-text-m);">
                            لا توجد عناصر في هذه الفاتورة
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background: var(--tf-surface2);">
                        <td colspan="6" style="font-weight: 800; text-align: left;">الإجمالي:</td>
                        <td style="font-weight: 900; font-size: 18px; color: var(--tf-green);">
                            {{ number_format($invoice->total, 2) }} ج.م
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon green"><i class="fas fa-wallet"></i></div>
                <div>
                    <h3 class="tf-title-text">ملخص الدفع</h3>
                    <p class="tf-title-sub">حالة الدفع</p>
                </div>
            </div>
        </div>
        <div class="tf-card-body">
            <div class="tf-grid-3">
                <div class="tf-total-card">
                    <div style="font-size: 12px; color: var(--tf-text-m); margin-bottom: 8px;">إجمالي الفاتورة</div>
                    <div style="font-size: 24px; font-weight: 900; color: var(--tf-text-h);">{{ number_format($invoice->total, 2) }} <span style="font-size: 14px;">جنيه</span></div>
                </div>
                <div class="tf-total-card">
                    <div style="font-size: 12px; color: var(--tf-text-m); margin-bottom: 8px;">المبلغ المدفوع</div>
                    <div style="font-size: 24px; font-weight: 900; color: var(--tf-green);">{{ number_format($invoice->paid ?? 0, 2) }} <span style="font-size: 14px;">جنيه</span></div>
                </div>
                <div class="tf-total-card">
                    <div style="font-size: 12px; color: var(--tf-text-m); margin-bottom: 8px;">المبلغ المتبقي</div>
                    <div style="font-size: 24px; font-weight: 900; color: <?php echo $invoice->remaining > 0 ? 'var(--tf-red)' : 'var(--tf-green)'; ?>;">
                        {{ number_format($invoice->remaining ?? 0, 2) }} <span style="font-size: 14px;">جنيه</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($invoice->payments && $invoice->payments->count() > 0)
    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon violet"><i class="fas fa-money-bill-wave"></i></div>
                <div>
                    <h3 class="tf-title-text">المدفوعات</h3>
                    <p class="tf-title-sub">سجل المدفوعات</p>
                </div>
            </div>
        </div>
        <div class="tf-table-wrapper">
            <table class="tf-table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>المبلغ</th>
                        <th>طريقة الدفع</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $payment)
                    <tr>
                        <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                        <td style="font-weight: 800; color: var(--tf-green);">{{ number_format($payment->amount, 2) }} ج.م</td>
                        <td>{{ $payment->payment_method ?? 'نقدي' }}</td>
                        <td style="color: var(--tf-text-m);">{{ $payment->notes ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($invoice->notes)
    <div class="tf-card tf-section">
        <div class="tf-card-body">
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <i class="fas fa-sticky-note" style="color: var(--tf-violet); font-size: 20px; margin-top: 2px;"></i>
                <div>
                    <h4 style="font-weight: 800; color: var(--tf-text-h); margin-bottom: 8px;">ملاحظات</h4>
                    <p style="color: var(--tf-text-m); font-size: 14px; line-height: 1.6;">{{ $invoice->notes }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
