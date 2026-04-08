@extends('layouts.app')

@section('title', 'تعديل فاتورة بيع')
@section('page-title', 'تعديل فاتورة بيع')

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
    .tf-card.amber .tf-card-icon { background: var(--tf-amber-soft); color: var(--tf-amber); }

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
        background: linear-gradient(135deg, var(--tf-amber), #d48009);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(232,147,10,0.35);
    }
    .tf-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(232,147,10,0.45); }
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
        border-color: var(--tf-amber);
        box-shadow: 0 0 0 3px rgba(232,147,10,0.12);
    }

    .tf-label {
        display: block; font-size: 12px; font-weight: 700;
        color: var(--tf-text-m); margin-bottom: 6px;
    }

    .tf-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
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

    .tf-row-item {
        display: grid; grid-template-columns: 2fr 1.5fr repeat(5, 1fr);
        gap: 12px; align-items: center;
        padding: 16px; border-radius: 14px; background: var(--tf-surface);
        border: 1px solid var(--tf-border); margin-bottom: 10px;
        transition: all .2s;
    }
    .tf-row-item:hover { border-color: var(--tf-amber); }

    .tf-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 50px;
        font-size: 11px; font-weight: 800;
    }
    .tf-badge.green  { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-badge.amber  { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .tf-badge.red    { background: var(--tf-red-soft); color: var(--tf-red); }

    .tf-total-box {
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
    .tf-total-value.amber { color: var(--tf-amber); }

    .tf-alert {
        padding: 16px; border-radius: 14px; border-right: 4px solid;
        background: var(--tf-surface);
    }
    .tf-alert-info { border-color: var(--tf-blue); background: var(--tf-blue-soft); }
    .tf-alert-warning { border-color: var(--tf-amber); background: var(--tf-amber-soft); }

    .tf-invoice-num {
        display: inline-block; padding: 6px 14px;
        border-radius: 50px; font-size: 12px; font-weight: 800;
        background: var(--tf-amber-soft); color: var(--tf-amber);
    }
</style>
@endpush

@section('content')
<div class="tf-page">
    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon amber"><i class="fas fa-edit"></i></div>
                <div>
                    <h2 class="tf-title-text">تعديل فاتورة بيع</h2>
                    <p class="tf-title-sub">تحديث بيانات الفاتورة</p>
                </div>
            </div>
            <span class="tf-invoice-num">{{ $invoice->invoice_number }}</span>
        </div>

        <form method="POST" action="{{ route('invoices.sales.update', $invoice->id) }}" class="tf-card-body">
            @csrf
            @method('PUT')

            <div class="tf-grid-2" style="margin-bottom: 20px;">
                <div>
                    <label class="tf-label"><i class="fas fa-user" style="color: var(--tf-green);"></i> العميل</label>
                    <select name="customer_id" class="tf-select" required>
                        <option value="">اختر العميل</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected($customer->id == $invoice->customer_id)>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <p style="color: var(--tf-red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="tf-label"><i class="fas fa-warehouse" style="color: var(--tf-indigo);"></i> المخزن</label>
                    <select name="warehouse_id" class="tf-select" required>
                        <option value="">اختر المخزن</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected($warehouse->id == $invoice->warehouse_id)>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <p style="color: var(--tf-red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="tf-label"><i class="fas fa-calendar" style="color: var(--tf-blue);"></i> تاريخ الفاتورة</label>
                <input type="date" name="invoice_date" value="{{ $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : now()->format('Y-m-d') }}" class="tf-input">
            </div>

            <div class="tf-card" style="margin-bottom: 20px;">
                <div class="tf-card-head">
                    <div class="tf-card-title">
                        <div class="tf-card-icon indigo"><i class="fas fa-boxes"></i></div>
                        <div>
                            <h3 class="tf-title-text">الأصناف</h3>
                            <p class="tf-title-sub">تعديل أصناف الفاتورة</p>
                        </div>
                    </div>
                </div>
                <div class="tf-card-body">
                    <div id="items-container">
                        @foreach($invoice->items as $index => $item)
                        <div class="tf-row-item">
                            <div>
                                <label class="tf-label">المنتج</label>
                                <select name="items[{{ $index }}][product_id]" class="tf-select" required>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" @selected($product->id == $item->product_id)>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="tf-label">الوحدة</label>
                                <select name="items[{{ $index }}][selling_unit_id]" class="tf-select">
                                    <option value="">-- اختر الوحدة --</option>
                                    @php
                                        $product = $products->firstWhere('id', $item->product_id);
                                        $sellingUnits = $product ? $product->activeSellingUnits : collect();
                                    @endphp
                                    @forelse($sellingUnits as $unit)
                                        <option value="{{ $unit->id }}" @selected($unit->id == $item->selling_unit_id)>
                                            {{ $unit->unit_label }} ({{ $unit->conversion_factor }}x)
                                        </option>
                                    @empty
                                        <option value="">لا توجد وحدات</option>
                                    @endforelse
                                </select>
                            </div>
                            <div>
                                <label class="tf-label">الكمية</label>
                                <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" step="0.001" class="tf-input" required>
                                <input type="hidden" name="items[{{ $index }}][conversion_factor]" value="{{ $item->conversion_factor ?? 1 }}">
                                <input type="hidden" name="items[{{ $index }}][unit_code]" value="{{ $item->unit_code ?? 'piece' }}">
                            </div>
                            <div>
                                <label class="tf-label">السعر</label>
                                <input type="number" name="items[{{ $index }}][price]" value="{{ $item->unit_price ?? $item->price ?? 0 }}" step="0.01" class="tf-input" required>
                            </div>
                             <div>
                                  <label class="tf-label">خصم %</label>
                                  <input type="number" name="items[{{ $index }}][discount]" value="{{ $item->discount_value ?? 0 }}" step="0.01" min="0" max="100" class="tf-input">
                              </div>
                             <div>
                                  <label class="tf-label">ضريبة %</label>
                                  <input type="number" name="items[{{ $index }}][tax_rate]" value="{{ $item->tax_rate ?? 0 }}" step="0.01" min="0" max="100" class="tf-input">
                             </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="tf-grid-2" style="margin-bottom: 20px;">
                <div class="tf-total-box">
                    <h4 style="font-size: 14px; font-weight: 800; color: var(--tf-text-h); margin-bottom: 16px;">
                        <i class="fas fa-money-bill-wave" style="color: var(--tf-green);"></i> معلومات الدفع
                    </h4>
                    <div class="tf-total-row">
                        <span class="tf-total-label">إجمالي الفاتورة:</span>
                        <span class="tf-total-value">{{ number_format($invoice->calculated_details['net_total'] ?? 0, 2) }} ج.م</span>
                    </div>
                    <div style="margin-top: 12px;">
                        <label class="tf-label">المبلغ المدفوع</label>
                        <input type="number" name="paid" id="paid" value="{{ $invoice->paid ?? 0 }}" step="0.01" min="0" max="{{ $invoice->calculated_details['net_total'] ?? 0 }}" class="tf-input" style="font-weight: 700;">
                    </div>
                    <div style="margin-top: 12px;">
                        <label class="tf-label">المتبقي</label>
                        <div id="remaining-amount" style="font-size: 24px; font-weight: 900; padding: 12px; border-radius: 14px; background: var(--tf-amber-soft); color: var(--tf-amber);">
                            {{ number_format($invoice->calculated_details['remaining'] ?? 0, 2) }} ج.م
                        </div>
                    </div>
                    <div class="tf-alert tf-alert-warning" style="margin-top: 16px;">
                        <span class="tf-badge" id="payment-status-badge">
                            @php
                                if ($invoice->payment_status == 'paid') echo 'مدفوعة بالكامل';
                                elseif ($invoice->payment_status == 'partial') echo 'مدفوعة جزئياً';
                                else echo 'غير مدفوعة';
                            @endphp
                        </span>
                    </div>
                </div>

                <div>
                    <div class="tf-total-box" style="margin-bottom: 16px;">
                        <h4 style="font-size: 14px; font-weight: 800; color: var(--tf-text-h); margin-bottom: 16px;">
                            <i class="fas fa-percent" style="color: var(--tf-amber);"></i> إضافات
                        </h4>
                        <div style="margin-bottom: 12px;">
                            <label class="tf-label">خصم عام</label>
                            <input type="number" name="discount_value" value="{{ $invoice->discount_value ?? 0 }}" step="0.01" min="0" class="tf-input">
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label class="tf-label">ضريبة عامة</label>
                            <input type="number" name="tax_amount" value="{{ $invoice->tax_amount ?? 0 }}" step="0.01" min="0" class="tf-input">
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label class="tf-label">تكلفة الشحن</label>
                            <input type="number" name="shipping_cost" value="{{ $invoice->shipping_cost ?? 0 }}" step="0.01" min="0" class="tf-input">
                        </div>
                        <div>
                            <label class="tf-label">مصاريف أخرى</label>
                            <input type="number" name="other_charges" value="{{ $invoice->other_charges ?? 0 }}" step="0.01" min="0" class="tf-input">
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="tf-label"><i class="fas fa-sticky-note" style="color: var(--tf-violet);"></i> ملاحظات</label>
                <textarea name="notes" rows="3" class="tf-input" placeholder="أضف أي ملاحظات هنا...">{{ $invoice->notes }}</textarea>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 20px; border-top: 1px solid var(--tf-border-soft);">
                <a href="{{ route('invoices.sales.index') }}" class="tf-btn tf-btn-secondary">
                    <i class="fas fa-arrow-right"></i> إلغاء
                </a>
                <button type="submit" class="tf-btn tf-btn-primary">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
            </div>
        </form>
    </div>

    <div class="tf-card tf-section">
        <div class="tf-card-body">
            <div class="tf-alert tf-alert-info">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <i class="fas fa-info-circle" style="color: var(--tf-blue); font-size: 20px; margin-top: 2px;"></i>
                    <div>
                        <h4 style="font-weight: 800; color: var(--tf-text-h); margin-bottom: 8px;">تعليمات التعديل</h4>
                        <ul style="font-size: 13px; color: var(--tf-text-m); line-height: 1.8; padding-right: 16px;">
                            <li>يمكنك تعديل المبلغ المدفوع لتحديث حالة الدفع تلقائياً</li>
                            <li>إذا كان المبلغ المدفوع = الإجمالي، ستصبح الفاتورة "مدفوعة بالكامل"</li>
                            <li>إذا كان المبلغ المدفوع أقل من الإجمالي، ستصبح "مدفوعة جزئياً"</li>
                            <li>إذا كان المبلغ المدفوع = 0، ستصبح "غير مدفوعة"</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paidInput = document.getElementById('paid');
    const remainingDisplay = document.getElementById('remaining-amount');
    const statusBadge = document.getElementById('payment-status-badge');
    const totalAmount = {{ ($invoice->calculated_details['net_total'] ?? 0) }};

    paidInput.addEventListener('input', function() {
        const paid = parseFloat(this.value) || 0;
        const remaining = totalAmount - paid;
        
        remainingDisplay.textContent = remaining.toFixed(2) + ' ج.م';
        
        let statusText = '';
        let statusClass = '';
        
        if (remaining <= 0) {
            statusText = 'مدفوعة بالكامل';
            statusClass = 'tf-badge green';
            remainingDisplay.style.background = 'var(--tf-green-soft)';
            remainingDisplay.style.color = 'var(--tf-green)';
        } else if (paid > 0) {
            statusText = 'مدفوعة جزئياً';
            statusClass = 'tf-badge amber';
            remainingDisplay.style.background = 'var(--tf-amber-soft)';
            remainingDisplay.style.color = 'var(--tf-amber)';
        } else {
            statusText = 'غير مدفوعة';
            statusClass = 'tf-badge red';
            remainingDisplay.style.background = 'var(--tf-red-soft)';
            remainingDisplay.style.color = 'var(--tf-red)';
        }
        
        statusBadge.textContent = statusText;
        statusBadge.className = statusClass;
    });
});
</script>
@endpush
@endsection
