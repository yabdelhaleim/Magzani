@php
    $messages = [
        'success' => [
            'title' => 'تمت العملية بنجاح',
            'icon' => 'fa-check-circle',
            'role' => 'status',
            'autoDismiss' => true,
        ],
        'error' => [
            'title' => 'حدث خطأ',
            'icon' => 'fa-times-circle',
            'role' => 'alert',
            'autoDismiss' => false,
        ],
        'warning' => [
            'title' => 'تحذير',
            'icon' => 'fa-exclamation-triangle',
            'role' => 'alert',
            'autoDismiss' => false,
        ],
        'info' => [
            'title' => 'معلومة',
            'icon' => 'fa-info-circle',
            'role' => 'status',
            'autoDismiss' => true,
        ],
    ];
@endphp

<div class="global-flash-stack" aria-live="polite" aria-atomic="true">
    @foreach($messages as $type => $settings)
        @if(session($type))
            <div class="toast {{ $type }} {{ $settings['autoDismiss'] ? 'toast-auto-dismiss' : '' }}"
                 role="{{ $settings['role'] }}"
                 data-global-flash="{{ $type }}">
                <div class="toast-icon" aria-hidden="true">
                    <i class="fas {{ $settings['icon'] }}"></i>
                </div>
                <div style="flex:1;">
                    <p style="font-weight:700;margin:0 0 2px;">{{ $settings['title'] }}</p>
                    <p data-flash-body style="margin:0;font-weight:400;opacity:0.8;font-size:13px;">{{ session($type) }}</p>
                </div>
                <button type="button"
                        class="toast-close"
                        onclick="this.closest('.toast').remove()"
                        aria-label="إغلاق الرسالة">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
        @endif
    @endforeach
</div>
