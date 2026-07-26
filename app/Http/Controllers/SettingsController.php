<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    protected $service;

    public function __construct(SettingsService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        try {
            $company = \App\Models\Company::first() ?? new \App\Models\Company();
            $system = \App\Models\SystemSetting::first() ?? new \App\Models\SystemSetting();

            return view('settings.index', compact('company', 'system'));
        } catch (\Exception $e) {
            Log::error('Failed to load settings page', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء تحميل صفحة الإعدادات.');
        }
    }

    public function updateCompany(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string|max:500',
                'tax_number' => 'nullable|string|max:100',
                'commercial_register' => 'nullable|string|max:100',
                'logo' => 'nullable|image|max:2048',
            ]);

            $this->service->updateCompany($data);

            return redirect()->back()->with('success', 'تم تحديث بيانات الشركة بنجاح.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to update company', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث بيانات الشركة.');
        }
    }

    public function deleteLogo()
    {
        try {
            $this->service->deleteLogo();

            return redirect()->back()->with('success', 'تم حذف شعار الشركة بنجاح.');
        } catch (\Exception $e) {
            Log::error('Failed to delete logo', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء حذف الشعار.');
        }
    }

    public function updateSystem(Request $request)
    {
        try {
            $data = $request->validate([
                'default_currency' => 'required|string|max:10',
                'date_format' => 'required|string|max:20',
                'default_tax' => 'required|numeric|min:0',
                'rows_per_page' => 'required|integer|min:1',
                'low_stock_alert' => 'nullable|boolean',
                'allow_negative_stock' => 'nullable|boolean',
                'confirm_before_delete' => 'nullable|boolean',
                'auto_invoice_number' => 'nullable|boolean',
                'auto_print_invoice' => 'nullable|boolean',
                'auto_email_invoice' => 'nullable|boolean',
            ]);

            $this->service->updateSystem($data);

            return redirect()->back()->with('success', 'تم تحديث إعدادات النظام بنجاح.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to update system settings', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث إعدادات النظام.');
        }
    }

    public function storeUser(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'role' => 'required|string|max:50',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $this->service->createUser($data);

            return redirect()->back()->with('success', 'تم إضافة المستخدم بنجاح.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DB error creating user', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'البريد الإلكتروني مستخدم بالفعل.');
        } catch (\Exception $e) {
            Log::error('Failed to create user', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة المستخدم.');
        }
    }

    public function updateUser(Request $request, User $user)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'role' => 'required|string|max:50',
                'password' => 'nullable|string|min:6|confirmed',
            ]);

            $this->service->updateUser($user, $data);

            return redirect()->back()->with('success', 'تم تحديث بيانات المستخدم بنجاح.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'المستخدم غير موجود');
        } catch (\Exception $e) {
            Log::error('Failed to update user', [
                'user_id' => $user->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث المستخدم.');
        }
    }

    public function deleteUser(User $user)
    {
        try {
            $this->service->deleteUser($user);

            return redirect()->back()->with('success', 'تم حذف المستخدم بنجاح.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'المستخدم غير موجود');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DB error deleting user', [
                'user_id' => $user->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->with('error', 'لا يمكن حذف المستخدم. قد يكون مرتبطاً ببيانات أخرى.');
        } catch (\Exception $e) {
            Log::error('Failed to delete user', [
                'user_id' => $user->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->with('error', 'حدث خطأ أثناء حذف المستخدم.');
        }
    }

    public function backup()
    {
        try {
            $this->service->backup();

            return redirect()->back()->with('success', 'تم إنشاء النسخة الاحتياطية بنجاح.');
        } catch (\Exception $e) {
            Log::error('Failed to create backup', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء إنشاء النسخة الاحتياطية.');
        }
    }

    public function restoreBackup(Request $request)
    {
        try {
            $request->validate([
                'backup_file' => 'required|file|mimes:sql,zip',
            ]);

            $path = $request->file('backup_file')->store('backups', 'public');

            $this->service->restoreBackup($path);

            return redirect()->back()->with('success', 'تم استعادة النسخة الاحتياطية بنجاح.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to restore backup', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء استعادة النسخة الاحتياطية.');
        }
    }
}
