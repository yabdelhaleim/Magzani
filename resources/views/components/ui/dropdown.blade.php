@props([
    'align' => 'end',  // start | end
    'width' => 'md',   // sm | md | lg
])

@php
$widths = [
    'sm' => 'w-44',
    'md' => 'w-56',
    'lg' => 'w-72',
];
$w = $widths[$width] ?? $widths['md'];
@endphp

<div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
    <div @click="open = !open">
        {{ $trigger }}
    </div>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute z-30 mt-2 {{ $w }} rounded-xl bg-white border border-ink-200 shadow-3 py-1.5
               {{ $align === 'end' ? 'end-0' : 'start-0' }}"
        x-cloak
    >
        {{ $slot }}
    </div>
</div>
