@extends('layouts.app')

@section('title', 'تفاصيل الجرد - ' . $stock_count->count_number)

@section('page-title', 'تفاصيل الجرد')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('stock-counts.index') }}" 
                   class="w-10 h-10 bg-white border-2 border-gray-200 hover:border-gray-300 rounded-xl flex items-center justify-center transition-all group">
                    <svg class="w-5 h-5 text-gray-600 group-hover:text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-4xl font-black text-gray-900">{{ $stock_count->count_number }}</h1>
                <span class="px-4 py-2 rounded-xl text-sm font-bold
                    {{ $stock_count->status == 'completed' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $stock_count->status == 'in_progress' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $stock_count->status == 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $stock_count->status == 'draft' ? 'bg-gray-100 text-gray-700' : '' }}">
                    {{ $stock_count->status_label }}
                </span>
            </div>
            <p class="text-gray-500">عرض تفاصيل عملية الجرد</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-2">
            @if($stock_count->status == 'draft')
            <form action="{{ route('stock-counts.start', $stock_count->id) }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-xl font-bold transition-all shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    بدء الجرد
                </button>
            </form>
            @endif

            @if($stock_count->status == 'in_progress')
            <a href="{{ route('stock-counts.count', $stock_count->id) }}" 
               class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-3 rounded-xl font-bold transition-all shadow-md hover:shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                متابعة الجرد
            </a>
            <form action="{{ route('stock-counts.complete', $stock_count->id) }}" 
                  method="POST" 
                  onsubmit="return confirm('هل أنت متأكد من إتمام الجرد؟')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl font-bold transition-all shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    إتمام الجرد
                </button>
            </form>
            @endif

            @if($stock_count->status == 'completed')
            <a href="{{ route('stock-counts.print', $stock_count->id) }}" 
               class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white px-5 py-3 rounded-xl font-bold transition-all shadow-md hover:shadow-lg" 
               target="_blank">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                طباعة
            </a>
            @endif

            @if(in_array($stock_count->status, ['draft', 'in_progress']))
            <form action="{{ route('stock-counts.cancel', $stock_count->id) }}" 
                  method="POST"
                  onsubmit="return confirm('هل أنت متأكد من إلغاء الجرد؟')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-5 py-3 rounded-xl font-bold transition-all shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    إلغاء
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info Card -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border-2 border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b-2 border-gray-100">
                <h2 class="text-lg font-black text-gray-900 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    معلومات الجرد
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm font-bold text-gray-500 mb-2 block">رقم الجرد</label>
                        <div class="text-xl font-black text-gray-900">{{ $stock_count->count_number }}</div>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-gray-500 mb-2 block">المخزن</label>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                            </svg>
                            <span class="text-lg font-bold text-gray-900">{{ $stock_count->warehouse->name }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-gray-500 mb-2 block">تاريخ الجرد</label>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-gray-900 font-semibold">{{ $stock_count->count_date->format('Y-m-d') }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-gray-500 mb-2 block">المنشئ</label>
                        @if($stock_count->creator)
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-bold text-sm">
                                    {{ substr($stock_count->creator->name, 0, 1) }}
                                </span>
                            </div>
                            <span class="text-gray-900 font-semibold">{{ $stock_count->creator->name }}</span>
                        </div>
                        @else
                        <span class="text-gray-400">غير محدد</span>
                        @endif
                    </div>
                    <div class="col-span-2">
                        <label class="text-sm font-bold text-gray-500 mb-2 block">ملاحظات</label>
                        <div class="bg-gray-50 rounded-xl p-4 text-gray-700 min-h-[60px]">
                            {{ $stock_count->notes ?: 'لا توجد ملاحظات' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 rounded-2xl shadow-sm border-2 border-blue-100 overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    إحصائيات الجرد
                </h3>
                
                <div class="space-y-5">
                    <!-- Total Items -->
                    <div class="bg-white/80 backdrop-blur rounded-xl p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-bold text-gray-700">إجمالي الأصناف</span>
                            <span class="text-2xl font-black text-blue-600">{{ $summary['total_items'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>

                    <!-- Counted Items -->
                    <div class="bg-white/80 backdrop-blur rounded-xl p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-bold text-gray-700">تم الجرد</span>
                            <span class="text-2xl font-black text-green-600">{{ $summary['items_counted'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full transition-all duration-500" style="width: {{ $summary['progress_percentage'] }}%"></div>
                        </div>
                    </div>

                    <!-- Variances -->
                    <div class="bg-white/80 backdrop-blur rounded-xl p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold text-gray-700">فروقات</span>
                            <span class="text-2xl font-black text-yellow-600">{{ $summary['items_with_variance'] }}</span>
                        </div>
                    </div>

                    <div class="border-t-2 border-white/50 pt-4">
                        <!-- Surplus -->
                        <div class="flex justify-between items-center py-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                </svg>
                                <span class="text-sm font-bold text-gray-700">فائض</span>
                            </div>
                            <span class="text-xl font-black text-green-600">+{{ number_format($summary['total_surplus'], 2) }}</span>
                        </div>

                        <!-- Shortage -->
                        <div class="flex justify-between items-center py-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                </svg>
                                <span class="text-sm font-bold text-gray-700">عجز</span>
                            </div>
                            <span class="text-xl font-black text-red-600">-{{ number_format($summary['total_shortage'], 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b-2 border-gray-100">
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                تفاصيل الأصناف ({{ $stock_count->items->count() }})
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b-2 border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-black text-gray-700 uppercase">#</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-gray-700 uppercase">المنتج</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-gray-700 uppercase">كمية النظام</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-gray-700 uppercase">الكمية الفعلية</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-gray-700 uppercase">الفرق</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-gray-700 uppercase">الحالة</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-gray-700 uppercase">ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($stock_count->items as $index => $item)
                    <tr class="hover:bg-gray-50 transition-colors {{ $item->has_variance ? 'bg-yellow-50/50' : '' }}">
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-lg text-sm font-bold text-gray-700">
                                {{ $index + 1 }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $item->product->name }}</div>
                            <div class="text-sm text-gray-500 font-medium">{{ $item->product->code }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-lg font-black text-gray-900">{{ number_format($item->system_quantity, 2) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($item->actual_quantity !== null)
                                <span class="text-lg font-black text-blue-600">{{ number_format($item->actual_quantity, 2) }}</span>
                            @else
                                <span class="text-gray-400 font-bold">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($item->variance != 0)
                                <div class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg {{ $item->variance > 0 ? 'bg-green-100' : 'bg-red-100' }}">
                                    <span class="font-black {{ $item->variance > 0 ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $item->variance > 0 ? '+' : '' }}{{ number_format($item->variance, 2) }}
                                    </span>
                                    <span class="text-xs font-bold {{ $item->variance > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $item->variance_label }}
                                    </span>
                                </div>
                            @else
                                <span class="text-gray-400 font-bold">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1.5 rounded-lg text-xs font-bold
                                {{ $item->status == 'adjusted' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $item->status == 'counted' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $item->status == 'pending' ? 'bg-gray-100 text-gray-700' : '' }}">
                                {{ $item->status == 'pending' ? 'معلق' : ($item->status == 'counted' ? 'تم الجرد' : 'تمت التسوية') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($item->notes)
                                <span class="text-sm text-gray-600">{{ Str::limit($item->notes, 30) }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection