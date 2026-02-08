@extends('layouts.app')

@section('title', 'التحويلات المعلقة')
@section('page-title', 'التحويلات المعلقة')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">التحويلات المعلقة</h2>
            <p class="text-gray-600 text-sm mt-1">التحويلات التي تحتاج إلى مراجعة واعتماد</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('transfers.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                تحويل جديد
            </a>
            <a href="{{ route('transfers.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                كل التحويلات
            </a>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($transfers->count() > 0)
        <div class="p-4 bg-yellow-50 border-b border-yellow-200">
            <div class="flex items-center gap-2 text-yellow-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span class="font-semibold">لديك {{ $transfers->count() }} تحويل معلق</span>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">#</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">رقم التحويل</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">من</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">إلى</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">التاريخ</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">المنتجات</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">إجراء</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($transfers as $transfer)
                    <tr class="hover:bg-yellow-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-gray-800">#{{ $transfer->id }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-blue-600">{{ $transfer->transfer_number }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-gray-800">{{ $transfer->fromWarehouse->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-gray-800">{{ $transfer->toWarehouse->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ $transfer->transfer_date }}</span>
                            <p class="text-xs text-gray-400">{{ $transfer->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">
                                {{ $transfer->items->count() }} منتج
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('transfers.show', $transfer->id) }}"
                               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                مراجعة
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500 font-semibold text-lg">لا توجد تحويلات معلقة</p>
                                <p class="text-sm text-gray-400">جميع التحويلات تم معالجتها</p>
                                <a href="{{ route('transfers.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold text-sm mt-2">
                                    عرض جميع التحويلات →
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
