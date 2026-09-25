@props([
    'navItems' => [],
])

@php
    use Illuminate\Support\Facades\Route;
    try {
        $company = function_exists('tenant') && tenant()
            ? \App\Models\Company::first()
            : null;
    } catch (\Throwable $e) {
        $company = null;
    }
    $currentRoute = request()->route()?->getName() ?? '';
@endphp

<aside
    x-data="{
        collapsed: localStorage.getItem('sidebar_collapsed') === 'true',
        mobileOpen: false,
        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('sidebar_collapsed', this.collapsed);
        }
    }"
    :class="collapsed ? 'sidebar-collapsed' : ''"
    class="app-shell__sidebar"
>
    {{-- Brand --}}
    <div class="px-4 h-16 flex items-center border-b" style="border-color: var(--sb-border);">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 min-w-0">
            <div class="brand__mark flex-shrink-0" style="width: 38px; height: 38px;">
                <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 6.5C4 5.12 5.12 4 6.5 4H17.5C18.88 4 20 5.12 20 6.5V17.5C20 18.88 18.88 20 17.5 20H6.5C5.12 20 4 18.88 4 17.5V6.5Z" stroke="currentColor" stroke-width="2"/>
                    <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div x-show="!collapsed" x-cloak class="flex flex-col leading-none min-w-0">
                <span class="text-white font-semibold text-sm truncate" style="font-family: 'Fraunces', 'Reem Kufi', serif;">
                    {{ $company->name ?? 'Magzani' }}
                </span>
                <span class="text-xs mt-0.5 truncate" style="color: var(--sb-ink-3); font-family: 'IBM Plex Sans Arabic', sans-serif;">
                    نظام إدارة المخازن
                </span>
            </div>
        </a>

        <button
            type="button"
            @click="toggle()"
            class="ms-auto w-8 h-8 rounded-lg flex items-center justify-center transition-colors hover:bg-white/5"
            style="color: var(--sb-ink-3);"
            aria-label="Toggle sidebar"
        >
            <svg x-show="!collapsed" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
            <svg x-show="collapsed" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4">
        @php
            $renderNav = function($items) use (&$renderNav, $currentRoute) {
                echo '<div class="space-y-0.5">';
                foreach ($items as $item) {
                    $hasChildren = !empty($item['children']);
                    $isActive = ($item['route'] ?? null) === $currentRoute;
                    $labelKey = $item['label'] ?? '';
                    $isUpgrade = ($item['upgrade'] ?? false);

                    if ($hasChildren) {
                        echo '<div x-data="{ open: ' . (in_array(true, array_map(fn($c) => str_starts_with($currentRoute, $c['route'] ?? '___'), $item['children'])) ? 'true' : 'false') . ' }">';
                        echo '<button type="button" @click="open = !open" class="nav-link w-full">';
                        if (!empty($item['icon'])) {
                            echo '<span class="nav-link__icon">' . $item['icon'] . '</span>';
                        }
                        echo '<span x-show="!collapsed" x-cloak class="truncate flex-1 text-start">' . __($labelKey) . '</span>';
                        if (!$isUpgrade) {
                            echo '<svg x-show="!collapsed" :class="open ? \'rotate-180\' : \'\'" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>';
                        }
                        echo '</button>';
                        if ($isUpgrade) {
                            echo '<span x-show="!collapsed" x-cloak class="ms-auto text-xs px-1.5 py-0.5 rounded-md font-semibold" style="background: linear-gradient(90deg, #F59E0B, #D97706); color: #FFFFFF;">ترقية</span>';
                        }
                        echo '<div x-show="open && !collapsed" x-cloak class="ms-4 mt-1 space-y-0.5">';
                        foreach ($item['children'] as $child) {
                            $childActive = ($child['route'] ?? null) === $currentRoute || str_starts_with($currentRoute, $child['route'] ?? '___');
                            echo '<a href="' . (isset($child['route']) ? route($child['route']) : '#') . '" class="nav-link text-xs" style="padding-block: 0.45rem; ' . ($childActive ? 'background: rgba(255,255,255,0.06); color: #FFFFFF;' : '') . '">';
                            echo '<span class="w-1 h-1 rounded-full" style="background: currentColor; opacity: 0.5;"></span>';
                            echo '<span x-show="!collapsed" x-cloak class="truncate flex-1 text-start">' . __($child['label'] ?? '') . '</span>';
                            echo '</a>';
                        }
                        echo '</div>';
                        echo '</div>';
                    } else {
                        echo '<a href="' . (isset($item['route']) ? route($item['route']) : '#') . '" class="nav-link' . ($isActive ? ' is-active' : '') . '">';
                        if (!empty($item['icon'])) {
                            echo '<span class="nav-link__icon">' . $item['icon'] . '</span>';
                        }
                        echo '<span x-show="!collapsed" x-cloak class="truncate flex-1 text-start">' . __($labelKey) . '</span>';
                        if ($isUpgrade) {
                            echo '<span x-show="!collapsed" x-cloak class="ms-auto text-xs px-1.5 py-0.5 rounded-md font-semibold" style="background: linear-gradient(90deg, #F59E0B, #D97706); color: #FFFFFF;">ترقية</span>';
                        }
                        if (!empty($item['badge'])) {
                            echo '<span x-show="!collapsed" x-cloak class="nav-link__badge">' . $item['badge'] . '</span>';
                        }
                        echo '</a>';
                    }
                }
                echo '</div>';
            };
        @endphp

        @foreach($navItems as $group)
            @if(!empty($group['title']))
                <div x-show="!collapsed" x-cloak class="nav-group__title">{{ __($group['title']) }}</div>
            @endif
            @php $renderNav($group['items'] ?? []); @endphp
        @endforeach
    </nav>

    {{-- User Footer --}}
    @auth
        <div class="px-3 py-3 border-t" style="border-color: var(--sb-border);">
            <div class="flex items-center gap-2.5 px-2 py-2 rounded-xl" style="background: rgba(255,255,255,0.04);">
                <x-ui.avatar name="{{ auth()->user()->name }}" size="sm" />
                <div x-show="!collapsed" x-cloak class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="text-xs truncate" style="color: var(--sb-ink-3);">{{ auth()->user()->role_name ?? '' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" x-show="!collapsed" x-cloak>
                    @csrf
                    <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-white/5" style="color: var(--sb-ink-3);" title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    @endauth
</aside>

<style>
    [x-cloak] { display: none !important; }
    .app-shell__sidebar.sidebar-collapsed { width: 5rem; }
    .app-shell__sidebar.sidebar-collapsed ~ .app-shell__main { margin-inline-start: 5rem; }
</style>
