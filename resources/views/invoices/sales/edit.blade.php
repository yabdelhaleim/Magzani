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

    .tf-grand-total {
        font-size: 28px; font-weight: 900; color: var(--tf-green);
        text-shadow: 0 2px 4px rgba(15, 170, 126, 0.1);
    }

    /* تحسين شكل جدول الإجماليات */
    .totals-card {
        background: linear-gradient(135deg, #ffffff, #fcfdfe);
        border-radius: 24px;
        padding: 30px;
        border: 1px solid var(--tf-border);
        box-shadow: var(--tf-shadow-card);
    }
    .totals-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--tf-text-h);
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--tf-border-soft);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .total-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }
    .total-item label {
        font-size: 14px;
        font-weight: 600;
        color: var(--tf-text-m);
    }
    .total-item .value {
        font-size: 15px;
        font-weight: 800;
        color: var(--tf-text-h);
    }
    .grand-total-item {
        margin-top: 25px;
        padding-top: 20px;
        border-top: 2px dashed var(--tf-border-soft);
    }

    /* Footer Styles */
    .invoice-footer {
        margin-top: 50px;
        padding: 40px;
        text-align: center;
        border-top: 1px solid var(--tf-border-soft);
        color: var(--tf-text-m);
    }
    .footer-links {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-bottom: 20px;
    }
    .footer-links a {
        color: var(--tf-text-m);
        text-decoration: none;
        font-weight: 700;
        font-size: 13px;
        transition: color 0.2s;
    }
    .footer-links a:hover { color: var(--tf-indigo); }
    
    .powered-by {
        font-size: 12px;
        font-weight: 600;
        opacity: 0.8;
    }
    .powered-by span { color: var(--tf-indigo); font-weight: 800; }
    /* هيدر الشركة الاحترافي */
    .company-invoice-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(to left, #ffffff, #f8faff);
        padding: 30px 40px;
        border-radius: 24px;
        margin-bottom: 30px;
        border: 1px solid var(--tf-border);
        box-shadow: var(--tf-shadow-sm);
        position: relative;
        overflow: hidden;
    }
    .company-invoice-header::before {
        content: '';
        position: absolute;
        top: 0; right: 0; width: 8px; height: 100%;
        background: linear-gradient(to bottom, var(--tf-indigo), var(--tf-blue));
    }
    .company-invoice-header::after {
        content: '\f571';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        left: -20px;
        bottom: -20px;
        font-size: 120px;
        color: rgba(79, 99, 210, 0.03);
        transform: rotate(-15deg);
        pointer-events: none;
    }
    .header-info h1 {
        font-size: 28px;
        font-weight: 900;
        color: var(--tf-text-h);
        margin: 0 0 8px 0;
        letter-spacing: -0.5px;
    }
    .header-info p {
        font-size: 14px;
        color: var(--tf-text-m);
        margin: 4px 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .header-logo img {
        max-height: 85px;
        filter: drop-shadow(0 10px 15px rgba(0,0,0,0.08));
    }
    .header-badge {
        background: white;
        color: var(--tf-indigo);
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 15px;
        box-shadow: 0 4px 12px rgba(79, 99, 210, 0.1);
        border: 1px solid var(--tf-indigo-soft);
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
        border-color: var(--tf-indigo);
        background: white;
        box-shadow: 0 0 0 4px rgba(79, 99, 210, 0.1);
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
        .tf-table tbody td:first-child, 
        .tf-table tbody td:nth-child(2),
        .tf-table tbody td:last-child {
            grid-column: span 2;
        }
        .tf-table-input { text-align: right; }
    }
</style>
@endpush

@section('content')
<div class="tf-page" x-data="{ showConfigModal: false }">
    <!-- هيدر الشركة الاحترافي -->
    <div class="company-invoice-header tf-section">
        <button type="button" @click="showConfigModal = true" style="position: absolute; top: 20px; left: 20px; width: 35px; height: 35px; background: white; border: 1px solid var(--tf-border); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--tf-text-m); cursor: pointer; transition: all 0.2s; z-index: 10;" title="تعديل بيانات الفاتورة">
            <i class="fas fa-cog"></i>
        </button>

        <div class="header-info">
            <h1>{{ $company->name ?? 'نظام ماجزني لإدارة المخازن' }}</h1>
            <p><i class="fas fa-map-marker-alt" style="color: var(--tf-indigo);"></i> {{ $company->address ?? 'العنوان غير مسجل' }}</p>
            <p><i class="fas fa-phone" style="color: var(--tf-indigo);"></i> {{ $company->phone ?? '01XXXXXXXXX' }}</p>
            <div class="header-badge">
                <i class="fas fa-shield-alt"></i>
                نظام الفواتير المعتمد
            </div>
        </div>
        <div class="header-logo">
            @if(isset($company->logo) && $company->logo)
                <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo">
            @else
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--tf-indigo-soft), #e0e7ff); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-weight: 900; color: var(--tf-indigo); font-size: 32px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
                    {{ substr($company->name ?? 'M', 0, 1) }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Quick Setup -->
    <div x-show="showConfigModal" 
         style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 20px;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        
        <div style="background: white; border-radius: 24px; width: 100%; max-width: 500px; box-shadow: var(--tf-shadow-lg); overflow: hidden;" @click.away="showConfigModal = false">
            <div style="padding: 25px; background: var(--tf-surface2); border-bottom: 1px solid var(--tf-border-soft); display: flex; justify-content: space-between; align-items: center;">
                <h3 class="tf-title-text">تعديل بيانات الهوية</h3>
                <button type="button" @click="showConfigModal = false" class="tf-text-m hover:text-red-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="padding: 25px;">
                    <div style="margin-bottom: 20px;">
                        <label class="tf-label">اسم الشركة / النشاط</label>
                        <input type="text" name="name" value="{{ $company->name ?? '' }}" class="tf-input" required>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label class="tf-label">شعار الفاتورة (Logo)</label>
                        <input type="file" name="logo" class="tf-input" accept="image/*">
                    </div>
                    <div class="tf-grid-2">
                        <div>
                            <label class="tf-label">رقم الهاتف</label>
                            <input type="text" name="phone" value="{{ $company->phone ?? '' }}" class="tf-input">
                        </div>
                        <div>
                            <label class="tf-label">العنوان</label>
                            <input type="text" name="address" value="{{ $company->address ?? '' }}" class="tf-input">
                        </div>
                    </div>
                </div>
                <div style="padding: 20px 25px; background: var(--tf-surface2); border-top: 1px solid var(--tf-border-soft); display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" @click="showConfigModal = false" class="tf-btn tf-btn-secondary">إلغاء</button>
                    <button type="submit" class="tf-btn tf-btn-primary">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>

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

            <div class="tf-grid-3 tf-section">
                <div class="totals-card">
                    <h4 class="totals-title">
                        <i class="fas fa-calculator" style="color: var(--tf-green);"></i> الحسابات
                    </h4>
                    <div class="total-item">
                        <label>إجمالي الفاتورة:</label>
                        <span class="value">{{ number_format($invoice->calculated_details['net_total'] ?? 0, 2) }} ج.م</span>
                    </div>
                    <div class="total-item">
                        <label style="color: var(--tf-red);">الخصم العام:</label>
                        <input type="number" name="discount_value" value="{{ $invoice->discount_value ?? 0 }}" step="0.01" min="0" class="tf-table-input" style="width: 100px;">
                    </div>
                    <div class="total-item">
                        <label style="color: var(--tf-blue);">الضريبة:</label>
                        <input type="number" name="tax_amount" value="{{ $invoice->tax_amount ?? 0 }}" step="0.01" min="0" class="tf-table-input" style="width: 100px;">
                    </div>
                    <div class="grand-total-item">
                        <div class="total-item" style="margin-bottom: 0;">
                            <label style="font-size: 16px; color: var(--tf-text-h);">الإجمالي النهائي:</label>
                            <span class="tf-grand-total">{{ number_format($invoice->calculated_details['net_total'] ?? 0, 2) }} ج.م</span>
                        </div>
                    </div>
                </div>

                <div class="totals-card">
                    <h4 class="totals-title">
                        <i class="fas fa-wallet" style="color: var(--tf-blue);"></i> الدفع
                    </h4>
                    <div style="margin-bottom: 20px;">
                        <label class="tf-label">المبلغ المدفوع</label>
                        <div class="square-input" style="background: white; border: 1.5px solid var(--tf-border); border-radius: 14px; padding: 5px;">
                            <input type="number" name="paid" id="paid" value="{{ $invoice->paid ?? 0 }}" step="0.01" min="0" 
                                   class="tf-table-input" style="border: none; background: transparent; font-size: 20px;" placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label class="tf-label">المتبقي</label>
                        <div id="remaining-amount" style="font-size: 28px; font-weight: 900; padding: 15px; border-radius: 18px; text-align: center; transition: all 0.3s;"
                             :style="{{ ($invoice->calculated_details['remaining'] ?? 0) > 0 ? 'background: var(--tf-red-soft); color: var(--tf-red);' : 'background: var(--tf-green-soft); color: var(--tf-green);' }}">
                             {{ number_format($invoice->calculated_details['remaining'] ?? 0, 2) }} ج.م
                        </div>
                    </div>
                    <div style="margin-top: 15px; text-align: center;">
                        <span class="tf-badge" id="payment-status-badge" style="padding: 8px 20px; font-size: 13px;">
                            @php
                                if ($invoice->payment_status == 'paid') echo 'مدفوعة بالكامل';
                                elseif ($invoice->payment_status == 'partial') echo 'مدفوعة جزئياً';
                                else echo 'غير مدفوعة';
                            @endphp
                        </span>
                    </div>
                </div>

                <div class="totals-card">
                    <h4 class="totals-title">
                        <i class="fas fa-sticky-note" style="color: var(--tf-violet);"></i> ملاحظات
                    </h4>
                    <textarea name="notes" class="tf-input" style="height: 150px; resize: none; border-radius: 18px;" placeholder="أضف ملاحظات إضافية هنا...">{{ $invoice->notes }}</textarea>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; align-items: center; padding: 30px; gap: 15px; margin-top: 20px; background: var(--tf-surface2); border-radius: 20px; border: 1px solid var(--tf-border);">
                <a href="{{ route('invoices.sales.index') }}" class="tf-btn tf-btn-secondary" style="padding: 14px 30px;">
                    <i class="fas fa-arrow-right"></i> إلغاء
                </a>
                <button type="submit" class="tf-btn tf-btn-primary" style="padding: 14px 40px; font-size: 16px;">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
            </div>
        </form>
    </div>

    <footer class="invoice-footer tf-section">
        <div class="footer-links">
            <a href="#">الدعم الفني</a>
            <a href="#">سياسة الاستخدام</a>
            <a href="#">دليل المستخدم</a>
        </div>
        <p class="powered-by">تم التطوير بواسطة <span>نظام ماجزني الذكي</span> &copy; {{ date('Y') }}</p>
    </footer>
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
