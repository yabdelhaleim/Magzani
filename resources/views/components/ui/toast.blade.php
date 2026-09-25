@props([
    'variant' => 'info',  // info | success | warning | danger
    'title'   => '',
    'message' => '',
])

<div {{ $attributes->merge(['class' => "alert alert--{$variant}"]) }} role="alert">
    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        @if($variant === 'success')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        @elseif($variant === 'warning')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        @elseif($variant === 'danger')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        @else
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        @endif
    </svg>
    <div class="flex-1 min-w-0">
        @if($title)<div class="text-sm font-semibold">{{ $title }}</div>@endif
        @if($message)<div class="text-sm mt-0.5">{{ $message }}</div>@else {{ $slot }} @endif
    </div>
    <button type="button" class="text-current opacity-60 hover:opacity-100" aria-label="Close">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>
