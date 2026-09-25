@props([
    'value'   => 0,
    'max'     => 100,
    'variant' => 'navy',  // navy | success | warning | danger
    'label'   => null,
])

@php
$percentage = $max > 0 ? min(100, max(0, ($value / $max) * 100)) : 0;
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($label)
        <div class="flex items-center justify-between text-xs mb-1.5">
            <span class="font-medium text-ink-700">{{ $label }}</span>
            <span class="font-semibold text-ink-900">{{ round($percentage) }}%</span>
        </div>
    @endif
    <div class="progress progress--{{ $variant }}">
        <div class="progress__bar" style="width: {{ $percentage }}%"></div>
    </div>
</div>
