@extends('layouts.app')

@section('title', 'إعداد المحاسبة — السنة المالية')

@section('content')
<div class="space-y-6">
    @include('accounting.setup.partials.wizard-header', ['currentStep' => 2])

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-blue-600"></i>
                إنشاء السنة المالية
            </h3>
            <p class="text-gray-600 mb-6">حدد تاريخ بداية ونهاية السنة المالية. سيتم إنشاء فترات شهرية تلقائياً.</p>

            @if($fiscalYears->isNotEmpty())
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-blue-800 font-medium mb-2"><i class="fas fa-info-circle ml-1"></i> السنوات المالية الموجودة:</p>
                    <ul class="space-y-1">
                        @foreach($fiscalYears as $year)
                            <li class="text-blue-700 text-sm">
                                {{ $year->name }} ({{ $year->start_date->format('Y-m-d') }} — {{ $year->end_date->format('Y-m-d') }})
                                @if($year->is_current)
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs mr-1">الحالية</span>
                                @endif
                                — {{ $year->periods->count() }} فترة
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('accounting.setup.step', 3) }}" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2">
                        المتابعة للخطوة التالية
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            @else
                <form method="POST" action="{{ route('accounting.setup.save-fiscal-year') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">اسم السنة المالية</label>
                            <input type="text" name="name" value="{{ now()->year }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="مثال: 2026">
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ البداية</label>
                            <input type="date" name="start_date" value="{{ now()->startOfYear()->format('Y-m-d') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ النهاية</label>
                            <input type="date" name="end_date" value="{{ now()->endOfYear()->format('Y-m-d') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('end_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-between pt-4 border-t border-gray-100">
                        <a href="{{ route('accounting.setup.step', 1) }}" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                            <i class="fas fa-arrow-right ml-1"></i> الخطوة السابقة
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2">
                            إنشاء السنة المالية والمتابعة
                            <i class="fas fa-arrow-left"></i>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
