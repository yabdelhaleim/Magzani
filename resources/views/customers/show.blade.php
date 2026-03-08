@extends('layouts.app')

@section('title', 'تفاصيل العميل')
@section('page-title', 'تفاصيل العميل')

@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
    <a href="{{ route('customers.index') }}" class="hover:text-indigo-600 transition-colors">العملاء</a>
    <i class="fas fa-chevron-left text-xs"></i>
    <span class="text-gray-800 font-medium">{{ $customer->name }}</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ===== LEFT: Profile Card ===== --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Cover --}}
            <div class="h-24 bg-gradient-to-l from-indigo-500 to-violet-600 relative">
                <div class="absolute inset-0 opacity-20"
                     style="background-image: url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\");"></div>
            </div>

            {{-- Avatar --}}
            <div class="px-6 pb-6">
                <div class="-mt-10 mb-4">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg ring-4 ring-white">
                        <span class="text-white text-2xl font-bold">{{ mb_substr($customer->name, 0, 1) }}</span>
                    </div>
                </div>

                <h2 class="text-xl font-bold text-gray-900">{{ $customer->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">عميل مسجل</p>

                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                        نشط
                    </span>
                    <span class="text-xs text-gray-400 mr-auto">
                        <i class="fas fa-calendar-alt ml-1"></i>
                        {{ $customer->created_at->format('Y/m/d') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mt-4 space-y-3">
            <h3 class="text-sm font-bold text-gray-700 mb-4">ملخص الحساب</h3>

            <div class="flex items-center justify-between py-2.5 border-b border-gray-50">
                <div class="flex items-center gap-2.5 text-sm text-gray-600">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <i class="fas fa-file-invoice text-blue-500 text-xs"></i>
                    </div>
                    إجمالي الفواتير
                </div>
                <span class="font-bold text-gray-800">{{ $customer->invoices_count ?? 0 }}</span>
            </div>

            <div class="flex items-center justify-between py-2.5 border-b border-gray-50">
                <div class="flex items-center gap-2.5 text-sm text-gray-600">
                    <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-green-500 text-xs"></i>
                    </div>
                    إجمالي المبيعات
                </div>
                <span class="font-bold text-gray-800">{{ number_format($customer->total_sales ?? 0, 2) }}</span>
            </div>

            <div class="flex items-center justify-between py-2.5">
                <div class="flex items-center gap-2.5 text-sm text-gray-600">
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                        <i class="fas fa-clock text-red-500 text-xs"></i>
                    </div>
                    المبالغ المستحقة
                </div>
                <span class="font-bold text-red-600">{{ number_format($customer->balance ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- ===== RIGHT: Details ===== --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Contact Info --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <i class="fas fa-address-card text-indigo-500 text-sm"></i>
                </div>
                <h3 class="font-bold text-gray-800">معلومات التواصل</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Phone --}}
                <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 hover:bg-indigo-50/50 transition-colors group">
                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-100 transition-colors">
                        <i class="fas fa-phone-alt text-indigo-500 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">رقم الهاتف</p>
                        <p class="font-semibold text-gray-800 text-sm">{{ $customer->phone ?? '—' }}</p>
                        @if($customer->phone)
                        <a href="tel:{{ $customer->phone }}" class="text-xs text-indigo-500 hover:underline mt-0.5 block">اتصال</a>
                        @endif
                    </div>
                </div>

                {{-- Email --}}
                <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 hover:bg-indigo-50/50 transition-colors group">
                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-100 transition-colors">
                        <i class="fas fa-envelope text-indigo-500 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">البريد الإلكتروني</p>
                        <p class="font-semibold text-gray-800 text-sm">{{ $customer->email ?? '—' }}</p>
                        @if($customer->email)
                        <a href="mailto:{{ $customer->email }}" class="text-xs text-indigo-500 hover:underline mt-0.5 block">إرسال بريد</a>
                        @endif
                    </div>
                </div>

                {{-- Address --}}
                <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 hover:bg-indigo-50/50 transition-colors group md:col-span-2">
                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-100 transition-colors">
                        <i class="fas fa-map-marker-alt text-indigo-500 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">العنوان</p>
                        <p class="font-semibold text-gray-800 text-sm">{{ $customer->address ?? '—' }}</p>
                    </div>
                </div>

                {{-- Registration Date --}}
                <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 hover:bg-indigo-50/50 transition-colors group">
                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-100 transition-colors">
                        <i class="fas fa-calendar-check text-indigo-500 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">تاريخ التسجيل</p>
                        <p class="font-semibold text-gray-800 text-sm">{{ $customer->created_at->format('Y/m/d') }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $customer->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                {{-- Last Update --}}
                <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 hover:bg-indigo-50/50 transition-colors group">
                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-100 transition-colors">
                        <i class="fas fa-history text-indigo-500 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">آخر تحديث</p>
                        <p class="font-semibold text-gray-800 text-sm">{{ $customer->updated_at->format('Y/m/d') }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $customer->updated_at->diffForHumans() }}</p>
                    </div>
                </div>

            </div>
        </div>

        {{-- Notes --}}
        @if($customer->notes)
        <div class="bg-amber-50 rounded-2xl border border-amber-100 p-5">
            <div class="flex items-center gap-2 mb-3">
                <i class="fas fa-sticky-note text-amber-500"></i>
                <h3 class="font-bold text-amber-800 text-sm">ملاحظات</h3>
            </div>
            <p class="text-sm text-amber-700 leading-relaxed">{{ $customer->notes }}</p>
        </div>
        @endif

        {{-- Actions --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-gray-700 mb-4">الإجراءات</h3>
            <div class="flex flex-wrap gap-3">

                <a href="{{ route('customers.edit', $customer->id) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition-all shadow-sm hover:shadow-indigo-200 hover:shadow-md">
                    <i class="fas fa-pen text-xs"></i>
                    تعديل البيانات
                </a>

                <a href="{{ route('invoices.sales.create', ['customer_id' => $customer->id]) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition-all shadow-sm hover:shadow-green-200 hover:shadow-md">
                    <i class="fas fa-file-invoice text-xs"></i>
                    فاتورة جديدة
                </a>

                <a href="{{ route('customers.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition-all">
                    <i class="fas fa-arrow-right text-xs"></i>
                    رجوع للقائمة
                </a>

                <button onclick="confirmDelete()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold transition-all mr-auto">
                    <i class="fas fa-trash text-xs"></i>
                    حذف العميل
                </button>
            </div>
        </div>

    </div>
</div>

{{-- Delete Confirm Modal --}}
<div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-trash text-red-500 text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">تأكيد الحذف</h3>
        <p class="text-sm text-gray-500 mb-6">هل أنت متأكد من حذف العميل <strong class="text-gray-800">{{ $customer->name }}</strong>؟ لا يمكن التراجع عن هذا الإجراء.</p>
        <div class="flex gap-3">
            <button onclick="closeModal()"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition-all">
                إلغاء
            </button>
            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-full px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition-all">
                    نعم، احذف
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete() {
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    function closeModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>
@endpush

@endsection