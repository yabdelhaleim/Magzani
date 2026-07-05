@extends('layouts.app')

@section('title', 'دفتر الأستاذ العام')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100 font-sans">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">دفتر الأستاذ العام (General Ledger)</h2>
            <p class="text-gray-600 mt-1">كشف حركات تفصيلي ورصيد افتتاحي وختامي لأي حساب مالي خلال فترة محددة</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors no-print">
            <i class="fas fa-print ml-1"></i> طباعة التقرير
        </button>
    </div>

    <!-- Filters (No Print) -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 no-print">
        <form method="GET" action="{{ route('accounting.reports.general-ledger') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحساب المالي</label>
                <select name="account_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">-- اختر الحساب --</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                            {{ $acc->code }} - {{ $acc->name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>
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
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-1.5 w-full">
                    <i class="fas fa-search"></i> عرض كشف الحساب
                </button>
            </div>
        </form>
    </div>

    @if($ledger)
        <!-- General Ledger Sheet -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden font-sans">
            <!-- Sheet Header -->
            <div class="p-6 bg-gray-50 border-b border-gray-100 text-center">
                <h3 class="text-xl font-bold text-gray-800">كشف حركات دفتر الأستاذ التفصيلي</h3>
                <p class="text-sm text-gray-600 mt-1">حساب: <span class="font-bold text-gray-800">{{ $account->code }} - {{ $account->name_ar }}</span></p>
                <p class="text-xs text-gray-400 mt-1 font-mono">للفترة من: {{ $from }} إلى: {{ $to }}</p>
            </div>

            <!-- Ledger Table -->
            <table class="w-full">
                <thead class="bg-gray-100 border-b border-gray-300 text-xs font-semibold text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-right">رقم القيد</th>
                        <th class="px-4 py-3 text-right">التاريخ</th>
                        <th class="px-4 py-3 text-right">البيان التفصيلي</th>
                        <th class="px-4 py-3 text-center w-32">مدين (+)</th>
                        <th class="px-4 py-3 text-center w-32">دائن (-)</th>
                        <th class="px-4 py-3 text-center w-36">الرصيد الجاري</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-mono text-sm">
                    <!-- Opening Balance Row -->
                    <tr class="bg-blue-50/20 font-bold text-blue-900">
                        <td colspan="3" class="px-4 py-3 text-left font-sans text-xs uppercase tracking-wider">الرصيد الافتتاحي المُرّحل:</td>
                        <td colspan="2" class="px-4 py-3"></td>
                        <td class="px-4 py-3 text-center">{{ number_format($ledger['opening_balance'], 2) }}</td>
                    </tr>

                    <!-- Movements -->
                    @php $currBalance = $ledger['opening_balance']; @endphp
                    @forelse($ledger['lines'] as $line)
                        @php
                            // تحديث الرصيد الجاري محلياً وفق طبيعة الحساب
                            $normal = optional($account->accountType)->normal_balance ?? 'debit';
                            if ($normal === 'debit') {
                                $currBalance += ($line['debit'] - $line['credit']);
                            } else {
                                $currBalance += ($line['credit'] - $line['debit']);
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-800 font-sans">
                                <a href="{{ route('accounting.journal.show', $line['journal_entry_id']) }}" class="text-blue-600 hover:underline">
                                    {{ $line['entry_number'] }}
                                </a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 font-sans">{{ $line['date'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 font-sans max-w-sm truncate">{{ $line['description'] }}</td>
                            <td class="px-4 py-3 text-center text-green-600 font-bold">
                                {{ $line['debit'] > 0 ? number_format($line['debit'], 2) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-red-600 font-bold">
                                {{ $line['credit'] > 0 ? number_format($line['credit'], 2) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-gray-900 bg-gray-50/20">{{ number_format($currBalance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-500 font-sans">لا توجد حركات مسجلة للحساب خلال الفترة المحددة.</td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Footer with Closing Balance -->
                <tfoot class="bg-gray-100 border-t-2 border-gray-300 font-mono font-bold text-base text-gray-900">
                    <tr class="bg-gray-50">
                        <td colspan="3" class="px-4 py-4 text-left font-sans">مجموع حركات الفترة:</td>
                        <td class="px-4 py-4 text-center text-green-600">{{ number_format($ledger['total_debit'], 2) }}</td>
                        <td class="px-4 py-4 text-center text-red-600">{{ number_format($ledger['total_credit'], 2) }}</td>
                        <td></td>
                    </tr>
                    <tr class="bg-blue-50/30 text-blue-900 text-lg">
                        <td colspan="3" class="px-4 py-4 text-left font-sans">الرصيد الختامي للفترة:</td>
                        <td colspan="2" class="px-4 py-4"></td>
                        <td class="px-4 py-4 text-center">{{ number_format($ledger['closing_balance'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="bg-white p-12 rounded-xl border border-gray-100 text-center text-gray-500 font-sans">
            <i class="fas fa-search text-4xl text-gray-300 mb-3"></i>
            <p>يرجى اختيار الحساب وتحديد فترة التاريخ للبحث وعرض كشف حركاته.</p>
        </div>
    @endif
</div>

@style
@media print {
    .no-print { display: none !important; }
    body { background: #fff !important; }
}
@endstyle
@endsection
