@extends('layouts.app')

@section('title', 'تقرير تكلفة الخشب في الإنتاج')
@section('page-title', 'تقرير تكلفة الخشب في الإنتاج')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">تكلفة الخشب في الإنتاج</h2>
        <a href="{{ route('reports.financial') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-right ml-1"></i> رجوع
        </a>
    </div>

    @if(isset($report) && count($report) > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">رقم الأمر</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">المنتج</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">الكمية المنتجة</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">دفعات الخشب</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">م³ الخشب</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">تكلفة الخشب</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">التكلفة الكلية</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">نسبة الخشب</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($report as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium">{{ $item['order_number'] ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $item['product_name'] ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $item['quantity_produced'] ?? 0 }}</td>
                    <td class="px-4 py-3 text-sm">{{ $item['wood_batches_used'] ?? 0 }}</td>
                    <td class="px-4 py-3 text-sm">{{ number_format($item['total_wood_m3'] ?? 0, 4) }}</td>
                    <td class="px-4 py-3 text-sm">{{ number_format($item['wood_cost'] ?? 0, 2) }}</td>
                    <td class="px-4 py-3 text-sm font-medium">{{ number_format($item['total_cost'] ?? 0, 2) }}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="px-2 py-1 rounded-full text-xs @if(($item['wood_cost_percentage'] ?? 0) > 50) bg-red-100 text-red-800 @elseif(($item['wood_cost_percentage'] ?? 0) > 30) bg-yellow-100 text-yellow-800 @else bg-green-100 text-green-800 @endif">
                            {{ number_format($item['wood_cost_percentage'] ?? 0, 1) }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-12 text-gray-500">
        <i class="fas fa-industry text-4xl mb-3"></i>
        <p>لا توجد بيانات إنتاج</p>
    </div>
    @endif
</div>
@endsection
