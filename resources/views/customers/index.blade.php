@extends('layouts.app')

@section('title', 'قائمة العملاء')
@section('page-title', 'العملاء')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">جميع العملاء</h2>
        <p class="text-gray-600 mt-1">إدارة وتتبع العملاء والحسابات</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('customers.export') . '?' . request()->getQueryString() }}"
           class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition flex items-center gap-2">
            <i class="fas fa-file-excel"></i>
            تصدير Excel
        </a>
        <a href="{{ route('customers.create') }}"
           class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition flex items-center gap-2">
            <i class="fas fa-plus"></i>
            إضافة عميل جديد
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-gray-500 text-sm mb-1">إجمالي العملاء</p>
        <h3 class="text-2xl font-bold">{{ $customers->total() }}</h3>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-gray-500 text-sm mb-1">عملاء نشطون</p>
        <h3 class="text-2xl font-bold">{{ $activeCustomers ?? 0 }}</h3>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-gray-500 text-sm mb-1">إجمالي الديون</p>
        <h3 class="text-2xl font-bold">{{ number_format($totalDebt ?? 0) }} ج.م</h3>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-gray-500 text-sm mb-1">متوسط المشتريات</p>
        <h3 class="text-2xl font-bold">{{ number_format($avgSales ?? 0) }} ج.م</h3>
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-4"></th>
                    <th class="px-6 py-4">الكود</th>
                    <th class="px-6 py-4">اسم العميل</th>
                    <th class="px-6 py-4">الهاتف</th>
                    <th class="px-6 py-4">البريد</th>
                    <th class="px-6 py-4">الرصيد</th>
                    <th class="px-6 py-4">الحالة</th>
                    <th class="px-6 py-4">إجراءات</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($customers as $customer)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <input type="checkbox">
                    </td>

                    <td class="px-6 py-4 font-semibold">
                        C-{{ str_pad($customer->id, 3, '0', STR_PAD_LEFT) }}
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($customer->name) }}"
                                 class="w-10 h-10 rounded-full">
                            <div>
                                <div class="font-semibold">{{ $customer->name }}</div>
                                <div class="text-xs text-gray-500">
                                    عميل منذ {{ $customer->created_at->format('Y') }}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">{{ $customer->phone }}</td>
                    <td class="px-6 py-4">{{ $customer->email }}</td>

                    <td class="px-6 py-4 font-semibold">
                        {{ number_format($customer->balance ?? 0) }} ج.م
                    </td>

                    {{-- الحالة --}}
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $customer->is_active
                                ? 'bg-green-100 text-green-700'
                                : 'bg-gray-100 text-gray-700' }}">
                            {{ $customer->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>

                    {{-- الإجراءات --}}
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <a href="{{ route('customers.show', $customer->id) }}"
                               class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('customers.edit', $customer->id) }}"
                               class="p-2 bg-gray-100 text-gray-600 rounded-lg">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('customers.statement', $customer->id) }}"
                               class="p-2 bg-purple-100 text-purple-600 rounded-lg">
                                <i class="fas fa-file-invoice"></i>
                            </a>
                            {{-- زر الحذف --}}
                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا العميل؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-red-100 text-red-600 rounded-lg">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-6 text-gray-500">
                        لا يوجد عملاء
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="border-t px-6 py-4">
        {{ $customers->links() }}
    </div>
</div>
@endsection
