<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of users (Admin only)
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user (Admin only)
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user (Admin only)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,employee',
            'is_active' => 'boolean',
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // تسجيل في سجل الأنشطة
        \App\Models\ActivityLog::log('user_create', $newUser, "تم إنشاء مستخدم جديد باسم: '{$newUser->name}' وصلاحية: '{$newUser->role}'", [
            'email' => $newUser->email,
            'role' => $newUser->role,
        ]);

        return redirect()->route('users.index')->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user (Admin only)
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user (Admin only)
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,employee',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? $user->is_active,
        ];

        // Only update password if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // تسجيل في سجل الأنشطة
        \App\Models\ActivityLog::log('user_update', $user, "تم تحديث بيانات المستخدم: '{$user->name}' وصلاحيته إلى: '{$user->role}'", [
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ]);

        return redirect()->route('users.index')->with('success', 'تم تحديث بيانات المستخدم بنجاح');
    }

    /**
     * Remove the specified user (Admin only - soft delete)
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'لا يمكنك حذف حسابك الخاص');
        }

        // تسجيل في سجل الأنشطة قبل الحذف
        \App\Models\ActivityLog::log('user_delete', $user, "تم حذف حساب المستخدم: '{$user->name}'", [
            'email' => $user->email,
            'role' => $user->role,
        ]);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم بنجاح');
    }

    /**
     * Toggle user active status (Admin only)
     */
    public function toggleActive(User $user)
    {
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'لا يمكنك إلغاء تفعيل حسابك الخاص');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'تفعيل' : 'إلغاء تفعيل';
        $statusAr = $user->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';

        // تسجيل في سجل الأنشطة
        \App\Models\ActivityLog::log('user_toggle_active', $user, "تم تغيير حالة الحساب للمستخدم '{$user->name}' إلى: '{$statusAr}'", [
            'email' => $user->email,
            'status' => $status,
        ]);

        return redirect()->route('users.index')->with('success', "{$statusAr} حساب المستخدم بنجاح");
    }
}