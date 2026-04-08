@extends('layouts.app')

@section('title', 'إضافة مرتجع مبيعات')
@section('page-title', 'إضافة مرتجع مبيعات')

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
    .tf-card.red .tf-card-icon { background: var(--tf-red-soft); color: var(--tf-red); }

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

    .tf-input, .tf-select {
        width: 100%; padding: 12px 16px;
        border: 1.5px solid var(--tf-border); border-radius: 14px;
        font-size: 14px; font-family: 'Cairo', sans-serif;
        color: var(--tf-text-h); background: var(--tf-surface);
        transition: all .25s; outline: none;
    }
    .tf-input:focus, .tf-select:focus {
        border-color: var(--tf-red);
        box-shadow: 0 0 0 3px rgba(232,75,90,0.12);
    }

    .tf-label {
        display: block; font-size: 12px; font-weight: 700;
        color: var(--tf-text-m); margin-bottom: 6px;
    }

    .tf-form-group {
        margin-bottom: 20px;
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
                    <h2 class="tf-title-text">إنشاء مرتجع مبيعات جديد</h2>
                    <p class="tf-title-sub">إرجاع منتجات من فاتورة مبيعات</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('invoices.sales-returns.store') }}" enctype="multipart/form-data" class="tf-card-body">
            @csrf

            <div class="tf-form-group">
                <label class="tf-label"><i class="fas fa-file-invoice" style="color: var(--tf-indigo);"></i> فاتورة البيع</label>
                <select name="sales_invoice_id" class="tf-select" required>
                    <option value="">اختر فاتورة</option>
                    @foreach($invoices as $invoice)
                        <option value="{{ $invoice->id }}">
                            {{ $invoice->invoice_number }} - {{ $invoice->customer->name }}
                        </option>
                    @endforeach
                </select>
                @error('sales_invoice_id')
                    <p style="color: var(--tf-red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="tf-form-group">
                <label class="tf-label"><i class="fas fa-money-bill-wave" style="color: var(--tf-green);"></i> قيمة المرتجع</label>
                <input type="number" name="total_amount" step="0.01" value="{{ old('total_amount') }}" class="tf-input" placeholder="أدخل قيمة المرتجع" required>
                @error('total_amount')
                    <p style="color: var(--tf-red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="tf-form-group">
                <label class="tf-label"><i class="fas fa-comment-alt" style="color: var(--tf-amber);"></i> سبب المرتجع</label>
                <textarea name="reason" rows="3" class="tf-input" placeholder="مثال: عيب في المنتج / غير مطابق" required>{{ old('reason') }}</textarea>
                @error('reason')
                    <p style="color: var(--tf-red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="tf-form-group">
                <label class="tf-label"><i class="fas fa-image" style="color: var(--tf-violet);"></i> صور داعمة (اختياري)</label>
                <input type="file" name="images[]" multiple class="tf-input" style="padding: 10px;">
                <p style="font-size: 12px; color: var(--tf-text-m); margin-top: 4px;">يمكنك رفع أكثر من صورة لتوضيح سبب المرتجع</p>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="tf-btn tf-btn-primary">
                    <i class="fas fa-save"></i> حفظ المرتجع
                </button>
                <a href="{{ route('invoices.sales-returns.index') }}" class="tf-btn tf-btn-secondary">
                    <i class="fas fa-arrow-right"></i> رجوع
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
