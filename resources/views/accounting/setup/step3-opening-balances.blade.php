@extends('layouts.app')

@section('title', 'إعداد المحاسبة — الأرصدة الافتتاحية')

@section('content')
<div class="space-y-6">
    @include('accounting.setup.partials.wizard-header', ['currentStep' => 3])

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="p-4 bg-blue-50 text-blue-700 rounded-lg border border-blue-200">{{ session('info') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
                <i class="fas fa-balance-scale text-blue-600"></i>
                إدخال الأرصدة الافتتاحية
            </h3>
            <p class="text-gray-600 mb-4">أدخل أرصدة الحسابات الفعلية لبداية السنة المالية. يمكنك تخطي هذه الخطوة إذا كنت تبدأ من الصفر.</p>

            @if($hasOpeningEntry)
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg mb-4">
                    <p class="text-green-800 font-medium"><i class="fas fa-check-circle ml-1"></i> تم إدخال الأرصدة الافتتاحية بالفعل.</p>
                </div>
                <div class="flex justify-end">
                    <a href="{{ route('accounting.setup.step', 4) }}" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2">
                        المتابعة <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            @else
                <form method="POST" action="{{ route('accounting.setup.save-opening-balances') }}">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700">الكود</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700">اسم الحساب</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700">النوع</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 w-40">مدين</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 w-40">دائن</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($accounts as $account)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-mono text-gray-800">{{ $account->code }}</td>
                                    <td class="px-4 py-2 text-gray-800">{{ $account->name_ar }}</td>
                                    <td class="px-4 py-2">
                                        <span class="text-xs text-gray-500">{{ $account->accountType->name_ar }}</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" step="0.01" min="0"
                                            name="balances[{{ $account->id }}][debit]"
                                            value="{{ old("balances.{$account->id}.debit", 0) }}"
                                            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-center text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent balance-input"
                                            data-side="debit" data-row="{{ $account->id }}">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" step="0.01" min="0"
                                            name="balances[{{ $account->id }}][credit]"
                                            value="{{ old("balances.{$account->id}.credit", 0) }}"
                                            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-center text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent balance-input"
                                            data-side="credit" data-row="{{ $account->id }}">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-100 font-bold">
                                    <td colspan="3" class="px-4 py-3 text-left">الإجمالي</td>
                                    <td class="px-4 py-3 text-center" id="totalDebit">0.00</td>
                                    <td class="px-4 py-3 text-center" id="totalCredit">0.00</td>
                                </tr>
                                <tr id="balanceDiffRow" class="hidden">
                                    <td colspan="3" class="px-4 py-2 text-left text-red-600 font-bold">الفرق</td>
                                    <td colspan="2" class="px-4 py-2 text-center text-red-600 font-bold" id="balanceDiff"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="flex justify-between pt-4 mt-4 border-t border-gray-100">
                        <div class="flex gap-3">
                            <a href="{{ route('accounting.setup.step', 2) }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                                <i class="fas fa-arrow-right ml-1"></i> السابقة
                            </a>
                            <a href="{{ route('accounting.setup.step', 4) }}" class="px-4 py-2 text-yellow-600 hover:text-yellow-800">
                                تخطي هذه الخطوة <i class="fas fa-forward mr-1"></i>
                            </a>
                        </div>
                        <button type="submit" id="submitBtn" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2">
                            حفظ الأرصدة والمتابعة
                            <i class="fas fa-arrow-left"></i>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.balance-input');
    const totalDebitEl = document.getElementById('totalDebit');
    const totalCreditEl = document.getElementById('totalCredit');
    const diffEl = document.getElementById('balanceDiff');
    const diffRow = document.getElementById('balanceDiffRow');

    function recalc() {
        let totalDebit = 0, totalCredit = 0;
        inputs.forEach(input => {
            const val = parseFloat(input.value) || 0;
            if (input.dataset.side === 'debit') totalDebit += val;
            else totalCredit += val;
        });
        totalDebitEl.textContent = totalDebit.toFixed(2);
        totalCreditEl.textContent = totalCredit.toFixed(2);

        const diff = Math.abs(totalDebit - totalCredit);
        if (diff > 0.01) {
            diffRow.classList.remove('hidden');
            diffEl.textContent = diff.toFixed(2);
        } else {
            diffRow.classList.add('hidden');
        }
    }

    inputs.forEach(input => input.addEventListener('input', recalc));
    recalc();
});
</script>
@endpush
@endsection
