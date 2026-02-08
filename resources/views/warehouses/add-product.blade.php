@extends('layouts.app')

@section('title', 'إضافة منتج للمخزن')
@section('page-title', 'إضافة منتج للمخزن')

@section('content')

<!-- Header -->
<div class="mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('warehouses.show', $warehouse->id) }}" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">إضافة منتج إلى: {{ $warehouse->name }}</h2>
            <p class="text-gray-600 text-sm mt-1">أضف منتج جديد لهذا المخزن</p>
        </div>
    </div>
</div>

<div class="max-w-4xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form action="{{ route('warehouses.add-product', $warehouse->id) }}" method="POST">
            @csrf

            <!-- اختيار المنتج -->
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">
                    المنتج <span class="text-red-500">*</span>
                </label>
                <select name="product_id" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('product_id') border-red-500 @enderror" 
                        required>
                    <option value="">-- اختر المنتج --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->code ?? $product->sku }})
                        </option>
                    @endforeach
                </select>
                @error('product_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- الكمية -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">
                        الكمية <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="quantity" 
                           value="{{ old('quantity', 0) }}" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('quantity') border-red-500 @enderror" 
                           required 
                           min="0"
                           step="0.01"
                           placeholder="0.00">
                    @error('quantity')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- الحد الأدنى -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">
                        الحد الأدنى للتنبيه
                    </label>
                    <input type="number" 
                           name="min_stock" 
                           value="{{ old('min_stock', 10) }}" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('min_stock') border-red-500 @enderror" 
                           min="0"
                           step="0.01"
                           placeholder="10">
                    @error('min_stock')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- متوسط التكلفة -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">
                        متوسط التكلفة (ج.م)
                    </label>
                    <input type="number" 
                           step="0.01" 
                           name="average_cost" 
                           value="{{ old('average_cost', 0) }}" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('average_cost') border-red-500 @enderror" 
                           min="0"
                           placeholder="0.00">
                    @error('average_cost')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- معلومات توضيحية -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded mb-6">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold mb-1">💡 ملاحظات:</p>
                        <ul class="list-disc mr-5 space-y-1">
                            <li>الكمية: عدد الوحدات المتوفرة من المنتج في هذا المخزن</li>
                            <li>الحد الأدنى: عند وصول الكمية لهذا الرقم سيتم التنبيه</li>
                            <li>متوسط التكلفة: تكلفة شراء الوحدة الواحدة من المنتج</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- الأزرار -->
            <div class="flex gap-3">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2 shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    إضافة المنتج
                </button>
                
                <a href="{{ route('warehouses.show', $warehouse->id) }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-8 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>

@endsection