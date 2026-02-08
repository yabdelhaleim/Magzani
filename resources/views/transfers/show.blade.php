@extends('layouts.app')

@section('title', 'تفاصيل التحويل #' . $transfer->id)
@section('page-title', 'تفاصيل التحويل')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">تحويل رقم #{{ $transfer->id }}</h2>
            <p class="text-gray-600 text-sm mt-1">
                رقم التحويل: <span class="font-semibold">{{ $transfer->transfer_number }}</span> | 
                تاريخ الإنشاء: {{ $transfer->created_at->format('Y-m-d H:i') }}
            </p>
        </div>
        <div class="flex gap-3">
            {{-- ✅ تصحيح: استخدم 'received' مش 'completed' --}}
            @if($transfer->status === 'received')
                <form action="{{ route('transfers.reverse', $transfer->id) }}" method="POST" 
                      onsubmit="return confirm('⚠️ هل أنت متأكد من عكس هذا التحويل؟\n\nسيتم إرجاع المنتجات إلى المخزن المصدر.')">
                    @csrf
                    <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                        عكس التحويل
                    </button>
                </form>
            @endif

            @if(in_array($transfer->status, ['draft', 'pending', 'received']))
                <form action="{{ route('transfers.cancel', $transfer->id) }}" method="POST"
                      onsubmit="return confirm('⚠️ هل أنت متأكد من إلغاء هذا التحويل؟\n\n{{ $transfer->status === 'received' ? 'سيتم إرجاع المنتجات للمخزن المصدر.' : '' }}')">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        إلغاء التحويل
                    </button>
                </form>
            @endif

            <a href="{{ route('transfers.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                رجوع
            </a>
        </div>
    </div>
</div>

<!-- معلومات التحويل -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex items-center gap-3 mb-6 pb-4 border-b">
        <div class="bg-blue-100 p-2 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-800">معلومات التحويل</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <p class="text-sm text-gray-500 mb-2">من مخزن</p>
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </div>
                <p class="font-bold text-gray-800">{{ $transfer->fromWarehouse->name }}</p>
            </div>
        </div>

        <div>
            <p class="text-sm text-gray-500 mb-2">إلى مخزن</p>
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                </div>
                <p class="font-bold text-gray-800">{{ $transfer->toWarehouse->name }}</p>
            </div>
        </div>

        <div>
            <p class="text-sm text-gray-500 mb-2">الحالة</p>
            {{-- ✅ تصحيح جميع الحالات --}}
            @if($transfer->status === 'received')
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    مستلم
                </span>
            @elseif($transfer->status === 'pending')
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg text-sm font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    معلق
                </span>
            @elseif($transfer->status === 'draft')
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    مسودة
                </span>
            @elseif($transfer->status === 'reversed')
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-orange-100 text-orange-700 rounded-lg text-sm font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                    معكوس
                </span>
            @else
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    ملغي
                </span>
            @endif
        </div>
    </div>

    @if($transfer->notes)
        <div class="mt-6 p-4 bg-gray-50 rounded-lg border-r-4 border-blue-500">
            <p class="text-sm text-gray-500 mb-2 font-semibold">ملاحظات:</p>
            <p class="text-gray-700">{{ $transfer->notes }}</p>
        </div>
    @endif
</div>

<!-- المنتجات -->
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex items-center gap-3 mb-6 pb-4 border-b">
        <div class="bg-green-100 p-2 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-800">المنتجات المحولة ({{ $transfer->items->count() }})</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">المنتج</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">الكمية</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">ملاحظات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($transfer->items as $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $item->product->name }}</p>
                                <p class="text-xs text-gray-500 mt-1">رمز المنتج: {{ $item->product->sku }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-block px-4 py-2 bg-blue-100 text-blue-700 rounded-lg font-bold">
                                {{ number_format($item->quantity, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ $item->notes ?? '--' }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-green-50 rounded-lg border border-blue-200">
        <div class="flex justify-between items-center">
            <span class="font-bold text-gray-700">إجمالي عدد المنتجات:</span>
            <span class="text-2xl font-bold text-blue-600">{{ $transfer->items->count() }}</span>
        </div>
    </div>
</div>
@endsection