@extends('layouts.app')

@section('title', 'تعديل المخزن')
@section('page-title', 'تعديل مخزن')

@section('content')

<!-- Header -->
<div class="mb-6">
    <div class="flex items-center gap-3 mb-2">
        <a href="{{ route('warehouses.show', $warehouse->id) }}" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <h2 class="text-2xl font-bold text-gray-800">تعديل مخزن: {{ $warehouse->name }}</h2>
    </div>
    <p class="text-gray-600 text-sm mr-9">تعديل بيانات المخزن ومعلوماته</p>
</div>

<!-- Form Container -->
<div class="max-w-4xl">
    <form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- المعلومات الأساسية -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b">
                <div class="bg-blue-100 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">المعلومات الأساسية</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- اسم المخزن -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        اسم المخزن <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           value="{{ old('name', $warehouse->name) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('name') border-red-500 @enderror" 
                           placeholder="مثال: المخزن الرئيسي"
                           required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- كود المخزن -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        كود المخزن <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="code" 
                           value="{{ old('code', $warehouse->code) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('code') border-red-500 @enderror" 
                           placeholder="مثال: WH001"
                           required>
                    @error('code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- المسؤول -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        مسؤول المخزن
                    </label>
                    <select name="manager_id" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('manager_id') border-red-500 @enderror">
                        <option value="">اختر المسؤول</option>
                        @foreach(\App\Models\User::all() as $user)
                            <option value="{{ $user->id }}" {{ old('manager_id', $warehouse->manager_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- الحالة -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        حالة المخزن <span class="text-red-500">*</span>
                    </label>
                    <select name="status" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('status') border-red-500 @enderror"
                            required>
                        <option value="active" {{ old('status', $warehouse->status) == 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ old('status', $warehouse->status) == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                        <option value="maintenance" {{ old('status', $warehouse->status) == 'maintenance' ? 'selected' : '' }}>قيد الصيانة</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- معلومات الموقع -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b">
                <div class="bg-green-100 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">معلومات الموقع</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- المدينة -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        المدينة
                    </label>
                    <input type="text" 
                           name="city" 
                           value="{{ old('city', $warehouse->city) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('city') border-red-500 @enderror" 
                           placeholder="مثال: القاهرة">
                    @error('city')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- المنطقة -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        المنطقة
                    </label>
                    <input type="text" 
                           name="area" 
                           value="{{ old('area', $warehouse->area) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('area') border-red-500 @enderror" 
                           placeholder="مثال: مدينة نصر">
                    @error('area')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- العنوان -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        العنوان بالتفصيل
                    </label>
                    <input type="text" 
                           name="address" 
                           value="{{ old('address', $warehouse->address) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('address') border-red-500 @enderror" 
                           placeholder="مثال: شارع مصطفى النحاس، الحي الثامن">
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- معلومات الاتصال -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b">
                <div class="bg-purple-100 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">معلومات الاتصال</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- رقم الهاتف -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        رقم الهاتف
                    </label>
                    <input type="tel" 
                           name="phone" 
                           value="{{ old('phone', $warehouse->phone) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('phone') border-red-500 @enderror" 
                           placeholder="مثال: 01012345678">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- البريد الإلكتروني -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        البريد الإلكتروني
                    </label>
                    <input type="email" 
                           name="email" 
                           value="{{ old('email', $warehouse->email) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('email') border-red-500 @enderror" 
                           placeholder="مثال: warehouse@company.com">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- الوصف -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b">
                <div class="bg-orange-100 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">معلومات إضافية</h3>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    الوصف
                </label>
                <textarea name="description" 
                          rows="4"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('description') border-red-500 @enderror" 
                          placeholder="أضف وصفاً تفصيلياً للمخزن...">{{ old('description', $warehouse->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- الأزرار -->
        <div class="flex items-center gap-3">
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2 shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                تحديث المخزن
            </button>
            
            <a href="{{ route('warehouses.show', $warehouse->id) }}" 
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-8 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                إلغاء
            </a>
        </div>
    </form>
</div>

@endsection
