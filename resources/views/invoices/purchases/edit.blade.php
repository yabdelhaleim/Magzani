@extends('layouts.app')

@section('title', 'تعديل فاتورة شراء')
@section('page-title', 'تعديل فاتورة شراء')

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
        transition: transform .4s cubic-bezier(.34,1.56,.64,1);
    }
    .tf-card:hover .tf-card-icon { animation: iconBounce .6s ease; }
    .tf-card.violet .tf-card-icon { background: var(--tf-violet); color: var(--tf-surface); }
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
    .tf-btn-violet:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(124,92,236,0.45); }

    /* هيدر الشركة الاحترافي */
    .company-invoice-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 25px 35px;
        border-radius: 20px;
        margin-bottom: 25px;
        border: 1px solid var(--tf-border);
        box-shadow: var(--tf-shadow-sm);
        position: relative;
        overflow: hidden;
    }
    .company-invoice-header::before {
        content: '';
        position: absolute;
        top: 0; right: 0; width: 6px; height: 100%;
        background: var(--tf-violet);
    }
    .header-info h1 {
        font-size: 24px;
        font-weight: 900;
        color: var(--tf-text-h);
        margin: 0 0 5px 0;
    }
    .header-info p {
        font-size: 13px;
        color: var(--tf-text-m);
        margin: 0;
        font-weight: 600;
    }
    .header-logo img {
        max-height: 70px;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.05));
    }
    .header-badge {
        background: var(--tf-violet-soft);
        color: var(--tf-violet);
        padding: 6px 15px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
    }

    /* تحسين شكل المربعات في الجدول */
    .tf-table-input {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid var(--tf-border-soft);
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        background: #fcfdfe;
        text-align: center;
    }
    .tf-table-input:focus {
        border-color: var(--tf-violet);
        background: white;
        box-shadow: 0 0 0 4px rgba(124, 92, 236, 0.1);
        outline: none;
        transform: translateY(-1px);
    }

    @media (max-width: 992px) {
        .tf-table-wrapper { border: none; }
        .tf-table thead { display: none; }
        .tf-table tbody tr {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            padding: 20px;
            background: white;
            border-radius: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--tf-border);
            box-shadow: var(--tf-shadow-sm);
        }
        .tf-table tbody td {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0 !important;
            border: none !important;
        }
        .tf-table tbody td::before {
            content: attr(data-label);
            font-size: 11px;
            font-weight: 800;
            color: var(--tf-text-m);
            text-transform: uppercase;
        }
        .tf-item-row {
            grid-template-columns: 1fr 1fr;
            padding: 20px;
        }
        .tf-item-row > div:first-child { grid-column: span 2; }
        .tf-item-row > button { grid-column: span 2; width: 100% !important; margin-top: 10px; }
    }

    .tf-btn-secondary {
        background: var(--tf-surface); color: var(--tf-text-b);
        border: 1.5px solid var(--tf-border);
    }
    .tf-btn-secondary:hover { background: var(--tf-surface2); }
    .tf-btn-green {
        background: linear-gradient(135deg, var(--tf-green), #0d8a6e);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(15,170,126,0.35);
    }
    .tf-btn-green:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15,170,126,0.45); }

    .tf-input, .tf-select {
        width: 100%; padding: 12px 16px;
        border: 1.5px solid var(--tf-border); border-radius: 14px;
        font-size: 14px; font-family: 'Cairo', sans-serif;
        color: var(--tf-text-h); background: var(--tf-surface);
        transition: all .25s; outline: none;
    }
    .tf-input:focus, .tf-select:focus {
        border-color: var(--tf-violet);
        box-shadow: 0 0 0 3px rgba(124,92,236,0.12);
    }

    .tf-label {
        display: block; font-size: 12px; font-weight: 700;
        color: var(--tf-text-m); margin-bottom: 6px;
    }

    .tf-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    @media (max-width: 900px) { .tf-grid-3 { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px) { .tf-grid-3 { grid-template-columns: 1fr; } }

    .tf-item-row {
        display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 50px;
        gap: 12px; align-items: center;
        padding: 16px; border-radius: 14px; background: var(--tf-surface2);
        border: 1px solid var(--tf-border); margin-bottom: 12px;
        transition: all .2s;
    }
    .tf-item-row:hover { border-color: var(--tf-violet); }

    .tf-error-box {
        padding: 16px; border-radius: 14px; border-right: 4px solid var(--tf-red);
        background: var(--tf-red-soft); margin-bottom: 20px;
    }
    .tf-error-box ul { margin: 0; padding-right: 20px; }
    .tf-error-box li { color: var(--tf-red); font-size: 14px; }

    .tf-alert {
        padding: 16px; border-radius: 14px; border-right: 4px solid;
        background: var(--tf-surface);
    }
    .tf-alert-warning { border-color: var(--tf-amber); background: var(--tf-amber-soft); }

    .tf-invoice-num {
        display: inline-block; padding: 8px 16px;
        border-radius: 50px; font-size: 14px; font-weight: 800;
        background: var(--tf-violet-soft); color: var(--tf-violet);
    }

    .tf-action-btn-del {
        display: inline-flex; align-items: center; justify-content: center;
        width: 40px; height: 40px; border-radius: 10px;
        cursor: pointer; transition: all .2s; border: none;
        background: var(--tf-red-soft); color: var(--tf-red);
    }
    .tf-action-btn-del:hover { background: var(--tf-red); color: var(--tf-surface); }
</style>
@endpush

@push('scripts')
<script>
let itemIndex = {{ count($invoice->items) }};

function addItem() {
    const container = document.getElementById('items-container');
    const firstItem = container.querySelector('.tf-item-row');
    const newItem = firstItem.cloneNode(true);
    
    newItem.querySelectorAll('select, input').forEach(field => {
        if (field.name) {
            field.name = field.name.replace(/items\[\d+\]/, `items[${itemIndex}]`);
        }
        if (field.type !== 'button' && !field.classList.contains('item-total')) {
            field.value = '';
        }
    });
    
    newItem.querySelector('.item-total').value = '0';
    
    container.appendChild(newItem);
    itemIndex++;
}

function removeItem(button) {
    const container = document.getElementById('items-container');
    const items = container.querySelectorAll('.tf-item-row');
    
    if (items.length > 1) {
        button.closest('.tf-item-row').remove();
    } else {
        alert('يجب وجود صنف واحد على الأقل');
    }
}

function calculateItemTotal(input) {
    const row = input.closest('.tf-item-row');
    const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const total = qty * price;
    
    row.querySelector('.item-total').value = total.toFixed(2);
}
</script>
@endpush

@section('content')
<div class="tf-page">
    <!-- هيدر الشركة الاحترافي -->
    <div class="company-invoice-header tf-section">
        <div class="header-info">
            <h1>{{ $company->name ?? 'نظام ماجزني لإدارة المخازن' }}</h1>
            <p><i class="fas fa-map-marker-alt"></i> {{ $company->address ?? 'العنوان غير مسجل' }}</p>
            <p><i class="fas fa-phone"></i> {{ $company->phone ?? '01XXXXXXXXX' }}</p>
            <div class="header-badge">
                <i class="fas fa-file-invoice-dollar"></i>
                نظام الفواتير الذكي
            </div>
        </div>
        <div class="header-logo">
            @if(isset($company->logo) && $company->logo)
                <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo">
            @else
                <div style="width: 70px; height: 70px; background: var(--tf-violet-soft); border-radius: 15px; display: flex; align-items: center; justify-content: center; font-weight: 900; color: var(--tf-violet); font-size: 24px;">
                    M
                </div>
            @endif
        </div>
    </div>

    @if($errors->any())
    <div class="tf-error-box tf-section">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon violet"><i class="fas fa-edit"></i></div>
                <div>
                    <h2 class="tf-title-text">تعديل فاتورة شراء</h2>
                    <p class="tf-title-sub">{{ $invoice->invoice_number }}</p>
                </div>
            </div>
            <span class="tf-invoice-num">{{ $invoice->invoice_number }}</span>
        </div>

        <form method="POST" action="{{ route('invoices.purchases.update', $invoice->id) }}" class="tf-card-body">
            @csrf
            @method('PUT')

            <div class="tf-grid-3" style="margin-bottom: 20px;">
                <div>
                    <label class="tf-label"><i class="fas fa-user-tag" style="color: var(--tf-violet);"></i> المورد <span style="color: var(--tf-red);">*</span></label>
                    <select name="supplier_id" class="tf-select" required>
                        <option value="">اختر المورد</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ (old('supplier_id', $invoice->supplier_id) == $supplier->id) ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <p style="color: var(--tf-red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="tf-label"><i class="fas fa-warehouse" style="color: var(--tf-indigo);"></i> المخزن <span style="color: var(--tf-red);">*</span></label>
                    <select name="warehouse_id" class="tf-select" required>
                        <option value="">اختر المخزن</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ (old('warehouse_id', $invoice->warehouse_id) == $warehouse->id) ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <p style="color: var(--tf-red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="tf-label"><i class="fas fa-calendar" style="color: var(--tf-blue);"></i> تاريخ الفاتورة <span style="color: var(--tf-red);">*</span></label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}" class="tf-input" required>
                    @error('invoice_date')
                        <p style="color: var(--tf-red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="tf-card" style="margin-bottom: 20px;">
                <div class="tf-card-head">
                    <div class="tf-card-title">
                        <div class="tf-card-icon violet" style="background: var(--tf-violet-soft); color: var(--tf-violet);"><i class="fas fa-boxes"></i></div>
                        <div>
                            <h3 class="tf-title-text">الأصناف</h3>
                            <p class="tf-title-sub">تعديل أصناف الفاتورة</p>
                        </div>
                    </div>
                    <button type="button" onclick="addItem()" class="tf-btn tf-btn-primary">
                        <i class="fas fa-plus"></i> إضافة صنف
                    </button>
                </div>
                <div class="tf-card-body">
                    <div id="items-container">
                        @foreach($invoice->items as $index => $item)
                        <div class="tf-item-row">
                            <div>
                                <label class="tf-label">الصنف <span style="color: var(--tf-red);">*</span></label>
                                <select name="items[{{ $index }}][product_id]" class="tf-select" required>
                                    <option value="">اختر الصنف</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="tf-label">الكمية <span style="color: var(--tf-red);">*</span></label>
                                <input type="number" name="items[{{ $index }}][qty]" step="0.01" min="0.01" value="{{ $item->qty }}" class="item-qty tf-input" onchange="calculateItemTotal(this)" required>
                            </div>
                            <div>
                                <label class="tf-label">سعر الوحدة <span style="color: var(--tf-red);">*</span></label>
                                <input type="number" name="items[{{ $index }}][price]" step="0.01" min="0" value="{{ $item->price }}" class="item-price tf-input" onchange="calculateItemTotal(this)" required>
                            </div>
                            <div>
                                <label class="tf-label">الإجمالي</label>
                                <input type="text" class="item-total tf-input" style="background: var(--tf-surface2);" readonly value="{{ number_format($item->total, 2) }}">
                            </div>
                            <button type="button" onclick="removeItem(this)" class="tf-action-btn-del" title="حذف">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="tf-grid-3" style="margin-bottom: 20px;">
                <div>
                    <label class="tf-label"><i class="fas fa-percent" style="color: var(--tf-amber);"></i> الخصم (اختياري)</label>
                    <input type="number" name="discount" step="0.01" min="0" value="{{ old('discount', $invoice->discount) }}" class="tf-input" placeholder="0">
                </div>
                <div>
                    <label class="tf-label"><i class="fas fa-tax" style="color: var(--tf-blue);"></i> الضريبة (اختياري)</label>
                    <input type="number" name="tax" step="0.01" min="0" value="{{ old('tax', $invoice->tax) }}" class="tf-input" placeholder="0">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="tf-label"><i class="fas fa-sticky-note" style="color: var(--tf-violet);"></i> ملاحظات (اختياري)</label>
                <textarea name="notes" rows="3" class="tf-input" placeholder="أي ملاحظات إضافية...">{{ old('notes', $invoice->notes) }}</textarea>
            </div>

            <div class="tf-alert tf-alert-warning" style="margin-bottom: 20px;">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <i class="fas fa-exclamation-triangle" style="color: var(--tf-amber); font-size: 20px; margin-top: 2px;"></i>
                    <div>
                        <p style="font-weight: 800; color: var(--tf-text-h);">تنبيه!</p>
                        <p style="font-size: 13px; color: var(--tf-text-m);">عند تعديل الفاتورة، سيتم عكس الكميات القديمة من المخزن ثم إضافة الكميات الجديدة.</p>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="tf-btn tf-btn-green">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
                <a href="{{ route('invoices.purchases.show', $invoice->id) }}" class="tf-btn tf-btn-secondary">
                    <i class="fas fa-times"></i> إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection