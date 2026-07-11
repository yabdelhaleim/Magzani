{{-- Extends the standalone pricing layout --}}
@extends('pricing.layout')

@php
    $primaryCtaUrl   = $settings['hero']['primary_cta_url']   ?? $signupUrl;
    $secondaryCtaUrl = $settings['hero']['secondary_cta_url'] ?? $demoUrl;
    $cardCtaUrl      = $demoUrl;
@endphp

@section('title', config('pricing.seo_title'))
@section('description', config('pricing.seo_description'))
@section('keywords', config('pricing.seo_keywords'))

@php
    // JSON-LD for Google rich results
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

{{-- ===================== SECTION 1: STICKY TOP BAR (with mobile menu) ===================== --}}
<header class="k-topbar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">

        {{-- Brand — Kayyan SaaS Logo (the new design) --}}
        <a href="/pricing" class="flex items-center gap-2.5 group shrink-0" style="color:#f8fafc">
            {{-- Icon-only (K) for tight spaces (header, mobile) --}}
            <img src="/images/kayan-icon.svg"
                 alt="{{ config('pricing.brand_name') }}"
                 class="h-9 sm:h-10 w-auto transition-transform group-hover:scale-105"
                 width="40" height="40">

            {{-- Wordmark (K + كيان + tagline) for desktop --}}
            <div class="hidden sm:flex items-center gap-2">
                <span class="text-xl font-black tracking-tight leading-none">
                    {{ config('pricing.brand_name') }}
                </span>
            </div>
        </a>

        {{-- Desktop nav --}}
        <nav class="k-topbar-nav hidden md:flex items-center gap-7 text-sm font-bold text-slate-300">
            <a href="#features" class="hover:text-white transition-colors">المميزات</a>
            <a href="#pricing"  class="hover:text-white transition-colors">الأسعار</a>
            <a href="#compare"  class="hover:text-white transition-colors">المقارنة</a>
            <a href="#faq"      class="hover:text-white transition-colors">الأسئلة الشائعة</a>
        </nav>

        {{-- CTA + mobile menu button --}}
        <div class="flex items-center gap-2">
            <a href="{{ $primaryCtaUrl }}" class="hidden sm:inline-flex k-btn k-btn-primary text-xs">
                <span>ابدأ تجربتك</span>
                <i class="fas fa-arrow-left text-[10px]"></i>
            </a>
            <button type="button" class="k-menu-btn" aria-label="فتح القائمة" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    {{-- Mobile dropdown --}}
    <div class="k-menu-panel" style="display:none">
        <a href="#features">المميزات</a>
        <a href="#pricing">الأسعار</a>
        <a href="#compare">المقارنة</a>
        <a href="#faq">الأسئلة الشائعة</a>
        <a href="{{ $primaryCtaUrl }}" class="bg-indigo-600/20 text-white mt-2">ابدأ تجربتك</a>
    </div>
</header>

{{-- ===================== SECTION 2: HERO (compact v2) ===================== --}}
<section class="relative py-10 sm:py-14 lg:py-20 overflow-hidden">
    {{-- Drifting background blobs --}}
    <div class="drift-blob absolute top-10 right-10 w-60 h-60 bg-indigo-500/15 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="drift-blob absolute bottom-10 left-10 w-60 h-60 bg-purple-500/15 rounded-full blur-[100px] pointer-events-none" style="animation-delay:-7s"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

        <div class="k-reveal">
            <span class="k-eyebrow">
                <i class="fas fa-rocket ml-2"></i>
                نظام SaaS متعدد المستأجرين
            </span>
        </div>

        <h1 class="k-h text-3xl sm:text-4xl lg:text-5xl xl:text-6xl mb-3 k-reveal bg-gradient-to-l from-white via-slate-100 to-slate-300 bg-clip-text text-transparent leading-tight" style="transition-delay:80ms">
            نظام إدارة الأعمال والمخازن الذكي
        </h1>

        <p class="k-reveal text-sm sm:text-base lg:text-lg text-slate-300 max-w-3xl mx-auto mb-6 leading-relaxed" style="transition-delay:160ms">
            منصة متكاملة لإدارة <span class="text-amber-400 font-bold">نقاط البيع</span>، و<span class="text-emerald-400 font-bold">المخزون</span>، و<span class="text-cyan-400 font-bold">المحاسبة</span>، و<span class="text-purple-400 font-bold">التصنيع</span> — كل ما يحتاجه عملك في مكان واحد، بتجربة استخدام عربية أصيلة.
        </p>

        <div class="k-reveal flex flex-col sm:flex-row gap-3 justify-center items-center mb-6" style="transition-delay:240ms">
            <a href="{{ $primaryCtaUrl }}" class="k-btn k-btn-primary text-sm sm:text-base px-6 sm:px-8 py-3 sm:py-4 w-full sm:w-auto">
                <span>ابدأ تجربتك المجانية</span>
                <i class="fas fa-arrow-left"></i>
            </a>
            <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener" class="k-btn k-btn-ghost text-sm sm:text-base px-6 sm:px-8 py-3 sm:py-4 w-full sm:w-auto">
                <i class="fas fa-calendar-check"></i>
                <span>احجز ديمو</span>
            </a>
        </div>

        <div class="k-reveal-stagger flex flex-wrap justify-center gap-x-4 gap-y-2 text-xs sm:text-sm text-slate-400 max-w-3xl mx-auto" style="transition-delay:320ms">
            <span class="flex items-center gap-1.5 whitespace-nowrap"><i class="fas fa-check-circle text-emerald-400 text-xs"></i> 14 يوم تجربة مجانية</span>
            <span class="flex items-center gap-1.5 whitespace-nowrap"><i class="fas fa-check-circle text-emerald-400 text-xs"></i> بدون بطاقة ائتمان</span>
            <span class="flex items-center gap-1.5 whitespace-nowrap"><i class="fas fa-check-circle text-emerald-400 text-xs"></i> دعم فني عربي</span>
            <span class="flex items-center gap-1.5 whitespace-nowrap"><i class="fas fa-check-circle text-emerald-400 text-xs"></i> إلغاء في أي وقت</span>
        </div>
    </div>
</section>

