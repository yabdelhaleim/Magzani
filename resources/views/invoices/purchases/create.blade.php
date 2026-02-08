@extends('layouts.app')

@section('title', 'إضافة فاتورة شراء')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- رسائل الخطأ --}}
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-500 to-pink-500 px-6 py-4">
            <h2 class="text-2xl font-bold text-white">إضافة فاتورة شراء جديدة</h2>
        </div>

        <form method="POST" action="{{ route('invoices.purchases.store') }}" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                {{-- Supplier --}}
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        المورد <span class="text-red-500">*</span>
                    </label>
                    <select name="supplier_id" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('supplier_id') border-red-500 @enderror"
                            required>
                        <option value="">اختر المورد</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Warehouse --}}
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        المخزن <span class="text-red-500">*</span>
                    </label>
                    <select name="warehouse_id" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('warehouse_id') border-red-500 @enderror"
                            required>
                        <option value="">اختر المخزن</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Invoice Date --}}
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        تاريخ الفاتورة <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="invoice_date" 
                           value="{{ old('invoice_date', date('Y-m-d')) }}"
                           max="{{ date('Y-m-d') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('invoice_date') border-red-500 @enderror"
                           required>
                    @error('invoice_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Items Section --}}
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">الأصناف</h3>
                    <button type="button" 
                            onclick="addItem()" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-plus ml-2"></i>
                        إضافة صنف
                    </button>
                </div>

                <div id="items-container">
                    {{-- Item Row Template (سيتم نسخها بالـ JavaScript) --}}
                    <div class="item-row bg-gray-50 p-4 rounded-lg mb-3 border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            {{-- Product --}}
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-medium mb-1">
                                    الصنف <span class="text-red-500">*</span>
                                </label>
                                <select name="items[0][product_id]" 
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        required>
                                    <option value="">اختر الصنف</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Quantity --}}
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-1">
                                    الكمية <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="items[0][qty]" 
                                       step="0.01"
                                       min="0.01"
                                       placeholder="0"
                                       class="item-qty w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       onchange="calculateItemTotal(this)"
                                       required>
                            </div>

                            {{-- Price --}}
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-1">
                                    سعر الوحدة <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="items[0][price]" 
                                       step="0.01"
                                       min="0"
                                       placeholder="0"
                                       class="item-price w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       onchange="calculateItemTotal(this)"
                                       required>
                            </div>

                            {{-- Total & Remove --}}
                            <div class="flex items-end gap-2">
                                <div class="flex-1">
                                    <label class="block text-gray-700 text-sm font-medium mb-1">الإجمالي</label>
                                    <input type="text" 
                                           class="item-total w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100"
                                           readonly 
                                           value="0">
                                </div>
                                <button type="button" 
                                        onclick="removeItem(this)" 
                                        class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                                        title="حذف">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Discount & Tax --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">الخصم (اختياري)</label>
                    <input type="number" 
                           name="discount" 
                           step="0.01"
                           min="0"
                           value="{{ old('discount', 0) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="0">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">الضريبة (اختياري)</label>
                    <input type="number" 
                           name="tax" 
                           step="0.01"
                           min="0"
                           value="{{ old('tax', 0) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="0">
                </div>
            </div>

            {{-- Notes --}}
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">ملاحظات (اختياري)</label>
                <textarea name="notes" 
                          rows="3"
                          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                          placeholder="أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3">
                <button type="submit" 
                        class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-save ml-2"></i>
                    حفظ الفاتورة
                </button>
                <a href="{{ route('invoices.purchases.index') }}" 
                   class="px-6 py-3 bg-gray-500 text-white font-medium rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-arrow-right ml-2"></i>
                    رجوع
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let itemIndex = 1;

// إضافة صنف جديد
function addItem() {
    const container = document.getElementById('items-container');
    const firstItem = container.querySelector('.item-row');
    const newItem = firstItem.cloneNode(true);
    
    // تحديث الـ name attributes
    newItem.querySelectorAll('select, input').forEach(field => {
        if (field.name) {
            field.name = field.name.replace(/items\[\d+\]/, `items[${itemIndex}]`);
        }
        if (field.type !== 'button') {
            field.value = '';
        }
    });
    
    // إعادة تعيين الـ total
    newItem.querySelector('.item-total').value = '0';
    
    container.appendChild(newItem);
    itemIndex++;
}

// حذف صنف
function removeItem(button) {
    const container = document.getElementById('items-container');
    const items = container.querySelectorAll('.item-row');
    
    if (items.length > 1) {
        button.closest('.item-row').remove();
    } else {
        alert('يجب وجود صنف واحد على الأقل');
    }
}

// حساب إجمالي الصنف
function calculateItemTotal(input) {
    const row = input.closest('.item-row');
    const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const total = qty * price;
    
    row.querySelector('.item-total').value = total.toFixed(2);
}
</script>
@endpush
@endsection