@php
    $existingLines = isset($recurring)
        ? $recurring->lines->map(fn($l) => [
            'account_id'  => (string) $l->account_id,
            'debit'       => $l->debit,
            'credit'      => $l->credit,
            'description' => $l->description ?? '',
        ])->values()->all()
        : [];
@endphp

<div class="max-w-6xl mx-auto space-y-6" x-data="recurringForm(@json($existingLines))">
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ isset($recurring) ? 'تعديل قالب' : 'قالب قيد متكرر جديد' }}</h2>
        </div>
        <a href="{{ route('accounting.recurring.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg"><i class="fas fa-arrow-right"></i> عودة</a>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            <ul class="list-disc list-inside text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ isset($recurring) ? route('accounting.recurring.update', $recurring) : route('accounting.recurring.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
        @csrf
        @if(isset($recurring)) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">اسم القالب *</label>
                <input type="text" name="template_name" required value="{{ old('template_name', $recurring->template_name ?? '') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">التكرار *</label>
                <select name="frequency" required class="w-full px-3 py-2 border rounded-lg">
                    @foreach(['daily'=>'يومي','weekly'=>'أسبوعي','monthly'=>'شهري','quarterly'=>'ربع سنوي','yearly'=>'سنوي'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('frequency', $recurring->frequency ?? 'monthly') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">تاريخ التشغيل القادم *</label>
                <input type="date" name="next_run_date" required value="{{ old('next_run_date', isset($recurring) ? $recurring->next_run_date->toDateString() : now()->toDateString()) }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">تاريخ الانتهاء</label>
                <input type="date" name="end_date" value="{{ old('end_date', $recurring->end_date?->toDateString() ?? '') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">البيان *</label>
                <textarea name="description" required rows="2" class="w-full px-3 py-2 border rounded-lg">{{ old('description', $recurring->description ?? '') }}</textarea>
            </div>
            <div class="flex gap-6">
                <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $recurring->is_active ?? true))> نشط</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="auto_post" value="1" @checked(old('auto_post', $recurring->auto_post ?? true))> اعتماد تلقائي</label>
            </div>
        </div>

        <div>
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-gray-800">أسطر القيد</h3>
                <button type="button" @click="addLine()" class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg text-sm"><i class="fas fa-plus"></i> سطر</button>
            </div>
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full min-w-[700px]">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-3 py-2 text-right text-xs">الحساب</th>
                        <th class="px-3 py-2 text-center text-xs">مدين</th>
                        <th class="px-3 py-2 text-center text-xs">دائن</th>
                        <th class="px-3 py-2 text-right text-xs">البيان</th>
                        <th class="w-10"></th>
                    </tr></thead>
                    <tbody>
                        <template x-for="(line, index) in lines" :key="index">
                            <tr class="border-t">
                                <td class="px-3 py-2">
                                    <select :name="'lines['+index+'][account_id]'" x-model="line.account_id" required class="w-full px-2 py-1 border rounded text-sm">
                                        <option value="">— اختر —</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name_ar }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2"><input type="number" step="0.01" min="0" :name="'lines['+index+'][debit]'" x-model="line.debit" class="w-full px-2 py-1 border rounded text-sm text-center"></td>
                                <td class="px-3 py-2"><input type="number" step="0.01" min="0" :name="'lines['+index+'][credit]'" x-model="line.credit" class="w-full px-2 py-1 border rounded text-sm text-center"></td>
                                <td class="px-3 py-2"><input type="text" :name="'lines['+index+'][description]'" x-model="line.description" class="w-full px-2 py-1 border rounded text-sm"></td>
                                <td class="px-3 py-2 text-center"><button type="button" @click="removeLine(index)" class="text-red-500"><i class="fas fa-times"></i></button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium">حفظ القالب</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function recurringForm(existing) {
    return {
        lines: existing.length ? existing : [
            { account_id: '', debit: '', credit: '', description: '' },
            { account_id: '', debit: '', credit: '', description: '' },
        ],
        addLine() { this.lines.push({ account_id: '', debit: '', credit: '', description: '' }); },
        removeLine(i) { if (this.lines.length > 2) this.lines.splice(i, 1); },
    };
}
</script>
@endpush
