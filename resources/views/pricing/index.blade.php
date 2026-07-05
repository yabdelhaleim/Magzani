{{-- Extends the standalone pricing layout (NOT layouts.app — this is a public marketing page) --}}
@extends('pricing.layout')

@section('title', config('pricing.seo_title'))
@section('description', config('pricing.seo_description'))
@section('keywords', config('pricing.seo_keywords'))

@php
    // Build structured data OFFLINE so we don't have to fight Blade parsing of
    // complex Blade directives inside a <script type="application/ld+json"> block.
    $pricingLd = [
        '@context' => 'https://schema.org',
        '@type'    => 'ProductCollection',
        'name'     => config('pricing.brand_name') . ' - باقات الأسعار',
        'description' => config('pricing.seo_description'),
        'brand'    => ['@type' => 'Brand', 'name' => config('pricing.brand_name')],
        'offers'   => $plans->map(fn ($p) => [
            '@type'         => 'Offer',
            'name'          => $p->name,
            'description'   => $p->description,
            'price'         => number_format((float) $p->price, 2, '.', ''),
            'priceCurrency' => 'SAR',
            'category'      => 'subscription',
            'availability'  => 'https://schema.org/InStock',
        ])->values()->all(),
    ];
@endphp

@push('json-ld')
<script type="application/ld+json">{!! json_encode($pricingLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endpush

@section('content')

{{-- ===================== SECTION 1: STICKY TOP BAR ===================== --}}
<header class="sticky top-0 z-40 glass-panel border-b border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

        <a href="/pricing" class="flex items-center gap-3 group">
            <svg width="32" height="32" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-label="{{ config('pricing.brand_name') }}">
                <rect x="5" y="5" width="90" height="90" rx="26" fill="url(#kayanBgSmall)" />
                <path d="M 34 26 L 46 18 L 46 71 L 34 79 Z" fill="url(#kLeftSmall)" filter="url(#glowSmall)" />
                <path d="M 46 44 L 68 20 L 78 20 L 53 47 Z" fill="url(#kRightTopSmall)" />
                <path d="M 50 44 L 75 72 L 64 72 L 46 51 Z" fill="url(#kRightBottomSmall)" />
                <path d="M 46 44 L 56 48 L 46 52 Z" fill="#e0f2fe" opacity="0.9" />
            </svg>
            <span class="text-xl font-black bg-gradient-to-l from-blue-400 to-cyan-400 bg-clip-text text-transparent">{{ config('pricing.brand_name') }}</span>
        </a>

        <nav class="hidden md:flex items-center gap-7 text-sm font-semibold text-slate-300">
            <a href="#features"   class="hover:text-white transition">المميزات</a>
            <a href="#pricing"    class="hover:text-white transition">الأسعار</a>
            <a href="#compare"    class="hover:text-white transition">المقارنة</a>
            <a href="#faq"        class="hover:text-white transition">الأسئلة الشائعة</a>
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ $signupUrl }}" class="k-btn k-btn-primary text-xs">
                <span>ابدأ تجربتك</span>
                <i class="fas fa-arrow-left text-[10px]"></i>
            </a>
        </div>
    </div>
</header>

