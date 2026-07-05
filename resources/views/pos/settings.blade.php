@extends('layouts.app')

@section('title', 'إعدادات الكاشير')
@section('page-title', 'إعدا@push('styles')
<style>
        :root {
        --tf-bg:          transparent;
        --tf-surface:     #ffffff;
        --tf-surface2:    #f8fafc;
        --tf-border:      #e2e8f0;
        --tf-border-soft: #f1f5f9;
        --tf-indigo:      #2563eb;
        --tf-indigo-light:#3b82f6;
        --tf-indigo-soft: rgba(37, 99, 235, 0.1);
        --tf-blue:        #3b82f6;
        --tf-blue-soft:   rgba(37, 99, 235, 0.1);
        --tf-green:       #2563eb;
        --tf-green-soft:  rgba(37, 99, 235, 0.1);
        --tf-red:         #ef4444;
        --tf-red-soft:    rgba(239, 68, 68, 0.15);
        --tf-amber:       #f59e0b;
        --tf-amber-soft:  rgba(245, 158, 11, 0.15);
        --tf-text-h:      #0f172a;
        --tf-text-b:      #334155;
        --tf-text-m:      #64748b;
        --tf-text-d:      #94a3b8;
        --tf-text-s:      #475569;
        --tf-shadow-sm:   0 2px 12px rgba(0,0,0,0.05);
        --tf-shadow-card: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        --tf-shadow-lg:   0 10px 25px -5px rgba(0, 0, 0, 0.05);
        --radius-lg:      20px;
        --radius-md:      14px;
        --radius-sm:      8px;
    }

    

    .pos-settings-page {
        background: var(--tf-bg);
        min-height: 100vh;
        padding: 24px;
        
        -webkit-
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes iconBounce {
        0%,100% { transform: translateY(0); }
        50%     { transform: translateY(-4px); }
    }

    .animated-fade-up {
        animation: fadeUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    /* Cards */
    .settings-card {
        background: var(--tf-surface);
        border-radius: var(--radius-md);
        border: 1px solid var(--tf-border);
        box-shadow: var(--tf-shadow-card);
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .settings-card:hover {
        box-shadow: var(--tf-shadow-lg);
    }
    .settings-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2);
    }
    .settings-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    .settings-card.blue .settings-card-icon { background: var(--tf-blue-soft); color: var(--tf-blue); }
    .settings-card.amber .settings-card-icon { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .settings-card.indigo .settings-card-icon { background: var(--tf-indigo-soft); color: var(--tf-indigo); }

    .settings-card-title { font-size: 15px; font-weight: 800; color: var(--tf-text-h); margin: 0; }
    .settings-card-subtitle { font-size: 11px; color: var(--tf-text-m); margin: 3px 0 0 0; font-weight: 600; }

    /* Custom Toggles */
    .toggle-switch-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        border-radius: 12px;
        border: 1.5px solid var(--tf-border-soft);
        background: #f8fafc !important;
        transition: all 0.25s;
        cursor: pointer;
    }
    .toggle-switch-container:hover {
        border-color: #cbd5e1 !important;
        background: #f1f5f9 !important;
    }
    .toggle-switch {
        position: relative;
        width: 46px;
        height: 24px;
        background: rgba(255, 255, 255, 0.12) !important;
        border-radius: 50px;
        transition: background 0.25s;
    }
    .toggle-switch::before {
        content: '';
        position: absolute;
        top: 2px;
        right: 2px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: white;
        transition: transform 0.25s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .toggle-input:checked + .toggle-switch {
        background: var(--tf-green) !important;
    }
    .toggle-input:checked + .toggle-switch::before {
        transform: translateX(-22px);
    }

    /* Input overrides to fit style */
    input[type="text"], textarea, select {
        background: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        color: #0f172a !important;
        outline: none;
        border-radius: 0.75rem !important;
        transition: all 0.2s ease-in-out;
    }
    input[type="text"]:focus, textarea:focus, select:focus {
        border-color: var(--tf-indigo) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    }

    .text-slate-800 { color: var(--tf-text-h) !important; }
    .text-slate-700 { color: var(--tf-text-b) !important; }
    .text-slate-600 { color: var(--tf-text-b) !important; }
    .text-slate-500 { color: var(--tf-text-m) !important; }
    .text-slate-400 { color: var(--tf-text-m) !important; }

    /* Header icons override */
    .mb-6 h1 span {
        background: rgba(99, 102, 241, 0.15) !important;
        color: #818cf8 !important;
    }

    /* Submit Bar */
    .mt-6.p-4.bg-white {
        background: #f1f5f9 !important;
        border: 1px solid #f8fafc !important;
        box-shadow: var(--tf-shadow-card) !important;
    }
    .mt-6.p-4.bg-white a {
        border-color: #334155 !important;
        color: var(--tf-text-m) !important;
    }
    .mt-6.p-4.bg-white a:hover {
        background: #f8fafc !important;
        color: #0f172a !important;
    }
    .btn-primary {
        background: var(--tf-indigo) !important;
        color: white !important;
        border: none !important;
    }
    .btn-primary:hover {
        background: #4f46e5 !important;
        box-shadow: 0 0 12px rgba(99, 102, 241, 0.4) !important;
    }

    /* Alerts */
    .bg-emerald-50 {
        background: rgba(16, 185, 129, 0.15) !important;
        border-color: rgba(16, 185, 129, 0.3) !important;
        color: #34d399 !important;
    }
    .bg-emerald-100 {
        background: rgba(16, 185, 129, 0.2) !important;
        color: #10b981 !important;
    }
    .bg-red-50 {
        background: rgba(239, 68, 68, 0.15) !important;
        border-color: rgba(239, 68, 68, 0.3) !important;
        color: #f87171 !important;
    }
    .bg-red-100 {
        background: rgba(239, 68, 68, 0.2) !important;
        color: #ef4444 !important;
    }

    /* Receipt Preview Styling (Glassmorphism / Paper look) */
    .receipt-preview {
        background: #ffffff !important;
        color: #1e293b !important;
        border-radius: var(--radius-md);
        border: 1.5px dashed var(--tf-border);
        padding: 24px;
        box-shadow: 0 15px 35px -5px rgba(0,0,0,0.02), 0 5px 15px -5px rgba(0,0,0,0.02);
        font-family: 'Courier New', Courier, monospace;
        position: relative;
    }
    .receipt-preview * {
        color: #1e293b !important;
    }
    .receipt-preview .text-slate-600 {
        color: #475569 !important;
    }
    .receipt-preview .text-slate-400 {
        color: #94a3b8 !important;
    }
    .receipt-preview .text-indigo-700 {
        color: #4338ca !important;
    }
    .receipt-preview::before, .receipt-preview::after {
        content: '';
        position: absolute;
        left: 0; right: 0;
        height: 8px;
        background-size: 16px 8px;
    }
    .receipt-preview::before {
        top: -8px;
        background-image: radial-gradient(circle, transparent 70%, #ffffff 70%);
        transform: rotate(180deg);
    }
    .receipt-preview-header {
        text-align: center;
        border-bottom: 1px dashed #cbd5e1;
        padding-bottom: 12px;
        margin-bottom: 12px;
    }
    .receipt-preview-footer {
        text-align: center;
        border-top: 1px dashed #cbd5e1;
        padding-top: 12px;
        margin-top: 12px;
        font-size: 12px;
        color: #64748b !important;
    }
</style>
@endpusht-s);
    }

</style>
@endpush

@section('content')
<div class="pos-settings-page" x-data="posSettingsApp()">
    
    <!-- ══ Alerts ══ -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 animated-fade-up">
            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0 text-emerald-600">
                <i class="fas fa-check-circle"></i>
            </div>
            <p class="font-bold text-sm">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl flex items-center gap-3 animated-fade-up">
            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 text-red-600">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <p class="font-bold text-sm">{{ session('error') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl animated-fade-up">
            <div class="flex items-center gap-3 mb-2">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
                <span class="font-bold text-sm">برجاء مراجعة الأخطاء التالية:</span>
            </div>
            <ul class="list-disc list-inside text-xs font-semibold space-y-1 pr-6">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ══ Header ══ -->
    <div class="mb-6 animated-fade-up">
        <h1 class="text-2xl font-black text-slate-800 flex items-center gap-3">
            <span class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center"><i class="fas fa-cogs"></i></span>
            إعدادات الكاشير ونقاط البيع
        </h1>
        <p class="text-xs text-slate-400 font-semibold mt-1">تخصيص نقطة البيع الافتراضية، إدارة الصلاحيات، إعدادات الفاتورة الحرارية وإلزام الورديات.</p>
    </div>

    <!-- ══ Form Container ══ -->
    <form action="{{ route('pos.settings.update') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left & Middle: Input cards -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- 1. Basic configuration card -->
                <div class="settings-card blue animated-fade-up" style="animation-delay: 0.05s;">
                    <div class="settings-card-header">
                        <div class="settings-card-icon"><i class="fas fa-sliders-h"></i></div>
                        <div>
                            <h3 class="settings-card-title">تهيئة نقطة البيع</h3>
                            <p class="settings-card-subtitle">التهيئة الأساسية مثل الاسم، المستودع، ونوع الدفع الافتراضي</p>
                        </div>
                    </div>
                    <div class="card-body p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            <!-- POS Name -->
                            <div>
                                <label class="block text-xs font-black text-slate-600 mb-1.5">اسم نقطة البيع <span class="text-red-500">*</span></label>
                                <input type="text" name="pos_name" value="{{ old('pos_name', $settings->pos_name) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:outline-none transition font-bold" required>
                            </div>

                            <!-- Default warehouse -->
                            <div>
                                <label class="block text-xs font-black text-slate-600 mb-1.5">المستودع الافتراضي للبيع <span class="text-red-500">*</span></label>
                                <select name="default_warehouse_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:outline-none transition bg-white font-bold cursor-pointer" required>
                                    <option value="">-- اختر مستودعاً --</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ old('default_warehouse_id', $settings->default_warehouse_id) == $wh->id ? 'selected' : '' }}>
                                            {{ $wh->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Default payment method -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-black text-slate-600 mb-1.5">طريقة الدفع الافتراضية</label>
                                <select name="default_payment_method" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:outline-none transition bg-white font-bold cursor-pointer">
                                    <option value="cash" {{ old('default_payment_method', $settings->default_payment_method) == 'cash' ? 'selected' : '' }}>نقدي (Cash)</option>
                                    <option value="card" {{ old('default_payment_method', $settings->default_payment_method) == 'card' ? 'selected' : '' }}>بطاقة دفع / شبكة (Card)</option>
                                    <option value="credit" {{ old('default_payment_method', $settings->default_payment_method) == 'credit' ? 'selected' : '' }}>بيع آجل (Credit)</option>
                                    <option value="multiple" {{ old('default_payment_method', $settings->default_payment_method) == 'multiple' ? 'selected' : '' }}>متعدد (Split / Combined)</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- 2. Security & Operation flow switches -->
                <div class="settings-card amber animated-fade-up" style="animation-delay: 0.1s;">
                    <div class="settings-card-header">
                        <div class="settings-card-icon"><i class="fas fa-shield-alt"></i></div>
                        <div>
                            <h3 class="settings-card-title">طبيعة سير العمل والتحقق</h3>
                            <p class="settings-card-subtitle">التحكم في قيود المخزون، فتح الورديات والطباعة المباشرة</p>
                        </div>
                    </div>
                    <div class="card-body p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            <!-- Toggle: Require shift to sell -->
                            <label class="toggle-switch-container select-none">
                                <div style="padding-left:12px;">
                                    <span class="block text-xs font-black text-slate-700">إلزام فتح الوردية قبل البيع</span>
                                    <span class="block text-[10px] text-slate-400 font-semibold mt-0.5">يجب على الكاشير فتح شفت وإدخال العهدة لبدء البيع</span>
                                </div>
                                <input type="checkbox" name="require_shift" value="1" class="hidden toggle-input" {{ old('require_shift', $settings->require_shift) ? 'checked' : '' }}>
                                <div class="toggle-switch flex-shrink-0"></div>
                            </label>

                            <!-- Toggle: Auto print receipt -->
                            <label class="toggle-switch-container select-none">
                                <div style="padding-left:12px;">
                                    <span class="block text-xs font-black text-slate-700">طباعة الفاتورة تلقائياً</span>
                                    <span class="block text-[10px] text-slate-400 font-semibold mt-0.5">إرسال الفاتورة للطابعة فور تأكيد عملية البيع</span>
                                </div>
                                <input type="checkbox" name="auto_print_receipt" value="1" class="hidden toggle-input" {{ old('auto_print_receipt', $settings->auto_print_receipt) ? 'checked' : '' }}>
                                <div class="toggle-switch flex-shrink-0"></div>
                            </label>

                            <!-- Toggle: Allow negative stock -->
                            <label class="toggle-switch-container select-none md:col-span-2">
                                <div style="padding-left:12px;">
                                    <span class="block text-xs font-black text-slate-700">السماح بالبيع من مخزون سالب</span>
                                    <span class="block text-[10px] text-slate-400 font-semibold mt-0.5">تجاوز قيود كميات المخزون الحالية وإتمام البيع حتى لو انتهت الكمية</span>
                                </div>
                                <input type="checkbox" name="allow_negative_stock" value="1" class="hidden toggle-input" {{ old('allow_negative_stock', $settings->allow_negative_stock) ? 'checked' : '' }}>
                                <div class="toggle-switch flex-shrink-0"></div>
                            </label>

                        </div>
                    </div>
                </div>

                <!-- 3. Thermal Receipt Customizer -->
                <div class="settings-card indigo animated-fade-up" style="animation-delay: 0.15s;">
                    <div class="settings-card-header">
                        <div class="settings-card-icon"><i class="fas fa-file-invoice"></i></div>
                        <div>
                            <h3 class="settings-card-title">تخصيص إيصال البيع الحراري</h3>
                            <p class="settings-card-subtitle">التحكم في النصوص المطبوعة أعلى وأسفل الفاتورة</p>
                        </div>
                    </div>
                    <div class="card-body p-6 space-y-4">
                        
                        <!-- Header text -->
                        <div>
                            <label class="block text-xs font-black text-slate-600 mb-1.5">نص ترحيبي أعلى الفاتورة (Header Text)</label>
                            <input type="text" name="receipt_header_text" x-model="headerText" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:outline-none transition font-bold" placeholder="مثال: مرحباً بكم في متجرنا">
                        </div>

                        <!-- Footer text -->
                        <div>
                            <label class="block text-xs font-black text-slate-600 mb-1.5">نص ختامي أسفل الفاتورة (Footer Text)</label>
                            <textarea name="receipt_footer_text" x-model="footerText" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:outline-none transition" placeholder="مثال: البضاعة المباعة لا ترد ولا تستبدل إلا خلال 14 يوماً بموجب إيصال الشراء"></textarea>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Right: Receipt preview panel -->
            <div class="lg:col-span-1 space-y-6">
                <div class="settings-card animated-fade-up" style="animation-delay: 0.2s; position: sticky; top: 24px;">
                    <div class="settings-card-header">
                        <div class="settings-card-icon" style="background:#f1f5f9; color:#475569;"><i class="fas fa-receipt"></i></div>
                        <div>
                            <h3 class="settings-card-title">معاينة الإيصال الحراري</h3>
                            <p class="settings-card-subtitle">معاينة حية ومباشرة لشكل الفاتورة المطبوعة</p>
                        </div>
                    </div>
                    <div class="card-body p-6">
                        <div class="receipt-preview text-slate-800 font-mono text-[11px] leading-relaxed">
                            
                            <!-- Header print -->
                            <div class="receipt-preview-header font-bold text-center">
                                <h4 class="font-black text-sm mb-1">شعار الشركة</h4>
                                <p class="mb-1" x-text="headerText || 'عنوان ترحيبي الفاتورة'"></p>
                                <p>رقم الفاتورة: #INV-2026-0001</p>
                            </div>

                            <!-- Sample items -->
                            <div class="space-y-1.5 border-b border-dashed border-slate-300 pb-3 mb-3">
                                <div class="flex justify-between font-bold">
                                    <span>المنتج × الكمية</span>
                                    <span>الإجمالي</span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>أسمنت بورتلاند × 2 طن</span>
                                    <span>6,200.00</span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>رمل ناعم × 1 متر</span>
                                    <span>400.00</span>
                                </div>
                            </div>

                            <!-- Calculations -->
                            <div class="space-y-1 border-b border-dashed border-slate-300 pb-3 mb-3 font-bold">
                                <div class="flex justify-between">
                                    <span>المجموع الفرعي</span>
                                    <span>6,600.00 ج.م</span>
                                </div>
                                <div class="flex justify-between text-slate-600 text-[10px]">
                                    <span>ضريبة القيمة المضافة (14%)</span>
                                    <span>924.00 ج.م</span>
                                </div>
                                <div class="flex justify-between text-sm text-indigo-700">
                                    <span>الإجمالي الكلي</span>
                                    <span>7,524.00 ج.م</span>
                                </div>
                            </div>

                            <!-- Footer print -->
                            <div class="receipt-preview-footer text-center">
                                <p class="leading-relaxed" x-text="footerText || 'شكراً لزيارتكم'"></p>
                                <div class="mt-4 text-[9px] text-slate-400">
                                    <p>Magzani Cloud System</p>
                                    <p>www.magzani.com</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ══ Submit Action Bar ══ -->
        <div class="mt-6 p-4 bg-white rounded-2xl border border-slate-100 shadow-md flex justify-end gap-3 animated-fade-up" style="animation-delay: 0.25s;">
            <a href="{{ route('pos.index') }}" class="px-6 py-2.5 border border-slate-200 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-50 transition">إلغاء</a>
            <button type="submit" class="btn-primary py-2.5 px-8 rounded-xl text-sm font-black flex items-center gap-2">
                <i class="fas fa-save"></i> حفظ جميع الإعدادات
            </button>
        </div>

    </form>

</div>
@endsection

@push('scripts')
<script>
    function posSettingsApp() {
        return {
            headerText: '{{ old("receipt_header_text", $settings->receipt_header_text) }}',
            footerText: '{{ old("receipt_footer_text", $settings->receipt_footer_text) }}',
        };
    }
</script>
@endpush
