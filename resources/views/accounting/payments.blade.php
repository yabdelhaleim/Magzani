@extends('layouts.app')

@section('title', 'المقبوضات والمدفوعات')
@section('page-title', 'المقبوضات والمدفوعات')

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
        --tf-green-soft:  #d1fae5;
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

    .tf-card-head {
        padding: 20px 24px;
        background: linear-gradient(135deg, var(--tf-indigo), var(--tf-violet));
        color: white;
    }
    .tf-card-head h3 {
        margin: 0; font-size: 1.25rem; font-weight: 700;
    }

    .tf-stat-card {
        background: var(--tf-surface); border-radius: 20px;
        padding: 1.5rem; position: relative; overflow: hidden;
        box-shadow: var(--tf-shadow-card);
        transition: all .35s cubic-bezier(.22,1,.36,1);
    }
    .tf-stat-card:hover { transform: translateY(-4px); box-shadow: var(--tf-shadow-lg); }

    .tf-stat-icon {
        width: 56px; height: 56px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 1rem; box-shadow: var(--tf-shadow-sm);
    }

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
    .tf-label i { margin-left: 6px; }

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
    .tf-btn-green {
        background: linear-gradient(135deg, var(--tf-green), #34d399);
        color: white;
    }
    .tf-btn-green:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(15,170,126,0.35);
    }
    .tf-btn-red {
        background: linear-gradient(135deg, var(--tf-red), #f77066);
        color: white;
    }
    .tf-btn-red:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(232,75,90,0.35);
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
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600;
    }
    .tf-badge-green {
        background: var(--tf-green-soft); color: var(--tf-green);
    }
    .tf-badge-red {
        background: var(--tf-red-soft); color: var(--tf-red);
    }
    .tf-badge-indigo {
        background: var(--tf-indigo-soft); color: var(--tf-indigo);
    }

    .tf-amount-in { color: var(--tf-green); font-weight: 700; font-size: 1.1rem; }
    .tf-amount-out { color: var(--tf-red); font-weight: 700; font-size: 1.1rem; }

    .tf-paginate {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 24px; border-top: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2);
    }
    .tf-paginate-info { font-size: 14px; color: var(--tf-text-m); }
    .tf-paginate-info strong { color: var(--tf-text-h); }

    .tf-modal {
        position: fixed; inset: 0; z-index: 50;
        background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
        display: none; align-items: center; justify-content: center;
        padding: 1rem;
    }
    .tf-modal.active { display: flex; }

    .tf-modal-content {
        background: var(--tf-surface); border-radius: 20px;
        max-width: 28rem; width: 100%; max-height: 90vh; overflow-y: auto;
        box-shadow: var(--tf-shadow-lg);
    }

    .tf-modal-head {
        padding: 1.25rem 1.5rem;
        border-radius: 20px 20px 0 0;
        display: flex; align-items: center; justify-content: space-between;
    }
    .tf-modal-head.green {
        background: linear-gradient(135deg, var(--tf-green), #34d399);
    }
    .tf-modal-head.red {
        background: linear-gradient(135deg, var(--tf-red), #f77066);
    }
    .tf-modal-head h3 {
        margin: 0; font-size: 1.25rem; font-weight: 700; color: white;
    }
    .tf-modal-close {
        width: 32px; height: 32px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        color: white; cursor: pointer; transition: all .2s;
    }
    .tf-modal-close:hover { background: rgba(255,255,255,0.2); }

    .tf-modal-body { padding: 1.5rem; }

    .tf-form-group { margin-bottom: 1rem; }

    .tf-action-btn {
        padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 600;
        cursor: pointer; transition: all .3s; border: none;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    }

    .tf-action-btn-edit {
        width: 36px; height: 36px;
        background: var(--tf-blue-soft); color: var(--tf-blue);
        border-radius: 10px;
    }
    .tf-action-btn-edit:hover { background: var(--tf-blue); color: white; }

    .tf-action-btn-delete {
        width: 36px; height: 36px;
        background: var(--tf-red-soft); color: var(--tf-red);
        border-radius: 10px;
    }
    .tf-action-btn-delete:hover { background: var(--tf-red); color: white; }

    .tf-header-gradient {
        background: linear-gradient(135deg, var(--tf-indigo), var(--tf-violet), #c084fc);
        border-radius: 20px; padding: 2rem; color: white;
        position: relative; overflow: hidden;
    }
    .tf-header-gradient::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.15) 0%, transparent 40%),
                    radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 40%);
    }
    .tf-header-content { position: relative; z-index: 1; }

    .tf-empty-state {
        padding: 4rem 2rem; text-align: center;
    }
    .tf-empty-icon {
        width: 96px; height: 96px; border-radius: 50%;
        background: var(--tf-surface2);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem;
    }
    .tf-empty-icon i { font-size: 2.5rem; color: var(--tf-text-d); }
