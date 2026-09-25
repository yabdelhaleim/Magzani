@props([
    'label'      => '',
    'value'      => '0',
    'suffix'     => null,
    'delta'      => null,
    'deltaLabel' => null,
    'trend'      => null,    // 'up' | 'down' | 'flat'
    'icon'       => null,
    'variant'    => 'navy',  // navy | brass | success | warning | danger | info
    'loading'    => false,
    'href'       => null,
])

@php
    $trendDirection = $trend ?? 'flat';
    $trendIcon = match($trendDirection) {
        'up'   => 'M7 17L17 7M9 7h8v8',
        'down' => 'M17 7L7 17M15 17H7V9',
        default => 'M5 12h14',
    };
@endphp

<div {{ $attributes->merge(['class' => "kpi kpi--{$variant}"]) }}>
    {{-- Head: label + icon --}}
    <div class="kpi__head">
        <div class="min-w-0 pe-12">
            <div class="kpi__label">{{ $label }}</div>
        </div>
        @if($icon)
            <div class="kpi__icon" aria-hidden="true">
                <x-dynamic-component :component="$icon" class="w-5 h-5" />
            </div>
        @endif
    </div>

    {{-- Value --}}
    @if($loading)
        <div class="skeleton h-9 w-32 mt-1"></div>
    @else
        <div class="flex items-baseline gap-1">
            <div class="kpi__value">{{ $value }}</div>
            @if($suffix)
                <div class="kpi__value-suffix">{{ $suffix }}</div>
            @endif
        </div>
        @if($deltaLabel)
            <div class="kpi__vs">{{ $deltaLabel }}</div>
        @endif
    @endif

    {{-- Foot: delta pill + sparkline (separated by dashed border) --}}
    @unless($loading)
        <div class="kpi__foot">
            @if($delta !== null)
                <div class="kpi__delta kpi__delta--{{ $trendDirection }}">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="{{ $trendIcon }}" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>{{ $delta }}</span>
                </div>
            @else
                <span></span>
            @endif

            @isset($sparkline)
                <div class="flex-shrink-0" style="width: 80px;">
                    {{ $sparkline }}
                </div>
            @endisset
        </div>
    @endunless

    @if($href)
        <a href="{{ $href }}" class="absolute inset-0 z-10 rounded-2xl" aria-label="{{ $label }}"></a>
    @endif
</div>