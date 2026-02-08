@extends('layouts.app')

@section('title', 'إضافة مورد جديد')
@section('page-title', 'إضافة مورد جديد')

@section('content')
<div class="space-y-6">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-600">
        <a href="{{ route('suppliers.index') }}" class="hover:text-blue-600">الموردين</a>
        <i class="fas fa-chevron-left text-xs"></i>
        <span class="text-gray-900 font-medium">إضافة مورد جديد</span>
    </nav>

    <div class="max-w-4xl mx-auto">
        
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg p-8 text-white mb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-plus text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold mb-1">إضافة مورد جديد</h2>
                    <p class="text-blue-100">قم بإدخال بيانات المورد بشكل صحيح لإضافته للنظام</p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Basic Information Section -->
                <div>
                    <div class="flex items-center gap-3 mb-6 pb-3 border-b border-gray-200">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-info-circle text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">المعلومات الأساسية</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                اسم المورد <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   value="{{ old('name') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                                   placeholder="أدخل اسم المورد"
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                رقم الهاتف <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="text" 
                                       name="phone" 
                                       id="phone"
                                       value="{{ old('phone') }}"
                                       class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror"
                                       placeholder="01234567890"
                                       required>
                            </div>
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                البريد الإلكتروني
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" 
                                       name="email" 
                                       id="email"
                                       value="{{ old('email') }}"
                                       class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                                       placeholder="example@email.com">
                            </div>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div>
                    <div class="flex items-center gap-3 mb-6 pb-3 border-b border-gray-200">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-map-marker-alt text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">معلومات الاتصال</h3>
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">العنوان</label>
                        <textarea name="address" 
                                  id="address"
                                  rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('address') border-red-500 @enderror"
                                  placeholder="أدخل العنوان الكامل للمورد">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div>
                    <div class="flex items-center gap-3 mb-6 pb-3 border-b border-gray-200">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clipboard text-purple-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">معلومات إضافية</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">ملاحظات</label>
                            <textarea name="notes" 
                                      id="notes"
                                      rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="أضف أي ملاحظات إضافية عن المورد">{{ old('notes') }}</textarea>
                        </div>

                        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active"
                                   value="1"
                                   checked
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                            <label for="is_active" class="flex-1">
                                <span class="font-semibold text-gray-800">تفعيل المورد</span>
                                <p class="text-sm text-gray-600 mt-1">يمكن للموردين النشطين فقط إجراء عمليات شراء</p>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('suppliers.index') }}" 
                       class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-arrow-right ml-2"></i>
                        إلغاء والرجوع
                    </a>
                    <button type="submit" 
                            class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg hover:shadow-xl">
                        <i class="fas fa-save ml-2"></i>
                        حفظ المورد
                    </button>
                </div>

            </form>
        </div>

        <!-- Help Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mt-6">
            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-lightbulb text-3xl text-blue-600"></i>
                </div>
                <div>
                    <h4 class="font-bold text-blue-900 mb-2">نصائح مهمة</h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• تأكد من إدخال رقم هاتف صحيح للتواصل مع المورد</li>
                        <li>• يمكنك تعديل معلومات المورد في أي وقت</li>
                        <li>• الحقول المميزة بـ <span class="text-red-600 font-bold">*</span> إلزامية</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection