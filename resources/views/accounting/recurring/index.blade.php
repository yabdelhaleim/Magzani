@extends('layouts.app')

@section('title', 'القيود المتكررة')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">القيود المتكررة</h2>
            <p class="text-gray-600 mt-1">قوالب قيود تُولَّد تلقائياً (إيجار، رواتب، استهلاك...)</p>
        </div>
        <a href="{{ route('accounting.recurring.create') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
            <i class="fas fa-plus-circle ml-1"></i> قالب جديد
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">القالب</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">التكرار</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">التشغيل القادم</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">آخر تشغيل</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">الحالة</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($templates as $template)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-800">{{ $template->template_name }}</div>
                            <div class="text-xs text-gray-500">{{ Str::limit($template->description, 50) }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $template->frequencyLabel() }}</td>
                        <td class="px-4 py-3 text-sm">{{ $template->next_run_date?->toDateString() }}</td>
                        <td class="px-4 py-3 text-sm">{{ $template->last_run_date?->toDateString() ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $template->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $template->is_active ? 'نشط' : 'موقوف' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                <form method="POST" action="{{ route('accounting.recurring.run', $template) }}">
                                    @csrf
                                    <button class="text-blue-600 hover:text-blue-800 text-sm" title="تشغيل الآن"><i class="fas fa-play"></i></button>
                                </form>
                                <a href="{{ route('accounting.recurring.edit', $template) }}" class="text-gray-600 hover:text-gray-800 text-sm"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('accounting.recurring.destroy', $template) }}" onsubmit="return confirm('حذف القالب؟')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800 text-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">لا توجد قوالب متكررة بعد.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($templates->hasPages())
            <div class="p-4 border-t">{{ $templates->links() }}</div>
        @endif
    </div>
</div>
@endsection
