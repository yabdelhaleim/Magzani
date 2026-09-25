@props([
    'title' => 'Dashboard',
    'breadcrumbs' => [],
    'headerNotifications' => null,
    'headerUnreadCount' => 0,
])

@php
    $headerNotifications = $headerNotifications ?? collect();
    $headerUnreadCount = (int) $headerUnreadCount;
@endphp

<header
    x-data="{
        scrolled: false,
        init() {
            const onScroll = () => { this.scrolled = window.scrollY > 4; };
            window.addEventListener('scroll', onScroll);
            onScroll();
        }
    }"
    :class="scrolled ? 'shadow-2' : ''"
    class="topbar"
>
    {{-- Mobile menu toggle --}}
    <button
        type="button"
        class="lg:hidden w-10 h-10 rounded-xl flex items-center justify-center bg-white border border-ink-200 text-ink-700 hover:bg-ink-50"
        aria-label="Menu"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    {{-- Title — demoted to muted breadcrumb-style (page-header owns the h1) --}}
    <div class="flex-1 min-w-0 hidden sm:block">
        @if(!empty($breadcrumbs))
            <nav class="flex items-center gap-1.5 text-xs text-ink-500">
                @foreach($breadcrumbs as $crumb)
                    @if(!$loop->last)
                        <a href="{{ $crumb['url'] ?? '#' }}" class="hover:text-navy-700 transition-colors">{{ $crumb['label'] }}</a>
                        <svg class="w-3 h-3 text-ink-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    @else
                        <span class="text-ink-800 font-medium">{{ $crumb['label'] }}</span>
                    @endif
                @endforeach
            </nav>
        @endif
        <span class="text-[11px] font-semibold text-ink-500 uppercase tracking-wider truncate block">
            {{ $title }}
        </span>
    </div>

    {{-- Right cluster --}}
    <div class="flex items-center gap-2 ms-auto">
        {{-- Global Search --}}
        <x-ui.global-search />

        {{-- Quick actions --}}
        <div class="hidden md:flex items-center gap-1.5">
            <button class="w-10 h-10 rounded-xl flex items-center justify-center bg-white border border-ink-200 text-ink-700 hover:bg-ink-50 transition-colors" title="New sale">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            </button>
        </div>

        {{-- Notifications --}}
        <x-ui.dropdown width="sm" align="end">
            <x-slot:trigger>
                <button class="relative w-10 h-10 rounded-xl flex items-center justify-center bg-white border border-ink-200 text-ink-700 hover:bg-ink-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($headerUnreadCount > 0)
                        <span class="absolute top-2 end-2 min-w-[16px] h-4 px-1 rounded-full bg-danger ring-2 ring-white text-[10px] font-bold text-white flex items-center justify-center">{{ $headerUnreadCount > 99 ? '99+' : $headerUnreadCount }}</span>
                    @endif
                </button>
            </x-slot:trigger>

            <div class="px-4 py-3 border-b border-ink-200 flex items-center justify-between">
                <div class="text-sm font-semibold text-ink-900">الإشعارات</div>
                @if($headerUnreadCount > 0)
                    <span class="tag tag--danger">{{ $headerUnreadCount }} جديد</span>
                @endif
            </div>
            <div class="max-h-80 overflow-y-auto">
                @forelse($headerNotifications as $notification)
                    @php
                        $type = $notification['type'] ?? 'info';
                        $typeClasses = [
                            'success' => 'bg-success-soft text-success-700',
                            'warning' => 'bg-warning-soft text-warning-700',
                            'danger'  => 'bg-danger-soft text-danger-700',
                            'info'    => 'bg-info-soft text-info-700',
                            'navy'    => 'bg-navy-50 text-navy-700',
                            'brass'   => 'bg-warning-soft text-warning-700',
                        ];
                        $typeClass = $typeClasses[$type] ?? $typeClasses['info'];
                    @endphp
                    <a href="{{ $notification['url'] ?? route('notifications.open', $notification['id']) }}"
                       class="flex items-start gap-3 px-4 py-3 hover:bg-ink-50 transition-colors border-b border-ink-100 {{ empty($notification['is_read']) ? 'bg-info-soft/30' : '' }}">
                        <span class="w-9 h-9 rounded-lg flex-shrink-0 flex items-center justify-center {{ $typeClass }}">
                            <i class="fas {{ $notification['icon'] ?? 'fa-bell' }}"></i>
                        </span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-medium text-ink-900">
                                @if(empty($notification['is_read']))
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-danger me-1.5 align-middle"></span>
                                @endif
                                {{ $notification['title'] }}
                            </span>
                            <span class="block text-xs text-ink-500 mt-0.5 truncate">{{ $notification['message'] }}</span>
                            <span class="block text-xs text-ink-400 mt-1">{{ $notification['created_at'] }}</span>
                        </span>
                    </a>
                @empty
                    <div class="px-4 py-10 text-center">
                        <div class="w-12 h-12 rounded-2xl mx-auto bg-ink-100 text-ink-500 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <div class="text-sm font-semibold text-ink-900">لا توجد إشعارات</div>
                        <div class="text-xs text-ink-500 mt-1">ستظهر هنا الأمور التي تحتاج متابعة.</div>
                    </div>
                @endforelse
            </div>
            <div class="px-4 py-2 border-t border-ink-200 bg-ink-50 text-center flex items-center justify-between gap-2">
                <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-navy-700 hover:text-navy-800">عرض كل الإشعارات</a>
                @if($headerUnreadCount > 0)
                    <form action="{{ route('notifications.read-all') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-ink-600 hover:text-ink-900">تعليم الكل كمقروء</button>
                    </form>
                @endif
            </div>
        </x-ui.dropdown>

        {{-- User menu --}}
        @auth
            <x-ui.dropdown width="md" align="end">
                <x-slot:trigger>
                    <button class="flex items-center gap-2 h-10 px-2 pe-3 rounded-xl bg-white border border-ink-200 hover:bg-ink-50 transition-colors">
                        <x-ui.avatar name="{{ auth()->user()->name }}" size="sm" />
                        <div class="hidden md:block text-start">
                            <div class="text-xs font-semibold text-ink-900 leading-tight">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-ink-500 leading-tight">{{ auth()->user()->role_name ?? '' }}</div>
                        </div>
                        <svg class="hidden md:block w-3 h-3 text-ink-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </x-slot:trigger>

                <div class="px-4 py-3 border-b border-ink-200">
                    <div class="text-sm font-semibold text-ink-900">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-ink-500 truncate">{{ auth()->user()->email }}</div>
                </div>
                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-ink-700 hover:bg-ink-50 transition-colors">
                    <svg class="w-4 h-4 text-ink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    الإعدادات
                </a>
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-ink-700 hover:bg-ink-50 transition-colors">
                    <svg class="w-4 h-4 text-ink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    الملف الشخصي
                </a>
                <div class="border-t border-ink-200 my-1"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 px-4 py-2.5 text-sm text-danger hover:bg-danger-soft transition-colors w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        تسجيل الخروج
                    </button>
                </form>
            </x-ui.dropdown>
        @endauth
    </div>
</header>
