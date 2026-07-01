@extends('layouts.app')

@section('title', 'إغلاق الوردية وتسليم الصندوق')
@section('page-title', 'إغلاق وتسليم الوردية')

@push('styles')
<style>
    :root {
        --tf-bg:          transparent;
        --tf-surface:     rgba(22, 33, 56, 0.6);
        --tf-surface2:    rgba(10, 16, 28, 0.55);
        --tf-border:      rgba(255, 255, 255, 0.06);
        --tf-border-soft: rgba(255, 255, 255, 0.04);
        --tf-indigo:      #6366f1;
        --tf-indigo-soft: rgba(99, 102, 241, 0.15);
        --tf-green:       #10b981;
        --tf-green-soft:  rgba(16, 185, 129, 0.15);
        --tf-red:         #ef4444;
        --tf-red-soft:    rgba(239, 68, 68, 0.15);
        --tf-amber:       #f59e0b;
        --tf-amber-soft:  rgba(245, 158, 11, 0.15);
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

    .shift-close-page {
        min-height: 100vh;
        background: var(--tf-bg);
        padding: 32px 20px;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .close-header {
        max-width: 1000px; margin: 0 auto 28px;
        animation: fadeUp 0.4s ease both;
    }
    .close-header h1 {
        font-size: 24px; font-weight: 900; color: var(--tf-text-h);
        display: flex; align-items: center; gap: 12px;
    }
    .close-header h1 .icon-wrap {
        width: 44px; height: 44px; border-radius: 12px;
        background: rgba(239, 68, 68, 0.15) !important; color: #f87171 !important;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
    }
    .close-header p {
        font-size: 12px; color: var(--tf-text-m); font-weight: 600; margin: 4px 0 0 56px;
    }

    .close-grid {
        max-width: 1000px; margin: 0 auto;
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 768px) { .close-grid { grid-template-columns: 1fr; } }

    .close-card {
        background: var(--tf-surface);
        border-radius: var(--radius-md);
        border: 1px solid var(--tf-border);
        box-shadow: var(--tf-shadow-card);
        overflow: hidden;
        animation: fadeUp 0.4s ease both;
    }
    .close-card.full-width { grid-column: 1 / -1; }

    .close-card-header {
        display: flex; align-items: center; gap: 12px;
        padding: 18px 22px;
        border-bottom: 1px solid var(--tf-border);
        background: var(--tf-surface2);
    }
    .close-card-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
    }
    .icon-indigo { background: var(--tf-indigo-soft); color: var(--tf-indigo); }
    .icon-green  { background: var(--tf-green-soft); color: var(--tf-green); }
    .icon-amber  { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .icon-red    { background: var(--tf-red-soft); color: var(--tf-red); }

    .close-card-title { font-size: 14px; font-weight: 800; color: var(--tf-text-h); margin: 0; }
    .close-card-sub   { font-size: 11px; color: var(--tf-text-m); font-weight: 600; margin: 2px 0 0; }

    .close-card-body { padding: 22px; }

    /* Summary Items */
    .summary-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid var(--tf-border);
    }
    .summary-row:last-child { border-bottom: none; }
    .summary-row .s-label { font-size: 12px; font-weight: 700; color: var(--tf-text-m); }
    .summary-row .s-value { font-size: 14px; font-weight: 900; color: var(--tf-text-h); }
    .summary-row .s-value.green { color: var(--tf-green); }
    .summary-row .s-value.red   { color: var(--tf-red); }
    .summary-row .s-value.large { font-size: 18px; }

    /* Stat Boxes */
    .stat-boxes {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .stat-box {
        background: var(--tf-surface2);
        border: 1px solid var(--tf-border);
        border-radius: 12px;
        padding: 14px;
        text-align: center;
    }
    .stat-box .stat-num {
        font-size: 22px; font-weight: 900; color: var(--tf-text-h); margin-bottom: 4px;
    }
    .stat-box .stat-label {
        font-size: 11px; font-weight: 700; color: var(--tf-text-m);
    }

    /* Input counter styling */
    .close-card input[type="number"], .close-card textarea {
        background: rgba(10, 16, 28, 0.65) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        color: #f1f5f9 !important;
        outline: none;
    }
    .close-card input[type="number"]:focus, .close-card textarea:focus {
        border-color: var(--tf-indigo) !important;
        box-shadow: 0 0 12px rgba(99, 102, 241, 0.3) !important;
    }

    /* Denomination Total card override */
    .close-card-body div[style*="linear-gradient"] {
        background: rgba(16, 185, 129, 0.15) !important;
        border: 1.5px solid rgba(16, 185, 129, 0.3) !important;
    }
    .close-card-body div[style*="linear-gradient"] span {
        color: #34d399 !important;
    }

    /* Actual Balance Input */
    .actual-input-group {
        display: flex;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 14px;
        overflow: hidden;
        background: rgba(10, 16, 28, 0.65) !important;
        transition: all 0.2s;
    }
    .actual-input-group:focus-within {
        border-color: var(--tf-indigo) !important;
        box-shadow: 0 0 12px rgba(99, 102, 241, 0.3) !important;
    }
    .actual-input-group .currency-tag {
        background: rgba(99, 102, 241, 0.15) !important;
        color: #818cf8 !important;
        font-weight: 900; font-size: 14px;
        padding: 14px 18px;
        border-left: 1px solid rgba(255, 255, 255, 0.06) !important;
        display: flex; align-items: center;
    }
    .actual-input-group input {
        flex: 1; border: none; outline: none;
        padding: 14px 18px;
        font-size: 20px; font-weight: 900;
        color: #f1f5f9 !important;
        background: transparent;
        text-align: left; direction: ltr;
    }

    /* Difference display */
    .diff-display {
        border-radius: 12px; padding: 14px;
        display: flex; align-items: center; gap: 12px;
        margin-top: 12px;
    }
    .diff-display.ok     { background: rgba(16, 185, 129, 0.15) !important; }
    .diff-display.over   { background: rgba(245, 158, 11, 0.15) !important; }
    .diff-display.under  { background: rgba(239, 68, 68, 0.15) !important; }
    .diff-icon { font-size: 22px; }
    .diff-display.ok    .diff-icon { color: var(--tf-green) !important; }
    .diff-display.over  .diff-icon { color: var(--tf-amber) !important; }
    .diff-display.under .diff-icon { color: var(--tf-red) !important; }
    .diff-text { font-size: 13px; font-weight: 700; }
    .diff-display.ok    .diff-text { color: #34d399 !important; }
    .diff-display.over  .diff-text { color: #fbbf24 !important; }
    .diff-display.under .diff-text { color: #f87171 !important; }
    .diff-num { font-size: 18px; font-weight: 900; }

    /* Buttons */
    .btn-close-shift {
        width: 100%; padding: 16px;
        background: linear-gradient(135deg, #ef4444, #dc2626) !important;
        color: white; font-size: 15px; font-weight: 900;
        border: none; border-radius: 14px; cursor: pointer;
        transition: all 0.25s;
        display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .btn-close-shift:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.35) !important;
    }

    .btn-back {
        display: block; width: 100%; padding: 13px;
        background: transparent; border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 12px; color: var(--tf-text-m) !important;
        font-size: 13px; font-weight: 700;
        cursor: pointer; text-align: center; text-decoration: none;
        margin-top: 10px; transition: all 0.2s;
    }
    .btn-back:hover {
        border-color: rgba(255, 255, 255, 0.2) !important;
        background: rgba(255, 255, 255, 0.05) !important;
        color: #f1f5f9 !important;
    }

    .duration-badge {
        background: var(--tf-indigo-soft); color: var(--tf-indigo);
        font-size: 11px; font-weight: 800;
        padding: 4px 10px; border-radius: 20px;
    }
</style>
@endpush

@section('content')
<div class="shift-close-page" x-data="shiftCloseApp()">

    {{-- Alerts --}}
    @if(session('error'))
        <div style="max-width:1000px; margin:0 auto 20px; padding:14px 18px; background:#fef2f2; border:1px solid #fecaca; border-radius:12px; color:#dc2626; font-weight:700; font-size:13px;">
            <i class="fas fa-exclamation-circle ml-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="close-header">
        <h1>
            <span class="icon-wrap"><i class="fas fa-lock"></i></span>
            إغلاق وتسليم الوردية
        </h1>
        <p>مراجعة وتأكيد رصيد الصندوق قبل إغلاق الوردية وتسليمها للمدير</p>
    </div>

    <form action="{{ route('pos.shift.close') }}" method="POST">
        @csrf
        <div class="close-grid">

            {{-- Card 1: Shift Summary --}}
            <div class="close-card" style="animation-delay:0.05s">
                <div class="close-card-header">
                    <div class="close-card-icon icon-indigo"><i class="fas fa-user-clock"></i></div>
                    <div>
                        <p class="close-card-title">ملخص الوردية</p>
                        <p class="close-card-sub">معلومات الجلسة الحالية</p>
                    </div>
                    <span class="duration-badge mr-auto">{{ $shift->duration }}</span>
                </div>
                <div class="close-card-body">
                    <div class="summary-row">
                        <span class="s-label">الكاشير</span>
                        <span class="s-value">{{ $shift->user->name }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">وقت الفتح</span>
                        <span class="s-value">{{ $shift->opened_at->format('d/m/Y — H:i') }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">رصيد الافتتاح</span>
                        <span class="s-value">{{ number_format($shift->opening_balance, 2) }} ج.م</span>
                    </div>
                    @if(auth()->user()->isAdmin())
                    <div class="summary-row">
                        <span class="s-label">إجمالي المبيعات</span>
                        <span class="s-value green">+ {{ number_format($shift->total_sales, 2) }} ج.م</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">إجمالي المرتجعات</span>
                        <span class="s-value red">- {{ number_format($shift->total_returns, 2) }} ج.م</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">صافي المبيعات</span>
                        <span class="s-value" style="color:var(--tf-indigo);">{{ number_format($shift->total_sales - $shift->total_returns, 2) }} ج.م</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">الرصيد النقدي المتوقع</span>
                        <span class="s-value large amber">{{ number_format($expectedBalance, 2) }} ج.م</span>
                    </div>
                    @else
                    <div class="summary-row" style="justify-content: center; padding: 16px 0;">
                        <span class="s-label" style="color:var(--tf-indigo); font-size:13px;"><i class="fas fa-eye-slash ml-1"></i> يتم مراجعة أرقام المبيعات من قبل الإدارة</span>
                    </div>
                    @endif
                </div>
            </div>

            @if(auth()->user()->isAdmin())
            {{-- Card 2: Statistics --}}
            <div class="close-card" style="animation-delay:0.1s">
                <div class="close-card-header">
                    <div class="close-card-icon icon-green"><i class="fas fa-chart-bar"></i></div>
                    <div>
                        <p class="close-card-title">إحصائيات الوردية</p>
                        <p class="close-card-sub">ملخص نشاط الوردية</p>
                    </div>
                </div>
                <div class="close-card-body">
                    <div class="stat-boxes">
                        <div class="stat-box">
                            <div class="stat-num" style="color: var(--tf-green)">{{ $shift->sales_count }}</div>
                            <div class="stat-label">فاتورة بيع</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-num" style="color: var(--tf-red)">{{ $shift->returns_count }}</div>
                            <div class="stat-label">مرتجع</div>
                        </div>
                        <div class="stat-box" style="grid-column: 1 / -1;">
                            <div class="stat-num" style="color: var(--tf-indigo)">{{ number_format($shift->total_sales - $shift->total_returns, 2) }}</div>
                            <div class="stat-label">صافي مبيعات الوردية (ج.م)</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Card 3: Closing Balance + Difference --}}
            <div class="close-card full-width" style="animation-delay:0.15s">
                <div class="close-card-header">
                    <div class="close-card-icon icon-amber"><i class="fas fa-coins"></i></div>
                    <div>
                        <p class="close-card-title">عد الصندوق وتسجيل الرصيد الفعلي</p>
                        <p class="close-card-sub">أدخل الرصيد الفعلي الموجود في الصندوق الآن</p>
                    </div>
                </div>
                <div class="close-card-body">
                    <div style="max-width: 500px; margin: 0 auto;">

                        {{-- Cash Denomination Counter --}}
                        <div x-data="denominationApp()" style="margin-bottom: 20px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                                <label style="font-size:12px; font-weight:800; color:var(--tf-text-b);">
                                    <i class="fas fa-calculator" style="color:var(--tf-amber); margin-left:6px;"></i>
                                    عداد الأوراق النقدية — أدخل عدد كل فئة
                                </label>
                                <button type="button" @click="resetAll()" style="font-size:11px; color:var(--tf-text-m); cursor:pointer; border:none; background:none; font-weight:700;">
                                    <i class="fas fa-redo"></i> إعادة تعيين
                                </button>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px;">
                                <template x-for="d in denominations" :key="d.value">
                                    <div style="display:flex; align-items:center; gap:8px; background:var(--tf-surface2); border:1px solid var(--tf-border); border-radius:10px; padding:10px 12px;">
                                        <span style="font-size:13px; font-weight:900; color:var(--tf-text-h); min-width:40px;" x-text="d.label"></span>
                                        <span style="font-size:11px; color:var(--tf-text-m);">×</span>
                                        <input type="number"
                                               x-model.number="d.count"
                                               @input="syncTotal()"
                                               min="0" step="1" placeholder="0"
                                               style="flex:1; border:1.5px solid var(--tf-border); border-radius:8px; padding:6px 10px; font-size:14px; font-weight:900; text-align:center; outline:none; color:var(--tf-text-h); width:60px;">
                                        <span style="font-size:11px; font-weight:700; color:var(--tf-green); min-width:50px; text-align:left;" x-text="(d.count * d.value).toLocaleString('ar-EG') + ' ج'"></span>
                                    </div>
                                </template>
                            </div>

                            {{-- Denomination Total --}}
                            <div style="background: linear-gradient(135deg,var(--tf-green-soft),#d1fae5); border:1.5px solid #6ee7b7; border-radius:12px; padding:14px 18px; display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <span style="font-size:13px; font-weight:800; color:#065f46;">إجمالي العد:</span>
                                <span style="font-size:20px; font-weight:900; color:#059669;" x-text="denominationTotal.toLocaleString('ar-EG', {minimumFractionDigits:2}) + ' ج.م'"></span>
                            </div>
                            <button type="button" @click="applyTotal()"
                                    style="width:100%; padding:10px; background:var(--tf-indigo); color:white; border:none; border-radius:10px; font-size:13px; font-weight:800; cursor:pointer; margin-bottom:16px;">
                                <i class="fas fa-check ml-2"></i> تطبيق المجموع كرصيد فعلي
                            </button>
                        </div>

                        {{-- Actual Balance Input --}}
                        <label style="display:block; font-size:12px; font-weight:800; color:var(--tf-text-b); margin-bottom:8px;">
                            الرصيد الفعلي في الصندوق <span style="color:#dc2626">*</span>
                            <span style="color:var(--tf-text-m); font-weight:600; font-size:11px;">(يمكن تعديله يدوياً)</span>
                        </label>
                        <div class="actual-input-group">
                            <input
                                type="number"
                                name="closing_balance_actual"
                                x-model.number="actualBalance"
                                value="{{ old('closing_balance_actual') }}"
                                min="0" step="0.01" placeholder="0.00"
                                required
                            >
                            <div class="currency-tag">ج.م</div>
                        </div>
                        @error('closing_balance_actual')
                            <p style="color:#dc2626; font-size:12px; font-weight:700; margin-top:6px;">{{ $message }}</p>
                        @enderror


                        @if(auth()->user()->isAdmin())
                        {{-- Difference Display --}}
                        <div
                            :class="diffClass"
                            class="diff-display"
                        >
                            <span class="diff-icon">
                                <i :class="diffIcon" class="fas"></i>
                            </span>
                            <div class="flex-1">
                                <div class="diff-text" x-text="diffLabel"></div>
                                <div class="diff-num" x-text="formattedDiff + ' ج.م'"></div>
                            </div>
                        </div>
                        @else
                        <div class="diff-display" style="background: var(--tf-indigo-soft); justify-content:center; margin-top:16px;">
                            <span class="diff-text" style="color:var(--tf-indigo); font-size:14px;"><i class="fas fa-info-circle ml-1"></i> سيتم مراجعة العجز أو الزيادة من قبل الإدارة بعد التسليم</span>
                        </div>
                        @endif

                        {{-- Notes --}}
                        <div style="margin-top: 18px;">
                            <label style="display:block; font-size:12px; font-weight:800; color:var(--tf-text-b); margin-bottom:8px;">
                                ملاحظات التسليم
                            </label>
                            <textarea
                                name="notes"
                                rows="3"
                                style="width:100%; border:2px solid var(--tf-border); border-radius:12px; padding:12px 16px; font-size:13px; color:var(--tf-text-h); resize:none; font-family:inherit; font-weight:600; box-sizing:border-box; outline:none; transition: border-color 0.2s;"
                                placeholder="أي ملاحظات عند تسليم الوردية مثل أسباب الفرق، مشاكل واجهتها...">{{ old('notes') }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="close-card full-width" style="animation-delay:0.2s; padding:0;">
                <div style="padding: 20px 24px; display: flex; gap: 12px; justify-content: flex-end; align-items:center; flex-wrap:wrap;">
                    <a href="{{ route('pos.index') }}" class="btn-back" style="width:auto; padding: 13px 24px; display:inline-block; margin-top:0;">
                        <i class="fas fa-arrow-right ml-2"></i> رجوع للكاشير
                    </a>
                    <button type="submit" class="btn-close-shift" style="width:auto; padding:14px 32px;">
                        <i class="fas fa-lock"></i>
                        إغلاق الوردية وتسليم الصندوق نهائياً
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function shiftCloseApp() {
    return {
        actualBalance: {{ old('closing_balance_actual', 0) }},
        expectedBalance: {{ auth()->user()->isAdmin() ? $expectedBalance : 'null' }},

        get diff() {
            if (this.expectedBalance === null) return 0;
            return this.actualBalance - this.expectedBalance;
        },
        get diffClass() {
            if (this.diff === 0) return 'diff-display ok';
            if (this.diff > 0)  return 'diff-display over';
            return 'diff-display under';
        },
        get diffIcon() {
            if (this.diff === 0) return 'fa-check-circle';
            if (this.diff > 0)  return 'fa-arrow-up';
            return 'fa-arrow-down';
        },
        get diffLabel() {
            if (this.diff === 0) return 'الصندوق متطابق تماماً ✅';
            if (this.diff > 0)  return 'زيادة في الصندوق — يرجى مراجعة الفواتير';
            return 'نقص في الصندوق — يرجى مراجعة الفواتير';
        },
        get formattedDiff() {
            const abs = Math.abs(this.diff).toFixed(2);
            const sign = this.diff >= 0 ? '+' : '-';
            return sign + Number(abs).toLocaleString('ar-EG');
        }
    };
}
</script>

<script>
function denominationApp() {
    return {
        denominations: [
            { value: 200,  label: '200 ج', count: 0 },
            { value: 100,  label: '100 ج', count: 0 },
            { value: 50,   label: '50 ج',  count: 0 },
            { value: 20,   label: '20 ج',  count: 0 },
            { value: 10,   label: '10 ج',  count: 0 },
            { value: 5,    label: '5 ج',   count: 0 },
            { value: 1,    label: '1 ج',   count: 0 },
            { value: 0.5,  label: '½ ج',   count: 0 },
        ],

        get denominationTotal() {
            return this.denominations.reduce((sum, d) => sum + (d.count * d.value), 0);
        },

        syncTotal() {
            // auto-update actualBalance in parent shiftCloseApp scope
            const parent = Alpine.$data(document.querySelector('[x-data="shiftCloseApp()"]'));
            if (parent) parent.actualBalance = parseFloat(this.denominationTotal.toFixed(2));
        },

        applyTotal() {
            const total = parseFloat(this.denominationTotal.toFixed(2));
            // Find shiftCloseApp instance and update actualBalance
            document.querySelectorAll('[x-data]').forEach(el => {
                const data = Alpine.$data(el);
                if (typeof data.actualBalance !== 'undefined') {
                    data.actualBalance = total;
                }
            });
        },

        resetAll() {
            this.denominations.forEach(d => d.count = 0);
            this.syncTotal();
        }
    };
}
</script>
@endpush

