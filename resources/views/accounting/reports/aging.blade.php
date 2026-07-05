@extends('layouts.app')

@section('title', 'تقرير تقادم الديون')

@section('content')
<div class="space-y-6 font-sans">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100 no-print">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">تقرير تقادم الديون (Debt Aging Report)</h2>
            <p class="text-gray-600 mt-1">تحليل فترات تأخر الديون المستحقة على العملاء أو للموردين حسب فئات الأيام</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors">
            <i class="fas fa-print ml-1"></i> طباعة التقرير
        </button>
    </div>

    <!-- Filters (No Print) -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 no-print">
        <form method="GET" action="{{ route('accounting.reports.aging') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع التقادم</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="receivable" {{ request('type') === 'receivable' ? 'selected' : '' }}>أعمار الذمم المدينة (العملاء)</option>
                    <option value="payable" {{ request('type') === 'payable' ? 'selected' : '' }}>أعمار الذمم الدائنة (الموردين)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">في تاريخ</label>
                <input type="date" name="as_of" value="{{ request('as_of', $asOf) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-1.5 w-full">
                    <i class="fas fa-search"></i> عرض تقرير الأعمار
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Box (Buckets Grid) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <span class="text-xs text-gray-400 block font-semibold mb-1">0 - 30 يوم</span>
            <span class="font-mono text-xl font-bold text-gray-800">{{ number_format($data['buckets']['0-30'], 2) }}</span>
            <div class="w-full bg-gray-100 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-green-500 h-1.5" style="width: {{ $data['total'] > 0 ? ($data['buckets']['0-30'] / $data['total']) * 100 : 0 }}%"></div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <span class="text-xs text-gray-400 block font-semibold mb-1">31 - 60 يوم</span>
            <span class="font-mono text-xl font-bold text-gray-800">{{ number_format($data['buckets']['31-60'], 2) }}</span>
            <div class="w-full bg-gray-100 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-yellow-500 h-1.5" style="width: {{ $data['total'] > 0 ? ($data['buckets']['31-60'] / $data['total']) * 100 : 0 }}%"></div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <span class="text-xs text-gray-400 block font-semibold mb-1">61 - 90 يوم</span>
            <span class="font-mono text-xl font-bold text-gray-800">{{ number_format($data['buckets']['61-90'], 2) }}</span>
            <div class="w-full bg-gray-100 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-orange-500 h-1.5" style="width: {{ $data['total'] > 0 ? ($data['buckets']['61-90'] / $data['total']) * 100 : 0 }}%"></div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <span class="text-xs text-gray-400 block font-semibold mb-1">فوق 90 يوم (خطرة)</span>
            <span class="font-mono text-xl font-bold text-red-600">{{ number_format($data['buckets']['90+'], 2) }}</span>
            <div class="w-full bg-gray-100 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-red-500 h-1.5" style="width: {{ $data['total'] > 0 ? ($data['buckets']['90+'] / $data['total']) * 100 : 0 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Invoices Aging Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-100 text-center">
            <h3 class="text-xl font-bold text-gray-800">تفاصيل الفواتير والمديونيات المفتوحة</h3>
            <p class="text-sm text-gray-500 mt-1">التقرير حتى تاريخ: <span class="font-mono font-bold text-gray-800">{{ $data['as_of'] }}</span></p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b border-gray-200 text-xs font-semibold text-gray-600">
                    <tr>
                        <th class="px-6 py-4 text-right">المستفيد / العميل</th>
                        <th class="px-6 py-4 text-right">رقم الفاتورة</th>
                        <th class="px-6 py-4 text-center">تاريخ الفاتورة</th>
                        <th class="px-6 py-4 text-center">العمر (بالأيام)</th>
                        <th class="px-6 py-4 text-center">الفئة العمرية</th>
                        <th class="px-6 py-4 text-center">المبلغ المتبقي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-mono text-sm">
                    @forelse($data['rows'] as $row)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-sans text-gray-800 font-bold text-right">{{ $row['party'] }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-600">{{ $row['invoice_no'] }}</td>
                            <td class="px-6 py-4 text-center text-gray-500">{{ $row['date'] }}</td>
                            <td class="px-6 py-4 text-center font-bold text-gray-800">{{ $row['days'] }} يوم</td>
                            <td class="px-6 py-4 text-center font-sans">
                                <span class="px-2 py-0.5 text-xs rounded-full font-semibold
                                    @if($row['bucket'] === '0-30') bg-green-50 text-green-700 border border-green-200
                                    @elseif($row['bucket'] === '31-60') bg-yellow-50 text-yellow-700 border border-yellow-200
                                    @elseif($row['bucket'] === '61-90') bg-orange-50 text-orange-700 border border-orange-200
                                    @else bg-red-50 text-red-700 border border-red-200 animate-pulse
                                    @endif">
                                    {{ $row['bucket'] }} يوم
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-gray-900">{{ number_format($row['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500 font-sans">لا توجد فواتير أو مستحقات غير مسددة في هذه الفئة.</td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Summary footer -->
                @if(count($data['rows']) > 0)
                    <tfoot class="bg-gray-100 border-t border-gray-300 font-mono font-bold text-base text-gray-900">
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-left font-sans">إجمالي المديونيات المتأخرة:</td>
                            <td class="px-6 py-4 text-center text-red-600">{{ number_format($data['total'], 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
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
