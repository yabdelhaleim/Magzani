<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1B3A5C">
    <meta name="app-version" content="{{ config('app.version', '1.0.0') }}">

    <title>@yield('title', 'Magzani ERP')</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%231B3A5C'/><text x='50' y='68' font-size='62' text-anchor='middle' fill='%23B08D5A' font-family='Georgia' font-weight='700'>M</text></svg>">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js (must be loaded before app.js loads) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('head')
    @livewireStyles
</head>

<body class="antialiased">

    {{-- Atmospheric background layers (fixed, behind app) --}}
    <div class="bg-canvas" aria-hidden="true"></div>
    <div class="bg-grid" aria-hidden="true"></div>
    <div class="bg-mark" aria-hidden="true"></div>

    <div class="app-shell">
        @hasSection('sidebar')
            @yield('sidebar')
        @else
            <x-layout.sidebar :nav-items="app('App\Navigation\NavRegistry')->forTenant()" />
        @endif

        <div class="app-shell__main">
            @hasSection('topbar')
                @yield('topbar')
            @else
                <x-layout.topbar :title="$__env->yieldContent('page-title', __('dashboard.title'))" :breadcrumbs="app('App\Navigation\BreadcrumbRegistry')->current()" />
            @endif

            <main class="flex-1 min-w-0 px-4 sm:px-6 lg:px-8 py-6">
                <div class="app-container">
                    @include('components.flash-messages')

                    @yield('content')
                </div>
            </main>

            <footer class="mt-auto px-6 py-4 border-t border-ink-200 bg-white/60 backdrop-blur-sm">
                <div class="app-container flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-ink-500">
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
