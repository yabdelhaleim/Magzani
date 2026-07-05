@extends('layouts.app')

@section('title', 'إنشاء سند صرف')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">إنشاء سند صرف جديد (New Payment Voucher)</h2>
            <p class="text-gray-600 mt-1">تسجيل مبالغ مدفوعة لموردين أو مصروفات نقدية أو تحويلات صادرة وتأثيرها على الحسابات</p>
        </div>
        <a href="{{ route('accounting.vouchers.payment.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors flex items-center gap-1.5">
            <i class="fas fa-arrow-right"></i> عودة للقائمة
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('accounting.vouchers.payment.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ السند *</label>
                    <input type="date" name="entry_date" required value="{{ old('entry_date', now()->toDateString()) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Reference -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">المرجع (رقم السند/الشيك/التحويل)</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" placeholder="رقم المرجع..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Debit Account -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">جهة الصرف / سبب الدفع (حساب مدين) *</label>
                    <select name="debit_account_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- اختر الحساب المدين المقابل --</option>
                        @foreach($debitAccounts as $acc)
                            <option value="{{ $acc->id }}" {{ old('debit_account_id') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Cash/Bank Account (Credit) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">طريقة الدفع (حساب دائن) *</label>
                    <select name="cash_account_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- اختر حساب الصندوق / البنك --</option>
                        @foreach($cashAccounts as $acc)
                            <option value="{{ $acc->id }}" {{ old('cash_account_id') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Amount -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">المبلغ المصروف *</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required value="{{ old('amount') }}" placeholder="0.00"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono font-bold text-lg text-center">
                </div>

                <!-- General Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">البيان والشرح *</label>
                    <textarea name="description" required rows="3" placeholder="اكتب بياناً تفصيلياً يوضح سبب إصدار سند الصرف..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description', 'سند صرف نقدي') }}</textarea>
                </div>
            </div>
        </div>

        <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all shadow hover:shadow-md">
                <i class="fas fa-check-circle ml-1"></i> حفظ وترحيل سند الصرف
            </button>
        </div>
    </form>
</div>
@endsection
