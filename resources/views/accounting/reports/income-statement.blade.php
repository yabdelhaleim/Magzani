@extends('layouts.app')

@section('title', 'قائمة الدخل')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">قائمة الدخل (Income Statement / P&L)</h2>
            <p class="text-gray-600 mt-1">قياس الأداء المالي للمنشأة عن طريق مقابلة الإيرادات بالمصروفات خلال فترة محددة</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors no-print">
            <i class="fas fa-print ml-1"></i> طباعة التقرير
        </button>
    </div>

    <!-- Filters (No Print) -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 no-print">
        <form method="GET" action="{{ route('accounting.reports.income-statement') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                <input type="date" name="from" value="{{ request('from', $from) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" name="to" value="{{ request('to', $to) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-1.5">
                    <i class="fas fa-filter"></i> عرض التقرير
                </button>
            </div>
        </form>
    </div>

    <!-- Report Sheet Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-4xl mx-auto p-8 space-y-8 font-sans">
        <!-- Letterhead -->
        <div class="text-center pb-6 border-b-2 border-gray-200">
            <h3 class="text-2xl font-bold text-gray-800">قائمة الدخل للفترة</h3>
            <p class="text-sm text-gray-500 mt-2">
                من تاريخ: <span class="font-mono font-semibold">{{ $data['from'] }}</span>
                إلى تاريخ: <span class="font-mono font-semibold">{{ $data['to'] }}</span>
            </p>
        </div>

        <!-- REVENUES SECTION -->
        <div>
            <h4 class="text-lg font-bold text-gray-800 bg-gray-50 p-2.5 rounded-lg mb-3 flex justify-between">
                <span>1. الإيرادات التشغيلية</span>
                <span class="text-xs text-gray-400 font-normal">دائن (+)</span>
            </h4>
            <div class="space-y-2.5 font-mono text-sm px-3">
                @foreach($data['revenues'] as $rev)
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                        <span class="text-gray-700 font-sans font-medium">{{ $rev['code'] }} - {{ $rev['name'] }}</span>
                        <span class="text-gray-900 font-bold">{{ number_format($rev['net_balance'] ?? 0, 2) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between items-center py-2.5 border-t border-gray-300 text-base font-bold text-gray-900">
                    <span class="font-sans">إجمالي الإيرادات:</span>
                    <span>{{ number_format($data['total_revenues'] ?? $data['total_revenue'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- EXPENSES SECTION -->
        <div>
            <h4 class="text-lg font-bold text-gray-800 bg-gray-50 p-2.5 rounded-lg mb-3 flex justify-between">
                <span>2. المصروفات وتكلفة المبيعات</span>
                <span class="text-xs text-gray-400 font-normal">مدين (-)</span>
            </h4>
            <div class="space-y-2.5 font-mono text-sm px-3">
                @foreach($data['expenses'] as $exp)
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                        <span class="text-gray-700 font-sans font-medium">{{ $exp['code'] }} - {{ $exp['name'] }}</span>
                        <span class="text-gray-900 font-bold">({{ number_format($exp['net_balance'] ?? 0, 2) }})</span>
                    </div>
                @endforeach
                <div class="flex justify-between items-center py-2.5 border-t border-gray-300 text-base font-bold text-gray-900">
                    <span class="font-sans">إجمالي التكاليف والمصروفات:</span>
                    <span>({{ number_format($data['total_expenses'] ?? $data['total_expense'] ?? 0, 2) }})</span>
                </div>
            </div>
        </div>

        <!-- NET INCOME SECTION -->
        <div class="p-6 rounded-xl {{ $data['net_income'] >= 0 ? 'bg-green-50 border border-green-200 text-green-950' : 'bg-red-50 border border-red-200 text-red-950' }} flex justify-between items-center font-bold text-xl">
            <div>
                <span class="block text-xs uppercase tracking-wider text-gray-500 font-sans font-semibold mb-1">النتيجة النهائية</span>
                <span>{{ $data['net_income'] >= 0 ? 'صافي أرباح الفترة (Net Profit)' : 'صافي خسائر الفترة (Net Loss)' }}</span>
            </div>
            <span class="font-mono text-2xl">{{ number_format($data['net_income'], 2) }}</span>
        </div>
    </div>
</div>

@style
@media print {
    .no-print { display: none !important; }
    body { background: #fff !important; }
}
@endstyle
@endsection
