{{--
 | Master layout — Atelier Design System (formerly: KAYAN/Magzani-v3).
 |
 | This layout is used by 146+ pages via `@extends('layouts.app')`.
 | It powers the Atelier sidebar, topbar, notifications, and user menu.
 |
 | Sections provided:
 |   - @yield('title')           Page <title>
 |   - @yield('page-title')      H1 in the topbar
 |   - @yield('content')        Main content area
 |   - @stack('styles')         Per-page <style> pushes (legacy)
 |   - @stack('head')           Per-page <head> pushes (atelier)
 |   - @stack('scripts')        Per-page <script> pushes
 |   - @stack('modals')         Per-page modal markup
 |   - @include('components.flash-messages')  Rendered automatically
 |
 | View composers supply:
 |   - $planFeatures            (sensitive: do not change)
 |   - $headerNotifications     (Livewire notification DB feed)
 |   - $headerUnreadCount       (Livewire notification DB feed)
 --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1B3A5C">
    <meta name="app-version" content="{{ config('app.version', '1.0.0') }}">

    <title>@yield('title', 'Magzani ERP')</title>

    {{-- Atelier Favicon (M monogram in navy/brass) --}}
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%231B3A5C'/><text x='50' y='68' font-size='62' text-anchor='middle' fill='%23B08D5A' font-family='Georgia' font-weight='700'>M</text></svg>">

    {{-- Atelier Design System (Vite) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js (must be loaded before app.js loads) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Legacy: <style> pushes from old pages still using @stack('styles') --}}
    @stack('styles')

    {{-- New: per-page <head> pushes --}}
    @stack('head')

    @livewireStyles
</head>

<body class="antialiased">

    <div class="app-shell">
        {{-- Sidebar --}}
        @hasSection('sidebar')
            @yield('sidebar')
        @else
            <x-layout.sidebar :nav-items="app('App\Navigation\NavRegistry')->forTenant()" />
        @endif

        <div class="app-shell__main">
            {{-- Topbar (with notifications + user menu) --}}
            @hasSection('topbar')
                @yield('topbar')
            @else
                <x-layout.topbar
                    :title="$__env->yieldContent('page-title', __('dashboard.title'))"
                    :breadcrumbs="app('App\Navigation\BreadcrumbRegistry')->current()"
                    :header-notifications="$headerNotifications ?? collect()"
                    :header-unread-count="$headerUnreadCount ?? 0"
                />
            @endif

            {{-- Page body --}}
            <main class="flex-1 min-w-0 px-4 sm:px-6 lg:px-8 py-6">
                @include('components.flash-messages')

                @php
                    $canReviewPostingFailures = Auth::check()
                        && (Auth::user()->isAdmin() || Auth::user()->hasPermission('accounting.posting-failures.read'));
                    $showPostingFailureBanner = request()->routeIs('dashboard') && $canReviewPostingFailures;
                    $unresolvedCount = $showPostingFailureBanner
                        ? \Illuminate\Support\Facades\Cache::remember(
                            \App\Models\AccountingPostingFailure::unresolvedCountCacheKey(),
                            60,
                            fn () => \App\Models\AccountingPostingFailure::where('resolved', false)->count()
                        )
                        : 0;
                @endphp

                @if($showPostingFailureBanner && $unresolvedCount > 0)
                    <div class="flex items-center justify-between gap-4 mb-4 p-4 rounded-xl border bg-danger-soft/40 border-danger-soft text-danger-700" role="alert">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-danger-soft text-danger-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-danger-700">يوجد قيود محاسبية معلّقة لم يتم ترحيلها!</h4>
                                <p class="text-xs text-danger-700/80 mt-0.5">هناك <strong>{{ $unresolvedCount }}</strong> قيد محاسبي فشل ترحيله تلقائياً. يرجى مراجعة المشاكل وإعادة المحاولة لتجنب عدم اتساق التقارير المالية.</p>
                            </div>
                        </div>
                        <a href="{{ route('accounting.posting-failures.index') }}" class="btn btn--danger btn--sm">إدارة أخطاء الترحيل</a>
                    </div>
                @endif

                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="px-6 py-4 border-t border-ink-200 bg-white/60 backdrop-blur-sm">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-ink-500">
                    <div>
                        © {{ date('Y') }} <span class="font-semibold text-ink-700">Magzani</span> · جميع الحقوق محفوظة
                    </div>
                    <div class="flex items-center gap-3">
                        <span>v{{ config('app.version', '1.0.0') }}</span>
                        <span class="w-1 h-1 rounded-full bg-ink-300"></span>
                        <span class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-success-500 animate-pulse"></span>
                            متصل
                        </span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @stack('modals')
    @livewireScripts
    @stack('scripts')
</body>
</html>
