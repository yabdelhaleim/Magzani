@extends('layouts.app')

@section('title', 'إدارة الأدوار')

@section('content')
<div class="page-body">

    <!-- Header with Create Form -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-user-tag text-blue-600 ml-2"></i>
                إدارة الأدوار
            </h1>
            <p class="text-gray-600 mt-1">
                إدارة الأدوار وصلاحياتها في النظام
            </p>
        </div>
    </div>

    <!-- Create Role Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6 border-b border-gray-200">
            <h5 class="text-lg font-bold text-gray-800 mb-0">
                <i class="fas fa-plus-circle text-green-600 ml-2"></i>
                إنشاء دور جديد
            </h5>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('permissions.store-role') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">اسم الدور (إنجليزي)</label>
                    <input type="text" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="مثال: branch_manager">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">اسم العرض</label>
                    <input type="text" name="display_name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="مثال: مدير فرع">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">الوصف</label>
                    <input type="text" name="description" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="وصف مختصر للدور">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-plus ml-2"></i>
                        إنشاء
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($roles as $role)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition" id="role-card-{{ $role->id }}">
            <!-- Role Header -->
            <div class="p-4 border-b border-gray-200" style="background-color: {{ $role->color }}15;">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-1 rounded text-sm font-bold" 
                                  style="background-color: {{ $role->color }}; color: white;">
                                {{ $role->display_name }}
                            </span>
                            @if($role->is_system)
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">دور نظام</span>
                            @endif
                        </div>
                        <code class="text-xs text-gray-500">@{{ $role->name }}</code>
                    </div>
                </div>
            </div>

            <!-- Role Body -->
            <div class="p-4">
                <p class="text-gray-600 text-sm mb-4">{{ $role->description ?: 'بدون وصف' }}</p>

                <!-- Users Count -->
                <div class="mb-4">
                    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                        <i class="fas fa-users text-blue-500"></i>
                        <span class="font-semibold">المستخدمين ({{ $role->users->count() }})</span>
                    </div>
                    <div class="flex flex-wrap gap-1">
                        @foreach($role->users->take(5) as $user)
                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                                {{ $user->name }}
                            </span>
                        @endforeach
                        @if($role->users->count() > 5)
                            <span class="text-xs text-gray-400">+{{ $role->users->count() - 5 }}</span>
                        @endif
                    </div>
                </div>

                <!-- Permissions Count -->
                <div class="mb-4">
                    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                        <i class="fas fa-key text-amber-500"></i>
                        <span class="font-semibold">الصلاحيات ({{ $role->permissions->count() }})</span>
                    </div>
                    <div class="space-y-1 max-h-32 overflow-y-auto">
                        @foreach($role->permissions->take(8) as $permission)
                            <div class="flex items-center gap-2 text-xs">
                                <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded font-mono">
                                    {{ $permission->action }}
                                </span>
                                <span class="text-gray-600">{{ $permission->display_name }}</span>
                            </div>
                        @endforeach
                        @if($role->permissions->count() > 8)
                            <div class="text-xs text-gray-400 pt-1 border-t border-gray-100">
                                + {{ $role->permissions->count() - 8 }} صلاحية أخرى
                            </div>
                        @endif
                    </div>
                </div>

                @if(!$role->is_system)
                <!-- Action Buttons -->
                <div class="flex gap-2 pt-3 border-t border-gray-100">
                    <button type="button"
                            onclick="toggleEditRoleForm('edit-role-form-{{ $role->id }})"
                            class="flex-1 px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm font-semibold transition">
                        <i class="fas fa-edit ml-1"></i>
                        تعديل
                    </button>
                    <button type="button"
                            onclick="if(confirm('هل أنت متأكد من حذف هذا الدور؟')) document.getElementById('delete-role-{{ $role->id }}').submit();"
                            class="px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-semibold transition">
                        <i class="fas fa-trash"></i>
                    </button>
                    <form id="delete-role-{{ $role->id }}" 
                          method="POST" 
                          action="{{ route('permissions.destroy-role', $role) }}" 
                          style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>

                <!-- Inline Edit Form (Hidden by default) -->
                <div id="edit-role-form-{{ $role->id }}" style="display: none;" class="mt-4 pt-4 border-t border-gray-200">
                    <form method="POST" action="{{ route('permissions.update-role', $role) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">اسم العرض</label>
                                <input type="text" name="display_name" value="{{ $role->display_name }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">اللون</label>
                                <input type="color" name="color" value="{{ $role->color }}" 
                                       class="w-full h-10 rounded-lg cursor-pointer border border-gray-300">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">الوصف</label>
                            <textarea name="description" rows="2" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $role->description }}</textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition">
                                حفظ التغييرات
                            </button>
                            <button type="button" onclick="toggleEditRoleForm('edit-role-form-{{ $role->id }})"
                                    class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-semibold">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="pt-3 border-t border-gray-100">
                    <span class="text-xs text-gray-400">دور نظام - لا يمكن تعديله</span>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

</div>

@push('scripts')
<script>
function toggleEditRoleForm(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endpush

@endsection
