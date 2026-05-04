@extends('layouts.app')

@section('title', 'سجل تحويلات المخزن')
@section('page-title', 'سجل تحويلات المخزن - ' . ($warehouse->name ?? ''))

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">تحويلات المخزن: {{ $warehouse->name }}</h2>
        <a href="{{ route('transfers.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-right ml-1"></i> رجوع
        </a>
    </div>

    @if($transfers->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">رقم التحويل</th>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">من</th>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">إلى</th>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">التاريخ</th>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">الحالة</th>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($transfers as $transfer)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium">{{ $transfer->transfer_number }}</td>
                    <td class="px-6 py-4 text-sm">{{ $transfer->fromWarehouse->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm">{{ $transfer->toWarehouse->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm">{{ $transfer->transfer_date ?? $transfer->created_at->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            @if($transfer->status === 'completed') bg-green-100 text-green-800
                            @elseif($transfer->status === 'pending') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $transfer->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('transfers.show', $transfer->id) }}" class="text-blue-600 hover:text-blue-800">عرض</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $transfers->links() }}</div>
    @else
    <div class="text-center py-12 text-gray-500">
        <i class="fas fa-exchange-alt text-4xl mb-3"></i>
        <p>لا توجد تحويلات لهذا المخزن</p>
    </div>
    @endif
</div>
@endsection
