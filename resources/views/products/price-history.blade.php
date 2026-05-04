@extends('layouts.app')

@section('title', 'سعر تاريخ المنتج')
@section('page-title', 'سجل تغيرات الأسعار - ' . $product->name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">سجل تغيرات الأسعار: {{ $product->name }}</h2>
        <a href="{{ route('products.show', $product->id) }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-right ml-1"></i> رجوع
        </a>
    </div>

    @if($product->priceHistory && $product->priceHistory->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">التاريخ</th>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">نوع السعر</th>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">السعر القديم</th>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">السعر الجديد</th>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">الفرق</th>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">بواسطة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($product->priceHistory as $history)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm">{{ $history->changed_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4 text-sm">{{ $history->price_type }}</td>
                    <td class="px-6 py-4 text-sm">{{ number_format($history->old_price, 2) }}</td>
                    <td class="px-6 py-4 text-sm font-medium">{{ number_format($history->new_price, 2) }}</td>
                    <td class="px-6 py-4 text-sm">
                        @php $diff = $history->new_price - $history->old_price; @endphp
                        <span class="{{ $diff > 0 ? 'text-green-600' : ($diff < 0 ? 'text-red-600' : 'text-gray-500') }}">
                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $history->changer->name ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-12 text-gray-500">
        <i class="fas fa-history text-4xl mb-3"></i>
        <p>لا يوجد سجل تغيرات أسعار لهذا المنتج</p>
    </div>
    @endif
</div>
@endsection
