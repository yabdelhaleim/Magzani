@php
    $steps = [
        1 => ['icon' => 'fa-sitemap',       'label' => 'دليل الحسابات'],
        2 => ['icon' => 'fa-calendar-alt',   'label' => 'السنة المالية'],
        3 => ['icon' => 'fa-balance-scale',  'label' => 'أرصدة افتتاحية'],
        4 => ['icon' => 'fa-robot',          'label' => 'الترحيل التلقائي'],
        5 => ['icon' => 'fa-clipboard-check','label' => 'التحقق والتفعيل'],
    ];
@endphp

<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">إعداد النظام المحاسبي</h2>
            <p class="text-gray-600 mt-1">الخطوة {{ $currentStep }} من 5</p>
        </div>
        <a href="{{ route('accounting.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">
            <i class="fas fa-times ml-1"></i> إلغاء
        </a>
    </div>

    {{-- Progress Steps --}}
    <div class="flex items-center justify-between">
        @foreach($steps as $num => $step)
            <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-colors
                        {{ $num < $currentStep ? 'bg-green-500 text-white' : '' }}
                        {{ $num === $currentStep ? 'bg-blue-600 text-white ring-4 ring-blue-100' : '' }}
                        {{ $num > $currentStep ? 'bg-gray-200 text-gray-500' : '' }}
                    ">
                        @if($num < $currentStep)
                            <i class="fas fa-check"></i>
                        @else
                            <i class="fas {{ $step['icon'] }} text-xs"></i>
                        @endif
                    </div>
                    <span class="text-xs mt-2 font-medium {{ $num === $currentStep ? 'text-blue-700' : 'text-gray-500' }}">
                        {{ $step['label'] }}
                    </span>
                </div>

                @unless($loop->last)
                    <div class="flex-1 h-0.5 mx-3 mt-[-16px] {{ $num < $currentStep ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                @endunless
            </div>
        @endforeach
    </div>
</div>
