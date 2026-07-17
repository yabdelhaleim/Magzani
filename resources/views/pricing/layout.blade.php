{{--
    Kayyan SaaS - Public Pricing Layout v2
    Enhanced with animations, fluid typography, better responsive behavior.

    Changes vs v1:
    - Added scroll-reveal animations (IntersectionObserver-driven)
    - Fluid typography using clamp() for mobile-to-desktop scaling
    - Mobile menu with hamburger toggle
    - Better focus states (a11y)
    - Reduced-motion respect (prefers-reduced-motion)
    - Better dark/light contrast ratios
--}}
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0b0f19">
    <meta name="color-scheme" content="dark">

    {{-- ===== PRIMARY SEO ===== --}}
    <title>@yield('title', config('pricing.seo_title'))</title>
    <meta name="description" content="@yield('description', config('pricing.seo_description'))">
    <meta name="keywords" content="@yield('keywords', config('pricing.seo_keywords'))">
    <meta name="author" content="{{ config('pricing.brand_name') }}">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
    <link rel="canonical" href="{{ $canonical ?? url('/pricing') }}">

    {{-- ===== OPEN GRAPH (Facebook, WhatsApp previews) ===== --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('pricing.brand_name') }}">
    <meta property="og:title" content="@yield('title', config('pricing.seo_title'))">
    <meta property="og:description" content="@yield('description', config('pricing.seo_description'))">
    <meta property="og:url" content="{{ $canonical ?? url('/pricing') }}">
    <meta property="og:locale" content="ar_SA">
    <meta property="og:locale:alternate" content="en_US">
    <meta property="og:image" content="{{ $siteOrigin ?? '' }}/og-image.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ config('pricing.brand_name') }} - {{ config('pricing.brand_tagline') }}">

    {{-- ===== TWITTER CARD ===== --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', config('pricing.seo_title'))">
    <meta name="twitter:description" content="@yield('description', config('pricing.seo_description'))">
    <meta name="twitter:image" content="{{ $siteOrigin ?? '' }}/og-image.png">

    {{-- ===== FAVICON ===== --}}
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="/favicon.ico">

    {{-- ===== ASSETS (CDN) ===== --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://images.unsplash.com">

    {{-- ===== JSON-LD ===== --}}
    @stack('json-ld')

    {{-- ===== CRITICAL CSS — fluid typography + animation system ===== --}}
    <style>
        :root {
            --k-bg: #0b0f19;
            --k-surface: rgba(17, 24, 39, 0.7);
            --k-card: rgba(31, 41, 55, 0.4);
            --k-card-hover: rgba(31, 41, 55, 0.6);
            --k-border: rgba(255, 255, 255, 0.05);
            --k-border-hover: rgba(168, 85, 247, 0.25);
            --k-indigo: #6366f1;
            --k-amber: #f59e0b;
            --k-purple: #a855f7;
            --k-cyan: #06b6d4;
            --k-emerald: #10b981;
            --k-text: #f8fafc;
            --k-text-muted: #94a3b8;
            --k-text-dim: #64748b;

            /* Fluid typography — clamps scale smoothly between mobile & desktop */
            --k-fs-hero: clamp(2.25rem, 4vw + 1rem, 4.5rem);       /* h1: 36-72px */
            --k-fs-h2: clamp(1.75rem, 3vw + 0.75rem, 3rem);      /* h2: 28-48px */
            --k-fs-h3: clamp(1.25rem, 2vw + 0.5rem, 2rem);       /* h3: 20-32px */
            --k-fs-lead: clamp(1rem, 1.5vw + 0.5rem, 1.375rem);  /* subtitle: 16-22px */
            --k-fs-body: clamp(0.9375rem, 0.5vw + 0.75rem, 1.0625rem); /* body: 15-17px */
            --k-fs-small: 0.875rem;

            /* Spacing */
            --k-section-py: clamp(3.5rem, 8vw, 6rem);
        }

        html { font-size: 16px; -webkit-text-size-adjust: 100%; }
        html, body { font-family: 'Cairo', system-ui, -apple-system, sans-serif; }
        body {
            background: var(--k-bg);
            color: var(--k-text);
            font-size: var(--k-fs-body);
            line-height: 1.65;
            letter-spacing: -0.005em;
            font-feature-settings: "kern", "liga", "calt";
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background-image:
                radial-gradient(ellipse at 10% 10%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 90% 90%, rgba(168, 85, 247, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(6, 182, 212, 0.05) 0%, transparent 70%);
            background-attachment: fixed;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Headings use fluid type + tightened letter-spacing */
        .k-h { font-family: 'Cairo', sans-serif; font-weight: 900; letter-spacing: -.025em; line-height: 1.1; }
        .k-h-1 { font-size: var(--k-fs-hero); }
        .k-h-2 { font-size: var(--k-fs-h2); line-height: 1.15; }
        .k-h-3 { font-size: var(--k-fs-h3); line-height: 1.25; }

        /* Smooth rendering + focus rings */
        *:focus-visible { outline: 2px solid var(--k-indigo); outline-offset: 2px; border-radius: 8px; }

        /* ===== GLASS UTILITIES ===== */
        .glass-panel {
            background: var(--k-surface);
            backdrop-filter: blur(14px) saturate(150%);
            -webkit-backdrop-filter: blur(14px) saturate(150%);
            border: 1px solid var(--k-border);
        }
        .glass-card {
            background: var(--k-card);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--k-border);
            transition: all .35s cubic-bezier(.4, 0, .2, 1);
        }
        .glass-card:hover {
            border-color: var(--k-border-hover);
            background: var(--k-card-hover);
            transform: translateY(-4px);
        }

        /* ===== K-PRICING CARD (with animated gradient border) ===== */
        .k-card {
            position: relative;
            background: linear-gradient(160deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.01) 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            padding: clamp(1.5rem, 3vw, 2.5rem);
            transition: transform .45s cubic-bezier(.4, 0, .2, 1), box-shadow .45s, border-color .45s;
            will-change: transform;
        }
        .k-card:hover {
            transform: translateY(-10px) scale(1.01);
            border-color: rgba(168, 85, 247, 0.45);
            box-shadow: 0 30px 60px -15px rgba(0,0,0,0.5), 0 0 0 1px rgba(168,85,247,.2);
        }
        .k-card--featured {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            border-color: rgba(245, 158, 11, 0.3);
            transform: scale(1.02);
        }
        .k-card--featured:hover {
            box-shadow: 0 40px 80px -10px rgba(245, 158, 11, 0.35), 0 0 0 1px rgba(245,158,11,.3);
        }
        .k-card--featured::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 28px;
            padding: 1px;
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 50%, #06b6d4 100%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
            opacity: .8;
        }
        @media (max-width: 1023px) { .k-card--featured { transform: scale(1); } }

        /* ===== K-BUTTONS ===== */
        .k-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .55rem;
            padding: clamp(.7rem, 1.5vw, .95rem) clamp(1.1rem, 2.5vw, 1.5rem);
            border-radius: 14px;
            font-weight: 700; line-height: 1;
            transition: transform .25s cubic-bezier(.4, 0, .2, 1), box-shadow .25s, filter .25s;
            cursor: pointer; text-align: center;
            font-size: clamp(0.875rem, 1vw + 0.5rem, 1rem);
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }
        .k-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,.1);
            transform: translateX(-100%);
            transition: transform .5s;
        }
        .k-btn:hover::after { transform: translateX(0); }
        .k-btn:active { transform: scale(.97); }
        .k-btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
            color: white;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);
        }
        .k-btn-primary:hover {
            box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.55);
            transform: translateY(-2px);
        }
        .k-btn-featured {
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            color: white;
            box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.4);
        }
        .k-btn-featured:hover {
            box-shadow: 0 22px 45px -10px rgba(245, 158, 11, 0.65);
            transform: translateY(-2px);
        }
        .k-btn-ghost {
            background: rgba(255,255,255,.06);
            color: var(--k-text);
            border: 1px solid rgba(255,255,255,.12);
        }
        .k-btn-ghost:hover {
            background: rgba(255,255,255,.12);
            border-color: rgba(255,255,255,.25);
        }

        /* ===== K-EYEBROW ===== */
        .k-eyebrow {
            display: inline-block;
            padding: .45rem 1.15rem;
            border-radius: 999px;
            font-size: clamp(.75rem, 1vw + .5rem, .85rem);
            font-weight: 700;
            letter-spacing: 0.05em;
            background: rgba(99, 102, 241, 0.12);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, 0.25);
            margin-bottom: 1.25rem;
        }

        /* ===== SCROLL REVEAL ANIMATIONS — v2 (robust) =====
           - Initial state: opacity 0 + small Y offset
           - When .is-visible is added: animate to visible
           - Failsafe: if JS doesn't fire, elements still show after 1.2s (no-JS fallback)
           - Honors prefers-reduced-motion
        */
        @keyframes kRevealIn {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes kStaggerIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .k-reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity .7s cubic-bezier(.16, 1, .3, 1), transform .7s cubic-bezier(.16, 1, .3, 1);
            will-change: opacity, transform;
        }
        .k-reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        /* Failsafe: after 1.5s without .is-visible, show anyway */
        .k-reveal:not(.is-visible) { animation: kRevealIn 0s 1.5s forwards; }

        /* Stagger children */
        .k-reveal-stagger > * {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity .65s cubic-bezier(.16, 1, .3, 1), transform .65s cubic-bezier(.16, 1, .3, 1);
        }
        .k-reveal-stagger.is-visible > * {
            opacity: 1;
            transform: translateY(0);
        }
        .k-reveal-stagger.is-visible > *:nth-child(1) { transition-delay: 0ms; }
        .k-reveal-stagger.is-visible > *:nth-child(2) { transition-delay: 80ms; }
        .k-reveal-stagger.is-visible > *:nth-child(3) { transition-delay: 160ms; }
        .k-reveal-stagger.is-visible > *:nth-child(4) { transition-delay: 240ms; }
        .k-reveal-stagger.is-visible > *:nth-child(5) { transition-delay: 320ms; }
        .k-reveal-stagger.is-visible > *:nth-child(6) { transition-delay: 400ms; }
        .k-reveal-stagger.is-visible > *:nth-child(7) { transition-delay: 480ms; }
        .k-reveal-stagger.is-visible > *:nth-child(8) { transition-delay: 560ms; }
        /* Failsafe */
        .k-reveal-stagger:not(.is-visible) > * { animation: kStaggerIn 0s 1.6s forwards; }

        /* Floating animation */
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, .55); }
            50%      { box-shadow: 0 0 0 14px rgba(245, 158, 11, 0); }
        }
        .pulse-glow { animation: pulseGlow 2.4s infinite; }

        /* Subtle background drift */
        @keyframes drift {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
            50%      { transform: translate3d(20px, -20px, 0) scale(1.1); }
        }
        .drift-blob { animation: drift 20s ease-in-out infinite; }

        /* Floating icon animation */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-8px); }
        }
        .float-icon { animation: float 4s ease-in-out infinite; }

        /* Accordion (FAQ) */
        details.faq {
            background: var(--k-card);
            border: 1px solid var(--k-border);
            border-radius: 18px;
            padding: 0;
            transition: border-color .25s, background .25s;
        }
        details.faq:hover { border-color: rgba(168,85,247,.25); }
        details.faq[open] { border-color: rgba(99,102,241,.35); background: rgba(99,102,241,.05); }
        details.faq > summary {
            cursor: pointer; padding: 1.1rem 1.4rem;
            list-style: none; display: flex; justify-content: space-between; align-items: center;
            font-weight: 700; font-size: 1rem; line-height: 1.4;
        }
        details.faq > summary::-webkit-details-marker { display: none; }
        details.faq > summary::after {
            content: '\f078'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
            transition: transform .3s ease; color: var(--k-indigo); margin-right: .5rem;
            flex-shrink: 0;
        }
        details.faq[open] > summary::after { transform: rotate(-180deg); }
        details.faq > .faq-body {
            padding: 0 1.4rem 1.3rem;
            color: var(--k-text-muted);
            line-height: 1.75;
            max-width: 65ch;
        }

        /* ===== TOP BAR — mobile menu ===== */
        .k-topbar {
            position: sticky; top: 0; z-index: 50;
            background: rgba(11, 15, 25, 0.85);
            backdrop-filter: blur(14px) saturate(180%);
            -webkit-backdrop-filter: blur(14px) saturate(180%);
            border-bottom: 1px solid var(--k-border);
        }
        .k-menu-btn {
            display: none;
            background: rgba(255,255,255,.06);
            border: 1px solid var(--k-border);
            color: var(--k-text);
            width: 42px; height: 42px;
            border-radius: 12px;
            align-items: center; justify-content: center;
            cursor: pointer; transition: background .2s;
        }
        .k-menu-btn:hover { background: rgba(255,255,255,.12); }
        .k-menu-panel {
            display: none;
            position: absolute; top: 100%; left: 0; right: 0;
            background: rgba(15, 23, 42, 0.98);
            backdrop-filter: blur(14px);
            border-top: 1px solid var(--k-border);
            border-bottom: 1px solid var(--k-border);
            padding: 1rem 1rem 1.25rem;
        }
        .k-menu-panel a {
            display: block; padding: .75rem .85rem;
            color: var(--k-text-muted); border-radius: 12px;
            font-weight: 600; font-size: 0.95rem;
            transition: background .2s, color .2s;
        }
        .k-menu-panel a:hover { background: rgba(255,255,255,.06); color: white; }
        @media (max-width: 767px) {
            .k-menu-btn { display: inline-flex; }
            .k-topbar-nav { display: none; }
        }

        /* ===== Reduced-motion accessibility ===== */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
            .k-reveal { opacity: 1; transform: none; }
        }

        /* ===== Print-friendly ===== */
        @media print {
            body { background: white; color: black; }
            .k-topbar, .k-btn, .drift-blob, .pulse-glow { display: none; }
        }

        /* ===== MODAL (Plan Details) ===== */
        .k-modal {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            animation: kModalIn .25s ease-out;
        }
        .k-modal[hidden] { display: none; }
        @keyframes kModalIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        .k-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            cursor: pointer;
        }
        .k-modal-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            max-height: min(90vh, 720px);
            display: flex;
            flex-direction: column;
            background: linear-gradient(160deg, rgba(15, 23, 42, 0.98) 0%, rgba(11, 15, 25, 0.98) 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(168, 85, 247, 0.15);
            animation: kModalSlide .35s cubic-bezier(.16, 1, .3, 1);
            overflow: hidden;
        }
        @keyframes kModalSlide {
            from { opacity: 0; transform: translateY(20px) scale(.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .k-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .k-modal-header--featured {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(239, 68, 68, 0.05) 100%);
            border-bottom-color: rgba(245, 158, 11, 0.2);
        }
        .k-modal-close {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--k-text);
            width: 38px;
            height: 38px;
            border-radius: 10px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background .2s, border-color .2s, transform .2s;
            flex-shrink: 0;
        }
        .k-modal-close:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.25);
            transform: rotate(90deg);
        }
        .k-modal-body {
            padding: 1.25rem 1.5rem 1.5rem;
            overflow-y: auto;
            flex: 1 1 auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.2) transparent;
        }
        .k-modal-body::-webkit-scrollbar { width: 8px; }
        .k-modal-body::-webkit-scrollbar-track { background: transparent; }
        .k-modal-body::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.15);
            border-radius: 4px;
        }
        .k-modal-body::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.25); }
        .k-modal-footer {
            padding: 1rem 1.5rem 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(0, 0, 0, 0.2);
        }
        .k-feature-row {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: background .2s, border-color .2s;
        }
        .k-feature-row:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
        }
        .k-feature-row--enabled {
            background: rgba(16, 185, 129, 0.05);
            border-color: rgba(16, 185, 129, 0.2);
        }
        .k-feature-row--featured.k-feature-row--enabled {
            background: rgba(245, 158, 11, 0.06);
            border-color: rgba(245, 158, 11, 0.22);
        }
        .k-feature-row--disabled {
            opacity: 0.5;
        }
        .k-feature-row__icon {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 2px;
        }
        .k-feature-row__icon--on {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
        }
        .k-feature-row__icon--featured-on {
            background: rgba(245, 158, 11, 0.18);
            color: #fbbf24;
        }
        .k-feature-row__icon--off {
            background: rgba(100, 116, 139, 0.2);
            color: #64748b;
        }
        .k-feature-row__body { flex: 1 1 auto; min-width: 0; }
        .k-feature-row__title {
            font-weight: 700;
            color: var(--k-text);
            font-size: 0.95rem;
            line-height: 1.4;
        }
        .k-feature-row__limit {
            font-size: 0.75rem;
            color: var(--k-text-muted);
            margin-top: 0.15rem;
        }
        .k-feature-row__status {
            flex-shrink: 0;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            letter-spacing: 0.02em;
            white-space: nowrap;
            align-self: center;
        }
        .k-feature-row__status--on {
            background: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .k-feature-row__status--off {
            background: rgba(100, 116, 139, 0.15);
            color: #94a3b8;
            border: 1px solid rgba(100, 116, 139, 0.25);
        }
        .k-feature-row__status--featured-on {
            background: rgba(245, 158, 11, 0.15);
            color: #fcd34d;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
    </style>

    @stack('head')
</head>
<body>

    @yield('content')

    {{-- ===== SCROLL REVEAL OBSERVER (v2 — robust) ===== --}}
    <script>
        // Scroll-reveal: uses IntersectionObserver + requestAnimationFrame for smooth first-paint
        (function() {
            var elements = document.querySelectorAll('.k-reveal, .k-reveal-stagger');
            if (!elements.length) return;

            // No-JS / no-IO fallback: show everything after a short delay
            if (typeof IntersectionObserver === 'undefined' || !('IntersectionObserver' in window)) {
                setTimeout(function() {
                    elements.forEach(function(el) { el.classList.add('is-visible'); });
                }, 50);
                return;
            }

            // Skip if user prefers reduced motion
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                elements.forEach(function(el) { el.classList.add('is-visible'); });
                return;
            }

            // Use rAF + setTimeout to ensure elements already in viewport
            // become visible even if observer hasn't fired yet
            var showImmediate = function() {
                requestAnimationFrame(function() {
                    elements.forEach(function(el) {
                        var rect = el.getBoundingClientRect();
                        if (rect.top < window.innerHeight && rect.bottom > 0) {
                            el.classList.add('is-visible');
                        }
                    });
                });
            };

            // Initial sweep (covers already-visible elements)
            showImmediate();

            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.08,
                rootMargin: '0px 0px -40px 0px'
            });

            elements.forEach(function(el) { observer.observe(el); });
        })();

        // Mobile menu toggle
        (function() {
            const btn = document.querySelector('.k-menu-btn');
            const panel = document.querySelector('.k-menu-panel');
            if (!btn || !panel) return;
            btn.addEventListener('click', function() {
                const isOpen = panel.style.display === 'block';
                panel.style.display = isOpen ? 'none' : 'block';
                btn.setAttribute('aria-expanded', String(!isOpen));
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.className = isOpen ? 'fas fa-bars' : 'fas fa-times';
                }
            });
            // Close on link click
            panel.querySelectorAll('a').forEach(function(a) {
                a.addEventListener('click', function() {
                    panel.style.display = 'none';
                    btn.setAttribute('aria-expanded', 'false');
                    const icon = btn.querySelector('i');
                    if (icon) icon.className = 'fas fa-bars';
                });
            });
        })();

        // Plan Details Modal — open/close
        (function() {
            var openTriggers = document.querySelectorAll('[data-modal-target]');
            if (!openTriggers.length) return;

            function openModal(id) {
                var modal = document.getElementById(id);
                if (!modal) return;
                modal.hidden = false;
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                // Move focus to the close button for accessibility
                var closeBtn = modal.querySelector('.k-modal-close');
                if (closeBtn) setTimeout(function() { closeBtn.focus(); }, 50);
            }

            function closeModal(modal) {
                if (!modal || modal.hidden) return;
                modal.hidden = true;
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            openTriggers.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    openModal(btn.getAttribute('data-modal-target'));
                });
            });

            // Close on [data-modal-close] click (backdrop, close button, footer close)
            document.addEventListener('click', function(e) {
                var closer = e.target.closest('[data-modal-close]');
                if (!closer) return;
                var modal = closer.closest('.k-modal');
                if (modal) closeModal(modal);
            });

            // Close on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key !== 'Escape') return;
                var open = document.querySelectorAll('.k-modal:not([hidden])');
                if (!open.length) return;
                open.forEach(closeModal);
            });

            // Trap focus inside modal while open
            document.addEventListener('keydown', function(e) {
                if (e.key !== 'Tab') return;
                var modal = document.querySelector('.k-modal:not([hidden])');
                if (!modal) return;
                var focusables = modal.querySelectorAll('a, button, [tabindex]:not([tabindex="-1"])');
                if (!focusables.length) return;
                var first = focusables[0];
                var last  = focusables[focusables.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            });
        })();
    </script>

    @stack('scripts')
    @yield('scripts')
</body>
</html>