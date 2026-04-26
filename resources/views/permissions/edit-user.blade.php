@extends('layouts.app')

@section('title', 'تعديل صلاحيات المستخدم')

@section('content')
<div class="page-body">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-user-shield text-blue-600 ml-2"></i>
                تعديل صلاحيات المستخدم
            </h1>
            <p class="text-gray-600 mt-1">
                {{ $user->name }} - {{ $user->email }}
            </p>
        </div>
        <a href="{{ route('permissions.index') }}" 
           class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition flex items-center gap-2">
            <i class="fas fa-arrow-right"></i>
            رجوع
        </a>
    </div>

    <form method="POST" action="{{ route('permissions.update-user', $user) }}">
        @csrf
        @method('PUT')

        <!-- Roles Selection -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h5 class="text-lg font-bold text-gray-800 mb-0">
                    <i class="fas fa-user-tag text-green-600 ml-2"></i>
                    الأدوار
                </h5>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($roles as $role)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox"
                                   name="roles[]"
                                   value="{{ $role->id }}"
                                   @if($user->roles->contains($role->id)) checked @endif
                                   class="mt-1 w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold" 
                                          style="background-color: {{ $role->color }}20; color: {{ $role->color }};">
                                        {{ $role->display_name }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500">{{ $role->description ?: 'بدون وصف' }}</p>
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Direct Permissions by Module -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h5 class="text-lg font-bold text-gray-800 mb-0">
                        <i class="fas fa-key text-amber-600 ml-2"></i>
                        الصلاحيات المباشرة
                    </h5>
                    <div class="flex gap-2">
                        <button type="button" 
                                onclick="selectAllPermissions()"
                                class="px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm font-semibold transition">
                            تحديد الكل
                        </button>
                        <button type="button" 
                                onclick="deselectAllPermissions()"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold transition">
                            إلغاء التحديد
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                @foreach($permissionsByModule as $module => $permissions)
                <div class="mb-6 last:mb-0">
                    <h6 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-folder text-amber-500"></i>
                        @php
                            $moduleName = trans()->has('modules.' . $module) ? __('modules.' . $module) : $module;
                        @endphp
                        {{ $moduleName }}
                    </h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($permissions as $permission)
                        <div class="flex items-center gap-3 p-2 rounded hover:bg-gray-50 transition">
                            <input type="checkbox"
                                   class="permission-checkbox w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                   id="permission_{{ $permission->id }}"
                                   name="permissions[]"
                                   value="{{ $permission->id }}"
                                   @if($user->permissions->contains($permission->id)) checked @endif>
                            <label for="permission_{{ $permission->id }}" class="flex-1 cursor-pointer">
                                <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs font-mono mb-1">
                                    {{ $permission->action }}
                                </span>
                                <div class="text-sm text-gray-700">{{ $permission->display_name }}</div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Inherited Permissions from Roles -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h5 class="text-lg font-bold text-gray-800 mb-0">
                    <i class="fas fa-info-circle text-blue-600 ml-2"></i>
                    الصلاحيات المكتسبة من الأدوار
                </h5>
            </div>
            <div class="p-6">
                @if($user->roles->count() > 0)
                    <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-lg mb-4">
                        <i class="fas fa-info-circle ml-2"></i>
                        هذا المستخدم يحصل على الصلاحيات التالية من أدواره:
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($user->roles as $role)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h6 class="font-bold text-gray-800 mb-3">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold" 
                                      style="background-color: {{ $role->color }}20; color: {{ $role->color }};">
                                    {{ $role->display_name }}
                                </span>
                            </h6>
                            <div class="space-y-1">
                                @foreach($role->permissions->take(10) as $perm)
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-mono">
                                            {{ $perm->action }}
                                        </span>
                                        <span class="text-gray-600">{{ $perm->display_name }}</span>
                                    </div>
                                @endforeach
                                @if($role->permissions->count() > 10)
                                    <div class="text-xs text-gray-400 pt-1 border-t border-gray-100">
                                        + {{ $role->permissions->count() - 10 }} صلاحية أخرى
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-lg">
                        <i class="fas fa-exclamation-triangle ml-2"></i>
                        هذا المستخدم ليس لديه أي أدوار محددة
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex justify-between">
                    <a href="{{ route('permissions.index') }}" 
                       class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition">
                        <i class="fas fa-times ml-2"></i>
                        إلغاء
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-save ml-2"></i>
                        حفظ التغييرات
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

@push('scripts')
<script>
function selectAllPermissions() {
    document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
        checkbox.checked = true;
    });
}

function deselectAllPermissions() {
    document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
        checkbox.checked = false;
    });
}
</script>
@endpush
@endsection
