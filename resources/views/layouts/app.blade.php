<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام إدارة المخازن')</title>

    @php
        // جلب بيانات الشركة لكل الصفحات
        $company = \App\Models\Company::first();
    @endphp

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap CSS (for warehouse-orders pages) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700;800;900&family=Tajawal:wght@300;400;500;700;800;900&family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Magzani Design System (Inlined to bypass tenant asset loading issues) -->
    <style>
        @include('layouts.theme-css')
    </style>

    <style>
        :root {
            --navy:        #0b1120;
            --navy-2:      #111827;
            --navy-3:      #1a2540;
            --navy-4:      #1f3058;
            --accent:      #3b82f6;
            --accent-2:    #6366f1;
            --gold:        #f59e0b;
            --gold-light:  #fcd34d;
            --surface:     #f0f4ff;
            --surface-2:   #e8eeff;
            --text-main:   #0f172a;
            --text-muted:  #64748b;
            --border:      rgba(255,255,255,0.06);
            --glow:        rgba(59,130,246,0.25);
            --sidebar-w:   270px;
            --sidebar-sm:  72px;
        }

        body, p, span, div, td, th, h1, h2, h3, h4, h5, h6, input, select, textarea, button, a {
            font-family: 'Rubik', 'Tajawal', 'Cairo', sans-serif !important;
        }

        /* Reset for Font Awesome Icons */
        i, .fa, .fas, .far, .fab {
            font-family: 'Font Awesome 6 Free', 'Font Awesome 6 Brands', 'Font Awesome 5 Free', 'Font Awesome 5 Brands', sans-serif !important;
        }

        body {
            background: var(--surface);
            background-image:
                radial-gradient(circle at 20% 20%, rgba(99,102,241,0.06) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(59,130,246,0.05) 0%, transparent 50%);
            min-height: 100vh;
            font-size: 16px !important; /* Larger readable ERP font size */
            color: #0b1120;
        }

        /* Make headings, labels and table headers rich dark navy */
        h1, h2, h3, h4, h5, h6, th, .page-header-left h2, .section-title, .stat-val, .stat-lbl {
            color: #0b1120 !important;
            font-weight: 700 !important;
        }

        /* Glowing Blue Accent for active elements, numbers and prices */
        .text-blue-600, .text-primary, a:hover {
            color: #2563eb !important;
            text-shadow: 0 0 1px rgba(37, 99, 235, 0.2) !important;
        }

        /* ── Sidebar ── */
        .sidebar {
            position: fixed;
            right: 0; top: 0;
            height: 100vh;
            width: var(--sidebar-w);
            background: linear-gradient(180deg, var(--navy) 0%, var(--navy-3) 100%);
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: width 0.35s cubic-bezier(.4,0,.2,1), transform 0.35s cubic-bezier(.4,0,.2,1);
            overflow: hidden;
            box-shadow: -8px 0 40px rgba(0,0,0,0.4);
        }
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(99,102,241,0.8), transparent);
        }
        .sidebar.collapsed { width: var(--sidebar-sm); }
        .sidebar.mobile-hidden { transform: translateX(100%); }

        /* Logo */
        .sidebar-logo {
            padding: 20px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 14px;
            min-height: 72px;
            position: relative;
        }
        .logo-icon {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }
        .logo-text {
            overflow: hidden;
            transition: opacity 0.25s, width 0.35s;
            white-space: nowrap;
        }
        .logo-text h2 {
            color: #fff;
            font-weight: 800;
            font-size: 15px;
            line-height: 1.2;
            letter-spacing: -0.3px;
        }
        .logo-text p {
            color: rgba(255,255,255,0.4);
            font-size: 11px;
            margin-top: 1px;
        }
        .sidebar.collapsed .logo-text { opacity: 0; width: 0; }

        /* Toggle btn */
        .toggle-btn {
            position: absolute;
            left: 14px; top: 50%; transform: translateY(-50%);
            width: 30px; height: 30px;
            border-radius: 8px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.5);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }
        .toggle-btn:hover { background: rgba(255,255,255,0.12); color: #fff; }
        .sidebar.collapsed .toggle-btn { left: 50%; transform: translate(-50%, -50%); }

        /* Nav */
        .nav-scroll {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 12px 10px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
        }
        .nav-scroll::-webkit-scrollbar { width: 4px; }
        .nav-scroll::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
        }

        .nav-section-label {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.25);
            text-transform: uppercase;
            padding: 14px 14px 6px;
            white-space: nowrap;
            transition: opacity 0.2s;
        }
        .sidebar.collapsed .nav-section-label { opacity: 0; }

        .nav-divider {
            height: 1px;
            background: var(--border);
            margin: 8px 10px;
        }

        /* Nav item */
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 10px;
            border-radius: 10px;
            cursor: pointer;
            color: rgba(255,255,255,0.55);
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
            width: 100%;
            border: 1px solid transparent;
            background: none;
            text-align: right;
        }
        .nav-item::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(99,102,241,0.15), transparent);
            opacity: 0;
            transition: opacity 0.2s;
            pointer-events: none;
        }
        .nav-item:hover { color: rgba(255,255,255,0.9); }
        .nav-item:hover::before { opacity: 1; }
        .nav-item.active {
            color: #fff;
            background: linear-gradient(90deg, rgba(99,102,241,0.22), rgba(59,130,246,0.1));
            border-color: rgba(99,102,241,0.22);
        }
        .nav-item.active::after {
            content: '';
            position: absolute;
            right: 0; top: 20%; height: 60%;
            width: 3px;
            background: linear-gradient(180deg, var(--accent-2), var(--accent));
            border-radius: 4px 0 0 4px;
        }

        /* Icon box */
        .nav-item .icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
            background: rgba(255,255,255,0.06);
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            color: rgba(255,255,255,0.6);
        }
        .nav-item:hover .icon {
            background: rgba(99,102,241,0.2);
            color: #a5b4fc;
        }
        .nav-item.active .icon {
            background: linear-gradient(135deg, rgba(99,102,241,0.35), rgba(59,130,246,0.25));
            color: #c7d2fe;
            box-shadow: 0 0 14px rgba(99,102,241,0.3);
        }

        /* Collapsed → center icon */
        .sidebar.collapsed .nav-item {
            justify-content: center;
            padding: 8px 0;
            gap: 0;
        }
        .sidebar.collapsed .nav-scroll {
            padding: 12px 8px;
        }

        .nav-item .label {
            flex: 1;
            overflow: hidden;
            transition: opacity 0.2s, max-width 0.35s cubic-bezier(.4,0,.2,1);
            max-width: 200px;
        }
        .sidebar.collapsed .nav-item .label {
            opacity: 0;
            max-width: 0;
            pointer-events: none;
        }

        .nav-item .chevron {
            font-size: 10px;
            transition: transform 0.3s, opacity 0.2s;
            color: rgba(255,255,255,0.3);
            flex-shrink: 0;
        }
        .sidebar.collapsed .nav-item .chevron { opacity: 0; max-width: 0; overflow: hidden; }
        .chevron.open { transform: rotate(180deg); }

        /* Sub menu */
        .sub-menu {
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(.4,0,.2,1), opacity 0.25s;
            max-height: 0;
            opacity: 0;
        }
        .sub-menu.open { max-height: 600px; opacity: 1; }
        .sidebar.collapsed .sub-menu { max-height: 0; opacity: 0; }

        .sub-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px 8px 10px;
            margin: 1px 0 1px 8px;
            border-radius: 8px;
            color: rgba(255,255,255,0.45);
            font-size: 12.5px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            position: relative;
        }
        .sub-item::before {
            content: '';
            position: absolute;
            right: -8px; top: 50%;
            transform: translateY(-50%);
            width: 1px; height: 70%;
            background: rgba(255,255,255,0.08);
        }
        .sub-item:hover { color: rgba(255,255,255,0.85); background: rgba(255,255,255,0.05); }
        .sub-item.active {
            color: var(--accent);
            background: rgba(59,130,246,0.1);
        }
        .sub-item .dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            flex-shrink: 0;
            transition: all 0.2s;
        }
        .sub-item:hover .dot { background: var(--accent); }
        .sub-item.active .dot { background: var(--accent); box-shadow: 0 0 6px var(--accent); }

        /* Sidebar footer */
        .sidebar-footer {
            padding: 14px;
            border-top: 1px solid var(--border);
            background: rgba(0,0,0,0.2);
        }
        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .user-avatar {
            width: 36px; height: 36px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid rgba(99,102,241,0.4);
        }
        .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .user-info { overflow: hidden; transition: opacity 0.2s; }
        .user-info p { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar.collapsed .user-info { opacity: 0; width: 0; }
        .online-dot {
            width: 8px; height: 8px;
            background: #22c55e;
            border-radius: 50%;
            border: 2px solid var(--navy);
            position: absolute;
            bottom: -1px; right: -1px;
            box-shadow: 0 0 6px #22c55e;
        }

        /* ── Backdrop ── */
        .backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        .backdrop.visible { opacity: 1; pointer-events: all; }

        /* ── Main Content ── */
        .main-content {
            margin-right: var(--sidebar-w);
            transition: margin-right 0.35s cubic-bezier(.4,0,.2,1);
            min-height: 100vh;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .main-content.sidebar-collapsed { margin-right: var(--sidebar-sm); }
        .main-content.sidebar-mobile  { margin-right: 0; }

        /* ── Top Bar ── */
        .topbar {
            position: sticky; top: 0; z-index: 500;
            background: rgba(240,244,255,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(99,102,241,0.1);
            padding: 0 24px;
            height: 68px;
            display: flex; align-items: center; gap: 16px;
            box-shadow: 0 1px 20px rgba(15,23,42,0.06);
        }

        .topbar-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-main);
            flex: 1;
        }

        /* Search */
        .search-wrap {
            position: relative;
        }
        .search-wrap input {
            background: rgba(99,102,241,0.07);
            border: 1px solid rgba(99,102,241,0.15);
            border-radius: 12px;
            padding: 8px 16px 8px 36px;
            font-size: 13px;
            color: var(--text-main);
            width: 220px;
            transition: all 0.25s;
            outline: none;
            font-family: 'Cairo', sans-serif;
        }
        .search-wrap input:focus {
            background: #fff;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
            width: 280px;
        }
        .search-wrap input::placeholder { color: var(--text-muted); }
        .search-wrap .search-icon {
            position: absolute;
            left: 12px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 13px;
            pointer-events: none;
        }

        /* Icon button */
        .icon-btn {
            width: 40px; height: 40px;
            border-radius: 11px;
            background: rgba(99,102,241,0.07);
            border: 1px solid rgba(99,102,241,0.12);
            color: var(--text-muted);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.2s;
            position: relative;
        }
        .icon-btn:hover {
            background: rgba(99,102,241,0.14);
            color: var(--accent);
            border-color: rgba(99,102,241,0.3);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99,102,241,0.15);
        }
        .icon-btn .badge {
            position: absolute;
            top: 6px; right: 6px;
            width: 8px; height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid var(--surface);
            animation: pulseBadge 2s infinite;
        }
        @keyframes pulseBadge {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); }
            50%       { box-shadow: 0 0 0 4px rgba(239,68,68,0); }
        }

        /* Dropdown */
        .dropdown-panel {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            background: #fff;
            border: 1px solid rgba(99,102,241,0.12);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(15,23,42,0.12), 0 4px 16px rgba(99,102,241,0.08);
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            transform: translateY(-8px) scale(0.97);
            transition: all 0.2s cubic-bezier(.4,0,.2,1);
            z-index: 600;
        }
        .dropdown-panel.open {
            opacity: 1;
            pointer-events: all;
            transform: translateY(0) scale(1);
        }

        /* Notif panel */
        .notif-panel { width: 340px; }
        .notif-header {
            padding: 16px 20px;
            background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(59,130,246,0.05));
            border-bottom: 1px solid rgba(99,102,241,0.1);
        }
        .notif-header h3 { font-weight: 700; font-size: 14px; color: var(--text-main); }

        /* User panel */
        .user-panel { width: 220px; }
        .user-panel-header {
            padding: 16px 20px;
            background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(59,130,246,0.05));
            border-bottom: 1px solid rgba(99,102,241,0.1);
        }
        .panel-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 16px;
            color: var(--text-main);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s;
        }
        .panel-link:hover { background: rgba(99,102,241,0.06); color: var(--accent); }
        .panel-link .icon { width: 28px; height: 28px; border-radius: 7px; background: rgba(99,102,241,0.08); display: flex; align-items: center; justify-content: center; font-size: 12px; }
        .panel-link.danger { color: #ef4444; }
        .panel-link.danger:hover { background: rgba(239,68,68,0.06); }
        .panel-link.danger .icon { background: rgba(239,68,68,0.08); }

        /* ── Page body ── */
        .page-body {
            flex: 1;
            min-width: 0;
            padding: 28px;
        }

        /* Toast alerts */
        .toast {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 14px;
            font-size: 13.5px;
            font-weight: 500;
            animation: toastIn 0.4s cubic-bezier(.4,0,.2,1);
            position: relative;
            overflow: hidden;
        }
        @keyframes toastIn {
            from { opacity: 0; transform: translateY(-16px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0)    scale(1); }
        }
        .toast.success { background: #f0fdf4; border: 1px solid #86efac; color: #15803d; }
        .toast.error   { background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; }
        .toast.warning { background: #fffbeb; border: 1px solid #fcd34d; color: #b45309; }
        .toast.info    { background: #eff6ff; border: 1px solid #93c5fd; color: #1d4ed8; }
        .toast-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .toast.success .toast-icon { background: rgba(34,197,94,0.12); }
        .toast.error   .toast-icon { background: rgba(239,68,68,0.12); }
        .toast.warning .toast-icon { background: rgba(245,158,11,0.12); }
        .toast.info    .toast-icon { background: rgba(59,130,246,0.12); }
        .toast-close {
            margin-right: auto;
            cursor: pointer;
            opacity: 0.5;
            transition: opacity 0.15s;
            background: none; border: none; color: inherit;
            font-size: 14px; padding: 2px;
        }
        .toast-close:hover { opacity: 1; }

        /* ── Footer ── */
        .main-footer {
            padding: 18px 28px;
            background: rgba(255,255,255,0.7);
            border-top: 1px solid rgba(99,102,241,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-muted);
        }
        .footer-badge {
            background: linear-gradient(90deg, var(--accent-2), var(--accent));
            color: #fff;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        /* ── Logo Banner ── */
        .logo-banner {
            margin: 32px 28px 0;
            background: linear-gradient(135deg, #0a0d22 0%, #161e52 40%, #0a0d22 100%);
            border-radius: 20px;
            padding: 28px 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 22px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(15,23,42,0.5);
        }
        .logo-banner::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(85,114,255,0.18), rgba(59,130,246,0.1), transparent);
            animation: bannerSweep 4s ease-in-out infinite;
        }
        @keyframes bannerSweep {
            0%   { left: -60%; }
            50%  { left: 100%; }
            100% { left: -60%; }
        }
        .logo-banner::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            border: 1px solid rgba(85,114,255,0.25);
            pointer-events: none;
        }
        .logo-banner .logo-banner-glow {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #5572ff, #3b82f6, #8b5cf6, transparent);
            animation: glowPulse 2s ease-in-out infinite;
        }
        @keyframes glowPulse {
            0%, 100% { opacity: 0.5; }
            50%      { opacity: 1; }
        }
        .logo-banner-text {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .logo-banner-text h3 {
            color: #e8edf8;
            font-size: 18px;
            font-weight: 900;
            margin: 0;
            letter-spacing: 6px;
        }
        .logo-banner-text p {
            color: rgba(255,255,255,0.35);
            font-size: 10px;
            margin: 4px 0 0;
            font-weight: 500;
            letter-spacing: 3px;
        }

        /* ── Bottom Brand Bar ── */
        .bottom-brand-bar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;
            background: linear-gradient(135deg, #0f1535 0%, #090d22 100%);
            padding: 8px 24px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow:
                0 10px 30px rgba(0,0,0,0.4),
                0 0 25px rgba(85,114,255,0.25);
            border: 1px solid rgba(85,114,255,0.2);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .bottom-brand-bar:hover {
            transform: translateX(-50%) translateY(-5px);
            box-shadow:
                0 15px 40px rgba(0,0,0,0.5),
                0 0 35px rgba(85,114,255,0.45);
            border-color: rgba(85,114,255,0.45);
        }
        .bar-logo-wrap {
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            position: relative;
        }
        .bar-brand-text {
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 4px;
            color: #e8edf8;
            text-transform: uppercase;
        }
        .pulse-dot {
            width: 8px; height: 8px;
            background: #22c55e;
            border-radius: 50%;
            position: relative;
        }
        .pulse-dot::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: #22c55e;
            opacity: 0.4;
            animation: pulse-ring 1.5s cubic-bezier(0.455,0.03,0.515,0.955) infinite;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.5); opacity: 0.8; }
            80%, 100% { transform: scale(2.5); opacity: 0; }
        }

        /* Logo sparkle animation */
        .logo-sparkle-anim {
            animation: logoSparkle 3s ease-in-out infinite;
        }
        @keyframes logoSparkle {
            0%, 100% { filter: drop-shadow(0 0 8px rgba(85,114,255,0.4)); }
            50%       { filter: drop-shadow(0 0 18px rgba(85,114,255,0.75)); }
        }

        /* ── Mobile ── */
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-content { margin-right: 0 !important; }
            .topbar { padding: 0 16px; }
            .logo-banner { margin: 24px 16px 0; padding: 20px 24px; gap: 16px; }
        }
        @media (max-width: 640px) {
            .logo-banner { flex-direction: column; text-align: center; gap: 12px; padding: 24px 20px; }
            .page-body { padding: 16px; }
            .topbar-title { font-size: 17px; }
            .search-wrap { display: none; }
        }

        /* Tooltip for collapsed sidebar */
        .nav-item[data-tip]:hover::after {
            content: attr(data-tip);
            position: absolute;
            left: calc(100% + 10px);
            top: 50%;
            transform: translateY(-50%);
            background: rgba(15,23,42,0.95);
            color: #fff;
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 8px;
            white-space: nowrap;
            z-index: 9999;
            pointer-events: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .sidebar:not(.collapsed) .nav-item[data-tip]:hover::after { display: none; }

        /* Page load animation */
        .page-body > * { animation: fadeUp 0.45s ease both; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .bottom-brand-bar { padding: 6px 16px; gap: 10px; bottom: 15px; }
            .bar-brand-text { font-size: 11px; letter-spacing: 2px; }
        }

        /* Feature Gating: Disabled/Faded nav item and Upgrade badge */
        .nav-item-disabled {
            opacity: 0.55;
            cursor: not-allowed !important;
            position: relative;
            pointer-events: auto !important;
        }
        .nav-item-disabled * {
            pointer-events: none;
        }
        .badge-upgrade {
            background: linear-gradient(90deg, #f59e0b, #d97706);
            color: #fff !important;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 6px;
            margin-right: auto;
            box-shadow: 0 0 8px rgba(245, 158, 11, 0.3);
            animation: badgePulse 2s infinite;
            white-space: nowrap;
        }
        @keyframes badgePulse {
            0%, 100% { box-shadow: 0 0 6px rgba(245, 158, 11, 0.3); opacity: 0.9; }
            50% { box-shadow: 0 0 12px rgba(245, 158, 11, 0.7); opacity: 1; }
        }
        .sidebar.collapsed .badge-upgrade {
            display: none !important;
        }
    </style>


    @stack('styles')
    @livewireStyles
</head>
<body class="magzani-v3">

<!-- ══════════════════════════════════════
     SHARED SVG DEFS (sparkle filter used by logos)
     ══════════════════════════════════════ -->
<svg width="0" height="0" style="position:absolute;">
    <defs>
        <filter id="spk" x="-120%" y="-120%" width="340%" height="340%">
            <feGaussianBlur in="SourceGraphic" stdDeviation="1.5" result="b1"/>
            <feGaussianBlur in="SourceGraphic" stdDeviation="0.7" result="b2"/>
            <feMerge><feMergeNode in="b1"/><feMergeNode in="b2"/><feMergeNode in="SourceGraphic"/></feMerge>
        </filter>
        <filter id="spk-lg" x="-120%" y="-120%" width="340%" height="340%">
            <feGaussianBlur in="SourceGraphic" stdDeviation="3.5" result="b1"/>
            <feGaussianBlur in="SourceGraphic" stdDeviation="1.5" result="b2"/>
            <feMerge><feMergeNode in="b1"/><feMergeNode in="b2"/><feMergeNode in="SourceGraphic"/></feMerge>
        </filter>
        <filter id="blue-pk" x="-80%" y="-80%" width="260%" height="260%">
            <feGaussianBlur in="SourceGraphic" stdDeviation="2" result="b"/>
            <feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
        </filter>
        <radialGradient id="badge-bg" cx="45%" cy="32%" r="68%">
            <stop offset="0%" stop-color="#1f2b78"/>
            <stop offset="55%" stop-color="#151c55"/>
            <stop offset="100%" stop-color="#090d22"/>
        </radialGradient>
        <radialGradient id="m-grad-s" cx="50%" cy="0%" r="100%">
            <stop offset="0%" stop-color="#f0f3ff"/>
            <stop offset="100%" stop-color="#c8d0e8"/>
        </radialGradient>
    </defs>
</svg>

<!-- Bottom Brand Bar -->
<div class="bottom-brand-bar">
    <div class="bar-logo-wrap" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
        <!-- Premium Kayan 3D Glowing SVG Logo (White Bg, Blue K) -->
        <svg class="logo-sparkle-anim" width="34" height="34" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 0 5px rgba(37, 99, 235, 0.45));">
            <defs>
                <radialGradient id="kayanWhiteBgSmall" cx="40%" cy="30%" r="75%">
                    <stop offset="0%" stop-color="#ffffff" />
                    <stop offset="100%" stop-color="#f8fafc" />
                </radialGradient>
                <linearGradient id="glowingBlueBorderSmall" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#60a5fa" />
                    <stop offset="100%" stop-color="#1d4ed8" />
                </linearGradient>
                <linearGradient id="kStem3DSmall" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#3b82f6" />
                    <stop offset="100%" stop-color="#1d4ed8" />
                </linearGradient>
                <linearGradient id="kBranchTop3DSmall" x1="0%" y1="100%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#00d2ff" />
                    <stop offset="100%" stop-color="#0066ff" />
                </linearGradient>
                <linearGradient id="kBranchBottom3DSmall" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1d4ed8" />
                    <stop offset="100%" stop-color="#0b2545" />
                </linearGradient>
                <filter id="blueKglowSmall" x="-20%" y="-20%" width="140%" height="140%">
                    <feGaussianBlur stdDeviation="2.5" result="blur" />
                    <feMerge>
                        <feMergeNode in="blur"/>
                        <feMergeNode in="SourceGraphic"/>
                    </feMerge>
                </filter>
            </defs>
            <rect x="4" y="4" width="92" height="92" rx="26" fill="url(#kayanWhiteBgSmall)" stroke="url(#glowingBlueBorderSmall)" stroke-width="2.5" />
            <rect x="8" y="8" width="84" height="84" rx="22" fill="none" stroke="rgba(59, 130, 246, 0.08)" stroke-width="1.5" />
            <g filter="url(#blueKglowSmall)">
                <path d="M 32 24 C 32 22.895 32.895 22 34 22 L 44 22 C 45.105 22 46 22.895 46 24 L 46 76 C 46 77.105 45.105 78 44 78 L 34 78 C 32.895 78 32 77.105 32 76 Z" fill="url(#kStem3DSmall)" />
                <path d="M 44 46 L 68 22 C 68.8 21.2 70 21.2 70.8 22 L 76 27.2 C 76.8 28 76.8 29.2 76 30 L 53 52 Z" fill="url(#kBranchTop3DSmall)" />
                <path d="M 46 48 L 71 74 C 71.8 74.8 71.8 76 71 76.8 L 65.8 82 C 65 82.8 63.8 82.8 63 82 L 44 58 Z" fill="url(#kBranchBottom3DSmall)" />
                <path d="M 44 46 L 53 50 L 44 54 Z" fill="#e0f2fe" opacity="0.9" />
            </g>
            <circle cx="70" cy="25" r="2" fill="#00d2ff" />
        </svg>
    </div>
    <span class="bar-brand-text">KAYAN</span>
    <div class="pulse-dot"></div>
</div>

<!-- Backdrop -->
<div class="backdrop" id="backdrop" onclick="closeMobileSidebar()"></div>

<!-- ═══ SIDEBAR ═══ -->
<aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="logo-icon" style="background:transparent;box-shadow:none;display:flex;align-items:center;justify-content:center;">
            <!-- Premium Kayan 3D Glowing SVG Logo (White Bg, Blue K) -->
            <svg class="logo-sparkle-anim" width="46" height="46" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 0 8px rgba(37, 99, 235, 0.45));">
                <defs>
                    <radialGradient id="kayanWhiteBg" cx="40%" cy="30%" r="75%">
                        <stop offset="0%" stop-color="#ffffff" />
                        <stop offset="100%" stop-color="#f8fafc" />
                    </radialGradient>
                    <linearGradient id="glowingBlueBorder" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#60a5fa" />
                        <stop offset="100%" stop-color="#1d4ed8" />
                    </linearGradient>
                    <linearGradient id="kStem3D" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#3b82f6" />
                        <stop offset="100%" stop-color="#1d4ed8" />
                    </linearGradient>
                    <linearGradient id="kBranchTop3D" x1="0%" y1="100%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#00d2ff" />
                        <stop offset="100%" stop-color="#0066ff" />
                    </linearGradient>
                    <linearGradient id="kBranchBottom3D" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#1d4ed8" />
                        <stop offset="100%" stop-color="#0b2545" />
                    </linearGradient>
                    <filter id="blueKglow" x="-20%" y="-20%" width="140%" height="140%">
                        <feGaussianBlur stdDeviation="3" result="blur" />
                        <feMerge>
                            <feMergeNode in="blur"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>
                </defs>
                <rect x="4" y="4" width="92" height="92" rx="26" fill="url(#kayanWhiteBg)" stroke="url(#glowingBlueBorder)" stroke-width="2.5" />
                <rect x="8" y="8" width="84" height="84" rx="22" fill="none" stroke="rgba(59, 130, 246, 0.08)" stroke-width="1.5" />
                <g filter="url(#blueKglow)">
                    <path d="M 32 24 C 32 22.895 32.895 22 34 22 L 44 22 C 45.105 22 46 22.895 46 24 L 46 76 C 46 77.105 45.105 78 44 78 L 34 78 C 32.895 78 32 77.105 32 76 Z" fill="url(#kStem3D)" />
                    <path d="M 44 46 L 68 22 C 68.8 21.2 70 21.2 70.8 22 L 76 27.2 C 76.8 28 76.8 29.2 76 30 L 53 52 Z" fill="url(#kBranchTop3D)" />
                    <path d="M 46 48 L 71 74 C 71.8 74.8 71.8 76 71 76.8 L 65.8 82 C 65 82.8 63.8 82.8 63 82 L 44 58 Z" fill="url(#kBranchBottom3D)" />
                    <path d="M 44 46 L 53 50 L 44 54 Z" fill="#e0f2fe" opacity="0.9" />
                </g>
                <circle cx="70" cy="25" r="2" fill="#00d2ff" />
            </svg>
        </div>
        <div class="logo-text">
            <h2>{{ $company->name ?? 'KAYAN' }}</h2>
            <p>{{ $company->email ?? 'نظام إدارة المخازن' }}</p>
        </div>
        <button class="toggle-btn" onclick="toggleSidebar()" id="toggleBtn">
            <i class="fas fa-chevron-right" id="toggleIcon"></i>
        </button>
    </div>

    <!-- Navigation -->
    <div class="nav-scroll">

        <a href="{{ route('dashboard') }}"
           class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           data-tip="لوحة التحكم">
            <div class="icon"><i class="fas fa-chart-pie"></i></div>
            <span class="label">لوحة التحكم</span>
        </a>

        @if(function_exists('tenant') && tenant() && tenant()->hasFeature('pos'))
        <div x-data="{ open: {{ request()->routeIs('pos.*') ? 'true' : 'false' }} }">
            <button class="nav-item {{ request()->routeIs('pos.*') ? 'active' : '' }}"
                    @click="open = !open" data-tip="نقاط البيع (POS)">
                <div class="icon"><i class="fas fa-cash-register text-indigo-400"></i></div>
                <span class="label text-indigo-200 font-extrabold">نقاط البيع (POS) ⚡</span>
                <i class="fas fa-chevron-down chevron" :class="open ? 'open' : ''"></i>
            </button>
            <div class="sub-menu" :class="open ? 'open' : ''">
                <a href="{{ route('pos.index') }}" class="sub-item {{ request()->routeIs('pos.index') ? 'active' : '' }}"><span class="dot"></span>شاشة الكاشير</a>
                <a href="{{ route('pos.returns') }}" class="sub-item {{ request()->routeIs('pos.returns') ? 'active' : '' }}"><span class="dot"></span>المرتجعات (POS)</a>
                <a href="{{ route('pos.xreport') }}" class="sub-item {{ request()->routeIs('pos.xreport') ? 'active' : '' }}"><span class="dot"></span>تقرير X اللحظي</a>
                <a href="{{ route('pos.history') }}" class="sub-item {{ request()->routeIs('pos.history') ? 'active' : '' }}"><span class="dot"></span>سجل الورديات</a>
                @if(Auth::user()->isAdmin())
                <a href="{{ route('pos.settings.index') }}" class="sub-item {{ request()->routeIs('pos.settings.*') ? 'active' : '' }}"><span class="dot"></span>إعدادات الكاشير</a>
                @endif
            </div>
        </div>
        @endif

        <div class="nav-divider"></div>
        <div class="nav-section-label">إدارة المخزون</div>

        @if($planFeatures->contains('multi_warehouse') || $planFeatures->contains('warehouses'))
        <div x-data="{ open: {{ request()->routeIs('warehouses.*','transfers.*','stock-counts.*','movements.*','warehouse-orders.*') ? 'true' : 'false' }} }">
            <button class="nav-item {{ request()->routeIs('warehouses.*','transfers.*','stock-counts.*','movements.*','warehouse-orders.*') ? 'active' : '' }}"
                    @click="open = !open" data-tip="المخازن">
                <div class="icon"><i class="fas fa-warehouse"></i></div>
                <span class="label">المخازن والجرد</span>
                <i class="fas fa-chevron-down chevron" :class="open ? 'open' : ''"></i>
            </button>
            <div class="sub-menu" :class="open ? 'open' : ''">
                <a href="{{ route('warehouses.index') }}"   class="sub-item {{ request()->routeIs('warehouses.index')   ? 'active' : '' }}"><span class="dot"></span>قائمة المخازن</a>
                <a href="{{ route('warehouses.create') }}"  class="sub-item {{ request()->routeIs('warehouses.create')  ? 'active' : '' }}"><span class="dot"></span>إضافة مخزن</a>
                <a href="{{ route('transfers.create') }}"   class="sub-item {{ request()->routeIs('transfers.create')   ? 'active' : '' }}"><span class="dot"></span>تحويل بين المخازن</a>
                <a href="{{ route('transfers.index') }}"    class="sub-item {{ request()->routeIs('transfers.index')    ? 'active' : '' }}"><span class="dot"></span>سجل التحويلات</a>
                <a href="{{ route('warehouse-orders.inbound.index') }}" class="sub-item {{ request()->routeIs('warehouse-orders.inbound.*') ? 'active' : '' }}"><span class="dot"></span>أذونات الإدخال</a>
                <a href="{{ route('warehouse-orders.outbound.index') }}" class="sub-item {{ request()->routeIs('warehouse-orders.outbound.*') ? 'active' : '' }}"><span class="dot"></span>أذونات الإخراج</a>
                <a href="{{ route('stock-counts.index') }}" class="sub-item {{ request()->routeIs('stock-counts.index') ? 'active' : '' }}"><span class="dot"></span>إدارة الجرد</a>
                <a href="{{ route('stock-counts.create') }}" class="sub-item {{ request()->routeIs('stock-counts.create') ? 'active' : '' }}"><span class="dot"></span>جرد جديد</a>
                <a href="{{ route('movements.index') }}"    class="sub-item {{ request()->routeIs('movements.*')        ? 'active' : '' }}"><span class="dot"></span>حركات المخزن</a>
            </div>
        </div>
        @else
        <div class="nav-item nav-item-disabled" data-tip="المخازن والجرد (ترقية الباقة)">
            <div class="icon"><i class="fas fa-warehouse"></i></div>
            <span class="label">المخازن والجرد</span>
            <span class="badge-upgrade">ترقية الباقة</span>
        </div>
        @endif

        <div x-data="{ open: {{ request()->routeIs('products.*', 'categories.*') ? 'true' : 'false' }} }">
            <button class="nav-item {{ request()->routeIs('products.*', 'categories.*') ? 'active' : '' }}"
                    @click="open = !open" data-tip="المنتجات">
                <div class="icon"><i class="fas fa-box-open"></i></div>
                <span class="label">المنتجات</span>
                <i class="fas fa-chevron-down chevron" :class="open ? 'open' : ''"></i>
            </button>
            <div class="sub-menu" :class="open ? 'open' : ''">
                <a href="{{ route('products.index') }}"             class="sub-item {{ request()->routeIs('products.index')            ? 'active' : '' }}"><span class="dot"></span>قائمة المنتجات</a>
                <a href="{{ route('products.create') }}"            class="sub-item {{ request()->routeIs('products.create')           ? 'active' : '' }}"><span class="dot"></span>إضافة منتج</a>
                <a href="{{ route('categories.index') }}"           class="sub-item {{ request()->routeIs('categories.*')              ? 'active' : '' }}"><span class="dot"></span>تصنيفات المنتجات</a>
                <a href="{{ route('products.bulk-price-update') }}"  class="sub-item {{ request()->routeIs('products.bulk-price-update') ? 'active' : '' }}"><span class="dot"></span>تحديث الأسعار</a>
            </div>
        </div>

        @if($planFeatures->contains('manufacturing'))
        <div x-data="{ open: {{ request()->routeIs('manufacturing.*','manufacturing-orders.*') ? 'true' : 'false' }} }">
            <button class="nav-item {{ request()->routeIs('manufacturing.*','manufacturing-orders.*') ? 'active' : '' }}"
                    @click="open = !open" data-tip="التصنيع">
                <div class="icon"><i class="fas fa-industry"></i></div>
                <span class="label">التصنيع</span>
                <i class="fas fa-chevron-down chevron" :class="open ? 'open' : ''"></i>
            </button>
            <div class="sub-menu" :class="open ? 'open' : ''">
                <a href="{{ route('manufacturing-orders.index') }}"  class="sub-item {{ request()->routeIs('manufacturing-orders.index')  ? 'active' : '' }}"><span class="dot"></span>أوامر التصنيع</a>
                <a href="{{ route('manufacturing-orders.create') }}" class="sub-item {{ request()->routeIs('manufacturing-orders.create') ? 'active' : '' }}"><span class="dot"></span>إنشاء أمر تصنيع</a>
                <a href="{{ route('manufacturing-orders.raw-materials.index') }}" class="sub-item {{ request()->routeIs('manufacturing-orders.raw-materials.index') ? 'active' : '' }}"><span class="dot"></span>الخامات</a>
                <a href="{{ route('manufacturing-orders.raw-materials.create') }}" class="sub-item {{ request()->routeIs('manufacturing-orders.raw-materials.create') ? 'active' : '' }}"><span class="dot"></span>إنشاء خامة</a>
                <div style="height:1px;background:rgba(255,255,255,0.08);margin:6px 0;"></div>
                <a href="{{ route('manufacturing.index') }}"  class="sub-item {{ request()->routeIs('manufacturing.index')  ? 'active' : '' }}"><span class="dot"></span>حسابات التكلفة</a>
                <a href="{{ route('manufacturing.create') }}" class="sub-item {{ request()->routeIs('manufacturing.create') ? 'active' : '' }}"><span class="dot"></span>حساب جديد</a>
                <div style="height:1px;background:rgba(255,255,255,0.08);margin:6px 0;"></div>
                <a href="{{ route('manufacturing.wood-stocks.index') }}" class="sub-item {{ request()->routeIs('manufacturing.wood-stocks.*') ? 'active' : '' }}"><span class="dot"></span>مخزون الخشب الخام</a>
                <a href="{{ route('manufacturing.wood-dispensings.index') }}" class="sub-item {{ request()->routeIs('manufacturing.wood-dispensings.*') ? 'active' : '' }}"><span class="dot"></span>سجل الصرف</a>
            </div>
        </div>
        @else
        <div class="nav-item nav-item-disabled" data-tip="التصنيع (ترقية الباقة)">
            <div class="icon"><i class="fas fa-industry"></i></div>
            <span class="label">التصنيع</span>
            <span class="badge-upgrade">ترقية الباقة</span>
        </div>
        @endif


        @if(function_exists('tenant') && tenant() && (tenant()->hasFeature('sales') || tenant()->hasFeature('purchases')))
        <div class="nav-divider"></div>
        <div class="nav-section-label">المعاملات التجارية</div>

        <div x-data="{ open: {{ request()->routeIs('invoices.*') ? 'true' : 'false' }} }">
            <button class="nav-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}"
                    @click="open = !open" data-tip="الفواتير">
                <div class="icon"><i class="fas fa-file-invoice"></i></div>
                <span class="label">الفواتير</span>
                <i class="fas fa-chevron-down chevron" :class="open ? 'open' : ''"></i>
            </button>
            <div class="sub-menu" :class="open ? 'open' : ''">
                @if(tenant()->hasFeature('sales'))
                <a href="{{ route('invoices.sales.index') }}"           class="sub-item {{ request()->routeIs('invoices.sales.*')           ? 'active' : '' }}"><span class="dot"></span>فواتير المبيعات</a>
                @endif
                @if(tenant()->hasFeature('purchases'))
                <a href="{{ route('invoices.purchases.index') }}"       class="sub-item {{ request()->routeIs('invoices.purchases.*')       ? 'active' : '' }}"><span class="dot"></span>فواتير المشتريات</a>
                @endif
                @if(tenant()->hasFeature('sales'))
                <a href="{{ route('invoices.sales-returns.index') }}"   class="sub-item {{ request()->routeIs('invoices.sales-returns.*')   ? 'active' : '' }}"><span class="dot"></span>مرتجعات المبيعات</a>
                @endif
                @if(tenant()->hasFeature('purchases'))
                <a href="{{ route('invoices.purchase-returns.index') }}" class="sub-item {{ request()->routeIs('invoices.purchase-returns.*') ? 'active' : '' }}"><span class="dot"></span>مرتجعات المشتريات</a>
                @endif
            </div>
        </div>

        <div x-data="{ open: {{ request()->routeIs('customers.*','suppliers.*') ? 'true' : 'false' }} }">
            <button class="nav-item {{ request()->routeIs('customers.*','suppliers.*') ? 'active' : '' }}"
                    @click="open = !open" data-tip="العملاء">
                <div class="icon"><i class="fas fa-users"></i></div>
                <span class="label">العملاء والموردون</span>
                <i class="fas fa-chevron-down chevron" :class="open ? 'open' : ''"></i>
            </button>
            <div class="sub-menu" :class="open ? 'open' : ''">
                @if(tenant()->hasFeature('sales'))
                <a href="{{ route('customers.index') }}" class="sub-item {{ request()->routeIs('customers.*') ? 'active' : '' }}"><span class="dot"></span>العملاء</a>
                @endif
                @if(tenant()->hasFeature('purchases'))
                <a href="{{ route('suppliers.index') }}" class="sub-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><span class="dot"></span>الموردون</a>
                @endif
            </div>
        </div>
        @endif

        <div class="nav-divider"></div>
        <div class="nav-section-label">التحليل والمالية</div>

        @if($planFeatures->contains('accounting'))
        <div x-data="{ open: {{ request()->routeIs('accounting.*') ? 'true' : 'false' }} }">
            <button class="nav-item {{ request()->routeIs('accounting.*') ? 'active' : '' }}"
                    @click="open = !open" data-tip="الحسابات">
                <div class="icon"><i class="fas fa-coins"></i></div>
                <span class="label">الحسابات</span>
                <i class="fas fa-chevron-down chevron" :class="open ? 'open' : ''"></i>
            </button>
            <div class="sub-menu" :class="open ? 'open' : ''">
                <a href="{{ route('accounting.treasury') }}"       class="sub-item {{ request()->routeIs('accounting.treasury')    ? 'active' : '' }}"><span class="dot"></span>الخزينة</a>
                <a href="{{ route('accounting.payments') }}"       class="sub-item {{ request()->routeIs('accounting.payments')    ? 'active' : '' }}"><span class="dot"></span>المدفوعات</a>
                <a href="{{ route('accounting.expenses.index') }}"  class="sub-item {{ request()->routeIs('accounting.expenses.*') ? 'active' : '' }}"><span class="dot"></span>المصروفات</a>
            </div>
        </div>
        @else
        <div class="nav-item nav-item-disabled" data-tip="الحسابات (ترقية الباقة)">
            <div class="icon"><i class="fas fa-coins"></i></div>
            <span class="label">الحسابات</span>
            <span class="badge-upgrade">ترقية الباقة</span>
        </div>
        @endif

        @if($planFeatures->contains('accounting_advanced'))
        <div x-data="{ open: {{ request()->routeIs('accounting.dashboard') || request()->routeIs('accounting.coa.*') || request()->routeIs('accounting.journal.*') || request()->routeIs('accounting.vouchers.*') || request()->routeIs('accounting.fiscal.*') || request()->routeIs('accounting.fixed-assets.*') || request()->routeIs('accounting.settings.*') || request()->routeIs('accounting.reports.*') ? 'true' : 'false' }} }">
            <button class="nav-item {{ request()->routeIs('accounting.*') && !request()->routeIs('accounting.treasury') && !request()->routeIs('accounting.payments') && !request()->routeIs('accounting.expenses.*') ? 'active' : '' }}"
                    @click="open = !open" data-tip="المحاسبة المتقدمة">
                <div class="icon"><i class="fas fa-calculator"></i></div>
                <span class="label">المحاسبة المتقدمة</span>
                <i class="fas fa-chevron-down chevron" :class="open ? 'open' : ''"></i>
            </button>
            <div class="sub-menu" :class="open ? 'open' : ''">
                <a href="{{ route('accounting.dashboard') }}" class="sub-item {{ request()->routeIs('accounting.dashboard') ? 'active' : '' }}"><span class="dot"></span>لوحة التحكم</a>
                <a href="{{ route('accounting.coa.index') }}" class="sub-item {{ request()->routeIs('accounting.coa.*') ? 'active' : '' }}"><span class="dot"></span>دليل الحسابات</a>
                <a href="{{ route('accounting.journal.index') }}" class="sub-item {{ request()->routeIs('accounting.journal.*') ? 'active' : '' }}"><span class="dot"></span>قيود اليومية</a>
                <a href="{{ route('accounting.vouchers.receipt.index') }}" class="sub-item {{ request()->routeIs('accounting.vouchers.receipt.*') ? 'active' : '' }}"><span class="dot"></span>سندات القبض</a>
                <a href="{{ route('accounting.vouchers.payment.index') }}" class="sub-item {{ request()->routeIs('accounting.vouchers.payment.*') ? 'active' : '' }}"><span class="dot"></span>سندات الصرف</a>
                <a href="{{ route('accounting.fiscal.index') }}" class="sub-item {{ request()->routeIs('accounting.fiscal.*') ? 'active' : '' }}"><span class="dot"></span>الفترات المالية</a>
                <a href="{{ route('accounting.fixed-assets.index') }}" class="sub-item {{ request()->routeIs('accounting.fixed-assets.*') ? 'active' : '' }}"><span class="dot"></span>الأصول الثابتة</a>
                <a href="{{ route('accounting.settings.index') }}" class="sub-item {{ request()->routeIs('accounting.settings.*') ? 'active' : '' }}"><span class="dot"></span>الإعدادات المحاسبية</a>
                
                <div class="nav-divider" style="margin: 4px 10px; opacity: 0.3;"></div>
                <div class="nav-section-label" style="padding: 6px 14px 4px; font-size: 8px;">التقارير المالية</div>
                <a href="{{ route('accounting.reports.trial-balance') }}" class="sub-item {{ request()->routeIs('accounting.reports.trial-balance') ? 'active' : '' }}"><span class="dot"></span>ميزان المراجعة</a>
                <a href="{{ route('accounting.reports.income-statement') }}" class="sub-item {{ request()->routeIs('accounting.reports.income-statement') ? 'active' : '' }}"><span class="dot"></span>قائمة الدخل</a>
                <a href="{{ route('accounting.reports.balance-sheet') }}" class="sub-item {{ request()->routeIs('accounting.reports.balance-sheet') ? 'active' : '' }}"><span class="dot"></span>الميزانية العمومية</a>
                <a href="{{ route('accounting.reports.general-ledger') }}" class="sub-item {{ request()->routeIs('accounting.reports.general-ledger') ? 'active' : '' }}"><span class="dot"></span>دفتر الأستاذ العام</a>
                <a href="{{ route('accounting.reports.partner-ledger') }}" class="sub-item {{ request()->routeIs('accounting.reports.partner-ledger') ? 'active' : '' }}"><span class="dot"></span>كشف حساب شريك</a>
                <a href="{{ route('accounting.reports.audit-trail') }}" class="sub-item {{ request()->routeIs('accounting.reports.audit-trail') ? 'active' : '' }}"><span class="dot"></span>سجل الرقابة والتدقيق</a>
                <a href="{{ route('accounting.reports.aging') }}" class="sub-item {{ request()->routeIs('accounting.reports.aging') ? 'active' : '' }}"><span class="dot"></span>تقادم الديون</a>
            </div>
        </div>
        @endif

        @if($planFeatures->contains('reports_advanced') || $planFeatures->contains('reports'))
        <div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
            <button class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                    @click="open = !open" data-tip="التقارير">
                <div class="icon"><i class="fas fa-chart-bar"></i></div>
                <span class="label">التقارير</span>
                <i class="fas fa-chevron-down chevron" :class="open ? 'open' : ''"></i>
            </button>
            <div class="sub-menu" :class="open ? 'open' : ''">
                <a href="{{ route('reports.inventory') }}"   class="sub-item {{ request()->routeIs('reports.inventory')   ? 'active' : '' }}"><span class="dot"></span>تقرير المخزون</a>
                <a href="{{ route('reports.financial') }}"   class="sub-item {{ request()->routeIs('reports.financial')   ? 'active' : '' }}"><span class="dot"></span>التقرير المالي</a>
                <a href="{{ route('reports.profit-loss') }}" class="sub-item {{ request()->routeIs('reports.profit-loss') ? 'active' : '' }}"><span class="dot"></span>الأرباح والخسائر</a>
            </div>
        </div>
        @else
        <div class="nav-item nav-item-disabled" data-tip="التقارير المتقدمة (ترقية الباقة)">
            <div class="icon"><i class="fas fa-chart-bar"></i></div>
            <span class="label">التقارير المتقدمة</span>
            <span class="badge-upgrade">ترقية الباقة</span>
        </div>
        @endif


        <div class="nav-divider"></div>
        <div class="nav-section-label">النظام</div>

        <a href="{{ route('settings.index') }}"
           class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}"
           data-tip="الإعدادات">
            <div class="icon"><i class="fas fa-sliders-h"></i></div>
            <span class="label">الإعدادات</span>
        </a>

        @if(Auth::user()->isAdmin())
        <a href="{{ route('users.index') }}"
           class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}"
           data-tip="المستخدمين">
            <div class="icon"><i class="fas fa-users-cog"></i></div>
            <span class="label">إدارة المستخدمين</span>
        </a>
        <a href="{{ route('permissions.index') }}"
           class="nav-item {{ request()->routeIs('permissions.*') ? 'active' : '' }}"
           data-tip="الصلاحيات">
            <div class="icon"><i class="fas fa-shield-alt"></i></div>
            <span class="label">إدارة الصلاحيات</span>
        </a>
        @endif

    </div>

    <!-- User Footer -->
    <div class="sidebar-footer">
        @auth
        <div class="user-card">
            <div class="user-avatar" style="position:relative;">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=6366f1&color=fff&bold=true">
                <span class="online-dot"></span>
            </div>
            <div class="user-info">
                <p style="font-size:13px;font-weight:700;color:#fff;margin:0;">{{ Auth::user()->name }}</p>
                <p style="font-size:11px;color:rgba(255,255,255,0.4);margin:0;">{{ Auth::user()->role_name }}</p>
            </div>
        </div>
        @endauth
    </div>

