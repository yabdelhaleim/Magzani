<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionAndRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ==================== إنشاء الأدوار ====================

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'مدير النظام',
                'description' => 'لديه جميع الصلاحيات في النظام',
                'color' => '#dc2626',
                'is_system' => true,
            ]
        );

        $employeeRole = Role::firstOrCreate(
            ['name' => 'employee'],
            [
                'display_name' => 'موظف',
                'description' => 'موظف في النظام له صلاحيات محدودة',
                'color' => '#3b82f6',
                'is_system' => true,
            ]
        );

        // ==================== إنشاء الصلاحيات ====================

        // صلاحيات المبيعات
        $salesPermissions = [
            [
                'name' => 'sales.invoices.create',
                'display_name' => 'إنشاء فواتير المبيعات',
                'description' => 'القدرة على إنشاء فواتير مبيعات جديدة',
                'module' => 'sales',
                'action' => 'create',
            ],
            [
                'name' => 'sales.invoices.read',
                'display_name' => 'عرض فواتير المبيعات',
                'description' => 'القدرة على عرض فواتير المبيعات',
                'module' => 'sales',
                'action' => 'read',
            ],
            [
                'name' => 'sales.invoices.update',
                'display_name' => 'تعديل فواتير المبيعات',
                'description' => 'القدرة على تعديل فواتير المبيعات',
                'module' => 'sales',
                'action' => 'update',
            ],
            [
                'name' => 'sales.invoices.delete',
                'display_name' => 'حذف فواتير المبيعات',
                'description' => 'القدرة على حذف فواتير المبيعات',
                'module' => 'sales',
                'action' => 'delete',
            ],
            [
                'name' => 'sales.invoices.print',
                'display_name' => 'طباعة فواتير المبيعات',
                'description' => 'القدرة على طباعة فواتير المبيعات',
                'module' => 'sales',
                'action' => 'print',
            ],
            [
                'name' => 'sales.returns.create',
                'display_name' => 'إنشاء مرتجعات المبيعات',
                'description' => 'القدرة على إنشاء مرتجعات مبيعات',
                'module' => 'sales',
                'action' => 'create',
            ],
            [
                'name' => 'sales.returns.read',
                'display_name' => 'عرض مرتجعات المبيعات',
                'description' => 'القدرة على عرض مرتجعات المبيعات',
                'module' => 'sales',
                'action' => 'read',
            ],
            [
                'name' => 'sales.returns.delete',
                'display_name' => 'حذف مرتجعات المبيعات',
                'description' => 'القدرة على حذف مرتجعات المبيعات',
                'module' => 'sales',
                'action' => 'delete',
            ],
        ];

        // صلاحيات المشتريات
        $purchasesPermissions = [
            [
                'name' => 'purchases.invoices.create',
                'display_name' => 'إنشاء فواتير المشتريات',
                'description' => 'القدرة على إنشاء فواتير مشتريات',
                'module' => 'purchases',
                'action' => 'create',
            ],
            [
                'name' => 'purchases.invoices.read',
                'display_name' => 'عرض فواتير المشتريات',
                'description' => 'القدرة على عرض فواتير المشتريات',
                'module' => 'purchases',
                'action' => 'read',
            ],
            [
                'name' => 'purchases.invoices.update',
                'display_name' => 'تعديل فواتير المشتريات',
                'description' => 'القدرة على تعديل فواتير مشتريات',
                'module' => 'purchases',
                'action' => 'update',
            ],
            [
                'name' => 'purchases.invoices.delete',
                'display_name' => 'حذف فواتير المشتريات',
                'description' => 'القدرة على حذف فواتير مشتريات',
                'module' => 'purchases',
                'action' => 'delete',
            ],
            [
                'name' => 'purchases.returns.create',
                'display_name' => 'إنشاء مرتجعات المشتريات',
                'description' => 'القدرة على إنشاء مرتجعات مشتريات',
                'module' => 'purchases',
                'action' => 'create',
            ],
            [
                'name' => 'purchases.returns.read',
                'display_name' => 'عرض مرتجعات المشتريات',
                'description' => 'القدرة على عرض مرتجعات المشتريات',
                'module' => 'purchases',
                'action' => 'read',
            ],
            [
                'name' => 'purchases.returns.delete',
                'display_name' => 'حذف مرتجعات المشتريات',
                'description' => 'القدرة على حذف مرتجعات المشتريات',
                'module' => 'purchases',
                'action' => 'delete',
            ],
        ];

        // صلاحيات المخزون
        $warehousePermissions = [
            [
                'name' => 'warehouse.products.create',
                'display_name' => 'إضافة منتجات',
                'description' => 'القدرة على إضافة منتجات جديدة',
                'module' => 'warehouse',
                'action' => 'create',
            ],
            [
                'name' => 'warehouse.products.read',
                'display_name' => 'عرض المنتجات',
                'description' => 'القدرة على عرض المنتجات',
                'module' => 'warehouse',
                'action' => 'read',
            ],
            [
                'name' => 'warehouse.products.update',
                'display_name' => 'تعديل المنتجات',
                'description' => 'القدرة على تعديل بيانات المنتجات',
                'module' => 'warehouse',
                'action' => 'update',
            ],
            [
                'name' => 'warehouse.products.delete',
                'display_name' => 'حذف المنتجات',
                'description' => 'القدرة على حذف المنتجات',
                'module' => 'warehouse',
                'action' => 'delete',
            ],
            [
                'name' => 'warehouse.transfers.create',
                'display_name' => 'إنشاء تحويلات مخزون',
                'description' => 'القدرة على تحويل المنتجات بين المخازن',
                'module' => 'warehouse',
                'action' => 'create',
            ],
            [
                'name' => 'warehouse.transfers.read',
                'display_name' => 'عرض تحويلات المخزون',
                'description' => 'القدرة على عرض تحويلات المخزون',
                'module' => 'warehouse',
                'action' => 'read',
            ],
            [
                'name' => 'warehouse.movements.read',
                'display_name' => 'عرض حركات المخزون',
                'description' => 'القدرة على عرض حركات المخزون',
                'module' => 'warehouse',
                'action' => 'read',
            ],
            [
                'name' => 'warehouse.stock_counts.create',
                'display_name' => 'إنشاء جرد مخزون',
                'description' => 'القدرة على إنشاء جرد مخزون',
                'module' => 'warehouse',
                'action' => 'create',
            ],
        ];

        // صلاحيات العملاء
        $customersPermissions = [
            [
                'name' => 'customers.create',
                'display_name' => 'إضافة عملاء',
                'description' => 'القدرة على إضافة عملاء جدد',
                'module' => 'customers',
                'action' => 'create',
            ],
            [
                'name' => 'customers.read',
                'display_name' => 'عرض العملاء',
                'description' => 'القدرة على عرض العملاء',
                'module' => 'customers',
                'action' => 'read',
            ],
            [
                'name' => 'customers.update',
                'display_name' => 'تعديل العملاء',
                'description' => 'القدرة على تعديل بيانات العملاء',
                'module' => 'customers',
                'action' => 'update',
            ],
            [
                'name' => 'customers.delete',
                'display_name' => 'حذف العملاء',
                'description' => 'القدرة على حذف العملاء',
                'module' => 'customers',
                'action' => 'delete',
            ],
            [
                'name' => 'customers.statement',
                'display_name' => 'كشوف حساب العملاء',
                'description' => 'القدرة على عرض كشوف حسابات العملاء',
                'module' => 'customers',
                'action' => 'read',
            ],
        ];

        // صلاحيات الموردين
        $suppliersPermissions = [
            [
                'name' => 'suppliers.create',
                'display_name' => 'إضافة موردين',
                'description' => 'القدرة على إضافة موردين جدد',
                'module' => 'suppliers',
                'action' => 'create',
            ],
            [
                'name' => 'suppliers.read',
                'display_name' => 'عرض الموردين',
                'description' => 'القدرة على عرض الموردين',
                'module' => 'suppliers',
                'action' => 'read',
            ],
            [
                'name' => 'suppliers.update',
                'display_name' => 'تعديل الموردين',
                'description' => 'القدرة على تعديل بيانات الموردين',
                'module' => 'suppliers',
                'action' => 'update',
            ],
            [
                'name' => 'suppliers.delete',
                'display_name' => 'حذف الموردين',
                'description' => 'القدرة على حذف الموردين',
                'module' => 'suppliers',
                'action' => 'delete',
            ],
            [
                'name' => 'suppliers.statement',
                'display_name' => 'كشوف حساب الموردين',
                'description' => 'القدرة على عرض كشوف حسابات الموردين',
                'module' => 'suppliers',
                'action' => 'read',
            ],
        ];

        // صلاحيات التصنيع
        $manufacturingPermissions = [
            [
                'name' => 'manufacturing.create',
                'display_name' => 'إنشاء أوامر التصنيع',
                'description' => 'القدرة على إنشاء أوامر تصنيع',
                'module' => 'manufacturing',
                'action' => 'create',
            ],
            [
                'name' => 'manufacturing.read',
                'display_name' => 'عرض أوامر التصنيع',
                'description' => 'القدرة على عرض أوامر التصنيع',
                'module' => 'manufacturing',
                'action' => 'read',
            ],
            [
                'name' => 'manufacturing.update',
                'display_name' => 'تعديل أوامر التصنيع',
                'description' => 'القدرة على تعديل أوامر التصنيع',
                'module' => 'manufacturing',
                'action' => 'update',
            ],
            [
                'name' => 'manufacturing.delete',
                'display_name' => 'حذف أوامر التصنيع',
                'description' => 'القدرة على حذف أوامر التصنيع',
                'module' => 'manufacturing',
                'action' => 'delete',
            ],
            [
                'name' => 'manufacturing.complete',
                'display_name' => 'إكمال أوامر التصنيع',
                'description' => 'القدرة على إكمال أوامر التصنيع',
                'module' => 'manufacturing',
                'action' => 'complete',
            ],
        ];

        // صلاحيات التقارير
        $reportsPermissions = [
            [
                'name' => 'reports.sales',
                'display_name' => 'تقرير المبيعات',
                'description' => 'القدرة على عرض تقارير المبيعات',
                'module' => 'reports',
                'action' => 'read',
            ],
            [
                'name' => 'reports.purchases',
                'display_name' => 'تقرير المشتريات',
                'description' => 'القدرة على عرض تقارير المشتريات',
                'module' => 'reports',
                'action' => 'read',
            ],
            [
                'name' => 'reports.inventory',
                'display_name' => 'تقرير المخزون',
                'description' => 'القدرة على عرض تقارير المخزون',
                'module' => 'reports',
                'action' => 'read',
            ],
            [
                'name' => 'reports.financial',
                'display_name' => 'التقارير المالية',
                'description' => 'القدرة على عرض التقارير المالية',
                'module' => 'reports',
                'action' => 'read',
            ],
            [
                'name' => 'reports.profit_loss',
                'display_name' => 'تقرير الأرباح والخسائر',
                'description' => 'القدرة على عرض تقرير الأرباح والخسائر',
                'module' => 'reports',
                'action' => 'read',
            ],
        ];

        // صلاحيات المحاسبة
        $accountingPermissions = [
            [
                'name' => 'accounting.treasury',
                'display_name' => 'الخزينة',
                'description' => 'القدرة على إدارة الخزينة',
                'module' => 'accounting',
                'action' => 'read',
            ],
            [
                'name' => 'accounting.payments',
                'display_name' => 'المدفوعات',
                'description' => 'القدرة على إدارة المدفوعات',
                'module' => 'accounting',
                'action' => 'read',
            ],
            [
                'name' => 'accounting.expenses',
                'display_name' => 'المصروفات',
                'description' => 'القدرة على إدارة المصروفات',
                'module' => 'accounting',
                'action' => 'read',
            ],
            [
                'name' => 'accounting.statistics',
                'display_name' => 'إحصائيات مالية',
                'description' => 'القدرة على عرض الإحصائيات المالية',
                'module' => 'accounting',
                'action' => 'read',
            ],
        ];

        // صلاحيات المستخدمين
        $usersPermissions = [
            [
                'name' => 'users.create',
                'display_name' => 'إضافة مستخدمين',
                'description' => 'القدرة على إضافة مستخدمين جدد',
                'module' => 'users',
                'action' => 'create',
            ],
            [
                'name' => 'users.read',
                'display_name' => 'عرض المستخدمين',
                'description' => 'القدرة على عرض المستخدمين',
                'module' => 'users',
                'action' => 'read',
            ],
            [
                'name' => 'users.update',
                'display_name' => 'تعديل المستخدمين',
                'description' => 'القدرة على تعديل بيانات المستخدمين',
                'module' => 'users',
                'action' => 'update',
            ],
            [
                'name' => 'users.delete',
                'display_name' => 'حذف المستخدمين',
                'description' => 'القدرة على حذف المستخدمين',
                'module' => 'users',
                'action' => 'delete',
            ],
            [
                'name' => 'users.permissions',
                'display_name' => 'إدارة الصلاحيات',
                'description' => 'القدرة على إدارة صلاحيات المستخدمين',
                'module' => 'users',
                'action' => 'manage',
            ],
        ];

        // صلاحيات الإعدادات
        $settingsPermissions = [
            [
                'name' => 'settings.company',
                'display_name' => 'إعدادات الشركة',
                'description' => 'القدرة على تعديل إعدادات الشركة',
                'module' => 'settings',
                'action' => 'update',
            ],
            [
                'name' => 'settings.system',
                'display_name' => 'إعدادات النظام',
                'description' => 'القدرة على تعديل إعدادات النظام',
                'module' => 'settings',
                'action' => 'update',
            ],
        ];

        // تجميع كل الصلاحيات
        $allPermissions = array_merge(
            $salesPermissions,
            $purchasesPermissions,
            $warehousePermissions,
            $customersPermissions,
            $suppliersPermissions,
            $manufacturingPermissions,
            $reportsPermissions,
            $accountingPermissions,
            $usersPermissions,
            $settingsPermissions
        );

        // إنشاء الصلاحيات
        foreach ($allPermissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                [
                    'display_name' => $permissionData['display_name'],
                    'description' => $permissionData['description'],
                    'module' => $permissionData['module'],
                    'action' => $permissionData['action'],
                    'is_system' => true,
                ]
            );
        }

        // ==================== منح الصلاحيات للأدوار ====================

        // المدير لديه كل الصلاحيات
        $adminRole->syncPermissions(Permission::pluck('id')->toArray());

        // الموظف لديه صلاحيات محدودة افتراضياً
        $employeeDefaultPermissions = [
            'sales.invoices.read',
            'sales.invoices.create',
            'sales.invoices.update',
            'sales.returns.read',
            'purchases.invoices.read',
            'purchases.invoices.create',
            'purchases.returns.read',
            'warehouse.products.read',
            'warehouse.transfers.read',
            'warehouse.movements.read',
            'customers.read',
            'suppliers.read',
            'manufacturing.read',
            'reports.sales',
            'reports.purchases',
            'reports.inventory',
        ];

        $employeePermissionIds = Permission::whereIn('name', $employeeDefaultPermissions)
            ->pluck('id')
            ->toArray();

        $employeeRole->syncPermissions($employeePermissionIds);

        $this->command->info('✅ تم إنشاء الصلاحيات والأدوار بنجاح!');
        $this->command->newLine();
        $this->command->info('📊 عدد الصلاحيات المُنشأة: ' . count($allPermissions));
        $this->command->info('👥 عدد الأدوار المُنشأة: 2 (مدير، موظف)');
        $this->command->newLine();
        $this->command->info('🔑 الصلاحيات الافتراضية للموظف:');
        foreach ($employeeDefaultPermissions as $perm) {
            $this->command->info('  - ' . $perm);
        }
    }
}
