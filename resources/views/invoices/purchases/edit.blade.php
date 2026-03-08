سأقوم بتحسين تصميم صفحة "تعديل فاتورة شراء" لتكون متوافقة تماماً مع الهواتف والويب، مع التركيز على سهولة إضافة الأصناف وتعديلها في الهاتف.

**أبرز التعديلات:**
1.  **تحسين الأصناف (Items Layout):** في الهاتف، سيظهر حقل "المنتج" بحجم كامل، وحقول "الكمية والسعر" بجانب بعضهما لتوفير المساحة، مع زر حذف واضح.
2.  **الأزرار (Buttons):** أصبحت الأزرار الرئيسية (حفظ وإلغاء) تظهر بشكل عمودي في الهاتف (Full Width) ليسهل الضغط عليها، وأفقياً في الويب.
3.  **التنبيهات:** تم تحسين شكل رسائل الخطأ والتنبيه لتبدو واضحة وجميلة.
4.  **معالجة الجافاسكريبت:** تم تحسين كود إضافة صنف جديد ليقوم بتصفير حقول الاختيار (Select) بشكل صحيح.

إليك الكود المحدث:

```blade
@extends('layouts.app')

@section('title', 'تعديل فاتورة شراء')

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-6 max-w-7xl">
    
    {{-- رسائل الخطأ --}}
    @if($errors->any())
        <div class="bg-red-50 border-r-4 border-red-500 text-red-700 px-4 py-3 rounded-lg mb-4 shadow-sm" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle ml-2"></i>
                <strong class="font-bold">يوجد خطأ!</strong>
            </div>
            <ul class="mt-2 text-sm list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-600 to-pink-500 px-4 sm:px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h2 class="text-xl sm:text-2xl font-bold text-white">
                    تعديل فاتورة شراء
                </h2>
                <span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm font-mono">
                    #{{ $invoice->invoice_number }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('invoices.purchases.update', $invoice->id) }}" class="p-4 sm:p-6">
            @csrf
            @method('PUT')

            {{-- البيانات الأساسية --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-6">
                {{-- Supplier --}}
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                        المورد <span class="text-red-500">*</span>
                    </label>
                    <select name="supplier_id" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition text-sm @error('supplier_id') border-red-500 @enderror"
                            required>
                        <option value="">اختر المورد</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" 
                                {{ (old('supplier_id', $invoice->supplier_id) == $supplier->id) ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Warehouse --}}
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                        المخزن <span class="text-red-500">*</span>
                    </label>
                    <select name="warehouse_id" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition text-sm @error('warehouse_id') border-red-500 @enderror"
                            required>
                        <option value="">اختر المخزن</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" 
                                {{ (old('warehouse_id', $invoice->warehouse_id) == $warehouse->id) ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Invoice Date --}}
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                        تاريخ الفاتورة <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="invoice_date" 
                           value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}"
                           max="{{ date('Y-m-d') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition text-sm @error('invoice_date') border-red-500 @enderror"
                           required>
                    @error('invoice_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Items Section --}}
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3 border-b pb-3">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-boxes text-purple-500"></i>
                        قائمة الأصناف
                    </h3>
                    <button type="button" 
                            onclick="addItem()" 
                            class="w-full sm:w-auto px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition flex items-center justify-center gap-2 shadow-sm text-sm font-semibold">
                        <i class="fas fa-plus"></i>
                        إضافة صنف جديد
                    </button>
                </div>

                <div id="items-container" class="space-y-3">
                    @foreach($invoice->items as $index => $item)
                        <div class="item-row bg-gray-50 p-3 sm:p-4 rounded-xl border border-gray-200 hover:border-purple-300 transition-colors">
                            {{-- تصميم شبكي متجاوب: عمود واحد في الموبايل، 5 أعمدة في الويب --}}
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                                
                                {{-- Product (يأخذ عرض كامل في الموبايل) --}}
                                <div class="col-span-2 sm:col-span-2">
                                    <label class="block text-gray-600 text-xs font-medium mb-1">الصنف <span class="text-red-500">*</span></label>
                                    <select name="items[{{ $index }}][product_id]" 
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white"
                                            required>
                                        <option value="">اختر الصنف</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Quantity --}}
                                <div>
                                    <label class="block text-gray-600 text-xs font-medium mb-1">الكمية <span class="text-red-500">*</span></label>
                                    <input type="number" 
                                           name="items[{{ $index }}][qty]" 
                                           step="0.01"
                                           min="0.01"
                                           value="{{ $item->qty }}"
                                           class="item-qty w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent text-center"
                                           onchange="calculateItemTotal(this)"
                                           required>
                                </div>

                                {{-- Price --}}
                                <div>
                                    <label class="block text-gray-600 text-xs font-medium mb-1">السعر <span class="text-red-500">*</span></label>
                                    <input type="number" 
                                           name="items[{{ $index }}][price]" 
                                           step="0.01"
                                           min="0"
                                           value="{{ $item->price }}"
                                           class="item-price w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent text-center"
                                           onchange="calculateItemTotal(this)"
                                           required>
                                </div>

                                {{-- Total & Remove (يجمعهم في صف واحد) --}}
                                <div class="col-span-2 sm:col-span-1 flex items-end gap-2">
                                    <div class="flex-1">
                                        <label class="block text-gray-600 text-xs font-medium mb-1">الإجمالي</label>
                                        <input type="text" 
                                               class="item-total w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-700 font-semibold text-center"
                                               readonly 
                                               value="{{ number_format($item->total, 2) }}">
                                    </div>
                                    <button type="button" 
                                            onclick="removeItem(this)" 
                                            class="px-3 py-2 bg-red-100 hover:bg-red-500 text-red-600 hover:text-white rounded-lg transition border border-red-200 hover:border-red-500"
                                            title="حذف الصنف">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Discount & Tax --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-6 bg-gray-50 p-4 rounded-lg border">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">الخصم (اختياري)</label>
                    <input type="number" 
                           name="discount" 
                           step="0.01"
                           min="0"
                           value="{{ old('discount', $invoice->discount) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                           placeholder="0.00">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">الضريبة (اختياري)</label>
                    <input type="number" 
                           name="tax" 
                           step="0.01"
                           min="0"
                           value="{{ old('tax', $invoice->tax) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                           placeholder="0.00">
                </div>
            </div>

            {{-- Notes --}}
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">ملاحظات (اختياري)</label>
                <textarea name="notes" 
                          rows="2"
                          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                          placeholder="أي ملاحظات إضافية...">{{ old('notes', $invoice->notes) }}</textarea>
            </div>

            {{-- Warning Alert --}}
            <div class="bg-amber-50 border-r-4 border-amber-400 text-amber-800 px-4 py-3 rounded-lg mb-6 text-sm">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                    <div>
                        <p class="font-bold">تنبيه هام!</p>
                        <p class="text-amber-700">عند تعديل الفاتورة، سيتم تعديل الكميات في المخزون تلقائياً بناءً على التغييرات الجديدة.</p>
                    </div>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t">
                <button type="submit" 
                        class="flex-1 sm:flex-none px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-lg transition shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i>
                    حفظ التعديلات
                </button>
                <a href="{{ route('invoices.purchases.show', $invoice->id) }}" 
                   class="flex-1 sm:flex-none px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition text-center flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-right"></i>
                    إلغاء والرجوع
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let itemIndex = {{ count($invoice->items) }};

// إضافة صنف جديد
function addItem() {
    const container = document.getElementById('items-container');
    const firstItem = container.querySelector('.item-row');
    
    if (!firstItem) return; // In case there are no items initially
    
    const newItem = firstItem.cloneNode(true);
    
    // تحديث الـ name attributes وإعادة تعيين القيم
    newItem.querySelectorAll('select, input').forEach(field => {
        if (field.name) {
            field.name = field.name.replace(/items\[\d+\]/, `items[${itemIndex}]`);
        }
        
        // تصفير القيم
        if (field.tagName === 'SELECT') {
            field.selectedIndex = 0; // اختيار الخيار الأول (اختر الصنف)
        } else if (field.type === 'number') {
            field.value = ''; // تصفير حقول الأرقام
        } else if (field.classList.contains('item-total')) {
            field.value = '0.00';
        }
    });
    
    container.appendChild(newItem);
    itemIndex++;
    
    // التمرير للعنصر الجديد
    newItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// حذف صنف
function removeItem(button) {
    const container = document.getElementById('items-container');
    const items = container.querySelectorAll('.item-row');
    
    if (items.length > 1) {
        // تأكيد بسيط قبل الحذف
        if(confirm('هل أنت متأكد من حذف هذا الصنف؟')) {
            button.closest('.item-row').remove();
        }
    } else {
        // تنبيه مقسم (Toast أفضل لكن هذا ابسط)
        alert('يجب وجود صنف واحد على الأقل في الفاتورة');
    }
}

// حساب إجمالي الصنف
function calculateItemTotal(input) {
    const row = input.closest('.item-row');
    const qtyInput = row.querySelector('.item-qty');
    const priceInput = row.querySelector('.item-price');
    
    const qty = parseFloat(qtyInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    
    const total = qty * price;
    row.querySelector('.item-total').value = total.toFixed(2);
}
</script>
@endpush
@endsection
```