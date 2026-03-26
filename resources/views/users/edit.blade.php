@extends('layouts.app')

@section('title', 'تعديل مستخدم')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-6">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('users.index') }}" class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-white hover:bg-white/30 transition-colors">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <div>
                            <h2 class="text-2xl font-bold text-white">تعديل مستخدم</h2>
                            <p class="text-yellow-100 mt-1">تحديث بيانات المستخدم: {{ $user->name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form action="{{ route('users.update', $user->id) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div class="col-span-2">
                            <label for="name" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-user ml-2 text-yellow-500"></i>
                                الاسم <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all @error('name') border-red-500 @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   placeholder="أدخل اسم المستخدم"
                                   required>
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-span-2">
                            <label for="email" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-envelope ml-2 text-yellow-500"></i>
                                البريد الإلكتروني <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all @error('email') border-red-500 @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   placeholder="أدخل البريد الإلكتروني"
                                   required>
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-phone ml-2 text-yellow-500"></i>
                                الهاتف
                            </label>
                            <input type="text" 
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all @error('phone') border-red-500 @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $user->phone) }}" 
                                   placeholder="أدخل رقم الهاتف">
                            @error('phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Role -->
                        <div>
                            <label for="role" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-user-tag ml-2 text-yellow-500"></i>
                                الدور <span class="text-red-500">*</span>
                            </label>
                            <select class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all @error('role') border-red-500 @enderror" 
                                    id="role" 
                                    name="role" 
                                    required>
                                <option value="">اختر الدور</option>
                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>مدير النظام</option>
                                <option value="employee" {{ old('role', $user->role) === 'employee' ? 'selected' : '' }}>موظف</option>
                            </select>
                            @error('role')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-lock ml-2 text-yellow-500"></i>
                                كلمة المرور الجديدة
                            </label>
                            <input type="password" 
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all @error('password') border-red-500 @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="اتركها فارغة إذا لا تريد تغييرها">
                            @error('password')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Confirmation -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-lock ml-2 text-yellow-500"></i>
                                تأكيد كلمة المرور الجديدة
                            </label>
                            <input type="password" 
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 outline-none transition-all" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="أعد إدخال كلمة المرور">
                        </div>

                        <!-- Active Status -->
                        <div class="col-span-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" 
                                           class="sr-only" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                    <div class="w-12 h-6 bg-gray-300 rounded-full transition-colors" id="toggle-bg"></div>
                                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform shadow" id="toggle-dot"></div>
                                </div>
                                <span class="text-gray-700 font-bold">حساب نشط</span>
                            </label>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-4 mt-8 pt-6 border-t border-gray-200">
                        <button type="submit" class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white px-8 py-3 rounded-xl font-bold hover:from-yellow-600 hover:to-orange-600 transition-all shadow-lg flex items-center gap-2">
                            <i class="fas fa-save"></i>
                            تحديث
                        </button>
                        <a href="{{ route('users.index') }}" class="bg-gray-100 text-gray-700 px-8 py-3 rounded-xl font-bold hover:bg-gray-200 transition-all flex items-center gap-2">
                            <i class="fas fa-times"></i>
                            إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
#is_active:checked ~ #toggle-bg {
    background-color: #10B981;
}
#is_active:checked ~ #toggle-dot {
    transform: translateX(100%);
}
</style>
@endsection