{{-- ===================== SECTION 2: HERO ===================== --}}
<section class="relative py-20 lg:py-32 overflow-hidden">
    <div class="absolute top-20 right-10 w-72 h-72 bg-indigo-500/20 rounded-full blur-[100px]"></div>
    <div class="absolute bottom-20 left-10 w-72 h-72 bg-purple-500/20 rounded-full blur-[100px]"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

        <span class="k-eyebrow">
            <i class="fas fa-rocket ml-2"></i>
            نظام SaaS متعدد المستأجرين
        </span>

        <h1 class="k-h text-5xl sm:text-6xl lg:text-7xl mb-6 bg-gradient-to-l from-white via-slate-100 to-slate-300 bg-clip-text text-transparent">
            نظام إدارة الأعمال والمخازن الذكي
        </h1>

        <p class="text-lg sm:text-xl text-slate-300 max-w-3xl mx-auto mb-10 leading-relaxed">
            منصة متكاملة لإدارة <span class="text-amber-400 font-bold">نقاط البيع</span>، و<span class="text-emerald-400 font-bold">المخزون</span>، و<span class="text-cyan-400 font-bold">المحاسبة</span>، و<span class="text-purple-400 font-bold">التصنيع</span> - كل ما يحتاجه عملك في مكان واحد، بتجربة استخدام عربية أصيلة.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
            <a href="{{ $signupUrl }}" class="k-btn k-btn-primary text-base px-8 py-4 w-full sm:w-auto">
                <span>ابدأ تجربتك المجانية</span>
                <i class="fas fa-arrow-left"></i>
            </a>
            <a href="{{ $demoUrl }}" target="_blank" rel="noopener" class="k-btn k-btn-ghost text-base px-8 py-4 w-full sm:w-auto">
                <i class="fas fa-calendar-check"></i>
                <span>احجز ديمو</span>
            </a>
        </div>

        <div class="flex flex-wrap justify-center gap-x-8 gap-y-3 text-sm text-slate-400">
            <span class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-400"></i> 14 يوم تجربة مجانية</span>
            <span class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-400"></i> بدون بطاقة ائتمان</span>
            <span class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-400"></i> دعم فني عربي</span>
            <span class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-400"></i> إلغاء في أي وقت</span>
        </div>
    </div>
</section>

