@extends('layouts.app')

@section('title', 'إعداد المحاسبة — التحقق والتفعيل')

@section('content')
<div class="space-y-6">
    @include('accounting.setup.partials.wizard-header', ['currentStep' => 5])

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
                <i class="fas fa-clipboard-check text-blue-600"></i>
                التحقق من جاهزية النظام المحاسبي
            </h3>
            <p class="text-gray-600 mb-6">مراجعة نهائية قبل بدء استخدام النظام المحاسبي.</p>

            <div class="space-y-3">
                {{-- دليل الحسابات --}}
                <div class="flex items-center gap-3 p-4 rounded-lg {{ $checks['chart'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                    <i class="fas {{ $checks['chart'] ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' }} text-xl"></i>
                    <div>
                        <span class="font-medium {{ $checks['chart'] ? 'text-green-800' : 'text-red-800' }}">دليل الحسابات</span>
                        <p class="text-sm {{ $checks['chart'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $checks['chart'] ? 'تم زرع دليل الحسابات الافتراضي بنجاح' : 'لم يتم إعداد دليل الحسابات بعد' }}
                        </p>
                    </div>
                    @unless($checks['chart'])
                        <a href="{{ route('accounting.setup.step', 1) }}" class="mr-auto text-sm text-blue-600 hover:underline">إعداد</a>
                    @endunless
                </div>

                {{-- السنة المالية --}}
                <div class="flex items-center gap-3 p-4 rounded-lg {{ $checks['fiscal'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                    <i class="fas {{ $checks['fiscal'] ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' }} text-xl"></i>
                    <div>
                        <span class="font-medium {{ $checks['fiscal'] ? 'text-green-800' : 'text-red-800' }}">السنة المالية</span>
                        <p class="text-sm {{ $checks['fiscal'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $checks['fiscal'] ? 'توجد سنة مالية حالية نشطة' : 'لم يتم إنشاء سنة مالية بعد' }}
                        </p>
                    </div>
                    @unless($checks['fiscal'])
                        <a href="{{ route('accounting.setup.step', 2) }}" class="mr-auto text-sm text-blue-600 hover:underline">إعداد</a>
                    @endunless
                </div>

                {{-- الفترات المالية --}}
                <div class="flex items-center gap-3 p-4 rounded-lg {{ $checks['periods'] ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200' }}">
                    <i class="fas {{ $checks['periods'] ? 'fa-check-circle text-green-600' : 'fa-exclamation-triangle text-yellow-600' }} text-xl"></i>
                    <div>
                        <span class="font-medium {{ $checks['periods'] ? 'text-green-800' : 'text-yellow-800' }}">الفترات المالية</span>
                        <p class="text-sm {{ $checks['periods'] ? 'text-green-600' : 'text-yellow-600' }}">
                            {{ $checks['periods'] ? 'توجد فترات مالية مفتوحة للتسجيل' : 'لا توجد فترات مالية مفتوحة' }}
                        </p>
                    </div>
                </div>

                {{-- الإعدادات --}}
                <div class="flex items-center gap-3 p-4 rounded-lg {{ $checks['settings'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                    <i class="fas {{ $checks['settings'] ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' }} text-xl"></i>
                    <div>
                        <span class="font-medium {{ $checks['settings'] ? 'text-green-800' : 'text-red-800' }}">الإعدادات المحاسبية</span>
                        <p class="text-sm {{ $checks['settings'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $checks['settings'] ? 'تم ضبط الإعدادات الأساسية' : 'لم يتم ضبط الإعدادات بعد' }}
                        </p>
                    </div>
                </div>
            </div>

            @php $allPassed = !in_array(false, $checks, true); @endphp

            <div class="flex justify-between pt-4 mt-6 border-t border-gray-100">
                <a href="{{ route('accounting.setup.step', 4) }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-right ml-1"></i> السابقة
                </a>

                @if($allPassed)
                    <a href="{{ route('accounting.setup.complete') }}" class="px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center gap-2">
                        <i class="fas fa-rocket"></i>
                        بدء استخدام النظام المحاسبي
                    </a>
                @else
                    <span class="px-6 py-2.5 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed font-medium">
                        يرجى إكمال جميع الخطوات أولاً
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
