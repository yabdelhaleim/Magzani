@extends('layouts.app')

@section('title', 'الفترات المالية')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">الفترات والسنوات المالية</h2>
            <p class="text-gray-600 mt-1">فتح وإغلاق الفترات المالية الشهرية وإجراء تسويات إقفال الحسابات السنوية</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="toggleModal('create-year-modal')" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all shadow hover:shadow-md flex items-center gap-1.5">
                <i class="fas fa-plus-circle"></i> إنشاء سنة مالية جديدة
            </button>
            <a href="{{ route('accounting.fiscal.year-end') }}" class="px-5 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-lg font-medium flex items-center gap-1.5">
                <i class="fas fa-lock"></i> معالج إقفال السنة
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <!-- Fiscal Years List -->
    <div class="space-y-6">
        @forelse($fiscalYears as $year)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Year Header -->
                <div class="p-6 bg-gray-50 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="text-xl font-bold text-gray-800">{{ $year->name }}</h3>
                            @if($year->is_current)
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 border border-blue-200">
                                    السنة الحالية
                                </span>
                            @endif
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $year->is_closed ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-green-100 text-green-700 border border-green-200' }}">
                                {{ $year->is_closed ? 'مغلقة' : 'مفتوحة للعمل' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">الفترة من: {{ $year->start_date->toDateString() }} إلى: {{ $year->end_date->toDateString() }}</p>
                    </div>

                    <div class="flex gap-2 w-full md:w-auto">
                        @if(!$year->is_current)
                            <form method="POST" action="{{ route('accounting.fiscal.year.set-current', $year->id) }}">
                                @csrf
                                <button type="submit" class="w-full md:w-auto px-4 py-2 bg-white hover:bg-gray-100 text-gray-700 text-sm font-semibold border border-gray-200 rounded-lg transition-colors">
                                    تعيين كالسنة الحالية
                                </button>
                            </form>
                        @endif

                        @if(!$year->is_closed)
                            <form method="POST" action="{{ route('accounting.fiscal.year.close', $year->id) }}" onsubmit="return confirm('⚠️ تحذير: إغلاق السنة المالية سيقوم بإغلاق جميع الشهور الـ 12 التابعة لها ومنع التعديل نهائياً. هل أنت متأكد؟')">
                                @csrf
                                <button type="submit" class="w-full md:w-auto px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center justify-center gap-1.5">
                                    <i class="fas fa-lock"></i> إغلاق السنة المالية (قيد الإقفال)
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Months Grid -->
                <div class="p-6">
                    <h4 class="text-sm font-bold text-gray-500 mb-4 uppercase tracking-wider">الفترات المالية الشهرية</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($year->periods as $period)
                            <div class="p-4 rounded-lg border {{ $period->is_closed ? 'bg-gray-50 border-gray-200 text-gray-500' : 'bg-white border-blue-100 shadow-sm text-gray-800' }} flex flex-col justify-between h-40">
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="font-bold text-base">{{ $period->name }}</span>
                                        <span class="text-xxs font-mono text-gray-400">#{{ $period->period_number }}</span>
                                    </div>
                                    <span class="text-xs text-gray-500 block">من: {{ $period->start_date->toDateString() }}</span>
                                    <span class="text-xs text-gray-500 block">إلى: {{ $period->end_date->toDateString() }}</span>
                                </div>

                                <div class="pt-3 border-t border-gray-100 flex items-center justify-between mt-2">
                                    @if($period->is_closed)
                                        <span class="text-xs text-red-600 font-semibold flex items-center gap-1">
                                            <i class="fas fa-lock"></i> مغلقة
                                        </span>
                                        @if($period->closed_at)
                                            <span class="text-xxs text-gray-400 font-mono">{{ $period->closed_at->format('Y-m-d') }}</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-green-600 font-semibold flex items-center gap-1">
                                            <i class="fas fa-lock-open"></i> مفتوحة
                                        </span>
                                        <form method="POST" action="{{ route('accounting.fiscal.period.close', $period->id) }}" onsubmit="return confirm('هل أنت متأكد من إغلاق هذه الفترة المالية؟ لن يُسمح بترحيل قيود جديدة إليها.')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-white hover:bg-gray-100 text-red-600 border border-red-200 rounded text-xxs font-bold transition-all">
                                                إغلاق الفترة
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white p-12 rounded-xl border border-gray-100 text-center text-gray-500">
                <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                <p>لا توجد سنوات مالية مسجلة بعد.</p>
                <button onclick="toggleModal('create-year-modal')" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors">
                    إعداد السنة المالية الأولى
                </button>
            </div>
        @endforelse
    </div>
</div>

<!-- Create Year Modal -->
<div id="create-year-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="toggleModal('create-year-modal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        
        <div class="inline-block overflow-hidden text-right align-bottom transition-all transform bg-white rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">إنشاء سنة مالية جديدة</h3>
                <button onclick="toggleModal('create-year-modal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form method="POST" action="{{ route('accounting.fiscal.store') }}">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">اسم السنة المالية *</label>
                        <input type="text" name="name" required placeholder="مثال: السنة المالية 2026"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ بداية السنة *</label>
                        <input type="date" name="start_date" required value="{{ now()->startOfYear()->toDateString() }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">سيقوم النظام تلقائياً بإنشاء 12 فترة شهرية بدءاً من هذا التاريخ.</p>
                    </div>
                </div>
                
                <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" onclick="toggleModal('create-year-modal')" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 rounded-lg text-sm font-semibold transition-colors">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors">
                        إنشاء السنة المالية
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.toggle('hidden');
    }
</script>
@endsection
