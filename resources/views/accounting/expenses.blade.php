@extends('layouts.app')

@section('title','إدارة المصروفات')
@section('page-title','إدارة المصروفات')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap');
    
    :root {
        --danger-primary: #dc2626;
        --danger-light: #fee2e2;
        --danger-dark: #991b1b;
        --success-color: #10b981;
        --bg-gradient: linear-gradient(135deg, #fef3c7 0%, #fca5a5 100%);
        --card-shadow: 0 20px 60px rgba(220, 38, 38, 0.15);
    }
    
    body {
        font-family: 'IBM Plex Sans Arabic', sans-serif;
        background: var(--bg-gradient);
        min-height: 100vh;
    }
    
    .expenses-hero {
        background: linear-gradient(135deg, #dc2626 0%, #7f1d1d 100%);
        padding: 3rem 2rem;
        border-radius: 25px;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }
    
    .expenses-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }
    
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .hero-content {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2rem;
    }
    
    .hero-text h1 {
        font-family: 'Cairo', sans-serif;
        font-size: 2.5rem;
        font-weight: 800;
        color: white;
        margin: 0 0 0.5rem 0;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .hero-text p {
        color: rgba(255,255,255,0.9);
        font-size: 1.1rem;
    }
    
    .hero-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        padding: 2rem;
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.2);
    }
    
    .hero-stat {
        text-align: center;
    }
    
    .hero-stat-label {
        color: rgba(255,255,255,0.8);
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
    
    .hero-stat-value {
        font-family: 'Cairo', sans-serif;
        font-size: 1.75rem;
        font-weight: 800;
        color: white;
    }
    
    .add-expense-card {
        background: white;
        border-radius: 25px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        border-top: 5px solid var(--danger-primary);
    }
    
    .form-title {
        font-family: 'Cairo', sans-serif;
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .form-title svg {
        color: var(--danger-primary);
    }
    
    .expense-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .form-group.full-width {
        grid-column: 1 / -1;
    }
    
    .form-label {
        font-weight: 600;
        color: #374151;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .required {
        color: var(--danger-primary);
    }
    
    .form-input,
    .form-select,
    .form-textarea {
        padding: 0.875rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-family: inherit;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f9fafb;
    }
    
    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--danger-primary);
        background: white;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }
    
    .form-textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    .submit-btn {
        padding: 1rem 2rem;
        background: linear-gradient(135deg, var(--danger-primary) 0%, var(--danger-dark) 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1.05rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
    }
    
    .expenses-list-card {
        background: white;
        border-radius: 25px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }
    
    .list-header {
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        padding: 1.5rem 2rem;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .list-title {
        font-family: 'Cairo', sans-serif;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
    }
    
    .list-filters {
        display: flex;
        gap: 1rem;
        align-items: center;
    }
    
    .filter-btn {
        padding: 0.5rem 1rem;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        text-decoration: none;
    }
    
    .filter-btn:hover,
    .filter-btn.active {
        background: rgba(255,255,255,0.2);
        color: white;
    }
    
    .expenses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        padding: 2rem;
    }
    
    .expense-card {
        background: #f9fafb;
        border-radius: 15px;
        padding: 1.5rem;
        border-left: 5px solid var(--danger-primary);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .expense-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(220, 38, 38, 0.05) 0%, transparent 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .expense-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(220, 38, 38, 0.2);
    }
    
    .expense-card:hover::before {
        opacity: 1;
    }
    
    .expense-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
        position: relative;
        z-index: 1;
    }
    
    .expense-type {
        font-family: 'Cairo', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: #1f2937;
    }
    
    .expense-amount {
        font-family: 'Cairo', sans-serif;
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--danger-primary);
        direction: ltr;
        text-align: right;
    }
    
    .expense-details {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        position: relative;
        z-index: 1;
    }
    
    .expense-detail {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6b7280;
        font-size: 0.95rem;
    }
    
    .expense-detail svg {
        width: 18px;
        height: 18px;
        color: var(--danger-primary);
        flex-shrink: 0;
    }
    
    .expense-notes {
        margin-top: 1rem;
        padding: 1rem;
        background: white;
        border-radius: 10px;
        color: #4b5563;
        font-size: 0.9rem;
        line-height: 1.6;
        position: relative;
        z-index: 1;
    }
    
    .expense-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        position: relative;
        z-index: 1;
    }
    
    .action-icon-btn {
        flex: 1;
        padding: 0.75rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .edit-btn {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .edit-btn:hover {
        background: #bfdbfe;
    }
    
    .delete-btn {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .delete-btn:hover {
        background: #fecaca;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
    }
    
    .empty-state svg {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        opacity: 0.3;
    }
    
    .empty-state h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .pagination-container {
        padding: 1.5rem 2rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }
    
    .alert-success {
        background: #dcfce7;
        color: #166534;
        border-left: 4px solid #10b981;
    }
    
    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #dc2626;
    }
    
    .text-danger {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    @media (max-width: 768px) {
        .hero-content {
            flex-direction: column;
        }
        
        .hero-stats {
            grid-template-columns: 1fr;
            width: 100%;
        }
        
        .expenses-grid {
            grid-template-columns: 1fr;
        }
        
        .expense-form {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

@php
    // تعريف الأنواع وطرق الدفع
    $expenseTypes = [
        'rent' => 'إيجار',
        'salaries' => 'رواتب',
        'utilities' => 'مرافق (كهرباء، مياه، غاز)',
        'maintenance' => 'صيانة',
        'supplies' => 'مستلزمات',
        'marketing' => 'تسويق ودعاية',
        'transportation' => 'مواصلات',
        'communication' => 'اتصالات',
        'insurance' => 'تأمينات',
        'taxes' => 'ضرائب ورسوم',
        'other' => 'أخرى',
    ];

    $paymentMethods = [
        'cash' => 'نقدي',
        'bank' => 'تحويل بنكي',
        'credit_card' => 'بطاقة ائتمان',
        'check' => 'شيك',
    ];
@endphp

<div class="expenses-hero">
    <div class="hero-content">
        <div class="hero-text">
            <h1>إدارة المصروفات</h1>
            <p>تتبع ومراقبة جميع المصروفات بدقة واحترافية</p>
        </div>
        
        <div class="hero-stats">
            <div class="hero-stat">
                <div class="hero-stat-label">مصروفات اليوم</div>
                <div class="hero-stat-value">{{ number_format($todayExpenses ?? 0, 2) }}</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-label">مصروفات الشهر</div>
                <div class="hero-stat-value">{{ number_format($monthExpenses ?? 0, 2) }}</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-label">إجمالي المصروفات</div>
                <div class="hero-stat-value">{{ number_format($totalExpenses ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="add-expense-card">
    <h2 class="form-title">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        إضافة مصروف جديد
    </h2>
    
    <form method="POST" action="{{ route('accounting.expenses.store') }}" class="expense-form">
        @csrf
        
        <div class="form-group">
            <label class="form-label">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                نوع المصروف <span class="required">*</span>
            </label>
            <select name="type" class="form-select" required>
                <option value="">اختر نوع المصروف</option>
                @foreach($expenseTypes as $key => $label)
                    <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('type')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                المبلغ <span class="required">*</span>
            </label>
            <input type="number" name="amount" class="form-input" placeholder="0.00" step="0.01" value="{{ old('amount') }}" required>
            @error('amount')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                التاريخ <span class="required">*</span>
            </label>
            <input type="date" name="date" class="form-input" value="{{ old('date', date('Y-m-d')) }}" required>
            @error('date')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                طريقة الدفع <span class="required">*</span>
            </label>
            <select name="payment_method" class="form-select" required>
                <option value="">اختر طريقة الدفع</option>
                @foreach($paymentMethods as $key => $label)
                    <option value="{{ $key }}" {{ old('payment_method') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('payment_method')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                المستفيد
            </label>
            <input type="text" name="beneficiary" class="form-input" placeholder="اسم المستفيد" value="{{ old('beneficiary') }}">
            @error('beneficiary')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                رقم الفاتورة / الإيصال
            </label>
            <input type="text" name="invoice_number" class="form-input" placeholder="رقم المستند" value="{{ old('invoice_number') }}">
            @error('invoice_number')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group full-width">
            <label class="form-label">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                ملاحظات وتفاصيل إضافية
            </label>
            <textarea name="notes" class="form-textarea" placeholder="أضف أي ملاحظات أو تفاصيل إضافية هنا...">{{ old('notes') }}</textarea>
            @error('notes')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group full-width">
            <button type="submit" class="submit-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                إضافة المصروف
            </button>
        </div>
    </form>
</div>

<div class="expenses-list-card">
    <div class="list-header">
        <h3 class="list-title">سجل المصروفات ({{ $expenses->total() }})</h3>
        <div class="list-filters">
            <a href="{{ route('accounting.expenses.index') }}" class="filter-btn {{ !request('filter') ? 'active' : '' }}">الكل</a>
            <a href="{{ route('accounting.expenses.index', ['filter' => 'today']) }}" class="filter-btn {{ request('filter') == 'today' ? 'active' : '' }}">اليوم</a>
            <a href="{{ route('accounting.expenses.index', ['filter' => 'month']) }}" class="filter-btn {{ request('filter') == 'month' ? 'active' : '' }}">هذا الشهر</a>
        </div>
    </div>
    
    @if($expenses->count() > 0)
        <div class="expenses-grid">
            @foreach($expenses as $expense)
                @php
                    // استخراج نوع المصروف
                    $type = 'أخرى';
                    if (!empty($expense->category) && isset($expenseTypes[$expense->category])) {
                        $type = $expenseTypes[$expense->category];
                    } else {
                        foreach ($expenseTypes as $arabic) {
                            if (str_contains($expense->description ?? '', $arabic)) {
                                $type = $arabic;
                                break;
                            }
                        }
                    }
                    
                    // استخراج المستفيد
                    $beneficiary = null;
                    if (preg_match('/المستفيد:\s*([^|]+)/', $expense->description ?? '', $matches)) {
                        $beneficiary = trim($matches[1]);
                    }
                    
                    // استخراج طريقة الدفع
                    $paymentMethod = null;
                    if (preg_match('/طريقة الدفع:\s*([^|]+)/', $expense->description ?? '', $matches)) {
                        $paymentMethod = trim($matches[1]);
                    }
                    
                    // استخراج رقم الفاتورة
                    $invoiceNumber = $expense->reference;
                    if (empty($invoiceNumber) && preg_match('/رقم الفاتورة:\s*([^|]+)/', $expense->description ?? '', $matches)) {
                        $invoiceNumber = trim($matches[1]);
                    }
                    
                    // استخراج الملاحظات
                    $notes = null;
                    $parts = array_map('trim', explode('|', $expense->description ?? ''));
                    $knownPrefixes = ['المستفيد:', 'طريقة الدفع:', 'رقم الفاتورة:', 'إيجار', 'رواتب', 'مرافق', 'صيانة', 'مستلزمات', 'تسويق', 'مواصلات', 'اتصالات', 'تأمينات', 'ضرائب'];
                    foreach (array_reverse($parts) as $part) {
                        $isKnown = false;
                        foreach ($knownPrefixes as $prefix) {
                            if (str_starts_with($part, $prefix)) {
                                $isKnown = true;
                                break;
                            }
                        }
                        if (!$isKnown && !empty($part)) {
                            $notes = $part;
                            break;
                        }
                    }
                @endphp
                
                <div class="expense-card">
                    <div class="expense-header">
                        <div class="expense-type">{{ $type }}</div>
                        <div class="expense-amount">{{ number_format($expense->amount, 2) }} ج.م</div>
                    </div>
                    
                    <div class="expense-details">
                        <div class="expense-detail">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>{{ \Carbon\Carbon::parse($expense->transaction_date)->format('d/m/Y') }}</span>
                        </div>
                        
                        @if($paymentMethod)
                            <div class="expense-detail">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span>{{ $paymentMethod }}</span>
                            </div>
                        @endif
                        
                        @if($beneficiary)
                            <div class="expense-detail">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>{{ $beneficiary }}</span>
                            </div>
                        @endif
                        
                        @if($invoiceNumber)
                            <div class="expense-detail">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span>فاتورة: {{ $invoiceNumber }}</span>
                            </div>
                        @endif
                    </div>
                    
                    @if($notes)
                        <div class="expense-notes">
                            {{ $notes }}
                        </div>
                    @endif
                    
                    <div class="expense-actions">
                        <button class="action-icon-btn edit-btn" onclick="alert('سيتم إضافة نموذج التعديل قريباً')">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            تعديل
                        </button>
                        <form method="POST" action="{{ route('accounting.expenses.destroy', $expense->id) }}" style="flex: 1;" onsubmit="return confirm('هل أنت متأكد من حذف هذا المصروف؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-icon-btn delete-btn" style="width: 100%;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                حذف
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="pagination-container">
            {{ $expenses->links() }}
        </div>
    @else
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3>لا توجد مصروفات مسجلة</h3>
            <p>ابدأ بإضافة مصروف جديد باستخدام النموذج أعلاه</p>
        </div>
    @endif
</div>

@endsection