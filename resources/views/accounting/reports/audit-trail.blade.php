@extends('layouts.app')

@section('title', 'سجل الرقابة والتدقيق المحاسبي')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100 font-sans">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">سجل الرقابة والتدقيق (Audit Trail)</h2>
            <p class="text-gray-600 mt-1">تتبع كافة العمليات والقيود الحاصلة في النظام المحاسبي بالتفصيل</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors no-print">
            <i class="fas fa-print ml-1"></i> طباعة السجل
        </button>
    </div>

    <!-- Filters (No Print) -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 no-print font-sans">
        <form method="GET" action="{{ route('accounting.reports.audit-trail') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع العملية</label>
                <select name="action" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">كل العمليات</option>
                    <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>إنشاء</option>
                    <option value="posted" {{ request('action') === 'posted' ? 'selected' : '' }}>اعتماد (ترحيل)</option>
                    <option value="reversed" {{ request('action') === 'reversed' ? 'selected' : '' }}>عكس قيد</option>
                    <option value="closed_period" {{ request('action') === 'closed_period' ? 'selected' : '' }}>إغلاق فترة مالية</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">المستخدم</label>
                <select name="user_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">كل المستخدمين</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                <input type="date" name="from" value="{{ $filters['from'] }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" name="to" value="{{ $filters['to'] }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-1.5 w-full">
                    <i class="fas fa-search"></i> تصفية النتائج
                </button>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden font-sans">
        <table class="w-full text-right border-collapse">
            <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600">
                <tr>
                    <th class="px-6 py-3 text-right">المعرف</th>
                    <th class="px-6 py-3 text-right">التاريخ والوقت</th>
                    <th class="px-6 py-3 text-right">المستخدم</th>
                    <th class="px-6 py-3 text-right">العملية</th>
                    <th class="px-6 py-3 text-right">نوع المستند</th>
                    <th class="px-6 py-3 text-right">معرف المستند</th>
                    <th class="px-6 py-3 text-center no-print">التفاصيل</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-mono text-gray-500">#{{ $log->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $log->created_at ?? $log->performed_at }}</td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-800">
                            {{ $log->user?->name ?? 'نظام مجدول' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->action === 'created')
                                <span class="px-2 py-1 bg-yellow-50 text-yellow-700 rounded-md text-xs font-semibold">إنشاء مسودة</span>
                            @elseif($log->action === 'posted')
                                <span class="px-2 py-1 bg-green-50 text-green-700 rounded-md text-xs font-semibold">اعتماد (ترحيل)</span>
                            @elseif($log->action === 'reversed')
                                <span class="px-2 py-1 bg-red-50 text-red-700 rounded-md text-xs font-semibold">عكس قيد</span>
                            @elseif($log->action === 'closed_period')
                                <span class="px-2 py-1 bg-purple-50 text-purple-700 rounded-md text-xs font-semibold">إغلاق فترة</span>
                            @else
                                <span class="px-2 py-1 bg-gray-50 text-gray-700 rounded-md text-xs font-semibold">{{ $log->action }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-mono text-gray-500">
                            {{ class_basename($log->auditable_type) }}
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900">
                            @if(class_basename($log->auditable_type) === 'JournalEntry')
                                <a href="{{ route('accounting.journal.show', $log->auditable_id) }}" class="text-blue-600 hover:underline">
                                    #{{ $log->auditable_id }}
                                </a>
                            @else
                                #{{ $log->auditable_id }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center no-print">
                            <button onclick="viewDetails({{ $log->id }}, '{{ json_encode($log->old_values) }}', '{{ json_encode($log->new_values) }}')" class="text-blue-600 hover:text-blue-800 text-xs font-semibold">
                                <i class="fas fa-eye ml-0.5"></i> تفاصيل التغيير
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500">لا توجد سجلات تدقيق متطابقة مع شروط البحث.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="p-4 bg-gray-50 border-t border-gray-200 no-print">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<!-- Details Modal -->
<div id="details_modal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 no-print font-sans">
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 max-w-2xl w-full mx-4 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">تفاصيل الفروقات (Diff Summary)</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto font-mono text-xs">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h4 class="font-bold text-gray-700 mb-2 font-sans text-sm">القيم السابقة:</h4>
                    <pre id="old_values" class="bg-red-50/50 p-4 rounded-lg text-red-800 overflow-x-auto whitespace-pre-wrap"></pre>
                </div>
                <div>
                    <h4 class="font-bold text-gray-700 mb-2 font-sans text-sm">القيم الجديدة:</h4>
                    <pre id="new_values" class="bg-green-50/50 p-4 rounded-lg text-green-800 overflow-x-auto whitespace-pre-wrap"></pre>
                </div>
            </div>
        </div>
        <div class="p-4 bg-gray-50 border-t border-gray-100 text-left">
            <button onclick="closeModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors">إغلاق نافذة التفاصيل</button>
        </div>
    </div>
</div>

<script>
function viewDetails(id, oldVal, newVal) {
    let oldObj = JSON.parse(oldVal);
    let newObj = JSON.parse(newVal);

    document.getElementById('old_values').textContent = oldObj ? JSON.stringify(oldObj, null, 4) : 'لا توجد بيانات سابقة';
    document.getElementById('new_values').textContent = newObj ? JSON.stringify(newObj, null, 4) : 'لا توجد تغييرات مسجلة';
    document.getElementById('details_modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('details_modal').classList.add('hidden');
}
</script>
@endsection
