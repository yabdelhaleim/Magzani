@extends('layouts.app')

@section('title','إدارة المصروفات')
@section('page-title','إدارة المصروفات')

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
        --tf-red-light:   #ef4444;
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
            radial-gradient(ellipse 90% 70% at 5% -15%,  rgba(220,38,38,0.2) 0%, transparent 50%),
            radial-gradient(ellipse 70% 60% at 95% 115%, rgba(99,102,241,0.12) 0%, transparent 50%);
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
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        color: white;
    }
    .tf-card-head h3 {
        margin: 0; font-size: 1.25rem; font-weight: 700;
    }

    .tf-input, .tf-select, .tf-textarea {
        width: 100%; padding: 12px 16px;
        background: var(--tf-surface2);
        border: 1px solid var(--tf-border);
        border-radius: 12px; font-size: 14px;
        color: var(--tf-text-h); transition: all .25s;
    }
    .tf-input:focus, .tf-select:focus, .tf-textarea:focus {
        outline: none; border-color: var(--tf-red);
        box-shadow: 0 0 0 3px rgba(232,75,90,0.12);
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
    .tf-btn-red {
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        color: white;
    }
    .tf-btn-red:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220,38,38,0.4);
    }
    .tf-btn-secondary {
        background: var(--tf-surface2); color: var(--tf-text-b);
        border: 1px solid var(--tf-border);
    }
    .tf-btn-secondary:hover { background: var(--tf-border-soft); }

    .tf-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;
    }
    .tf-badge-blue { background: var(--tf-blue-soft); color: var(--tf-blue); }

    .tf-paginate {
        display: flex; align-items: center; justify-content: center;
        padding: 16px 24px; border-top: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2);
    }

    .tf-alert {
        padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 500;
    }
    .tf-alert-success {
        background: var(--tf-green-soft); color: var(--tf-green);
        border-left: 4px solid var(--tf-green);
    }
    .tf-alert-danger {
        background: var(--tf-red-soft); color: var(--tf-red);
        border-left: 4px solid var(--tf-red);
    }

    .tf-error {
        color: var(--tf-red); font-size: 0.875rem; margin-top: 0.25rem;
    }

    .tf-hero {
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        border-radius: 20px; padding: 2rem; color: white;
        position: relative; overflow: hidden;
        margin-bottom: 1.5rem; box-shadow: var(--tf-shadow-card);
    }
    .tf-hero::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.1) 0%, transparent 40%);
    }
    .tf-hero-content { position: relative; z-index: 1; }

    .tf-hero-stats {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;
        background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);
        padding: 1.5rem; border-radius: 16px; border: 1px solid rgba(255,255,255,0.2);
    }
    .tf-hero-stat-label { color: rgba(255,255,255,0.8); font-size: 0.875rem; margin-bottom: 4px; }
    .tf-hero-stat-value { font-size: 1.75rem; font-weight: 800; }

    .tf-form-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;
    }
    .tf-form-full { grid-column: 1 / -1; }

    .tf-expense-card {
        background: var(--tf-surface2); border-radius: 16px;
        padding: 1.25rem; border-left: 4px solid var(--tf-red);
        transition: all .35s cubic-bezier(.22,1,.36,1);
        position: relative; overflow: hidden;
    }
    .tf-expense-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(135deg, rgba(232,75,90,0.03) 0%, transparent 100%);
        opacity: 0; transition: opacity .3s;
    }
    .tf-expense-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(220,38,38,0.2); }
    .tf-expense-card:hover::before { opacity: 1; }

    .tf-expense-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem; }
    .tf-expense-type { font-size: 1.1rem; font-weight: 700; color: var(--tf-text-h); }
    .tf-expense-amount { font-size: 1.5rem; font-weight: 800; color: var(--tf-red); direction: ltr; text-align: right; }

    .tf-expense-detail {
        display: flex; align-items: center; gap: 0.5rem;
        color: var(--tf-text-m); font-size: 0.9rem; margin-bottom: 0.5rem;
        position: relative; z-index: 1;
    }
    .tf-expense-detail i { color: var(--tf-red); width: 16px; }

    .tf-expense-notes {
        margin-top: 0.75rem; padding: 0.75rem;
        background: var(--tf-surface); border-radius: 10px;
        color: var(--tf-text-m); font-size: 0.85rem; line-height: 1.5;
        position: relative; z-index: 1;
    }

    .tf-expense-actions {
        display: flex; gap: 0.5rem; margin-top: 1rem; position: relative; z-index: 1;
    }
    .tf-action-btn {
        flex: 1; padding: 0.75rem; border-radius: 10px; font-weight: 600;
        font-size: 0.85rem; cursor: pointer; transition: all .3s; border: none;
        display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    .tf-action-btn-edit { background: var(--tf-blue-soft); color: var(--tf-blue); }
    .tf-action-btn-edit:hover { background: var(--tf-blue); color: white; }
    .tf-action-btn-delete { background: var(--tf-red-soft); color: var(--tf-red); }
    .tf-action-btn-delete:hover { background: #dc2626; color: white; }

    .tf-empty-state { text-align: center; padding: 4rem 2rem; }
    .tf-empty-icon {
        width: 80px; height: 80px; border-radius: 50%;
        background: var(--tf-surface2); display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem;
    }
    .tf-empty-icon i { font-size: 2rem; color: var(--tf-text-d); }

    .tf-filter-btn {
        padding: 0.5rem 1rem; background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2); color: white;
        border-radius: 8px; font-size: 0.85rem; text-decoration: none;
        transition: all .3s;
    }
    .tf-filter-btn:hover, .tf-filter-btn.active { background: rgba(255,255,255,0.25); color: white; }

    @media (max-width: 768px) {
        .tf-hero-stats { grid-template-columns: 1fr; }
        .tf-form-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="tf-page">
@php
    $expenseTypes = [
        'rent' => 'إيجار', 'salaries' => 'رواتب', 'utilities' => 'مرافق',
        'maintenance' => 'صيانة', 'supplies' => 'مستلزمات', 'marketing' => 'تسويق',
        'transportation' => 'مواصلات', 'communication' => 'اتصالات',
        'insurance' => 'تأمينات', 'taxes' => 'ضرائب', 'other' => 'أخرى',
    ];
    $paymentMethods = [
        'cash' => 'نقدي', 'bank' => 'تحويل بنكي', 'credit_card' => 'بطاقة ائتمان', 'check' => 'شيك',
    ];
@endphp

<div class="tf-hero tf-section">
    <div class="tf-hero-content">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 style="font-size: 2rem; font-weight: 800; margin: 0 0 0.5rem 0;">إدارة المصروفات</h1>
                <p style="color: rgba(255,255,255,0.9);">تتبع ومراقبة جميع المصروفات بدقة واحترافية</p>
            </div>
        </div>
        <div class="tf-hero-stats">
            <div class="tf-hero-stat">
                <div class="tf-hero-stat-label">مصروفات اليوم</div>
                <div class="tf-hero-stat-value">{{ number_format($todayExpenses ?? 0, 2) }}</div>
            </div>
            <div class="tf-hero-stat">
                <div class="tf-hero-stat-label">مصروفات الشهر</div>
                <div class="tf-hero-stat-value">{{ number_format($monthExpenses ?? 0, 2) }}</div>
            </div>
            <div class="tf-hero-stat">
                <div class="tf-hero-stat-label">إجمالي المصروفات</div>
                <div class="tf-hero-stat-value">{{ number_format($totalExpenses ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="tf-alert tf-alert-success tf-section">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="tf-alert tf-alert-danger tf-section">{{ session('error') }}</div>
@endif

<div class="tf-card tf-section" style="border-top: 4px solid var(--tf-red);">
    <div class="p-6">
        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--tf-text-h); margin-bottom: 1.25rem;">
            <i class="fas fa-plus-circle" style="color: var(--tf-red); margin-left: 8px;"></i>
            إضافة مصروف جديد
        </h2>
        <form method="POST" action="{{ route('accounting.expenses.store') }}" class="tf-form-grid">
            @csrf
            <div>
                <label class="tf-label"><i class="fas fa-tag"></i>نوع المصروف <span style="color: var(--tf-red);">*</span></label>
                <select name="type" class="tf-select" required>
                    <option value="">اختر نوع المصروف</option>
                    @foreach($expenseTypes as $key => $label)
                        <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type') <span class="tf-error">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="tf-label"><i class="fas fa-money-bill"></i>المبلغ <span style="color: var(--tf-red);">*</span></label>
                <input type="number" name="amount" class="tf-input" placeholder="0.00" step="0.01" value="{{ old('amount') }}" required>
                @error('amount') <span class="tf-error">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="tf-label"><i class="fas fa-calendar"></i>التاريخ <span style="color: var(--tf-red);">*</span></label>
                <input type="date" name="date" class="tf-input" value="{{ old('date', date('Y-m-d')) }}" required>
                @error('date') <span class="tf-error">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="tf-label"><i class="fas fa-credit-card"></i>طريقة الدفع <span style="color: var(--tf-red);">*</span></label>
                <select name="payment_method" class="tf-select" required>
                    <option value="">اختر طريقة الدفع</option>
                    @foreach($paymentMethods as $key => $label)
                        <option value="{{ $key }}" {{ old('payment_method') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('payment_method') <span class="tf-error">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="tf-label"><i class="fas fa-user"></i>المستفيد</label>
                <input type="text" name="beneficiary" class="tf-input" placeholder="اسم المستفيد" value="{{ old('beneficiary') }}">
                @error('beneficiary') <span class="tf-error">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="tf-label"><i class="fas fa-file-invoice"></i>رقم الفاتورة / الإيصال</label>
                <input type="text" name="invoice_number" class="tf-input" placeholder="رقم المستند" value="{{ old('invoice_number') }}">
                @error('invoice_number') <span class="tf-error">{{ $message }}</span> @enderror
            </div>
            <div class="tf-form-full">
                <label class="tf-label"><i class="fas fa-sticky-note"></i>ملاحظات وتفاصيل إضافية</label>
                <textarea name="notes" class="tf-textarea" placeholder="أضف أي ملاحظات أو تفاصيل إضافية هنا...">{{ old('notes') }}</textarea>
                @error('notes') <span class="tf-error">{{ $message }}</span> @enderror
            </div>
            <div class="tf-form-full">
                <button type="submit" class="tf-btn tf-btn-red">
                    <i class="fas fa-check"></i>
                    إضافة المصروف
                </button>
            </div>
        </form>
    </div>
</div>

<div class="tf-card tf-section" style="animation-delay: 0.12s;">
    <div class="tf-card-head" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>سجل المصروفات ({{ $expenses->total() }})</h3>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('accounting.expenses.index') }}" class="tf-filter-btn {{ !request('filter') ? 'active' : '' }}">الكل</a>
            <a href="{{ route('accounting.expenses.index', ['filter' => 'today']) }}" class="tf-filter-btn {{ request('filter') == 'today' ? 'active' : '' }}">اليوم</a>
            <a href="{{ route('accounting.expenses.index', ['filter' => 'month']) }}" class="tf-filter-btn {{ request('filter') == 'month' ? 'active' : '' }}">هذا الشهر</a>
        </div>
    </div>
    
    @if($expenses->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.25rem; padding: 1.5rem;">
            @foreach($expenses as $expense)
            @php
                $type = 'أخرى';
                if (!empty($expense->category) && isset($expenseTypes[$expense->category])) {
                    $type = $expenseTypes[$expense->category];
                } else {
                    foreach ($expenseTypes as $arabic) {
                        if (str_contains($expense->description ?? '', $arabic)) { $type = $arabic; break; }
                    }
                }
                $beneficiary = preg_match('/المستفيد:\s*([^|]+)/', $expense->description ?? '', $m) ? trim($m[1]) : null;
                $paymentMethod = preg_match('/طريقة الدفع:\s*([^|]+)/', $expense->description ?? '', $m) ? trim($m[1]) : null;
                $invoiceNumber = $expense->reference ?: (preg_match('/رقم الفاتورة:\s*([^|]+)/', $expense->description ?? '', $m) ? trim($m[1]) : null);
                $notes = null;
                $parts = array_map('trim', explode('|', $expense->description ?? ''));
                $known = ['المستفيد:', 'طريقة الدفع:', 'رقم الفاتورة:', 'إيجار', 'رواتب', 'مرافق', 'صيانة', 'مستلزمات', 'تسويق', 'مواصلات', 'اتصالات', 'تأمينات', 'ضرائب'];
                foreach (array_reverse($parts) as $p) {
                    $isKnown = false;
                    foreach ($known as $k) { if (str_starts_with($p, $k)) { $isKnown = true; break; } }
                    if (!$isKnown && !empty($p)) { $notes = $p; break; }
                }
            @endphp
                <div class="tf-expense-card">
                    <div class="tf-expense-header">
                        <div class="tf-expense-type">{{ $type }}</div>
                        <div class="tf-expense-amount">{{ number_format($expense->amount, 2) }} ج.م</div>
                    </div>
                    <div class="tf-expense-detail">
                        <i class="fas fa-calendar"></i>
                        <span>{{ \Carbon\Carbon::parse($expense->transaction_date)->format('d/m/Y') }}</span>
                    </div>
                    @if($paymentMethod)
                    <div class="tf-expense-detail">
                        <i class="fas fa-credit-card"></i>
                        <span>{{ $paymentMethod }}</span>
                    </div>
                    @endif
                    @if($beneficiary)
                    <div class="tf-expense-detail">
                        <i class="fas fa-user"></i>
                        <span>{{ $beneficiary }}</span>
                    </div>
                    @endif
                    @if($invoiceNumber)
                    <div class="tf-expense-detail">
                        <i class="fas fa-file-invoice"></i>
                        <span>فاتورة: {{ $invoiceNumber }}</span>
                    </div>
                    @endif
                    @if($notes)
                    <div class="tf-expense-notes">{{ $notes }}</div>
                    @endif
                    <div class="tf-expense-actions">
                        <button class="tf-action-btn tf-action-btn-edit" onclick="alert('سيتم إضافة نموذج التعديل قريباً')">
                            <i class="fas fa-edit"></i> تعديل
                        </button>
                        <form method="POST" action="{{ route('accounting.expenses.destroy', $expense->id) }}" style="flex: 1;" onsubmit="return confirm('هل أنت متأكد من حذف هذا المصروف؟')">
                            @csrf @method('DELETE')
                            <button type="submit" class="tf-action-btn tf-action-btn-delete" style="width: 100%;">
                                <i class="fas fa-trash"></i> حذف
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="tf-paginate">{{ $expenses->links() }}</div>
    @else
        <div class="tf-empty-state">
            <div class="tf-empty-icon"><i class="fas fa-inbox"></i></div>
            <h3 style="color: var(--tf-text-h);">لا توجد مصروفات مسجلة</h3>
            <p style="color: var(--tf-text-m);">ابدأ بإضافة مصروف جديد باستخدام النموذج أعلاه</p>
        </div>
    @endif
</div>
</div>
@endsection