@extends('layouts.app')

@section('title', 'المقبوضات والمدفوعات')
@section('page-title', 'المقبوضات والمدفوعات')

@section('content')
<div class="space-y-6">
    
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl p-8 text-white shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full opacity-10">
            <div class="absolute top-10 right-10 w-32 h-32 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 left-10 w-40 h-40 bg-white rounded-full blur-3xl"></div>
        </div>
        <div class="relative z-10">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-4xl font-bold mb-2">المقبوضات والمدفوعات</h2>
                    <p class="text-white/80 text-lg">إدارة شاملة لجميع الحركات المالية</p>
                </div>
                <div class="hidden md:flex items-center gap-4">
                    <button onclick="openModal('depositModal')" class="bg-white/20 hover:bg-white/30 backdrop-blur-lg px-6 py-3 rounded-xl font-bold transition hover-scale flex items-center gap-2 border border-white/30">
                        <i class="fas fa-plus-circle"></i>
                        إيداع جديد
                    </button>
                    <button onclick="openModal('withdrawalModal')" class="bg-white/20 hover:bg-white/30 backdrop-blur-lg px-6 py-3 rounded-xl font-bold transition hover-scale flex items-center gap-2 border border-white/30">
                        <i class="fas fa-minus-circle"></i>
                        سحب جديد
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Cash Balance -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-wallet text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full">نقدي</span>
            </div>
            <h3 class="text-gray-500 text-sm font-semibold mb-2">رصيد الخزينة</h3>
            <p class="text-3xl font-bold text-gray-800" dir="ltr" style="text-align: right;">{{ number_format($cashBalance, 2) }} <span class="text-lg text-gray-500">ج.م</span></p>
        </div>

        <!-- Bank Balance -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-university text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-3 py-1 rounded-full">بنكي</span>
            </div>
            <h3 class="text-gray-500 text-sm font-semibold mb-2">الرصيد البنكي</h3>
            <p class="text-3xl font-bold text-gray-800" dir="ltr" style="text-align: right;">{{ number_format($bankBalance, 2) }} <span class="text-lg text-gray-500">ج.م</span></p>
        </div>

        <!-- Total Liquidity -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-coins text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-purple-600 bg-purple-100 px-3 py-1 rounded-full">إجمالي</span>
            </div>
            <h3 class="text-gray-500 text-sm font-semibold mb-2">إجمالي السيولة</h3>
            <p class="text-3xl font-bold text-gray-800" dir="ltr" style="text-align: right;">{{ number_format($totalLiquidity, 2) }} <span class="text-lg text-gray-500">ج.م</span></p>
        </div>

        <!-- Today Transactions -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-chart-line text-2xl text-white"></i>
                </div>
                <span class="text-xs font-semibold text-orange-600 bg-orange-100 px-3 py-1 rounded-full">اليوم</span>
            </div>
            <h3 class="text-gray-500 text-sm font-semibold mb-2">حركات اليوم</h3>
            <p class="text-3xl font-bold text-gray-800">{{ $todayTransactions->count() }} <span class="text-lg text-gray-500">حركة</span></p>
        </div>
    </div>

    <!-- Mobile Action Buttons -->
    <div class="md:hidden grid grid-cols-2 gap-3">
        <button onclick="openModal('depositModal')" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2">
            <i class="fas fa-plus-circle"></i>
            إيداع جديد
        </button>
        <button onclick="openModal('withdrawalModal')" class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2">
            <i class="fas fa-minus-circle"></i>
            سحب جديد
        </button>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-filter text-indigo-600"></i>
            تصفية وبحث
        </h3>
        <form method="GET" action="{{ route('accounting.payments') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            
            <!-- Type Filter -->
            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-list ml-1 text-gray-400"></i>
                    نوع الحركة
                </label>
                <select name="type" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    <option value="">الكل</option>
                    <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>إيداع</option>
                    <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>سحب</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-tag ml-1 text-gray-400"></i>
                    التصنيف
                </label>
                <select name="category" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    <option value="">الكل</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Start Date -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt ml-1 text-gray-400"></i>
                    من تاريخ
                </label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
            </div>

            <!-- End Date -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar-check ml-1 text-gray-400"></i>
                    إلى تاريخ
                </label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
            </div>

            <!-- Buttons -->
            <div class="md:col-span-2 flex items-end gap-2">
                <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-6 py-2.5 rounded-lg font-semibold transition hover-scale shadow-md">
                    <i class="fas fa-search ml-2"></i>
                    بحث
                </button>
                <a href="{{ route('accounting.payments') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg transition text-center">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-list-alt"></i>
                سجل الحركات المالية
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-hashtag ml-1"></i>
                            الرقم
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-calendar ml-1"></i>
                            التاريخ والوقت
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-tag ml-1"></i>
                            النوع
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-align-right ml-1"></i>
                            الوصف
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-folder ml-1"></i>
                            التصنيف
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-arrow-down ml-1 text-green-500"></i>
                            وارد
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-arrow-up ml-1 text-red-500"></i>
                            صادر
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                            الإجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-br from-gray-100 to-gray-200 text-gray-700 rounded-lg text-sm font-bold shadow-sm">
                                    {{ $transaction->id }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-clock text-gray-400"></i>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $transaction->transaction_date->format('d/m/Y') }}</p>
                                        <p class="text-xs text-gray-500">{{ $transaction->created_at->format('h:i A') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($transaction->transaction_type == 'deposit')
                                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm font-bold">
                                        <i class="fas fa-arrow-down"></i>
                                        إيداع
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 px-4 py-2 rounded-full text-sm font-bold">
                                        <i class="fas fa-arrow-up"></i>
                                        سحب
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-700 max-w-xs truncate" title="{{ $transaction->description }}">
                                    {{ $transaction->description ?? 'بدون وصف' }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                @if($transaction->category)
                                    <span class="inline-flex items-center gap-1 bg-indigo-100 text-indigo-700 px-3 py-1 rounded-lg text-xs font-semibold">
                                        <i class="fas fa-tag"></i>
                                        {{ $transaction->category }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($transaction->transaction_type == 'deposit')
                                    <span class="text-green-600 font-bold text-lg" dir="ltr">
                                        + {{ number_format($transaction->amount, 2) }} ج.م
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($transaction->transaction_type == 'withdrawal')
                                    <span class="text-red-600 font-bold text-lg" dir="ltr">
                                        - {{ number_format($transaction->amount, 2) }} ج.م
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editTransaction({{ $transaction->id }})" class="bg-blue-100 hover:bg-blue-200 text-blue-600 w-9 h-9 rounded-lg transition flex items-center justify-center" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('accounting.transactions.destroy', $transaction->id) }}" method="POST" class="inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-600 w-9 h-9 rounded-lg transition flex items-center justify-center" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-inbox text-5xl text-gray-300"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-800 mb-2">لا توجد حركات مالية</h3>
                                    <p class="text-gray-500">لم يتم تسجيل أي حركات مالية بعد</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        عرض <span class="font-semibold text-gray-800">{{ $transactions->firstItem() ?? 0 }}</span>
                        إلى <span class="font-semibold text-gray-800">{{ $transactions->lastItem() ?? 0 }}</span>
                        من أصل <span class="font-semibold text-gray-800">{{ $transactions->total() }}</span> حركة
                    </div>
                    <div>
                        {{ $transactions->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Deposit Modal -->
<div id="depositModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4 flex items-center justify-between rounded-t-2xl">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-plus-circle"></i>
                إيداع جديد
            </h3>
            <button onclick="closeModal('depositModal')" class="text-white hover:bg-white/20 w-8 h-8 rounded-lg transition flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('accounting.deposits.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">المبلغ *</label>
                <input type="number" name="amount" step="0.01" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">الوصف</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">التصنيف</label>
                <input type="text" name="category" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">التاريخ</label>
                <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-3 rounded-lg font-bold shadow-lg transition">
                    <i class="fas fa-check ml-2"></i>
                    حفظ الإيداع
                </button>
                <button type="button" onclick="closeModal('depositModal')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold transition">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Withdrawal Modal -->
<div id="withdrawalModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4 flex items-center justify-between rounded-t-2xl">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-minus-circle"></i>
                سحب جديد
            </h3>
            <button onclick="closeModal('withdrawalModal')" class="text-white hover:bg-white/20 w-8 h-8 rounded-lg transition flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('accounting.withdrawals.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">المبلغ *</label>
                <input type="number" name="amount" step="0.01" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">الوصف</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">التصنيف</label>
                <input type="text" name="category" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">التاريخ</label>
                <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-6 py-3 rounded-lg font-bold shadow-lg transition">
                    <i class="fas fa-check ml-2"></i>
                    حفظ السحب
                </button>
                <button type="button" onclick="closeModal('withdrawalModal')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold transition">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('depositModal');
        closeModal('withdrawalModal');
    }
});

function editTransaction(id) {
    // Implement edit functionality
    alert('تعديل الحركة رقم: ' + id);
}
</script>
@endpush

@endsection