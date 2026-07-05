@extends('layouts.app')

@section('title', 'تفاصيل سند القبض')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">سند قبض رقم: {{ $journalEntry->entry_number }}</h2>
            <p class="text-gray-600 mt-1">تاريخ الإصدار: {{ $journalEntry->entry_date->toDateString() }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('accounting.vouchers.receipt.print', $journalEntry->id) }}" target="_blank"
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors flex items-center gap-1.5">
                <i class="fas fa-print"></i> طباعة السند
            </a>
            <a href="{{ route('accounting.vouchers.receipt.index') }}" 
               class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors flex items-center gap-1.5">
                <i class="fas fa-arrow-right"></i> عودة للقائمة
            </a>
        </div>
    </div>

    <!-- Voucher Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
        <!-- Details grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-gray-100">
            <div>
                <span class="text-xs text-gray-400 block">المبلغ المقبوض</span>
                <span class="font-mono text-2xl font-bold text-green-600 mt-1 block">
                    {{ number_format($journalEntry->total_debit, 2) }} {{ \App\Models\AccountingSetting::value('default_currency') ?? 'SAR' }}
                </span>
            </div>
            @if($journalEntry->reference)
                <div>
                    <span class="text-xs text-gray-400 block">رقم المرجع (الشيك/التحويل)</span>
                    <span class="font-mono text-lg font-bold text-gray-800 mt-1 block">{{ $journalEntry->reference }}</span>
                </div>
            @endif
            <div class="md:col-span-2">
                <span class="text-xs text-gray-400 block">البيان والشرح</span>
                <span class="font-medium text-gray-800 text-base mt-1 block leading-relaxed">{{ $journalEntry->description }}</span>
            </div>
        </div>

        <!-- Ledger Entries mapping -->
        <div>
            <h3 class="text-sm font-bold text-gray-500 mb-4 uppercase tracking-wider">التأثير المحاسبي في الحسابات (GL Lines)</h3>
            <div class="border border-gray-100 rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-right">الحساب</th>
                            <th class="px-4 py-3 text-center w-1/4">مدين (+)</th>
                            <th class="px-4 py-3 text-center w-1/4">دائن (-)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 font-mono text-sm">
                        @foreach($journalEntry->lines as $line)
                            <tr>
                                <td class="px-4 py-3 text-gray-800 font-sans">
                                    {{ $line->account->code }} - {{ $line->account->name_ar }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-green-600">
                                    {{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-red-600">
                                    {{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
