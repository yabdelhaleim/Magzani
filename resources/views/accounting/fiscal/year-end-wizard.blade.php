@extends('layouts.app')

@section('title', 'معالج إقفال السنة المالية')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-800">معالج إقفال السنة المالية</h2>
        <p class="text-gray-600 mt-1">مراجعة الأرصدة وإنشاء قيود الإقفال وترحيل الأرباح المحتجزة</p>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">{{ session('error') }}</div>
    @endif

    <form method="GET" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex gap-4 items-end">
        <div class="flex-1">
            <label class="block text-sm font-medium mb-1">السنة المالية</label>
            <select name="year_id" class="w-full px-3 py-2 border rounded-lg" onchange="this.form.submit()">
                @foreach($fiscalYears as $fy)
                    <option value="{{ $fy->id }}" @selected($year?->id === $fy->id)>{{ $fy->name }} ({{ $fy->is_closed ? 'مغلقة' : 'مفتوحة' }})</option>
                @endforeach
            </select>
        </div>
    </form>

    @if($year && $preview)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h3 class="text-lg font-bold">{{ $year->name }}</h3>
            <p class="text-sm text-gray-500">{{ $year->start_date->toDateString() }} — {{ $year->end_date->toDateString() }}</p>

            @if($year->is_closed)
                <div class="p-4 bg-gray-50 rounded-lg text-gray-600">هذه السنة مغلقة بالفعل.</div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-blue-50 rounded-lg text-center">
                        <div class="text-2xl font-bold text-blue-700">{{ number_format($preview['total_revenue'], 2) }}</div>
                        <div class="text-xs text-gray-600">إجمالي الإيرادات</div>
                    </div>
                    <div class="p-4 bg-orange-50 rounded-lg text-center">
                        <div class="text-2xl font-bold text-orange-700">{{ number_format($preview['total_expenses'], 2) }}</div>
                        <div class="text-xs text-gray-600">إجمالي المصروفات</div>
                    </div>
                    <div class="p-4 {{ $preview['net_income'] >= 0 ? 'bg-green-50' : 'bg-red-50' }} rounded-lg text-center">
                        <div class="text-2xl font-bold">{{ number_format($preview['net_income'], 2) }}</div>
                        <div class="text-xs text-gray-600">صافي الدخل</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <div class="text-2xl font-bold">{{ $preview['open_periods'] }}</div>
                        <div class="text-xs text-gray-600">فترات مفتوحة (ستُغلق تلقائياً)</div>
                    </div>
                </div>

                @if($preview['has_drafts'])
                    <div class="p-4 bg-yellow-50 text-yellow-800 rounded-lg border border-yellow-200">
                        ⚠️ يوجد {{ $preview['draft_entries'] }} قيد مسودة — يجب اعتمادها أو حذفها قبل الإقفال.
                    </div>
                @endif

                <div class="border-t pt-4">
                    <h4 class="font-semibold mb-2">قيود الإقفال التي ستُنشأ:</h4>
                    <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside">
                        <li>إقفال حسابات الإيرادات → ملخص الدخل</li>
                        <li>إقفال حسابات المصروفات → ملخص الدخل</li>
                        <li>ترحيل صافي الدخل → الأرباح المحتجزة</li>
                    </ul>
                </div>

                @if(!$preview['has_drafts'])
                    <form method="POST" action="{{ route('accounting.fiscal.year-end.execute') }}" class="space-y-4 border-t pt-4" onsubmit="return confirm('تأكيد إقفال السنة المالية؟ لا يمكن التراجع.')">
                        @csrf
                        <input type="hidden" name="fiscal_year_id" value="{{ $year->id }}">
                        <label class="flex items-center gap-2"><input type="checkbox" name="create_next_year" value="1"> إنشاء السنة المالية التالية تلقائياً</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="create_opening_entry" value="1"> إنشاء قيد أرصدة افتتاحية للسنة الجديدة</label>
                        <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                            <i class="fas fa-lock ml-1"></i> تنفيذ إقفال السنة
                        </button>
                    </form>
                @endif
            @endif
        </div>
    @endif

    <div class="text-center">
        <a href="{{ route('accounting.fiscal.index') }}" class="text-blue-600 hover:underline text-sm">← العودة للفترات المالية</a>
    </div>
</div>
@endsection
