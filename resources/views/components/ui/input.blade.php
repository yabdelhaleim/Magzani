@props([
    'type'    => 'text',
    'name'    => null,
    'id'      => null,
    'label'   => null,
    'placeholder' => '',
    'value'   => null,
    'error'   => null,
    'hint'    => null,
    'icon'    => null,
    'size'    => 'md',  // md | lg
    'required' => false,
    'disabled' => false,
    'readonly' => false,
])

@php
$inputId = $id ?? ($name ?? 'input-' . uniqid());
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $inputId }}" class="label">
            {{ $label }}
            @if($required)<span class="text-danger">*</span>@endif
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <span class="input-group__icon input-group__icon--start">
                <x-dynamic-component :component="$icon" class="w-4 h-4" />
            </span>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $inputId }}"
            name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            value="{{ $value ?? old($name) }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            {{ $attributes->merge(['class' => 'input ' . ($size === 'lg' ? 'input-lg' : '') . ' ' . ($icon ? 'ps-10' : '') . ' ' . ($error ? 'input--error' : '')]) }}
        />
    </div>

    @if($error)
        <p class="mt-1 text-xs text-danger flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $error }}
        </p>
    @elseif($hint)
        <p class="mt-1 text-xs text-ink-500">{{ $hint }}</p>
    @endif
</div>
