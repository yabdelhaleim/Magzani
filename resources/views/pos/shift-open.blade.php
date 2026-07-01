@extends('layouts.app')

@section('title', 'فتح وردية جديدة')
@section('page-title', 'فتح وردية جديدة')

@push('styles')
<style>
    :root {
        --tf-bg:          transparent;
        --tf-surface:     rgba(22, 33, 56, 0.6);
        --tf-surface2:    rgba(10, 16, 28, 0.55);
        --tf-border:      rgba(255, 255, 255, 0.06);
        --tf-indigo:      #6366f1;
        --tf-green:       #10b981;
        --tf-green-soft:  rgba(16, 185, 129, 0.15);
        --tf-text-h:      #f1f5f9;
        --tf-text-b:      #cbd5e1;
        --tf-text-m:      #94a3b8;
        --tf-shadow-card: 0 8px 32px 0 rgba(0, 0, 0, 0.25);
        --tf-shadow-lg:   0 12px 40px rgba(0, 0, 0, 0.35);
        --radius-lg:      24px;
        --radius-md:      16px;
    }

    /* Scoped Dark Mode Overrides for Immersive Cashier Experience */
    body, .main-content, #mainContent {
        background: radial-gradient(circle at top right, #131e35, #080d1a) !important;
        color: #e2e8f0 !important;
    }
    .sidebar {
        background: #070b14 !important;
        border-left: 1px solid rgba(255, 255, 255, 0.03) !important;
    }
    .sidebar * {
        color: rgba(226, 232, 240, 0.65) !important;
    }
    .sidebar .nav-item.active, .sidebar .nav-item.active * {
        background: rgba(16, 185, 129, 0.1) !important;
        color: #10b981 !important;
        border-right: 3px solid #10b981 !important;
    }
    .sidebar .nav-section-label {
        color: rgba(226, 232, 240, 0.3) !important;
    }
    .sidebar .nav-divider {
        border-color: rgba(255, 255, 255, 0.03) !important;
    }
    .main-header {
        background: #070b14 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
    }
    .main-header * {
        color: #e2e8f0 !important;
    }
    .main-footer {
        background: #070b14 !important;
        border-top: 1px solid rgba(255, 255, 255, 0.03) !important;
        color: rgba(226, 232, 240, 0.35) !important;
    }

    .shift-open-page {
        min-height: 100vh;
        background: var(--tf-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 16px;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulse-ring {
        0%   { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
        70%  { transform: scale(1);    box-shadow: 0 0 0 16px rgba(16,185,129,0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16,185,129,0); }
    }

    .shift-card {
        background: var(--tf-surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--tf-border);
        box-shadow: var(--tf-shadow-lg);
        width: 100%;
        max-width: 520px;
        overflow: hidden;
        animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .shift-card-header {
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        padding: 36px 32px 28px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .shift-card-header::before {
        content: '';
        position: absolute;
        top: -50%; right: -30%;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.07);
    }
    .shift-card-header::after {
        content: '';
        position: absolute;
        bottom: -40%; left: -20%;
        width: 160px; height: 160px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }

    .shift-icon-wrap {
        width: 72px; height: 72px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 16px;
        font-size: 28px;
        color: white;
        animation: pulse-ring 2.5s ease-in-out infinite;
        position: relative; z-index: 1;
    }
    .shift-card-header h1 {
        color: white; font-size: 22px; font-weight: 900; margin: 0 0 6px;
        position: relative; z-index: 1;
    }
    .shift-card-header p {
        color: rgba(255,255,255,0.8); font-size: 13px; font-weight: 600; margin: 0;
        position: relative; z-index: 1;
    }

    .shift-info-bar {
        background: var(--tf-surface2);
        border-bottom: 1px solid var(--tf-border);
        padding: 16px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .shift-info-item {
        text-align: center;
    }
    .shift-info-item .label {
        font-size: 10px; font-weight: 700; color: var(--tf-text-m); text-transform: uppercase; letter-spacing: 0.5px;
    }
    .shift-info-item .value {
        font-size: 14px; font-weight: 900; color: var(--tf-text-h); margin-top: 2px;
    }

    .shift-body {
        padding: 32px;
    }

    .balance-input-wrap {
        position: relative;
        margin-bottom: 20px;
    }
    .balance-input-wrap label {
        display: block;
        font-size: 12px; font-weight: 800; color: var(--tf-text-b);
        margin-bottom: 8px;
    }
    .balance-input-group {
        display: flex;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 14px;
        overflow: hidden;
        background: rgba(10, 16, 28, 0.65) !important;
        transition: all 0.2s;
    }
    .balance-input-group:focus-within {
        border-color: var(--tf-green) !important;
        box-shadow: 0 0 12px rgba(16, 185, 129, 0.3) !important;
    }
    .balance-currency {
        background: rgba(16, 185, 129, 0.15) !important;
        color: #34d399 !important;
        font-weight: 900;
        font-size: 14px;
        padding: 14px 18px;
        border-left: 1px solid rgba(255, 255, 255, 0.06) !important;
        display: flex; align-items: center;
        white-space: nowrap;
    }
    .balance-input-group input {
        flex: 1;
        border: none; outline: none;
        padding: 14px 18px;
        font-size: 20px; font-weight: 900;
        color: #f1f5f9 !important;
        background: transparent;
        text-align: left;
        direction: ltr;
    }
    .balance-hint {
        font-size: 11px; color: var(--tf-text-m); font-weight: 600;
        margin-top: 6px;
    }

    .notes-wrap label {
        display: block;
        font-size: 12px; font-weight: 800; color: var(--tf-text-b);
        margin-bottom: 8px;
    }
    .notes-wrap textarea {
        width: 100%; border: 1px solid rgba(255, 255, 255, 0.08) !important; border-radius: 12px;
        padding: 12px 16px; font-size: 13px; color: #f1f5f9 !important;
        background: rgba(10, 16, 28, 0.65) !important; transition: all 0.2s;
        resize: none; font-family: inherit; font-weight: 600;
        box-sizing: border-box;
    }
    .notes-wrap textarea:focus {
        outline: none; border-color: var(--tf-green) !important;
        box-shadow: 0 0 12px rgba(16, 185, 129, 0.3) !important;
    }

    .btn-open-shift {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        color: white;
        font-size: 16px; font-weight: 900;
        border: none; border-radius: 14px;
        cursor: pointer;
        transition: all 0.25s;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        margin-top: 24px;
        letter-spacing: 0.3px;
    }
    .btn-open-shift:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35);
    }
    .btn-open-shift:active { transform: translateY(0); }

    .btn-cancel-shift {
        width: 100%; padding: 12px;
        background: transparent;
        color: var(--tf-text-m); font-size: 13px; font-weight: 700;
        border: 1px solid rgba(255, 255, 255, 0.08) !important; border-radius: 12px;
        cursor: pointer; margin-top: 10px;
        transition: all 0.2s; text-align: center; text-decoration: none;
        display: block;
    }
    .btn-cancel-shift:hover {
        border-color: rgba(255, 255, 255, 0.2) !important;
        background: rgba(255, 255, 255, 0.05) !important;
        color: #f1f5f9 !important;
    }

    .alert-info {
        background: rgba(99, 102, 241, 0.15) !important;
        border: 1px solid rgba(99, 102, 241, 0.3) !important;
        color: #818cf8 !important;
        border-radius: 12px;
        padding: 12px 16px; font-size: 12px; font-weight: 700;
        display: flex; align-items: flex-start; gap: 10px; margin-bottom: 20px;
    }

    .error-message {
        background: rgba(239, 68, 68, 0.15) !important;
        border: 1px solid rgba(239, 68, 68, 0.3) !important;
        color: #f87171 !important;
        border-radius: 12px;
        padding: 12px 16px; font-size: 12px; font-weight: 700;
        margin-bottom: 16px;
    }
</style>
@endpush

@section('content')
<div class="shift-open-page">
    <div class="shift-card">

        {{-- Header --}}
        <div class="shift-card-header">
            <div class="shift-icon-wrap">
                <i class="fas fa-cash-register"></i>
            </div>
            <h1>فتح وردية جديدة</h1>
            <p>قبل البدء بالبيع، يجب فتح وردية وتسجيل رصيد الصندوق الابتدائي</p>
        </div>

        {{-- Info Bar --}}
        <div class="shift-info-bar">
            <div class="shift-info-item">
                <div class="label">الكاشير</div>
                <div class="value">{{ auth()->user()->name }}</div>
            </div>
            <div class="shift-info-item">
                <div class="label">التاريخ</div>
                <div class="value">{{ now()->format('d/m/Y') }}</div>
            </div>
            <div class="shift-info-item">
                <div class="label">الوقت</div>
                <div class="value" id="live-time">{{ now()->format('H:i') }}</div>
            </div>
        </div>

        {{-- Body --}}
        <div class="shift-body">

            {{-- Alerts --}}
            @if(session('error'))
                <div class="error-message">
                    <i class="fas fa-exclamation-circle ml-2"></i>{{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="error-message">
                    @foreach($errors->all() as $error)
                        <div>• {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="alert-info">
                <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
                <span>أدخل الرصيد الموجود في الصندوق حالياً (العهدة). سيُستخدم هذا الرقم لحساب الفرق عند التسليم في نهاية الوردية.</span>
            </div>

            <form action="{{ route('pos.shift.open') }}" method="POST">
                @csrf

                {{-- Opening Balance --}}
                <div class="balance-input-wrap">
                    <label for="opening_balance">رصيد الصندوق الابتدائي (العهدة) <span style="color:#dc2626">*</span></label>
                    <div class="balance-input-group">
                        <input
                            type="number"
                            id="opening_balance"
                            name="opening_balance"
                            value="{{ old('opening_balance', '0') }}"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            autofocus
                            required
                        >
                        <div class="balance-currency">ج.م</div>
                    </div>
                    <p class="balance-hint">يمكن إدخال صفر إذا كان الصندوق فارغاً</p>
                </div>

                {{-- Notes --}}
                <div class="notes-wrap">
                    <label for="shift_notes">ملاحظات (اختياري)</label>
                    <textarea id="shift_notes" name="notes" rows="2" placeholder="أي ملاحظات إضافية عن بداية الوردية...">{{ old('notes') }}</textarea>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-open-shift">
                    <i class="fas fa-play-circle"></i>
                    فتح الوردية والبدء بالبيع
                </button>

                <a href="{{ route('pos.index') }}" class="btn-cancel-shift">
                    رجوع لشاشة الكاشير
                </a>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Update live time every minute
    function updateTime() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const el = document.getElementById('live-time');
        if (el) el.textContent = h + ':' + m;
    }
    setInterval(updateTime, 30000);
</script>
@endpush
