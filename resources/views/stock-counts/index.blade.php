@extends('layouts.app')

@section('title', 'إدارة الجرد')

@section('page-title', 'إدارة الجرد')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-4xl font-black text-gray-900 mb-2">إدارة الجرد</h1>
            <p class="text-gray-500">جرد المخزون ومطابقة الكميات</p>
        </div>
        <a href="{{ route('stock-counts.create') }}" 
           class="inline-flex items-center gap-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all group">
            <svg class="w-6 h-6 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>إنشاء جرد جديد</span>
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-100">
        <div class="p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-900 mb-2">المخزن</label>
                    <select name="warehouse_id" class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium">
                        <option value="">جميع المخازن</option>
                        @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-900 mb-2">الحالة</label>
                    <select name="status" class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium">
                        <option value="">جميع الحالات</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>جاري التنفيذ</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        بحث
                    </button>
                    <a href="{{ route('stock-counts.index') }}" 
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 p-3 rounded-xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Stock Counts List -->
    <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-100 overflow-hidden">
        @forelse($stockCounts as $count)
        <div class="border-b-2 border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors">
            <div class="p-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <!-- Main Info -->
                    <div class="flex-1">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-xl font-black text-gray-900">{{ $count->count_number }}</h3>
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold
                                        {{ $count->status == 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $count->status == 'in_progress' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $count->status == 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $count->status == 'draft' ? 'bg-gray-100 text-gray-700' : '' }}">
                                        {{ $count->status_label }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                        </svg>
                                        <span class="font-medium text-gray-900">{{ $count->warehouse->name }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>{{ $count->count_date->format('Y-m-d') }}</span>
                                    </div>
                                    @if($count->creator)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-blue-600 font-bold text-xs">
                                                {{ substr($count->creator->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <span>{{ $count->creator->name }}</span>
                                    </div>
                                    @endif
                                </div>

                                <!-- Progress Bar -->
                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-sm mb-2">
                                        <span class="font-medium text-gray-700">التقدم</span>
                                        <span class="text-gray-600">{{ $count->items_counted }}/{{ $count->total_items }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-full rounded-full transition-all duration-500 flex items-center justify-end px-2"
                                             style="width: {{ $count->progress_percentage }}%">
                                            @if($count->progress_percentage > 15)
                                            <span class="text-white text-xs font-bold">{{ number_format($count->progress_percentage, 0) }}%</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($count->discrepancies > 0)
                                <div class="mt-3 inline-flex items-center gap-2 bg-yellow-50 text-yellow-800 px-3 py-1.5 rounded-lg text-sm font-semibold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    {{ $count->discrepancies }} فروقات
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-2 lg:flex-col lg:items-end">
                        @if($count->status == 'draft')
                        <form action="{{ route('stock-counts.start', $count->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-bold text-sm transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                بدء الجرد
                            </button>
                        </form>
                        @endif

                        @if($count->status == 'in_progress')
                        <a href="{{ route('stock-counts.count', $count->id) }}" 
                           class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-bold text-sm transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            متابعة الجرد
                        </a>
                        @endif

                        <a href="{{ route('stock-counts.show', $count->id) }}" 
                           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold text-sm transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            عرض
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="p-16 text-center">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-100 rounded-full mb-4">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">لا توجد عمليات جرد</h3>
            <p class="text-gray-500 mb-6">ابدأ بإنشاء أول عملية جرد للمخزون</p>
            <a href="{{ route('stock-counts.create') }}" 
               class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                إنشاء جرد جديد
            </a>
        </div>
        @endforelse
        
        @if($stockCounts->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t-2 border-gray-100">
            {{ $stockCounts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection