<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام إدارة المخازن')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts - Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Cairo', sans-serif; }

        html, body {
            overflow-x: hidden;
            max-width: 100vw;
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* ===== SIDEBAR ===== */
        #sidebar {
            position: fixed;
            right: 0;
            top: 0;
            height: 100vh;
            width: 260px;
            background: #0f172a;
            z-index: 50;
            overflow-y: auto;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        #sidebar.sidebar-hidden {
            transform: translateX(100%);
        }

        /* ===== MAIN CONTENT ===== */
        #main-content {
            transition: margin-right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
        }

        /* Desktop: push content when sidebar open */
        @media (min-width: 1024px) {
            #main-content.sidebar-pushed {
                margin-right: 260px;
            }
        }

        /* Mobile: no push, overlay instead */
        @media (max-width: 1023px) {
            #main-content.sidebar-pushed {
                margin-right: 0;
            }
        }

        /* ===== OVERLAY ===== */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 45;
            backdrop-filter: blur(2px);
        }

        @media (max-width: 1023px) {
            #sidebar-overlay {
                display: block;
            }
        }

        /* ===== FLOATING TOGGLE BUTTON ===== */
        #sidebar-toggle-btn {
            position: fixed;
            bottom: 28px;
            left: 28px;
            z-index: 60;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: #fff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.45);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        #sidebar-toggle-btn:hover {
            transform: scale(1.08);
            box-shadow: 0 6px 28px rgba(59, 130, 246, 0.6);
        }

        #sidebar-toggle-btn i {
            font-size: 1.2rem;
            transition: transform 0.3s;
        }

        #sidebar-toggle-btn.is-open i {
            transform: rotate(90deg);
        }

        /* ===== SIDEBAR SCROLLBAR ===== */
        #sidebar::-webkit-scrollbar { width: 4px; }
        #sidebar::-webkit-scrollbar-track { background: #0f172a; }
        #sidebar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        #sidebar::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* ===== MENU ACTIVE INDICATOR ===== */
        .menu-active {
            position: relative;
        }
        .menu-active::before {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 65%;
            background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
            border-radius: 0 4px 4px 0;
        }

        /* ===== ANIMATIONS ===== */
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .animate-slide-in       { animation: slideIn 0.3s ease-out; }
        .animate-slide-in-right { animation: slideInRight 0.3s ease-out; }

        /* ===== RESPONSIVE HELPERS ===== */
        @media (max-width: 768px) {
            .hide-mobile    { display: none !important; }
            .mobile-padding { padding: 0.75rem !important; }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50" x-data="{}">

    <!-- ===================== OVERLAY (mobile only) ===================== -->
    <div id="sidebar-overlay" 
         style="display:none" 
         onclick="toggleSidebar()">
    </div>

    <!-- ===================== SIDEBAR ===================== -->
    <aside id="sidebar" class="">

        <!-- Brand Header -->
        <div class="p-5 flex items-center gap-3 border-b border-slate-800 flex-shrink-0">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                <i class="fas fa-warehouse text-white text-lg"></i>
            </div>
            <div>
                <h2 class="font-bold text-white text-base leading-tight">نظام المخازن</h2>
                <p class="text-xs text-slate-400">إدارة متكاملة</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">

            <!-- لوحة التحكم -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-all {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white menu-active' : '' }}">
                <i class="fas fa-chart-line w-4 text-sm"></i>
                <span class="text-sm">لوحة التحكم</span>
            </a>

            <div class="border-t border-slate-800 my-3"></div>
            <p class="text-xs text-slate-500 px-4 pb-1 font-semibold uppercase tracking-wider">المخازن</p>

            <!-- المخازن والجرد -->
            <div x-data="{ open: {{ request()->routeIs('warehouses.*') || request()->routeIs('transfers.*') || request()->routeIs('stock-counts.*') || request()->routeIs('movements.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-all {{ request()->routeIs('warehouses.*','transfers.*','stock-counts.*','movements.*') ? 'bg-slate-800 text-white' : '' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-warehouse w-4 text-sm"></i>
                        <span class="text-sm">المخازن والجرد</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-collapse class="mr-7 mt-1 space-y-0.5 border-r border-slate-700 pr-2">
                    <a href="{{ route('warehouses.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('warehouses.index') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-list w-3"></i><span>قائمة المخازن</span>
                    </a>
                    <a href="{{ route('warehouses.create') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('warehouses.create') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-plus-circle w-3"></i><span>إضافة مخزن</span>
                    </a>
                    <div class="border-t border-slate-700 my-1"></div>
                    <a href="{{ route('transfers.create') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('transfers.create') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-exchange-alt w-3"></i><span>تحويل بين المخازن</span>
                    </a>
                    <a href="{{ route('transfers.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('transfers.index') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-history w-3"></i><span>سجل التحويلات</span>
                    </a>
                    <div class="border-t border-slate-700 my-1"></div>
                    <a href="{{ route('stock-counts.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('stock-counts.index') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-clipboard-check w-3"></i><span>إدارة الجرد</span>
                    </a>
                    <a href="{{ route('stock-counts.create') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('stock-counts.create') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-plus-square w-3"></i><span>جرد جديد</span>
                    </a>
                    <div class="border-t border-slate-700 my-1"></div>
                    <a href="{{ route('movements.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('movements.index','movements.warehouse','movements.product') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-arrow-right-arrow-left w-3"></i><span>حركات المخزن</span>
                    </a>
                </div>
            </div>

            <div class="border-t border-slate-800 my-3"></div>
            <p class="text-xs text-slate-500 px-4 pb-1 font-semibold uppercase tracking-wider">الأعمال</p>

            <!-- المنتجات -->
            <div x-data="{ open: {{ request()->routeIs('products.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-all {{ request()->routeIs('products.*') ? 'bg-slate-800 text-white' : '' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-box w-4 text-sm"></i><span class="text-sm">المنتجات</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-collapse class="mr-7 mt-1 space-y-0.5 border-r border-slate-700 pr-2">
                    <a href="{{ route('products.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('products.index') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-list w-3"></i><span>قائمة المنتجات</span>
                    </a>
                    <a href="{{ route('products.create') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('products.create') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-plus-circle w-3"></i><span>إضافة منتج</span>
                    </a>
                    <a href="{{ route('products.bulk-price-update') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('products.bulk-price-update') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-tags w-3"></i><span>تحديث الأسعار</span>
                    </a>
                </div>
            </div>

            <!-- الفواتير -->
            <div x-data="{ open: {{ request()->routeIs('invoices.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-all {{ request()->routeIs('invoices.*') ? 'bg-slate-800 text-white' : '' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-invoice w-4 text-sm"></i><span class="text-sm">الفواتير</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-collapse class="mr-7 mt-1 space-y-0.5 border-r border-slate-700 pr-2">
                    <a href="{{ route('invoices.sales.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('invoices.sales.*') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-cash-register w-3"></i><span>فواتير المبيعات</span>
                    </a>
                    <a href="{{ route('invoices.purchases.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('invoices.purchases.*') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-shopping-cart w-3"></i><span>فواتير المشتريات</span>
                    </a>
                    <div class="border-t border-slate-700 my-1"></div>
                    <a href="{{ route('invoices.sales-returns.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('invoices.sales-returns.*') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-undo w-3"></i><span>مرتجعات المبيعات</span>
                    </a>
                    <a href="{{ route('invoices.purchase-returns.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('invoices.purchase-returns.*') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-reply w-3"></i><span>مرتجعات المشتريات</span>
                    </a>
                </div>
            </div>

            <!-- العملاء والموردون -->
            <div x-data="{ open: {{ request()->routeIs('customers.*') || request()->routeIs('suppliers.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-all {{ request()->routeIs('customers.*','suppliers.*') ? 'bg-slate-800 text-white' : '' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-users w-4 text-sm"></i><span class="text-sm">العملاء والموردون</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-collapse class="mr-7 mt-1 space-y-0.5 border-r border-slate-700 pr-2">
                    <a href="{{ route('customers.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('customers.*') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-user-tag w-3"></i><span>العملاء</span>
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('suppliers.*') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-truck w-3"></i><span>الموردون</span>
                    </a>
                </div>
            </div>

            <div class="border-t border-slate-800 my-3"></div>
            <p class="text-xs text-slate-500 px-4 pb-1 font-semibold uppercase tracking-wider">المالية</p>

            <!-- الحسابات -->
            <div x-data="{ open: {{ request()->routeIs('accounting.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-all {{ request()->routeIs('accounting.*') ? 'bg-slate-800 text-white' : '' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-money-bill-wave w-4 text-sm"></i><span class="text-sm">الحسابات</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-collapse class="mr-7 mt-1 space-y-0.5 border-r border-slate-700 pr-2">
                    <a href="{{ route('accounting.treasury') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('accounting.treasury') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-university w-3"></i><span>الخزينة</span>
                    </a>
                    <a href="{{ route('accounting.payments') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('accounting.payments') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-credit-card w-3"></i><span>المدفوعات</span>
                    </a>
                    <a href="{{ route('accounting.expenses.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('accounting.expenses.*') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-file-invoice-dollar w-3"></i><span>المصروفات</span>
                    </a>
                </div>
            </div>

            <!-- التقارير -->
            <div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-all {{ request()->routeIs('reports.*') ? 'bg-slate-800 text-white' : '' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-chart-bar w-4 text-sm"></i><span class="text-sm">التقارير</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-collapse class="mr-7 mt-1 space-y-0.5 border-r border-slate-700 pr-2">
                    <a href="{{ route('reports.inventory') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('reports.inventory') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-boxes w-3"></i><span>تقرير المخزون</span>
                    </a>
                    <a href="{{ route('reports.financial') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('reports.financial') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-dollar-sign w-3"></i><span>التقرير المالي</span>
                    </a>
                    <a href="{{ route('reports.profit-loss') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all {{ request()->routeIs('reports.profit-loss') ? 'text-blue-400 bg-slate-800' : '' }}">
                        <i class="fas fa-chart-line w-3"></i><span>الأرباح والخسائر</span>
                    </a>
                </div>
            </div>

            <div class="border-t border-slate-800 my-3"></div>

            <!-- الإعدادات -->
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-all {{ request()->routeIs('settings.*') ? 'bg-slate-800 text-white menu-active' : '' }}">
                <i class="fas fa-cog w-4 text-sm"></i>
                <span class="text-sm">الإعدادات</span>
            </a>

            @if(Auth::user()->isAdmin())
            <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-all {{ request()->routeIs('users.*') ? 'bg-slate-800 text-white menu-active' : '' }}">
                <i class="fas fa-users-cog w-4 text-sm"></i>
                <span class="text-sm">إدارة المستخدمين</span>
            </a>
            @endif

        </nav>

        <!-- User Profile (Bottom) -->
        <div class="flex-shrink-0 p-4 border-t border-slate-800 bg-slate-900/80">
            @auth
            <div class="flex items-center gap-3">
                <div class="relative flex-shrink-0">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=667eea&color=fff" 
                         class="w-9 h-9 rounded-full ring-2 ring-blue-500/50">
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-slate-900 rounded-full"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-white text-sm truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ Auth::user()->role_name }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="تسجيل الخروج"
                            class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-red-400 hover:bg-slate-800 rounded-lg transition-all">
                        <i class="fas fa-sign-out-alt text-sm"></i>
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </aside>

    <!-- ===================== FLOATING TOGGLE BUTTON ===================== -->
    <button id="sidebar-toggle-btn" onclick="toggleSidebar()" title="فتح/إغلاق القائمة">
        <i class="fas fa-bars"></i>
    </button>

    <!-- ===================== MAIN CONTENT ===================== -->
    <main id="main-content" class="">

        <!-- Top Bar -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="px-4 md:px-6 py-4 flex items-center justify-between">
                <h1 class="text-xl md:text-2xl font-bold text-gray-800">@yield('page-title', 'لوحة التحكم')</h1>

                <div class="flex items-center gap-2 md:gap-4">
                    <!-- Search -->
                    <div class="relative hidden md:block">
                        <input type="text" 
                               placeholder="بحث سريع..." 
                               class="w-64 px-4 py-2 pr-10 bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-sm">
                        <i class="fas fa-search absolute right-3 top-2.5 text-gray-400 text-sm"></i>
                    </div>

                    <!-- Notifications -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-all">
                            <i class="fas fa-bell text-lg"></i>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute left-0 mt-2 w-72 bg-white rounded-xl shadow-xl border animate-slide-in z-50">
                            <div class="p-4 border-b bg-gradient-to-r from-blue-50 to-purple-50">
                                <h3 class="font-bold text-gray-800 text-sm">الإشعارات</h3>
                            </div>
                            <div class="p-8 text-center text-gray-400">
                                <i class="fas fa-check-circle text-3xl mb-2 text-gray-300"></i>
                                <p class="text-sm">لا توجد إشعارات جديدة</p>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 p-1.5 hover:bg-gray-100 rounded-lg transition-all">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=667eea&color=fff" 
                                 class="w-8 h-8 rounded-full ring-2 ring-gray-200">
                            <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute left-0 mt-2 w-48 bg-white rounded-xl shadow-xl border animate-slide-in z-50">
                            <div class="p-3 border-b bg-gradient-to-r from-blue-50 to-purple-50">
                                <p class="font-semibold text-sm text-gray-800">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ Auth::user()->role_name }}</p>
                            </div>
                            <a href="#" class="flex items-center gap-2 px-4 py-2.5 hover:bg-gray-50 transition-colors text-sm text-gray-700">
                                <i class="fas fa-user-circle text-gray-400 w-4"></i><span>الملف الشخصي</span>
                            </a>
                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-4 py-2.5 hover:bg-gray-50 transition-colors text-sm text-gray-700">
                                <i class="fas fa-cog text-gray-400 w-4"></i><span>الإعدادات</span>
                            </a>
                            @endif
                            <div class="border-t border-gray-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 hover:bg-red-50 text-red-500 transition-colors text-sm">
                                    <i class="fas fa-sign-out-alt w-4"></i><span>تسجيل الخروج</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="p-4 md:p-6">

            @if(session('success'))
            <div class="bg-green-50 border border-green-200 border-r-4 border-r-green-500 text-green-700 px-4 py-3 rounded-lg mb-4 animate-slide-in flex items-center gap-3 shadow-sm">
                <i class="fas fa-check-circle text-lg text-green-500"></i>
                <div class="flex-1"><p class="font-semibold text-sm">نجاح</p><p class="text-xs">{{ session('success') }}</p></div>
                <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-700"><i class="fas fa-times"></i></button>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-50 border border-red-200 border-r-4 border-r-red-500 text-red-700 px-4 py-3 rounded-lg mb-4 animate-slide-in flex items-center gap-3 shadow-sm">
                <i class="fas fa-exclamation-circle text-lg text-red-500"></i>
                <div class="flex-1"><p class="font-semibold text-sm">خطأ</p><p class="text-xs">{{ session('error') }}</p></div>
                <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-700"><i class="fas fa-times"></i></button>
            </div>
            @endif

            @if(session('warning'))
            <div class="bg-yellow-50 border border-yellow-200 border-r-4 border-r-yellow-500 text-yellow-700 px-4 py-3 rounded-lg mb-4 animate-slide-in flex items-center gap-3 shadow-sm">
                <i class="fas fa-exclamation-triangle text-lg text-yellow-500"></i>
                <div class="flex-1"><p class="font-semibold text-sm">تحذير</p><p class="text-xs">{{ session('warning') }}</p></div>
                <button onclick="this.parentElement.remove()" class="text-yellow-400 hover:text-yellow-700"><i class="fas fa-times"></i></button>
            </div>
            @endif

            @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 border-r-4 border-r-blue-500 text-blue-700 px-4 py-3 rounded-lg mb-4 animate-slide-in flex items-center gap-3 shadow-sm">
                <i class="fas fa-info-circle text-lg text-blue-500"></i>
                <div class="flex-1"><p class="font-semibold text-sm">معلومة</p><p class="text-xs">{{ session('info') }}</p></div>
                <button onclick="this.parentElement.remove()" class="text-blue-400 hover:text-blue-700"><i class="fas fa-times"></i></button>
            </div>
            @endif

            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="bg-white border-t mt-8">
            <div class="px-6 py-4 flex items-center justify-between text-xs text-gray-400">
                <p>© {{ date('Y') }} نظام إدارة المخازن. جميع الحقوق محفوظة.</p>
                <p>الإصدار 2.0.0</p>
            </div>
        </footer>
    </main>

    <!-- ===================== SIDEBAR TOGGLE SCRIPT ===================== -->
    <script>
        const sidebar     = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const overlay     = document.getElementById('sidebar-overlay');
        const toggleBtn   = document.getElementById('sidebar-toggle-btn');
        const toggleIcon  = toggleBtn.querySelector('i');

        // Detect desktop
        const isDesktop = () => window.innerWidth >= 1024;

        // Default state: open on desktop, closed on mobile
        let sidebarOpen = isDesktop();

        function applyState() {
            if (sidebarOpen) {
                sidebar.classList.remove('sidebar-hidden');
                mainContent.classList.add('sidebar-pushed');
                toggleBtn.classList.add('is-open');
                toggleIcon.className = 'fas fa-times';
                // Show overlay only on mobile
                overlay.style.display = isDesktop() ? 'none' : 'block';
            } else {
                sidebar.classList.add('sidebar-hidden');
                mainContent.classList.remove('sidebar-pushed');
                toggleBtn.classList.remove('is-open');
                toggleIcon.className = 'fas fa-bars';
                overlay.style.display = 'none';
            }
        }

        function toggleSidebar() {
            sidebarOpen = !sidebarOpen;
            applyState();
        }

        // Close sidebar when overlay clicked (already wired via onclick)
        // But also update state:
        overlay.addEventListener('click', function() {
            sidebarOpen = false;
            applyState();
        });

        // On resize: re-evaluate
        window.addEventListener('resize', function() {
            if (isDesktop()) {
                // On desktop, keep current sidebarOpen state but hide overlay
                overlay.style.display = 'none';
                mainContent.classList.toggle('sidebar-pushed', sidebarOpen);
            } else {
                // On mobile, hide push effect
                mainContent.classList.remove('sidebar-pushed');
                if (sidebarOpen) {
                    overlay.style.display = 'block';
                }
            }
        });

        // Initial state
        applyState();
    </script>

    @stack('scripts')
</body>
</html>