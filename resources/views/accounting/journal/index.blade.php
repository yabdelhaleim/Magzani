@extends('layouts.app')

@section('title', 'قيود اليومية')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">دفتر قيود اليومية العام (Journal Entries)</h2>
            <p class="text-gray-600 mt-1">عرض ومراجعة كافة القيود المسجلة آلياً أو يدوياً في النظام</p>
        </div>
        <a href="{{ route('accounting.journal.create') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all shadow hover:shadow-md flex items-center gap-1.5">
            <i class="fas fa-plus-circle"></i> تسجيل قيد يدوي جديد
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">إجمالي القيود</p>
                <h3 class="text-2xl font-bold text-gray-800 font-mono">{{ $stats['total'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center text-blue-600">
                <i class="fas fa-book"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">قيود معتمدة (رحلت)</p>
                <h3 class="text-2xl font-bold text-green-600 font-mono">{{ $stats['posted'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center text-green-600">
                <i class="fas fa-check-double"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">مسودات معلقة</p>
                <h3 class="text-2xl font-bold text-yellow-600 font-mono">{{ $stats['draft'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-yellow-50 rounded-full flex items-center justify-center text-yellow-600">
                <i class="fas fa-eraser"></i>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('accounting.journal.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" name="search" placeholder="رقم القيد أو البيان..." value="{{ request('search') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">حالة القيد</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">الكل</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                    <option value="posted" {{ request('status') === 'posted' ? 'selected' : '' }}>معتمد</option>
                    <option value="reversed" {{ request('status') === 'reversed' ? 'selected' : '' }}>معكوس</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-1.5">
                    <i class="fas fa-search"></i> بحث وتصفية
                </button>
            </div>
        </form>
    </div>

    <!-- Journal Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">رقم القيد</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">التاريخ</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">البيان</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">المصدر</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">القيمة الإجمالية</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">الحالة</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-mono">
                    @forelse($entries as $entry)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">
                                {{ $entry->entry_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $entry->entry_date->toDateString() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800 font-sans max-w-sm truncate">
                                {{ $entry->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-sans">
                                <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-700 rounded border border-gray-200">
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
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-sans flex items-center justify-center gap-2">
                                <a href="{{ route('accounting.journal.show', $entry->id) }}" class="text-blue-600 hover:text-blue-900 font-medium">عرض التفاصيل</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500 font-sans">لا توجد قيود مسجلة توافق شروط البحث.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($entries->hasPages())
            <div class="p-6 bg-gray-50 border-t border-gray-100">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
