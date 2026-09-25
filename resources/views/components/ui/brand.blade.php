@props([
    'title'    => 'Magzani',
    'subtitle' => null,
    'mark'     => 'M',
    'href'     => null,
    'size'     => 'md',  // sm | md | lg
])

@php
$sizes = [
    'sm' => ['mark' => 'w-8 h-8 text-base',  'text' => 'text-sm'],
    'md' => ['mark' => 'w-9 h-9 text-lg',    'text' => 'text-base'],
    'lg' => ['mark' => 'w-11 h-11 text-xl',  'text' => 'text-lg'],
];
$sz = $sizes[$size] ?? $sizes['md'];
@endphp

<{{ $href ? 'a' : 'div' }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'brand']) }}
>
    <div class="brand__mark {{ $sz['mark'] }}">{{ $mark }}</div>
    <div class="flex flex-col leading-none">
        <span class="brand__text {{ $sz['text'] }}">{{ $title }}</span>
        @if($subtitle)
            <span class="brand__sub">{{ $subtitle }}</span>
        @endif
    </div>
</{{ $href ? 'a' : 'div' }}>
