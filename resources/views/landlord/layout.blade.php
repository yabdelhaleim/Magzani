<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم السحابية | مخزني SaaS</title>
    
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

    <!-- Sidebar -->
    <aside class="w-64 glass-panel border-l border-slate-800 flex flex-col justify-between fixed h-full z-50">
        <div>
            <!-- Logo -->
            <div class="p-6 border-b border-slate-800 text-center">
                <a href="{{ url('/') }}" class="text-xl font-extrabold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent flex items-center justify-center gap-2">
                    <i class="fa-solid fa-cubes"></i>
                    <span>مخزني SaaS</span>
                </a>
                <span class="text-xs text-purple-400 block mt-1 font-semibold">لوحة تحكم السوبر أدمن</span>
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
