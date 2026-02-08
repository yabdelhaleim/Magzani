@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold mb-6">إضافة مخزن جديد</h2>

    <form action="#" method="POST" class="space-y-4">
        <!-- اسم المخزن -->
        <div>
            <label class="block mb-1 font-medium">اسم المخزن</label>
            <input type="text" placeholder="مثال: المخزن الرئيسي"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- موقع المخزن -->
        <div>
            <label class="block mb-1 font-medium">الموقع</label>
            <input type="text" placeholder="العنوان / المدينة"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- المسؤول عن المخزن -->
        <div>
            <label class="block mb-1 font-medium">المسؤول</label>
            <input type="text" placeholder="اسم المسؤول"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- ملاحظات -->
        <div>
            <label class="block mb-1 font-medium">ملاحظات</label>
            <textarea placeholder="أي ملاحظات إضافية"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      rows="3"></textarea>
        </div>

        <!-- أزرار الإجراء -->
        <div class="flex gap-3 mt-4">
            <button type="submit"
                    class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition">
                حفظ
            </button>
            <a href="{{ route('werehouses.index') }}"
               class="bg-gray-300 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                رجوع
            </a>
        </div>
    </form>
</div>
@endsection
