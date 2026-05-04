@extends('layouts.app')

@section('title', 'تقرير مخزون الخشب')
@section('page-title', 'تقرير مخزون الخشب')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">تقرير مخزون الخشب</h2>
        <a href="{{ route('reports.financial') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-right ml-1"></i> رجوع
        </a>
    </div>

    @if(isset($summary))
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-gray-500 text-sm">إجمالي الدفعات</p>
            <p class="text-2xl font-bold text-indigo-600">{{ $summary['total_batches'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-gray-500 text-sm">إجمالي المتر المكعب</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($summary['total_m3'], 3) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-gray-500 text-sm">المتبقي م³</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($summary['remaining_m3'], 3) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-gray-500 text-sm">قيمة المتبقي</p>
            <p class="text-2xl font-bold text-purple-600">{{ number_format($summary['remaining_value'], 2) }}</p>
        </div>
    </div>
    @endif

    @if(isset($stocks) && $stocks->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">#</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">نوع الخشب</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">الأبعاد</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">الكمية</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">م³</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">تكلفة الوحدة</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">القيمة الإجمالية</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($stocks as $i => $stock)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 text-sm font-medium">{{ $stock->wood_type ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $stock->length ?? 0 }} x {{ $stock->width ?? 0 }} x {{ $stock->thickness ?? 0 }}</td>
                    <td class="px-4 py-3 text-sm">{{ $stock->quantity ?? 0 }}</td>
                    <td class="px-4 py-3 text-sm">{{ number_format($stock->total_m3 ?? 0, 3) }}</td>
                    <td class="px-4 py-3 text-sm">{{ number_format($stock->unit_cost ?? 0, 2) }}</td>
                    <td class="px-4 py-3 text-sm font-medium">{{ number_format($stock->remaining_value ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-12 text-gray-500">
        <i class="fas fa-tree text-4xl mb-3"></i>
        <p>لا توجد بيانات خشب</p>
    </div>
    @endif
</div>
@endsection
