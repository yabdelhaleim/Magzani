@props([
    'variant' => 'default',  // default | success | warning | danger | info | navy | brass
    'dot'     => false,
    'icon'    => null,
])

<span {{ $attributes->merge(['class' => "tag tag--{$variant}" . ($dot ? ' tag--dot' : '')]) }}>
    @if($icon)
        <x-dynamic-component :component="$icon" class="w-3 h-3" />
    @endif
    {{ $slot }}
</span>
