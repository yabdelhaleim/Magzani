@props([
    'variant' => 'default',  // default | elevated | flat | brass | navy
    'padding' => true,
    'hover'   => true,
])

@php
$classes = collect(['card']);
$classes->push("card--{$variant}");
if (!$hover) { $classes->push('hover:!shadow-1'); }
@endphp

<div {{ $attributes->merge(['class' => $classes->implode(' ')]) }}>
    @if(isset($header))
        <div class="card__header">
            {{ $header }}
        </div>
    @endif
    <div class="{{ $padding ? 'card__body' : '' }}">
        {{ $slot }}
    </div>
    @if(isset($footer))
        <div class="card__footer">
            {{ $footer }}
        </div>
    @endif
</div>
