@extends('layouts.app')

@section('title', 'إدارة المخازن')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">إدارة المخازن</h2>
            <p class="text-gray-600 text-sm mt-1">عرض وإدارة جميع المخازن</p>
        </div>
        <a href="{{ route('warehouses.create') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2 shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            مخزن جديد
        </a>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
        {{ session('error') }}
    </div>
    @endif

    <!-- Warehouses Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($warehouses as $warehouse)
        <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow overflow-hidden border border-gray-200">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">{{ $warehouse->name }}</h3>
                            <p class="text-blue-100 text-sm">{{ $warehouse->code }}</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        {{ $warehouse->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $warehouse->is_active ? 'نشط' : 'غير نشط' }}
                    </span>
                </div>
            </div>

            <!-- Body -->
            <div class="p-4">
                <!-- Location -->
                @if($warehouse->address)
                <div class="flex items-start gap-2 text-gray-600 mb-3">
                    <svg class="w-5 h-5 mt-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-sm">{{ Str::limit($warehouse->address, 50) }}</span>
                </div>
                @endif

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-xs text-blue-600 font-semibold mb-1">إجمالي الأصناف</p>
                        <p class="text-2xl font-bold text-blue-700">{{ number_format($warehouse->total_products ?? 0) }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                        <p class="text-xs text-green-600 font-semibold mb-1">قيمة المخزون</p>
                        <p class="text-2xl font-bold text-green-700">{{ number_format($warehouse->total_value ?? 0) }}</p>
                    </div>
                </div>

                <!-- Manager -->
                @if($warehouse->manager_name)
                <div class="flex items-center gap-2 text-gray-600 mb-4">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="text-sm font-semibold">{{ $warehouse->manager_name }}</span>
                </div>
                @endif

                <!-- Actions -->
                <div class="flex items-center gap-2 pt-3 border-t border-gray-200">
                    <a href="{{ route('warehouses.show', $warehouse->id) }}" 
                       class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                        عرض التفاصيل
                    </a>
                    <a href="{{ route('warehouses.edit', $warehouse->id) }}" 
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                    <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST" 
                          onsubmit="return confirm('⚠️ هل أنت متأكد من حذف هذا المخزن؟')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">لا توجد مخازن</h3>
                <p class="text-gray-600 mb-6">لم يتم إنشاء أي مخازن بعد</p>
                <a href="{{ route('warehouses.create') }}" 
                   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    إنشاء مخزن جديد
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($warehouses->hasPages())
    <div class="mt-6">
        {{ $warehouses->links() }}
    </div>
    @endif
</div>
@endsection