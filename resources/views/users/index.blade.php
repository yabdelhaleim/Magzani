@extends('layouts.app')

@section('title', 'إدارة المستخدمين')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-white">إدارة المستخدمين</h2>
                            <p class="text-blue-100 mt-1">إضافة وتعديل وإدارة حسابات المستخدمين</p>
                        </div>
                        <a href="{{ route('users.create') }}" 
                           class="bg-white text-blue-600 px-6 py-3 rounded-xl font-bold hover:bg-blue-50 transition-all shadow-lg flex items-center gap-2">
                            <i class="fas fa-plus"></i>
                            إضافة مستخدم جديد
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
                <div class="p-6">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-4 flex items-center gap-2">
                            <i class="fas fa-check-circle text-xl"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 flex items-center gap-2">
                            <i class="fas fa-exclamation-circle text-xl"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Table -->
                <div class="px-6 pb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b-2 border-gray-200">
                                    <th class="text-right py-4 px-4 font-bold text-gray-700">#</th>
                                    <th class="text-right py-4 px-4 font-bold text-gray-700">الاسم</th>
                                    <th class="text-right py-4 px-4 font-bold text-gray-700">البريد الإلكتروني</th>
                                    <th class="text-right py-4 px-4 font-bold text-gray-700">الهاتف</th>
                                    <th class="text-right py-4 px-4 font-bold text-gray-700">الدور</th>
                                    <th class="text-right py-4 px-4 font-bold text-gray-700">الحالة</th>
                                    <th class="text-right py-4 px-4 font-bold text-gray-700">تاريخ الإنشاء</th>
                                    <th class="text-right py-4 px-4 font-bold text-gray-700">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($users as $user)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-4 text-gray-600">{{ $loop->iteration }}</td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <span class="font-semibold text-gray-800">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-gray-600">{{ $user->email }}</td>
                                    <td class="py-4 px-4 text-gray-600">{{ $user->phone ?? '-' }}</td>
                                    <td class="py-4 px-4">
                                        <span class="px-3 py-1 rounded-full text-sm font-bold {{ $user->role === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ $user->role_name }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="px-3 py-1 rounded-full text-sm font-bold {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $user->is_active ? 'نشط' : 'غير نشط' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-gray-500 text-sm">{{ $user->created_at->format('Y-m-d') }}</td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('users.show', $user->id) }}" 
                                               class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-200 transition-colors" 
                                               title="عرض">
                                                <i class="fas fa-eye text-sm"></i>
                                            </a>
                                            <a href="{{ route('users.edit', $user->id) }}" 
                                               class="w-8 h-8 bg-yellow-100 text-yellow-600 rounded-lg flex items-center justify-center hover:bg-yellow-200 transition-colors" 
                                               title="تعديل">
                                                <i class="fas fa-edit text-sm"></i>
                                            </a>
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('users.toggle-active', $user->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="w-8 h-8 {{ $user->is_active ? 'bg-gray-100 text-gray-600' : 'bg-green-100 text-green-600' }} rounded-lg flex items-center justify-center hover:bg-gray-200 transition-colors" 
                                                            title="{{ $user->is_active ? 'إلغاء تفعيل' : 'تفعيل' }}">
                                                        <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }} text-sm"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="w-8 h-8 bg-red-100 text-red-600 rounded-lg flex items-center justify-center hover:bg-red-200 transition-colors" 
                                                            title="حذف">
                                                        <i class="fas fa-trash text-sm"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($users->isEmpty())
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-users text-3xl text-gray-400"></i>
                            </div>
                            <p class="text-gray-500 text-lg">لا يوجد مستخدمون مسجلون</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection