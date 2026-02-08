@extends('layouts.app')

@section('title', 'إدارة الخزينة')
@section('page-title', 'مركز التحكم المالي')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Tajawal:wght@400;500;700&display=swap');
    
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --danger-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --dark-bg: #1a1d29;
        --card-bg: #ffffff;
        --text-primary: #2d3748;
        --text-secondary: #718096;
        --border-color: #e2e8f0;
    }
    
    body {
        font-family: 'Tajawal', sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }
    
    .page-header {
        background: var(--dark-bg);
        padding: 2rem;
        border-radius: 20px;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
        animation: pulse 4s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }
    
    .page-title {
        font-family: 'Cairo', sans-serif;
        font-size: 2.5rem;
        font-weight: 800;
        color: white;
        margin: 0;
        position: relative;
        z-index: 1;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }
    
    .page-subtitle {
        color: rgba(255,255,255,0.8);
        margin-top: 0.5rem;
        position: relative;
        z-index: 1;
        font-size: 1.1rem;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: var(--card-bg);
        border-radius: 20px;
        padding: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255,255,255,0.8);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: var(--gradient);
        transform: scaleX(0);
        transition: transform 0.3s ease;
        transform-origin: left;
    }
    
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    
    .stat-card:hover::before {
        transform: scaleX(1);
    }
    
    .stat-card.cash { --gradient: var(--success-gradient); }
    .stat-card.bank { --gradient: var(--info-gradient); }
    .stat-card.total { --gradient: var(--primary-gradient); }
    .stat-card.transactions { --gradient: var(--warning-gradient); }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        background: var(--gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }
    
    .stat-icon svg {
        width: 30px;
        height: 30px;
        color: white;
    }
    
    .stat-label {
        font-size: 0.95rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .stat-value {
        font-family: 'Cairo', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        direction: ltr;
        text-align: right;
    }
    
    .filters-section {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    }
    
    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .filter-label {
        font-weight: 600;
        color: var(--text-secondary);
        font-size: 0.875rem;
    }
    
    .filter-input {
        padding: 0.75rem;
        border: 2px solid var(--border-color);
        border-radius: 10px;
        font-family: inherit;
        transition: all 0.3s ease;
    }
    
    .filter-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .action-btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    }
    
    .transactions-container {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    }
    
    .table-header {
        padding: 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .table-title {
        font-family: 'Cairo', sans-serif;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
    }
    
    .transactions-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .transactions-table thead {
        background: #f7fafc;
        border-bottom: 2px solid var(--border-color);
    }
    
    .transactions-table th {
        padding: 1rem;
        text-align: right;
        font-weight: 700;
        color: var(--text-primary);
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .transactions-table tbody tr {
        border-bottom: 1px solid var(--border-color);
        transition: all 0.2s ease;
    }
    
    .transactions-table tbody tr:hover {
        background: #f7fafc;
    }
    
    .transactions-table td {
        padding: 1rem;
        color: var(--text-primary);
        font-size: 0.95rem;
    }
    
    .badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge.income {
        background: #d1fae5;
        color: #065f46;
    }
    
    .badge.expense {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .amount-in {
        color: #10b981;
        font-weight: 700;
        font-family: 'Cairo', sans-serif;
    }
    
    .amount-out {
        color: #ef4444;
        font-weight: 700;
        font-family: 'Cairo', sans-serif;
    }
    
    .pagination-wrapper {
        padding: 1.5rem;
        display: flex;
        justify-content: center;
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .page-title {
            font-size: 2rem;
        }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <h1 class="page-title">مركز التحكم المالي</h1>
    <p class="page-subtitle">إدارة شاملة لجميع العمليات المالية والخزينة</p>
</div>

<div class="stats-grid">
    <div class="stat-card cash">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <div class="stat-label">رصيد الخزينة النقدي</div>
        <div class="stat-value">{{ number_format($cashBalance, 2) }} ج.م</div>
    </div>
    
    <div class="stat-card bank">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
        </div>
        <div class="stat-label">الرصيد البنكي</div>
        <div class="stat-value">{{ number_format($bankBalance, 2) }} ج.م</div>
    </div>
    
    <div class="stat-card total">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="stat-label">إجمالي السيولة المتاحة</div>
        <div class="stat-value">{{ number_format($totalLiquidity, 2) }} ج.م</div>
    </div>
    
    <div class="stat-card transactions">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
        </div>
        <div class="stat-label">عدد حركات اليوم</div>
        <div class="stat-value">{{ $todayTransactions->count() }}</div>
    </div>
</div>

<div class="filters-section">
    <h3 style="font-family: 'Cairo', sans-serif; font-size: 1.3rem; font-weight: 700; margin-bottom: 1rem;">تصفية وبحث</h3>
    <form method="GET" action="{{ route('accounting.treasury') }}">
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">من تاريخ</label>
                <input type="date" name="from_date" class="filter-input" value="{{ request('from_date') }}">
            </div>
            <div class="filter-group">
                <label class="filter-label">إلى تاريخ</label>
                <input type="date" name="to_date" class="filter-input" value="{{ request('to_date') }}">
            </div>
            <div class="filter-group">
                <label class="filter-label">نوع الحركة</label>
                <select name="type" class="filter-input">
                    <option value="">الكل</option>
                    <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>إيداع</option>
                    <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>سحب</option>
                </select>
            </div>
            <div class="filter-group" style="display: flex; align-items: flex-end;">
                <button type="submit" class="action-btn" style="width: 100%;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    بحث
                </button>
            </div>
        </div>
    </form>
</div>

<div class="transactions-container">
    <div class="table-header">
        <h3 class="table-title">سجل الحركات المالية</h3>
    </div>
    
    <table class="transactions-table">
        <thead>
            <tr>
                <th>التاريخ والوقت</th>
                <th>نوع الحركة</th>
                <th>الوصف</th>
                <th>وارد</th>
                <th>صادر</th>
            </tr>
        </thead>
        <tbody>
        @forelse($transactions as $tx)
            <tr>
                <td>
                    <div style="font-weight: 600;">{{ $tx->transaction_date->format('d/m/Y') }}</div>
                    <div style="font-size: 0.85rem; color: var(--text-secondary);">{{ $tx->created_at->format('h:i A') }}</div>
                </td>
                <td>
                    @if($tx->transaction_type == 'deposit')
                        <span class="badge income">إيداع</span>
                    @else
                        <span class="badge expense">سحب</span>
                    @endif
                </td>
                <td>{{ $tx->description ?? 'بدون وصف' }}</td>
                <td>
                    @if($tx->transaction_type == 'deposit')
                        <span class="amount-in">+ {{ number_format($tx->amount, 2) }} ج.م</span>
                    @else
                        <span style="color: var(--text-secondary);">-</span>
                    @endif
                </td>
                <td>
                    @if($tx->transaction_type == 'withdrawal')
                        <span class="amount-out">- {{ number_format($tx->amount, 2) }} ج.م</span>
                    @else
                        <span style="color: var(--text-secondary);">-</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                    <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">لا توجد حركات مالية</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
    
    @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="pagination-wrapper">
        {{ $transactions->links() }}
    </div>
    @endif
</div>

@endsection