@props([
    'id'    => null,
    'title' => null,
    'size'  => 'md',  // sm | md | lg
])

@php
$id = $id ?? 'drawer-' . uniqid();
$sizes = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-2xl',
];
$maxWidth = $sizes[$size] ?? $sizes['md'];
@endphp

<div
    x-data="{ open: false }"
    x-show="open"
    x-transition.opacity.duration.200ms
    @keydown.escape.window="open = false"
    class="fixed inset-0 z-50"
    x-cloak
>
    <div class="modal-backdrop is-open" @click="open = false"></div>

    <div class="drawer {{ $maxWidth }} is-open" @click.stop>
        @if($title)
            <div class="flex items-center justify-between gap-3 px-6 py-4 border-b border-ink-200">
                <h3 class="text-base font-semibold text-ink-900" style="font-family: 'Fraunces', 'Reem Kufi', serif;">
                    {{ $title }}
                </h3>
                <button type="button" class="btn btn--ghost btn--sm btn--icon" @click="open = false" aria-label="Close">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif
        <div class="p-6">
            {{ $slot }}
        </div>
        @isset($footer)
            <div class="px-6 py-4 border-t border-ink-200 bg-ink-50 flex items-center justify-end gap-2">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
