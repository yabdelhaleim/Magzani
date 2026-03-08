@extends('layouts.app')

@section('title', 'تفاصيل فاتورة شراء')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@300;400;500;700;800&display=swap');

    :root {
        --primary:      #1a1f3a;
        --accent:       #4f6ef7;
        --accent-light: #6b84ff;
        --accent-soft:  rgba(79,110,247,0.10);
        --success:      #059669;
        --success-soft: rgba(5,150,105,0.10);
        --danger:       #e03355;
        --danger-soft:  rgba(224,51,85,0.10);
        --warning:      #d97706;
        --warning-soft: rgba(217,119,6,0.10);
        --purple:       #7c3aed;
        --purple-soft:  rgba(124,58,237,0.10);
        --surface:      #ffffff;
        --surface-2:    #f8f9fd;
        --border:       rgba(0,0,0,0.07);
        --text-main:    #1a1f3a;
        --text-muted:   #8b92a5;
        --shadow-sm:    0 2px 8px rgba(0,0,0,0.06);
        --shadow-md:    0 8px 24px rgba(0,0,0,0.10);
        --radius:       18px;
        --radius-sm:    10px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Cairo', 'Tajawal', sans-serif;
        background: var(--surface-2);
        color: var(--text-main);
        direction: rtl;
    }

    /* ── PAGE ── */
    .inv-page {
        max-width: 960px;
        margin: 0 auto;
        padding: 24px 16px 90px;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    /* ── HEADER BAR ── */
    .inv-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .inv-topbar-title h2 {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--primary);
        letter-spacing: -0.5px;
        line-height: 1.2;
    }

    .inv-topbar-title p { font-size: 0.82rem; color: var(--text-muted); margin-top: 3px; }

    .topbar-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }

    /* ── BUTTONS ── */
    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 10px 18px; border-radius: 50px;
        font-family: 'Cairo', sans-serif;
        font-size: 0.83rem; font-weight: 700;
        text-decoration: none; border: none; cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
        white-space: nowrap;
    }

    .btn svg { width: 15px; height: 15px; }
    .btn:hover { transform: translateY(-1px); }

    .btn-edit   { background: var(--accent-soft); color: var(--accent); border: 1.5px solid rgba(79,110,247,0.2); }
    .btn-edit:hover { background: var(--accent); color: #fff; box-shadow: 0 6px 18px rgba(79,110,247,0.3); }

    .btn-print  { background: var(--purple-soft); color: var(--purple); border: 1.5px solid rgba(124,58,237,0.2); }
    .btn-print:hover { background: var(--purple); color: #fff; box-shadow: 0 6px 18px rgba(124,58,237,0.3); }

    .btn-back   { background: var(--surface); color: var(--text-muted); border: 1.5px solid var(--border); }
    .btn-back:hover { background: var(--accent-soft); border-color: var(--accent); color: var(--accent); }

    .btn-delete { background: var(--danger-soft); color: var(--danger); border: 1.5px solid rgba(224,51,85,0.2); }
    .btn-delete:hover { background: var(--danger); color: #fff; box-shadow: 0 6px 18px rgba(224,51,85,0.3); }

    /* ── SUCCESS ALERT ── */
    .success-alert {
        background: var(--success-soft);
        border: 1px solid rgba(5,150,105,0.2);
        border-radius: var(--radius-sm);
        padding: 13px 18px;
        color: #047857;
        font-size: 0.85rem;
        font-weight: 500;
        display: flex; align-items: center; gap: 9px;
    }

    .success-alert svg { width: 18px; height: 18px; flex-shrink: 0; }

    /* ── CARD ── */
    .inv-card {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
        overflow: hidden;
        animation: fadeUp 0.35s ease both;
    }

    .inv-card:nth-child(2) { animation-delay: 0.05s; }
    .inv-card:nth-child(3) { animation-delay: 0.10s; }
    .inv-card:nth-child(4) { animation-delay: 0.15s; }
    .inv-card:nth-child(5) { animation-delay: 0.20s; }

    .inv-card-header {
        padding: 16px 22px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: 10px;
    }

    .card-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .card-icon svg { width: 17px; height: 17px; }
    .inv-card-header h3 { font-size: 0.93rem; font-weight: 700; color: var(--primary); }

    .inv-card-body { padding: 20px 22px; }

    /* ── INFO GRID (4 boxes) ── */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .info-box {
        background: var(--surface-2);
        border-radius: 12px;
        padding: 14px 16px;
        border: 1.5px solid var(--border);
        position: relative;
        overflow: hidden;
    }

    .info-box::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 3px; height: 100%;
    }

    .info-box.purple::before { background: var(--purple); }
    .info-box.blue::before   { background: var(--accent); }
    .info-box.green::before  { background: var(--success); }
    .info-box.orange::before { background: var(--warning); }

    .info-label { font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 5px; }

    .info-val { font-size: 0.95rem; font-weight: 800; color: var(--primary); }
    .info-box.purple .info-val { color: var(--purple); }

    /* ── STATUS BADGE ── */
    .status-wrap { display: flex; align-items: center; gap: 10px; }
    .status-label { font-size: 0.78rem; font-weight: 600; color: var(--text-muted); }

    .badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 13px; border-radius: 50px;
        font-size: 0.75rem; font-weight: 700;
    }

    .badge svg { width: 12px; height: 12px; }
    .badge-paid    { background: var(--success-soft); color: var(--success); }
    .badge-pending { background: var(--warning-soft); color: var(--warning); }
    .badge-cancelled { background: var(--danger-soft); color: var(--danger); }

    /* ── ITEMS TABLE ── */
    .items-table-wrap { overflow-x: auto; }

    .items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table thead tr {
        background: var(--surface-2);
        border-bottom: 2px solid var(--border);
    }

    .items-table th {
        padding: 12px 16px;
        text-align: right;
        font-size: 0.7rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .items-table tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background 0.15s;
    }

    .items-table tbody tr:last-child { border-bottom: none; }
    .items-table tbody tr:hover { background: #fafbff; }

    .items-table td { padding: 13px 16px; font-size: 0.85rem; vertical-align: middle; }

    .cell-num { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); }

    .product-cell { display: flex; align-items: center; gap: 9px; }
    .product-icon {
        width: 32px; height: 32px;
        background: var(--accent-soft);
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .product-icon svg { width: 14px; height: 14px; color: var(--accent); }
    .product-name { font-weight: 700; color: var(--primary); }
    .product-sku  { font-size: 0.7rem; color: var(--text-muted); margin-top: 1px; }

    .cell-qty   { font-weight: 600; color: var(--primary); }
    .cell-price { color: var(--text-muted); font-weight: 500; }

    .cell-total {
        display: inline-flex; align-items: center; gap: 4px;
        background: var(--success-soft);
        color: var(--success);
        padding: 4px 10px;
        border-radius: 7px;
        font-size: 0.83rem;
        font-weight: 800;
    }

    /* ── MOBILE ITEM CARDS ── */
    .items-mobile { display: none; flex-direction: column; gap: 10px; padding: 14px; }

    .item-mobile-card {
        background: var(--surface-2);
        border: 1.5px solid var(--border);
        border-radius: 12px;
        padding: 13px 14px;
    }

    .item-mc-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 9px; gap: 8px; }
    .item-mc-name { font-size: 0.88rem; font-weight: 700; color: var(--primary); }
    .item-mc-sku  { font-size: 0.7rem; color: var(--text-muted); margin-top: 1px; }

    .item-mc-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
    .item-mc-field-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.4px; font-weight: 700; color: var(--text-muted); margin-bottom: 2px; }
    .item-mc-field-val   { font-size: 0.83rem; font-weight: 700; color: var(--primary); }
    .item-mc-field-val.total { color: var(--success); }

    /* ── FINANCIALS ── */
    .financials-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    /* Notes */
    .notes-box {
        background: var(--surface-2);
        border: 1.5px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        font-size: 0.88rem;
        color: var(--text-main);
        line-height: 1.7;
        min-height: 80px;
    }

    /* Summary */
    .summary-box {
        border: 1.5px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
    }

    .summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 18px;
        border-bottom: 1px solid var(--border);
        gap: 12px;
    }

    .summary-row:last-child { border-bottom: none; }

    .s-label { font-size: 0.83rem; font-weight: 600; color: var(--text-muted); }
    .s-val   { font-size: 0.88rem; font-weight: 700; color: var(--primary); }
    .s-val.discount { color: var(--danger); }
    .s-val.tax      { color: var(--warning); }

    .summary-row.grand {
        background: var(--accent-soft);
        border-top: 2px solid rgba(79,110,247,0.2);
    }

    .summary-row.grand .s-label { font-size: 0.9rem; font-weight: 800; color: var(--primary); }
    .summary-row.grand .s-val   { font-size: 1.3rem; font-weight: 900; color: var(--accent); }

    /* ── META INFO ── */
    .meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .meta-item { display: flex; align-items: center; gap: 8px; }
    .meta-icon {
        width: 32px; height: 32px;
        border-radius: 9px;
        background: var(--surface-2);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .meta-icon svg { width: 14px; height: 14px; color: var(--text-muted); }
    .meta-label { font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; }
    .meta-val   { font-size: 0.83rem; font-weight: 600; color: var(--primary); }

    /* ── BOTTOM ACTIONS ── */
    .inv-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    /* ── MOBILE BOTTOM BAR ── */
    .mobile-action-bar {
        display: none;
        position: fixed;
        bottom: 0; left: 0; right: 0;
        background: var(--surface);
        border-top: 1px solid var(--border);
        padding: 12px 16px;
        gap: 10px;
        z-index: 100;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
    }

    .mobile-action-bar .btn { flex: 1; justify-content: center; border-radius: 12px; }

    /* ── ANIMATION ── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── PRINT ── */
    @media print {
        .inv-topbar, .topbar-actions, .mobile-action-bar, .inv-actions,
        .no-print { display: none !important; }
        body { background: white; }
        .inv-page { padding: 0; }
        .inv-card { box-shadow: none; border: 1px solid #ddd; }
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
        .info-grid { grid-template-columns: repeat(2, 1fr); }
        .financials-grid { grid-template-columns: 1fr; }
        .meta-grid { grid-template-columns: 1fr; }

        /* Hide desktop table, show mobile cards */
        .items-table-wrap { display: none; }
        .items-mobile { display: flex; }

        /* Hide desktop actions, show mobile bar */
        .inv-actions { display: none; }
        .mobile-action-bar { display: flex; }

        .inv-topbar-title h2 { font-size: 1.3rem; }
        .inv-card-body { padding: 16px; }
    }

    @media (max-width: 480px) {
        .info-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
        .info-box { padding: 12px; }
        .info-val { font-size: 0.88rem; }

        .topbar-actions .btn-edit,
        .topbar-actions .btn-print { display: none; }
    }
</style>
@endpush

@section('content')
<div class="inv-page">

    {{-- ── TOP BAR ── --}}
    <div class="inv-topbar no-print">
        <div class="inv-topbar-title">
            <h2>تفاصيل فاتورة الشراء</h2>
            <p>عرض بيانات وأصناف الفاتورة</p>
        </div>
        <div class="topbar-actions">
            <a href="{{ route('invoices.purchases.edit', $invoice->id) }}" class="btn btn-edit">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                تعديل
            </a>
            <button onclick="window.print()" class="btn btn-print">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                طباعة
            </button>
        </div>
    </div>

    {{-- ── SUCCESS ALERT ── --}}
    @if(session('success'))
    <div class="success-alert">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ── CARD 1: INVOICE INFO ── --}}
    <div class="inv-card">
        <div class="inv-card-header">
            <div class="card-icon" style="background:var(--accent-soft)">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--accent)">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3>بيانات الفاتورة</h3>

            {{-- Status Badge in header --}}
            <div class="status-wrap" style="margin-right:auto">
                @if($invoice->status == 'paid')
                    <span class="badge badge-paid">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        مدفوعة
                    </span>
                @elseif($invoice->status == 'pending')
                    <span class="badge badge-pending">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        معلقة
                    </span>
                @else
                    <span class="badge badge-cancelled">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        ملغاة
                    </span>
                @endif
            </div>
        </div>
        <div class="inv-card-body">
            <div class="info-grid">
                <div class="info-box purple">
                    <div class="info-label">رقم الفاتورة</div>
                    <div class="info-val">{{ $invoice->invoice_number }}</div>
                </div>
                <div class="info-box blue">
                    <div class="info-label">المورد</div>
                    <div class="info-val">{{ $invoice->supplier->name }}</div>
                </div>
                <div class="info-box green">
                    <div class="info-label">المخزن</div>
                    <div class="info-val">{{ $invoice->warehouse->name }}</div>
                </div>
                <div class="info-box orange">
                    <div class="info-label">تاريخ الفاتورة</div>
                    <div class="info-val">{{ $invoice->invoice_date->format('Y/m/d') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── CARD 2: ITEMS ── --}}
    <div class="inv-card">
        <div class="inv-card-header">
            <div class="card-icon" style="background:var(--success-soft)">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--success)">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h3>الأصناف</h3>
            <span style="margin-right:auto;font-size:0.78rem;color:var(--text-muted);font-weight:600">
                {{ $invoice->items->count() }} صنف
            </span>
        </div>

        {{-- Desktop Table --}}
        <div class="items-table-wrap">
            <table class="items-table">
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
                        <td><span class="cell-num">{{ $index + 1 }}</span></td>
                        <td>
                            <div class="product-cell">
                                <div class="product-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="product-name">{{ $item->product->name }}</div>
                                    @if(isset($item->product->sku))
                                    <div class="product-sku">{{ $item->product->sku }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><span class="cell-qty">{{ number_format($item->qty, 2) }}</span></td>
                        <td><span class="cell-price">{{ number_format($item->price, 2) }} جنيه</span></td>
                        <td><span class="cell-total">{{ number_format($item->total, 2) }} جنيه</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Item Cards --}}
        <div class="items-mobile">
            @foreach($invoice->items as $index => $item)
            <div class="item-mobile-card">
                <div class="item-mc-top">
                    <div>
                        <div class="item-mc-name">{{ $item->product->name }}</div>
                        @if(isset($item->product->sku))
                        <div class="item-mc-sku">{{ $item->product->sku }}</div>
                        @endif
                    </div>
                    <span class="cell-num" style="background:var(--accent-soft);color:var(--accent);padding:3px 8px;border-radius:50px;font-size:0.68rem">
                        {{ $index + 1 }}
                    </span>
                </div>
                <div class="item-mc-row">
                    <div>
                        <div class="item-mc-field-label">الكمية</div>
                        <div class="item-mc-field-val">{{ number_format($item->qty, 2) }}</div>
                    </div>
                    <div>
                        <div class="item-mc-field-label">السعر</div>
                        <div class="item-mc-field-val">{{ number_format($item->price, 2) }}</div>
                    </div>
                    <div>
                        <div class="item-mc-field-label">الإجمالي</div>
                        <div class="item-mc-field-val total">{{ number_format($item->total, 2) }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── CARD 3: FINANCIALS + NOTES ── --}}
    <div class="inv-card">
        <div class="inv-card-header">
            <div class="card-icon" style="background:var(--warning-soft)">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--warning)">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h3>الملخص المالي</h3>
        </div>
        <div class="inv-card-body">
            <div class="financials-grid">

                {{-- Notes --}}
                @if($invoice->notes)
                <div>
                    <div style="font-size:0.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:8px">الملاحظات</div>
                    <div class="notes-box">{{ $invoice->notes }}</div>
                </div>
                @endif

                {{-- Financial Summary --}}
                <div @if(!$invoice->notes) style="grid-column:1/-1;max-width:420px;margin-right:auto" @endif>
                    <div class="summary-box">
                        <div class="summary-row">
                            <span class="s-label">المجموع الفرعي</span>
                            <span class="s-val">{{ number_format($invoice->subtotal, 2) }} جنيه</span>
                        </div>
                        @if($invoice->discount > 0)
                        <div class="summary-row">
                            <span class="s-label">الخصم</span>
                            <span class="s-val discount">− {{ number_format($invoice->discount, 2) }} جنيه</span>
                        </div>
                        @endif
                        @if($invoice->tax > 0)
                        <div class="summary-row">
                            <span class="s-label">الضريبة</span>
                            <span class="s-val tax">+ {{ number_format($invoice->tax, 2) }} جنيه</span>
                        </div>
                        @endif
                        <div class="summary-row grand">
                            <span class="s-label">الإجمالي النهائي</span>
                            <span class="s-val">{{ number_format($invoice->total, 2) }} جنيه</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── CARD 4: METADATA ── --}}
    <div class="inv-card">
        <div class="inv-card-header">
            <div class="card-icon" style="background:var(--surface-2)">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--text-muted)">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3>معلومات إضافية</h3>
        </div>
        <div class="inv-card-body">
            <div class="meta-grid">
                <div class="meta-item">
                    <div class="meta-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div>
                        <div class="meta-label">تاريخ الإنشاء</div>
                        <div class="meta-val">{{ $invoice->created_at->format('Y/m/d h:i A') }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <div class="meta-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="meta-label">آخر تحديث</div>
                        <div class="meta-val">{{ $invoice->updated_at->format('Y/m/d h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── DESKTOP ACTIONS ── --}}
    <div class="inv-actions no-print">
        <a href="{{ route('invoices.purchases.index') }}" class="btn btn-back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            رجوع للقائمة
        </a>
        <a href="{{ route('invoices.purchases.edit', $invoice->id) }}" class="btn btn-edit">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            تعديل الفاتورة
        </a>
        <form action="{{ route('invoices.purchases.destroy', $invoice->id) }}"
              method="POST" style="display:inline"
              onsubmit="return confirm('⚠️ هل أنت متأكد من حذف هذه الفاتورة؟ سيتم تقليل الكميات من المخزن.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-delete">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                حذف الفاتورة
            </button>
        </form>
    </div>

</div>

{{-- ── MOBILE BOTTOM ACTION BAR ── --}}
<div class="mobile-action-bar no-print">
    <a href="{{ route('invoices.purchases.index') }}" class="btn btn-back">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        رجوع
    </a>
    <a href="{{ route('invoices.purchases.edit', $invoice->id) }}" class="btn btn-edit">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        تعديل
    </a>
    <form action="{{ route('invoices.purchases.destroy', $invoice->id) }}"
          method="POST" style="flex:1"
          onsubmit="return confirm('⚠️ هل أنت متأكد من حذف هذه الفاتورة؟')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-delete" style="width:100%;border-radius:12px;justify-content:center">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            حذف
        </button>
    </form>
</div>

@endsection