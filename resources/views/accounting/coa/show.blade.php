@extends('layouts.app')

@section('title', 'كشف الحساب')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">دفتر أستاذ الحساب: [{{ $account->code }}]</h2>
            <p class="text-gray-600 mt-1">{{ $account->name_ar }} @if($account->name_en) <span class="font-mono text-xs">({{ $account->name_en }})</span> @endif</p>
        </div>
        <a href="{{ route('accounting.coa.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors flex items-center gap-1.5">
            <i class="fas fa-arrow-right"></i> عودة للدليل
        </a>
    </div>

    <!-- Stats & Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <span class="text-xs text-gray-500 block">طبيعة الحساب</span>
            <span class="font-bold text-gray-800 text-lg">
                @if(optional($account->accountType)->normal_balance === 'debit')
                    مدين (DR)
                @else
                    دائن (CR)
                @endif
            </span>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <span class="text-xs text-gray-500 block">نوع الحساب الرئيسي</span>
            <span class="font-bold text-gray-800 text-lg">{{ optional($account->accountType)->name_ar }}</span>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <span class="text-xs text-gray-500 block">الرصيد المتراكم الحالي (Materialized)</span>
            <span class="font-bold text-blue-600 text-xl font-mono">{{ number_format($balance, 2) }}</span>
        </div>
    </div>

    <!-- Date Range Filters -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('accounting.coa.show', $account->id) }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <i class="fas fa-search"></i> تصفية الحركات
                </button>
            </div>
        </form>
    </div>

    <!-- Movements Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-history text-blue-600"></i>
                <span>حركات الحساب التفصيلية</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">رقم القيد</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">التاريخ</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">البيان / الوصف</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">مدين (+)</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">دائن (-)</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-mono">
                    @php 
                        $runningDebit = 0; 
                        $runningCredit = 0; 
                    @endphp
                    @forelse($lines as $line)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">
                                {{ optional($line->journalEntry)->entry_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ optional($line->journalEntry)->entry_date?->toDateString() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800 font-sans">
                                {{ $line->description ?? optional($line->journalEntry)->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-green-600">
                                {{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-red-600">
                                {{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-sans">
                                <a href="{{ route('accounting.journal.show', $line->journal_entry_id) }}" class="text-blue-600 hover:text-blue-900 font-medium">عرض القيد</a>
                            </td>
                        </tr>
                        @php
                            $runningDebit += $line->debit;
                            $runningCredit += $line->credit;
                        @endphp
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500 font-sans">لا توجد حركات مسجلة لهذا الحساب في الفترة المحددة.</td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Summary Footer -->
                @if($lines->count() > 0)
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr class="font-bold text-gray-900">
                            <td colspan="3" class="px-6 py-4 text-left">إجمالي حركات الصفحة:</td>
                            <td class="px-6 py-4 text-center text-green-600">{{ number_format($runningDebit, 2) }}</td>
                            <td class="px-6 py-4 text-center text-red-600">{{ number_format($runningCredit, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        
        @if($lines->hasPages())
            <div class="p-6 bg-gray-50 border-t border-gray-100">
                {{ $lines->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
