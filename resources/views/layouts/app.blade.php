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

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">

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

        * { font-family: 'Cairo', 'Tajawal', sans-serif; box-sizing: border-box; }

        body {
            background: var(--surface);
            background-image:
                radial-gradient(circle at 20% 20%, rgba(99,102,241,0.06) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(59,130,246,0.05) 0%, transparent 50%);
            min-height: 100vh;
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
    </style>

    @stack('styles')
</head>
<body>

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
    <div class="bar-logo-wrap">
        <!-- Enhanced small badge logo -->
        <svg class="logo-sparkle-anim" width="40" height="40" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="48" fill="url(#badge-bg)"/>
            <circle cx="50" cy="50" r="46" stroke="#bdc5e0" stroke-width="0.9" fill="none" opacity="0.55"/>
            <circle cx="50" cy="50" r="43" stroke="#bdc5e0" stroke-width="0.4" stroke-dasharray="2.5,4" fill="none" opacity="0.3"/>
            <circle cx="50" cy="50" r="40" stroke="#5572ff" stroke-width="0.3" stroke-dasharray="1,8" fill="none" opacity="0.28"/>
            <polyline points="14,72 14,32 50,52 86,32 86,72"
                fill="none" stroke="url(#m-grad-s)" stroke-width="11"
                stroke-linejoin="miter" stroke-miterlimit="10" stroke-linecap="butt"/>
            <!-- Blue peaks -->
            <polyline points="9,31 14,22 19,31" fill="none" stroke="#5572ff" stroke-width="2" stroke-linejoin="round" filter="url(#blue-pk)"/>
            <polyline points="81,31 86,22 91,31" fill="none" stroke="#5572ff" stroke-width="2" stroke-linejoin="round" filter="url(#blue-pk)"/>
            <!-- Diamond sparkles -->
            <path d="M 79,13 L 81,19 L 87,21 L 81,23 L 79,29 L 77,23 L 71,21 L 77,19 Z" fill="white" opacity="0.92" filter="url(#spk)"/>
            <path d="M 21,13 L 23,19 L 29,21 L 23,23 L 21,29 L 19,23 L 13,21 L 19,19 Z" fill="white" opacity="0.92" filter="url(#spk)"/>
            <path d="M 50,5 L 51.5,9.5 L 56,11 L 51.5,12.5 L 50,17 L 48.5,12.5 L 44,11 L 48.5,9.5 Z" fill="white" opacity="0.82" filter="url(#spk)"/>
            <path d="M 13,54 L 14.2,58 L 18,59.2 L 14.2,60.4 L 13,64.4 L 11.8,60.4 L 8,59.2 L 11.8,58 Z" fill="white" opacity="0.62" filter="url(#spk)"/>
            <path d="M 87,54 L 88.2,58 L 92,59.2 L 88.2,60.4 L 87,64.4 L 85.8,60.4 L 82,59.2 L 85.8,58 Z" fill="white" opacity="0.62" filter="url(#spk)"/>
            <!-- Micro dots -->
            <path d="M 39,14 L 39.8,16.5 L 42,17.3 L 39.8,18 L 39,20.5 L 38.2,18 L 36,17.3 L 38.2,16.5 Z" fill="white" opacity="0.55"/>
            <path d="M 61,14 L 61.8,16.5 L 64,17.3 L 61.8,18 L 61,20.5 L 60.2,18 L 58,17.3 L 60.2,16.5 Z" fill="white" opacity="0.55"/>
            <circle cx="37" cy="57" r="1.2" fill="#7b92ff" opacity="0.6"/>
            <circle cx="63" cy="57" r="1.2" fill="#7b92ff" opacity="0.6"/>
        </svg>
    </div>
    <span class="bar-brand-text">MAGZANI</span>
    <div class="pulse-dot"></div>
</div>

<!-- Backdrop -->
<div class="backdrop" id="backdrop" onclick="closeMobileSidebar()"></div>

<!-- ═══ SIDEBAR ═══ -->
<aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="logo-icon" style="background:transparent;box-shadow:none;">
            <svg class="logo-sparkle-anim" width="40" height="40" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="48" fill="url(#badge-bg)"/>
                <circle cx="50" cy="50" r="46" stroke="#bdc5e0" stroke-width="0.9" fill="none" opacity="0.55"/>
                <circle cx="50" cy="50" r="43" stroke="#bdc5e0" stroke-width="0.4" stroke-dasharray="2.5,4" fill="none" opacity="0.3"/>
                <polyline points="14,72 14,32 50,52 86,32 86,72"
                    fill="none" stroke="url(#m-grad-s)" stroke-width="11"
                    stroke-linejoin="miter" stroke-miterlimit="10" stroke-linecap="butt"/>
                <polyline points="9,31 14,22 19,31" fill="none" stroke="#5572ff" stroke-width="2" stroke-linejoin="round" filter="url(#blue-pk)"/>
                <polyline points="81,31 86,22 91,31" fill="none" stroke="#5572ff" stroke-width="2" stroke-linejoin="round" filter="url(#blue-pk)"/>
                <path d="M 79,13 L 81,19 L 87,21 L 81,23 L 79,29 L 77,23 L 71,21 L 77,19 Z" fill="white" opacity="0.92" filter="url(#spk)"/>
                <path d="M 21,13 L 23,19 L 29,21 L 23,23 L 21,29 L 19,23 L 13,21 L 19,19 Z" fill="white" opacity="0.92" filter="url(#spk)"/>
                <path d="M 50,5 L 51.5,9.5 L 56,11 L 51.5,12.5 L 50,17 L 48.5,12.5 L 44,11 L 48.5,9.5 Z" fill="white" opacity="0.82" filter="url(#spk)"/>
                <path d="M 13,54 L 14.2,58 L 18,59.2 L 14.2,60.4 L 13,64.4 L 11.8,60.4 L 8,59.2 L 11.8,58 Z" fill="white" opacity="0.62" filter="url(#spk)"/>
                <path d="M 87,54 L 88.2,58 L 92,59.2 L 88.2,60.4 L 87,64.4 L 85.8,60.4 L 82,59.2 L 85.8,58 Z" fill="white" opacity="0.62" filter="url(#spk)"/>
                <path d="M 39,14 L 39.8,16.5 L 42,17.3 L 39.8,18 L 39,20.5 L 38.2,18 L 36,17.3 L 38.2,16.5 Z" fill="white" opacity="0.55"/>
                <path d="M 61,14 L 61.8,16.5 L 64,17.3 L 61.8,18 L 61,20.5 L 60.2,18 L 58,17.3 L 60.2,16.5 Z" fill="white" opacity="0.55"/>
                <circle cx="37" cy="57" r="1.2" fill="#7b92ff" opacity="0.6"/>
                <circle cx="63" cy="57" r="1.2" fill="#7b92ff" opacity="0.6"/>
            </svg>
        </div>
        <div class="logo-text">
            <h2>MAGZANI</h2>
            <p>نظام إدارة المخازن</p>
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

        <div class="nav-divider"></div>
        <div class="nav-section-label">إدارة المخزون</div>

        <div x-data="{ open: {{ request()->routeIs('warehouses.*','transfers.*','stock-counts.*','movements.*') ? 'true' : 'false' }} }">
            <button class="nav-item {{ request()->routeIs('warehouses.*','transfers.*','stock-counts.*','movements.*') ? 'active' : '' }}"
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
                <a href="{{ route('stock-counts.index') }}" class="sub-item {{ request()->routeIs('stock-counts.index') ? 'active' : '' }}"><span class="dot"></span>إدارة الجرد</a>
                <a href="{{ route('stock-counts.create') }}" class="sub-item {{ request()->routeIs('stock-counts.create') ? 'active' : '' }}"><span class="dot"></span>جرد جديد</a>
                <a href="{{ route('movements.index') }}"    class="sub-item {{ request()->routeIs('movements.*')        ? 'active' : '' }}"><span class="dot"></span>حركات المخزن</a>
            </div>
        </div>

        <div x-data="{ open: {{ request()->routeIs('products.*') ? 'true' : 'false' }} }">
            <button class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}"
                    @click="open = !open" data-tip="المنتجات">
                <div class="icon"><i class="fas fa-box-open"></i></div>
                <span class="label">المنتجات</span>
                <i class="fas fa-chevron-down chevron" :class="open ? 'open' : ''"></i>
            </button>
            <div class="sub-menu" :class="open ? 'open' : ''">
                <a href="{{ route('products.index') }}"             class="sub-item {{ request()->routeIs('products.index')            ? 'active' : '' }}"><span class="dot"></span>قائمة المنتجات</a>
                <a href="{{ route('products.create') }}"            class="sub-item {{ request()->routeIs('products.create')           ? 'active' : '' }}"><span class="dot"></span>إضافة منتج</a>
                <a href="{{ route('products.bulk-price-update') }}"  class="sub-item {{ request()->routeIs('products.bulk-price-update') ? 'active' : '' }}"><span class="dot"></span>تحديث الأسعار</a>
            </div>
        </div>

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
                <a href="{{ route('invoices.sales.index') }}"           class="sub-item {{ request()->routeIs('invoices.sales.*')           ? 'active' : '' }}"><span class="dot"></span>فواتير المبيعات</a>
                <a href="{{ route('invoices.purchases.index') }}"       class="sub-item {{ request()->routeIs('invoices.purchases.*')       ? 'active' : '' }}"><span class="dot"></span>فواتير المشتريات</a>
                <a href="{{ route('invoices.sales-returns.index') }}"   class="sub-item {{ request()->routeIs('invoices.sales-returns.*')   ? 'active' : '' }}"><span class="dot"></span>مرتجعات المبيعات</a>
                <a href="{{ route('invoices.purchase-returns.index') }}" class="sub-item {{ request()->routeIs('invoices.purchase-returns.*') ? 'active' : '' }}"><span class="dot"></span>مرتجعات المشتريات</a>
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
                <a href="{{ route('customers.index') }}" class="sub-item {{ request()->routeIs('customers.*') ? 'active' : '' }}"><span class="dot"></span>العملاء</a>
                <a href="{{ route('suppliers.index') }}" class="sub-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><span class="dot"></span>الموردون</a>
            </div>
        </div>

        <div class="nav-divider"></div>
        <div class="nav-section-label">التحليل والمالية</div>

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
        <!-- Enhanced full logo with sparkles and text -->
        <svg style="position:relative;z-index:1;" width="90" height="90" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="bspk" x="-120%" y="-120%" width="340%" height="340%">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="1.8" result="b1"/>
                    <feMerge><feMergeNode in="b1"/><feMergeNode in="SourceGraphic"/></feMerge>
                </filter>
                <filter id="bblu" x="-80%" y="-80%" width="260%" height="260%">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="2.5" result="b"/>
                    <feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
                </filter>
                <radialGradient id="bnrbg" cx="45%" cy="32%" r="68%">
                    <stop offset="0%" stop-color="#1f2b78"/>
                    <stop offset="55%" stop-color="#151c55"/>
                    <stop offset="100%" stop-color="#090d22"/>
                </radialGradient>
            </defs>
            <circle cx="50" cy="50" r="48" fill="url(#bnrbg)"/>
            <circle cx="50" cy="50" r="46" stroke="#c0cae0" stroke-width="1" fill="none" opacity="0.5"/>
            <circle cx="50" cy="50" r="43" stroke="#c0cae0" stroke-width="0.5" fill="none" opacity="0.2"/>
            <circle cx="50" cy="50" r="40" stroke="#c0cae0" stroke-width="0.4" stroke-dasharray="2.5,5" fill="none" opacity="0.2"/>
            <polyline points="14,72 14,32 50,52 86,32 86,72"
                fill="none" stroke="#dde3f4" stroke-width="11"
                stroke-linejoin="miter" stroke-miterlimit="10" stroke-linecap="butt"/>
            <polyline points="9,31 14,22 19,31" fill="none" stroke="#5572ff" stroke-width="2" stroke-linejoin="round" filter="url(#bblu)"/>
            <polyline points="81,31 86,22 91,31" fill="none" stroke="#5572ff" stroke-width="2" stroke-linejoin="round" filter="url(#bblu)"/>
            <!-- Diamond sparkles -->
            <path d="M 79,13 L 81,19 L 87,21 L 81,23 L 79,29 L 77,23 L 71,21 L 77,19 Z" fill="white" opacity="0.92" filter="url(#bspk)"/>
            <path d="M 21,13 L 23,19 L 29,21 L 23,23 L 21,29 L 19,23 L 13,21 L 19,19 Z" fill="white" opacity="0.92" filter="url(#bspk)"/>
            <path d="M 50,4 L 51.8,9.5 L 57,11.3 L 51.8,13 L 50,18.5 L 48.2,13 L 43,11.3 L 48.2,9.5 Z" fill="white" opacity="0.85" filter="url(#bspk)"/>
            <path d="M 12,53 L 13.5,57.5 L 18,59 L 13.5,60.5 L 12,65 L 10.5,60.5 L 6,59 L 10.5,57.5 Z" fill="white" opacity="0.65" filter="url(#bspk)"/>
            <path d="M 88,53 L 89.5,57.5 L 94,59 L 89.5,60.5 L 88,65 L 86.5,60.5 L 82,59 L 86.5,57.5 Z" fill="white" opacity="0.65" filter="url(#bspk)"/>
            <path d="M 38,13 L 39,16 L 42,17 L 39,18 L 38,21 L 37,18 L 34,17 L 37,16 Z" fill="white" opacity="0.55"/>
            <path d="M 62,13 L 63,16 L 66,17 L 63,18 L 62,21 L 61,18 L 58,17 L 61,16 Z" fill="white" opacity="0.55"/>
            <path d="M 25,74 L 26,77 L 29,78 L 26,79 L 25,82 L 24,79 L 21,78 L 24,77 Z" fill="white" opacity="0.4"/>
            <path d="M 75,74 L 76,77 L 79,78 L 76,79 L 75,82 L 74,79 L 71,78 L 74,77 Z" fill="white" opacity="0.4"/>
            <circle cx="37" cy="57" r="1.2" fill="#7b92ff" opacity="0.6"/>
            <circle cx="63" cy="57" r="1.2" fill="#7b92ff" opacity="0.6"/>
        </svg>

        <div class="logo-banner-text" style="position:relative;z-index:1;">
            <h3>MAGZANI</h3>
            <p>WAREHOUSES &amp; INVOICES</p>
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

@stack('scripts')
</body>
</html>