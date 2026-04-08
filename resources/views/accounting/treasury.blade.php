@extends('layouts.app')

@section('title', 'إدارة الخزينة')
@section('page-title', 'مركز التحكم المالي')

@push('styles')
<style>
    :root {
        --tf-bg:          #f4f7fe;
        --tf-surface:     #ffffff;
        --tf-surface2:    #f8faff;
        --tf-border:      #e4eaf7;
        --tf-border-soft: #edf0f9;

        --tf-indigo:      #4338ca;
        --tf-indigo-light:#6366f1;
        --tf-indigo-soft: #e0e7ff;

        --tf-blue:        #3a8ef0;
        --tf-blue-soft:   #e8f2ff;
        --tf-green:       #059669;
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
            radial-gradient(ellipse 90% 70% at 5% -15%,  rgba(99,102,241,0.2) 0%, transparent 50%),
            radial-gradient(ellipse 70% 60% at 95% 115%, rgba(139,92,246,0.15) 0%, transparent 50%);
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
    .tf-section:nth-child(4) { animation-delay: 0.28s; }

    .tf-card {
        background: var(--tf-surface); border-radius: 20px;
        border: 1px solid var(--tf-border);
        overflow: hidden; box-shadow: var(--tf-shadow-card);
        margin-bottom: 20px; position: relative;
        transition: all .35s cubic-bezier(.22,1,.36,1);
    }
    .tf-card:hover { transform: translateY(-3px); box-shadow: var(--tf-shadow-lg); }

    .tf-stat-card {
        background: var(--tf-surface); border-radius: 20px;
        padding: 1.5rem; position: relative; overflow: hidden;
        box-shadow: var(--tf-shadow-card);
        transition: all .35s cubic-bezier(.22,1,.36,1);
    }
    .tf-stat-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 4px; background: var(--gradient);
        transform: scaleX(0); transition: transform .3s ease;
        transform-origin: left;
    }
    .tf-stat-card:hover { transform: translateY(-4px); box-shadow: var(--tf-shadow-lg); }
    .tf-stat-card:hover::before { transform: scaleX(1); }
    .tf-stat-card.cash { --gradient: linear-gradient(135deg, #059669, #34d399); }
    .tf-stat-card.bank { --gradient: linear-gradient(135deg, var(--tf-blue), #6bb9f8); }
    .tf-stat-card.total { --gradient: linear-gradient(135deg, var(--tf-indigo), var(--tf-violet)); }
    .tf-stat-card.today { --gradient: linear-gradient(135deg, var(--tf-amber), #f5c842); }

    .tf-stat-icon {
        width: 52px; height: 52px; border-radius: 14px;
        background: var(--gradient);
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 1rem; box-shadow: var(--tf-shadow-sm);
    }
    .tf-stat-icon svg { width: 26px; height: 26px; color: white; }

    .tf-stat-label {
        font-size: 13px; color: var(--tf-text-m); margin-bottom: 4px;
        font-weight: 500;
    }

    .tf-stat-value {
        font-size: 1.75rem; font-weight: 800;
        color: var(--tf-text-h); direction: ltr; text-align: right;
    }

    .tf-input, .tf-select {
        width: 100%; padding: 12px 16px;
        background: var(--tf-surface2);
        border: 1px solid var(--tf-border);
        border-radius: 12px; font-size: 14px;
        color: var(--tf-text-h); transition: all .25s;
    }
    .tf-input:focus, .tf-select:focus {
        outline: none; border-color: var(--tf-indigo);
        box-shadow: 0 0 0 3px rgba(79,99,210,0.12);
    }
    .tf-input::placeholder { color: var(--tf-text-d); }

    .tf-label {
        display: block; font-size: 13px; font-weight: 600;
        color: var(--tf-text-b); margin-bottom: 8px;
    }

    .tf-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 12px 20px; border-radius: 12px; font-size: 14px; font-weight: 600;
        cursor: pointer; transition: all .3s cubic-bezier(.22,1,.36,1);
        border: none;
    }
    .tf-btn-primary {
        background: linear-gradient(135deg, var(--tf-indigo), var(--tf-violet));
        color: white;
    }
    .tf-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79,99,210,0.35);
    }

    .tf-table { width: 100%; border-collapse: collapse; }
    .tf-table thead {
        background: linear-gradient(to right, var(--tf-surface2), var(--tf-surface));
    }
    .tf-table th {
        padding: 16px 20px; text-align: right;
        font-size: 12px; font-weight: 700; text-transform: uppercase;
        color: var(--tf-text-m); letter-spacing: 0.5px;
    }
    .tf-table td {
        padding: 16px 20px; border-top: 1px solid var(--tf-border-soft);
        color: var(--tf-text-b); font-size: 14px;
    }
    .tf-table tbody tr {
        transition: all .25s;
    }
    .tf-table tbody tr:hover {
        background: var(--tf-surface2);
    }

    .tf-badge {
        display: inline-block; padding: 6px 14px; border-radius: 20px;
        font-size: 12px; font-weight: 600; text-transform: uppercase;
    }
    .tf-badge-income {
        background: var(--tf-green-soft); color: var(--tf-green);
    }
    .tf-badge-expense {
        background: var(--tf-red-soft); color: var(--tf-red);
    }

    .tf-amount-in {
        color: var(--tf-green); font-weight: 700;
    }
    .tf-amount-out {
        color: var(--tf-red); font-weight: 700;
    }

    .tf-paginate {
        display: flex; align-items: center; justify-content: center;
        padding: 16px 24px; border-top: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2);
    }

    .tf-card-head {
        padding: 20px 24px;
        background: linear-gradient(135deg, var(--tf-indigo), var(--tf-violet));
        color: white;
    }
    .tf-card-head h3 {
        margin: 0; font-size: 1.25rem; font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="tf-page">
    <div class="tf-section" style="margin-bottom: 1.5rem;">
        <h2 class="text-3xl font-bold" style="color: var(--tf-text-h);">مركز التحكم المالي</h2>
        <p class="mt-1" style="color: var(--tf-text-m);">إدارة شاملة لجميع العمليات المالية والخزينة</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="tf-stat-card cash tf-section">
            <div class="tf-stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="tf-stat-label">رصيد الخزينة النقدي</div>
            <div class="tf-stat-value">{{ number_format($cashBalance, 2) }} ج.م</div>
        </div>
        
        <div class="tf-stat-card bank tf-section" style="animation-delay: 0.06s;">
            <div class="tf-stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <div class="tf-stat-label">الرصيد البنكي</div>
            <div class="tf-stat-value">{{ number_format($bankBalance, 2) }} ج.م</div>
        </div>
        
        <div class="tf-stat-card total tf-section" style="animation-delay: 0.12s;">
            <div class="tf-stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="tf-stat-label">إجمالي السيولة المتاحة</div>
            <div class="tf-stat-value">{{ number_format($totalLiquidity, 2) }} ج.م</div>
        </div>
        
        <div class="tf-stat-card today tf-section" style="animation-delay: 0.18s;">
            <div class="tf-stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div class="tf-stat-label">عدد حركات اليوم</div>
            <div class="tf-stat-value">{{ $todayTransactions->count() }}</div>
        </div>
    </div>

    <div class="tf-card tf-section" style="animation-delay: 0.20s;">
        <div class="tf-card-head">
            <h3>تصفية وبحث</h3>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('accounting.treasury') }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="filter-group">
                        <label class="tf-label">من تاريخ</label>
                        <input type="date" name="from_date" class="tf-input" value="{{ request('from_date') }}">
                    </div>
                    <div class="filter-group">
                        <label class="tf-label">إلى تاريخ</label>
                        <input type="date" name="to_date" class="tf-input" value="{{ request('to_date') }}">
                    </div>
                    <div class="filter-group">
                        <label class="tf-label">نوع الحركة</label>
                        <select name="type" class="tf-select">
                            <option value="">الكل</option>
                            <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>إيداع</option>
                            <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>سحب</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="tf-btn tf-btn-primary" style="width: 100%;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            بحث
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="tf-card tf-section" style="animation-delay: 0.28s;">
        <div class="tf-card-head">
            <h3>سجل الحركات المالية</h3>
        </div>
        
        <table class="tf-table">
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
                        <div style="font-weight: 600; color: var(--tf-text-h);">{{ $tx->transaction_date->format('d/m/Y') }}</div>
                        <div style="font-size: 0.85rem; color: var(--tf-text-m);">{{ $tx->created_at->format('h:i A') }}</div>
                    </td>
                    <td>
                        @if($tx->transaction_type == 'deposit')
                            <span class="tf-badge tf-badge-income">إيداع</span>
                        @else
                            <span class="tf-badge tf-badge-expense">سحب</span>
                        @endif
                    </td>
                    <td>{{ $tx->description ?? 'بدون وصف' }}</td>
                    <td>
                        @if($tx->transaction_type == 'deposit')
                            <span class="tf-amount-in">+ {{ number_format($tx->amount, 2) }} ج.م</span>
                        @else
                            <span style="color: var(--tf-text-m);">-</span>
                        @endif
                    </td>
                    <td>
                        @if($tx->transaction_type == 'withdrawal')
                            <span class="tf-amount-out">- {{ number_format($tx->amount, 2) }} ج.م</span>
                        @else
                            <span style="color: var(--tf-text-m);">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-12" style="color: var(--tf-text-m);">
                        <div style="font-size: 1.1rem; font-weight: 600;">لا توجد حركات مالية</div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        
        @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="tf-paginate">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection