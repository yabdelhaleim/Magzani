@extends('layouts.app')

@section('title', 'تعديل مورد')
@section('page-title', 'تعديل مورد')

@section('content')
<div class="space-y-6">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-600">
        <a href="{{ route('suppliers.index') }}" class="hover:text-blue-600">الموردين</a>
        <i class="fas fa-chevron-left text-xs"></i>
        <a href="{{ route('suppliers.show', $supplier->id) }}" class="hover:text-blue-600">{{ $supplier->name }}</a>
        <i class="fas fa-chevron-left text-xs"></i>
        <span class="text-gray-900 font-medium">تعديل</span>
    </nav>

    <div class="max-w-4xl mx-auto">
        
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl shadow-lg p-8 text-white mb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-edit text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold mb-1">تعديل بيانات المورد</h2>
                    <p class="text-yellow-100">تحديث معلومات المورد: <strong>{{ $supplier->name }}</strong></p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div>
                    <div class="flex items-center gap-3 mb-6 pb-3 border-b border-gray-200">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-info-circle text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">المعلومات الأساسية</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                اسم المورد <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $supplier->name) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" required>
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                رقم الهاتف <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $supplier->phone) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror" required>
                            @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">البريد الإلكتروني</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $supplier->email) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <div class="flex items-center gap-3 mb-6 pb-3 border-b border-gray-200">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-map-marker-alt text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">معلومات الاتصال</h3>
                    </div>
                    <textarea name="address" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('address', $supplier->address) }}</textarea>
                </div>

                <!-- Additional Info -->
                <div>
                    <div class="flex items-center gap-3 mb-6 pb-3 border-b border-gray-200">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clipboard text-purple-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">معلومات إضافية</h3>
                    </div>
                    <div class="space-y-4">
                        <textarea name="notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="ملاحظات">{{ old('notes', $supplier->notes) }}</textarea>
                        
                        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded">
                            <label for="is_active"><span class="font-semibold">تفعيل المورد</span></label>
                        </div>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="bg-gray-50 rounded-lg p-4 grid grid-cols-2 gap-4 text-sm">
                    <div><i class="fas fa-calendar-plus text-gray-400 ml-2"></i><strong>تاريخ الإضافة:</strong> {{ $supplier->created_at->format('Y-m-d h:i A') }}</div>
                    <div><i class="fas fa-calendar-edit text-gray-400 ml-2"></i><strong>آخر تحديث:</strong> {{ $supplier->updated_at->format('Y-m-d h:i A') }}</div>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('suppliers.show', $supplier->id) }}" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-arrow-right ml-2"></i>إلغاء والرجوع
                    </a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-lg hover:shadow-xl">
                        <i class="fas fa-save ml-2"></i>حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border p-6 mt-6">
            <h4 class="font-bold mb-4"><i class="fas fa-bolt text-yellow-500 ml-2"></i>إجراءات سريعة</h4>
            <div class="flex gap-3 flex-wrap">
                <a href="{{ route('suppliers.show', $supplier->id) }}" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">
                    <i class="fas fa-eye ml-1"></i>عرض التفاصيل
                </a>
                <a href="{{ route('suppliers.statement', $supplier->id) }}" class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200">
                    <i class="fas fa-file-invoice ml-1"></i>كشف الحساب
                </a>
            </div>
        </div>

    </div>
</div>
@endsection