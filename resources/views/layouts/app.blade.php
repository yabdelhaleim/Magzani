<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام إدارة المخازن')</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts - Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Cairo', sans-serif; }

        /* ===== SIDEBAR CORE ===== */
        :root {
            --sidebar-w: 260px;
            --sidebar-collapsed-w: 72px;
            --topbar-h: 64px;
            --sidebar-bg: #0f172a;
            --sidebar-hover: rgba(255,255,255,0.07);
            --sidebar-active: rgba(99,102,241,0.2);
            --accent: #6366f1;
            --accent-light: #818cf8;
        }

        body { background: #f1f5f9; overflow-x: hidden; }

        /* Scrollbar */
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        /* ===== SIDEBAR ===== */
        #sidebar {
            position: fixed;
            top: 0; right: 0;
            height: 100vh;
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            z-index: 50;
            display: flex;
            flex-direction: column;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: width, transform;
            box-shadow: -4px 0 24px rgba(0,0,0,0.3);
        }

        /* Desktop collapsed state */
        #sidebar.collapsed {
            width: var(--sidebar-collapsed-w);
        }

        /* Mobile hidden state */
        #sidebar.mobile-hidden {
            transform: translateX(100%);
        }

        /* ===== SIDEBAR LABEL TRANSITIONS ===== */
        .sidebar-label {
            transition: opacity 0.2s ease, max-width 0.3s ease;
            overflow: hidden;
            white-space: nowrap;
            max-width: 200px;
            opacity: 1;
        }
        #sidebar.collapsed .sidebar-label {
            opacity: 0;
            max-width: 0;
            pointer-events: none;
        }

        .sidebar-chevron {
            transition: opacity 0.2s ease, transform 0.3s ease;
            opacity: 1;
        }
        #sidebar.collapsed .sidebar-chevron {
            opacity: 0;
            pointer-events: none;
        }

        /* Sub-menu hidden when collapsed */
        #sidebar.collapsed .sub-menu {
            display: none !important;
        }

        /* Tooltip on hover when collapsed */
        #sidebar.collapsed .nav-item-wrapper {
            position: relative;
        }
        #sidebar.collapsed .nav-item-wrapper::after {
            content: attr(data-tooltip);
            position: absolute;
            right: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%);
            background: #1e293b;
            color: #e2e8f0;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s ease;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 100;
        }
        #sidebar.collapsed .nav-item-wrapper:hover::after {
            opacity: 1;
        }

        /* ===== NAV LINKS ===== */
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            color: #94a3b8;
            transition: all 0.2s ease;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        .nav-link:hover {
            background: var(--sidebar-hover);
            color: #e2e8f0;
        }
        .nav-link.active {
            background: var(--sidebar-active);
            color: var(--accent-light);
        }
        .nav-link.active::before {
            content: '';
            position: absolute;
            right: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 60%;
            background: var(--accent);
            border-radius: 2px 0 0 2px;
        }
        .nav-link .nav-icon {
            width: 20px;
            text-align: center;
            flex-shrink: 0;
            font-size: 15px;
        }

        /* Nav group button */
        .nav-group-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            color: #94a3b8;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            gap: 12px;
            transition: all 0.2s ease;
        }
        .nav-group-btn:hover {
            background: var(--sidebar-hover);
            color: #e2e8f0;
        }
        .nav-group-btn.active {
            color: #e2e8f0;
            background: var(--sidebar-hover);
        }
        .nav-group-btn .btn-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 0;
        }
        .nav-group-btn .nav-icon {
            width: 20px;
            text-align: center;
            flex-shrink: 0;
            font-size: 15px;
        }

        /* Sub menu */
        .sub-menu {
            margin-top: 2px;
            margin-right: 34px;
            padding: 4px 0;
            border-right: 1px solid rgba(255,255,255,0.08);
            padding-right: 12px;
        }
        .sub-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 8px;
            color: #64748b;
            font-size: 13px;
            font-weight: 400;
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .sub-link:hover {
            color: #cbd5e1;
            background: var(--sidebar-hover);
        }
        .sub-link.active {
            color: var(--accent-light);
            background: var(--sidebar-active);
            font-weight: 600;
        }

        /* ===== MAIN CONTENT ===== */
        #main-content {
            margin-right: var(--sidebar-w);
            min-height: 100vh;
            transition: margin-right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #main-content.sidebar-collapsed {
            margin-right: var(--sidebar-collapsed-w);
        }

        /* ===== TOPBAR ===== */
        #topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            height: var(--topbar-h);
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
        }

        /* ===== TOGGLE BUTTON ===== */
        .toggle-btn {
            width: 36px; height: 36px;
            border-radius: 9px;
            border: none;
            background: #f1f5f9;
            color: #475569;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .toggle-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        /* ===== LOGO AREA ===== */
        .logo-area {
            padding: 20px 16px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            min-height: 72px;
        }
        .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(99,102,241,0.4);
        }
        .logo-text { overflow: hidden; }
        .logo-text h2 {
            font-size: 15px;
            font-weight: 700;
            color: #f1f5f9;
            white-space: nowrap;
        }
        .logo-text p {
            font-size: 11px;
            color: #64748b;
            white-space: nowrap;
        }

        /* ===== USER PROFILE (bottom) ===== */
        .user-profile {
            padding: 14px 16px;
            border-top: 1px solid rgba(255,255,255,0.07);
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.03);
            flex-shrink: 0;
        }
        .user-avatar {
            width: 36px; height: 36px;
            border-radius: 10px;
            flex-shrink: 0;
            ring: 2px solid rgba(99,102,241,0.4);
        }
        .user-info { overflow: hidden; flex: 1; }
        .user-info p:first-child {
            font-size: 13px;
            font-weight: 600;
            color: #e2e8f0;
            white-space: nowrap;
        }
        .user-info p:last-child {
            font-size: 11px;
            color: #475569;
            white-space: nowrap;
        }

        /* ===== OVERLAY ===== */
        #overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 49;
            backdrop-filter: blur(2px);
        }
        #overlay.show { display: block; }

        /* ===== MOBILE ===== */
        @media (max-width: 1023px) {
            #main-content {
                margin-right: 0 !important;
            }
            #sidebar {
                width: var(--sidebar-w) !important;
            }
        }

        /* ===== SEARCH BOX ===== */
        .search-box {
            position: relative;
        }
        .search-box input {
            padding: 8px 16px 8px 40px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 13px;
            color: #334155;
            width: 220px;
            transition: all 0.2s ease;
            font-family: 'Cairo', sans-serif;
        }
        .search-box input:focus {
            outline: none;
            border-color: var(--accent);
            background: white;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
            width: 260px;
        }
        .search-box .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 13px;
        }

        /* ===== NOTIFICATION BADGE ===== */
        .notif-badge {
            position: absolute;
            top: 2px; right: 2px;
            width: 8px; height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
        }

        /* ===== ALERTS ===== */
        .alert-item {
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border-right-width: 4px;
            border-right-style: solid;
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Divider */
        .nav-divider {
            height: 1px;
            background: rgba(255,255,255,0.06);
            margin: 8px 0;
        }

        /* Section label */
        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #334155;
            padding: 8px 14px 4px;
            transition: opacity 0.2s ease;
        }
        #sidebar.collapsed .nav-section-label {
            opacity: 0;
        }

        /* Dropdown animation */
        .dropdown-content {
            overflow: hidden;
            transition: max-height 0.25s ease, opacity 0.2s ease;
            max-height: 0;
            opacity: 0;
        }
        .dropdown-content.open {
            max-height: 500px;
            opacity: 1;
        }
    </style>

    @stack('styles')
