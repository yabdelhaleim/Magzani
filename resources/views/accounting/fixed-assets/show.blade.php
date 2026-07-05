@extends('layouts.app')

@section('title', 'تفاصيل الأصل الثابت')

@section('content')
<div class="space-y-6 font-sans">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $fixedAsset->name }}</h2>
            <p class="text-gray-600 mt-1">كود الأصل: <span class="font-mono font-bold text-gray-900">{{ $fixedAsset->code }}</span></p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('accounting.fixed-assets.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors">
                العودة للأصول
            </a>
            @if($fixedAsset->status !== 'disposed')
                <button onclick="openDisposalModal()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                    <i class="fas fa-trash-alt ml-1"></i> استبعاد أو بيع الأصل
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
            {!! session('success') !!}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            {!! session('error') !!}
        </div>
    @endif

    <!-- Specifications Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Asset Stats card -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2 space-y-6">
            <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3">المواصفات والبيانات المالية</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                <div>
                    <span class="text-xs text-gray-500 block">تاريخ الشراء</span>
                    <span class="font-bold text-gray-800">{{ $fixedAsset->purchase_date->toDateString() }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">تكلفة الشراء الأصيلة</span>
                    <span class="font-bold text-gray-800 font-mono">{{ number_format($fixedAsset->purchase_cost, 2) }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">القيمة التخريدية (الخردة)</span>
                    <span class="font-bold text-gray-800 font-mono">{{ number_format($fixedAsset->scrap_value, 2) }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">العمر الإنتاجي</span>
                    <span class="font-bold text-gray-800">{{ $fixedAsset->useful_life }} سنة</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">طريقة الإهلاك</span>
                    <span class="font-bold text-gray-800">القسط الثابت (Straight-Line)</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">القيمة الإجمالية القابلة للإهلاك</span>
                    <span class="font-bold text-gray-800 font-mono">{{ number_format($fixedAsset->purchase_cost - $fixedAsset->scrap_value, 2) }}</span>
                </div>
            </div>

            <hr class="border-gray-100">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-red-50/30 p-4 rounded-xl border border-red-100">
                    <span class="text-xs text-red-600 block font-semibold mb-1">مجمع الإهلاك المتراكم</span>
                    <span class="text-2xl font-bold text-red-700 font-mono">{{ number_format($fixedAsset->accumulated_depreciation, 2) }}</span>
                </div>
                <div class="bg-green-50/30 p-4 rounded-xl border border-green-100">
                    <span class="text-xs text-green-600 block font-semibold mb-1">القيمة الدفترية الحالية (Book Value)</span>
                    <span class="text-2xl font-bold text-green-700 font-mono">{{ number_format($fixedAsset->book_value, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Accounts Mapping Card -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 space-y-4">
            <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3">الحسابات المرتبطة</h3>
            
            <div class="space-y-4">
                <div>
                    <span class="text-xs text-gray-500 block">حساب الأصل الثابت</span>
                    <span class="font-medium text-gray-800 block">
                        {{ $fixedAsset->assetAccount->code }} - {{ $fixedAsset->assetAccount->name_ar }}
                    </span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">حساب مجمع الإهلاك</span>
                    <span class="font-medium text-gray-800 block">
                        {{ $fixedAsset->accumulatedDepreciationAccount->code }} - {{ $fixedAsset->accumulatedDepreciationAccount->name_ar }}
                    </span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">حساب مصروف الإهلاك</span>
                    <span class="font-medium text-gray-800 block">
                        {{ $fixedAsset->depreciationExpenseAccount->code }} - {{ $fixedAsset->depreciationExpenseAccount->name_ar }}
                    </span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">الحالة الحالية للأصل</span>
                    <div class="mt-1">
                        @if($fixedAsset->status === 'active')
                            <span class="px-3 py-1 text-xs rounded-full font-semibold bg-green-50 text-green-700 border border-green-200">نشط</span>
                        @elseif($fixedAsset->status === 'fully_depreciated')
                            <span class="px-3 py-1 text-xs rounded-full font-semibold bg-purple-50 text-purple-700 border border-purple-200">مستهلك بالكامل</span>
                        @elseif($fixedAsset->status === 'disposed')
                            <span class="px-3 py-1 text-xs rounded-full font-semibold bg-red-50 text-red-700 border border-red-200">مستبعد/مباع</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Disposal summary if disposed -->
    @if($fixedAsset->status === 'disposed')
        <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 font-sans space-y-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-info-circle text-gray-500"></i>
                <span>بيانات بيع/استبعاد الأصل</span>
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
                <div>
                    <span class="text-gray-500 block">تاريخ الاستبعاد</span>
                    <span class="font-bold text-gray-800">{{ $fixedAsset->disposed_at ? $fixedAsset->disposed_at->toDateString() : '—' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">قيمة الاستبعاد (البيع)</span>
                    <span class="font-bold text-gray-800 font-mono">{{ number_format($fixedAsset->disposal_value ?? 0, 2) }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">صافي الأرباح / الخسائر الرأسمالية</span>
                    <span class="font-bold font-mono {{ ($fixedAsset->disposal_gain_loss ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($fixedAsset->disposal_gain_loss ?? 0, 2) }} 
                        ({{ ($fixedAsset->disposal_gain_loss ?? 0) >= 0 ? 'أرباح' : 'خسائر' }})
                    </span>
                </div>
                <div>
                    <span class="text-gray-500 block">قيد الاستبعاد المالي</span>
                    <span class="font-medium text-gray-800">
                        @if($fixedAsset->disposalEntry)
                            <a href="{{ route('accounting.journal.show', $fixedAsset->disposal_entry_id) }}" class="text-blue-600 hover:underline font-mono">
                                {{ $fixedAsset->disposalEntry->entry_number }}
                            </a>
                        @else
                            —
                        @endif
                    </span>
                </div>
            </div>
        </div>
    @endif

    <!-- Depreciation History -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">سجل الإهلاكات الشهرية المقيّدة</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600">
                    <tr>
                        <th class="px-6 py-4">رقم الحركة</th>
                        <th class="px-6 py-4">تاريخ الإهلاك</th>
                        <th class="px-6 py-4 text-center">مبلغ الإهلاك</th>
                        <th class="px-6 py-4 text-center">رقم قيد اليومية</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    @forelse($fixedAsset->depreciations as $dep)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-gray-500">#{{ $dep->id }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $dep->depreciation_date->toDateString() }}</td>
                            <td class="px-6 py-4 text-center text-red-600 font-bold font-mono">{{ number_format($dep->amount, 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('accounting.journal.show', $dep->journal_entry_id) }}" class="text-blue-600 hover:underline font-mono">
                                    {{ $dep->journalEntry->entry_number }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">لا توجد حركات إهلاك مسجلة بعد لهذا الأصل.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Disposal Modal -->
<div id="disposal_modal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 font-sans">
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 max-w-md w-full mx-4 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">بيع أو استبعاد الأصل الثابت</h3>
            <button onclick="closeDisposalModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <form method="POST" action="{{ route('accounting.fixed-assets.dispose', $fixedAsset->id) }}" class="p-6 space-y-4">
            @csrf
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">قيمة البيع / الاستبعاد (جنيه) *</label>
                <input type="number" step="0.01" name="disposal_value" value="0.00" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono">
                <span class="text-xs text-gray-400 mt-1 block">أدخل 0 إذا كان الاستبعاد شطب بدون مقابل مالي.</span>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الاستبعاد *</label>
                <input type="date" name="disposed_at" value="{{ now()->toDateString() }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">حساب التحصيل النقدي المستلم</label>
                <select name="cash_account_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @foreach($cashAccounts as $acc)
                        <option value="{{ $acc->id }}">
                            {{ $acc->code }} - {{ $acc->name_ar }}
                        </option>
                    @endforeach
                </select>
                <span class="text-xs text-gray-400 mt-1 block">يُستخدم لتحصيل القيمة النقدية المستلمة كمدين (DR).</span>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <button type="button" onclick="closeDisposalModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors">إلغاء</button>
                <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">تأكيد الاستبعاد وتثبيت القيد</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDisposalModal() {
    document.getElementById('disposal_modal').classList.remove('hidden');
}

function closeDisposalModal() {
    document.getElementById('disposal_modal').classList.add('hidden');
}
</script>
@endsection
