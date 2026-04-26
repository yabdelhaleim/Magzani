<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * صفحة إدارة الصلاحيات الرئيسية
     */
    public function index()
    {
        $this->authorize('users.permissions');

        $users = User::with('roles', 'permissions')
            ->orderBy('name')
            ->paginate(20);

        $roles = Role::with('permissions')->get();
        $allPermissions = Permission::orderBy('module')->orderBy('name')->get();
        $modules = Permission::select('module')->distinct()->orderBy('module')->pluck('module');

        return view('permissions.index', compact('users', 'roles', 'allPermissions', 'modules'));
    }

    /**
     * عرض صفحة تعديل صلاحيات مستخدم محدد
     */
    public function editUser(User $user)
    {
        $this->authorize('users.permissions');

        $user->load('roles', 'permissions');
        $roles = Role::with('permissions')->get();
        $allPermissions = Permission::orderBy('module')->orderBy('name')->get();
        $modules = Permission::select('module')->distinct()->orderBy('module')->pluck('module');

        // تجميع الصلاحيات حسب الموديول
        $permissionsByModule = [];
        foreach ($allPermissions as $permission) {
            $permissionsByModule[$permission->module][] = $permission;
        }

        return view('permissions.edit-user', compact(
            'user',
            'roles',
            'allPermissions',
            'permissionsByModule',
            'modules'
        ));
    }

    /**
     * تحديث صلاحيات وأدوار المستخدم
     */
    public function updateUser(Request $request, User $user)
    {
        $this->authorize('users.permissions');

        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();

        try {
            // تحديث الأدوار
            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            } else {
                $user->roles()->sync([]);
            }

            // تحديث الصلاحيات المباشرة
            if ($request->has('permissions')) {
                $user->syncPermissions($request->permissions);
            } else {
                $user->permissions()->sync([]);
            }

            DB::commit();

            return redirect()
                ->route('permissions.index')
                ->with('success', 'تم تحديث صلاحيات المستخدم بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الصلاحيات: ' . $e->getMessage());
        }
    }

    /**
     * عرض جميع الأدوار والصلاحيات
     */
    public function roles()
    {
        $this->authorize('users.permissions');

        $roles = Role::with('permissions', 'users')->get();
        $allPermissions = Permission::orderBy('module')->orderBy('name')->get();
        $modules = Permission::select('module')->distinct()->orderBy('module')->pluck('module');

        return view('permissions.roles', compact('roles', 'allPermissions', 'modules'));
    }

    /**
     * إنشاء دور جديد
     */
    public function storeRole(Request $request)
    {
        $this->authorize('users.permissions');

        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'color' => $request->color ?: '#3b82f6',
            'is_system' => false,
        ]);

        return redirect()
            ->route('permissions.roles')
            ->with('success', 'تم إنشاء الدور بنجاح');
    }

    /**
     * تعديل دور
     */
    public function updateRole(Request $request, Role $role)
    {
        $this->authorize('users.permissions');

        if ($role->is_system) {
            return back()->with('error', 'لا يمكن تعديل أدوار النظام');
        }

        $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
        ]);

        $role->update([
            'display_name' => $request->display_name,
            'description' => $request->description,
            'color' => $request->color,
        ]);

        return redirect()
            ->route('permissions.roles')
            ->with('success', 'تم تحديث الدور بنجاح');
    }

    /**
     * حذف دور
     */
    public function destroyRole(Role $role)
    {
        $this->authorize('users.permissions');

        if ($role->is_system) {
            return back()->with('error', 'لا يمكن حذف أدوار النظام');
        }

        $role->delete();

        return redirect()
            ->route('permissions.roles')
            ->with('success', 'تم حذف الدور بنجاح');
    }

    /**
     * تحديث صلاحيات دور
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $this->authorize('users.permissions');

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $permissions = $request->permissions ?? [];
        $role->syncPermissions($permissions);

        return redirect()
            ->route('permissions.roles')
            ->with('success', 'تم تحديث صلاحيات الدور بنجاح');
    }

    /**
     * طباعة تقرير الصلاحيات
     */
    public function printReport()
    {
        $this->authorize('users.permissions');

        $users = User::with('roles', 'permissions')->orderBy('name')->get();
        $roles = Role::with('permissions', 'users')->get();
        $allPermissions = Permission::orderBy('module')->orderBy('name')->get();

        return view('permissions.print', compact('users', 'roles', 'allPermissions'));
    }
}