</head>
<body>

    <!-- Mobile Overlay -->
    <div id="overlay" onclick="closeMobileSidebar()"></div>

    <!-- ===== SIDEBAR ===== -->
    <aside id="sidebar" class="{{ request()->is('/') ? '' : '' }}">
        
        <!-- Logo -->
        <div class="logo-area">
            <div class="logo-icon">
                <i class="fas fa-warehouse text-white text-lg"></i>
            </div>
            <div class="logo-text sidebar-label">
                <h2>نظام المخازن</h2>
                <p>إدارة متكاملة</p>
            </div>
        </div>

        <!-- Navigation (scrollable) -->
        <nav class="flex-1 overflow-y-auto sidebar-scroll p-3 space-y-1 py-4">

            <!-- Dashboard -->
            <div class="nav-item-wrapper" data-tooltip="لوحة التحكم">
                <a href="{{ route('dashboard') }}"
                   class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line nav-icon"></i>
                    <span class="sidebar-label">لوحة التحكم</span>
                </a>
            </div>

            <div class="nav-divider"></div>
            <div class="nav-section-label">إدارة المخزون</div>

            <!-- المخازن والجرد -->
            <div x-data="{ open: {{ request()->routeIs('warehouses.*') || request()->routeIs('transfers.*') || request()->routeIs('stock-counts.*') || request()->routeIs('movements.*') ? 'true' : 'false' }} }">
                <div class="nav-item-wrapper" data-tooltip="المخازن والجرد">
                    <button @click="open = !open"
                            class="nav-group-btn {{ request()->routeIs('warehouses.*') || request()->routeIs('transfers.*') || request()->routeIs('stock-counts.*') || request()->routeIs('movements.*') ? 'active' : '' }}">
                        <div class="btn-left">
                            <i class="fas fa-warehouse nav-icon"></i>
                            <span class="sidebar-label">المخازن والجرد</span>
                        </div>
                        <i class="fas fa-chevron-down sidebar-chevron text-xs" :class="open ? 'rotate-180' : ''" style="transition: transform 0.25s ease;"></i>
                    </button>
                </div>
                <div class="dropdown-content sub-menu" :class="open ? 'open' : ''" x-init="$el.classList.toggle('open', open)">
                    <a href="{{ route('warehouses.index') }}" class="sub-link {{ request()->routeIs('warehouses.index') ? 'active' : '' }}">
                        <i class="fas fa-list text-xs opacity-60"></i> قائمة المخازن
                    </a>
                    <a href="{{ route('warehouses.create') }}" class="sub-link {{ request()->routeIs('warehouses.create') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle text-xs opacity-60"></i> إضافة مخزن
                    </a>
                    <div class="nav-divider mx-2"></div>
                    <a href="{{ route('transfers.create') }}" class="sub-link {{ request()->routeIs('transfers.create') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt text-xs opacity-60"></i> تحويل بين المخازن
                    </a>
                    <a href="{{ route('transfers.index') }}" class="sub-link {{ request()->routeIs('transfers.index') ? 'active' : '' }}">
                        <i class="fas fa-history text-xs opacity-60"></i> سجل التحويلات
                    </a>
                    <div class="nav-divider mx-2"></div>
                    <a href="{{ route('stock-counts.index') }}" class="sub-link {{ request()->routeIs('stock-counts.index') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check text-xs opacity-60"></i> إدارة الجرد
                    </a>
                    <a href="{{ route('stock-counts.create') }}" class="sub-link {{ request()->routeIs('stock-counts.create') ? 'active' : '' }}">
                        <i class="fas fa-plus-square text-xs opacity-60"></i> جرد جديد
                    </a>
                    <div class="nav-divider mx-2"></div>
                    <a href="{{ route('movements.index') }}" class="sub-link {{ request()->routeIs('movements.*') ? 'active' : '' }}">
                        <i class="fas fa-arrow-right-arrow-left text-xs opacity-60"></i> حركات المخزن
                    </a>
                </div>
            </div>

            <!-- المنتجات -->
            <div x-data="{ open: {{ request()->routeIs('products.*') ? 'true' : 'false' }} }">
                <div class="nav-item-wrapper" data-tooltip="المنتجات">
                    <button @click="open = !open"
                            class="nav-group-btn {{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <div class="btn-left">
                            <i class="fas fa-box nav-icon"></i>
                            <span class="sidebar-label">المنتجات</span>
                        </div>
                        <i class="fas fa-chevron-down sidebar-chevron text-xs" :class="open ? 'rotate-180' : ''" style="transition: transform 0.25s ease;"></i>
                    </button>
                </div>
                <div class="dropdown-content sub-menu" :class="open ? 'open' : ''" x-init="$el.classList.toggle('open', open)">
                    <a href="{{ route('products.index') }}" class="sub-link {{ request()->routeIs('products.index') ? 'active' : '' }}">
                        <i class="fas fa-list text-xs opacity-60"></i> قائمة المنتجات
                    </a>
                    <a href="{{ route('products.create') }}" class="sub-link {{ request()->routeIs('products.create') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle text-xs opacity-60"></i> إضافة منتج
                    </a>
                    <div class="nav-divider mx-2"></div>
                    <a href="{{ route('products.bulk-price-update') }}" class="sub-link {{ request()->routeIs('products.bulk-price-update') ? 'active' : '' }}">
                        <i class="fas fa-tags text-xs opacity-60"></i> تحديث الأسعار
                    </a>
                </div>
            </div>

            <div class="nav-divider"></div>
            <div class="nav-section-label">المعاملات</div>

            <!-- الفواتير -->
            <div x-data="{ open: {{ request()->routeIs('invoices.*') ? 'true' : 'false' }} }">
                <div class="nav-item-wrapper" data-tooltip="الفواتير">
                    <button @click="open = !open"
                            class="nav-group-btn {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                        <div class="btn-left">
                            <i class="fas fa-file-invoice nav-icon"></i>
                            <span class="sidebar-label">الفواتير</span>
                        </div>
                        <i class="fas fa-chevron-down sidebar-chevron text-xs" :class="open ? 'rotate-180' : ''" style="transition: transform 0.25s ease;"></i>
                    </button>
                </div>
                <div class="dropdown-content sub-menu" :class="open ? 'open' : ''" x-init="$el.classList.toggle('open', open)">
                    <a href="{{ route('invoices.sales.index') }}" class="sub-link {{ request()->routeIs('invoices.sales.*') ? 'active' : '' }}">
                        <i class="fas fa-cash-register text-xs opacity-60"></i> فواتير المبيعات
                    </a>
                    <a href="{{ route('invoices.purchases.index') }}" class="sub-link {{ request()->routeIs('invoices.purchases.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart text-xs opacity-60"></i> فواتير المشتريات
                    </a>
                    <div class="nav-divider mx-2"></div>
                    <a href="{{ route('invoices.sales-returns.index') }}" class="sub-link {{ request()->routeIs('invoices.sales-returns.*') ? 'active' : '' }}">
                        <i class="fas fa-undo text-xs opacity-60"></i> مرتجعات المبيعات
                    </a>
                    <a href="{{ route('invoices.purchase-returns.index') }}" class="sub-link {{ request()->routeIs('invoices.purchase-returns.*') ? 'active' : '' }}">
                        <i class="fas fa-reply text-xs opacity-60"></i> مرتجعات المشتريات
                    </a>
                </div>
            </div>

            <!-- العملاء والموردون -->
            <div x-data="{ open: {{ request()->routeIs('customers.*') || request()->routeIs('suppliers.*') ? 'true' : 'false' }} }">
                <div class="nav-item-wrapper" data-tooltip="العملاء والموردون">
                    <button @click="open = !open"
                            class="nav-group-btn {{ request()->routeIs('customers.*') || request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <div class="btn-left">
                            <i class="fas fa-users nav-icon"></i>
                            <span class="sidebar-label">العملاء والموردون</span>
                        </div>
                        <i class="fas fa-chevron-down sidebar-chevron text-xs" :class="open ? 'rotate-180' : ''" style="transition: transform 0.25s ease;"></i>
                    </button>
                </div>
                <div class="dropdown-content sub-menu" :class="open ? 'open' : ''" x-init="$el.classList.toggle('open', open)">
                    <a href="{{ route('customers.index') }}" class="sub-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                        <i class="fas fa-user-tag text-xs opacity-60"></i> العملاء
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="sub-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <i class="fas fa-truck text-xs opacity-60"></i> الموردون
                    </a>
                </div>
            </div>

            <div class="nav-divider"></div>
            <div class="nav-section-label">المالية</div>

            <!-- الحسابات -->
            <div x-data="{ open: {{ request()->routeIs('accounting.*') ? 'true' : 'false' }} }">
                <div class="nav-item-wrapper" data-tooltip="الحسابات">
                    <button @click="open = !open"
                            class="nav-group-btn {{ request()->routeIs('accounting.*') ? 'active' : '' }}">
                        <div class="btn-left">
                            <i class="fas fa-money-bill-wave nav-icon"></i>
                            <span class="sidebar-label">الحسابات</span>
                        </div>
                        <i class="fas fa-chevron-down sidebar-chevron text-xs" :class="open ? 'rotate-180' : ''" style="transition: transform 0.25s ease;"></i>
                    </button>
                </div>
                <div class="dropdown-content sub-menu" :class="open ? 'open' : ''" x-init="$el.classList.toggle('open', open)">
                    <a href="{{ route('accounting.treasury') }}" class="sub-link {{ request()->routeIs('accounting.treasury') ? 'active' : '' }}">
                        <i class="fas fa-university text-xs opacity-60"></i> الخزينة
                    </a>
                    <a href="{{ route('accounting.payments') }}" class="sub-link {{ request()->routeIs('accounting.payments') ? 'active' : '' }}">
                        <i class="fas fa-credit-card text-xs opacity-60"></i> المدفوعات
                    </a>
                    <a href="{{ route('accounting.expenses.index') }}" class="sub-link {{ request()->routeIs('accounting.expenses.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar text-xs opacity-60"></i> المصروفات
                    </a>
                </div>
            </div>

            <!-- التقارير -->
            <div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                <div class="nav-item-wrapper" data-tooltip="التقارير">
                    <button @click="open = !open"
                            class="nav-group-btn {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <div class="btn-left">
                            <i class="fas fa-chart-bar nav-icon"></i>
                            <span class="sidebar-label">التقارير</span>
                        </div>
                        <i class="fas fa-chevron-down sidebar-chevron text-xs" :class="open ? 'rotate-180' : ''" style="transition: transform 0.25s ease;"></i>
                    </button>
                </div>
                <div class="dropdown-content sub-menu" :class="open ? 'open' : ''" x-init="$el.classList.toggle('open', open)">
                    <a href="{{ route('reports.inventory') }}" class="sub-link {{ request()->routeIs('reports.inventory') ? 'active' : '' }}">
                        <i class="fas fa-boxes text-xs opacity-60"></i> تقرير المخزون
                    </a>
                    <a href="{{ route('reports.financial') }}" class="sub-link {{ request()->routeIs('reports.financial') ? 'active' : '' }}">
                        <i class="fas fa-dollar-sign text-xs opacity-60"></i> التقرير المالي
                    </a>
                    <a href="{{ route('reports.profit-loss') }}" class="sub-link {{ request()->routeIs('reports.profit-loss') ? 'active' : '' }}">
                        <i class="fas fa-chart-line text-xs opacity-60"></i> الأرباح والخسائر
                    </a>
                </div>
            </div>

            <div class="nav-divider"></div>

            <!-- الإعدادات -->
            <div class="nav-item-wrapper" data-tooltip="الإعدادات">
                <a href="{{ route('settings.index') }}"
                   class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog nav-icon"></i>
                    <span class="sidebar-label">الإعدادات</span>
                </a>
            </div>

        </nav>

        <!-- User Profile -->
        <div class="user-profile">
            <div class="relative flex-shrink-0">
                <img src="https://ui-avatars.com/api/?name=Admin&background=6366f1&color=fff"
                     class="user-avatar w-9 h-9 rounded-lg ring-2 ring-indigo-500/30">
                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-slate-900 rounded-full"></span>
            </div>
            <div class="user-info sidebar-label">
                <p>المسؤول</p>
                <p>مدير النظام</p>
            </div>
            <button class="sidebar-label mr-auto p-1.5 rounded-lg text-slate-500 hover:text-slate-300 hover:bg-white/10 transition-all" style="transition: all 0.2s ease;">
                <i class="fas fa-ellipsis-v text-xs"></i>
            </button>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div id="main-content">

        <!-- Top Bar -->
        <header id="topbar">
            <!-- Toggle Button -->
            <button class="toggle-btn" onclick="toggleSidebar()" id="toggle-btn" title="تبديل القائمة الجانبية">
                <i class="fas fa-bars" id="toggle-icon"></i>
            </button>

            <!-- Page Title -->
            <h1 class="text-lg font-bold text-gray-800 flex-1 truncate">@yield('page-title', 'لوحة التحكم')</h1>

            <div class="flex items-center gap-2 mr-auto">
                <!-- Search (desktop) -->
                <div class="search-box hidden md:block">
                    <input type="text" placeholder="بحث سريع...">
                    <i class="fas fa-search search-icon"></i>
                </div>

                <!-- Search (mobile) -->
                <button class="toggle-btn md:hidden">
                    <i class="fas fa-search text-sm"></i>
                </button>

                <!-- Notifications -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="toggle-btn relative">
                        <i class="fas fa-bell text-sm"></i>
                        <span class="notif-badge"></span>
                    </button>

                    <div x-show="open"
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 origin-top-left overflow-hidden">
                        <div class="p-4 border-b bg-gradient-to-l from-indigo-50 to-purple-50">
                            <h3 class="font-bold text-gray-800 text-sm">الإشعارات</h3>
                        </div>
                        <div class="p-10 text-center text-gray-400">
                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-bell-slash text-gray-400"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-500">لا توجد إشعارات جديدة</p>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="flex items-center gap-2 pl-3 pr-2 py-1.5 hover:bg-gray-100 rounded-xl transition-all">
                        <img src="https://ui-avatars.com/api/?name=Admin&background=6366f1&color=fff"
                             class="w-8 h-8 rounded-lg">
                        <div class="hidden md:block text-right">
                            <p class="text-xs font-semibold text-gray-800 leading-tight">المسؤول</p>
                            <p class="text-[10px] text-gray-500">مدير النظام</p>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] text-gray-400 hidden md:block"></i>
                    </button>

                    <div x-show="open"
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden origin-top-left">
                        <div class="p-3 border-b bg-gradient-to-l from-indigo-50 to-purple-50">
                            <p class="font-semibold text-sm text-gray-800">المسؤول</p>
                            <p class="text-xs text-gray-500">مدير النظام</p>
                        </div>
                        <a href="#" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors text-sm text-gray-700">
                            <i class="fas fa-user-circle text-gray-400 w-4"></i> الملف الشخصي
                        </a>
                        <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors text-sm text-gray-700">
                            <i class="fas fa-cog text-gray-400 w-4"></i> الإعدادات
                        </a>
                        <hr class="border-gray-100">
                        <a href="#" class="flex items-center gap-3 px-4 py-2.5 hover:bg-red-50 transition-colors text-sm text-red-600">
                            <i class="fas fa-sign-out-alt w-4"></i> تسجيل الخروج
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="p-4 md:p-6">

            <!-- Alerts -->
            @if(session('success'))
            <div class="alert-item bg-green-50 border-green-500 text-green-800 shadow-sm">
                <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-sm">تم بنجاح</p>
                    <p class="text-xs opacity-80 mt-0.5">{{ session('success') }}</p>
                </div>
                <button onclick="this.closest('.alert-item').remove()" class="text-green-500 hover:text-green-700 p-1">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert-item bg-red-50 border-red-500 text-red-800 shadow-sm">
                <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-600"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-sm">حدث خطأ</p>
                    <p class="text-xs opacity-80 mt-0.5">{{ session('error') }}</p>
                </div>
                <button onclick="this.closest('.alert-item').remove()" class="text-red-500 hover:text-red-700 p-1">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            @endif

            @if(session('warning'))
            <div class="alert-item bg-amber-50 border-amber-500 text-amber-800 shadow-sm">
                <div class="w-9 h-9 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-amber-600"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-sm">تحذير</p>
                    <p class="text-xs opacity-80 mt-0.5">{{ session('warning') }}</p>
                </div>
                <button onclick="this.closest('.alert-item').remove()" class="text-amber-500 hover:text-amber-700 p-1">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            @endif

            @if(session('info'))
            <div class="alert-item bg-blue-50 border-blue-500 text-blue-800 shadow-sm">
                <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-600"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-sm">معلومة</p>
                    <p class="text-xs opacity-80 mt-0.5">{{ session('info') }}</p>
                </div>
                <button onclick="this.closest('.alert-item').remove()" class="text-blue-500 hover:text-blue-700 p-1">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            @endif

            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="border-t bg-white/70 backdrop-blur-sm mt-8">
            <div class="px-6 py-4 flex items-center justify-between text-xs text-gray-400">
                <p>© {{ date('Y') }} نظام إدارة المخازن — جميع الحقوق محفوظة</p>
                <p class="bg-gray-100 px-2 py-1 rounded-md font-mono">v2.0.0</p>
            </div>
        </footer>
    </div>

    <script>
        // ===== SIDEBAR STATE MANAGEMENT =====
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const overlay = document.getElementById('overlay');
        const isMobile = () => window.innerWidth < 1024;

        // Load saved state
        let isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';

        function initSidebar() {
            if (isMobile()) {
                // Mobile: always start hidden
                sidebar.classList.add('mobile-hidden');
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('sidebar-collapsed');
            } else {
                // Desktop: use saved state
                sidebar.classList.remove('mobile-hidden');
                if (isCollapsed) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('sidebar-collapsed');
                } else {
                    sidebar.classList.remove('collapsed');
                    mainContent.classList.remove('sidebar-collapsed');
                }
            }
        }

        function toggleSidebar() {
            if (isMobile()) {
                // Mobile: show/hide with overlay
                const isHidden = sidebar.classList.contains('mobile-hidden');
                if (isHidden) {
                    sidebar.classList.remove('mobile-hidden');
                    overlay.classList.add('show');
                } else {
                    closeMobileSidebar();
                }
            } else {
                // Desktop: collapse/expand
                isCollapsed = !isCollapsed;
                localStorage.setItem('sidebar-collapsed', isCollapsed);
                sidebar.classList.toggle('collapsed', isCollapsed);
                mainContent.classList.toggle('sidebar-collapsed', isCollapsed);
            }
        }

        function closeMobileSidebar() {
            sidebar.classList.add('mobile-hidden');
            overlay.classList.remove('show');
        }

        // Handle resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(initSidebar, 100);
        });

        // Init on load
        initSidebar();
    </script>

    @stack('scripts')
</body>
</html>