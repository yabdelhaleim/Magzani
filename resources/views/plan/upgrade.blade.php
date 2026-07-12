@extends('layouts.app')

@section('title', 'ترقية باقة Kayyan SaaS')
@section('page-title', 'ترقية الباقة')

@push('styles')
<style>
    .upg-wrap {
        --upg-radius: 24px;
    }
    .upg-hero {
        position: relative;
        overflow: hidden;
        border-radius: var(--upg-radius);
        background:
            linear-gradient(135deg, #0f172a 0%, #1e1b4b 45%, #312e81 100%);
        border: 1px solid rgba(245, 158, 11, 0.18);
        box-shadow: 0 20px 50px -20px rgba(15, 23, 42, 0.6);
    }
    .upg-hero::before {
        content: "";
        position: absolute; inset: 0; pointer-events: none;
        background:
            radial-gradient(circle at 12% 20%, rgba(245, 158, 11, 0.18), transparent 40%),
            radial-gradient(circle at 88% 80%, rgba(99, 102, 241, 0.22), transparent 45%);
    }
    .upg-hero-icon {
        width: clamp(56px, 10vw, 80px);
        height: clamp(56px, 10vw, 80px);
        aspect-ratio: 1;
        border-radius: 20px;
        background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: clamp(1.5rem, 4vw, 2rem);
        box-shadow: 0 14px 28px -8px rgba(245, 158, 11, 0.45);
        flex-shrink: 0;
    }

    .upg-card {
        position: relative;
        border-radius: var(--upg-radius);
        transition: transform .3s cubic-bezier(0.22, 1, 0.36, 1),
                    box-shadow .3s ease,
                    border-color .3s ease;
        overflow: hidden;
    }
    .upg-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.18);
    }
    .upg-card.is-featured {
        background: linear-gradient(165deg, #0f172a 0%, #1e293b 100%);
        border: 1px solid rgba(245, 158, 11, 0.35);
        color: #f8fafc;
        box-shadow: 0 25px 50px -12px rgba(245, 158, 11, 0.18);
    }
    .upg-card.is-featured:hover {
        box-shadow: 0 30px 60px -15px rgba(245, 158, 11, 0.32);
    }

    .upg-badge {
        position: absolute;
        top: -14px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(90deg, #f59e0b 0%, #f97316 100%);
        color: white;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.5px;
        padding: 6px 16px;
        border-radius: 50px;
        box-shadow: 0 10px 22px -6px rgba(245, 158, 11, 0.55);
        text-transform: uppercase;
        white-space: nowrap;
    }
    .upg-badge-current {
        position: absolute;
        top: 14px;
        left: 14px;
        background: #4f46e5;
        color: white;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.5px;
        padding: 4px 12px;
        border-radius: 50px;
        text-transform: uppercase;
        z-index: 1;
    }

    .upg-price {
        font-size: clamp(2.25rem, 7vw, 3.25rem);
        font-weight: 900;
        line-height: 1;
        letter-spacing: -0.04em;
    }
    .upg-price-currency {
        font-size: clamp(0.9rem, 2.2vw, 1rem);
        font-weight: 700;
    }
    .upg-feature-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .upg-feature-list li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 8px 0;
        font-size: clamp(13px, 2.6vw, 14px);
        font-weight: 600;
        line-height: 1.5;
    }
    .upg-feature-list li .upg-check {
        flex-shrink: 0;
        margin-top: 2px;
    }
    .upg-feature-list li.is-locked {
        opacity: 0.5;
        text-decoration: line-through;
        text-decoration-color: rgba(148, 163, 184, 0.5);
        text-decoration-thickness: 1.5px;
    }

    .upg-cta-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 14px 22px;
        border-radius: 16px;
        font-weight: 800;
        font-size: 15px;
        text-decoration: none;
        transition: transform .25s, box-shadow .25s, background .25s;
    }
    .upg-cta-btn:hover {
        transform: translateY(-2px);
        text-decoration: none;
    }
    .upg-cta-btn[disabled] {
        cursor: not-allowed;
        opacity: 0.55;
    }
    .upg-cta-featured {
        background: linear-gradient(90deg, #f59e0b 0%, #f97316 100%);
        color: white;
        box-shadow: 0 14px 30px -10px rgba(245, 158, 11, 0.55);
    }
    .upg-cta-featured:hover {
        box-shadow: 0 20px 38px -10px rgba(245, 158, 11, 0.7);
        color: white;
    }
    .upg-cta-light {
        background: #0f172a;
        color: white;
        box-shadow: 0 8px 18px -6px rgba(15, 23, 42, 0.25);
    }
    .upg-cta-light:hover {
        background: #1e293b;
        color: white;
    }

    /* Contact card */
    .upg-contact-card {
        position: relative;
        overflow: hidden;
        border-radius: var(--upg-radius);
        background: white;
        border: 1px solid #e2e8f0;
        box-shadow: 0 15px 40px -15px rgba(15, 23, 42, 0.1);
    }
    .upg-contact-card::before {
        content: "";
        position: absolute;
        bottom: -60px;
        right: -60px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.12), transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    /* Section entrance animation */
    .upg-anim {
        animation: upgFadeUp 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    .upg-anim:nth-child(1) { animation-delay: 0.05s; }
    .upg-anim:nth-child(2) { animation-delay: 0.12s; }
    .upg-anim:nth-child(3) { animation-delay: 0.19s; }
    @keyframes upgFadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Tab divider responsive */
    .upg-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(148, 163, 184, 0.3), transparent);
        margin: 20px 0;
    }
    .upg-card.is-featured .upg-divider {
        background: linear-gradient(90deg, transparent, rgba(245, 158, 11, 0.35), transparent);
    }

    @media (max-width: 640px) {
        .upg-hero { padding: 28px 22px !important; }
        .upg-card { padding: 24px 20px !important; }
        .upg-badge { font-size: 10px; padding: 5px 14px; }
    }
