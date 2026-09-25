@props([
    'items' => [],
])

@php
    $toneMap = [
        'navy'    => ['bg' => 'bg-navy-50',    'text' => 'text-navy-700',    'border' => 'border-navy-200'],
        'brass'   => ['bg' => 'bg-warning-soft','text' => 'text-warning-700','border' => 'border-warning-200'],
        'success' => ['bg' => 'bg-success-soft','text' => 'text-success-700','border' => 'border-success-200'],
        'warning' => ['bg' => 'bg-warning-soft','text' => 'text-warning-700','border' => 'border-warning-200'],
        'danger'  => ['bg' => 'bg-danger-soft', 'text' => 'text-danger-700', 'border' => 'border-danger-200'],
        'info'    => ['bg' => 'bg-info-soft',   'text' => 'text-info-700',   'border' => 'border-info-200'],
    ];
@endphp

<div class="space-y-1">
    @foreach($items as $item)
        @php
            $tone = $toneMap[$item['tone'] ?? 'navy'] ?? $toneMap['navy'];
        @endphp
        <div class="activity-row">
            <div class="activity-row__icon {{ $tone['bg'] }} {{ $tone['text'] }}">
                @if(!empty($item['icon']))
                    <x-dynamic-component :component="$item['icon']" class="w-4 h-4" />
                @else
                    <span class="w-2 h-2 rounded-full bg-current"></span>
                @endif
            </div>
            <div class="flex-1 min-w-0 py-3 border-b border-dashed border-ink-200">
                <div class="text-[13px] font-medium text-ink-900 leading-snug">
                    {{ $item['title'] }}
                </div>
                @if(isset($item['desc']))
                    <div class="text-xs text-ink-500 mt-0.5">
                        {{ $item['desc'] }}
                    </div>
                @endif
                <div class="text-[11px] text-ink-400 mt-1 num">
                    {{ $item['time'] ?? '' }}
                </div>
            </div>
        </div>
    @endforeach
</div>