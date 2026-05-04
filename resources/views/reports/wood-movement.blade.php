@extends('layouts.app')

@section('title', 'تقرير حركة الخشب')
@section('page-title', 'تقرير حركة الخشب')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">تقرير حركة الخشب</h2>
        <a href="{{ route('reports.financial') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-right ml-1"></i> رجوع
        </a>
    </div>

    @if(isset($movements) && $movements->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">#</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">التاريخ</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">نوع الخشب</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">الحجم م³</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">الغرض</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">بواسطة</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">ملاحظات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($movements as $i => $movement)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 text-sm">{{ $movement->dispensing_date ?? $movement->created_at ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm font-medium">{{ $movement->wood_type ?? $movement->wood_stock_type ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ number_format($movement->volume_cm3_taken ?? 0 / 1000000, 3) }}</td>
                    <td class="px-4 py-3 text-sm">{{ $movement->purpose ?? $movement->notes ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $movement->user_name ?? $movement->created_by_name ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $movement->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-12 text-gray-500">
        <i class="fas fa-exchange-alt text-4xl mb-3"></i>
        <p>لا توجد حركات خشب</p>
    </div>
    @endif
</div>
@endsection
