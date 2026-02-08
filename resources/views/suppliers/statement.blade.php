@extends('layouts.app')

@section('title', 'كشف حساب المورد')
@section('page-title', 'كشف حساب المورد')

@section('content')
<div class="space-y-6" x-data="{ showPaymentModal: false }">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-600">
        <a href="{{ route('suppliers.index') }}" class="hover:text-blue-600">الموردين</a>
        <i class="fas fa-chevron-left text-xs"></i>
        <a href="{{ route('suppliers.show', $supplier->id) }}" class="hover:text-blue-600">{{ $supplier->name }}</a>
        <i class="fas fa-chevron-left text-xs"></i>
        <span class="text-gray-900 font-medium">كشف الحساب</span>
    </nav>

    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl shadow-lg p-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <i class="fas fa-file-invoice text-5xl opacity-75"></i>
                <div>
                    <h2 class="text-3xl font-bold mb-1">كشف حساب المورد</h2>
                    <p class="text-purple-100">{{ $supplier->name }} - #{{ $supplier->id }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg">
                    <i class="fas fa-print ml-2"></i>طباعة
                </button>
                <button class="px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg">
                    <i class="fas fa-file-excel ml-2"></i>تصدير
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 text-center">
            <div class="w-16 h-16 bg-purple-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                <i class="fas fa-shopping-cart text-2xl text-purple-600"></i>
            </div>
            <p class="text-sm text-gray-600 mb-2">إجمالي المشتريات (مدين)</p>
            <h3 class="text-3xl font-bold text-purple-600">{{ number_format($summary['total_purchases'] ?? 0, 2) }}</h3>
            <p class="text-xs text-gray-500 mt-1">جنيه مصري</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-2xl text-green-600"></i>
            </div>
            <p class="text-sm text-gray-600 mb-2">إجمالي المدفوعات (دائن)</p>
            <h3 class="text-3xl font-bold text-green-600">{{ number_format($summary['total_paid'] ?? 0, 2) }}</h3>
            <p class="text-xs text-gray-500 mt-1">جنيه مصري</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border-2 border-red-200 text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                <i class="fas fa-wallet text-2xl text-red-600"></i>
            </div>
            <p class="text-sm text-gray-600 mb-2">الرصيد المستحق</p>
            <h3 class="text-3xl font-bold text-red-600">{{ number_format($summary['balance'] ?? 0, 2) }}</h3>
            <p class="text-xs text-gray-500 mt-1">جنيه مصري</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('suppliers.statement', $supplier->id) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">من تاريخ</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">نوع الحركة</label>
                <select name="type" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">الكل</option>
                    <option value="invoice" {{ request('type') == 'invoice' ? 'selected' : '' }}>فواتير فقط</option>
                    <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>سداد فقط</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-filter ml-2"></i>تطبيق
                </button>
            </div>
        </form>
    </div>

    <!-- Statement Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b bg-gray-50">
            <h3 class="font-bold text-lg"><i class="fas fa-list text-purple-600 ml-2"></i>حركات الحساب</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">التاريخ</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">المستند</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600">البيان</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">مدين</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">دائن</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">الرصيد</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @php 
                        $runningBalance = 0;
                        $totalDebit = 0;
                        $totalCredit = 0;
                    @endphp
                    
                    @forelse($statement as $transaction)
                        @php
                            $runningBalance += ($transaction->debit ?? 0) - ($transaction->credit ?? 0);
                            $totalDebit += $transaction->debit ?? 0;
                            $totalCredit += $transaction->credit ?? 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm">
                                <i class="fas fa-calendar text-gray-400 ml-2"></i>{{ $transaction->date }}
                            </td>
                            <td class="px-6 py-4"><span class="px-2 py-1 bg-gray-100 rounded text-xs">#{{ $transaction->id ?? 'N/A' }}</span></td>
                            <td class="px-6 py-4">
                                @if($transaction->type == 'فاتورة شراء')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                                        <i class="fas fa-shopping-cart"></i>{{ $transaction->type }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                        <i class="fas fa-money-bill-wave"></i>{{ $transaction->type }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($transaction->debit > 0)
                                    <span class="text-lg font-bold text-red-600">{{ number_format($transaction->debit, 2) }}</span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($transaction->credit > 0)
                                    <span class="text-lg font-bold text-green-600">{{ number_format($transaction->credit, 2) }}</span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-lg font-bold {{ $runningBalance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($runningBalance, 2) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <i class="fas fa-inbox text-6xl text-gray-300 mb-4 block"></i>
                                <p class="text-gray-500">لا توجد حركات في الفترة المحددة</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                
                @if($statement->count() > 0)
                <tfoot class="bg-gray-50 border-t-2">
                    <tr>
                        <td colspan="3" class="px-6 py-4"><strong class="text-lg">الإجمالي</strong></td>
                        <td class="px-6 py-4 text-center"><strong class="text-lg text-red-600">{{ number_format($totalDebit, 2) }}</strong></td>
                        <td class="px-6 py-4 text-center"><strong class="text-lg text-green-600">{{ number_format($totalCredit, 2) }}</strong></td>
                        <td class="px-6 py-4 text-center">
                            <strong class="text-lg {{ $runningBalance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($runningBalance, 2) }}
                            </strong>
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold text-lg mb-1"><i class="fas fa-bolt text-yellow-500 ml-2"></i>إجراءات سريعة</h4>
                <p class="text-sm text-gray-600">قم بإجراء عملية سريعة للمورد</p>
            </div>
            <div class="flex gap-3">
                <button @click="showPaymentModal = true" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow-lg">
                    <i class="fas fa-plus ml-2"></i>إضافة سداد
                </button>
                <a href="{{ route('suppliers.show', $supplier->id) }}" class="px-6 py-3 bg-white text-gray-700 rounded-lg hover:bg-gray-50 shadow">
                    <i class="fas fa-eye ml-2"></i>التفاصيل
                </a>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div x-show="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" x-cloak>
        <div @click.away="showPaymentModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold"><i class="fas fa-plus-circle text-green-600 ml-2"></i>إضافة سداد جديد</h3>
                <button @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
                <div class="p-4 bg-blue-50 rounded-lg text-sm"><strong>الرصيد الحالي:</strong> {{ number_format($summary['balance'] ?? 0, 2) }} ج.م</div>
                <div><label class="block text-sm font-semibold mb-2">تاريخ السداد *</label><input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-2 border rounded-lg" required></div>
                <div><label class="block text-sm font-semibold mb-2">المبلغ *</label><input type="number" name="amount" step="0.01" class="w-full px-4 py-2 border rounded-lg" required></div>
                <div><label class="block text-sm font-semibold mb-2">طريقة الدفع</label><select name="method" class="w-full px-4 py-2 border rounded-lg"><option value="نقدي">نقدي</option><option value="تحويل بنكي">تحويل بنكي</option><option value="شيك">شيك</option></select></div>
                <div><label class="block text-sm font-semibold mb-2">ملاحظات</label><textarea name="notes" rows="2" class="w-full px-4 py-2 border rounded-lg"></textarea></div>
                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showPaymentModal = false" class="flex-1 px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">إلغاء</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"><i class="fas fa-save ml-2"></i>حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection