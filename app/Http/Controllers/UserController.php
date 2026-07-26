<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    /**
     * Display a listing of users (Admin only)
     */
    public function index()
    {
        try {
            $users = User::orderBy('created_at', 'desc')->get();
            return view('users.index', compact('users'));
        } catch (\Exception $e) {
            Log::error('Failed to load users list', ['error' => $e->getMessage()]);
            return view('users.index', ['users' => collect()])
                ->with('error', 'حدث خطأ أثناء تحميل قائمة المستخدمين.');
        }
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
        try {
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

            \App\Models\ActivityLog::log('user_create', $newUser, "تم إنشاء مستخدم جديد باسم: '{$newUser->name}' وصلاحية: '{$newUser->role}'", [
                'email' => $newUser->email,
                'role' => $newUser->role,
            ]);

            return redirect()->route('users.index')->with('success', 'تم إنشاء المستخدم بنجاح');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DB error creating user', [
                'email' => $request->email ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()
                ->with('error', 'البريد الإلكتروني مستخدم بالفعل أو حدث خطأ في قاعدة البيانات.');
        } catch (\Exception $e) {
            Log::error('Unexpected error creating user', [
                'email' => $request->email ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()
                ->with('error', 'حدث خطأ غير متوقع أثناء إنشاء المستخدم.');
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        try {
            return view('users.show', compact('user'));
        } catch (\Exception $e) {
            Log::error('Failed to show user', [
                'user_id' => $user->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->with('error', 'حدث خطأ أثناء عرض المستخدم.');
        }
    }

    /**
     * Show the form for editing the specified user (Admin only)
     */
    public function edit(User $user)
    {
        try {
            return view('users.edit', compact('user'));
        } catch (\Exception $e) {
            Log::error('Failed to load edit form', [
                'user_id' => $user->id,
                'error'    => $e->getMessage(),
            ]);
            return redirect()->route('users.index')
                ->with('error', 'حدث خطأ أثناء تحميل نموذج التعديل.');
        }
    }

    /**
     * Update the specified user (Admin only)
     */
    public function update(Request $request, User $user)
    {
        try {
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

            if (! empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            \App\Models\ActivityLog::log('user_update', $user, "تم تحديث بيانات المستخدم: '{$user->name}' وصلاحيته إلى: '{$user->role}'", [
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ]);

            return redirect()->route('users.index')->with('success', 'تم تحديث بيانات المستخدم بنجاح');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (ModelNotFoundException $e) {
            abort(404, 'المستخدم غير موجود');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DB error updating user', [
                'user_id' => $user->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->withInput()
                ->with('error', 'البريد الإلكتروني مستخدم بالفعل أو حدث خطأ في قاعدة البيانات.');
        } catch (\Exception $e) {
            Log::error('Unexpected error updating user', [
                'user_id' => $user->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->withInput()
                ->with('error', 'حدث خطأ غير متوقع أثناء تحديث المستخدم.');
        }
    }

    /**
     * Remove the specified user (Admin only - soft delete)
     */
    public function destroy(User $user)
    {
        try {
            if ($user->id === auth()->id()) {
                return redirect()->route('users.index')->with('error', 'لا يمكنك حذف حسابك الخاص');
            }

            \App\Models\ActivityLog::log('user_delete', $user, "تم حذف حساب المستخدم: '{$user->name}'", [
                'email' => $user->email,
                'role' => $user->role,
            ]);

            $user->delete();

            return redirect()->route('users.index')->with('success', 'تم حذف المستخدم بنجاح');
        } catch (ModelNotFoundException $e) {
            abort(404, 'المستخدم غير موجود');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DB error deleting user', [
                'user_id' => $user->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->with('error', 'لا يمكن حذف المستخدم. قد يكون مرتبطاً ببيانات أخرى.');
        } catch (\Exception $e) {
            Log::error('Unexpected error deleting user', [
                'user_id' => $user->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->with('error', 'حدث خطأ غير متوقع أثناء حذف المستخدم.');
        }
    }

    /**
     * Toggle user active status (Admin only)
     */
    public function toggleActive(User $user)
    {
        try {
            if ($user->id === auth()->id()) {
                return redirect()->route('users.index')->with('error', 'لا يمكنك إلغاء تفعيل حسابك الخاص');
            }

            $user->update(['is_active' => ! $user->is_active]);

            $statusAr = $user->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';

            \App\Models\ActivityLog::log('user_toggle_active', $user, "تم تغيير حالة الحساب للمستخدم '{$user->name}' إلى: '{$statusAr}'", [
                'email' => $user->email,
            ]);

            return redirect()->route('users.index')->with('success', "{$statusAr} حساب المستخدم بنجاح");
        } catch (ModelNotFoundException $e) {
            abort(404, 'المستخدم غير موجود');
        } catch (\Exception $e) {
            Log::error('Error toggling user active', [
                'user_id' => $user->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->with('error', 'حدث خطأ أثناء تغيير حالة المستخدم.');
        }
    }
}
