<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected $service;

    public function __construct(SettingsService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $company = \App\Models\Company::first();
        $system = \App\Models\SystemSetting::first();
        return view('settings.index', compact('company', 'system'));
    }

    public function updateCompany(Request $request)
    {
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
    }

    public function deleteLogo()
    {
        $this->service->deleteLogo();

        return redirect()->back()->with('success', 'تم حذف شعار الشركة بنجاح.');
    }

    public function updateSystem(Request $request)
    {
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
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string|max:50',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $this->service->createUser($data);

        return redirect()->back()->with('success', 'تم إضافة المستخدم بنجاح.');
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|string|max:50',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $this->service->updateUser($user, $data);

        return redirect()->back()->with('success', 'تم تحديث بيانات المستخدم بنجاح.');
    }

    public function deleteUser(User $user)
    {
        $this->service->deleteUser($user);

        return redirect()->back()->with('success', 'تم حذف المستخدم بنجاح.');
    }

    public function backup()
    {
        $this->service->backup();

        return redirect()->back()->with('success', 'تم إنشاء النسخة الاحتياطية بنجاح.');
    }

    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,zip',
        ]);

        $path = $request->file('backup_file')->store('backups', 'public');

        $this->service->restoreBackup($path);

        return redirect()->back()->with('success', 'تم استعادة النسخة الاحتياطية بنجاح.');
    }
}
