@extends('layouts.app')

@section('title', 'إضافة مرتجع مبيعات')
@section('page-title', 'إضافة مرتجع مبيعات')

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('returnForm', () => ({
        invoiceId: '',
        invoices: @json($invoicesData),
        
        items: [],
        total: 0,

        get selectedInvoice() {
            if (!this.invoiceId) return null;
            return this.invoices.find(inv => inv.id == this.invoiceId);
        },

        get availableItems() {
            if (!this.selectedInvoice) return [];
            return this.selectedInvoice.items.filter(item => item.available_quantity > 0);
        },

        onInvoiceChange() {
            this.items = [];
            this.total = 0;
        },

        addItem() {
            this.items.push({
                product_id: '',
                product_name: '',
                quantity: 1,
                price: 0,
                available_quantity: 0,
                total: 0,
                show_warning: false
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
            this.calculateTotal();
        },

        loadItemData(index) {
            const item = this.items[index];
            if (!item.product_id || !this.selectedInvoice) {
                item.price = 0;
                item.available_quantity = 0;
                item.show_warning = false;
                return;
            }

            const invoiceItem = this.selectedInvoice.items.find(
                i => i.product_id == item.product_id
            );

            if (invoiceItem) {
                item.product_name = invoiceItem.product_name;
                item.price = invoiceItem.price;
                item.available_quantity = invoiceItem.available_quantity;
                this.checkQuantity(index);
            }
        },

        checkQuantity(index) {
            const item = this.items[index];
            item.show_warning = item.quantity > item.available_quantity;
            this.calculateItemTotal(index);
        },

        calculateItemTotal(index) {
            const item = this.items[index];
            item.total = Math.round((item.quantity * item.price) * 100) / 100;
            this.calculateTotal();
        },

        calculateTotal() {
            this.total = Math.round(this.items.reduce((sum, item) => sum + item.total, 0) * 100) / 100;
        },

        validateForm() {
            if (!this.invoiceId) {
                alert('⚠️ يجب اختيار فاتورة أولاً');
                return false;
            }

            if (this.items.length === 0) {
                alert('⚠️ يجب إضافة صنف واحد على الأقل');
                return false;
            }

            const invalidItems = this.items.filter(item => 
                item.product_id && item.quantity > item.available_quantity
            );

            if (invalidItems.length > 0) {
                const itemsList = invalidItems.map(item => 
                    `- ${item.product_name}: مطلوب ${item.quantity}، متاح ${item.available_quantity}`
                ).join('\n');
                
                alert(`⚠️ الأصناف التالية تتجاوز الكمية المتاحة:\n\n${itemsList}`);
                return false;
            }

            return true;
        }
    }));
});
</script>
@endpush

@section('content')
<div x-data="returnForm" class="max-w-6xl mx-auto">
    <form method="POST" 
          action="{{ route('invoices.sales-returns.store') }}"
          enctype="multipart/form-data"
          @submit="if (!validateForm()) { $event.preventDefault(); }">
        @csrf

        <!-- Header Info -->
        <div class="bg-white p-6 rounded-xl shadow mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                <div class="bg-red-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                </div>
                إنشاء مرتجع مبيعات جديد
            </h2>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- اختيار فاتورة البيع -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        فاتورة البيع *
                    </label>
                    <select name="sales_invoice_id" 
                            x-model="invoiceId"
                            @change="onInvoiceChange()"
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            required>
                        <option value="">اختر فاتورة</option>
                        <template x-for="invoice in invoices" :key="invoice.id">
                            <option :value="invoice.id" 
                                    x-text="`${invoice.invoice_number} - ${invoice.customer_name}`">
                            </option>
                        </template>
                    </select>
                    @error('sales_invoice_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- تاريخ المرتجع -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        تاريخ المرتجع
                    </label>
                    <input type="date" 
                           name="return_date" 
                           value="{{ date('Y-m-d') }}"
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <!-- تحذير إذا لم يتم اختيار فاتورة -->
            <div x-show="!invoiceId" 
                 class="mt-4 bg-yellow-50 border border-yellow-300 rounded-lg p-4 flex items-start gap-3">
                <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-semibold text-yellow-800">يجب اختيار فاتورة أولاً</p>
                    <p class="text-sm text-yellow-700 mt-1">لن تتمكن من إضافة أصناف حتى تختار فاتورة البيع</p>
                </div>
            </div>
        </div>

        <!-- Invoice Details Display -->
        <div x-show="invoiceId && selectedInvoice" class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl shadow-lg p-6 mb-6 border-2 border-blue-200">
            <h3 class="text-lg font-bold text-blue-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                تفاصيل فاتورة البيع
            </h3>

            <div class="grid md:grid-cols-6 gap-4 mb-4">
                <div class="bg-white rounded-lg p-4 shadow">
                    <p class="text-xs text-gray-500 mb-1">رقم الفاتورة</p>
                    <p class="font-bold text-blue-600" x-text="selectedInvoice.invoice_number"></p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow">
                    <p class="text-xs text-gray-500 mb-1">العميل</p>
                    <p class="font-bold text-gray-800" x-text="selectedInvoice.customer_name"></p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow">
                    <p class="text-xs text-gray-500 mb-1">التاريخ</p>
                    <p class="font-bold text-gray-800" x-text="selectedInvoice.invoice_date"></p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow">
                    <p class="text-xs text-gray-500 mb-1">إجمالي الفاتورة</p>
                    <p class="font-bold text-blue-600" x-text="parseFloat(selectedInvoice.total || 0).toFixed(2) + ' ج.م'"></p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow">
                    <p class="text-xs text-gray-500 mb-1">المدفوع</p>
                    <p class="font-bold text-green-600" x-text="parseFloat(selectedInvoice.paid || 0).toFixed(2) + ' ج.م'"></p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow">
                    <p class="text-xs text-gray-500 mb-1">المتبقي</p>
                    <p class="font-bold text-red-600" x-text="parseFloat(selectedInvoice.remaining || 0).toFixed(2) + ' ج.م'"></p>
                </div>
            </div>

            <!-- Invoice Items Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-2">
                    <h4 class="font-bold text-white">أصناف الفاتورة الأصلية</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">الصنف</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">الكمية الأصلية</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">الكمية المرتجعة</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">المتاح للإرجاع</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">السعر</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <template x-for="item in selectedInvoice.items" :key="item.product_id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 font-medium" x-text="item.product_name"></td>
                                    <td class="px-3 py-2" x-text="item.original_quantity"></td>
                                    <td class="px-3 py-2 text-red-600 font-semibold" x-text="item.returned_quantity"></td>
                                    <td class="px-3 py-2">
                                        <span :class="item.available_quantity > 0 ? 'text-green-600 font-bold' : 'text-gray-400'"
                                              x-text="item.available_quantity"></span>
                                    </td>
                                    <td class="px-3 py-2" x-text="item.price.toFixed(2) + ' ج.م'"></td>
                                    <td class="px-3 py-2 font-bold" x-text="(item.original_quantity * item.price).toFixed(2) + ' ج.م'"></td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2">
                            <tr>
                                <td colspan="5" class="px-3 py-2 text-right font-bold">إجمالي الفاتورة:</td>
                                <td class="px-3 py-2 font-bold text-blue-600" 
                                    x-text="selectedInvoice.items.reduce((sum, item) => sum + (item.original_quantity * item.price), 0).toFixed(2) + ' ج.م'">
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white p-6 rounded-xl shadow mb-6" x-show="invoiceId">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">الأصناف المرتجعة</h3>
                <button type="button" 
                        @click="addItem()"
                        :disabled="availableItems.length === 0"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    إضافة صنف
                </button>
            </div>

            <!-- تحذير عدم وجود أصناف متاحة -->
            <div x-show="invoiceId && availableItems.length === 0"
                 class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-gray-600 font-semibold">لا توجد أصناف متاحة للإرجاع</p>
                <p class="text-gray-500 text-sm mt-1">جميع أصناف هذه الفاتورة تم إرجاعها بالكامل</p>
            </div>

            <div class="overflow-x-auto" x-show="availableItems.length > 0">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-2 text-right">#</th>
                            <th class="px-4 py-2 text-right">الصنف</th>
                            <th class="px-4 py-2 text-right">المتاح للإرجاع</th>
                            <th class="px-4 py-2 text-right">الكمية</th>
                            <th class="px-4 py-2 text-right">السعر</th>
                            <th class="px-4 py-2 text-right">الإجمالي</th>
                            <th class="px-4 py-2 text-center">حذف</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-gray-50" 
                                :class="{ 'bg-red-50': item.show_warning }">
                                <td class="px-4 py-2" x-text="index + 1"></td>
                                
                                <td class="px-4 py-2">
                                    <select :name="'items[' + index + '][product_id]'" 
                                            x-model="item.product_id"
                                            @change="loadItemData(index)"
                                            class="w-full border rounded px-2 py-1 text-sm"
                                            required>
                                        <option value="">اختر الصنف</option>
                                        <template x-for="availItem in availableItems" :key="availItem.product_id">
                                            <option :value="availItem.product_id" 
                                                    x-text="availItem.product_name">
                                            </option>
                                        </template>
                                    </select>
                                </td>

                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        <span x-text="item.available_quantity"
                                              :class="{
                                                  'text-green-600 font-bold': item.available_quantity > item.quantity,
                                                  'text-red-600 font-bold': item.available_quantity < item.quantity
                                              }">
                                        </span>
                                        <svg x-show="item.show_warning" 
                                             class="w-4 h-4 text-red-600" 
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <p x-show="item.show_warning" class="text-red-600 text-xs mt-1">
                                        تجاوز المتاح!
                                    </p>
                                </td>

                                <td class="px-4 py-2">
                                    <input type="number" 
                                           :name="'items[' + index + '][quantity]'"
                                           x-model="item.quantity" 
                                           @input="checkQuantity(index)"
                                           class="w-20 border rounded px-2 py-1 text-sm"
                                           :class="{ 'border-red-500 bg-red-50': item.show_warning }"
                                           min="0.001" step="0.001" required>
                                </td>

                                <td class="px-4 py-2">
                                    <input type="number" 
                                           :name="'items[' + index + '][price]'"
                                           x-model="item.price" 
                                           @input="calculateItemTotal(index)"
                                           class="w-24 border rounded px-2 py-1 text-sm" 
                                           min="0" step="0.01" required readonly>
                                </td>

                                <td class="px-4 py-2 font-semibold text-right"
                                    x-text="item.total.toFixed(2) + ' ج.م'"></td>

                                <td class="px-4 py-2 text-center">
                                    <button type="button" @click="removeItem(index)"
                                            class="text-red-600 hover:text-red-800 font-bold text-lg">✖</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="grid md:grid-cols-2 gap-6 mb-6" x-show="invoiceId">
            <!-- الإجمالي -->
            <div class="bg-white p-6 rounded-xl shadow">
                <label class="block text-sm font-semibold text-gray-700 mb-2">إجمالي المرتجع</label>
                <div class="text-3xl font-bold text-red-600" x-text="total.toFixed(2) + ' ج.م'"></div>
            </div>

            <!-- الملاحظات -->
            <div class="bg-white p-6 rounded-xl shadow">
                <label class="block text-sm font-semibold text-gray-700 mb-2">سبب المرتجع / ملاحظات</label>
                <textarea name="notes" 
                          rows="3"
                          class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                          placeholder="مثال: عيب في المنتج / غير مطابق للمواصفات"></textarea>
                @error('notes')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- صور داعمة -->
        <div class="bg-white p-6 rounded-xl shadow mb-6" x-show="invoiceId">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                صور داعمة (اختياري)
            </label>
            <input type="file" 
                   name="images[]" 
                   multiple
                   accept="image/*"
                   class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
            <p class="text-sm text-gray-500 mt-2">
                يمكنك رفع صور توضح سبب المرتجع (الحد الأقصى 2MB لكل صورة)
            </p>
        </div>

        <!-- Submit Buttons -->
        <div class="flex gap-3">
            <button type="submit" 
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                حفظ المرتجع
            </button>
            <a href="{{ route('invoices.sales-returns.index') }}" 
               class="px-6 py-3 rounded-lg font-semibold border border-gray-300 hover:bg-gray-50">
                إلغاء
            </a>
        </div>
    </form>
</div>
@endsection