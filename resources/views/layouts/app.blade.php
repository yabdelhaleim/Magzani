<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        * {
            font-family: 'Cairo', sans-serif;
        }
        
        .sidebar-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        
        .sidebar-scrollbar::-webkit-scrollbar-track {
            background: #1f2937;
        }
        
        .sidebar-scrollbar::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 10px;
        }
        
        .sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }

        .animate-slide-in-right {
            animation: slideInRight 0.3s ease-out;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .hover-scale {
            transition: transform 0.2s;
        }

        .hover-scale:hover {
            transform: scale(1.05);
        }

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
            height: 70%;
            background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
            border-radius: 0 4px 4px 0;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: true }">
    
    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'w-64' : 'w-20'" 
           class="fixed right-0 top-0 h-screen bg-gray-900 text-white transition-all duration-300 z-50 sidebar-scrollbar overflow-y-auto">
        
        <!-- Logo & Toggle -->
        <div class="p-4 flex items-center justify-between border-b border-gray-800">
            <div x-show="sidebarOpen" class="flex items-center gap-3 animate-slide-in-right">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center shadow-lg">
                    <i class="fas fa-warehouse text-xl"></i>
                </div>
                <div>
                    <h2 class="font-bold text-lg">نظام المخازن</h2>
                    <p class="text-xs text-gray-400">إدارة متكاملة</p>
                </div>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" 
                    class="text-gray-400 hover:text-white hover:bg-gray-800 p-2 rounded-lg transition-all">
                <i class="fas" :class="sidebarOpen ? 'fa-times' : 'fa-bars'"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="p-4 space-y-2">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-all {{ request()->routeIs('dashboard') ? 'bg-gray-800 menu-active' : '' }}">
                <i class="fas fa-chart-line w-5"></i>
                <span x-show="sidebarOpen">لوحة التحكم</span>
            </a>

            <hr class="border-gray-800 my-4">

            <!-- المخازن والجرد -->
            <div x-data="{ open: {{ request()->routeIs('warehouses.*') || request()->routeIs('transfers.*') || request()->routeIs('stock-counts.*') || request()->routeIs('movements.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-all {{ request()->routeIs('warehouses.*') || request()->routeIs('transfers.*') || request()->routeIs('stock-counts.*') || request()->routeIs('movements.*') ? 'bg-gray-800' : '' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-warehouse w-5"></i>
                        <span x-show="sidebarOpen">المخازن والجرد</span>
                    </div>
                    <i x-show="sidebarOpen" class="fas fa-chevron-down text-xs transition-transform duration-300" 
                       :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" 
                     x-collapse 
                     class="mr-8 mt-2 space-y-1">
                    
                    <a href="{{ route('warehouses.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('warehouses.index') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-list text-xs"></i>
                        <span>قائمة المخازن</span>
                    </a>
                    
                    <a href="{{ route('warehouses.create') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('warehouses.create') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-plus-circle text-xs"></i>
                        <span>إضافة مخزن</span>
                    </a>

                    <hr class="border-gray-700 my-2">
                    
                    <a href="{{ route('transfers.create') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('transfers.create') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-exchange-alt text-xs"></i>
                        <span>تحويل بين المخازن</span>
                    </a>
                    
                    <a href="{{ route('transfers.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('transfers.index') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-history text-xs"></i>
                        <span>سجل التحويلات</span>
                    </a>

                    <hr class="border-gray-700 my-2">
                    
                    <a href="{{ route('stock-counts.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('stock-counts.index') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-clipboard-check text-xs"></i>
                        <span>إدارة الجرد</span>
                    </a>
                    
                    <a href="{{ route('stock-counts.create') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('stock-counts.create') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-plus-square text-xs"></i>
                        <span>جرد جديد</span>
                    </a>

                    <hr class="border-gray-700 my-2">
                    
                    <a href="{{ route('movements.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('movements.index') || request()->routeIs('movements.warehouse') || request()->routeIs('movements.product') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-arrow-right-arrow-left text-xs"></i>
                        <span>حركات المخزن</span>
                    </a>
                </div>
            </div>

            <!-- المنتجات -->
            <div x-data="{ open: {{ request()->routeIs('products.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-all {{ request()->routeIs('products.*') ? 'bg-gray-800' : '' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-box w-5"></i>
                        <span x-show="sidebarOpen">المنتجات</span>
                    </div>
                    <i x-show="sidebarOpen" class="fas fa-chevron-down text-xs transition-transform duration-300" 
                       :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" 
                     x-collapse 
                     class="mr-8 mt-2 space-y-1">
                    
                    <a href="{{ route('products.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('products.index') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-list text-xs"></i>
                        <span>قائمة المنتجات</span>
                    </a>
                    
                    <a href="{{ route('products.create') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('products.create') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-plus-circle text-xs"></i>
                        <span>إضافة منتج</span>
                    </a>

                    <hr class="border-gray-700 my-2">
                    
                    <a href="{{ route('products.bulk-price-update') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('products.bulk-price-update') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-tags text-xs"></i>
                        <span>تحديث الأسعار</span>
                    </a>
                </div>
            </div>

            <!-- الفواتير -->
            <div x-data="{ open: {{ request()->routeIs('invoices.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-all {{ request()->routeIs('invoices.*') ? 'bg-gray-800' : '' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-invoice w-5"></i>
                        <span x-show="sidebarOpen">الفواتير</span>
                    </div>
                    <i x-show="sidebarOpen" class="fas fa-chevron-down text-xs transition-transform duration-300" 
                       :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" 
                     x-collapse 
                     class="mr-8 mt-2 space-y-1">
                    
                    <!-- فواتير المبيعات -->
                    <a href="{{ route('invoices.sales.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('invoices.sales.*') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-cash-register text-xs"></i>
                        <span>فواتير المبيعات</span>
                    </a>
                    
                    <!-- فواتير المشتريات -->
                    <a href="{{ route('invoices.purchases.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('invoices.purchases.*') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-shopping-cart text-xs"></i>
                        <span>فواتير المشتريات</span>
                    </a>

                    <hr class="border-gray-700 my-2">
                    
                    <!-- مرتجعات المبيعات -->
                    <a href="{{ route('invoices.sales-returns.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('invoices.sales-returns.*') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-undo text-xs"></i>
                        <span>مرتجعات المبيعات</span>
                    </a>
                    
                    <!-- مرتجعات المشتريات -->
                    <a href="{{ route('invoices.purchase-returns.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('invoices.purchase-returns.*') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-reply text-xs"></i>
                        <span>مرتجعات المشتريات</span>
                    </a>
                </div>
            </div>

            <!-- العملاء والموردون -->
            <div x-data="{ open: {{ request()->routeIs('customers.*') || request()->routeIs('suppliers.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-all {{ request()->routeIs('customers.*') || request()->routeIs('suppliers.*') ? 'bg-gray-800' : '' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-users w-5"></i>
                        <span x-show="sidebarOpen">العملاء والموردون</span>
                    </div>
                    <i x-show="sidebarOpen" class="fas fa-chevron-down text-xs transition-transform duration-300" 
                       :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" 
                     x-collapse 
                     class="mr-8 mt-2 space-y-1">
                    
                    <a href="{{ route('customers.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('customers.*') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-user-tag text-xs"></i>
                        <span>العملاء</span>
                    </a>
                    
                    <a href="{{ route('suppliers.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('suppliers.*') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-truck text-xs"></i>
                        <span>الموردون</span>
                    </a>
                </div>
            </div>

            <!-- الحسابات -->
            <div x-data="{ open: {{ request()->routeIs('accounting.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-all {{ request()->routeIs('accounting.*') ? 'bg-gray-800' : '' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-money-bill-wave w-5"></i>
                        <span x-show="sidebarOpen">الحسابات</span>
                    </div>
                    <i x-show="sidebarOpen" class="fas fa-chevron-down text-xs transition-transform duration-300" 
                       :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" 
                     x-collapse 
                     class="mr-8 mt-2 space-y-1">
                    
                    <a href="{{ route('accounting.treasury') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('accounting.treasury') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-university text-xs"></i>
                        <span>الخزينة</span>
                    </a>
                    
                    <a href="{{ route('accounting.payments') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('accounting.payments') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-credit-card text-xs"></i>
                        <span>المدفوعات</span>
                    </a>
                    
                    <a href="{{ route('accounting.expenses.index') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('accounting.expenses.*') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-file-invoice-dollar text-xs"></i>
                        <span>المصروفات</span>
                    </a>
                </div>
            </div>

            <!-- التقارير -->
            <div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-all {{ request()->routeIs('reports.*') ? 'bg-gray-800' : '' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span x-show="sidebarOpen">التقارير</span>
                    </div>
                    <i x-show="sidebarOpen" class="fas fa-chevron-down text-xs transition-transform duration-300" 
                       :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" 
                     x-collapse 
                     class="mr-8 mt-2 space-y-1">
                    
                    <a href="{{ route('reports.inventory') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('reports.inventory') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-boxes text-xs"></i>
                        <span>تقرير المخزون</span>
                    </a>
                    
                    <a href="{{ route('reports.financial') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('reports.financial') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-dollar-sign text-xs"></i>
                        <span>التقرير المالي</span>
                    </a>
                    
                    <a href="{{ route('reports.profit-loss') }}" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-all {{ request()->routeIs('reports.profit-loss') ? 'text-blue-400 font-semibold bg-gray-800' : '' }}">
                        <i class="fas fa-chart-line text-xs"></i>
                        <span>الأرباح والخسائر</span>
                    </a>
                </div>
            </div>

            <hr class="border-gray-800 my-4">

            <!-- الإعدادات -->
            <a href="{{ route('settings.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-all {{ request()->routeIs('settings.*') ? 'bg-gray-800 menu-active' : '' }}">
                <i class="fas fa-cog w-5"></i>
                <span x-show="sidebarOpen">الإعدادات</span>
            </a>
        </nav>

        <!-- User Profile -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-800 bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=667eea&color=fff" 
                         class="w-10 h-10 rounded-full ring-2 ring-blue-500">
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-gray-900 rounded-full"></span>
                </div>
                <div x-show="sidebarOpen" class="flex-1 min-w-0">
                    <p class="font-semibold text-sm truncate">المسؤول</p>
                    <p class="text-xs text-gray-400 truncate">مدير النظام</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main :class="sidebarOpen ? 'mr-64' : 'mr-20'" class="transition-all duration-300 min-h-screen">
        
        <!-- Top Bar -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-800">@yield('page-title', 'لوحة التحكم')</h1>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Search -->
                    <div class="relative">
                        <input type="text" 
                               placeholder="بحث سريع..." 
                               class="w-64 px-4 py-2 pr-10 bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                    </div>

                    <!-- Notifications -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-all">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                        
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition
                             class="absolute left-0 mt-2 w-80 bg-white rounded-lg shadow-xl border animate-slide-in">
                            <div class="p-4 border-b bg-gradient-to-r from-blue-50 to-purple-50">
                                <h3 class="font-bold text-gray-800">الإشعارات</h3>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <div class="p-8 text-center text-gray-400">
                                    <i class="fas fa-check-circle text-4xl mb-2"></i>
                                    <p class="text-sm">لا توجد إشعارات جديدة</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="flex items-center gap-2 p-2 hover:bg-gray-100 rounded-lg transition-all">
                            <img src="https://ui-avatars.com/api/?name=Admin&background=667eea&color=fff" 
                                 class="w-8 h-8 rounded-full ring-2 ring-gray-200">
                            <i class="fas fa-chevron-down text-xs text-gray-600"></i>
                        </button>
                        
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition
                             class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-xl border animate-slide-in">
                            <div class="p-3 border-b bg-gradient-to-r from-blue-50 to-purple-50">
                                <p class="font-semibold text-sm text-gray-800">المسؤول</p>
                                <p class="text-xs text-gray-600">مدير النظام</p>
                            </div>
                            <a href="#" class="flex items-center gap-2 px-4 py-3 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-user-circle text-gray-600"></i>
                                <span class="text-sm">الملف الشخصي</span>
                            </a>
                            <a href="#" 
                               class="flex items-center gap-2 px-4 py-3 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-cog text-gray-600"></i>
                                <span class="text-sm">الإعدادات</span>
                            </a>
                            <hr class="border-gray-200">
                            <a href="#" class="w-full flex items-center gap-2 px-4 py-3 hover:bg-red-50 text-red-600 transition-colors">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="text-sm">تسجيل الخروج</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="p-6">
            <!-- Success Message -->
            @if(session('success'))
            <div class="bg-green-100 border-r-4 border-green-500 text-green-700 px-4 py-3 rounded-lg mb-4 animate-slide-in flex items-center gap-3 shadow-sm">
                <i class="fas fa-check-circle text-xl"></i>
                <div class="flex-1">
                    <p class="font-semibold">نجاح</p>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
            <div class="bg-red-100 border-r-4 border-red-500 text-red-700 px-4 py-3 rounded-lg mb-4 animate-slide-in flex items-center gap-3 shadow-sm">
                <i class="fas fa-exclamation-circle text-xl"></i>
                <div class="flex-1">
                    <p class="font-semibold">خطأ</p>
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @endif

            <!-- Warning Message -->
            @if(session('warning'))
            <div class="bg-yellow-100 border-r-4 border-yellow-500 text-yellow-700 px-4 py-3 rounded-lg mb-4 animate-slide-in flex items-center gap-3 shadow-sm">
                <i class="fas fa-exclamation-triangle text-xl"></i>
                <div class="flex-1">
                    <p class="font-semibold">تحذير</p>
                    <p class="text-sm">{{ session('warning') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-yellow-700 hover:text-yellow-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @endif

            <!-- Info Message -->
            @if(session('info'))
            <div class="bg-blue-100 border-r-4 border-blue-500 text-blue-700 px-4 py-3 rounded-lg mb-4 animate-slide-in flex items-center gap-3 shadow-sm">
                <i class="fas fa-info-circle text-xl"></i>
                <div class="flex-1">
                    <p class="font-semibold">معلومة</p>
                    <p class="text-sm">{{ session('info') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-blue-700 hover:text-blue-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @endif

            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="bg-white border-t mt-8">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <p>© {{ date('Y') }} نظام إدارة المخازن. جميع الحقوق محفوظة.</p>
                    <p>الإصدار 2.0.0</p>
                </div>
            </div>
        </footer>
    </main>

    @stack('scripts')
</body>
</html>