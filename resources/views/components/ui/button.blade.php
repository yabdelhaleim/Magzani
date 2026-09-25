@props([
    'variant' => 'primary',   // primary | secondary | ghost | danger | success | warning | brass
    'size'    => 'md',        // xs | sm | md | lg | xl
    'type'    => 'button',
    'href'    => null,
    'icon'    => null,        // SVG path or component name
    'iconTrailing' => null,
    'loading' => false,
    'disabled' => false,
    'iconOnly' => false,
])

@php
$tag = $href ? 'a' : 'button';
$classes = collect(['btn']);
$classes->push("btn--{$variant}");
$classes->push($iconOnly ? "btn--icon btn--{$size}" : "btn--{$size}");
if ($loading || $disabled) {
    $classes->push('opacity-60 cursor-not-allowed pointer-events-none');
}
@endphp

<{{ $tag }}
    @if($tag === 'button') type="{{ $type }}" @endif
    @if($href) href="{{ $href }}" @endif
    @if($disabled) disabled @endif
    {{ $attributes->merge(['class' => $classes->implode(' ')]) }}
>
    @if($loading)
        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
    @elseif($icon && !$iconOnly)
        <x-dynamic-component :component="$icon" class="h-4 w-4 flex-shrink-0" />
    @endif

    @if(!$iconOnly)
        <span>{{ $slot }}</span>
    @elseif($icon)
        <x-dynamic-component :component="$icon" class="h-4 w-4" />
    @endif

    @if($iconTrailing && !$iconOnly)
        <x-dynamic-component :component="$iconTrailing" class="h-4 w-4 flex-shrink-0" />
    @endif
</{{ $tag }}>
