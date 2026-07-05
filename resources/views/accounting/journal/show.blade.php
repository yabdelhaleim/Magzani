@extends('layouts.app')

@section('title', 'تفاصيل القيد')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-2xl font-bold text-gray-800">قيد يومية رقم: {{ $journalEntry->entry_number }}</h2>
                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full 
                    @if($journalEntry->status->value === 'posted') bg-green-50 text-green-700 border border-green-200
                    @elseif($journalEntry->status->value === 'draft') bg-yellow-50 text-yellow-700 border border-yellow-200
                    @else bg-gray-50 text-gray-700 border border-gray-200
                    @endif">
                    {{ $journalEntry->status->label() }}
                </span>
            </div>
            <p class="text-gray-600 mt-1">تاريخ القيد: {{ $journalEntry->entry_date->toDateString() }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('accounting.journal.print', $journalEntry->id) }}" target="_blank"
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors flex items-center gap-1.5">
                <i class="fas fa-print"></i> طباعة القيد
            </a>
            <a href="{{ route('accounting.journal.index') }}" 
               class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors flex items-center gap-1.5">
                <i class="fas fa-arrow-right"></i> عودة للدفتر
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

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Entry Lines List (Ledger Sheet) -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-list-ul text-blue-600"></i>
                    <span>أسطر وتفاصيل القيد</span>
                </h3>
            </div>
            
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">الحساب</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 w-1/4">مدين (+)</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 w-1/4">دائن (-)</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">البيان التفصيلي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-mono">
                    @foreach($journalEntry->lines as $line)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-800 font-sans">
                                <a href="{{ route('accounting.coa.show', $line->account_id) }}" class="text-blue-600 hover:underline">
                                    {{ $line->account->code }} - {{ $line->account->name_ar }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-bold text-green-600">
                                {{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-bold text-red-600">
                                {{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-sans">
                                {{ $line->description ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200 font-mono font-bold text-base">
                    <tr>
                        <td class="px-4 py-3 text-left">الإجمالي:</td>
                        <td class="px-4 py-3 text-center text-green-600">{{ number_format($totalDebit, 2) }}</td>
                        <td class="px-4 py-3 text-center text-red-600">{{ number_format($totalCredit, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Meta Details & Posting Actions -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-lg font-bold text-gray-800 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <i class="fas fa-info-circle text-blue-600"></i>
                    <span>بيانات القيد العامة</span>
                </h3>
                
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-xs text-gray-400 block">البيان العام</span>
                        <span class="font-medium text-gray-800 leading-relaxed block">{{ $journalEntry->description }}</span>
                    </div>
                    @if($journalEntry->reference)
                        <div>
                            <span class="text-xs text-gray-400 block">المرجع</span>
                            <span class="font-mono text-gray-800 block">{{ $journalEntry->reference }}</span>
                        </div>
                    @endif
                    <div>
                        <span class="text-xs text-gray-400 block">المصدر التشغيلي</span>
                        <span class="font-semibold text-gray-800 font-mono bg-gray-50 px-2 py-0.5 rounded border border-gray-100 inline-block mt-0.5">
                            {{ $journalEntry->source_type }}
                        </span>
                    </div>
                    @if($journalEntry->currency_code)
                        <div>
                            <span class="text-xs text-gray-400 block">عملة القيد</span>
                            <span class="font-bold text-gray-800">{{ $journalEntry->currency_code }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Context Actions Panel -->
            @if($journalEntry->status->value === 'draft')
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <i class="fas fa-tasks text-blue-600"></i>
                        <span>إجراءات قيد المسودة</span>
                    </h3>
                    <p class="text-xs text-gray-500 mb-4 leading-relaxed">هذا القيد مسودة معلقة لم تُرحل لدفتر الأستاذ العام بعد ولم تؤثر على أرصدة الحسابات.</p>
                    <form method="POST" action="{{ route('accounting.journal.post', $journalEntry->id) }}">
                        @csrf
                        <button type="submit" class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition-colors flex items-center justify-center gap-1.5 shadow">
                            <i class="fas fa-check-double"></i> اعتماد وترحيل القيد لـ GL
                        </button>
                    </form>
                </div>
            @elseif($journalEntry->status->value === 'posted' && !$journalEntry->reversed_entry_id && !$journalEntry->reversal_of_id)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <i class="fas fa-undo-alt text-yellow-600"></i>
                        <span>عكس القيد المالي</span>
                    </h3>
                    <p class="text-xs text-gray-500 mb-4 leading-relaxed">القيد المعتمد نهائي ولا يمكن تعديله أو حذفه. لإصلاح الخطأ، يمكنك عكس القيد (إنشاء قيد معاكس تماماً).</p>
                    <form method="POST" action="{{ route('accounting.journal.reverse', $journalEntry->id) }}" onsubmit="return confirm('⚠️ هل أنت متأكد من عكس القيد؟ سيقوم النظام بإنشاء قيد عكسي معتمد تلقائياً لعكس أرصدة الحسابات.')">
                        @csrf
                        <button type="submit" class="w-full py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-semibold transition-colors flex items-center justify-center gap-1.5 shadow">
                            <i class="fas fa-undo"></i> عكس القيد (Reversal)
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