{{-- ===================== SECTION 3: TRUST STRIP ===================== --}}
<section class="relative py-12 sm:py-16 border-y border-white/5 bg-gradient-to-b from-transparent via-indigo-950/10 to-transparent">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="k-reveal-stagger grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 text-center">
            @php
                $stats = [
                    ['icon' => 'fa-building',       'color' => 'indigo',  'value' => '+500',   'label' => 'شركة ومؤسسة'],
                    ['icon' => 'fa-users',          'color' => 'cyan',    'value' => '+2,400', 'label' => 'مستخدم نشط'],
                    ['icon' => 'fa-receipt',        'color' => 'purple',  'value' => '+1.2M',  'label' => 'فاتورة شهرياً'],
                    ['icon' => 'fa-shield-halved',  'color' => 'emerald', 'value' => '99.9%',  'label' => 'وقت تشغيل مضمون'],
                ];
            @endphp
            @foreach($stats as $s)
                <div class="glass-card rounded-2xl p-5 sm:p-6">
                    <i class="fas {{ $s['icon'] }} text-3xl sm:text-4xl text-{{ $s['color'] }}-400 mb-3 float-icon"></i>
                    <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight">{{ $s['value'] }}</div>
                    <div class="text-xs sm:text-sm text-slate-400 mt-2">{{ $s['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== SECTION 4: FEATURES GRID ===================== --}}
<section id="features" class="py-16 sm:py-20 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 lg:mb-14">
            <div class="k-reveal"><span class="k-eyebrow">المميزات</span></div>
            <h2 class="k-h k-h-2 mb-4 k-reveal" style="transition-delay:80ms">كل ما يحتاجه عملك في مكان واحد</h2>
            <p class="k-reveal text-base sm:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed" style="transition-delay:160ms">مميزات متكاملة تغطي كل جوانب إدارة الأعمال من الكاشير إلى المحاسبة والتصنيع</p>
        </div>

        <div class="k-reveal-stagger grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
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
<section id="pricing" class="py-16 sm:py-24 lg:py-28 relative">
    <div class="drift-blob absolute top-0 right-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="drift-blob absolute bottom-0 left-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none" style="animation-delay:-10s"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 lg:mb-14">
            <div class="k-reveal"><span class="k-eyebrow">الأسعار</span></div>
            <h2 class="k-h k-h-2 mb-4 k-reveal" style="transition-delay:80ms">باقات مرنة تنمو معك</h2>
            <p class="k-reveal text-base sm:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed" style="transition-delay:160ms">اختر الباقة المناسبة لحجم عملك. كل الباقات تشمل تجربة مجانية لمدة 14 يوم</p>
        </div>

        @php
            $planCount = max(1, min(4, $plans->count()));
            $gridCols  = [
                1 => 'lg:grid-cols-1 max-w-md',
                2 => 'lg:grid-cols-2 max-w-3xl',
                3 => 'lg:grid-cols-3 max-w-6xl',
                4 => 'lg:grid-cols-4 max-w-7xl',
            ][$planCount];
        @endphp
        <div class="k-reveal-stagger grid grid-cols-1 {{ $gridCols }} gap-6 lg:gap-8 items-stretch mx-auto" style="transition-delay:240ms">
            @foreach($plans as $plan)
                @php
                    $isFeatured = (bool) $plan->is_featured;
                    $demoQuery = http_build_query(['utm_source' => 'pricing', 'utm_medium' => 'cta', 'utm_campaign' => $plan->slug]);
                    $demoLink  = $demoUrl . (str_contains($demoUrl, '?') ? '&' : '?') . $demoQuery;
                    $periodLabel = ($plan->billing_period ?? 'monthly') === 'yearly' ? 'سنوياً' : 'شهرياً';
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
                            {{ $plan->display_label ?: $plan->name }}
                        </span>
                        <h3 class="k-h k-h-3 mt-2 text-white">{{ $plan->name }}</h3>
                        <p class="text-sm text-slate-400 mt-2 min-h-[44px] leading-relaxed">{{ $plan->description }}</p>
                    </div>

                    <div class="mb-6 pb-6 border-b {{ $isFeatured ? 'border-amber-500/20' : 'border-white/10' }}">
                        <div class="flex items-baseline gap-2">
                            @if((float) $plan->price == 0)
                                <span class="text-3xl sm:text-4xl font-black text-white">مخصص</span>
                                <span class="text-sm text-slate-400 font-bold">تواصل معنا</span>
                            @else
                                <span class="text-5xl sm:text-6xl font-black text-white tracking-tight">{{ number_format((float) $plan->price, 0) }}</span>
                                <span class="text-sm text-slate-400 font-bold">ريال / {{ $periodLabel }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-2">
                            <i class="fas fa-shield-halved ml-1"></i>
                            بدون رسوم إعداد • إلغاء في أي وقت
                        </p>
                    </div>

                    <ul class="space-y-3 mb-8 flex-grow">
                        @forelse(($plan->value_props ?? []) as $prop)
                            <li class="flex items-start gap-3 text-sm text-slate-300">
                                <i class="fas fa-check-circle {{ $isFeatured ? 'text-amber-400' : 'text-emerald-400' }} text-base shrink-0 mt-0.5"></i>
                                <span>{{ $prop }}</span>
                            </li>
                        @empty
                            @foreach(($plan->featuresList ?? collect()) as $feat)
                                <li class="flex items-start gap-3 text-sm text-slate-300">
                                    <i class="fas fa-check-circle {{ $isFeatured ? 'text-amber-400' : 'text-emerald-400' }} text-base shrink-0 mt-0.5"></i>
                                    <span>{{ \App\Support\FeatureLabels::translate($feat->feature_key) }}</span>
                                </li>
                            @endforeach
                        @endforelse
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

        <p class="k-reveal text-center text-slate-400 text-sm mt-10">
            <i class="fas fa-lock text-emerald-400 ml-1"></i>
            جميع الباقات تشمل تشفير SSL 256-bit، نسخ احتياطية يومية، وفترة تجربة مجانية 14 يوماً
        </p>
    </div>
</section>

{{-- ===================== SECTION 6: COMPARISON TABLE ===================== --}}
<section id="compare" class="py-16 sm:py-20 lg:py-24 bg-gradient-to-b from-transparent via-slate-900/40 to-transparent">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 lg:mb-14">
            <div class="k-reveal"><span class="k-eyebrow">المقارنة</span></div>
            <h2 class="k-h k-h-2 mb-4 k-reveal" style="transition-delay:80ms">قارن بين الباقات</h2>
            <p class="k-reveal text-base sm:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed" style="transition-delay:160ms">كل التفاصيل تحت أمرك</p>
        </div>

        {{-- Desktop table --}}
        <div class="k-reveal hidden md:block glass-card rounded-3xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-900/60">
                    <tr>
                        <th class="text-right px-4 sm:px-6 py-5 font-black text-white text-sm sm:text-base">الميزة</th>
                        @foreach($plans as $plan)
                            <th class="text-center px-4 sm:px-6 py-5 font-black {{ $plan->is_featured ? 'text-amber-400' : 'text-white' }} text-sm sm:text-base">{{ $plan->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach(\App\Support\FeatureLabels::all() as $key => $label)
                        @php $anyHave = false; @endphp
                        @foreach($plans as $plan)
                            @php $has = $plan->featuresList->firstWhere('feature_key', $key); @endphp
                            @if($has && $has->is_enabled) @php $anyHave = true; @endphp @endif
                        @endforeach
                        @if($anyHave)
                            <tr class="hover:bg-white/[.02] transition-colors">
                                <td class="text-right px-4 sm:px-6 py-4 font-bold text-slate-200">{{ $label }}</td>
                                @foreach($plans as $plan)
                                    @php $has = $plan->featuresList->firstWhere('feature_key', $key); @endphp
                                    <td class="text-center px-4 sm:px-6 py-4">
                                        @if($has && $has->is_enabled)
                                            <i class="fas fa-check-circle text-emerald-400 text-lg"></i>
                                            @if($has->limit_value)<div class="text-[10px] text-slate-500 mt-1">حتى {{ $has->limit_value }}</div>@endif
                                        @else
                                            <i class="fas fa-times-circle text-slate-600 text-lg"></i>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile accordion view --}}
        <div class="k-reveal md:hidden space-y-3">
            @foreach($plans as $plan)
                <details class="faq">
                    <summary>
                        <span>{{ $plan->name }}</span>
                        <span class="text-xs text-slate-400 font-normal">{{ $plan->featuresList->count() }} ميزة</span>
                    </summary>
                    <div class="faq-body space-y-2">
                        @foreach(\App\Support\FeatureLabels::all() as $key => $label)
                            @php $has = $plan->featuresList->firstWhere('feature_key', $key); @endphp
                            @if($has)
                                <div class="flex items-center justify-between text-sm py-1.5 border-b border-white/5">
                                    <span class="text-slate-300">{{ $label }}</span>
                                    @if($has->is_enabled)
                                        <i class="fas fa-check-circle text-emerald-400"></i>
                                    @else
                                        <i class="fas fa-times-circle text-slate-600"></i>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== SECTION 7: ADD-ONS ===================== --}}
<section class="py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 sm:mb-12">
            <div class="k-reveal"><span class="k-eyebrow">خدمات إضافية</span></div>
            <h2 class="k-h k-h-2 k-reveal" style="transition-delay:80ms">إضافات احترافية عند الطلب</h2>
        </div>

        <div class="k-reveal-stagger grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
            @php
                $addons = [
                    ['icon' => 'fa-chalkboard-user', 'color' => 'indigo',  'title' => 'تدريب مخصص',   'desc' => 'جلسات تدريب مباشرة لفريقك عن بعد أو في مقر الشركة'],
                    ['icon' => 'fa-crown',            'color' => 'amber',   'title' => 'دعم VIP',       'desc' => 'دعم فني على مدار الساعة مع مهندس حساب مخصص'],
                    ['icon' => 'fa-puzzle-piece',     'color' => 'cyan',    'title' => 'تخصيص وتطوير', 'desc' => 'تطوير وحدات مخصصة تناسب طبيعة عملك الفريدة'],
                    ['icon' => 'fa-truck-ramp-box',   'color' => 'emerald', 'title' => 'ترحيل بيانات',  'desc' => 'نستورد بياناتك من أي نظام قديم بدون فقدان أي معلومة'],
                ];
            @endphp
            @foreach($addons as $a)
                <div class="glass-card rounded-2xl p-6 text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-{{ $a['color'] }}-500/10 border border-{{ $a['color'] }}-500/20 flex items-center justify-center mb-4">
                        <i class="fas {{ $a['icon'] }} text-{{ $a['color'] }}-400 text-2xl"></i>
                    </div>
                    <h3 class="font-black text-lg text-white mb-2">{{ $a['title'] }}</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">{{ $a['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== SECTION 8: FAQ ===================== --}}
<section id="faq" class="py-16 sm:py-20 lg:py-24 bg-gradient-to-b from-transparent via-indigo-950/10 to-transparent">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 sm:mb-14">
            <div class="k-reveal"><span class="k-eyebrow">الأسئلة الشائعة</span></div>
            <h2 class="k-h k-h-2 k-reveal" style="transition-delay:80ms">إجابات على أهم الأسئلة</h2>
        </div>

        <div class="space-y-3 k-reveal" style="transition-delay:160ms">
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
<section class="py-16 sm:py-20 lg:py-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/40 via-purple-900/30 to-cyan-900/30"></div>
    <div class="drift-blob absolute top-0 right-0 w-96 h-96 bg-indigo-500/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="drift-blob absolute bottom-0 left-0 w-96 h-96 bg-purple-500/20 rounded-full blur-[120px] pointer-events-none" style="animation-delay:-8s"></div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="k-reveal"><span class="k-eyebrow">جاهز للبدء؟</span></div>
        <h2 class="k-h k-h-2 mb-5 k-reveal" style="transition-delay:80ms">انضم لمئات الشركات التي تثق بكيان</h2>
        <p class="k-reveal text-base sm:text-lg text-slate-300 mb-10 max-w-2xl mx-auto leading-relaxed" style="transition-delay:160ms">ابدأ تجربتك المجانية اليوم. بدون بطاقة ائتمان، بدون التزامات.</p>

        <div class="k-reveal flex flex-col sm:flex-row gap-4 justify-center mb-10" style="transition-delay:240ms">
            <a href="{{ $primaryCtaUrl }}" class="k-btn k-btn-primary text-base px-8 py-4 w-full sm:w-auto">
                <i class="fas fa-rocket"></i>
                <span>ابدأ تجربتك المجانية الآن</span>
            </a>
        </div>

        <div class="k-reveal-stagger flex flex-wrap justify-center gap-x-6 gap-y-2 text-xs text-slate-400" style="transition-delay:320ms">
            <span><i class="fas fa-shield-halved text-emerald-400 ml-1"></i> SSL آمن</span>
            <span><i class="fas fa-clock text-amber-400 ml-1"></i> إعداد في 5 دقائق</span>
            <span><i class="fas fa-undo text-cyan-400 ml-1"></i> ضمان استرداد المبلغ 30 يوم</span>
        </div>
    </div>
</section>

<footer class="border-t border-white/5 bg-slate-950/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-14">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 sm:gap-10 mb-10">

            <div class="col-span-2 md:col-span-1" style="color:#f8fafc">
                <div class="flex items-center gap-3 mb-4">
                    <img src="/images/kayan-icon.svg"
                         alt="{{ config('pricing.brand_name') }}"
                         class="h-9 w-auto"
                         width="36" height="36">
                    <span class="text-lg font-black tracking-tight">{{ config('pricing.brand_name') }}</span>
                </div>
                <p class="text-sm text-slate-400 leading-relaxed">{{ config('pricing.brand_tagline') }}.</p>

                <div class="flex gap-3 mt-5">
                    <a href="#" class="w-9 h-9 rounded-lg glass-card flex items-center justify-center hover:bg-indigo-500/20 transition-colors" aria-label="Twitter"><i class="fab fa-x-twitter text-slate-300"></i></a>
                    <a href="https://remotelly1.site/" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg glass-card flex items-center justify-center hover:bg-cyan-500/20 transition-colors" aria-label="Booking"><i class="fas fa-calendar-check text-slate-300"></i></a>
                </div>
            </div>

            <div>
                <h4 class="font-black text-white mb-4 text-sm uppercase tracking-wider">المنتج</h4>
                <ul class="space-y-2.5 text-sm text-slate-400">
                    <li><a href="#features" class="hover:text-white transition-colors">المميزات</a></li>
                    <li><a href="#pricing" class="hover:text-white transition-colors">الأسعار</a></li>
                    <li><a href="#faq" class="hover:text-white transition-colors">الأسئلة الشائعة</a></li>
                    <li><a href="{{ $primaryCtaUrl }}" class="hover:text-white transition-colors">ابدأ مجاناً</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-black text-white mb-4 text-sm uppercase tracking-wider">الشركة</h4>
                <ul class="space-y-2.5 text-sm text-slate-400">
                    <li><a href="#" class="hover:text-white transition-colors">من نحن</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">المدونة</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">الوظائف</a></li>
                    <li><a href="mailto:info@kayyan.com" class="hover:text-white transition-colors">تواصل معنا</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-black text-white mb-4 text-sm uppercase tracking-wider">قانوني</h4>
                <ul class="space-y-2.5 text-sm text-slate-400">
                    <li><a href="#" class="hover:text-white transition-colors">سياسة الخصوصية</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">شروط الاستخدام</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">اتفاقية مستوى الخدمة</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">الأمان والامتثال</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/5 pt-7 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-slate-500">
            <p>© {{ date('Y') }} {{ config('pricing.brand_name') }}. جميع الحقوق محفوظة.</p>
            <p>صُنع بـ <i class="fas fa-heart text-rose-500 mx-1"></i> في السعودية</p>
        </div>
    </div>
</footer>

@endsection