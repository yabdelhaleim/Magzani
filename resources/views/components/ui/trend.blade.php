@props([
    'value' => 0,         // percentage change
    'trend' => 'flat',    // up | down | flat
    'label' => null,
])

<span {{ $attributes->merge(['class' => "trend trend--{$trend}"]) }}>
    @if($trend === 'up')
        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M9 7h8v8" stroke-linecap="round" stroke-linejoin="round"/></svg>
    @elseif($trend === 'down')
        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 7L7 17M15 17H7V9" stroke-linecap="round" stroke-linejoin="round"/></svg>
    @else
        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14" stroke-linecap="round"/></svg>
    @endif
    <span>{{ number_format($value, 1) }}%</span>
    @if($label)<span class="text-ink-500 font-normal ms-1">{{ $label }}</span>@endif
</span>
