سأقوم بتحسين تصميم صفحة "فواتير المشتريات" لتكون متجاوبة بالكامل وسهلة الاستخدام على الهواتف والويب.

**أبرز التعديلات:**
1.  **عرض بطاقات للموبايل:** إخفاء الجدول في الشاشات الصغيرة واستبداله ببطاقات (Cards) أنيقة تعرض المعلومات بشكل عمودي واضح.
2.  **تحسين الفلاتر:** جعل الفلاتر تظهر بشكل عمودي في الموبايل، ثم تتحول لشكل أفقي في الشاشات الكبيرة.
3.  **تحسين الإحصائيات:** تصغير النصوص قليلاً في الموبايل وجعلها شبكة من عمودين (2x2) لتوفير المساحة.
4.  **تحسين الأزرار:** في الموبايل، تظهر الأزرار كأزرار كاملة العرض (Full Width) لتسهيل اللمس، مع تقليل عدد الأيقونات الظاهرة ووضعها في قائمة منسدلة أو تجميعها بشكل ذكي.

إليك الكود المحدث:

```blade
@extends('layouts.app')

@section('title', 'فواتير المشتريات')

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8">

    {{-- رسائل النجاح والخطأ --}}
    @if(session('success'))
        <div class="bg-green-50 border-r-4 border-green-500 text-green-800 px-4 py-3 rounded-lg mb-4 shadow-sm flex items-center gap-2" role="alert">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-r-4 border-red-500 text-red-800 px-4 py-3 rounded-lg mb-4 shadow-sm flex items-center gap-2" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-600 to-pink-500 px-4 sm:px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-3">
            <h2 class="text-xl sm:text-2xl font-bold text-white">فواتير المشتريات</h2>
            <a href="{{ route('invoices.purchases.create') }}" 
               class="w-full sm:w-auto px-5 py-2.5 bg-white text-purple-600 rounded-lg hover:bg-purple-50 transition font-semibold text-center text-sm shadow-sm">
                <i class="fas fa-plus ml-2"></i>
                إضافة فاتورة جديدة
            </a>
        </div>

        <div class="p-4 sm:p-6">
            {{-- Statistics Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 p-3 sm:p-4 rounded-lg border-r-4 border-blue-500 shadow-sm">
                    <p class="text-[10px] sm:text-sm text-gray-600 font-medium">إجمالي الفواتير</p>
                    <p class="text-lg sm:text-2xl font-bold text-gray-900 mt-1">{{ $statistics['total_invoices'] }}</p>
                </div>
                
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-3 sm:p-4 rounded-lg border-r-4 border-purple-500 shadow-sm">
                    <p class="text-[10px] sm:text-sm text-gray-600 font-medium">إجمالي المشتريات</p>
                    <p class="text-lg sm:text-2xl font-bold text-gray-900 mt-1">{{ number_format($statistics['total_amount']) }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-500">جنيه</p>
                </div>
                
                <div class="bg-gradient-to-br from-red-50 to-orange-50 p-3 sm:p-4 rounded-lg border-r-4 border-red-500 shadow-sm">
                    <p class="text-[10px] sm:text-sm text-gray-600 font-medium">فواتير معلقة</p>
                    <p class="text-lg sm:text-2xl font-bold text-gray-900 mt-1">{{ $statistics['pending_invoices'] }}</p>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-3 sm:p-4 rounded-lg border-r-4 border-green-500 shadow-sm">
                    <p class="text-[10px] sm:text-sm text-gray-600 font-medium">مشتريات اليوم</p>
                    <p class="text-lg sm:text-2xl font-bold text-gray-900 mt-1">{{ number_format($statistics['today_amount']) }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-500">جنيه</p>
                </div>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('invoices.purchases.index') }}" class="mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                    
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">بحث</label>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="رقم الفاتورة..."
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">من تاريخ</label>
                        <input type="date" 
                               name="date_from" 
                               value="{{ request('date_from') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">إلى تاريخ</label>
                        <input type="date" 
                               name="date_to" 
                               value="{{ request('date_to') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">الحالة</label>
                        <select name="status" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                            <option value="">الكل</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلقة</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" 
                                class="flex-1 px-4 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium shadow-sm">
                            <i class="fas fa-search ml-1"></i>
                            بحث
                        </button>
                        <a href="{{ route('invoices.purchases.index') }}" 
                           class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
                
                <div class="mt-3 flex justify-end">
                    <a href="{{ route('invoices.purchases.export', request()->all()) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium shadow-sm">
                        <i class="fas fa-file-excel"></i>
                        تصدير Excel
                    </a>
                </div>
            </form>

            {{-- ================== --}}
            {{-- عرض الجدول (للأجهزة الكبيرة) --}}
            {{-- ================== --}}
            <div class="hidden md:block overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">رقم الفاتورة</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">التاريخ</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">المورد</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">المخزن</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">الإجمالي</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase">الحالة</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($invoices as $invoice)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm font-bold text-purple-600">
                                    {{ $invoice->invoice_number }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $invoice->invoice_date->format('Y-m-d') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                    {{ $invoice->supplier->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $invoice->warehouse->name }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                    {{ number_format($invoice->total, 2) }} <span class="text-xs font-normal text-gray-500">ج.م</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($invoice->status == 'paid')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">مدفوعة</span>
                                    @elseif($invoice->status == 'pending')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">معلقة</span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">ملغاة</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('invoices.purchases.show', $invoice->id) }}" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition" title="عرض"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('invoices.purchases.edit', $invoice->id) }}" class="p-2 text-green-600 hover:bg-green-100 rounded-lg transition" title="تعديل"><i class="fas fa-edit"></i></a>
                                        <a href="{{ route('invoices.purchases.print', $invoice->id) }}" target="_blank" class="p-2 text-purple-600 hover:bg-purple-100 rounded-lg transition" title="طباعة"><i class="fas fa-print"></i></a>
                                        <form action="{{ route('invoices.purchases.destroy', $invoice->id) }}" method="POST" class="inline" onsubmit="return confirm('هل أنت متأكد؟')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition" title="حذف"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                                    <p class="font-medium">لا توجد فواتير</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ================== --}}
            {{-- عرض البطاقات (للموبايل) --}}
            {{-- ================== --}}
            <div class="block md:hidden space-y-4">
                @forelse($invoices as $invoice)
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="bg-gray-50 p-4 flex justify-between items-center border-b border-gray-100">
                        <div>
                            <span class="font-bold text-purple-600 text-lg">{{ $invoice->invoice_number }}</span>
                            <div class="text-xs text-gray-500 mt-1">{{ $invoice->invoice_date->format('Y-m-d') }}</div>
                        </div>
                        <div>
                            @if($invoice->status == 'paid')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">مدفوعة</span>
                            @elseif($invoice->status == 'pending')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">معلقة</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">ملغاة</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="p-4 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">المورد:</span>
                            <span class="font-semibold text-gray-800">{{ $invoice->supplier->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">المخزن:</span>
                            <span class="font-medium text-gray-700">{{ $invoice->warehouse->name }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-dashed">
                            <span class="text-gray-600 font-medium">الإجمالي:</span>
                            <span class="font-bold text-base text-gray-900">{{ number_format($invoice->total, 2) }} ج.م</span>
                        </div>
                    </div>

                    <div class="px-4 pb-4 flex gap-2">
                        <a href="{{ route('invoices.purchases.show', $invoice->id) }}" class="flex-1 text-center bg-blue-600 text-white py-2 rounded-lg text-xs font-semibold hover:bg-blue-700">
                            <i class="fas fa-eye ml-1"></i> عرض
                        </a>
                        <a href="{{ route('invoices.purchases.edit', $invoice->id) }}" class="flex-1 text-center bg-gray-100 text-gray-700 py-2 rounded-lg text-xs font-semibold hover:bg-gray-200">
                            <i class="fas fa-edit ml-1"></i> تعديل
                        </a>
                        <a href="{{ route('invoices.purchases.print', $invoice->id) }}" target="_blank" class="bg-purple-100 text-purple-700 py-2 px-3 rounded-lg text-xs font-semibold hover:bg-purple-200">
                            <i class="fas fa-print"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                    <p>لا توجد فواتير</p>
                </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
```