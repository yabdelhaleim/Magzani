@extends('layouts.app')

@section('title', 'تحويل جديد بين المخازن')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">تحويل جديد بين المخازن</h2>
            <p class="text-gray-600 text-sm mt-1">نقل المنتجات من مخزن إلى آخر</p>
        </div>
        <a href="{{ route('transfers.index') }}" 
           class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            رجوع
        </a>
    </div>

    <form action="{{ route('transfers.store') }}" method="POST" x-data="transferForm()">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Transfer Info -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        معلومات التحويل
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- From Warehouse -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                من مخزن <span class="text-red-500">*</span>
                            </label>
                            <select name="from_warehouse_id" 
                                    x-model="fromWarehouse"
                                    @change="clearProducts()"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">اختر المخزن</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                            @error('from_warehouse_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- To Warehouse -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                إلى مخزن <span class="text-red-500">*</span>
                            </label>
                            <select name="to_warehouse_id" 
                                    x-model="toWarehouse"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">اختر المخزن</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                            @error('to_warehouse_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Transfer Date -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                تاريخ التحويل <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="transfer_date" 
                                   value="{{ old('transfer_date', date('Y-m-d')) }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('transfer_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">ملاحظات</label>
                        <textarea name="notes" 
                                  rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="ملاحظات إضافية (اختياري)">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Products Section -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            المنتجات
                        </h3>
                        <button type="button" 
                                @click="addProduct()"
                                :disabled="!fromWarehouse"
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg font-semibold transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            إضافة منتج
                        </button>
                    </div>

                    <template x-if="!fromWarehouse">
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500">⚠️ اختر المخزن المصدر أولاً لإضافة المنتجات</p>
                        </div>
                    </template>

                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    <!-- Product Select -->
                                    <div class="md:col-span-5">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">المنتج</label>
                                        <select :name="'items['+index+'][product_id]'" 
                                                x-model="item.product_id"
                                                @change="updateProductInfo(index)"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                            <option value="">اختر المنتج</option>
                                            @foreach($products as $product)
                                            <option value="{{ $product->id }}">
                                                {{ $product->name }} ({{ $product->sku }})
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Available Quantity -->
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">المتاح</label>
                                        <div class="px-3 py-2 rounded-lg text-sm font-bold text-center"
                                             :class="{
                                                 'bg-red-50 border border-red-200 text-red-700': item.available <= 0,
                                                 'bg-orange-50 border border-orange-200 text-orange-700': item.available > 0 && item.available < 10,
                                                 'bg-green-50 border border-green-200 text-green-700': item.available >= 10
                                             }"
                                             x-text="item.available !== null ? item.available.toFixed(2) : '--'">
                                        </div>
                                    </div>

                                    <!-- Quantity -->
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">الكمية</label>
                                        <input type="number" 
                                               :name="'items['+index+'][quantity]'" 
                                               x-model="item.quantity"
                                               :max="item.available"
                                               min="0.01"
                                               step="0.01"
                                               required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                    </div>

                                    <!-- Notes -->
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">ملاحظات</label>
                                        <input type="text" 
                                               :name="'items['+index+'][notes]'" 
                                               x-model="item.notes"
                                               placeholder="اختياري"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                    </div>

                                    <!-- Delete Button -->
                                    <div class="md:col-span-1 flex items-end">
                                        <button type="button" 
                                                @click="removeProduct(index)"
                                                class="w-full px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-colors">
                                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="fromWarehouse && items.length === 0">
                            <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                                <p class="text-gray-500">لم يتم إضافة أي منتجات بعد</p>
                                <button type="button" 
                                        @click="addProduct()"
                                        class="mt-3 text-blue-600 hover:text-blue-800 font-semibold">
                                    + إضافة منتج
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Summary Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">ملخص التحويل</h3>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between items-center pb-2 border-b">
                            <span class="text-gray-600 text-sm">عدد المنتجات:</span>
                            <span class="font-bold text-gray-800" x-text="items.length"></span>
                        </div>
                        
                        <div class="flex justify-between items-center pb-2 border-b">
                            <span class="text-gray-600 text-sm">إجمالي الكميات:</span>
                            <span class="font-bold text-gray-800" x-text="totalQuantity()"></span>
                        </div>
                    </div>

                    <!-- Validation Messages -->
                    <div class="mb-4">
                        <template x-if="!fromWarehouse">
                            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-3 py-2 rounded-lg text-xs">
                                ⚠️ اختر المخزن المصدر
                            </div>
                        </template>
                        
                        <template x-if="!toWarehouse">
                            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-3 py-2 rounded-lg text-xs mt-2">
                                ⚠️ اختر المخزن الوجهة
                            </div>
                        </template>
                        
                        <template x-if="fromWarehouse && toWarehouse && fromWarehouse === toWarehouse">
                            <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg text-xs mt-2">
                                ❌ لا يمكن التحويل لنفس المخزن
                            </div>
                        </template>
                        
                        <template x-if="items.length === 0">
                            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-3 py-2 rounded-lg text-xs mt-2">
                                ⚠️ أضف منتج واحد على الأقل
                            </div>
                        </template>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            :disabled="!canSubmit()"
                            class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-6 py-3 rounded-lg font-bold transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        تنفيذ التحويل
                    </button>

                    <p class="text-xs text-gray-500 mt-3 text-center">
                        💡 سيتم تنفيذ التحويل مباشرة بعد الحفظ
                    </p>
                </div>
            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
function transferForm() {
    // ✅ تحويل بيانات PHP إلى JavaScript
    const warehousesStock = @json($warehousesStock);
    
    return {
        fromWarehouse: '',
        toWarehouse: '',
        items: [],

        addProduct() {
            this.items.push({
                product_id: '',
                quantity: 1,
                available: null,
                notes: ''
            });
        },

        removeProduct(index) {
            this.items.splice(index, 1);
        },

        clearProducts() {
            this.items = [];
        },

        updateProductInfo(index) {
            const productId = this.items[index].product_id;
            
            if (!this.fromWarehouse || !productId) {
                this.items[index].available = null;
                return;
            }

            // ✅ جلب الكمية من البيانات المحملة مسبقاً
            const warehouseStock = warehousesStock[this.fromWarehouse];
            
            if (warehouseStock && warehouseStock[productId]) {
                this.items[index].available = warehouseStock[productId].available;
            } else {
                this.items[index].available = 0;
            }
        },

        totalQuantity() {
            return this.items.reduce((sum, item) => 
                sum + (parseFloat(item.quantity) || 0), 0
            ).toFixed(2);
        },

        canSubmit() {
            return this.fromWarehouse && 
                   this.toWarehouse && 
                   this.fromWarehouse !== this.toWarehouse && 
                   this.items.length > 0 &&
                   this.items.every(item => item.product_id && item.quantity > 0);
        }
    }
}
</script>
@endpush
@endsectionِِ