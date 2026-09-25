@props([
    'title'       => '',
    'subtitle'    => '',
    'breadcrumbs' => [],
])

<div class="page-header">
    <div>
        @if(!empty($breadcrumbs))
            <nav class="page-header__breadcrumb">
                @foreach($breadcrumbs as $crumb)
                    @if(!$loop->last)
                        <a href="{{ $crumb['url'] ?? '#' }}">{{ $crumb['label'] }}</a>
                        <svg class="w-3 h-3 text-ink-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    @else
                        <span class="text-ink-700">{{ $crumb['label'] }}</span>
                    @endif
                @endforeach
            </nav>
        @endif
        <h1 class="page-header__title">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-sm text-ink-500 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="page-header__actions">
            {{ $actions }}
        </div>
    @endif
</div>
