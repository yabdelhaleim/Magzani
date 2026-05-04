@extends('layouts.app')

@section('title', 'بحث في المخزن')
@section('page-title', 'بحث في المخزن - ' . ($warehouse->name ?? ''))

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">بحث في المخزن: {{ $warehouse->name }}</h2>
        <a href="{{ route('warehouses.show', $warehouse->id) }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-right ml-1"></i> رجوع
        </a>
    </div>

    <form method="GET" action="{{ route('warehouses.search', $warehouse->id) }}" class="mb-6">
        <div class="flex gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث بالاسم، SKU أو الباركود..." 
                   class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-search ml-1"></i> بحث
            </button>
        </div>
    </form>

    @if($products->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">#</th>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">المنتج</th>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">SKU</th>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">الكمية</th>
                    <th class="px-6 py-3 text-right text-sm font-medium text-gray-500">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($products as $index => $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm">{{ $products->firstItem() + $index }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $product->sku }}</td>
                    <td class="px-6 py-4 text-sm">{{ $product->pivot->quantity ?? 0 }}</td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('products.show', $product->id) }}" class="text-blue-600 hover:text-blue-800">عرض</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
    @else
    <div class="text-center py-12 text-gray-500">
        <i class="fas fa-search text-4xl mb-3"></i>
        <p>لا توجد نتائج</p>
    </div>
    @endif
</div>
@endsection
