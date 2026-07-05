@extends('layouts.app')

@section('title', 'ميزان المراجعة')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">ميزان المراجعة (Trial Balance)</h2>
            <p class="text-gray-600 mt-1">عرض مجاميع المدين والدائن والأرصدة لجميع الحسابات حتى تاريخ معين</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors no-print">
            <i class="fas fa-print ml-1"></i> طباعة التقرير
        </button>
    </div>

    <!-- Filters (No Print) -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 no-print">
        <form method="GET" action="{{ route('accounting.reports.trial-balance') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">حتى تاريخ (As of)</label>
                <input type="date" name="as_of" value="{{ request('as_of', $asOf ?? now()->toDateString()) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">أو اختر فترة مالية جاهزة</label>
                <select name="period_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">-- تصفية بالتاريخ أعلاه --</option>
                    @foreach($periods as $p)
                        <option value="{{ $p->id }}" {{ request('period_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->fiscalYear->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-1.5">
                    <i class="fas fa-filter"></i> عرض التقرير
                </button>
            </div>
        </form>
    </div>

    <!-- Trial Balance Sheet Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-100 text-center">
            <h3 class="text-xl font-bold text-gray-800">ميزان المراجعة بالأرصدة والمجاميع</h3>
            <p class="text-sm text-gray-500 mt-1">التقرير حتى تاريخ: <span class="font-mono font-bold text-gray-800">{{ $data['as_of'] }}</span></p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b border-gray-300 text-xs font-bold text-gray-700">
                    <tr>
                        <th rowspan="2" class="px-4 py-3 text-right border-l border-gray-200">رمز الحساب</th>
                        <th rowspan="2" class="px-4 py-3 text-right border-l border-gray-200">اسم الحساب</th>
                        <th rowspan="2" class="px-4 py-3 text-right border-l border-gray-200">نوع الحساب</th>
                        <th colspan="2" class="px-4 py-2 text-center border-b border-l border-gray-200">المجاميع الحركة</th>
                        <th colspan="2" class="px-4 py-2 text-center">الأرصدة النهائية</th>
                    </tr>
                    <tr class="bg-gray-50 text-xxs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-2 text-center border-l border-gray-200">مدين (+)</th>
                        <th class="px-4 py-2 text-center border-l border-gray-200">دائن (-)</th>
                        <th class="px-4 py-2 text-center border-l border-gray-200">مدين</th>
                        <th class="px-4 py-2 text-center">دائن</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-mono text-sm">
                    @forelse($data['accounts'] as $acc)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-500 font-semibold border-l border-gray-100">{{ $acc['code'] }}</td>
                            <td class="px-4 py-3 font-sans text-gray-800 font-bold border-l border-gray-100">
                                <a href="{{ route('accounting.coa.show', $acc['account_id']) }}" class="text-blue-600 hover:underline">
                                    {{ $acc['name'] }}
                                </a>
                            </td>
                            <td class="px-4 py-3 font-sans text-gray-500 text-xs border-l border-gray-100">{{ $acc['type'] ?? '-' }}</td>
                            <!-- المجاميع -->
                            <td class="px-4 py-3 text-center text-gray-600 border-l border-gray-100">
                                {{ $acc['total_debit'] > 0 ? number_format($acc['total_debit'], 2) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600 border-l border-gray-100">
                                {{ $acc['total_credit'] > 0 ? number_format($acc['total_credit'], 2) : '-' }}
                            </td>
                            <!-- الأرصدة النهائية -->
                            <td class="px-4 py-3 text-center text-green-700 font-bold border-l border-gray-100 bg-green-50/20">
                                {{ ($acc['balance_debit'] ?? 0) > 0 ? number_format($acc['balance_debit'], 2) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-red-700 font-bold bg-red-50/20">
                                {{ ($acc['balance_credit'] ?? 0) > 0 ? number_format($acc['balance_credit'], 2) : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500 font-sans">لا توجد قيود معتمدة لتوليد التقرير المالي.</td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Totals row -->
                @if(count($data['accounts']) > 0)
                    <tfoot class="bg-gray-100 border-t-2 border-gray-300 font-mono font-bold text-base text-gray-900">
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-left border-l border-gray-200 font-sans">الإجمالي المحاسبي:</td>
                            <td class="px-4 py-4 text-center border-l border-gray-200 text-gray-700">{{ number_format($data['total_debit'], 2) }}</td>
                            <td class="px-4 py-4 text-center border-l border-gray-200 text-gray-700">{{ number_format($data['total_credit'], 2) }}</td>
                            
                            @php
                                $totalBalD = collect($data['accounts'])->sum('balance_debit');
                                $totalBalC = collect($data['accounts'])->sum('balance_credit');
                            @endphp
                            <td class="px-4 py-4 text-center border-l border-gray-200 text-green-700 bg-green-50/30">{{ number_format($totalBalD, 2) }}</td>
                            <td class="px-4 py-4 text-center text-red-700 bg-red-50/30">{{ number_format($totalBalC, 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        @if($data['is_balanced'])
            <div class="p-4 bg-green-50 text-green-800 border-t border-green-200 text-center font-bold text-sm">
                ✅ ميزان المراجعة متوازن ومطابق للمعادلة المحاسبية المزدوجة (Σ مدين = Σ دائن)
            </div>
        @else
            <div class="p-4 bg-red-50 text-red-800 border-t border-red-200 text-center font-bold text-sm animate-pulse">
                ⚠️ تنبيه: يوجد عدم توازن في ميزان المراجعة! يرجى مراجعة قيود التسوية.
            </div>
        @endif
    </div>
</div>

@style
@media print {
    .no-print { display: none !important; }
    body { background: #fff !important; }
}
@endstyle
@endsection
