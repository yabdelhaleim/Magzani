@extends('layouts.app')

@section('page-title', 'إنشاء فاتورة بيع')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-bold mb-4">إنشاء فاتورة بيع جديدة</h2>

    <!-- Form مثال -->
    <form action="#" method="POST">
        @csrf

        <!-- العميل -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-1">اسم العميل</label>
            <input type="text" name="customer" class="w-full border px-3 py-2 rounded-lg" placeholder="أدخل اسم العميل">
        </div>

        <!-- المنتجات -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-1">المنتجات</label>
            <input type="text" name="products" class="w-full border px-3 py-2 rounded-lg" placeholder="أدخل المنتجات">
        </div>

        <!-- المجموع -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-1">المجموع</label>
            <input type="text" name="total" class="w-full border px-3 py-2 rounded-lg" placeholder="المبلغ الإجمالي">
        </div>

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
            حفظ الفاتورة
        </button>
    </form>
</div>
@endsection
