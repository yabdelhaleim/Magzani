@extends('layouts.app')

@section('title', 'تفاصيل المورد')
@section('page-title', 'تفاصيل المورد')

@push('styles')
<style>
/* ===========================
   SUPPLIER SHOW PAGE STYLES
=========================== */

/* Breadcrumb */
.sup-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #94a3b8;
    margin-bottom: 22px;
}
.sup-breadcrumb a { color: #94a3b8; text-decoration: none; transition: color 0.15s; }
.sup-breadcrumb a:hover { color: #6366f1; }
.sup-breadcrumb span { color: #334155; font-weight: 600; }
.sup-breadcrumb i { font-size: 10px; }

/* ===== HEADER CARD ===== */
.sup-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 60%, #9333ea 100%);
    border-radius: 20px;
    padding: 32px;
    color: white;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(99,102,241,0.35);
}
.sup-header::before {
    content: '';
    position: absolute;
    top: -40px; left: -40px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
    pointer-events: none;
}
.sup-header::after {
    content: '';
    position: absolute;
    bottom: -60px; right: 20%;
    width: 260px; height: 260px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
    pointer-events: none;
}
.sup-header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    position: relative;
    z-index: 1;
}
.sup-header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}
.sup-avatar {
    width: 76px; height: 76px;
    background: rgba(255,255,255,0.18);
    border-radius: 20px;
    border: 2px solid rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
    backdrop-filter: blur(4px);
}
.sup-header-name {
    font-size: 26px;
    font-weight: 800;
    margin: 0 0 10px;
    letter-spacing: -0.5px;
}
.sup-header-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.sup-header-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    color: rgba(255,255,255,0.8);
}
.badge-active {
    background: rgba(34,197,94,0.25);
    border: 1px solid rgba(34,197,94,0.4);
    color: #bbf7d0;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.badge-inactive {
    background: rgba(100,116,139,0.3);
    border: 1px solid rgba(100,116,139,0.4);
    color: #cbd5e1;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* Header action buttons */
.sup-header-actions {
    display: flex;
    gap: 10px;
    flex-shrink: 0;
}
.sup-btn-header {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 18px;
    border-radius: 12px;
    background: rgba(255,255,255,0.15);
    border: 1.5px solid rgba(255,255,255,0.25);
    color: white;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Cairo', sans-serif;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s;
    backdrop-filter: blur(4px);
    white-space: nowrap;
}
.sup-btn-header:hover {
    background: rgba(255,255,255,0.25);
    border-color: rgba(255,255,255,0.4);
    color: white;
}

/* ===== STATS GRID ===== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 900px) { .stats-grid { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 500px) { .stats-grid { grid-template-columns: 1fr; } }

.stat-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    padding: 22px;
    transition: box-shadow 0.2s, transform 0.2s;
}
.stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); transform: translateY(-2px); }
.stat-card.highlight { border: 2px solid #fecaca; }

.stat-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.stat-label { font-size: 13px; color: #64748b; font-weight: 500; }
.stat-icon {
    width: 40px; height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.stat-icon.purple { background: #f3e8ff; color: #9333ea; }
.stat-icon.green  { background: #dcfce7; color: #16a34a; }
.stat-icon.red    { background: #fee2e2; color: #dc2626; }
.stat-icon.blue   { background: #dbeafe; color: #2563eb; }

.stat-value { font-size: 24px; font-weight: 800; margin: 0 0 3px; line-height: 1.2; }
.stat-value.purple { color: #9333ea; }
.stat-value.green  { color: #16a34a; }
.stat-value.red    { color: #dc2626; }
.stat-value.blue   { color: #2563eb; }
.stat-sub { font-size: 11px; color: #94a3b8; }

/* ===== TWO COL GRID ===== */
.two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}
@media (max-width: 900px) { .two-col { grid-template-columns: 1fr; } }