</style>
@endpush

@section('content')
<div class="upg-wrap max-w-6xl mx-auto pb-6">

    {{-- ============================================================ --}}
    {{-- HERO HEADER — Dark accent banner with brand info --}}
    {{-- ============================================================ --}}
    <section class="upg-hero p-6 sm:p-8 md:p-10 mb-6 sm:mb-10 upg-anim">
        <div class="relative z-10 flex flex-col md:flex-row items-center gap-5 md:gap-7 text-center md:text-start">
            <div class="upg-hero-icon">
                <i class="fas fa-crown"></i>
            </div>
            <div class="flex-1 min-w-0">
                <span class="inline-block text-amber-400 text-[11px] sm:text-xs font-black tracking-widest uppercase mb-2">
                    Kayyan SaaS
                </span>
                <h1 class="text-white text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black mb-2 sm:mb-3 leading-tight font-cairo">
                    @if($reason)
                        هذه الميزة غير متاحة في باقتك الحالية!
                    @else
                        اختر الباقة المناسبة لنمو أعمالك
                    @endif
                </h1>
                @if($reason)
                    <p class="text-slate-300 text-sm sm:text-base mb-1">لقد حاولت الوصول إلى:</p>
                    <span class="inline-block text-amber-400 font-bold bg-amber-500/10 px-3 py-1.5 rounded-lg border border-amber-500/20 text-sm">
                        {{ $reason }}
                    </span>
                    <p class="text-slate-300 text-xs sm:text-sm mt-3 leading-relaxed">
                        أنت بحاجة إلى ترقية باقتك للوصول إلى هذه الخاصية والاستفادة من الميزات المتقدمة للنظام.
                    </p>
                @else
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-2xl">
                        قارن بين باقات نظام كيان واختر ما يلبي طموحاتك. كل الباقات تشمل تجربة مجانية لمدة 14 يوم، بدون بطاقة ائتمان.
                    </p>
                @endif
            </div>
        </div>

        {{-- Decorative floating dot --}}
        <div class="absolute -top-12 -left-12 w-40 h-40 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -right-16 w-48 h-48 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>
    </section>

    {{-- ============================================================ --}}
    {{-- PRICING CARDS — Light cards + dark accent on featured (Pro) --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 sm:gap-6 md:gap-7 mb-8 sm:mb-12 items-stretch">

        {{-- ============================================ --}}
        {{-- STARTER --}}
        {{-- ============================================ --}}
        <div class="upg-card bg-white border-2 {{ $currentPlanId === 'starter' ? 'border-indigo-500 ring-4 ring-indigo-500/10' : 'border-slate-100 hover:border-indigo-300' }} p-5 sm:p-7 md:p-8 flex flex-col upg-anim shadow-xl shadow-slate-200/40">
            @if($currentPlanId === 'starter')
                <span class="upg-badge-current">باقتك الحالية</span>
            @endif

            <div class="mb-1">
                <span class="text-indigo-600 text-[11px] sm:text-xs font-black tracking-widest uppercase">الباقة الأساسية</span>
            </div>
            <h3 class="text-slate-900 text-2xl sm:text-3xl font-black mt-2 font-cairo">Starter</h3>
            <p class="text-slate-400 text-xs sm:text-sm mt-2 leading-relaxed min-h-[36px]">
                باقة المبتدئين - تشمل الكاشير والمشتريات الأساسية
            </p>

            <div class="my-5 sm:my-6 flex items-baseline gap-2">
                <span class="upg-price text-slate-900">99</span>
                <span class="upg-price-currency text-slate-400">ريال / شهرياً</span>
            </div>

            <div class="upg-divider"></div>

            <ul class="upg-feature-list flex-1 mb-2">
                <li>
                    <i class="fas fa-check-circle text-emerald-500 upg-check"></i>
                    <span class="text-slate-700">نقاط البيع شاشة الكاشير (POS)</span>
                </li>
                <li>
                    <i class="fas fa-check-circle text-emerald-500 upg-check"></i>
                    <span class="text-slate-700">فواتير المبيعات والمشتريات</span>
                </li>
                <li class="is-locked">
                    <i class="fas fa-lock text-slate-300 upg-check"></i>
                    <span class="text-slate-500">تعدد المستودعات والجرد</span>
                </li>
                <li class="is-locked">
                    <i class="fas fa-lock text-slate-300 upg-check"></i>
                    <span class="text-slate-500">إدارة عمليات التصنيع التلقائية</span>
                </li>
                <li class="is-locked">
                    <i class="fas fa-lock text-slate-300 upg-check"></i>
                    <span class="text-slate-500">الحسابات والمصروفات المتقدمة</span>
                </li>
                <li class="is-locked">
                    <i class="fas fa-lock text-slate-300 upg-check"></i>
                    <span class="text-slate-500">التقارير المالية والأرباح والخسائر</span>
                </li>
            </ul>

            <div class="mt-6 sm:mt-8">
                @if($currentPlanId === 'starter')
                    <button class="upg-cta-btn bg-slate-100 text-slate-400" disabled>نشطة حالياً</button>
                @else
                    <a href="https://wa.me/966500000000?text={{ urlencode('أريد ترقية باقة متجري في نظام كيان إلى باقة Starter') }}" target="_blank" rel="noopener"
                       class="upg-cta-btn upg-cta-light">
                        <i class="fas fa-arrow-up text-xs"></i>
                        <span>طلب الباقة</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- PRO (FEATURED — dark accent) --}}
        {{-- ============================================ --}}
        <div class="upg-card is-featured p-5 sm:p-7 md:p-8 flex flex-col upg-anim">
            <span class="upg-badge">الأكثر طلباً ⚡</span>

            <div class="mb-1">
                <span class="text-amber-500 text-[11px] sm:text-xs font-black tracking-widest uppercase">باقة المحترفين</span>
            </div>
            <h3 class="text-white text-2xl sm:text-3xl font-black mt-2 font-cairo">Pro</h3>
            <p class="text-slate-400 text-xs sm:text-sm mt-2 leading-relaxed min-h-[36px]">
                إدارة كاملة للمخازن، عمليات التصنيع، والحسابات بحدود مرنة
            </p>

            <div class="my-5 sm:my-6 flex items-baseline gap-2">
                <span class="upg-price text-white">299</span>
                <span class="upg-price-currency text-slate-400">ريال / شهرياً</span>
            </div>

            <div class="upg-divider"></div>

            <ul class="upg-feature-list flex-1 mb-2">
                <li>
                    <i class="fas fa-check-circle text-amber-500 upg-check"></i>
                    <span class="text-slate-200">نقاط البيع شاشة الكاشير (POS)</span>
                </li>
                <li>
                    <i class="fas fa-check-circle text-amber-500 upg-check"></i>
                    <span class="text-slate-200">فواتير المبيعات والمشتريات المتقدمة</span>
                </li>
                <li>
                    <i class="fas fa-check-circle text-amber-500 upg-check"></i>
                    <span class="text-slate-200">تعدد المستودعات (حتى 5 مستودعات)</span>
                </li>
                <li>
                    <i class="fas fa-check-circle text-amber-500 upg-check"></i>
                    <span class="text-slate-200">إدارة عمليات التصنيع وتكاليف الخامات</span>
                </li>
                <li>
                    <i class="fas fa-check-circle text-amber-500 upg-check"></i>
                    <span class="text-slate-200">الحسابات والمصروفات والخزينة</span>
                </li>
                <li>
                    <i class="fas fa-check-circle text-amber-500 upg-check"></i>
                    <span class="text-slate-200">التقرير المالي والأرباح والخسائر</span>
                </li>
            </ul>

            <div class="mt-6 sm:mt-8">
                @if($currentPlanId === 'pro')
                    <button class="upg-cta-btn bg-slate-800 text-slate-500" disabled>نشطة حالياً</button>
                @else
                    <a href="https://wa.me/966500000000?text={{ urlencode('أريد ترقية باقة متجري في نظام كيان إلى باقة Pro الاحترافية') }}" target="_blank" rel="noopener"
                       class="upg-cta-btn upg-cta-featured">
                        <i class="fas fa-bolt text-xs"></i>
                        <span>ترقية الباقة الآن</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- ENTERPRISE --}}
        {{-- ============================================ --}}
        <div class="upg-card bg-white border-2 {{ $currentPlanId === 'enterprise' ? 'border-indigo-500 ring-4 ring-indigo-500/10' : 'border-slate-100 hover:border-teal-300' }} p-5 sm:p-7 md:p-8 flex flex-col upg-anim shadow-xl shadow-slate-200/40">
            @if($currentPlanId === 'enterprise')
                <span class="upg-badge-current">باقتك الحالية</span>
            @endif

            <div class="mb-1">
                <span class="text-teal-600 text-[11px] sm:text-xs font-black tracking-widest uppercase">باقة الشركات والنمو</span>
            </div>
            <h3 class="text-slate-900 text-2xl sm:text-3xl font-black mt-2 font-cairo">Enterprise</h3>
            <p class="text-slate-400 text-xs sm:text-sm mt-2 leading-relaxed min-h-[36px]">
                الباقة اللامحدودة لكافة الميزات والخصائص دون أي قيود
            </p>

            <div class="my-5 sm:my-6 flex items-baseline gap-2">
                <span class="upg-price text-slate-900">599</span>
                <span class="upg-price-currency text-slate-400">ريال / شهرياً</span>
            </div>

            <div class="upg-divider"></div>

            <ul class="upg-feature-list flex-1 mb-2">
                <li>
                    <i class="fas fa-check-circle text-emerald-500 upg-check"></i>
                    <span class="text-slate-700">كل ما تشمله الباقة الاحترافية Pro</span>
                </li>
                <li>
                    <i class="fas fa-check-circle text-emerald-500 upg-check"></i>
                    <span class="text-slate-700">عدد مستودعات وتصنيع لا نهائي</span>
                </li>
                <li>
                    <i class="fas fa-check-circle text-emerald-500 upg-check"></i>
                    <span class="text-slate-700">مزامنة جرد المستودعات المتقدمة</span>
                </li>
                <li>
                    <i class="fas fa-check-circle text-emerald-500 upg-check"></i>
                    <span class="text-slate-700">تقارير حركة الخشب والتحليل الدقيق</span>
                </li>
                <li>
                    <i class="fas fa-check-circle text-emerald-500 upg-check"></i>
                    <span class="text-slate-700">سجل عمليات وأمان متكامل للمستخدمين</span>
                </li>
                <li>
                    <i class="fas fa-check-circle text-emerald-500 upg-check"></i>
                    <span class="text-slate-700">دعم فني خاص على مدار الساعة</span>
                </li>
            </ul>

            <div class="mt-6 sm:mt-8">
                @if($currentPlanId === 'enterprise')
                    <button class="upg-cta-btn bg-slate-100 text-slate-400" disabled>نشطة حالياً</button>
                @else
                    <a href="https://wa.me/966500000000?text={{ urlencode('أريد ترقية باقة متجري في نظام كيان إلى باقة Enterprise الشاملة') }}" target="_blank" rel="noopener"
                       class="upg-cta-btn upg-cta-light">
                        <i class="fas fa-headset text-xs"></i>
                        <span>تواصل للترقية</span>
                    </a>
                @endif
            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- CONTACT CARD --}}
    {{-- ============================================================ --}}
    <div class="upg-contact-card p-5 sm:p-7 md:p-8 text-center max-w-3xl mx-auto upg-anim">
        <div class="relative z-10">
            <span class="inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 bg-emerald-100 text-emerald-600 rounded-2xl mb-4 text-2xl sm:text-3xl">
                <i class="fas fa-headset"></i>
            </span>
            <h3 class="text-slate-800 text-lg sm:text-xl md:text-2xl font-black mb-2 sm:mb-3 font-cairo">
                هل ترغب بمميزات مخصصة لشركتك؟
            </h3>
            <p class="text-slate-500 text-xs sm:text-sm mb-5 sm:mb-6 max-w-lg mx-auto leading-relaxed">
                فريق الدعم الفني جاهز لمساعدتك في تخصيص باقة تناسب حجم متجرك وتطلعاتك، تواصل معنا الآن عبر قنوات الاتصال المباشرة.
            </p>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3 sm:gap-4">
                <a href="https://wa.me/966500000000?text={{ urlencode('مرحباً، أريد الاستفسار عن تفاصيل ترقية الباقة لمتجري في نظام كيان') }}" target="_blank" rel="noopener"
                   class="upg-cta-btn sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-600/25">
                    <i class="fab fa-whatsapp text-base"></i>
                    <span>تواصل معنا عبر واتساب</span>
                </a>

                <a href="tel:+966500000000"
                   class="upg-cta-btn sm:w-auto bg-white hover:bg-slate-50 text-slate-700 border-2 border-slate-200 hover:border-slate-300">
                    <i class="fas fa-phone-alt text-sm"></i>
                    <span>اتصال مباشر بالدعم الفني</span>
                </a>
            </div>

            <p class="text-slate-400 text-[11px] sm:text-xs mt-5 sm:mt-6">
                <i class="fas fa-shield-halved text-emerald-500"></i>
                ضمان استرداد المبلغ خلال 30 يوم — بدون أي أسئلة
            </p>
        </div>
    </div>

</div>
@endsection