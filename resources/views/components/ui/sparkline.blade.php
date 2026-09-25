@props([
    'data'    => [],
    'color'   => 'currentColor',
    'fill'    => true,
    'height'  => 36,
])

@php
    $points = collect($data);
    $max = (float) ($points->max() ?: 1);
    $min = (float) ($points->min() ?: 0);
    $range = max(0.0001, $max - $min);

    $width = 200;
    $h = (int) $height;
    $count = $points->count();
    $step = $count > 1 ? $width / ($count - 1) : $width;

    $coords = $points->map(function ($val, $i) use ($max, $min, $range, $h, $step) {
        $x = $i * $step;
        $y = $h - (($val - $min) / $range) * ($h - 6) - 3;
        return round($x, 2) . ',' . round($y, 2);
    });

    $path = 'M ' . $coords->implode(' L ');
    $areaPath = "M 0,{$h} L " . $coords->implode(' L ') . " L {$width},{$h} Z";

    $gradientId = 'spark-' . uniqid();
    $stroke = $color === 'currentColor' ? 'currentColor' : $color;
@endphp

<svg viewBox="0 0 {{ $width }} {{ $h }}" preserveAspectRatio="none" class="block w-full" style="height: {{ $h }}px">
    @if($fill)
        <defs>
            <linearGradient id="{{ $gradientId }}" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="{{ $stroke }}" stop-opacity="0.28"/>
                <stop offset="100%" stop-color="{{ $stroke }}" stop-opacity="0"/>
            </linearGradient>
        </defs>
        <path d="{{ $areaPath }}" fill="url(#{{ $gradientId }})"/>
    @endif
    <path d="{{ $path }}" fill="none" stroke="{{ $stroke }}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>