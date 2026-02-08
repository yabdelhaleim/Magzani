@extends('layouts.app')

@section('title', 'تعديل فاتورة بيع')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4">
    <div class="max-w-5xl mx-auto">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <h2 class="text-2xl font-bold text-white">تعديل فاتورة بيع</h2>
                    </div>
                    <span class="bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-full text-sm font-semibold">
                        {{ $invoice->invoice_number }}
                    </span>
                </div>
            </div>

            <!-- Form Section -->
            <form method="POST" action="{{ route('invoices.sales.update', $invoice->id) }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Customer & Warehouse Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Customer -->
                    <div class="space-y-2">
                        <label for="customer_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="flex items-center space-x-2 space-x-reverse">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>العميل</span>
                            </span>
                        </label>
                        <select 
                            name="customer_id" 
                            id="customer_id"
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200 bg-white hover:border-yellow-400 text-gray-700"
                            required
                        >
                            <option value="" disabled>-- اختر العميل --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected($customer->id == $invoice->customer_id)>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Warehouse -->
                    <div class="space-y-2">
                        <label for="warehouse_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="flex items-center space-x-2 space-x-reverse">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <span>المستودع</span>
                            </span>
                        </label>
                        <select 
                            name="warehouse_id" 
                            id="warehouse_id"
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200 bg-white hover:border-yellow-400 text-gray-700"
                            required
                        >
                            <option value="" disabled>-- اختر المستودع --</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected($warehouse->id == $invoice->warehouse_id)>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Invoice Date -->
                <div class="space-y-2">
                    <label for="invoice_date" class="block text-sm font-semibold text-gray-700 mb-2">
                        <span class="flex items-center space-x-2 space-x-reverse">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>تاريخ الفاتورة</span>
                        </span>
                    </label>
                    <input 
                        type="date" 
                        name="invoice_date" 
                        id="invoice_date"
                        value="{{ $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : now()->format('Y-m-d') }}"
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200"
                    >
                </div>

                <!-- Items Section -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-5 border-2 border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center space-x-2 space-x-reverse">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span>الأصناف</span>
                    </h3>
                    
                    <div id="items-container" class="space-y-3">
                        @foreach($invoice->items as $index => $item)
                        <div class="item-row bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                                <!-- Product -->
                                <div class="md:col-span-2">
                                    <label class="text-xs font-semibold text-gray-600 mb-1 block">المنتج</label>
                                    <select 
                                        name="items[{{ $index }}][product_id]" 
                                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500"
                                        required
                                    >
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" @selected($product->id == $item->product_id)>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Quantity -->
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 mb-1 block">الكمية</label>
                                    <input 
                                        type="number" 
                                        name="items[{{ $index }}][quantity]" 
                                        value="{{ $item->quantity }}"
                                        step="0.001"
                                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500"
                                        required
                                    >
                                </div>

                                <!-- Price -->
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 mb-1 block">السعر</label>
                                    <input 
                                        type="number" 
                                        name="items[{{ $index }}][price]" 
                                        value="{{ $item->price }}"
                                        step="0.01"
                                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500"
                                        required
                                    >
                                </div>

                                <!-- Discount % -->
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 mb-1 block">خصم %</label>
                                    <input 
                                        type="number" 
                                        name="items[{{ $index }}][discount]" 
                                        value="{{ ($item->discount / ($item->quantity * $item->price)) * 100 }}"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500"
                                    >
                                </div>

                                <!-- Tax % -->
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 mb-1 block">ضريبة %</label>
                                    <input 
                                        type="number" 
                                        name="items[{{ $index }}][tax_rate]" 
                                        value="{{ ($item->tax / (($item->quantity * $item->price) - $item->discount)) * 100 }}"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500"
                                    >
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Payment Section -->
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-5 border-2 border-green-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center space-x-2 space-x-reverse">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>معلومات الدفع</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Total Amount (Read Only) -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700 mb-2 block">إجمالي الفاتورة</label>
                            <div class="bg-white border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-600 font-bold">
                                {{ number_format($invoice->calculated_details['net_total'] ?? 0, 2) }} جنيه
                            </div>
                        </div>

                        <!-- Paid Amount -->
                        <div>
                            <label for="paid" class="text-sm font-semibold text-gray-700 mb-2 block">المبلغ المدفوع</label>
                            <input 
                                type="number" 
                                name="paid" 
                                id="paid"
                                value="{{ $invoice->paid ?? 0 }}"
                                step="0.01"
                                min="0"
                                max="{{ $invoice->calculated_details['net_total'] ?? 0 }}"
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 font-semibold"
                                placeholder="0.00"
                            >
                            @error('paid')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remaining Amount (Auto Calculate) -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700 mb-2 block">المتبقي</label>
                            <div id="remaining-amount" class="bg-yellow-100 border-2 border-yellow-300 rounded-lg px-4 py-3 text-yellow-800 font-bold">
                                {{ number_format($invoice->calculated_details['remaining'] ?? 0, 2) }} جنيه
                            </div>
                        </div>
                    </div>

                    <!-- Payment Status Display -->
                    <div class="mt-4 p-4 bg-white rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-700">حالة الدفع الحالية:</span>
                            @php
                                $statusConfig = [
                                    'paid' => ['text' => 'مدفوعة بالكامل', 'class' => 'bg-green-100 text-green-800 border-green-300'],
                                    'partial' => ['text' => 'مدفوعة جزئياً', 'class' => 'bg-yellow-100 text-yellow-800 border-yellow-300'],
                                    'unpaid' => ['text' => 'غير مدفوعة', 'class' => 'bg-red-100 text-red-800 border-red-300'],
                                ];
                                $status = $statusConfig[$invoice->payment_status] ?? ['text' => 'غير محدد', 'class' => 'bg-gray-100 text-gray-800 border-gray-300'];
                            @endphp
                            <span id="payment-status-badge" class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold border-2 {{ $status['class'] }}">
                                {{ $status['text'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Additional Charges -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="discount_value" class="text-sm font-semibold text-gray-700 mb-2 block">خصم عام</label>
                        <input 
                            type="number" 
                            name="discount_value" 
                            id="discount_value"
                            value="{{ $invoice->discount_value ?? 0 }}"
                            step="0.01"
                            min="0"
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                            placeholder="0.00"
                        >
                    </div>

                    <div>
                        <label for="tax_amount" class="text-sm font-semibold text-gray-700 mb-2 block">ضريبة عامة</label>
                        <input 
                            type="number" 
                            name="tax_amount" 
                            id="tax_amount"
                            value="{{ $invoice->tax_amount ?? 0 }}"
                            step="0.01"
                            min="0"
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                            placeholder="0.00"
                        >
                    </div>

                    <div>
                        <label for="shipping_cost" class="text-sm font-semibold text-gray-700 mb-2 block">تكلفة الشحن</label>
                        <input 
                            type="number" 
                            name="shipping_cost" 
                            id="shipping_cost"
                            value="{{ $invoice->shipping_cost ?? 0 }}"
                            step="0.01"
                            min="0"
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                            placeholder="0.00"
                        >
                    </div>

                    <div>
                        <label for="other_charges" class="text-sm font-semibold text-gray-700 mb-2 block">مصاريف أخرى</label>
                        <input 
                            type="number" 
                            name="other_charges" 
                            id="other_charges"
                            value="{{ $invoice->other_charges ?? 0 }}"
                            step="0.01"
                            min="0"
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                            placeholder="0.00"
                        >
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="text-sm font-semibold text-gray-700 mb-2 block">ملاحظات</label>
                    <textarea 
                        name="notes" 
                        id="notes"
                        rows="3"
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200"
                        placeholder="أضف أي ملاحظات هنا..."
                    >{{ $invoice->notes }}</textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-4 border-t-2 border-gray-200">
                    <a href="{{ route('invoices.sales.index') }}" 
                       class="flex items-center space-x-2 space-x-reverse px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200 font-semibold shadow-sm hover:shadow">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>إلغاء</span>
                    </a>

                    <button 
                        type="submit" 
                        class="flex items-center space-x-2 space-x-reverse px-8 py-3 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-lg hover:from-yellow-600 hover:to-yellow-700 transition-all duration-200 font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>حفظ التعديلات</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Section -->
        <div class="mt-6 bg-blue-50 border-r-4 border-blue-400 p-4 rounded-lg">
            <div class="flex items-start space-x-3 space-x-reverse">
                <svg class="w-6 h-6 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h4 class="text-sm font-semibold text-blue-800 mb-1">تعليمات التعديل</h4>
                    <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                        <li>يمكنك تعديل المبلغ المدفوع لتحديث حالة الدفع تلقائياً</li>
                        <li>إذا كان المبلغ المدفوع = الإجمالي، ستصبح الفاتورة "مدفوعة بالكامل"</li>
                        <li>إذا كان المبلغ المدفوع أقل من الإجمالي، ستصبح "مدفوعة جزئياً"</li>
                        <li>إذا كان المبلغ المدفوع = 0، ستصبح "غير مدفوعة"</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paidInput = document.getElementById('paid');
    const remainingDisplay = document.getElementById('remaining-amount');
    const statusBadge = document.getElementById('payment-status-badge');
    const totalAmount = {{ $invoice->calculated_details['net_total'] ?? 0 }};

    // Update remaining amount when paid amount changes
    paidInput.addEventListener('input', function() {
        const paid = parseFloat(this.value) || 0;
        const remaining = totalAmount - paid;
        
        remainingDisplay.textContent = remaining.toFixed(2) + ' جنيه';
        
        // Update status badge
        let statusText = '';
        let statusClass = '';
        
        if (remaining <= 0) {
            statusText = 'مدفوعة بالكامل';
            statusClass = 'bg-green-100 text-green-800 border-green-300';
        } else if (paid > 0) {
            statusText = 'مدفوعة جزئياً';
            statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-300';
        } else {
            statusText = 'غير مدفوعة';
            statusClass = 'bg-red-100 text-red-800 border-red-300';
        }
        
        statusBadge.textContent = statusText;
        statusBadge.className = 'inline-flex items-center px-4 py-2 rounded-full text-sm font-bold border-2 ' + statusClass;
    });
});
</script>
@endsection