</aside>


<!-- ═══ MAIN CONTENT ═══ -->
<div class="main-content" id="mainContent">

    <!-- Top Bar -->
    <header class="topbar">

        <button class="icon-btn lg:hidden" onclick="openMobileSidebar()" style="margin-left:8px;">
            <i class="fas fa-bars"></i>
        </button>
        <button class="icon-btn hidden lg:flex" onclick="toggleSidebar()" id="desktopToggle">
            <i class="fas fa-bars"></i>
        </button>

        <h1 class="topbar-title">@yield('page-title', 'لوحة التحكم')</h1>

        <div class="search-wrap">
            <input type="text" placeholder="بحث سريع...">
            <i class="fas fa-search search-icon"></i>
        </div>

        <!-- Notifications -->
        <div style="position:relative;" x-data="{ open: false }">
            <button class="icon-btn" @click="open = !open" @click.away="open = false">
                <i class="fas fa-bell"></i>
                <span class="badge"></span>
            </button>
            <div class="dropdown-panel notif-panel" :class="open ? 'open' : ''">
                <div class="notif-header"><h3>الإشعارات</h3></div>
                <div style="padding:40px 20px;text-align:center;color:var(--text-muted);">
                    <div style="width:56px;height:56px;background:rgba(99,102,241,0.08);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:22px;color:var(--accent-2);">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <p style="font-size:13px;font-weight:600;color:var(--text-main);margin:0 0 4px;">لا توجد إشعارات</p>
                    <p style="font-size:12px;margin:0;">أنت على اطلاع دائم بكل شيء</p>
                </div>
            </div>
        </div>

        <!-- User -->
        <div style="position:relative;" x-data="{ open: false }">
            <button class="icon-btn" @click="open = !open" @click.away="open = false"
                    style="width:auto;padding:4px 10px;gap:8px;display:flex;align-items:center;">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=6366f1&color=fff&bold=true"
                     style="width:30px;height:30px;border-radius:8px;">
                <i class="fas fa-chevron-down" style="font-size:10px;"></i>
            </button>
            <div class="dropdown-panel user-panel" :class="open ? 'open' : ''">
                <div class="user-panel-header">
                    <p style="font-weight:700;font-size:13px;color:var(--text-main);margin:0 0 2px;">{{ Auth::user()->name }}</p>
                    <p style="font-size:11px;color:var(--text-muted);margin:0;">{{ Auth::user()->role_name }}</p>
                </div>
                <a href="#" class="panel-link">
                    <div class="icon"><i class="fas fa-user"></i></div>الملف الشخصي
                </a>
                @if(Auth::user()->isAdmin())
                <a href="{{ route('settings.index') }}" class="panel-link">
                    <div class="icon"><i class="fas fa-cog"></i></div>الإعدادات
                </a>
                @endif
                <div style="height:1px;background:rgba(99,102,241,0.08);margin:4px 0;"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="panel-link danger" style="width:100%;border:none;cursor:pointer;background:none;text-align:right;">
                        <div class="icon"><i class="fas fa-sign-out-alt"></i></div>تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>

    </header>

    <!-- Page Body -->
    <div class="page-body">

        @if(session('success'))
        <div class="toast success">
            <div class="toast-icon"><i class="fas fa-check-circle"></i></div>
            <div style="flex:1;">
                <p style="font-weight:700;margin:0 0 2px;">تمت العملية بنجاح</p>
                <p style="margin:0;font-weight:400;opacity:0.8;font-size:13px;">{{ session('success') }}</p>
            </div>
            <button class="toast-close" onclick="this.closest('.toast').remove()"><i class="fas fa-times"></i></button>
        </div>
        @endif

        @if(session('error'))
        <div class="toast error">
            <div class="toast-icon"><i class="fas fa-times-circle"></i></div>
            <div style="flex:1;">
                <p style="font-weight:700;margin:0 0 2px;">حدث خطأ</p>
                <p style="margin:0;font-weight:400;opacity:0.8;font-size:13px;">{{ session('error') }}</p>
            </div>
            <button class="toast-close" onclick="this.closest('.toast').remove()"><i class="fas fa-times"></i></button>
        </div>
        @endif

        @if(session('warning'))
        <div class="toast warning">
            <div class="toast-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div style="flex:1;">
                <p style="font-weight:700;margin:0 0 2px;">تحذير</p>
                <p style="margin:0;font-weight:400;opacity:0.8;font-size:13px;">{{ session('warning') }}</p>
            </div>
            <button class="toast-close" onclick="this.closest('.toast').remove()"><i class="fas fa-times"></i></button>
        </div>
        @endif

        @if(session('info'))
        <div class="toast info">
            <div class="toast-icon"><i class="fas fa-info-circle"></i></div>
            <div style="flex:1;">
                <p style="font-weight:700;margin:0 0 2px;">معلومة</p>
                <p style="margin:0;font-weight:400;opacity:0.8;font-size:13px;">{{ session('info') }}</p>
            </div>
            <button class="toast-close" onclick="this.closest('.toast').remove()"><i class="fas fa-times"></i></button>
        </div>
        @endif

        @yield('content')

    </div>

    <!-- ═══ LOGO BANNER — Enhanced Full Logo ═══ -->
    <div class="logo-banner">
        <!-- Premium Kayan 3D Glowing SVG Logo (White Bg, Blue K - Large) -->
        <svg style="position:relative;z-index:1;filter: drop-shadow(0 0 12px rgba(37, 99, 235, 0.45));" width="90" height="90" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <radialGradient id="kayanWhiteBgLarge" cx="40%" cy="30%" r="75%">
                    <stop offset="0%" stop-color="#ffffff" />
                    <stop offset="100%" stop-color="#f8fafc" />
                </radialGradient>
                <linearGradient id="glowingBlueBorderLarge" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#60a5fa" />
                    <stop offset="100%" stop-color="#1d4ed8" />
                </linearGradient>
                <linearGradient id="kStem3DLarge" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#3b82f6" />
                    <stop offset="100%" stop-color="#1d4ed8" />
                </linearGradient>
                <linearGradient id="kBranchTop3DLarge" x1="0%" y1="100%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#00d2ff" />
                    <stop offset="100%" stop-color="#0066ff" />
                </linearGradient>
                <linearGradient id="kBranchBottom3DLarge" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1d4ed8" />
                    <stop offset="100%" stop-color="#0b2545" />
                </linearGradient>
                <filter id="blueKglowLarge" x="-20%" y="-20%" width="140%" height="140%">
                    <feGaussianBlur stdDeviation="3" result="blur" />
                    <feMerge>
                        <feMergeNode in="blur"/>
                        <feMergeNode in="SourceGraphic"/>
                    </feMerge>
                </filter>
            </defs>
            <rect x="4" y="4" width="92" height="92" rx="26" fill="url(#kayanWhiteBgLarge)" stroke="url(#glowingBlueBorderLarge)" stroke-width="2.5" />
            <rect x="8" y="8" width="84" height="84" rx="22" fill="none" stroke="rgba(59, 130, 246, 0.08)" stroke-width="1.5" />
            <g filter="url(#blueKglowLarge)">
                <path d="M 32 24 C 32 22.895 32.895 22 34 22 L 44 22 C 45.105 22 46 22.895 46 24 L 46 76 C 46 77.105 45.105 78 44 78 L 34 78 C 32.895 78 32 77.105 32 76 Z" fill="url(#kStem3DLarge)" />
                <path d="M 44 46 L 68 22 C 68.8 21.2 70 21.2 70.8 22 L 76 27.2 C 76.8 28 76.8 29.2 76 30 L 53 52 Z" fill="url(#kBranchTop3DLarge)" />
                <path d="M 46 48 L 71 74 C 71.8 74.8 71.8 76 71 76.8 L 65.8 82 C 65 82.8 63.8 82.8 63 82 L 44 58 Z" fill="url(#kBranchBottom3DLarge)" />
                <path d="M 44 46 L 53 50 L 44 54 Z" fill="#e0f2fe" opacity="0.9" />
            </g>
            <circle cx="70" cy="25" r="2" fill="#00d2ff" />
        </svg>
        <div class="logo-banner-text" style="position:relative;z-index:1;">
            <h3>KAYAN</h3>
            <p>نظام إدارة المخازن والمبيعات</p>
        </div>
        <div class="logo-banner-glow"></div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <p style="margin:0;">© {{ date('Y') }} نظام إدارة المخازن — جميع الحقوق محفوظة</p>
        <span class="footer-badge">v 2.0.0</span>
    </footer>

