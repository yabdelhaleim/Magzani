@extends('layouts.app')

@section('title', 'كشف حساب المورد')
@section('page-title', 'كشف حساب المورد')

@push('styles')
<style>
.stmt-breadcrumb {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; color: #94a3b8; margin-bottom: 22px; flex-wrap: wrap;
}
.stmt-breadcrumb a { color: #94a3b8; text-decoration: none; transition: color 0.15s; }
.stmt-breadcrumb a:hover { color: #6366f1; }
.stmt-breadcrumb .current { color: #334155; font-weight: 600; }

/* HEADER */
.stmt-header {
    background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 50%, #7c3aed 100%);
    border-radius: 20px; padding: 28px 32px; color: white;
    margin-bottom: 24px; position: relative; overflow: hidden;
    box-shadow: 0 8px 32px rgba(79,70,229,0.4);
}
.stmt-header::before {
    content: ''; position: absolute; top: -60px; right: -60px;
    width: 220px; height: 220px; border-radius: 50%;
    background: rgba(255,255,255,0.05); pointer-events: none;
}
.stmt-header-inner {
    display: flex; align-items: center; justify-content: space-between;
    gap: 20px; position: relative; z-index: 1;
}
.stmt-header-left { display: flex; align-items: center; gap: 18px; }
.stmt-header-icon {
    width: 64px; height: 64px;
    background: rgba(255,255,255,0.12); border: 2px solid rgba(255,255,255,0.2);
    border-radius: 18px; display: flex; align-items: center; justify-content: center;
    font-size: 26px; color: white; flex-shrink: 0;
}
.stmt-header-title { font-size: 24px; font-weight: 800; margin: 0 0 6px; }
.stmt-header-sub { font-size: 13px; color: rgba(255,255,255,0.7); margin: 0; }
.stmt-header-actions { display: flex; gap: 10px; flex-shrink: 0; }
.stmt-btn-header {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 18px; border-radius: 12px;
    background: rgba(255,255,255,0.12); border: 1.5px solid rgba(255,255,255,0.22);
    color: white; font-size: 13px; font-weight: 600;
    font-family: 'Cairo', sans-serif; cursor: pointer;
    text-decoration: none; transition: background 0.2s; white-space: nowrap;
}
.stmt-btn-header:hover { background: rgba(255,255,255,0.22); color: white; }

/* STATS */
.stmt-stats {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 16px; margin-bottom: 24px;
}
@media (max-width: 700px) { .stmt-stats { grid-template-columns: 1fr; } }

.stmt-stat {
    background: white; border-radius: 18px;
    border: 1px solid #f1f5f9; box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    padding: 24px 20px; text-align: center;
    transition: box-shadow 0.2s, transform 0.2s;
}
.stmt-stat:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.08); transform: translateY(-2px); }
.stmt-stat.highlight { border: 2px solid #fecaca; background: #fff8f8; }
.stmt-stat-icon-wrap {
    width: 60px; height: 60px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 14px; font-size: 22px;
}
.stmt-stat-icon-wrap.purple { background: #f3e8ff; color: #9333ea; }
.stmt-stat-icon-wrap.green  { background: #dcfce7; color: #16a34a; }
.stmt-stat-icon-wrap.red    { background: #fee2e2; color: #dc2626; }
.stmt-stat-label { font-size: 13px; color: #64748b; margin: 0 0 8px; }
.stmt-stat-value { font-size: 28px; font-weight: 800; margin: 0 0 4px; line-height: 1.1; }
.stmt-stat-value.purple { color: #9333ea; }
.stmt-stat-value.green  { color: #16a34a; }
.stmt-stat-value.red    { color: #dc2626; }
.stmt-stat-curr { font-size: 11px; color: #94a3b8; }

/* FILTER */
.filter-card {
    background: white; border-radius: 16px; border: 1px solid #f1f5f9;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05); padding: 20px 24px; margin-bottom: 20px;
}
.filter-grid {
    display: grid; grid-template-columns: 1fr 1fr 1fr auto;
    gap: 14px; align-items: end;
}
@media (max-width: 800px) { .filter-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 500px) { .filter-grid { grid-template-columns: 1fr; } }

.filter-label {
    display: block; font-size: 12px; font-weight: 600; color: #475569;
    margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;
}
.filter-input {
    width: 100%; padding: 9px 13px; border: 1.5px solid #e2e8f0;
    border-radius: 10px; font-size: 13px; font-family: 'Cairo', sans-serif;
    color: #1e293b; background: #f8fafc; outline: none;
    transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box;
}
.filter-input:focus { border-color: #6366f1; background: white; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
.btn-filter {
    display: inline-flex; align-items: center; justify-content: center;
    gap: 7px; padding: 10px 22px; border-radius: 10px;
    background: #6366f1; color: white; font-size: 13px; font-weight: 600;
    font-family: 'Cairo', sans-serif; border: none; cursor: pointer;
    transition: background 0.2s; width: 100%;
}
.btn-filter:hover { background: #4f46e5; }

/* TABLE */
.table-card {
    background: white; border-radius: 18px; border: 1px solid #f1f5f9;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 24px;
}
.table-card-header {
    padding: 18px 24px; border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; gap: 10px; background: #fafafa;
}
.table-card-title {
    font-size: 15px; font-weight: 700; color: #1e293b;
    display: flex; align-items: center; gap: 8px; flex: 1;
}
.table-overflow { overflow-x: auto; }
.stmt-table { width: 100%; border-collapse: collapse; }
.stmt-table thead tr { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
.stmt-table th {
    padding: 13px 18px; font-size: 11px; font-weight: 700;
    color: #64748b; text-transform: uppercase; letter-spacing: 0.6px; white-space: nowrap;
}
.stmt-table th.tc { text-align: center; }
.stmt-table th.tr { text-align: right; }
.stmt-table tbody tr { border-bottom: 1px solid #f8fafc; transition: background 0.12s; }
.stmt-table tbody tr:last-child { border-bottom: none; }
.stmt-table tbody tr:hover { background: #f8fafc; }
.stmt-table td { padding: 14px 18px; font-size: 13px; color: #334155; vertical-align: middle; }
.stmt-table td.tc { text-align: center; }

.date-cell { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #475569; white-space: nowrap; }
.date-cell i { color: #94a3b8; font-size: 11px; }
.ref-badge {
    display: inline-flex; align-items: center; padding: 4px 10px;
    background: #f1f5f9; border-radius: 8px; font-size: 12px;
    font-weight: 600; color: #475569; font-family: monospace;
}
.type-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 20px; font-size: 12px;
    font-weight: 600; white-space: nowrap;
}
.type-badge.purchase { background: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
.type-badge.payment  { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.type-badge.return   { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }

.amt-debit  { font-size: 14px; font-weight: 700; color: #dc2626; }
.amt-credit { font-size: 14px; font-weight: 700; color: #16a34a; }
.amt-dash   { color: #cbd5e1; font-size: 18px; }
.bal-pos    { font-size: 14px; font-weight: 700; color: #dc2626; }
.bal-neg    { font-size: 14px; font-weight: 700; color: #16a34a; }
.bal-zero   { font-size: 14px; font-weight: 700; color: #64748b; }

.stmt-table tfoot tr { background: #f8fafc; border-top: 2px solid #e2e8f0; }
.stmt-table tfoot td { padding: 14px 18px; font-weight: 700; font-size: 14px; }

.stmt-empty { padding: 60px 24px; text-align: center; color: #94a3b8; }
.stmt-empty .ei {
    width: 72px; height: 72px; background: #f1f5f9; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px; font-size: 28px; color: #cbd5e1;
}
.stmt-empty p { font-size: 14px; margin: 0; }

/* QUICK ACTIONS */
.quick-actions {
    background: linear-gradient(135deg, #f0f9ff 0%, #f5f3ff 100%);
    border: 1px solid #e0e7ff; border-radius: 18px;
    padding: 22px 28px; display: flex; align-items: center;
    justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 8px;
}
.quick-actions-text h4 {
    font-size: 15px; font-weight: 700; color: #1e293b;
    margin: 0 0 4px; display: flex; align-items: center; gap: 8px;
}
.quick-actions-text p { font-size: 13px; color: #64748b; margin: 0; }
.quick-actions-btns { display: flex; gap: 10px; flex-wrap: wrap; }
.btn-green {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 11px 22px; border-radius: 12px; background: #16a34a;
    color: white; font-size: 14px; font-weight: 600;
    font-family: 'Cairo', sans-serif; border: none; cursor: pointer;
    text-decoration: none; transition: background 0.2s;
    box-shadow: 0 2px 8px rgba(22,163,74,0.25);
}
.btn-green:hover { background: #15803d; color: white; }
.btn-outline {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 11px 20px; border-radius: 12px; background: white;
    color: #475569; font-size: 14px; font-weight: 600;
    font-family: 'Cairo', sans-serif; border: 1.5px solid #e2e8f0;
    cursor: pointer; text-decoration: none; transition: background 0.2s;
}
.btn-outline:hover { background: #f8fafc; }

/* MODAL */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(15,23,42,0.5); z-index: 100;
    backdrop-filter: blur(4px); align-items: center;
    justify-content: center; padding: 20px;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: white; border-radius: 22px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.2);
    width: 100%; max-width: 460px; overflow: hidden;
    animation: mIn 0.25s cubic-bezier(0.34,1.56,0.64,1);
}
@keyframes mIn {
    from { opacity: 0; transform: scale(0.92) translateY(16px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-header {
    padding: 20px 24px; border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; justify-content: space-between;
}
.modal-title { font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 9px; }
.modal-close {
    width: 32px; height: 32px; border-radius: 9px; background: #f1f5f9;
    border: none; cursor: pointer; display: flex; align-items: center;
    justify-content: center; color: #64748b; font-size: 13px; transition: background 0.15s;
}
.modal-close:hover { background: #e2e8f0; }
.modal-body { padding: 22px 24px; }
.modal-footer {
    padding: 16px 24px; border-top: 1px solid #f1f5f9;
    background: #fafafa; display: flex; gap: 10px;
}
.modal-field { margin-bottom: 15px; }
.modal-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.modal-input {
    width: 100%; padding: 10px 13px; border: 1.5px solid #e2e8f0;
    border-radius: 10px; font-size: 14px; font-family: 'Cairo', sans-serif;
    color: #1e293b; background: #f8fafc; outline: none;
    transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box;
}
.modal-input:focus { border-color: #6366f1; background: white; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }
.modal-balance-info {
    background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 11px;
    padding: 12px 14px; font-size: 13px; color: #4338ca; margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px;
}
.btn-modal-save {
    flex: 1; padding: 11px; border-radius: 11px; background: #16a34a;
    color: white; border: none; font-size: 14px; font-weight: 600;
    font-family: 'Cairo', sans-serif; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    transition: background 0.2s;
}
.btn-modal-save:hover { background: #15803d; }
.btn-modal-cancel {
    flex: 1; padding: 11px; border-radius: 11px; background: #f1f5f9;
    color: #475569; border: none; font-size: 14px; font-weight: 600;
    font-family: 'Cairo', sans-serif; cursor: pointer; transition: background 0.2s;
}
.btn-modal-cancel:hover { background: #e2e8f0; }

@media (max-width: 768px) {
    .stmt-header-inner { flex-direction: column; align-items: flex-start; }
    .stmt-header-actions { width: 100%; }
    .stmt-btn-header { flex: 1; justify-content: center; }
    .quick-actions { flex-direction: column; align-items: flex-start; }
    .quick-actions-btns { width: 100%; }
    .btn-green, .btn-outline { flex: 1; justify-content: center; }
}
</style>
@endpush

@section('content')

<nav class="stmt-breadcrumb">
    <a href="{{ route('suppliers.index') }}">الموردين</a>
    <i class="fas fa-chevron-left"></i>
    <a href="{{ route('suppliers.show', $supplier->id) }}">{{ $supplier->name }}</a>
    <i class="fas fa-chevron-left"></i>
    <span class="current">كشف الحساب</span>
</nav>

{{-- HEADER --}}
<div class="stmt-header">
    <div class="stmt-header-inner">
        <div class="stmt-header-left">
            <div class="stmt-header-icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h2 class="stmt-header-title">كشف حساب المورد</h2>
                <p class="stmt-header-sub">{{ $supplier->name }} &nbsp;•&nbsp; #{{ $supplier->id }}</p>
            </div>
        </div>
        <div class="stmt-header-actions">
            <button onclick="window.print()" class="stmt-btn-header">
                <i class="fas fa-print"></i> طباعة
            </button>
            <button class="stmt-btn-header">
                <i class="fas fa-file-excel"></i> تصدير
            </button>
        </div>
    </div>
</div>

{{-- STATS --}}
<div class="stmt-stats">
    <div class="stmt-stat">
        <div class="stmt-stat-icon-wrap purple"><i class="fas fa-shopping-cart"></i></div>
        <p class="stmt-stat-label">إجمالي المشتريات (مدين)</p>
        <div class="stmt-stat-value purple">{{ number_format($summary['total_purchases'] ?? 0, 2) }}</div>
        <div class="stmt-stat-curr">جنيه مصري</div>
    </div>
    <div class="stmt-stat">
        <div class="stmt-stat-icon-wrap green"><i class="fas fa-money-bill-wave"></i></div>
        <p class="stmt-stat-label">إجمالي المدفوعات (دائن)</p>
        <div class="stmt-stat-value green">{{ number_format($summary['total_paid'] ?? 0, 2) }}</div>
        <div class="stmt-stat-curr">جنيه مصري</div>
    </div>
    <div class="stmt-stat highlight">
        <div class="stmt-stat-icon-wrap red"><i class="fas fa-wallet"></i></div>
        <p class="stmt-stat-label">الرصيد المستحق</p>
        <div class="stmt-stat-value red">{{ number_format($summary['balance'] ?? 0, 2) }}</div>
        <div class="stmt-stat-curr">جنيه مصري</div>
    </div>
</div>

{{-- FILTERS --}}
<div class="filter-card">
    <form method="GET" action="{{ route('suppliers.statement', $supplier->id) }}">
        <div class="filter-grid">
            <div>
                <label class="filter-label">من تاريخ</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="filter-input">
            </div>
            <div>
                <label class="filter-label">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="filter-input">
            </div>
            <div>
                <label class="filter-label">نوع الحركة</label>
                <select name="type" class="filter-input">
                    <option value="">الكل</option>
                    <option value="invoice" {{ request('type') == 'invoice' ? 'selected' : '' }}>فواتير فقط</option>
                    <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>سداد فقط</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i> تطبيق
                </button>
            </div>
        </div>
    </form>
</div>

{{-- TABLE --}}
<div class="table-card">
    <div class="table-card-header">
        <div class="table-card-title">
            <i class="fas fa-list" style="color:#6366f1;"></i>
            حركات الحساب
        </div>
        @if($statement->count() > 0)
        <span style="font-size:12px;color:#94a3b8;font-weight:500;">{{ $statement->count() }} حركة</span>
        @endif
    </div>
    <div class="table-overflow">
        <table class="stmt-table">
            <thead>
                <tr>
                    <th class="tr">التاريخ</th>
                    <th class="tr">المستند</th>
                    <th class="tr">البيان</th>
                    <th class="tc">مدين</th>
                    <th class="tc">دائن</th>
                    <th class="tc">الرصيد</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $runningBalance = 0;
                    $totalDebit     = 0;
                    $totalCredit    = 0;
                @endphp

                @forelse($statement as $tx)
                @php
                    $runningBalance += ($tx['debit'] ?? 0) - ($tx['credit'] ?? 0);
                    $totalDebit     += $tx['debit']  ?? 0;
                    $totalCredit    += $tx['credit'] ?? 0;
                    $typeStr = $tx['type_ar'] ?? '';
                    $tc = str_contains($typeStr,'سداد') || str_contains($typeStr,'دفع') ? 'payment'
                        : (str_contains($typeStr,'مرتجع') || str_contains($typeStr,'إرجاع') ? 'return' : 'purchase');
                    $ti = $tc === 'payment' ? 'fa-money-bill-wave' : ($tc === 'return' ? 'fa-undo' : 'fa-shopping-cart');
                @endphp
                <tr>
                    <td>
                        <div class="date-cell">
                            <i class="fas fa-calendar-alt"></i>
                            {{ $tx['date'] }}
                        </div>
                    </td>
                    <td><span class="ref-badge">#{{ $tx['reference'] ?? 'N/A' }}</span></td>
                    <td>
                        <span class="type-badge {{ $tc }}">
                            <i class="fas {{ $ti }}"></i> {{ $typeStr }}
                        </span>
                    </td>
                    <td class="tc">
                        @if(($tx['debit'] ?? 0) > 0)
                            <span class="amt-debit">{{ number_format($tx['debit'], 2) }}</span>
                        @else
                            <span class="amt-dash">—</span>
                        @endif
                    </td>
                    <td class="tc">
                        @if(($tx['credit'] ?? 0) > 0)
                            <span class="amt-credit">{{ number_format($tx['credit'], 2) }}</span>
                        @else
                            <span class="amt-dash">—</span>
                        @endif
                    </td>
                    <td class="tc">
                        @if($runningBalance > 0)
                            <span class="bal-pos">{{ number_format($runningBalance, 2) }}</span>
                        @elseif($runningBalance < 0)
                            <span class="bal-neg">{{ number_format(abs($runningBalance), 2) }}</span>
                        @else
                            <span class="bal-zero">0.00</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="stmt-empty">
                            <div class="ei"><i class="fas fa-inbox"></i></div>
                            <p>لا توجد حركات في الفترة المحددة</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>

            @if($statement->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="3" style="color:#1e293b;">الإجمالي</td>
                    <td class="tc" style="color:#dc2626;">{{ number_format($totalDebit, 2) }}</td>
                    <td class="tc" style="color:#16a34a;">{{ number_format($totalCredit, 2) }}</td>
                    <td class="tc">
                        @if($runningBalance > 0)
                            <span class="bal-pos">{{ number_format($runningBalance, 2) }}</span>
                        @elseif($runningBalance < 0)
                            <span class="bal-neg">{{ number_format(abs($runningBalance), 2) }}</span>
                        @else
                            <span class="bal-zero">0.00</span>
                        @endif
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- QUICK ACTIONS --}}
<div class="quick-actions">
    <div class="quick-actions-text">
        <h4><i class="fas fa-bolt" style="color:#f59e0b;"></i> إجراءات سريعة</h4>
        <p>قم بإجراء عملية سريعة للمورد</p>
    </div>
    <div class="quick-actions-btns">
        <button class="btn-green" onclick="openModal()">
            <i class="fas fa-plus"></i> إضافة سداد
        </button>
        <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn-outline">
            <i class="fas fa-eye"></i> التفاصيل
        </a>
    </div>
</div>

{{-- PAYMENT MODAL --}}
<div class="modal-overlay" id="payModal" onclick="if(event.target===this)closeModal()">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fas fa-plus-circle" style="color:#16a34a;"></i>
                إضافة سداد جديد
            </div>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>

        {{-- ⚠️ غيّر الـ action إلى route السداد الصحيح عندك --}}
        <form action="{{ route('accounting.payments') }}" method="POST">
            @csrf
            <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
            <div class="modal-body">
                <div class="modal-balance-info">
                    <i class="fas fa-info-circle"></i>
                    <span><strong>الرصيد الحالي:</strong> {{ number_format($summary['balance'] ?? 0, 2) }} ج.م</span>
                </div>
                <div class="modal-field">
                    <label class="modal-label">تاريخ السداد <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="modal-input" required>
                </div>
                <div class="modal-field">
                    <label class="modal-label">المبلغ <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" class="modal-input" required>
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
                <button type="button" class="btn-modal-cancel" onclick="closeModal()">إلغاء</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fas fa-save"></i> حفظ السداد
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openModal()  { document.getElementById('payModal').classList.add('open'); document.body.style.overflow='hidden'; }
    function closeModal() { document.getElementById('payModal').classList.remove('open'); document.body.style.overflow=''; }
    document.addEventListener('keydown', e => { if(e.key==='Escape') closeModal(); });
</script>
@endpush

@endsection