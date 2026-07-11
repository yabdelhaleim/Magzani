@extends('layouts.app')

@section('title', 'مراجعة الترحيلات الفاشلة')

@section('content')
@php
    $defaultCurrency = \App\Models\AccountingSetting::value('default_currency') ?? 'SAR';
@endphp
<div class="space-y-6">
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">مراجعة الترحيلات الفاشلة</h2>
            <p class="text-gray-600 mt-1">عمليات لم يتم ترحيلها لدفتر الأستاذ العام بسبب أخطاء</p>
        </div>
        <div class="flex gap-3">
            <span class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-sm font-medium">معلّق: {{ $pendingCount }}</span>
            <span class="px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-sm font-medium">محلول: {{ $resolvedCount }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="p-4 bg-blue-50 text-blue-700 rounded-lg border border-blue-200">{{ session('info') }}</div>
    @endif

    {{-- Filters --}}
    <div class="flex gap-2">
        <a href="{{ route('accounting.posting-failures.index') }}" class="px-4 py-2 rounded-lg text-sm {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">معلّق</a>
        <a href="{{ route('accounting.posting-failures.index', ['status' => 'resolved']) }}" class="px-4 py-2 rounded-lg text-sm {{ request('status') === 'resolved' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">محلول</a>
        <a href="{{ route('accounting.posting-failures.index', ['status' => 'all']) }}" class="px-4 py-2 rounded-lg text-sm {{ request('status') === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">الكل</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($failures->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-check-circle text-4xl text-green-400 mb-3"></i>
                <p class="text-lg font-medium">لا توجد ترحيلات فاشلة</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">#</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">المفتاح</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">نوع العملية</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">المبلغ المتأثر</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">الوصف</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">الخطأ</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700">المحاولات</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700">التاريخ</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700">الحالة</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($failures as $failure)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $failure->id }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-700 max-w-[200px] truncate" title="{{ $failure->source_event_key ?? $failure->event_key }}">
                            {{ $failure->source_event_key ?? $failure->event_key }}
                        </td>
                        <td class="px-4 py-3 text-gray-800">
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-semibold border border-indigo-100">
                                {{ $failure->transaction_type_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-bold font-mono text-gray-900">
                            {{ $failure->affected_amount !== null ? number_format($failure->affected_amount, 2) . ' ' . $defaultCurrency : '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-800">{{ $failure->description ?? '—' }}</td>
                        <td class="px-4 py-3 text-red-600 text-xs max-w-[250px] truncate" title="{{ $failure->error_message }}">
                            {{ Str::limit($failure->error_message, 80) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $failure->attempts ?? 1 }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500">
                            {{ $failure->failed_at ? $failure->failed_at->format('m/d H:i') : ($failure->created_at ? $failure->created_at->format('m/d H:i') : '—') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($failure->resolved)
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">محلول</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">معلّق</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @unless($failure->resolved)
                                <div class="flex gap-1 justify-center">
                                    <form method="POST" action="{{ route('accounting.posting-failures.retry', $failure) }}">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs hover:bg-blue-200" title="إعادة المحاولة">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('accounting.posting-failures.resolve', $failure) }}">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs hover:bg-green-200" title="تحديد كمحلول">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                </div>
                            @endunless
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-4 py-3 border-t border-gray-100">
                {{ $failures->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
