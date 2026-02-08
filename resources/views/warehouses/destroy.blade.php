@extends('layouts.app')

@section('title', 'حذف المخزن')
@section('page-title', 'حذف المخزن')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">حذف المخزن: {{ $warehouse->name }}</h2>
    
    <p class="text-gray-600 mb-6">
        هل أنت متأكد من أنك تريد حذف هذا المخزن؟ لن تتمكن من التراجع عن هذا الإجراء.
    </p>

    <table class="table-auto w-full mb-6 border border-gray-200">
        <tbody>
            <tr class="border-b">
                <th class="text-left px-4 py-2">الاسم</th>
                <td class="px-4 py-2">{{ $warehouse->name }}</td>
            </tr>
            <tr class="border-b">
                <th class="text-left px-4 py-2">الكود</th>
                <td class="px-4 py-2">{{ $warehouse->code }}</td>
            </tr>
            <tr class="border-b">
                <th class="text-left px-4 py-2">المدينة</th>
                <td class="px-4 py-2">{{ $warehouse->city }}</td>
            </tr>
            <tr class="border-b">
                <th class="text-left px-4 py-2">المنطقة</th>
                <td class="px-4 py-2">{{ $warehouse->area ?? '-' }}</td>
            </tr>
            <tr>
                <th class="text-left px-4 py-2">الحالة</th>
                <td class="px-4 py-2">{{ $warehouse->status_label }}</td>
            </tr>
        </tbody>
    </table>

    <div class="flex gap-4">
        <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                    onclick="return confirm('هل أنت متأكد من حذف هذا المخزن؟')">
                تأكيد الحذف
            </button>
        </form>

        <a href="{{ route('warehouses.index') }}"
           class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
           إلغاء
        </a>
    </div>
</div>
@endsection
