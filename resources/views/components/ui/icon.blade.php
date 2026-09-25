@props([
    'name' => null,
    'class' => 'w-4 h-4',
])

@php
    $map = [
        'icon-arrow-trending-up'   => 'arrow-trending-up',
        'icon-arrow-trending-down' => 'arrow-trending-down',
        'icon-arrow-down-tray'     => 'arrow-down-tray',
        'icon-banknotes'           => 'banknotes',
        'icon-users'               => 'users',
        'icon-cube'                => 'cube',
        'icon-exclamation'         => 'exclamation',
        'icon-receipt'             => 'receipt',
        'icon-truck'               => 'truck',
        'icon-user-plus'           => 'user-plus',
        'icon-arrows-right-left'   => 'arrows-right-left',
        'icon-clipboard'           => 'clipboard',
        'icon-chart-pie'           => 'chart-pie',
        'icon-credit-card'         => 'credit-card',
        'icon-clock'               => 'clock',
        'icon-check-circle'        => 'check-circle',
    ];
    $iconName = $map[$name] ?? $name;
@endphp

@if($iconName)
    <x-dynamic-component :component="'icon.' . $iconName" :class="$class" />
@endif