{{-- ===================== SECTION 3: TRUST STRIP ===================== --}}
<section class="relative py-12 border-y border-white/5 bg-gradient-to-b from-transparent via-indigo-950/10 to-transparent">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            @php
                $stats = [
                    ['icon' => 'fa-building',       'value' => '+500',   'label' => 'شركة ومؤسسة'],
                    ['icon' => 'fa-users',          'value' => '+2,400', 'label' => 'مستخدم نشط'],
                    ['icon' => 'fa-receipt',        'value' => '+1.2M',  'label' => 'فاتورة شهرياً'],
                    ['icon' => 'fa-shield-halved',  'value' => '99.9%',  'label' => 'وقت تشغيل مضمون'],
                ];
            @endphp
            @foreach($stats as $s)
                <div class="glass-card rounded-2xl p-6">
                    <i class="fas {{ $s['icon'] }} text-3xl text-indigo-400 mb-3"></i>
                    <div class="text-3xl font-black text-white">{{ $s['value'] }}</div>
                    <div class="text-sm text-slate-400 mt-1">{{ $s['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== SECTION 4: FEATURES GRID ===================== --}}
<section id="features" class="py-20 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="k-eyebrow">المميزات</span>
            <h2 class="k-h text-4xl sm:text-5xl mb-4">كل ما يحتاجه عملك في مكان واحد</h2>
            <p class="text-slate-400 text-lg max-w-2xl mx-auto">مميزات متكاملة تغطي كل جوانب إدارة الأعمال من الكاشير إلى المحاسبة والتصنيع</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $features = [
                    ['icon' => 'fa-cash-register', 'color' => 'amber',   'title' => 'نقاط بيع متقدمة',  'desc' => 'شاشة كاشير احترافية مع إدارة الورديات والتقفيل النقدي'],
                    ['icon' => 'fa-boxes-stacked', 'color' => 'cyan',    'title' => 'إدارة المخازن',     'desc' => 'تتبع المخزون في الوقت الفعلي عبر مستودعات متعددة'],
                    ['icon' => 'fa-industry',      'color' => 'purple',  'title' => 'التصنيع والأوامر',  'desc' => 'إدارة أوامر الإنتاج وقوائم المواد (BOM) والتكاليف'],
                    ['icon' => 'fa-calculator',    'color' => 'emerald', 'title' => 'محاسبة متكاملة',    'desc' => 'شجرة حسابات، قيود يومية، ميزان مراجعة، وقوائم مالية'],
                    ['icon' => 'fa-chart-pie',     'color' => 'rose',    'title' => 'تقارير ذكية',       'desc' => 'تحليلات مالية ومخزنية بصرية تساعدك في اتخاذ القرار'],
                    ['icon' => 'fa-users-gear',    'color' => 'indigo',  'title' => 'صلاحيات متقدمة',    'desc' => 'إدارة المستخدمين والأدوار بصلاحيات دقيقة لكل قسم'],
                    ['icon' => 'fa-warehouse',     'color' => 'sky',     'title' => 'مخزون الخشب',       'desc' => 'تتبع مخزون الخشب وصرفه لأوامر التصنيع تلقائياً'],
                    ['icon' => 'fa-headset',       'color' => 'amber',   'title' => 'دعم فني 24/7',      'desc' => 'فريق دعم عربي متخصص لمساعدتك في أي وقت'],
                ];
            @endphp
            @foreach($features as $f)
                <div class="glass-card rounded-2xl p-6 hover:border-{{ $f['color'] }}-500/40">
                    <div class="w-12 h-12 rounded-xl bg-{{ $f['color'] }}-500/10 border border-{{ $f['color'] }}-500/20 flex items-center justify-center mb-4">
                        <i class="fas {{ $f['icon'] }} text-{{ $f['color'] }}-400 text-xl"></i>
                    </div>
                    <h3 class="font-black text-lg text-white mb-2">{{ $f['title'] }}</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">{{ $f['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== SECTION 5: PRICING CARDS ===================== --}}
<section id="pricing" class="py-20 lg:py-28 relative">
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-[120px]"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-[120px]"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="k-eyebrow">الأسعار</span>
            <h2 class="k-h text-4xl sm:text-5xl mb-4">باقات مرنة تنمو معك</h2>
            <p class="text-slate-400 text-lg max-w-2xl mx-auto">اختر الباقة المناسبة لحجم عملك. كل الباقات تشمل تجربة مجانية لمدة 14 يوم</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch max-w-6xl mx-auto">
            @foreach($plans as $plan)
                @php
                    $isFeatured = $plan->slug === 'pro';
                    $demoQuery = http_build_query(['utm_source' => 'pricing', 'utm_medium' => 'cta', 'utm_campaign' => $plan->slug]);
                    $demoLink  = $demoUrl . (str_contains($demoUrl, '?') ? '&' : '?') . $demoQuery;
                @endphp

                <article class="k-card {{ $isFeatured ? 'k-card--featured' : '' }} flex flex-col">

                    @if($isFeatured)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 z-10">
                            <span class="inline-flex items-center gap-1 px-4 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-gradient-to-l from-amber-500 to-rose-500 text-white pulse-glow">
                                <i class="fas fa-bolt"></i>
                                الأكثر طلباً
                            </span>
                        </div>
                    @endif

                    <div class="mb-6">
                        <span class="text-xs font-extrabold tracking-widest uppercase {{ $isFeatured ? 'text-amber-400' : 'text-indigo-400' }}">
                            {{ $plan->slug === 'starter' ? 'الباقة الأساسية' : ($plan->slug === 'pro' ? 'الباقة الاحترافية' : 'الباقة المؤسسية') }}
                        </span>
                        <h3 class="k-h text-3xl mt-2 text-white">{{ $plan->name }}</h3>
                        <p class="text-sm text-slate-400 mt-2 min-h-[44px]">{{ $plan->description }}</p>
                    </div>

                    <div class="mb-6 pb-6 border-b {{ $isFeatured ? 'border-amber-500/20' : 'border-white/10' }}">
                        <div class="flex items-baseline gap-2">
                            @if((float) $plan->price == 0)
                                <span class="text-4xl font-black text-white">مخصص</span>
                            @else
                                <span class="text-6xl font-black text-white">{{ number_format((float) $plan->price, 0) }}</span>
                                <span class="text-sm text-slate-400 font-bold">ريال / شهرياً</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-2">
                            <i class="fas fa-shield-halved ml-1"></i>
                            بدون رسوم إعداد • إلغاء في أي وقت
                        </p>
                    </div>

                    <ul class="space-y-3 mb-8 flex-grow">
                        @foreach($plan->value_props ?? [] as $prop)
                            <li class="flex items-start gap-3 text-sm text-slate-300">
                                <i class="fas fa-check-circle {{ $isFeatured ? 'text-amber-400' : 'text-emerald-400' }} text-base shrink-0 mt-0.5"></i>
                                <span>{{ $prop }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="space-y-3 mt-auto">
                        <a href="{{ $demoLink }}" target="_blank" rel="noopener"
                           class="k-btn {{ $isFeatured ? 'k-btn-featured' : 'k-btn-primary' }} w-full">
                            <i class="fas fa-calendar-check"></i>
                            <span>احجز ديمو مجاني</span>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <p class="text-center text-slate-400 text-sm mt-10">
            <i class="fas fa-lock text-emerald-400 ml-1"></i>
            جميع الباقات تشمل تشفير SSL 256-bit، نسخ احتياطية يومية، وفترة تجربة مجانية 14 يوماً
        </p>
    </div>
</section>

{{-- ===================== SECTION 6: COMPARISON TABLE ===================== --}}
<section id="compare" class="py-20 lg:py-24 bg-gradient-to-b from-transparent via-slate-900/40 to-transparent">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="k-eyebrow">المقارنة</span>
            <h2 class="k-h text-4xl sm:text-5xl mb-4">قارن بين الباقات</h2>
            <p class="text-slate-400 text-lg max-w-2xl mx-auto">كل التفاصيل تحت أمرك</p>
        </div>

        <div class="hidden md:block glass-card rounded-3xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-900/60">
                    <tr>
                        <th class="text-right px-6 py-5 font-black text-white text-base">الميزة</th>
                        @foreach($plans as $plan)
                            <th class="text-center px-6 py-5 font-black {{ $plan->slug === 'pro' ? 'text-amber-400' : 'text-white' }} text-base">{{ $plan->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @php
                        $featureLabels = [
                            'pos'                 => 'نقاط البيع (POS) مع الكاشير',
                            'purchase'            => 'فواتير المبيعات والمشتريات',
                            'manufacturing'       => 'إدارة التصنيع وأوامر الإنتاج',
                            'multi_warehouse'     => 'تعدد المستودعات والتحويلات',
                            'accounting'          => 'الحسابات والمصروفات',
                            'accounting_advanced' => 'المحاسبة المتقدمة (شجرة الحسابات والقيود)',
                            'stock_count'         => 'الجرد الدوري وحركات المخزون',
                            'reports_advanced'    => 'التقارير المالية وقوائم الدخل',
                        ];
                    @endphp
                    @foreach($featureLabels as $key => $label)
                        @php $anyHave = false; @endphp
                        @foreach($plans as $plan)
                            @php $has = $plan->featuresList->firstWhere('feature_key', $key); @endphp
                            @if($has && $has->is_enabled) @php $anyHave = true; @endphp @endif
                        @endforeach
                        @if($anyHave)
                            <tr class="hover:bg-white/[.02] transition">
                                <td class="text-right px-6 py-4 font-bold text-slate-200">{{ $label }}</td>
                                @foreach($plans as $plan)
                                    @php $has = $plan->featuresList->firstWhere('feature_key', $key); @endphp
                                    <td class="text-center px-6 py-4">
                                        @if($has && $has->is_enabled)
                                            <i class="fas fa-check-circle text-emerald-400"></i>
                                            @if($has->limit_value)<div class="text-[10px] text-slate-500 mt-1">حتى {{ $has->limit_value }}</div>@endif
                                        @else
                                            <i class="fas fa-times-circle text-slate-600"></i>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-3">
            @foreach($plans as $plan)
                <details class="glass-card rounded-2xl">
                    <summary class="px-5 py-4 flex items-center justify-between cursor-pointer">
                        <span class="font-black text-white">{{ $plan->name }}</span>
                        <span class="text-xs text-slate-400">{{ $plan->featuresList->count() }} ميزة</span>
                    </summary>
                    <div class="px-5 pb-4 space-y-2">
                        @foreach($featureLabels as $key => $label)
                            @php $has = $plan->featuresList->firstWhere('feature_key', $key); @endphp
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-300">{{ $label }}</span>
                                @if($has && $has->is_enabled)
                                    <i class="fas fa-check-circle text-emerald-400"></i>
                                @else
                                    <i class="fas fa-times-circle text-slate-600"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== SECTION 7: ADD-ONS ===================== --}}
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="k-eyebrow">خدمات إضافية</span>
            <h2 class="k-h text-4xl sm:text-5xl mb-4">إضافات احترافية عند الطلب</h2>
        </div>

        @php
            $addons = [
                ['icon' => 'fa-chalkboard-user', 'color' => 'indigo',  'title' => 'تدريب مخصص',   'desc' => 'جلسات تدريب مباشرة لفريقك عن بعد أو في مقر الشركة'],
                ['icon' => 'fa-crown',            'color' => 'amber',   'title' => 'دعم VIP',       'desc' => 'دعم فني على مدار الساعة مع مهندس حساب مخصص'],
                ['icon' => 'fa-puzzle-piece',     'color' => 'cyan',    'title' => 'تخصيص وتطوير', 'desc' => 'تطوير وحدات مخصصة تناسب طبيعة عملك الفريدة'],
                ['icon' => 'fa-truck-ramp-box',   'color' => 'emerald', 'title' => 'ترحيل بيانات',  'desc' => 'نستورد بياناتك من أي نظام قديم بدون فقدان أي معلومة'],
            ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($addons as $a)
                <div class="glass-card rounded-2xl p-6 text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-{{ $a['color'] }}-500/10 border border-{{ $a['color'] }}-500/20 flex items-center justify-center mb-4">
                        <i class="fas {{ $a['icon'] }} text-{{ $a['color'] }}-400 text-2xl"></i>
                    </div>
                    <h3 class="font-black text-lg text-white mb-2">{{ $a['title'] }}</h3>
                    <p class="text-sm text-slate-400">{{ $a['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== SECTION 8: FAQ ===================== --}}
<section id="faq" class="py-20 lg:py-24 bg-gradient-to-b from-transparent via-indigo-950/10 to-transparent">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="k-eyebrow">الأسئلة الشائعة</span>
            <h2 class="k-h text-4xl sm:text-5xl mb-4">إجابات على أهم الأسئلة</h2>
        </div>

        @php
            $faqs = [
                ['q' => 'هل في فترة تجربة مجانية؟', 'a' => 'نعم، كل الباقات تشمل 14 يوم تجربة مجانية بدون الحاجة لإدخال بطاقة ائتمان. تقدر تترقّى أو تلغي في أي وقت خلال هذه الفترة بدون أي التزام.'],
                ['q' => 'هل أقدر أغير الباقة لاحقاً؟', 'a' => 'بالتأكيد! تقدر ترقية باقتك أو تخفيضها في أي وقت من داخل لوحة التحكم. التغيير يطبَّق فوراً على الفاتورة القادمة.'],
                ['q' => 'كم عدد المستخدمين في كل باقة؟', 'a' => 'باقة Starter: حتى 3 مستخدمين. باقة Pro: حتى 15 مستخدم. باقة Enterprise: مستخدمين غير محدود. يمكنك إضافة مستخدمين إضافيين في أي باقة بتكلفة بسيطة.'],
                ['q' => 'هل الدعم الفني بالعربية؟', 'a' => 'نعم، فريق الدعم بالكامل من المتحدثين الأصليين للعربية، متاح من الأحد للخميس 9ص-9م، وباقات Enterprise تشمل دعم 24/7.'],
                ['q' => 'كيف يتم ترحيل البيانات من نظام قديم؟', 'a' => 'نوفر خدمة ترحيل بيانات احترافية من Excel أو من أي نظام ERP/POS سابق. فريقنا يساعدك في استيراد المنتجات، العملاء، الفواتير، والأرصدة الافتتاحية بدون أي فقدان.'],
                ['q' => 'هل البيانات آمنة؟', 'a' => 'نعم، نستخدم تشفير SSL 256-bit، نسخ احتياطية يومية على خوادم متفرقة جغرافياً، ونلتزم بمعايير ISO 27001. مركز البيانات الرئيسي في أوروبا مع توزيع جغرافي.'],
            ];
        @endphp

        <div class="space-y-3">
            @foreach($faqs as $i => $faq)
                <details class="faq" {{ $i === 0 ? 'open' : '' }}>
                    <summary>{{ $faq['q'] }}</summary>
                    <div class="faq-body">{{ $faq['a'] }}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== SECTION 9: FINAL CTA + FOOTER ===================== --}}
<section class="py-20 lg:py-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/40 via-purple-900/30 to-cyan-900/30"></div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/20 rounded-full blur-[120px]"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-500/20 rounded-full blur-[120px]"></div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="k-eyebrow">جاهز للبدء؟</span>
        <h2 class="k-h text-4xl sm:text-5xl mb-5">انضم لمئات الشركات التي تثق بكيان</h2>
        <p class="text-slate-300 text-lg mb-10 max-w-2xl mx-auto">ابدأ تجربتك المجانية اليوم. بدون بطاقة ائتمان، بدون التزامات.</p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-10">
            <a href="{{ $signupUrl }}" class="k-btn k-btn-primary text-base px-8 py-4 w-full sm:w-auto">
                <i class="fas fa-rocket"></i>
                <span>ابدأ تجربتك المجانية الآن</span>
            </a>
        </div>

        <div class="flex flex-wrap justify-center gap-x-6 gap-y-2 text-xs text-slate-400">
            <span><i class="fas fa-shield-halved text-emerald-400 ml-1"></i> SSL آمن</span>
            <span><i class="fas fa-clock text-amber-400 ml-1"></i> إعداد في 5 دقائق</span>
            <span><i class="fas fa-undo text-cyan-400 ml-1"></i> ضمان استرداد المبلغ 30 يوم</span>
        </div>
    </div>
</section>

<footer class="border-t border-white/5 bg-slate-950/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-10 mb-10">

            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center gap-3 mb-4">
                    <svg width="32" height="32" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <rect x="5" y="5" width="90" height="90" rx="26" fill="url(#kayanBgSmall)" />
                        <path d="M 34 26 L 46 18 L 46 71 L 34 79 Z" fill="url(#kLeftSmall)" filter="url(#glowSmall)" />
                        <path d="M 46 44 L 68 20 L 78 20 L 53 47 Z" fill="url(#kRightTopSmall)" />
                        <path d="M 50 44 L 75 72 L 64 72 L 46 51 Z" fill="url(#kRightBottomSmall)" />
                        <path d="M 46 44 L 56 48 L 46 52 Z" fill="#e0f2fe" opacity="0.9" />
                    </svg>
                    <span class="text-lg font-black bg-gradient-to-l from-blue-400 to-cyan-400 bg-clip-text text-transparent">{{ config('pricing.brand_name') }}</span>
                </div>
                <p class="text-sm text-slate-400 leading-relaxed">{{ config('pricing.brand_tagline') }}.</p>

                <div class="flex gap-3 mt-5">
                    <a href="#" class="w-9 h-9 rounded-lg glass-card flex items-center justify-center hover:bg-indigo-500/20" aria-label="Twitter"><i class="fab fa-x-twitter text-slate-300"></i></a>
                    <a href="https://calendar.google.com/calendar/appointments/schedules" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg glass-card flex items-center justify-center hover:bg-cyan-500/20" aria-label="Booking"><i class="fas fa-calendar-check text-slate-300"></i></a>
                </div>
            </div>

            <div>
                <h4 class="font-black text-white mb-4">المنتج</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="#features" class="hover:text-white transition">المميزات</a></li>
                    <li><a href="#pricing" class="hover:text-white transition">الأسعار</a></li>
                    <li><a href="#faq" class="hover:text-white transition">الأسئلة الشائعة</a></li>
                    <li><a href="{{ $signupUrl }}" class="hover:text-white transition">ابدأ مجاناً</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-black text-white mb-4">الشركة</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="#" class="hover:text-white transition">من نحن</a></li>
                    <li><a href="#" class="hover:text-white transition">المدونة</a></li>
                    <li><a href="#" class="hover:text-white transition">الوظائف</a></li>
                    <li><a href="mailto:info@kayyan.com" class="hover:text-white transition">تواصل معنا</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-black text-white mb-4">قانوني</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="#" class="hover:text-white transition">سياسة الخصوصية</a></li>
                    <li><a href="#" class="hover:text-white transition">شروط الاستخدام</a></li>
                    <li><a href="#" class="hover:text-white transition">اتفاقية مستوى الخدمة</a></li>
                    <li><a href="#" class="hover:text-white transition">الأمان والامتثال</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/5 pt-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500">
            <p>© {{ date('Y') }} {{ config('pricing.brand_name') }}. جميع الحقوق محفوظة.</p>
            <p>صُنع بـ <i class="fas fa-heart text-rose-500 mx-1"></i> في السعودية</p>
        </div>
    </div>
</footer>

@endsection
