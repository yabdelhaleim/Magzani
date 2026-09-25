@props([
    'icon'    => null,
    'title'   => '',
    'desc'    => '',
])

<div class="empty-state">
    @if($icon)
        <div class="empty-state__icon">
            <x-dynamic-component :component="$icon" class="w-8 h-8" />
        </div>
    @endif
    @if($title)
        <div class="empty-state__title">{{ $title }}</div>
    @endif
    @if($desc)
        <div class="empty-state__desc">{{ $desc }}</div>
    @endif

    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
    @isset($actions)
        <div class="flex items-center gap-2 mt-2">{{ $actions }}</div>
    @endisset
    {{ $slot ?? '' }}
</div>