</style>
@endpush

@section('content')
<div class="tf-page">
    <!-- Header Section -->
    <div class="tf-header-gradient tf-section mb-6">
        <div class="tf-header-content">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-4xl font-bold mb-2">المقبوضات والمدفوعات</h2>
                    <p class="text-white/80 text-lg">إدارة شاملة لجميع الحركات المالية</p>
                </div>
                <div class="hidden md:flex items-center gap-3">
                    <button onclick="openModal('depositModal')" 
                            class="tf-btn tf-btn-green">
                        <i class="fas fa-plus-circle"></i>
                        إيداع جديد
                    </button>
                    <button onclick="openModal('withdrawalModal')" 
                            class="tf-btn tf-btn-red">
                        <i class="fas fa-minus-circle"></i>
                        سحب جديد
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="tf-stat-card tf-section">
            <div class="tf-stat-icon" style="background: linear-gradient(135deg, #059669, #34d399);">
                <i class="fas fa-wallet text-2xl text-white"></i>
            </div>
            <div class="tf-stat-label">رصيد الخزينة</div>
            <div class="tf-stat-value">{{ number_format($cashBalance, 2) }} <span style="font-size: 1rem; color: var(--tf-text-m);">ج.م</span></div>
        </div>

        <div class="tf-stat-card tf-section" style="animation-delay: 0.06s;">
            <div class="tf-stat-icon" style="background: linear-gradient(135deg, var(--tf-blue), #6bb9f8);">
                <i class="fas fa-university text-2xl text-white"></i>
            </div>
            <div class="tf-stat-label">الرصيد البنكي</div>
            <div class="tf-stat-value">{{ number_format($bankBalance, 2) }} <span style="font-size: 1rem; color: var(--tf-text-m);">ج.م</span></div>
        </div>

        <div class="tf-stat-card tf-section" style="animation-delay: 0.12s;">
            <div class="tf-stat-icon" style="background: linear-gradient(135deg, var(--tf-violet), #9575fa);">
                <i class="fas fa-coins text-2xl text-white"></i>
            </div>
            <div class="tf-stat-label">إجمالي السيولة</div>
            <div class="tf-stat-value">{{ number_format($totalLiquidity, 2) }} <span style="font-size: 1rem; color: var(--tf-text-m);">ج.م</span></div>
        </div>

        <div class="tf-stat-card tf-section" style="animation-delay: 0.18s;">
            <div class="tf-stat-icon" style="background: linear-gradient(135deg, var(--tf-amber), #f5c842);">
                <i class="fas fa-chart-line text-2xl text-white"></i>
            </div>
            <div class="tf-stat-label">حركات اليوم</div>
            <div class="tf-stat-value">{{ $todayTransactions->count() }} <span style="font-size: 1rem; color: var(--tf-text-m);">حركة</span></div>
        </div>
    </div>

    <!-- Mobile Action Buttons -->
    <div class="md:hidden grid grid-cols-2 gap-3 mb-6">
        <button onclick="openModal('depositModal')" class="tf-btn tf-btn-green">
            <i class="fas fa-plus-circle"></i>
            إيداع جديد
        </button>
        <button onclick="openModal('withdrawalModal')" class="tf-btn tf-btn-red">
            <i class="fas fa-minus-circle"></i>
            سحب جديد
        </button>
    </div>

    <!-- Filters Section -->
    <div class="tf-card tf-section" style="animation-delay: 0.20s;">
        <div class="p-6">
            <h3 class="text-xl font-bold mb-4" style="color: var(--tf-text-h);">
                <i class="fas fa-filter" style="color: var(--tf-indigo); margin-left: 8px;"></i>
                تصفية وبحث
            </h3>
            <form method="GET" action="{{ route('accounting.payments') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-3">
                    <label class="tf-label"><i class="fas fa-list"></i>نوع الحركة</label>
                    <select name="type" class="tf-select">
                        <option value="">الكل</option>
                        <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>إيداع</option>
                        <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>سحب</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="tf-label"><i class="fas fa-tag"></i>التصنيف</label>
                    <select name="category" class="tf-select">
                        <option value="">الكل</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="tf-label"><i class="fas fa-calendar-alt"></i>من تاريخ</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="tf-input">
                </div>

                <div class="md:col-span-2">
                    <label class="tf-label"><i class="fas fa-calendar-check"></i>إلى تاريخ</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="tf-input">
                </div>

                <div class="md:col-span-2 flex items-end gap-2">
                    <button type="submit" class="tf-btn tf-btn-primary flex-1">
                        <i class="fas fa-search"></i>
                        بحث
                    </button>
                    <a href="{{ route('accounting.payments') }}" class="tf-btn tf-btn-secondary" style="padding: 12px;">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="tf-card tf-section" style="animation-delay: 0.28s;">
        <div class="tf-card-head">
            <h3><i class="fas fa-list-alt ml-2"></i>سجل الحركات المالية</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="tf-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag ml-1"></i>الرقم</th>
                        <th><i class="fas fa-calendar ml-1"></i>التاريخ والوقت</th>
                        <th><i class="fas fa-tag ml-1"></i>النوع</th>
                        <th><i class="fas fa-align-right ml-1"></i>الوصف</th>
                        <th><i class="fas fa-folder ml-1"></i>التصنيف</th>
                        <th class="text-center"><i class="fas fa-arrow-down ml-1" style="color: var(--tf-green);"></i>وارد</th>
                        <th class="text-center"><i class="fas fa-arrow-up ml-1" style="color: var(--tf-red);"></i>صادر</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>
                                <span class="inline-flex items-center justify-center w-10 h-10" 
                                      style="background: var(--tf-surface2); border-radius: 10px; font-weight: 600; color: var(--tf-text-b);">
                                    {{ $transaction->id }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-clock" style="color: var(--tf-text-d);"></i>
                                    <div>
                                        <p class="font-semibold" style="color: var(--tf-text-h);">{{ $transaction->transaction_date->format('d/m/Y') }}</p>
                                        <p class="text-xs" style="color: var(--tf-text-m);">{{ $transaction->created_at->format('h:i A') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($transaction->transaction_type == 'deposit')
                                    <span class="tf-badge tf-badge-green">
                                        <i class="fas fa-arrow-down"></i>
                                        إيداع
                                    </span>
                                @else
                                    <span class="tf-badge tf-badge-red">
                                        <i class="fas fa-arrow-up"></i>
                                        سحب
                                    </span>
                                @endif
                            </td>
                            <td>
                                <p class="max-w-xs truncate" style="color: var(--tf-text-b);" title="{{ $transaction->description }}">
                                    {{ $transaction->description ?? 'بدون وصف' }}
                                </p>
                            </td>
                            <td>
                                @if($transaction->category)
                                    <span class="tf-badge tf-badge-indigo">
                                        <i class="fas fa-tag"></i>
                                        {{ $transaction->category }}
                                    </span>
                                @else
                                    <span style="color: var(--tf-text-d);">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($transaction->transaction_type == 'deposit')
                                    <span class="tf-amount-in" dir="ltr">+ {{ number_format($transaction->amount, 2) }} ج.م</span>
                                @else
                                    <span style="color: var(--tf-text-d);">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($transaction->transaction_type == 'withdrawal')
                                    <span class="tf-amount-out" dir="ltr">- {{ number_format($transaction->amount, 2) }} ج.م</span>
                                @else
                                    <span style="color: var(--tf-text-d);">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editTransaction({{ $transaction->id }})" class="tf-action-btn tf-action-btn-edit" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('accounting.transactions.destroy', $transaction->id) }}" method="POST" class="inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tf-action-btn tf-action-btn-delete" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="tf-empty-state">
                                    <div class="tf-empty-icon">
                                        <i class="fas fa-inbox"></i>
                                    </div>
                                    <h3 style="color: var(--tf-text-h);">لا توجد حركات مالية</h3>
                                    <p style="color: var(--tf-text-m);">لم يتم تسجيل أي حركات مالية بعد</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="tf-paginate">
                <div class="tf-paginate-info">
                    عرض <strong>{{ $transactions->firstItem() ?? 0 }}</strong>
                    إلى <strong>{{ $transactions->lastItem() ?? 0 }}</strong>
                    من أصل <strong>{{ $transactions->total() }}</strong> حركة
                </div>
                <div>
                    {{ $transactions->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Deposit Modal -->
<div id="depositModal" class="tf-modal">
    <div class="tf-modal-content">
        <div class="tf-modal-head green">
            <h3><i class="fas fa-plus-circle ml-2"></i>إيداع جديد</h3>
            <button onclick="closeModal('depositModal')" class="tf-modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('accounting.deposits.store') }}" method="POST" class="tf-modal-body">
            @csrf
            <div class="tf-form-group">
                <label class="tf-label">المبلغ *</label>
                <input type="number" name="amount" step="0.01" required class="tf-input" placeholder="أدخل المبلغ">
            </div>
            <div class="tf-form-group">
                <label class="tf-label">الوصف</label>
                <textarea name="description" rows="3" class="tf-input" placeholder="أدخل الوصف"></textarea>
            </div>
            <div class="tf-form-group">
                <label class="tf-label">التصنيف</label>
                <input type="text" name="category" class="tf-input" placeholder="أدخل التصنيف">
            </div>
            <div class="tf-form-group">
                <label class="tf-label">التاريخ</label>
                <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="tf-input">
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="tf-btn tf-btn-green flex-1">
                    <i class="fas fa-check"></i>
                    حفظ الإيداع
                </button>
                <button type="button" onclick="closeModal('depositModal')" class="tf-btn tf-btn-secondary flex-1" style="background: var(--tf-surface2); color: var(--tf-text-b);">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Withdrawal Modal -->
<div id="withdrawalModal" class="tf-modal">
    <div class="tf-modal-content">
        <div class="tf-modal-head red">
            <h3><i class="fas fa-minus-circle ml-2"></i>سحب جديد</h3>
            <button onclick="closeModal('withdrawalModal')" class="tf-modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('accounting.withdrawals.store') }}" method="POST" class="tf-modal-body">
            @csrf
            <div class="tf-form-group">
                <label class="tf-label">المبلغ *</label>
                <input type="number" name="amount" step="0.01" required class="tf-input" placeholder="أدخل المبلغ">
            </div>
            <div class="tf-form-group">
                <label class="tf-label">الوصف</label>
                <textarea name="description" rows="3" class="tf-input" placeholder="أدخل الوصف"></textarea>
            </div>
            <div class="tf-form-group">
                <label class="tf-label">التصنيف</label>
                <input type="text" name="category" class="tf-input" placeholder="أدخل التصنيف">
            </div>
            <div class="tf-form-group">
                <label class="tf-label">التاريخ</label>
                <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="tf-input">
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="tf-btn tf-btn-red flex-1">
                    <i class="fas fa-check"></i>
                    حفظ السحب
                </button>
                <button type="button" onclick="closeModal('withdrawalModal')" class="tf-btn tf-btn-secondary flex-1" style="background: var(--tf-surface2); color: var(--tf-text-b);">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('depositModal');
        closeModal('withdrawalModal');
    }
});

function editTransaction(id) {
    alert('تعديل الحركة رقم: ' + id);
}
</script>
@endpush

@endsection