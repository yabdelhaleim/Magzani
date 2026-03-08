سأقوم بتحسين تصميم صفحة "مرتجعات المبيعات" لتكون متوافقة تماماً مع الهواتف والويب، مع التركيز على تجربة مستخدم سهلة للمتابعة.

**أبرز التعديلات:**
1.  **تخطيط الهيدر:** أصبح عمودياً في الهاتف (Title فوق Button) وأفقياً في الويب.
2.  **الإحصائيات:** تم تحسين تصميم البطاقات لتكون أكثر وضوحاً.
3.  **عرض البيانات:**
    *   في **الويب**: يظهر الجدول كاملاً مع تحسينات بسيطة في التصميم.
    *   في **الهاتف**: تختفي الجدول وتظهر **بطاقات (Cards)** أنيقة تحتوي على تفاصيل المرتجع بشكل مرتب وسهل القراءة.

إليك الكود:

```blade
@extends('layouts.app')

@section('title', 'مرتجعات المبيعات')
@section('page-title', 'مرتجعات المبيعات')

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8">

    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">

        <!-- Header -->
        <div class="bg-gradient-to-r from-red-500 to-pink-500 px-4 sm:px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-3">
            <h2 class="text-xl sm:text-2xl font-bold text-white">مرتجعات المبيعات</h2>
            <a href="{{ route('invoices.sales-returns.create') }}"
               class="w-full sm:w-auto px-5 py-2.5 bg-white text-red-600 rounded-lg hover:bg-red-50 font-semibold text-center transition shadow-sm text-sm">
                <i class="fas fa-plus ml-2"></i>
                إضافة مرتجع جديد
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-3 gap-4 bg-gray-50 border-b">
            <div class="bg-white p-4 rounded-xl border-r-4 border-red-500 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm text-gray-500 font-medium">إجمالي المرتجعات</p>
                        <p class="text-xl sm:text-2xl font-bold text-gray-800 mt-1">{{ $returns->total() }}</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-undo-alt text-red-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border-r-4 border-orange-500 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm text-gray-500 font-medium">قيمة المرتجعات</p>
                        <p class="text-xl sm:text-2xl font-bold text-gray-800 mt-1">
                            {{ number_format($returns->sum('total_amount'), 2) }}
                        </p>
                        <p class="text-xs text-gray-400">جنيه</p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-coins text-orange-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border-r-4 border-yellow-500 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm text-gray-500 font-medium">مرتجعات اليوم</p>
                        <p class="text-xl sm:text-2xl font-bold text-gray-800 mt-1">
                            {{ $returns->where('created_at', '>=', today())->count() }}
                        </p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-calendar-day text-yellow-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================== -->
        <!-- عرض الجدول (للأجهزة الكبيرة) -->
        <!-- ================== -->
        <div class="hidden md:block overflow-x-auto p-6">
            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">رقم المرتجع</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">رقم الفاتورة</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">التاريخ</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">العميل</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">القيمة</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">السبب</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase">إجراءات</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($returns as $return)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-red-600 text-sm">
                                {{ $return->code }}
                            </td>
                            <td class="px-6 py-4 text-blue-600 font-medium text-sm">
                                {{ $return->salesInvoice->invoice_number ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 text-sm">
                                {{ $return->created_at->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4 text-gray-900 font-medium text-sm">
                                {{ $return->salesInvoice->customer->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-900 text-sm">
                                {{ number_format($return->total_amount, 2) }} <span class="font-normal text-gray-400 text-xs">ج.م</span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-sm max-w-xs truncate" title="{{ $return->reason }}">
                                {{ $return->reason }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('invoices.sales-returns.show', $return->id) }}"
                                   class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-xs font-semibold transition">
                                    <i class="fas fa-eye"></i>
                                    عرض
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                                    <p class="font-medium">لا توجد مرتجعات مسجلة</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- ================== -->
        <!-- عرض البطاقات (للموبايل) -->
        <!-- ================== -->
        <div class="block md:hidden p-4 space-y-4">
            @forelse($returns as $return)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <!-- رأس البطاقة -->
                <div class="bg-gray-50 p-4 flex justify-between items-center border-b border-gray-100">
                    <div>
                        <span class="text-xs text-gray-500">رقم المرتجع</span>
                        <h3 class="font-bold text-red-600 text-lg">{{ $return->code }}</h3>
                    </div>
                    <div class="text-left">
                        <span class="text-xs text-gray-500">فاتورة رقم</span>
                        <p class="font-semibold text-blue-600">{{ $return->salesInvoice->invoice_number ?? '-' }}</p>
                    </div>
                </div>
                
                <!-- تفاصيل البطاقة -->
                <div class="p-4 space-y-3 text-sm">
                    <div class="flex items-center gap-2 text-gray-600">
                        <i class="fas fa-user text-gray-400 w-4"></i>
                        <span class="font-medium text-gray-800">{{ $return->salesInvoice->customer->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <i class="fas fa-calendar text-gray-400 w-4"></i>
                        <span>{{ $return->created_at->format('Y-m-d') }}</span>
                    </div>
                    
                    <div class="pt-3 border-t border-dashed flex justify-between items-center">
                        <span class="text-gray-600">القيمة:</span>
                        <span class="font-bold text-lg text-gray-900">{{ number_format($return->total_amount, 2) }} ج.م</span>
                    </div>
                </div>

                <!-- ذيل البطاقة -->
                <div class="px-4 pb-4">
                    <a href="{{ route('invoices.sales-returns.show', $return->id) }}"
                       class="w-full block text-center bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg font-semibold text-sm transition shadow-sm">
                        <i class="fas fa-eye ml-1"></i> عرض التفاصيل
                    </a>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                <p class="font-medium">لا توجد مرتجعات</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($returns->hasPages())
        <div class="p-4 sm:px-6 border-t bg-gray-50">
            {{ $returns->links() }}
        </div>
        @endif

    </div>
</div>
@endsection
```