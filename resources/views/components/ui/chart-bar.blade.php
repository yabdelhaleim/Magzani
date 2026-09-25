@props([
    'id'         => null,
    'labels'     => [],
    'values'     => [],
    'color'      => '#1B3A5C',
    'height'     => 280,
    'horizontal' => false,
])

@php
$id = $id ?? 'chart-' . uniqid();
$max = max($values ?: [1]) * 1.1;
@endphp

<div x-data="{
    init() {
        const labels = @js($labels);
        const values = @js($values);
        const color = @js($color);
        const max = @js($max);
        const isRTL = document.documentElement.dir === 'rtl';
        const w = 600, h = {{ $height }};
        const pad = isRTL ? { l: 16, r: 50, t: 16, b: 36 } : { l: 50, r: 16, t: 16, b: 36 };
        const innerW = w - pad.l - pad.r;
        const innerH = h - pad.t - pad.b;
        const barWidth = innerW / values.length * 0.65;
        const gap = innerW / values.length * 0.35;

        const svg = document.getElementById('{{ $id }}');
        let html = '';

        // Gridlines (4 ticks)
        const xAnchor = isRTL ? 'start' : 'end';
        const xOffset = isRTL ? 8 : -8;
        for (let t = 0; t <= 4; t++) {
            const v = (max / 4) * t;
            const yy = pad.t + innerH - (v / max) * innerH;
            html += `<line x1='${pad.l}' y1='${yy}' x2='${w - pad.r}' y2='${yy}' stroke='#E7ECF3' stroke-width='1' stroke-dasharray='2,3'/>`;
            html += `<text x='${pad.l + xOffset}' y='${yy + 3}' font-size='10' fill='#7B8794' text-anchor='${xAnchor}' font-family='JetBrains Mono, monospace'>${Math.round(v)}</text>`;
        }

        values.forEach((v, i) => {
            // In RTL, bars render right-to-left
            const renderIndex = isRTL ? (values.length - 1 - i) : i;
            const bx = pad.l + renderIndex * (innerW / values.length) + gap / 2;
            const bh = (v / max) * innerH;
            const by = pad.t + innerH - bh;
            const gradId = 'bg-' + i;
            html += `<defs><linearGradient id='${gradId}' x1='0' y1='0' x2='0' y2='1'>
                <stop offset='0%' stop-color='${color}' stop-opacity='0.95'/>
                <stop offset='100%' stop-color='${color}' stop-opacity='0.65'/>
            </linearGradient></defs>`;
            html += `<rect x='${bx}' y='${by}' width='${barWidth}' height='${bh}' rx='6' fill='url(#${gradId})'>`;
            html += `<title>${labels[i] ?? ''}: ${Math.round(v)}</title></rect>`;
            html += `<text x='${bx + barWidth / 2}' y='${h - 16}' font-size='10' fill='#7B8794' text-anchor='middle' font-family='JetBrains Mono, monospace'>${labels[i] ?? ''}</text>`;
        });

        svg.innerHTML = html;
    }
}">
    <svg id="{{ $id }}" viewBox="0 0 600 {{ $height }}" preserveAspectRatio="none" class="w-full h-full" style="height: {{ $height }}px"></svg>
</div>