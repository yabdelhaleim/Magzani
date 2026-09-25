@props([
    'id'      => null,
    'title'   => null,
    'size'    => 'md',  // sm | md | lg | xl
    'wire'    => null,  // wire:model to bind open state
])

@php
$id = $id ?? 'modal-' . uniqid();
$sizes = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-lg',
    'lg' => 'max-w-2xl',
    'xl' => 'max-w-4xl',
];
$maxWidth = $sizes[$size] ?? $sizes['md'];
@endphp

<div
    x-data="{ open: false }"
    @if($wire) x-init="$watch('$wire.{{ $wire }}', v => open = v)" @endif
>
    <div x-show="open"
         x-transition.opacity.duration.200ms
         class="modal-backdrop"
         :class="{ 'is-open': open }"
         @click="open = false; @if($wire) $wire.{{ $wire }} = false @endif"
         x-cloak></div>

    <div x-show="open"
         x-transition.duration.200ms
         class="modal"
         :class="{ 'is-open': open }"
         role="dialog"
         aria-modal="true"
         x-cloak>
        <div class="modal__panel {{ $maxWidth }}" @click.stop>
            @if($title)
                <div class="flex items-center justify-between gap-3 px-6 py-4 border-b border-ink-200">
                    <h3 class="text-base font-semibold text-ink-900" style="font-family: 'Fraunces', 'Reem Kufi', serif;">
                        {{ $title }}
                    </h3>
                    <button
                        type="button"
                        class="btn btn--ghost btn--sm btn--icon"
                        @click="open = false; @if($wire) $wire.{{ $wire }} = false @endif"
                        aria-label="Close">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
            <div class="p-6">
                {{ $slot }}
            </div>
            @isset($footer)
                <div class="px-6 py-4 border-t border-ink-200 bg-ink-50 rounded-b-2xl flex items-center justify-end gap-2">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
