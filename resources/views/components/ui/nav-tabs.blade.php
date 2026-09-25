@props([
    'tabs'    => [],
    'active'  => null,
    'pills'   => false,
])

@if($pills)
    <div class="inline-flex items-center gap-1 p-1 bg-ink-100 rounded-xl">
        @foreach($tabs as $key => $label)
            <button
                type="button"
                wire:click="$set('activeTab', '{{ $key }}')"
                @class([
                    'px-4 py-1.5 text-sm font-medium rounded-lg transition-all duration-200',
                    'bg-white text-navy-700 shadow-1' => $active === $key,
                    'text-ink-600 hover:text-ink-900' => $active !== $key,
                ])
            >
                {{ $label }}
            </button>
        @endforeach
    </div>
@else
    <nav class="nav-tabs">
        @foreach($tabs as $key => $label)
            <a href="#"
               @class(['nav-tab', 'is-active' => $active === $key])
               wire:click.prevent="$set('activeTab', '{{ $key }}')">
                {{ $label }}
            </a>
        @endforeach
    </nav>
@endif
