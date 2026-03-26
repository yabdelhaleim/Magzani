@extends('layouts.app')

@section('title', 'تفاصيل المستخدم')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <a href="{{ route('users.index') }}" class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-white hover:bg-white/30 transition-colors">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                            <div>
                                <h2 class="text-2xl font-bold text-white">تفاصيل المستخدم</h2>
                                <p class="text-indigo-100 mt-1">معلومات حساب المستخدم</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('users.edit', $user->id) }}" class="bg-white text-indigo-600 px-5 py-2 rounded-xl font-bold hover:bg-indigo-50 transition-all flex items-center gap-2">
                                <i class="fas fa-edit"></i>
                                تعديل
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Info -->
                <div class="p-6">
                    <!-- Profile Section -->
                    <div class="flex items-center gap-6 mb-8 pb-8 border-b border-gray-200">
                        <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center text-white text-4xl font-bold shadow-lg">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h3>
                            <p class="text-gray-500">{{ $user->email }}</p>
                            <span class="inline-block mt-2 px-3 py-1 rounded-full text-sm font-bold {{ $user->role === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $user->role_name }}
                            </span>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-xl p-5">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <span class="text-gray-500 text-sm">البريد الإلكتروني</span>
                            </div>
                            <p class="text-gray-800 font-semibold">{{ $user->email }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-5">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center text-green-600">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <span class="text-gray-500 text-sm">الهاتف</span>
                            </div>
                            <p class="text-gray-800 font-semibold">{{ $user->phone ?? '-' }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-5">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <span class="text-gray-500 text-sm">الدور</span>
                            </div>
                            <p class="text-gray-800 font-semibold">{{ $user->role_name }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-5">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 {{ $user->is_active ? 'bg-green-100' : 'bg-gray-100' }} rounded-xl flex items-center justify-center {{ $user->is_active ? 'text-green-600' : 'text-gray-600' }}">
                                    <i class="fas {{ $user->is_active ? 'fa-check-circle' : 'fa-ban' }}"></i>
                                </div>
                                <span class="text-gray-500 text-sm">الحالة</span>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-bold {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $user->is_active ? 'نشط' : 'غير نشط' }}
                            </span>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-5">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center text-yellow-600">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <span class="text-gray-500 text-sm">تاريخ الإنشاء</span>
                            </div>
                            <p class="text-gray-800 font-semibold">{{ $user->created_at->format('Y-m-d') }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-5">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center text-orange-600">
                                    <i class="fas fa-calendar-edit"></i>
                                </div>
                                <span class="text-gray-500 text-sm">آخر تحديث</span>
                            </div>
                            <p class="text-gray-800 font-semibold">{{ $user->updated_at->format('Y-m-d') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection