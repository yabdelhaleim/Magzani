@extends('layouts.app')

@section('title', 'إضافة مرتجع مبيعات')
@section('page-title', 'إضافة مرتجع مبيعات')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">
            إنشاء مرتجع مبيعات جديد
        </h2>

        <form method="POST"
              action="{{ route('invoices.sales-returns.store') }}"
              enctype="multipart/form-data">
            @csrf

            <!-- اختيار فاتورة البيع -->
            <div class="mb-4">
                <label class="block font-semibold text-gray-700 mb-1">
                    فاتورة البيع
                </label>
                <select name="sales_invoice_id"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2"
                        required>
                    <option value="">اختر فاتورة</option>
                    @foreach($invoices as $invoice)
                        <option value="{{ $invoice->id }}">
                            {{ $invoice->invoice_number }}
                            - {{ $invoice->customer->name }}
                        </option>
                    @endforeach
                </select>
                @error('sales_invoice_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- قيمة المرتجع -->
            <div class="mb-4">
                <label class="block font-semibold text-gray-700 mb-1">
                    قيمة المرتجع
                </label>
                <input type="number"
                       name="total_amount"
                       step="0.01"
                       value="{{ old('total_amount') }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2"
                       placeholder="أدخل قيمة المرتجع"
                       required>
                @error('total_amount')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- سبب المرتجع -->
            <div class="mb-4">
                <label class="block font-semibold text-gray-700 mb-1">
                    سبب المرتجع
                </label>
                <textarea name="reason"
                          rows="3"
                          class="w-full border border-gray-300 rounded-lg px-4 py-2"
                          placeholder="مثال: عيب في المنتج / غير مطابق"
                          required>{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- صور داعمة (اختياري) -->
            <div class="mb-6">
                <label class="block font-semibold text-gray-700 mb-1">
                    صور (اختياري)
                </label>
                <input type="file"
                       name="images[]"
                       multiple
                       class="w-full border border-gray-300 rounded-lg px-4 py-2">
                <p class="text-sm text-gray-500 mt-1">
                    يمكنك رفع أكثر من صورة لتوضيح سبب المرتجع
                </p>
            </div>

            <!-- أزرار -->
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold">
                    حفظ المرتجع
                </button>

                <a href="{{ route('invoices.sales-returns.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-semibold">
                    رجوع
                </a>
            </div>

        </form>
    </div>

</div>
@endsection
