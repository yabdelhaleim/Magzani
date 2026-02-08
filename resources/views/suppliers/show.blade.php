@extends('layouts.app')

@section('title', 'تفاصيل المورد')
@section('page-title', 'تفاصيل المورد')

@section('content')
<div class="space-y-6" x-data="{ showPaymentModal: false }">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-600">
        <a href="{{ route('suppliers.index') }}" class="hover:text-blue-600">الموردين</a>
        <i class="fas fa-chevron-left text-xs"></i>
        <span class="text-gray-900 font-medium">{{ $supplier->name }}</span>
    </nav>

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg p-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-3xl font-bold">
                    {{ substr($supplier->name, 0, 2) }}
                </div>
                <div>
                    <h2 class="text-3xl font-bold mb-2">{{ $supplier->name }}</h2>
                    <div class="flex items-center gap-4 text-sm text-blue-100">
                        <span><i class="fas fa-hashtag ml-1"></i>{{ $supplier->id }}</span>
                        <span><i class="fas fa-calendar ml-1"></i>{{ $supplier->created_at->format('Y-m-d') }}</span>
                        @if($supplier->is_active ?? true)
                            <span class="px-3 py-1 bg-green-500 rounded-full"><i class="fas fa-check-circle ml-1"></i>نشط</span>
                        @else
                            <span class="px-3 py-1 bg-gray-500 rounded-full"><i class="fas fa-pause-circle ml-1"></i>غير نشط</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('suppliers.edit', $supplier->id) }}" class="px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg">
                    <i class="fas fa-edit ml-2"></i>تعديل
                </a>
                <a href="{{ route('suppliers.statement', $supplier->id) }}" class="px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg">
                    <i class="fas fa-file-invoice ml-2"></i>كشف الحساب
                </a>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-600">إجمالي المشتريات</p>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-purple-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-purple-600">{{ number_format($summary['total_purchases'] ?? 0, 2) }}</h3>
            <p class="text-xs text-gray-500 mt-1">جنيه مصري</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-600">إجمالي المدفوعات</p>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-green-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-green-600">{{ number_format($summary['total_paid'] ?? 0, 2) }}</h3>
            <p class="text-xs text-gray-500 mt-1">جنيه مصري</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border-2 border-red-200">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-600">الرصيد المستحق</p>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-wallet text-red-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-red-600">{{ number_format($summary['balance'] ?? 0, 2) }}</h3>
            <p class="text-xs text-gray-500 mt-1">جنيه مصري</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-600">عدد الفواتير</p>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-invoice text-blue-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-blue-600">{{ $supplier->purchaseInvoices()->count() }}</h3>
            <p class="text-xs text-gray-500 mt-1">فاتورة</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Contact Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-address-card text-blue-600"></i>
                معلومات الاتصال
            </h3>
            <div class="space-y-4">
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    <i class="fas fa-phone text-blue-600"></i>
                    <div><p class="text-xs text-gray-500">الهاتف</p><p class="font-semibold">{{ $supplier->phone ?? 'غير متوفر' }}</p></div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    <i class="fas fa-envelope text-blue-600"></i>
                    <div><p class="text-xs text-gray-500">البريد</p><p class="font-semibold">{{ $supplier->email ?? 'غير متوفر' }}</p></div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                    <i class="fas fa-map-marker-alt text-blue-600 mt-1"></i>
                    <div><p class="text-xs text-gray-500">العنوان</p><p class="font-semibold">{{ $supplier->address ?? 'غير متوفر' }}</p></div>
                </div>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle text-purple-600"></i>
                معلومات إضافية
            </h3>
            <div class="space-y-4">
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">تاريخ الإضافة</p>
                    <p class="font-semibold">{{ $supplier->created_at->format('Y-m-d h:i A') }}</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">آخر تحديث</p>
                    <p class="font-semibold">{{ $supplier->updated_at->format('Y-m-d h:i A') }}</p>
                </div>
                @if($supplier->notes)
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">ملاحظات</p>
                    <p>{{ $supplier->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Invoices & Payments -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Invoices -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b flex justify-between items-center">
                <h3 class="font-bold"><i class="fas fa-file-invoice text-purple-600 ml-2"></i>أحدث الفواتير</h3>
                <a href="{{ route('suppliers.statement', $supplier->id) }}" class="text-sm text-blue-600 hover:underline">عرض الكل</a>
            </div>
            <div class="divide-y">
                @forelse($recentInvoices as $invoice)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-semibold">فاتورة #{{ $invoice->id }}</p>
                            <p class="text-xs text-gray-500"><i class="fas fa-calendar ml-1"></i>{{ $invoice->invoice_date }}</p>
                        </div>
                        <p class="text-lg font-bold text-purple-600">{{ number_format($invoice->total, 2) }} ج.م</p>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400"><i class="fas fa-inbox text-4xl mb-2 block"></i>لا توجد فواتير</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b flex justify-between items-center">
                <h3 class="font-bold"><i class="fas fa-money-bill-wave text-green-600 ml-2"></i>أحدث المدفوعات</h3>
                <button @click="showPaymentModal = true" class="px-3 py-1 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
                    <i class="fas fa-plus ml-1"></i>إضافة سداد
                </button>
            </div>
            <div class="divide-y">
                @forelse($recentPayments as $payment)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-semibold">سداد #{{ $payment->id }}</p>
                            <p class="text-xs text-gray-500"><i class="fas fa-calendar ml-1"></i>{{ $payment->payment_date }}</p>
                        </div>
                        <p class="text-lg font-bold text-green-600">{{ number_format($payment->amount, 2) }} ج.م</p>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400"><i class="fas fa-inbox text-4xl mb-2 block"></i>لا توجد مدفوعات</div>
                @endforelse
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
                <div>
                    <label class="block text-sm font-semibold mb-2">تاريخ السداد *</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-2 border rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">المبلغ *</label>
                    <input type="number" name="amount" step="0.01" class="w-full px-4 py-2 border rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">طريقة الدفع</label>
                    <select name="method" class="w-full px-4 py-2 border rounded-lg">
                        <option value="نقدي">نقدي</option>
                        <option value="تحويل بنكي">تحويل بنكي</option>
                        <option value="شيك">شيك</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">ملاحظات</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 border rounded-lg"></textarea>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showPaymentModal = false" class="flex-1 px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">إلغاء</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"><i class="fas fa-save ml-2"></i>حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection