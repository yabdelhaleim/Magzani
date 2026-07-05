@extends('layouts.app')

@section('title', 'تسجيل قيد يدوي')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="journalForm()">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">تسجيل قيد يومية يدوي (New Journal Entry)</h2>
            <p class="text-gray-600 mt-1">تسجيل حركات تسوية يدوية مباشرة في دفتر الأستاذ العام</p>
        </div>
        <a href="{{ route('accounting.journal.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors flex items-center gap-1.5">
            <i class="fas fa-arrow-right"></i> عودة للدفتر
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('accounting.journal.store') }}" @submit="validateForm($event)" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf

        <div class="p-6 space-y-6">
            <!-- Entry Meta Fields -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6 border-b border-gray-100">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ القيد *</label>
                    <input type="date" name="entry_date" required value="{{ old('entry_date', now()->toDateString()) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">المرجع (Reference)</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" placeholder="رقم الفاتورة أو المستند..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الخيارات</label>
                    <div class="flex items-center gap-2 mt-2.5">
                        <input type="checkbox" name="post_immediately" id="post_immediately" value="1" checked
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="post_immediately" class="text-sm font-semibold text-gray-800 cursor-pointer">اعتماد القيد وترحيله فوراً</label>
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">البيان العام للقيد *</label>
                    <textarea name="description" required rows="2" placeholder="اكتب بياناً شاملاً يوضح طبيعة التسوية المالية..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Dynamic Entry Lines Table -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center justify-between">
                    <span>أسطر القيد المالي</span>
                    <button type="button" @click="addLine()" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-100 rounded-lg text-sm font-semibold transition-colors flex items-center gap-1.5">
                        <i class="fas fa-plus"></i> إضافة سطر جديد
                    </button>
                </h3>

                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="w-full min-w-[800px]">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 w-1/3">الحساب المالي</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 w-1/6">مدين (Debit)</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 w-1/6">دائن (Credit)</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">البيان التفصيلي للسطر</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="(line, index) in lines" :key="index">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-3">
                                        <select :name="'lines['+index+'][account_id]'" required x-model="line.account_id"
                                                class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                            <option value="">-- اختر الحساب --</option>
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}">
                                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-3">
                                        <input type="number" :name="'lines['+index+'][debit]'" step="0.01" min="0" x-model.number="line.debit" @input="onDebitInput(index)" placeholder="0.00"
                                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center font-mono font-bold text-sm">
                                    </td>
                                    <td class="p-3">
                                        <input type="number" :name="'lines['+index+'][credit]'" step="0.01" min="0" x-model.number="line.credit" @input="onCreditInput(index)" placeholder="0.00"
                                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center font-mono font-bold text-sm">
                                    </td>
                                    <td class="p-3">
                                        <input type="text" :name="'lines['+index+'][description]'" x-model="line.description" placeholder="بيان توضيحي لهذا السطر..."
                                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                    </td>
                                    <td class="p-3 text-center">
                                        <button type="button" @click="removeLine(index)" :disabled="lines.length <= 2"
                                                class="text-gray-400 hover:text-red-600 disabled:opacity-30 disabled:hover:text-gray-400 transition-colors">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <!-- Running Totals & Balance indicator -->
                        <tfoot class="bg-gray-50 border-t border-gray-200 font-mono font-bold">
                            <tr class="text-gray-800">
                                <td class="px-4 py-3 text-left">الإجمالي:</td>
                                <td class="px-4 py-3 text-center text-green-600 text-base" x-text="formatCurrency(totalDebit)"></td>
                                <td class="px-4 py-3 text-center text-red-600 text-base" x-text="formatCurrency(totalCredit)"></td>
                                <td colspan="2" class="px-4 py-3"></td>
                            </tr>
                            <tr class="bg-blue-50 text-blue-900">
                                <td class="px-4 py-3 text-left">فارق التوازن:</td>
                                <td colspan="2" class="px-4 py-3 text-center text-lg" :class="isBalanced() ? 'text-green-600' : 'text-red-600 animate-pulse'">
                                    <span x-text="formatCurrency(balanceDiff())"></span>
                                    <span class="text-xs font-semibold mr-2 font-sans" x-text="isBalanced() ? '✅ متوازن ومطابق' : '❌ غير متوازن'"></span>
                                </td>
                                <td colspan="2" class="px-4 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all shadow hover:shadow-md">
                <i class="fas fa-save ml-1.5"></i> حفظ وتسجيل القيد
            </button>
        </div>
    </form>
</div>

<script>
    function journalForm() {
        return {
            lines: [
                { account_id: '', debit: 0, credit: 0, description: '' },
                { account_id: '', debit: 0, credit: 0, description: '' }
            ],
            
            addLine() {
                this.lines.push({ account_id: '', debit: 0, credit: 0, description: '' });
            },
            
            removeLine(index) {
                if (this.lines.length > 2) {
                    this.lines.splice(index, 1);
                }
            },
            
            onDebitInput(index) {
                if (this.lines[index].debit > 0) {
                    this.lines[index].credit = 0;
                }
            },
            
            onCreditInput(index) {
                if (this.lines[index].credit > 0) {
                    this.lines[index].debit = 0;
                }
            },
            
            get totalDebit() {
                return this.lines.reduce((sum, line) => sum + (Number(line.debit) || 0), 0);
            },
            
            get totalCredit() {
                return this.lines.reduce((sum, line) => sum + (Number(line.credit) || 0), 0);
            },
            
            balanceDiff() {
                return Math.abs(this.totalDebit - this.totalCredit);
            },
            
            isBalanced() {
                return this.balanceDiff() < 0.01;
            },
            
            formatCurrency(val) {
                return val.toFixed(2);
            },
            
            validateForm(event) {
                if (!this.isBalanced()) {
                    event.preventDefault();
                    alert("❌ لا يمكن تسجيل القيد: مجموع المدين يجب أن يساوي مجموع الدائن تماماً.");
                    return false;
                }
                
                // التأكد أن كل سطر فيه حساب وقيمة
                for (let i = 0; i < this.lines.length; i++) {
                    const l = this.lines[i];
                    if (!l.account_id) {
                        event.preventDefault();
                        alert(`❌ يرجى اختيار الحساب في السطر رقم ${i + 1}`);
                        return false;
                    }
                    if ((l.debit || 0) === 0 && (l.credit || 0) === 0) {
                        event.preventDefault();
                        alert(`❌ يرجى كتابة قيمة مدين أو دائن في السطر رقم ${i + 1}`);
                        return false;
                    }
                }
                return true;
            }
        };
    }
</script>
@endsection
