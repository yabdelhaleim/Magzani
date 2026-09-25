@props([
    'id'      => null,
    'labels'  => [],
    'values'  => [],
    'colors'  => ['#1B3A5C', '#B08D5A', '#10B981', '#F59E0B', '#3B82F6', '#EF4444'],
    'height'  => 280,
    'centerTitle' => null,
    'centerValue' => null,
])

@php
$id = $id ?? 'chart-' . uniqid();
$total = array_sum($values) ?: 1;
@endphp

<div x-data="{
    init() {
        const labels = @js($labels);
        const values = @js($values);
        const colors = @js($colors);
        const total = @js($total);
        const size = 220, cx = size / 2, cy = size / 2, r = 80, ir = 56;
        const svg = document.getElementById('{{ $id }}');
        let angle = -Math.PI / 2;
        let html = '';

        values.forEach((v, i) => {
            const slice = (v / total) * Math.PI * 2;
            const x1 = cx + r * Math.cos(angle);
            const y1 = cy + r * Math.sin(angle);
            const x2 = cx + r * Math.cos(angle + slice);
            const y2 = cy + r * Math.sin(angle + slice);
            const x3 = cx + ir * Math.cos(angle + slice);
            const y3 = cy + ir * Math.sin(angle + slice);
            const x4 = cx + ir * Math.cos(angle);
            const y4 = cy + ir * Math.sin(angle);
            const large = slice > Math.PI ? 1 : 0;
            const color = colors[i % colors.length];

            html += `<path d='M ${x1} ${y1} A ${r} ${r} 0 ${large} 1 ${x2} ${y2} L ${x3} ${y3} A ${ir} ${ir} 0 ${large} 0 ${x4} ${y4} Z' fill='${color}' opacity='0.95'/>`;
            angle += slice;
        });

        svg.innerHTML = html;
    }
}">
    <div class="flex items-center gap-6" style="min-height: {{ $height }}px;">
        <div class="relative">
            <svg id="{{ $id }}" viewBox="0 0 220 220" class="w-56 h-56"></svg>
            @if($centerTitle || $centerValue)
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    @if($centerValue)
                        <div class="text-2xl font-bold text-ink-900" style="font-family: 'Fraunces', 'Reem Kufi', serif;">
                            {{ $centerValue }}
                        </div>
                    @endif
                    @if($centerTitle)
                        <div class="text-xs text-ink-500">{{ $centerTitle }}</div>
                    @endif
                </div>
            @endif
        </div>
        <div class="flex-1 space-y-2">
            @foreach($labels as $i => $label)
                <div class="flex items-center justify-between gap-3 text-sm">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2.5 h-2.5 rounded-sm flex-shrink-0" style="background: {{ $colors[$i % count($colors)] }}"></span>
                        <span class="truncate text-ink-700">{{ $label }}</span>
                    </div>
                    <div class="font-semibold text-ink-900">{{ number_format($values[$i] ?? 0) }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
