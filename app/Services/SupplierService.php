<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SupplierService
{
    /**
     * إنشاء مورد جديد
     */
    public function create(array $data): Supplier
    {
        return DB::transaction(function () use ($data) {
            try {
                // إضافة معلومات المستخدم
                $data['created_by'] = auth()->id();
                $data['current_balance'] = $data['opening_balance'] ?? 0;
                $data['balance'] = $data['opening_balance'] ?? 0;

                $supplier = Supplier::create($data);

                Log::info('تم إنشاء مورد جديد', [
                    'supplier_id' => $supplier->id,
                    'supplier_code' => $supplier->code,
                    'name' => $supplier->name,
                ]);

                return $supplier;

            } catch (Exception $e) {
                Log::error('خطأ في إنشاء المورد: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * تحديث بيانات مورد
     */
    public function update(Supplier $supplier, array $data): Supplier
    {
        return DB::transaction(function () use ($supplier, $data) {
            try {
                // إضافة معلومات المستخدم
                $data['updated_by'] = auth()->id();

                $supplier->update($data);

                Log::info('تم تحديث بيانات المورد', [
                    'supplier_id' => $supplier->id,
                    'supplier_code' => $supplier->code,
                ]);

                return $supplier->fresh();

            } catch (Exception $e) {
                Log::error('خطأ في تحديث المورد: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * حذف مورد (Soft Delete)
     */
    public function delete(Supplier $supplier): bool
    {
        return DB::transaction(function () use ($supplier) {
            try {
                // التحقق من عدم وجود فواتير مرتبطة
                if ($supplier->purchaseInvoices()->count() > 0) {
                    throw new Exception('لا يمكن حذف المورد لوجود فواتير مرتبطة به');
                }

                $supplier->delete();

                Log::info('تم حذف المورد', [
                    'supplier_id' => $supplier->id,
                    'supplier_code' => $supplier->code,
                ]);

                return true;

            } catch (Exception $e) {
                Log::error('خطأ في حذف المورد: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * الحصول على قائمة الموردين مع الفلترة
     */
    public function getSuppliers(array $filters = [])
    {
        $query = Supplier::query();

        // فلتر الحالة
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // البحث
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // فلتر بالمدينة
        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        // فلتر بالدولة
        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        // الترتيب
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * تحديث رصيد المورد
     */
    public function updateBalance(Supplier $supplier, float $amount, string $operation): Supplier
    {
        return DB::transaction(function () use ($supplier, $amount, $operation) {
            try {
                if ($operation === 'add') {
                    $supplier->current_balance += $amount;
                } elseif ($operation === 'subtract') {
                    $supplier->current_balance -= $amount;
                } else {
                    throw new Exception("عملية غير معروفة: $operation");
                }

                $supplier->balance = $supplier->current_balance;
                $supplier->save();

                Log::info('تم تحديث رصيد المورد', [
                    'supplier_id' => $supplier->id,
                    'operation' => $operation,
                    'amount' => $amount,
                    'new_balance' => $supplier->current_balance,
                ]);

                return $supplier;

            } catch (Exception $e) {
                Log::error('خطأ في تحديث رصيد المورد: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * احصائيات الموردين
     */
    public function getStatistics()
    {
        return [
            'total_suppliers' => Supplier::count(),
            'active_suppliers' => Supplier::where('is_active', true)->count(),
            'inactive_suppliers' => Supplier::where('is_active', false)->count(),
            'total_balance' => Supplier::sum('current_balance'),
            'suppliers_with_balance' => Supplier::where('current_balance', '>', 0)->count(),
        ];
    }

    /**
     * الحصول على كشف حساب المورد
     */
    public function getStatement(Supplier $supplier, array $filters = [])
    {
        $statement = $supplier->getStatement();

        // فلتر بالتاريخ من - إلى
        if (!empty($filters['date_from'])) {
            $statement = $statement->filter(function($item) use ($filters) {
                return $item['date'] >= $filters['date_from'];
            });
        }

        if (!empty($filters['date_to'])) {
            $statement = $statement->filter(function($item) use ($filters) {
                return $item['date'] <= $filters['date_to'];
            });
        }

        // فلتر بنوع العملية
        if (!empty($filters['type'])) {
            $statement = $statement->filter(function($item) use ($filters) {
                return $item['type'] == $filters['type'];
            });
        }

        return $statement->values();
    }

    /**
     * الحصول على ملخص مالي للمورد
     */
    public function getFinancialSummary(Supplier $supplier)
    {
        $totalPurchases = $supplier->purchaseInvoices()->sum('total');
        $totalReturns = $supplier->purchaseReturns()->sum('total');
        $totalPayments = $supplier->payments()->sum('amount');
        
        $netPurchases = $totalPurchases - $totalReturns;
        $outstandingBalance = $supplier->current_balance;

        return [
            'opening_balance' => $supplier->opening_balance,
            'total_purchases' => $totalPurchases,
            'total_returns' => $totalReturns,
            'net_purchases' => $netPurchases,
            'total_payments' => $totalPayments,
            'current_balance' => $outstandingBalance,
            'last_purchase_date' => $supplier->purchaseInvoices()->max('invoice_date'),
            'last_payment_date' => $supplier->payments()->max('payment_date'),
        ];
    }

    /**
     * تفعيل/إيقاف مورد
     */
    public function toggleStatus(Supplier $supplier): Supplier
    {
        $supplier->is_active = !$supplier->is_active;
        $supplier->updated_by = auth()->id();
        $supplier->save();

        Log::info('تم تغيير حالة المورد', [
            'supplier_id' => $supplier->id,
            'new_status' => $supplier->is_active ? 'نشط' : 'غير نشط',
        ]);

        return $supplier;
    }

    /**
     * الحصول على أفضل الموردين
     */
    public function getTopSuppliers($limit = 10)
    {
        return Supplier::withSum('purchaseInvoices', 'total')
            ->orderBy('purchase_invoices_sum_total', 'desc')
            ->limit($limit)
            ->get();
    }
}