</div>


<script>
    const sidebar     = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const backdrop    = document.getElementById('backdrop');
    const toggleIcon  = document.getElementById('toggleIcon');

    let isDesktop   = window.innerWidth >= 1024;
    let isCollapsed = false;

    function toggleSidebar() {
        if (!isDesktop) { openMobileSidebar(); return; }
        isCollapsed = !isCollapsed;
        sidebar.classList.toggle('collapsed', isCollapsed);
        mainContent.classList.toggle('sidebar-collapsed', isCollapsed);
        toggleIcon.className = isCollapsed ? 'fas fa-chevron-left' : 'fas fa-chevron-right';
    }

    function openMobileSidebar() {
        sidebar.classList.add('mobile-open');
        backdrop.classList.add('visible');
    }

    function closeMobileSidebar() {
        sidebar.classList.remove('mobile-open');
        backdrop.classList.remove('visible');
    }

    window.addEventListener('resize', () => {
        isDesktop = window.innerWidth >= 1024;
        if (isDesktop) closeMobileSidebar();
    });

    // Auto-dismiss toasts
    setTimeout(() => {
        document.querySelectorAll('.toast').forEach(t => {
            t.style.transition = 'opacity 0.5s, transform 0.5s';
            t.style.opacity = '0';
            t.style.transform = 'translateY(-10px)';
            setTimeout(() => t.remove(), 500);
        });
    }, 5000);
</script>

<!-- Bootstrap JS (for warehouse-orders pages) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')
@livewireScripts
</body>
</html>