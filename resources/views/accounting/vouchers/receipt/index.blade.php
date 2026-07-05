@extends('layouts.app')

@section('title', 'سندات القبض')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">سندات القبض (Receipt Vouchers)</h2>
            <p class="text-gray-600 mt-1">عرض وإصدار إيصالات استلام النقدية أو الشيكات والإيداعات البنكية</p>
        </div>
        <a href="{{ route('accounting.vouchers.receipt.create') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all shadow hover:shadow-md flex items-center gap-1.5">
            <i class="fas fa-plus-circle"></i> إنشاء سند قبض جديد
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">رقم السند / القيد</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">التاريخ</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">البيان</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">المرجع</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">المبلغ</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-mono">
                    @forelse($vouchers as $voucher)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">
                                {{ $voucher->entry_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-sans">
                                {{ $voucher->entry_date->toDateString() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800 font-sans max-w-sm truncate">
                                {{ $voucher->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-sans">
                                {{ $voucher->reference ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-green-600">
                                {{ number_format($voucher->total_debit, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-sans flex items-center justify-center gap-3">
                                <a href="{{ route('accounting.vouchers.receipt.show', $voucher->id) }}" class="text-blue-600 hover:text-blue-900 font-medium">عرض</a>
                                <span class="text-gray-300">|</span>
                                <a href="{{ route('accounting.vouchers.receipt.print', $voucher->id) }}" target="_blank" class="text-gray-600 hover:text-gray-900 font-medium"><i class="fas fa-print"></i> طباعة</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500 font-sans">لا توجد سندات قبض مسجلة بعد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($vouchers->hasPages())
            <div class="p-6 bg-gray-50 border-t border-gray-100">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
