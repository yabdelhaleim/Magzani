{{--
    Kayyan SaaS - Public Pricing Layout
    Standalone layout (no @extends) used by /pricing.
    - RTL Arabic, dark glass theme
    - SVG defs + K-letter logo inlined
    - All assets via CDN (Tailwind, FontAwesome, Cairo)
    - SEO meta block (title, description, OG, Twitter, JSON-LD)
    - Mobile-first
--}}
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0b0f19">

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

    {{-- ===== FAVICON (link from project public/favicon.ico) ===== --}}
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="/favicon.ico">

    {{-- ===== ASSETS (CDN) ===== --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://images.unsplash.com">

    {{-- ===== STRUCTURED DATA (JSON-LD) — pushed by views via @stack('json-ld') ===== --}}
    @stack('json-ld')

    {{-- ===== INLINE CRITICAL CSS — keeps first paint fast, works without waiting on Tailwind CDN ===== --}}
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
            --k-text: #f8fafc;
            --k-text-muted: #94a3b8;
        }
        html, body { font-family: 'Cairo', sans-serif; background: var(--k-bg); color: var(--k-text); }
        body {
            background-image:
                radial-gradient(circle at 10% 10%, rgba(99, 102, 241, 0.18) 0%, transparent 45%),
                radial-gradient(circle at 90% 90%, rgba(168, 85, 247, 0.18) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.05) 0%, transparent 60%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        /* Glass utilities */
        .glass-panel { background: var(--k-surface); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); border: 1px solid var(--k-border); }
        .glass-card  { background: var(--k-card); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid var(--k-border); transition: all .25s ease; }
        .glass-card:hover { border-color: var(--k-border-hover); background: var(--k-card-hover); transform: translateY(-4px); }

        /* K-Pricing card */
        .k-card {
            position: relative;
            background: linear-gradient(160deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.01) 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            padding: 2.25rem;
            transition: transform .35s cubic-bezier(.4,0,.2,1), box-shadow .35s, border-color .35s;
        }
        .k-card:hover {
            transform: translateY(-8px) scale(1.005);
            border-color: rgba(168, 85, 247, 0.4);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4), 0 0 0 1px rgba(168,85,247,.15);
        }
        .k-card--featured {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            border-color: rgba(245, 158, 11, 0.25);
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
        }
        .k-card--featured:hover { box-shadow: 0 30px 60px -10px rgba(245, 158, 11, 0.3), 0 0 0 1px rgba(245,158,11,.25); }

        /* K-button */
        .k-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .55rem;
            padding: .85rem 1.5rem; border-radius: 16px; font-weight: 700;
            transition: all .25s ease; cursor: pointer; text-align: center;
            font-size: 0.95rem; line-height: 1; white-space: nowrap;
        }
        .k-btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
            color: white; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);
        }
        .k-btn-primary:hover { box-shadow: 0 18px 35px -8px rgba(37, 99, 235, 0.5); transform: translateY(-2px); }
        .k-btn-featured {
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            color: white; box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.4);
        }
        .k-btn-featured:hover { box-shadow: 0 18px 35px -8px rgba(245, 158, 11, 0.6); transform: translateY(-2px); }
        .k-btn-ghost {
            background: rgba(255,255,255,.06); color: var(--k-text);
            border: 1px solid rgba(255,255,255,.12);
        }
        .k-btn-ghost:hover { background: rgba(255,255,255,.12); border-color: rgba(255,255,255,.25); }

        /* K-section-title */
        .k-eyebrow {
            display: inline-block;
            padding: .4rem 1rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            background: rgba(99, 102, 241, 0.12);
            color: #818cf8;
            border: 1px solid rgba(99, 102, 241, 0.25);
            margin-bottom: 1rem;
        }
        .k-h { font-family: 'Cairo', sans-serif; font-weight: 900; letter-spacing: -.02em; line-height: 1.15; }

        /* Subtle animated gradient overlay for featured card */
        .k-glow {
            position: absolute; pointer-events: none; border-radius: 9999px;
            filter: blur(60px); opacity: .4;
        }

        /* Accordion (FAQ) using <details> */
        details.faq { background: var(--k-card); border: 1px solid var(--k-border); border-radius: 18px; padding: 0; transition: all .2s; }
        details.faq:hover { border-color: rgba(168,85,247,.25); }
        details.faq[open] { border-color: rgba(99,102,241,.35); }
        details.faq > summary { cursor: pointer; padding: 1.1rem 1.4rem; list-style: none; display: flex; justify-content: space-between; align-items: center; font-weight: 700; }
        details.faq > summary::-webkit-details-marker { display: none; }
        details.faq > summary::after { content: '\f078'; font-family: 'Font Awesome 6 Free'; font-weight: 900; transition: transform .25s; color: var(--k-indigo); }
        details.faq[open] > summary::after { transform: rotate(180deg); }
        details.faq > .faq-body { padding: 0 1.4rem 1.3rem; color: var(--k-text-muted); line-height: 1.75; }

        /* Floating animation for "Most Popular" badge */
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, .6); }
            50%      { box-shadow: 0 0 0 14px rgba(245, 158, 11, 0); }
        }
        .pulse-glow { animation: pulseGlow 2s infinite; }

        /* Print-friendly reset */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
    </style>

    @stack('head')
</head>
<body>

    {{-- ===== KAYYAN SVG DEFS (reused from landlord layout) ===== --}}
    <svg width="0" height="0" style="position:absolute;" aria-hidden="true">
        <defs>
            <radialGradient id="kayanBgSmall" cx="40%" cy="30%" r="75%">
                <stop offset="0%" stop-color="#2563eb" />
                <stop offset="60%" stop-color="#1d4ed8" />
                <stop offset="100%" stop-color="#0b132b" />
            </radialGradient>
            <linearGradient id="kLeftSmall" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#60a5fa" />
                <stop offset="100%" stop-color="#2563eb" />
            </linearGradient>
            <linearGradient id="kRightTopSmall" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#38bdf8" />
                <stop offset="100%" stop-color="#0284c7" />
            </linearGradient>
            <linearGradient id="kRightBottomSmall" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#0284c7" />
                <stop offset="100%" stop-color="#1e3a8a" />
            </linearGradient>
            <filter id="glowSmall" x="-20%" y="-20%" width="140%" height="140%">
                <feGaussianBlur stdDeviation="2" result="blur" />
                <feComposite in="SourceGraphic" in2="blur" operator="over" />
            </filter>
        </defs>
    </svg>

    @yield('content')

    @stack('scripts')
    @yield('scripts')
</body>
</html>
