<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsService
{
    // تحديث بيانات الشركة
    public function updateCompany(array $data)
    {
        $company = Company::first();

        if(isset($data['logo'])){
            $path = $data['logo']->store('company_logos', 'public');
            $data['logo'] = $path;
        }

        $company->update($data);

        return $company;
    }

    // تحديث إعدادات النظام
    public function updateSystem(array $data)
    {
        $system = SystemSetting::first();

        // تحويل checkboxes إلى true/false
        $checkboxes = [
            'low_stock_alert', 
            'allow_negative_stock', 
            'confirm_before_delete', 
            'auto_invoice_number', 
            'auto_print_invoice', 
            'auto_email_invoice'
        ];

        foreach($checkboxes as $field){
            $data[$field] = $data[$field] ?? false;
        }

        $system->update($data);

        return $system;
    }

    // إضافة مستخدم
    public function createUser(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    // تحديث مستخدم
    public function updateUser(User $user, array $data)
    {
        if(!empty($data['password'])){
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return $user;
    }

    // حذف مستخدم
    public function deleteUser(User $user)
    {
        return $user->delete();
    }

    // النسخ الاحتياطي (مجرد مثال)
    public function backup()
    {
        // Storage::disk('local')->put('backups/backup.sql', '...');
        return true;
    }

    // استعادة النسخة الاحتياطية (مجرد مثال)
    public function restoreBackup(string $path)
    {
        // تنفيذ سكريبت restore
        return true;
    }
}