/* ===== INFO CARD ===== */
.info-card {
    background: white;
    border-radius: 18px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    overflow: hidden;
}
.info-card-header {
    padding: 18px 22px;
    border-bottom: 1px solid #f8fafc;
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.info-card-header-left {
    display: flex;
    align-items: center;
    gap: 8px;
}
.info-card-body { padding: 18px 22px; }

.info-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: #f8fafc;
    border-radius: 12px;
    margin-bottom: 10px;
    transition: background 0.15s;
}
.info-row:last-child { margin-bottom: 0; }
.info-row:hover { background: #f1f5f9; }

.info-row-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    background: #eef2ff;
    color: #6366f1;
}
.info-row-label { font-size: 11px; color: #94a3b8; margin: 0 0 2px; }
.info-row-value { font-size: 14px; font-weight: 600; color: #1e293b; margin: 0; }

/* ===== TABLE SECTION ===== */
.table-section {
    background: white;
    border-radius: 18px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    overflow: hidden;
}
.table-section-header {
    padding: 18px 22px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.table-section-title {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.table-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 22px;
    border-bottom: 1px solid #f8fafc;
    transition: background 0.15s;
}
.table-row:last-child { border-bottom: none; }
.table-row:hover { background: #f8fafc; }
.table-row-id { font-size: 14px; font-weight: 600; color: #1e293b; margin: 0 0 3px; }
.table-row-date { font-size: 12px; color: #94a3b8; margin: 0; display: flex; align-items: center; gap: 4px; }
.table-row-amount { font-size: 16px; font-weight: 800; }

.empty-state {
    padding: 48px 22px;
    text-align: center;
    color: #94a3b8;
}
.empty-state i { font-size: 36px; display: block; margin-bottom: 10px; opacity: 0.5; }
.empty-state p { font-size: 14px; margin: 0; }

/* Link style */
.link-blue { color: #6366f1; font-size: 13px; text-decoration: none; font-weight: 500; }
.link-blue:hover { text-decoration: underline; }

/* Add payment button */
.btn-add-payment {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 10px;
    background: #16a34a;
    color: white;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Cairo', sans-serif;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s;
}
.btn-add-payment:hover { background: #15803d; color: white; }

/* ===== PAYMENT MODAL ===== */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 100;
    backdrop-filter: blur(3px);
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal-overlay.open { display: flex; }

.modal-box {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 460px;
    overflow: hidden;
    animation: modalIn 0.25s ease;
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-title { font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
.modal-close {
    width: 32px; height: 32px;
    border-radius: 8px;
    background: #f1f5f9;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    transition: background 0.15s;
    font-size: 14px;
}
.modal-close:hover { background: #e2e8f0; }
.modal-body { padding: 24px; }
.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #f1f5f9;
    background: #fafafa;
    display: flex;
    gap: 10px;
}

.modal-field { margin-bottom: 16px; }
.modal-field:last-child { margin-bottom: 0; }
.modal-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.modal-input {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-family: 'Cairo', sans-serif;
    color: #1e293b;
    background: #f8fafc;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    box-sizing: border-box;
}
.modal-input:focus {
    border-color: #6366f1;
    background: white;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
}

.balance-info {
    background: #eef2ff;
    border: 1px solid #c7d2fe;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 13px;
    color: #4338ca;
    margin-bottom: 16px;
}
.balance-info strong { font-weight: 700; }

.btn-modal-save {
    flex: 1;
    padding: 10px;
    border-radius: 10px;
    background: #16a34a;
    color: white;
    border: none;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Cairo', sans-serif;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: background 0.2s;
}
.btn-modal-save:hover { background: #15803d; }
.btn-modal-cancel {
    flex: 1;
    padding: 10px;
    border-radius: 10px;
    background: #f1f5f9;
    color: #475569;
    border: none;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Cairo', sans-serif;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-modal-cancel:hover { background: #e2e8f0; }

/* Responsive header */
@media (max-width: 768px) {
    .sup-header-inner { flex-direction: column; align-items: flex-start; }
    .sup-header-actions { width: 100%; }
    .sup-btn-header { flex: 1; justify-content: center; }
}
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<nav class="sup-breadcrumb">
    <a href="{{ route('suppliers.index') }}">الموردين</a>
    <i class="fas fa-chevron-left"></i>
    <span>{{ $supplier->name }}</span>
</nav>

{{-- ===== HEADER ===== --}}
<div class="sup-header">
    <div class="sup-header-inner">

        {{-- Right: avatar + info --}}
        <div class="sup-header-right">
            <div class="sup-avatar">{{ mb_substr($supplier->name, 0, 2) }}</div>
            <div>
                <h2 class="sup-header-name">{{ $supplier->name }}</h2>
                <div class="sup-header-meta">
                    <span><i class="fas fa-hashtag"></i> {{ $supplier->id }}</span>
                    <span><i class="fas fa-calendar-alt"></i> {{ $supplier->created_at->format('Y-m-d') }}</span>
                    @if($supplier->is_active ?? true)
                        <span class="badge-active"><i class="fas fa-check-circle"></i> نشط</span>
                    @else
                        <span class="badge-inactive"><i class="fas fa-pause-circle"></i> غير نشط</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Left: action buttons --}}
        <div class="sup-header-actions">
            <a href="{{ route('suppliers.edit', $supplier->id) }}" class="sup-btn-header">
                <i class="fas fa-edit"></i> تعديل
            </a>
            <a href="{{ route('suppliers.statement', $supplier->id) }}" class="sup-btn-header">
                <i class="fas fa-file-invoice"></i> كشف الحساب
            </a>
        </div>

    </div>
</div>

{{-- ===== STATS ===== --}}
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">إجمالي المشتريات</span>
            <div class="stat-icon purple"><i class="fas fa-shopping-cart"></i></div>
        </div>
        <div class="stat-value purple">{{ number_format($summary['total_purchases'] ?? 0, 2) }}</div>
        <div class="stat-sub">جنيه مصري</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">إجمالي المدفوعات</span>
            <div class="stat-icon green"><i class="fas fa-money-bill-wave"></i></div>
        </div>
        <div class="stat-value green">{{ number_format($summary['total_paid'] ?? 0, 2) }}</div>
        <div class="stat-sub">جنيه مصري</div>
    </div>

    <div class="stat-card highlight">
        <div class="stat-top">
            <span class="stat-label">الرصيد المستحق</span>
            <div class="stat-icon red"><i class="fas fa-wallet"></i></div>
        </div>
        <div class="stat-value red">{{ number_format($summary['balance'] ?? 0, 2) }}</div>
        <div class="stat-sub">جنيه مصري</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">عدد الفواتير</span>
            <div class="stat-icon blue"><i class="fas fa-file-invoice"></i></div>
        </div>
        <div class="stat-value blue">{{ $supplier->purchaseInvoices()->count() }}</div>
        <div class="stat-sub">فاتورة</div>
    </div>

</div>

{{-- ===== INFO SECTIONS ===== --}}
<div class="two-col">

    {{-- Contact Info --}}
    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-header-left">
                <i class="fas fa-address-card" style="color:#6366f1;"></i>
                معلومات الاتصال
            </div>
        </div>
        <div class="info-card-body">
            <div class="info-row">
                <div class="info-row-icon"><i class="fas fa-phone-alt"></i></div>
                <div>
                    <p class="info-row-label">الهاتف</p>
                    <p class="info-row-value">{{ $supplier->phone ?? 'غير متوفر' }}</p>
                </div>
            </div>
            <div class="info-row">
                <div class="info-row-icon"><i class="fas fa-envelope"></i></div>
                <div>
                    <p class="info-row-label">البريد الإلكتروني</p>
                    <p class="info-row-value">{{ $supplier->email ?? 'غير متوفر' }}</p>
                </div>
            </div>
            <div class="info-row">
                <div class="info-row-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div>
                    <p class="info-row-label">العنوان</p>
                    <p class="info-row-value">{{ $supplier->address ?? 'غير متوفر' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Info --}}
    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-header-left">
                <i class="fas fa-info-circle" style="color:#8b5cf6;"></i>
                معلومات إضافية
            </div>
        </div>
        <div class="info-card-body">
            <div class="info-row">
                <div class="info-row-icon" style="background:#f0fdf4;color:#16a34a;"><i class="fas fa-calendar-plus"></i></div>
                <div>
                    <p class="info-row-label">تاريخ الإضافة</p>
                    <p class="info-row-value">{{ $supplier->created_at->format('Y-m-d h:i A') }}</p>
                </div>
            </div>
            <div class="info-row">
                <div class="info-row-icon" style="background:#fff7ed;color:#ea580c;"><i class="fas fa-history"></i></div>
                <div>
                    <p class="info-row-label">آخر تحديث</p>
                    <p class="info-row-value">{{ $supplier->updated_at->format('Y-m-d h:i A') }}</p>
                </div>
            </div>
            @if($supplier->notes)
            <div class="info-row" style="align-items:flex-start;">
                <div class="info-row-icon" style="background:#fefce8;color:#ca8a04;margin-top:2px;"><i class="fas fa-sticky-note"></i></div>
                <div>
                    <p class="info-row-label">ملاحظات</p>
                    <p class="info-row-value" style="font-weight:400;color:#374151;line-height:1.5;">{{ $supplier->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

</div>

{{-- ===== INVOICES & PAYMENTS ===== --}}
<div class="two-col">

    {{-- Recent Invoices --}}
    <div class="table-section">
        <div class="table-section-header">
            <div class="table-section-title">
                <i class="fas fa-file-invoice" style="color:#8b5cf6;"></i>
                أحدث الفواتير
            </div>
            <a href="{{ route('suppliers.statement', $supplier->id) }}" class="link-blue">عرض الكل</a>
        </div>

        @forelse($recentInvoices as $invoice)
        <div class="table-row">
            <div>
                <p class="table-row-id">فاتورة #{{ $invoice->id }}</p>
                <p class="table-row-date"><i class="fas fa-calendar"></i> {{ $invoice->invoice_date }}</p>
            </div>
            <span class="table-row-amount" style="color:#8b5cf6;">{{ number_format($invoice->total, 2) }} ج.م</span>
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>لا توجد فواتير بعد</p>
        </div>
        @endforelse
    </div>

    {{-- Recent Payments --}}
    <div class="table-section">
        <div class="table-section-header">
            <div class="table-section-title">
                <i class="fas fa-money-bill-wave" style="color:#16a34a;"></i>
                أحدث المدفوعات
            </div>
            <button class="btn-add-payment" onclick="openPaymentModal()">
                <i class="fas fa-plus"></i> إضافة سداد
            </button>
        </div>

        @forelse($recentPayments as $payment)
        <div class="table-row">
            <div>
                <p class="table-row-id">سداد #{{ $payment->id }}</p>
                <p class="table-row-date"><i class="fas fa-calendar"></i> {{ $payment->payment_date }}</p>
            </div>
            <span class="table-row-amount" style="color:#16a34a;">{{ number_format($payment->amount, 2) }} ج.م</span>
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>لا توجد مدفوعات بعد</p>
        </div>
        @endforelse
    </div>

</div>

{{-- ===== PAYMENT MODAL ===== --}}
<div class="modal-overlay" id="paymentModal" onclick="handleModalOverlayClick(event)">
    <div class="modal-box" id="modalBox">

        <div class="modal-header">
            <div class="modal-title">
                <i class="fas fa-plus-circle" style="color:#16a34a;"></i>
                إضافة سداد جديد
            </div>
            <button class="modal-close" onclick="closePaymentModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('suppliers.payments.store', $supplier->id) }}" method="POST">
            @csrf
            <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">

            <div class="modal-body">
                <div class="balance-info">
                    <strong>الرصيد الحالي:</strong>
                    {{ number_format($summary['balance'] ?? 0, 2) }} ج.م
                </div>

                <div class="modal-field">
                    <label class="modal-label">تاريخ السداد <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="modal-input" required>
                </div>

                <div class="modal-field">
                    <label class="modal-label">المبلغ <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="amount" step="0.01" placeholder="0.00" class="modal-input" required>
                </div>

                <div class="modal-field">
                    <label class="modal-label">طريقة الدفع</label>
                    <select name="method" class="modal-input">
                        <option value="نقدي">نقدي</option>
                        <option value="تحويل بنكي">تحويل بنكي</option>
                        <option value="شيك">شيك</option>
                    </select>
                </div>

                <div class="modal-field">
                    <label class="modal-label">ملاحظات</label>
                    <textarea name="notes" rows="2" class="modal-input" style="resize:none;"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closePaymentModal()">إلغاء</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fas fa-save"></i> حفظ السداد
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openPaymentModal() {
        document.getElementById('paymentModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closePaymentModal() {
        document.getElementById('paymentModal').classList.remove('open');
        document.body.style.overflow = '';
    }
    function handleModalOverlayClick(e) {
        if (e.target === document.getElementById('paymentModal')) {
            closePaymentModal();
        }
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePaymentModal();
    });
</script>
@endpush

@endsection