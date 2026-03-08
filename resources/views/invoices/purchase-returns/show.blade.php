@extends('layouts.app')

@section('title', 'تفاصيل مرتجع شراء')

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

    .inv-page {
        max-width: 960px;
        margin: 0 auto;
        padding: 24px 16px 90px;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

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

    .status-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: 50px;
        font-size: 0.8rem; font-weight: 700;
    }

    .status-draft { background: #f3f4f6; color: #6b7280; }
    .status-confirmed { background: var(--success-soft); color: var(--success); }
    .status-cancelled { background: var(--danger-soft); color: var(--danger); }

    .inv-card {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .inv-header {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        padding: 28px;
        border-bottom: 1px solid var(--border);
    }

    .inv-logo-area h1 {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary);
    }

    .inv-logo-area p {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .inv-info {
        text-align: left;
    }

    .inv-info h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 8px;
    }

    .inv-meta {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .inv-meta span {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .inv-meta strong {
        color: var(--text-main);
        font-weight: 600;
    }

    .inv-details {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        padding: 24px 28px;
        background: var(--surface-2);
    }

    .detail-item label {
        display: block;
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .detail-item span {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-main);
    }

    .inv-table-wrap {
        overflow-x: auto;
    }

    .inv-table {
        width: 100%;
        border-collapse: collapse;
    }

    .inv-table th,
    .inv-table td {
        padding: 14px 20px;
        text-align: right;
    }

    .inv-table th {
        background: var(--surface-2);
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border);
    }

    .inv-table td {
        font-size: 0.9rem;
        color: var(--text-main);
        border-bottom: 1px solid var(--border);
    }

    .inv-table tr:last-child td {
        border-bottom: none;
    }

    .inv-table .text-left { text-align: left; }
    .inv-table .text-center { text-align: center; }
    .inv-table .font-bold { font-weight: 700; }

    .inv-totals {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        padding: 24px 28px;
        gap: 8px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        width: 280px;
        font-size: 0.9rem;
    }

    .total-row.grand {
        padding-top: 12px;
        margin-top: 8px;
        border-top: 2px solid var(--primary);
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--danger);
    }

    .total-row span:first-child {
        color: var(--text-muted);
    }

    .total-row.grand span:first-child {
        color: var(--text-main);
    }

    .inv-notes {
        padding: 24px 28px;
        border-top: 1px solid var(--border);
    }

    .inv-notes h4 {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 8px;
    }

    .inv-notes p {
        font-size: 0.85rem;
        color: var(--text-muted);
        line-height: 1.6;
    }

    .inv-footer {
        text-align: center;
        padding: 24px;
        color: var(--text-muted);
        font-size: 0.8rem;
    }

    @media (max-width: 768px) {
        .inv-header {
            grid-template-columns: 1fr;
            text-align: center;
        }
        .inv-info {
            text-align: center;
        }
        .inv-details {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="inv-page">
    <!-- Header -->
    <div class="inv-topbar">
        <div class="inv-topbar-title">
            <h2>مرتجع شراء رقم: {{ $purchaseReturn->return_number }}</h2>
            <p>تفاصيل المرتجع والمعلومات الكاملة</p>
        </div>
        <div class="topbar-actions">
            <a href="{{ route('invoices.purchase-returns.index') }}" class="btn btn-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                رجوع
            </a>
            @if($purchaseReturn->status == 'draft')
            <a href="{{ route('invoices.purchase-returns.edit', $purchaseReturn->id) }}" class="btn btn-edit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                تعديل
            </a>
            @endif
            <button onclick="window.print()" class="btn btn-print">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                طباعة
            </button>
        </div>
    </div>

    <!-- Status -->
    <div>
        @switch($purchaseReturn->status)
            @case('draft')
                <span class="status-badge status-draft">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
                    مسودة
                </span>
                @break
            @case('confirmed')
                <span class="status-badge status-confirmed">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                   مؤكد
                </span>
                @break
            @case('cancelled')
                <span class="status-badge status-cancelled">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                    ملغى
                </span>
                @break
        @endswitch
    </div>

    <!-- Invoice Card -->
    <div class="inv-card">
        <!-- Header -->
        <div class="inv-header">
            <div class="inv-logo-area">
                <h1>مرتجع شراء</h1>
                <p>معلومات المرتجع الأساسية</p>
            </div>
            <div class="inv-info">
                <h3>رقم المرتجع: {{ $purchaseReturn->return_number }}</h3>
                <div class="inv-meta">
                    <span>تاريخ الإرجاع: <strong>{{ $purchaseReturn->return_date->format('Y-m-d') }}</strong></span>
                    @if($purchaseReturn->created_by)
                    <span>أنشئ بواسطة: <strong>{{ $purchaseReturn->creator->name ?? 'غير معروف' }}</strong></span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="inv-details">
            <div class="detail-item">
                <label>الفاتورة الأصلية</label>
                @if($purchaseReturn->purchaseInvoice)
                <span>
                    <a href="{{ route('invoices.purchases.show', $purchaseReturn->purchaseInvoice->id) }}" class="text-blue-600 hover:underline">
                        {{ $purchaseReturn->purchaseInvoice->invoice_number }}
                    </a>
                </span>
                @else
                <span>---</span>
                @endif
            </div>
            <div class="detail-item">
                <label>المورد</label>
                <span>{{ $purchaseReturn->purchaseInvoice->supplier->name ?? 'غير محدد' }}</span>
            </div>
            <div class="detail-item">
                <label>المخزن</label>
                <span>{{ $purchaseReturn->purchaseInvoice->warehouse->name ?? 'غير محدد' }}</span>
            </div>
        </div>

        <!-- Items Table -->
        <div class="inv-table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الصنف</th>
                        <th class="text-center">الكمية</th>
                        <th class="text-left">السعر</th>
                        <th class="text-left">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseReturn->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $item->product->name ?? 'غير معروف' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-left">{{ number_format($item->cost, 2) }} ج.م</td>
                        <td class="text-left font-bold">{{ number_format($item->total, 2) }} ج.م</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 40px;">
                            لا توجد أصناف في هذا المرتجع
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="inv-totals">
            <div class="total-row">
                <span>المجموع</span>
                <span>{{ number_format($purchaseReturn->subtotal, 2) }} ج.م</span>
            </div>
            @if($purchaseReturn->discount_amount > 0)
            <div class="total-row">
                <span>الخصم</span>
                <span>- {{ number_format($purchaseReturn->discount_amount, 2) }} ج.م</span>
            </div>
            @endif
            @if($purchaseReturn->tax_amount > 0)
            <div class="total-row">
                <span>الضريبة</span>
                <span>{{ number_format($purchaseReturn->tax_amount, 2) }} ج.م</span>
            </div>
            @endif
            <div class="total-row grand">
                <span>الإجمالي</span>
                <span>{{ number_format($purchaseReturn->total, 2) }} ج.م</span>
            </div>
        </div>

        <!-- Notes -->
        @if($purchaseReturn->return_reason || $purchaseReturn->notes)
        <div class="inv-notes">
            @if($purchaseReturn->return_reason)
            <h4>سبب الإرجاع</h4>
            <p>{{ $purchaseReturn->return_reason }}</p>
            @endif
            @if($purchaseReturn->notes)
            <h4 style="margin-top: 16px;">ملاحظات</h4>
            <p>{{ $purchaseReturn->notes }}</p>
            @endif
        </div>
        @endif

        <!-- Footer -->
        <div class="inv-footer">
            <p>شكراً لتعاملكم معنا</p>
        </div>
    </div>
</div>
@endsection
