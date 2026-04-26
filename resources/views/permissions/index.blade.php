@extends('layouts.app')

@section('title', 'إدارة الصلاحيات')

@section('content')
<div class="page-body">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-shield-alt text-blue-600 ml-2"></i>
                إدارة الصلاحيات
            </h1>
            <p class="text-gray-600 mt-1">
                إدارة صلاحيات وأدوار المستخدمين في النظام
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('permissions.roles') }}" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition flex items-center gap-2">
                <i class="fas fa-user-tag"></i>
                إدارة الأدوار
            </a>
            <a href="{{ route('permissions.print') }}" 
               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition flex items-center gap-2">
                <i class="fas fa-print"></i>
                طباعة التقرير
            </a>
        </div>
    </div>

    <!-- Company Header -->
    @if(session('company_logo'))
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-200">
        <div class="flex items-center gap-4">
            <img src="{{ session('company_logo') }}" alt="شعار الشركة" class="h-12 w-auto rounded-lg">
            <div>
                <h5 class="font-bold text-gray-800 mb-0">{{ session('company_name', 'اسم الشركة') }}</h5>
                @if(session('company_address'))
                <p class="text-gray-500 text-sm">{{ session('company_address') }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">عدد المستخدمين</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $users->total() }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-tag text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">عدد الأدوار</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $roles->count() }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-key text-amber-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">عدد الصلاحيات</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $allPermissions->count() }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cubes text-cyan-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">عدد الموديولات</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $modules->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h5 class="text-lg font-bold text-gray-800 mb-0">
                    <i class="fas fa-users text-blue-600 ml-2"></i>
                    المستخدمين وصلاحياتهم
                </h5>
                <form class="flex gap-2" method="GET" action="">
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="بحث عن مستخدم...">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase">المستخدم</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase">البريد الإلكتروني</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase">الدور</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase">الصلاحيات</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase">الحالة</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-circle text-gray-400 text-xl"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-800">{{ $user->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            @if($user->roles->count() > 0)
                                @foreach($user->roles as $role)
                                    <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold mb-1" 
                                          style="background-color: {{ $role->color }}20; color: {{ $role->color }};">
                                        {{ $role->display_name }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-gray-400 text-sm">لا يوجد دور</span>
                            @endif
                        </td>
<td class="px-6 py-4">
                                <span class="text-sm text-gray-600">
                                    {{ count($user->allPermissions()) }} صلاحية
                                </span>
                            </td>
                        <td class="px-6 py-4">
                            @if($user->is_active)
                                <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                                    <i class="fas fa-check ml-1"></i>نشط
                                </span>
                            @else
                                <span class="inline-block px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">
                                    <i class="fas fa-times ml-1"></i>غير نشط
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('permissions.edit-user', $user) }}"
                               class="inline-flex items-center gap-2 px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm font-semibold transition">
                                <i class="fas fa-edit"></i>
                                تعديل الصلاحيات
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">لا يوجد مستخدمين</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- Permissions by Module -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h5 class="text-lg font-bold text-gray-800 mb-0">
                <i class="fas fa-cubes text-cyan-600 ml-2"></i>
                الصلاحيات المتاحة حسب الموديول
            </h5>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($modules as $module)
                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                    <h6 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-folder text-amber-500"></i>
                        @php
                            $moduleName = trans()->has('modules.' . $module) ? __('modules.' . $module) : $module;
                        @endphp
                        {{ $moduleName }}
                    </h6>
                    <div class="space-y-2">
                        @foreach($allPermissions->where('module', $module)->take(5) as $permission)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">{{ $permission->display_name }}</span>
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs font-mono">
                                {{ $permission->action }}
                            </span>
                        </div>
                        @endforeach
                        @if($allPermissions->where('module', $module)->count() > 5)
                            <div class="text-xs text-gray-400 text-center pt-2 border-t border-gray-100">
                                + {{ $allPermissions->where('module', $module)->count() - 5 }} صلاحية أخرى
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
@media print {
    .no-print { display: none !important; }
    .card, .border { border: 1px solid #ddd !important; }
}
</style>
@endpush
@endsection
