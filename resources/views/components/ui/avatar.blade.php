@props([
    'name'  => '',
    'src'   => null,
    'size'  => 'md',  // xs | sm | md | lg | xl
    'alt'   => '',
])

@php
$initials = '';
if ($name) {
    $parts = explode(' ', trim($name));
    $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
    if (empty($initials)) $initials = strtoupper(substr($name, 0, 2));
}
@endphp

<div {{ $attributes->merge(['class' => "avatar avatar--{$size}"]) }}>
    @if($src)
        <img src="{{ $src }}" alt="{{ $alt ?: $name }}" class="w-full h-full object-cover">
    @else
        <span>{{ $initials }}</span>
    @endif
</div>
