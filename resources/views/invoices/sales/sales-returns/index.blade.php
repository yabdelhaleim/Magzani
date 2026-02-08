@extends('layouts.app')

@section('title', 'مرتجعات المبيعات')
@section('page-title', 'مرتجعات المبيعات')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow overflow-hidden">

        <!-- Header -->
        <div class="bg-gradient-to-r from-red-500 to-pink-500 px-6 py-4 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">مرتجعات المبيعات</h2>
            <a href="{{ route('invoices.sales-returns.create') }}"
               class="px-4 py-2 bg-white text-red-600 rounded-lg hover:bg-red-50 font-semibold">
                + إضافة مرتجع جديد
            </a>
        </div>

        <!-- Summary -->
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-red-50 p-4 rounded-lg border-r-4 border-red-500">
                <p class="text-sm text-gray-600">إجمالي المرتجعات</p>
                <p class="text-2xl font-bold">{{ $returns->total() }}</p>
            </div>

            <div class="bg-orange-50 p-4 rounded-lg border-r-4 border-orange-500">
                <p class="text-sm text-gray-600">قيمة المرتجعات</p>
                <p class="text-2xl font-bold">
                    {{ number_format($returns->sum('total_amount'), 2) }}
                </p>
                <p class="text-xs text-gray-500">جنيه</p>
            </div>

            <div class="bg-yellow-50 p-4 rounded-lg border-r-4 border-yellow-500">
                <p class="text-sm text-gray-600">مرتجعات اليوم</p>
                <p class="text-2xl font-bold">
                    {{ $returns->where('created_at', '>=', today())->count() }}
                </p>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto px-6 pb-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500">رقم المرتجع</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500">رقم الفاتورة</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500">التاريخ</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500">العميل</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500">القيمة</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500">السبب</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500">إجراءات</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($returns as $return)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-red-600">
                                {{ $return->code }}
                            </td>
                            <td class="px-4 py-3 text-blue-600">
                                {{ $return->salesInvoice->invoice_number ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $return->created_at->format('Y-m-d') }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $return->salesInvoice->customer->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 font-bold">
                                {{ number_format($return->total_amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $return->reason }}
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('invoices.sales-returns.show', $return->id) }}"
                                   class="text-blue-600 hover:underline">
                                    عرض
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-gray-500">
                                لا توجد مرتجعات
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $returns->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
