@extends('layouts.app')

@section('title', 'لوحة التحكم المحاسبية')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">لوحة التحكم المحاسبية</h2>
            <p class="text-gray-600 mt-1">متابعة الدفاتر، سلامة التوازن، والحالة المالية للمستأجر</p>
        </div>
        <div class="flex gap-3">
            @if($currentYear)
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-lg font-medium border border-blue-100">
                    <i class="fas fa-calendar-check"></i>
                    <span>السنة المالية الحالية: {{ $currentYear->name }}</span>
                </span>
            @else
                <a href="{{ route('accounting.fiscal.index') }}" class="px-4 py-2 bg-yellow-50 text-yellow-700 rounded-lg font-medium border border-yellow-100 hover:bg-yellow-100 transition-colors">
                    <i class="fas fa-exclamation-triangle ml-1"></i>
                    لم يتم إعداد سنة مالية جارية! اضغط لإعدادها.
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200 font-sans">
            {!! session('success') !!}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 font-sans">
            {!! session('error') !!}
        </div>
    @endif

    @if($pendingPostingFailures > 0)
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                <div>
                    <p class="font-bold text-red-800">{{ $pendingPostingFailures }} عملية ترحيل فاشلة تحتاج مراجعة</p>
                    <p class="text-sm text-red-600">عمليات لم تُرحَّل لدفتر الأستاذ العام — قد يسبب فرقاً بين sub-ledger و GL</p>
                </div>
            </div>
            <a href="{{ route('accounting.posting-failures.index') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 whitespace-nowrap">
                مراجعة الآن
            </a>
        </div>
    @endif

    @if($staleDrafts > 0)
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg flex items-center gap-3">
            <i class="fas fa-clock text-yellow-600"></i>
            <p class="text-yellow-800">{{ $staleDrafts }} قيد مسودة أقدم من 3 أيام — يُنصح باعتمادها أو حذفها</p>
        </div>
    @endif

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">إجمالي القيود</p>
                <h3 class="text-3xl font-bold text-gray-800">{{ $stats['total_entries'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center text-blue-600">
                <i class="fas fa-book text-xl"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">القيود المعتمدة</p>
                <h3 class="text-3xl font-bold text-green-600">{{ $stats['posted_entries'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center text-green-600">
                <i class="fas fa-file-invoice text-xl"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">قيود مسودة</p>
                <h3 class="text-3xl font-bold text-yellow-600">{{ $stats['draft_entries'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-yellow-50 rounded-full flex items-center justify-center text-yellow-600">
                <i class="fas fa-edit text-xl"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">قيود معلقة غير متوازنة</p>
                <h3 class="text-3xl font-bold {{ $stats['unbalanced'] > 0 ? 'text-red-600 animate-pulse' : 'text-gray-800' }}">
                    {{ $stats['unbalanced'] }}
                </h3>
            </div>
            <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center text-red-600">
                <i class="fas fa-balance-scale-left text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Health Check & Warnings -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Integrity Check Panel -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-shield-alt text-blue-600"></i>
                <span>فحص سلامة النظام المحاسبي</span>
            </h3>
            
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-100 flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="w-4 h-4 rounded-full bg-green-500 inline-block animate-ping"></span>
                    <div>
                        <p class="font-medium text-gray-800">حالة التوازن المحاسبي العام</p>
                        <p class="text-xs text-gray-500 mt-0.5">يقارن مجموع المدين والدائن للقيود المعتمَدة لضمان استقرار المعادلة المحاسبية</p>
                    </div>
                </div>
                <button onclick="runIntegrityCheck()" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-700 rounded-lg text-sm border border-gray-200 transition-colors">
                    إعادة الفحص الآن
                </button>
            </div>

            <div id="integrity-result" class="hidden p-4 rounded-lg border"></div>
        </div>

        <!-- Sidebar Warning/Fiscal Period Panel -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-hourglass-half text-yellow-600"></i>
                <span>الفترة الجارية</span>
            </h3>
            
            @if($currentPeriod)
                <div class="space-y-3">
                    <div>
                        <span class="text-xs text-gray-500 block">اسم الفترة</span>
                        <span class="font-bold text-gray-800 text-lg">{{ $currentPeriod->name }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-gray-500 block">تاريخ البدء</span>
                            <span class="font-medium text-gray-700">{{ $currentPeriod->start_date->toDateString() }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 block">تاريخ الانتهاء</span>
                            <span class="font-medium text-gray-700">{{ $currentPeriod->end_date->toDateString() }}</span>
                        </div>
                    </div>
                    <div class="pt-3 border-t border-gray-100 flex justify-between items-center">
                        <span class="inline-flex items-center gap-1.5 text-xs text-green-600 bg-green-50 px-2.5 py-1 rounded-full font-medium">
                            <i class="fas fa-lock-open"></i> مفتوحة للترحيل
                        </span>
                        <a href="{{ route('accounting.fiscal.index') }}" class="text-sm text-blue-600 hover:underline">إدارة الفترات ←</a>
                    </div>
                </div>
            @else
                <p class="text-gray-500 text-sm">لا توجد فترة مالية مفتوحة حالياً للشهر الجاري!</p>
            @endif
        </div>
    </div>

    {{-- Financial Ratios Quick View --}}
    @if(isset($ratios))
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-chart-pie text-purple-600"></i>
                <span>مؤشرات مالية سريعة</span>
            </h3>
            <a href="{{ route('accounting.reports.financial-ratios') }}" class="text-sm text-blue-600 hover:underline">التفاصيل ←</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500">نسبة التداول</p>
                <p class="text-lg font-bold {{ ($ratios['liquidity']['current_ratio'] ?? 0) >= 1 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $ratios['liquidity']['current_ratio'] ?? '—' }}
                </p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500">هامش صافي الربح</p>
                <p class="text-lg font-bold text-gray-800">{{ $ratios['profitability']['net_margin'] !== null ? $ratios['profitability']['net_margin'] . '%' : '—' }}</p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500">العائد على الأصول</p>
                <p class="text-lg font-bold text-gray-800">{{ $ratios['profitability']['return_on_assets'] !== null ? $ratios['profitability']['return_on_assets'] . '%' : '—' }}</p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500">الديون/حقوق الملكية</p>
                <p class="text-lg font-bold text-gray-800">{{ $ratios['leverage']['debt_to_equity'] ?? '—' }}</p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500">صافي الدخل</p>
                <p class="text-lg font-bold {{ ($ratios['summary']['net_income'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ number_format($ratios['summary']['net_income'] ?? 0, 0) }}
                </p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500">إجمالي الأصول</p>
                <p class="text-lg font-bold text-gray-800">{{ number_format($ratios['summary']['total_assets'] ?? 0, 0) }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Journal Entries -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-list-ul text-blue-600"></i>
                <span>آخر قيود اليومية المسجلة</span>
            </h3>
            <a href="{{ route('accounting.journal.index') }}" class="text-sm text-blue-600 hover:underline">عرض كل القيود ←</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">رقم القيد</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">التاريخ</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">البيان / الوصف</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">المصدر</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">المدين / الدائن</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">الحالة</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentEntries as $entry)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">
                                {{ $entry->entry_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $entry->entry_date->toDateString() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800 max-w-xs truncate">
                                {{ $entry->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2.5 py-1 text-xs rounded-lg font-medium bg-gray-100 text-gray-700 font-mono">
                                    {{ $entry->source_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900">
                                {{ number_format($entry->total_debit, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                <span class="px-2.5 py-1 text-xs rounded-full font-semibold 
                                    @if($entry->status->value === 'posted') bg-green-50 text-green-700 border border-green-200
                                    @elseif($entry->status->value === 'draft') bg-yellow-50 text-yellow-700 border border-yellow-200
                                    @else bg-gray-50 text-gray-700 border border-gray-200
                                    @endif">
                                    {{ $entry->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                <a href="{{ route('accounting.journal.show', $entry->id) }}" class="text-blue-600 hover:text-blue-900 font-medium">عرض التفاصيل</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">لا توجد قيود مسجلة بعد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function runIntegrityCheck() {
        const resDiv = document.getElementById('integrity-result');
        resDiv.className = "p-4 rounded-lg border bg-gray-50 text-gray-700 text-sm";
        resDiv.innerHTML = "<i class='fas fa-circle-notch animate-spin ml-2'></i> جاري فحص توازن الدفاتر...";
        resDiv.classList.remove('hidden');

        fetch("{{ route('accounting.integrity-check') }}")
            .then(response => response.json())
            .then(data => {
                if (data.is_balanced) {
                    resDiv.className = "p-4 rounded-lg border bg-green-50 text-green-800 border-green-200 text-sm flex items-start gap-2";
                    resDiv.innerHTML = `<i class='fas fa-check-circle text-green-600 mt-0.5'></i><div><p class='font-bold'>النظام متوازن بالكامل!</p><p class='text-xs mt-0.5'>تم تدقيق كافة القيود المعتمدة وتأكيد خلو النظام من أي انحرافات محاسبية. تاريخ الفحص: ${new Date(data.checked_at).toLocaleString()}</p></div>`;
                } else {
                    resDiv.className = "p-4 rounded-lg border bg-red-50 text-red-800 border-red-200 text-sm flex items-start gap-2";
                    let list = data.issues.map(iss => `<li>القيد # ${iss.entry_number} غير متوازن (مدين: ${iss.total_debit} / دائن: ${iss.total_credit})</li>`).join('');
                    resDiv.innerHTML = `<i class='fas fa-exclamation-triangle text-red-600 mt-0.5'></i><div><p class='font-bold'>تنبيه: تم العثور على قيود غير متوازنة (${data.issues_count})</p><ul class='list-disc list-inside mt-1 space-y-1 text-xs'>${list}</ul><form method="POST" action="{{ route('accounting.integrity.fix') }}" class="mt-3">@csrf<button type="submit" onclick="return confirm('هل أنت متأكد من رغبتك في إعادة احتساب وتحديث الأرصدة المتناقضة تلقائياً؟')" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-semibold">إصلاح وتحديث الأرصدة تلقائياً</button></form></div>`;
                }
            })
            .catch(err => {
                resDiv.className = "p-4 rounded-lg border bg-red-50 text-red-800 border-red-200 text-sm";
                resDiv.innerHTML = "❌ فشل الاتصال بخادم التدقيق.";
            });
    }

    // Run automatically on page load
    window.addEventListener('load', runIntegrityCheck);
</script>
@endsection
