@extends('layouts.app')

@section('title', 'إنشاء جرد جديد')

@section('page-title', 'إنشاء جرد جديد')

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-4xl font-black text-gray-900 mb-2">إنشاء جرد جديد</h1>
                <p class="text-gray-500">جرد المخزون ومطابقة الكميات الفعلية</p>
            </div>
            <a href="{{ route('stock-counts.index') }}" 
               class="group flex items-center gap-2 px-5 py-2.5 bg-white border-2 border-gray-200 rounded-xl hover:border-gray-300 transition-all">
                <svg class="w-5 h-5 text-gray-600 group-hover:text-gray-800 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="text-gray-700 font-medium group-hover:text-gray-900">رجوع</span>
            </a>
        </div>
    </div>

    <!-- Alert: Active Stock Count -->
    @if($errors->has('warehouse_id') && str_contains($errors->first('warehouse_id'), 'جرد نشط'))
        @php
            $activeCount = \App\Models\StockCount::where('warehouse_id', old('warehouse_id'))
                ->whereIn('status', ['draft', 'in_progress'])
                ->first();
        @endphp
        
        @if($activeCount)
        <div class="mb-6 bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-200 rounded-2xl p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-amber-900 mb-1">يوجد جرد نشط بالفعل</h3>
                    <p class="text-amber-700 mb-4">يوجد جرد مفتوح لهذا المخزن. يجب إكماله أو إلغاؤه قبل إنشاء جرد جديد.</p>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('stock-counts.show', $activeCount->id) }}" 
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-semibold transition-all shadow-sm hover:shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            عرض الجرد المفتوح
                        </a>
                        @if($activeCount->status == 'in_progress')
                        <a href="{{ route('stock-counts.count', $activeCount->id) }}" 
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border-2 border-amber-200 hover:border-amber-300 text-amber-900 rounded-xl font-semibold transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            إكمال الجرد الآن
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif

    <!-- Other Error Messages -->
    @if ($errors->any() && !($errors->has('warehouse_id') && str_contains($errors->first('warehouse_id'), 'جرد نشط')))
    <div class="mb-6 bg-red-50 border-2 border-red-200 rounded-2xl p-5">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <h4 class="text-red-800 font-bold mb-2">يرجى تصحيح الأخطاء التالية:</h4>
                <ul class="space-y-1 text-red-700">
                    @foreach ($errors->all() as $error)
                        @if(!str_contains($error, 'جرد نشط'))
                        <li class="flex items-start gap-2">
                            <span class="text-red-500 mt-1">•</span>
                            <span>{{ $error }}</span>
                        </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Success Message -->
    @if(session('success'))
    <div class="mb-6 bg-green-50 border-2 border-green-200 rounded-2xl p-5">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-green-800 font-semibold">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <!-- Main Form -->
    <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-100 overflow-hidden">
        <form action="{{ route('stock-counts.store') }}" method="POST" id="stockCountForm">
            @csrf

            <div class="p-8 space-y-8">
                <!-- Warehouse & Date -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-3">
                            المخزن <span class="text-red-500">*</span>
                        </label>
                        <select name="warehouse_id" 
                                id="warehouse_id"
                                class="w-full px-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium @error('warehouse_id') border-red-300 bg-red-50 @enderror" 
                                required>
                            <option value="">اختر المخزن</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }} @if($warehouse->code)({{ $warehouse->code }})@endif
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-3">
                            تاريخ الجرد <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="count_date" 
                               id="count_date"
                               class="w-full px-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium @error('count_date') border-red-300 bg-red-50 @enderror" 
                               value="{{ old('count_date', date('Y-m-d')) }}" 
                               max="{{ date('Y-m-d') }}"
                               required>
                    </div>
                </div>

                <!-- Count Type -->
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-3">
                        نوع الجرد <span class="text-red-500">*</span>
                    </label>
                    <div class="grid md:grid-cols-3 gap-4" id="count_type_options">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="count_type" value="full" 
                                   class="peer sr-only" 
                                   {{ old('count_type', 'full') == 'full' ? 'checked' : '' }}>
                            <div class="p-5 bg-gray-50 border-2 border-gray-200 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-md hover:border-gray-300">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-2xl peer-checked:bg-blue-100">
                                        📦
                                    </div>
                                    <h3 class="font-bold text-gray-900">جرد شامل</h3>
                                </div>
                                <p class="text-sm text-gray-600">جرد جميع المنتجات الموجودة في المخزن</p>
                            </div>
                        </label>

                        <label class="relative cursor-pointer">
                            <input type="radio" name="count_type" value="partial" 
                                   class="peer sr-only" 
                                   {{ old('count_type') == 'partial' ? 'checked' : '' }}>
                            <div class="p-5 bg-gray-50 border-2 border-gray-200 rounded-xl transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:shadow-md hover:border-gray-300">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-2xl">
                                        📋
                                    </div>
                                    <h3 class="font-bold text-gray-900">جرد جزئي</h3>
                                </div>
                                <p class="text-sm text-gray-600">جرد منتجات محددة تختارها بنفسك</p>
                            </div>
                        </label>

                        <label class="relative cursor-pointer">
                            <input type="radio" name="count_type" value="random" 
                                   class="peer sr-only" 
                                   {{ old('count_type') == 'random' ? 'checked' : '' }}>
                            <div class="p-5 bg-gray-50 border-2 border-gray-200 rounded-xl transition-all peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:shadow-md hover:border-gray-300">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-2xl">
                                        🎲
                                    </div>
                                    <h3 class="font-bold text-gray-900">جرد عشوائي</h3>
                                </div>
                                <p class="text-sm text-gray-600">النظام يختار عينة عشوائية للجرد</p>
                            </div>
                        </label>
                    </div>

                    <!-- Info for each type -->
                    <div id="type_info" class="mt-4"></div>
                </div>

                <!-- Products Selection (Partial) -->
                <div id="products_section" class="hidden">
                    <label class="block text-sm font-bold text-gray-900 mb-3">
                        اختر المنتجات <span class="text-red-500">*</span>
                    </label>
                    <div class="bg-purple-50 border-2 border-purple-200 rounded-xl p-4 mb-3">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-purple-800">
                                <p class="font-bold mb-1">كيف تختار المنتجات:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>اختر المخزن أولاً لعرض المنتجات المتاحة</li>
                                    <li>اضغط <kbd class="px-2 py-0.5 bg-white rounded border">Ctrl</kbd> + Click لاختيار عدة منتجات</li>
                                    <li>يمكنك اختيار حتى 1000 منتج</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <select name="product_ids[]" 
                                id="product_ids"
                                multiple
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-all"
                                style="min-height: 300px;">
                            <option value="" disabled class="text-gray-400">اختر المخزن أولاً...</option>
                        </select>
                        <div id="products_loading" class="hidden absolute inset-0 bg-white/90 backdrop-blur-sm flex items-center justify-center rounded-xl">
                            <div class="text-center">
                                <svg class="animate-spin h-10 w-10 text-purple-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-sm text-gray-600 font-medium">جاري تحميل المنتجات...</p>
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-500" id="selected_count">لم يتم اختيار أي منتجات</p>
                </div>

                <!-- Random Count -->
                <div id="random_section" class="hidden">
                    <label class="block text-sm font-bold text-gray-900 mb-3">
                        عدد المنتجات العشوائية <span class="text-red-500">*</span>
                    </label>
                    <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4 mb-3">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-green-800">
                                <p class="font-bold mb-1">الجرد العشوائي:</p>
                                <p>سيقوم النظام باختيار عدد من المنتجات بشكل عشوائي للجرد. هذه الطريقة مفيدة للجرد الدوري السريع والتحقق من دقة المخزون.</p>
                            </div>
                        </div>
                    </div>
                    <input type="number" 
                           name="random_count"
                           id="random_count"
                           class="w-full px-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 transition-all font-medium"
                           value="{{ old('random_count') }}"
                           min="1"
                           max="500"
                           placeholder="مثال: 50 منتج">
                    <p class="mt-2 text-sm text-gray-500">سيتم اختيار المنتجات عشوائياً من المخزن (حد أقصى: 500)</p>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-3">ملاحظات</label>
                    <textarea name="notes" 
                              id="notes"
                              class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all resize-none" 
                              rows="4" 
                              maxlength="2000"
                              placeholder="أضف أي ملاحظات إضافية حول الجرد...">{{ old('notes') }}</textarea>
                    <div class="mt-2 flex justify-between items-center">
                        <p class="text-sm text-gray-500">ملاحظات اختيارية عن الجرد</p>
                        <p class="text-sm text-gray-400"><span id="notes_count">0</span> / 2000</p>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="bg-gray-50 px-8 py-6 border-t-2 border-gray-100 flex gap-3">
                <button type="submit" 
                        id="submitBtn"
                        class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-4 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-3 group">
                    <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span>إنشاء الجرد</span>
                </button>
                <a href="{{ route('stock-counts.index') }}" 
                   class="px-8 py-4 bg-white border-2 border-gray-200 hover:border-gray-300 text-gray-700 rounded-xl font-bold transition-all">
                    إلغاء
                </a>
            </div>
        </form>
    </div>

    <!-- Help Section -->
    <div class="mt-8 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border-2 border-blue-100 p-8">
        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            خطوات إجراء الجرد
        </h3>
        <div class="grid md:grid-cols-2 gap-6">
            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 text-white rounded-xl flex items-center justify-center font-black text-lg shadow-md">1</div>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 mb-1">اختر المخزن والتاريخ</h4>
                    <p class="text-sm text-gray-600">حدد المخزن المراد جرده وتاريخ الجرد</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 text-white rounded-xl flex items-center justify-center font-black text-lg shadow-md">2</div>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 mb-1">حدد نوع الجرد</h4>
                    <p class="text-sm text-gray-600">شامل (كل المنتجات) أو جزئي (منتجات محددة) أو عشوائي (عينة)</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 text-white rounded-xl flex items-center justify-center font-black text-lg shadow-md">3</div>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 mb-1">ابدأ الجرد</h4>
                    <p class="text-sm text-gray-600">بعد الإنشاء، ابدأ تسجيل الكميات الفعلية</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-600 to-green-700 text-white rounded-xl flex items-center justify-center font-black text-lg shadow-md">4</div>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 mb-1">أتمم الجرد</h4>
                    <p class="text-sm text-gray-600">بعد الانتهاء، قم بإتمام الجرد لتحديث المخزون</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const countTypeRadios = document.querySelectorAll('input[name="count_type"]');
    const warehouseSelect = document.getElementById('warehouse_id');
    const productsSection = document.getElementById('products_section');
    const randomSection = document.getElementById('random_section');
    const productsSelect = document.getElementById('product_ids');
    const productsLoading = document.getElementById('products_loading');
    const notesTextarea = document.getElementById('notes');
    const notesCount = document.getElementById('notes_count');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('stockCountForm');
    const selectedCount = document.getElementById('selected_count');

    // Update character counter
    notesTextarea.addEventListener('input', function() {
        notesCount.textContent = this.value.length;
    });
    notesCount.textContent = notesTextarea.value.length;

    // Track selected products count
    if (productsSelect) {
        productsSelect.addEventListener('change', function() {
            const selected = this.selectedOptions.length;
            if (selected === 0) {
                selectedCount.textContent = 'لم يتم اختيار أي منتجات';
                selectedCount.classList.remove('text-green-600', 'font-bold');
            } else {
                selectedCount.textContent = `تم اختيار ${selected} منتج`;
                selectedCount.classList.add('text-green-600', 'font-bold');
            }
        });
    }

    // Handle count type changes
    countTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const type = this.value;
            
            productsSection.classList.toggle('hidden', type !== 'partial');
            randomSection.classList.toggle('hidden', type !== 'random');
            
            if (type === 'partial' && warehouseSelect.value) {
                loadWarehouseProducts(warehouseSelect.value);
            }
        });
    });

    // Load products when warehouse changes
    warehouseSelect.addEventListener('change', function() {
        const selectedType = document.querySelector('input[name="count_type"]:checked').value;
        if (selectedType === 'partial' && this.value) {
            loadWarehouseProducts(this.value);
        }
    });

    // Load warehouse products
    function loadWarehouseProducts(warehouseId) {
        productsLoading.classList.remove('hidden');
        productsSelect.innerHTML = '<option value="" disabled>جاري التحميل...</option>';
        productsSelect.disabled = true;
        
        fetch(`/stock-counts/warehouses/${warehouseId}/products`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('فشل تحميل المنتجات');
                }
                return response.json();
            })
            .then(data => {
                productsSelect.innerHTML = '';
                productsSelect.disabled = false;
                
                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        const code = product.sku || product.barcode || product.code || 'بدون كود';
                        option.textContent = `${product.name} (${code}) - الكمية: ${product.quantity}`;
                        productsSelect.appendChild(option);
                    });
                    selectedCount.textContent = `متاح ${data.count} منتج - اختر المنتجات المطلوبة`;
                } else {
                    productsSelect.innerHTML = '<option value="" disabled>لا توجد منتجات في هذا المخزن</option>';
                    selectedCount.textContent = 'لا توجد منتجات متاحة في هذا المخزن';
                    selectedCount.classList.add('text-red-600');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                productsSelect.innerHTML = '<option value="" disabled class="text-red-600">حدث خطأ في تحميل المنتجات</option>';
                productsSelect.disabled = false;
                selectedCount.textContent = 'حدث خطأ في تحميل المنتجات';
                selectedCount.classList.add('text-red-600');
                alert('⚠️ حدث خطأ في تحميل المنتجات. يرجى المحاولة مرة أخرى.');
            })
            .finally(() => {
                productsLoading.classList.add('hidden');
            });
    }

    // Form validation
    form.addEventListener('submit', function(e) {
        const countType = document.querySelector('input[name="count_type"]:checked').value;
        
        if (countType === 'partial') {
            const selectedProducts = Array.from(productsSelect.selectedOptions).filter(opt => opt.value);
            if (selectedProducts.length === 0) {
                e.preventDefault();
                alert('⚠️ يجب اختيار منتج واحد على الأقل للجرد الجزئي');
                productsSelect.focus();
                return false;
            }
        }
        
        if (countType === 'random') {
            const randomCount = document.getElementById('random_count').value;
            if (!randomCount || randomCount <= 0) {
                e.preventDefault();
                alert('⚠️ يجب تحديد عدد المنتجات للجرد العشوائي');
                document.getElementById('random_count').focus();
                return false;
            }
        }
        
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>جاري الإنشاء...</span>
        `;
    });

    // Initialize
    const checkedRadio = document.querySelector('input[name="count_type"]:checked');
    if (checkedRadio) {
        checkedRadio.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection