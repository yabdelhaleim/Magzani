@props([
    'id'      => null,
    'data'    => [],     // ['labels' => [...], 'series' => [['name' => ..., 'data' => [...]]]]
    'height'  => 280,
    'colors'  => ['#1B3A5C', '#B08D5A', '#10B981'],
])

@php
$id = $id ?? 'chart-' . uniqid();
$labels = $data['labels'] ?? [];
$series = $data['series'] ?? [];
@endphp

<div x-data="{
    init() {
        const seriesData = @js($series);
        const labels = @js($labels);
        const colors = @js($colors);
        const allValues = seriesData.flatMap(s => s.data);
        const max = Math.max(...allValues, 1) * 1.1;

        // RTL detection — flip chart axis for Arabic
        const isRTL = document.documentElement.dir === 'rtl';
        const w = 600, h = {{ $height }};
        const pad = isRTL ? { l: 16, r: 50, t: 16, b: 32 } : { l: 50, r: 16, t: 16, b: 32 };
        const innerW = w - pad.l - pad.r;
        const innerH = h - pad.t - pad.b;
        const stepX = labels.length > 1 ? innerW / (labels.length - 1) : innerW;
        const x = i => pad.l + (isRTL ? (labels.length - 1 - i) : i) * stepX;
        const y = v => pad.t + innerH - (v / max) * innerH;

        const svg = document.getElementById('{{ $id }}');
        let html = '';

        // Gridlines + Y-axis labels
        const ticks = 5;
        const xAnchor = isRTL ? 'start' : 'end';
        const xOffset = isRTL ? 8 : -8;
        for (let t = 0; t <= ticks; t++) {
            const v = (max / ticks) * t;
            const yy = y(v);
            html += `<line x1='${pad.l}' y1='${yy}' x2='${w - pad.r}' y2='${yy}' stroke='#E7ECF3' stroke-width='1' stroke-dasharray='2,3'/>`;
            html += `<text x='${pad.l + xOffset}' y='${yy + 3}' font-size='10' fill='#7B8794' text-anchor='${xAnchor}' font-family='JetBrains Mono, monospace'>${Math.round(v)}</text>`;
        }

        // X-axis labels
        const labelStep = Math.max(1, Math.ceil(labels.length / 8));
        labels.forEach((lbl, i) => {
            if (i % labelStep !== 0 && i !== labels.length - 1) return;
            html += `<text x='${x(i)}' y='${h - 10}' font-size='10' fill='#7B8794' text-anchor='middle' font-family='JetBrains Mono, monospace'>${lbl}</text>`;
        });

        // Series
        seriesData.forEach((s, si) => {
            const color = colors[si % colors.length];
            const pts = s.data.map((v, i) => `${x(i)},${y(v)}`).join(' ');
            const pathD = 'M ' + pts.replace(/ /g, ' L ');

            html += `<defs>
                <linearGradient id='grad-{{ $id }}-${si}' x1='0' y1='0' x2='0' y2='1'>
                    <stop offset='0%' stop-color='${color}' stop-opacity='0.20'/>
                    <stop offset='100%' stop-color='${color}' stop-opacity='0'/>
                </linearGradient>
            </defs>`;

            const firstX = x(0);
            const lastX = x(s.data.length - 1);
            html += `<path d='M ${firstX},${y(0)} L ${pts.replace(/ /g, ' L ')} L ${lastX},${y(0)} Z' fill='url(#grad-{{ $id }}-${si})'/>`;
            html += `<path d='${pathD}' fill='none' stroke='${color}' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'/>`;

            // Dots
            s.data.forEach((v, i) => {
                html += `<circle cx='${x(i)}' cy='${y(v)}' r='2.5' fill='white' stroke='${color}' stroke-width='2'>`;
                html += `<title>${lblAt(i)}: ${Math.round(v)}</title></circle>`;
            });
        });

        function lblAt(i) {
            const step = Math.max(1, Math.ceil(labels.length / 8));
            return labels[i] || '';
        }

        svg.innerHTML = html;
    }
}">
    <svg id="{{ $id }}" viewBox="0 0 600 {{ $height }}" preserveAspectRatio="none" class="w-full h-full" style="height: {{ $height }}px"></svg>

    @if(count($series) > 1)
        <div class="flex items-center gap-4 mt-3">
            @foreach($series as $i => $s)
                <div class="flex items-center gap-1.5 text-xs text-ink-600">
                    <span class="w-2.5 h-2.5 rounded-sm" style="background: {{ $colors[$i % count($colors)] }}"></span>
                    {{ $s['name'] ?? '' }}
                </div>
            @endforeach
        </div>
    @endif
</div>