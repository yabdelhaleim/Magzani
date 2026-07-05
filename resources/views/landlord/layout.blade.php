<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم السحابية | كيان SaaS</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts: Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Cairo', sans-serif;
        }
        body {
            background-color: #0b0f19;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(168, 85, 247, 0.1) 0%, transparent 40%);
            background-attachment: fixed;
        }
        .glass-panel {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glass-card {
            background: rgba(31, 41, 55, 0.4);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            border-color: rgba(168, 85, 247, 0.2);
            background: rgba(31, 41, 55, 0.6);
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex">

    <!-- SVG Defs for Kayan Logo -->
    <svg width="0" height="0" style="position:absolute;">
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

    <!-- Sidebar -->
    <aside class="w-64 glass-panel border-l border-slate-800 flex flex-col justify-between fixed h-full z-50">
        <div>
            <!-- Logo -->
            <div class="p-6 border-b border-slate-800 text-center">
                <a href="{{ url('/') }}" class="text-xl font-extrabold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent flex items-center justify-center gap-2">
                    <svg width="24" height="24" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="display:inline-block; vertical-align:middle;">
                        <rect x="5" y="5" width="90" height="90" rx="26" fill="url(#kayanBgSmall)" />
                        <path d="M 34 26 L 46 18 L 46 71 L 34 79 Z" fill="url(#kLeftSmall)" filter="url(#glowSmall)" />
                        <path d="M 46 44 L 68 20 L 78 20 L 53 47 Z" fill="url(#kRightTopSmall)" />
                        <path d="M 50 44 L 75 72 L 64 72 L 46 51 Z" fill="url(#kRightBottomSmall)" />
                        <path d="M 46 44 L 56 48 L 46 52 Z" fill="#e0f2fe" opacity="0.9" />
                    </svg>
                    <span>كيان SaaS</span>
                </a>
                <span class="text-xs text-blue-400 block mt-1 font-semibold">لوحة تحكم السوبر أدمن</span>
            </div>
            
            <!-- Navigation Links -->
            <nav class="p-4 space-y-2">
                <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition {{ request()->routeIs('super-admin.dashboard') ? 'bg-indigo-600/30 text-indigo-400 border border-indigo-500/20' : 'text-slate-400' }}">
                    <i class="fa-solid fa-chart-pie text-lg"></i>
                    <span class="font-semibold">لوحة الإحصائيات</span>
                </a>
                <a href="{{ route('super-admin.plans.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition {{ request()->routeIs('super-admin.plans.*') ? 'bg-indigo-600/30 text-indigo-400 border border-indigo-500/20' : 'text-slate-400' }}">
                    <i class="fa-solid fa-tags text-lg"></i>
                    <span class="font-semibold">إدارة الباقات</span>
                </a>
                <a href="{{ route('super-admin.tenants.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition {{ request()->routeIs('super-admin.tenants.*') ? 'bg-indigo-600/30 text-indigo-400 border border-indigo-500/20' : 'text-slate-400' }}">
                    <i class="fa-solid fa-users-gear text-lg"></i>
                    <span class="font-semibold">إدارة المشتركين</span>
                </a>
            </nav>
        </div>

        <!-- Footer link -->
        <div class="p-4 border-t border-slate-800">
            <a href="{{ url('/') }}" class="flex items-center justify-center gap-2 text-sm text-slate-400 hover:text-purple-400 transition py-2 px-4 rounded-lg bg-slate-800/40">
                <i class="fa-solid fa-arrow-left-long"></i>
                <span>عرض الموقع العام</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 mr-64 flex flex-col min-h-screen">
        <!-- Top bar -->
        <header class="h-16 glass-panel border-b border-slate-800 flex justify-between items-center px-8 fixed left-0 right-0 mr-64 z-40">
            <div>
                <h2 class="text-lg font-bold text-slate-300">
                    @yield('title', 'لوحة التحكم')
                </h2>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm font-semibold text-slate-400">مرحباً، مدير المنصة</span>
                <div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center font-bold text-white shadow-lg">
                    M
                </div>
            </div>
        </header>

        <!-- Page body -->
        <main class="flex-grow p-8 pt-24">
            @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl flex items-center gap-3 shadow-lg">
                <i class="fa-solid fa-circle-check text-lg"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl flex items-center gap-3 shadow-lg">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

</body>
</html>
