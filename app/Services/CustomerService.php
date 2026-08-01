<?php

namespace App\Services;

use App\Models\Customer;
use App\Exceptions\BusinessLogicException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CustomerService
{
    /**
     * إنشاء عميل جديد
     */
    public function create(array $data): Customer
    {
        try {
            // تحقق من عدم وجود عميل بنفس الاسم أو الهاتف
            $this->ensureUniqueCustomer($data['name'], $data['phone'] ?? null);

            $customer = Customer::create([
                'name'         => $data['name'],
                'phone'        => $data['phone'] ?? null,
                'email'        => $data['email'] ?? null,
                'address'      => $data['address'] ?? null,
                'balance'      => $data['balance'] ?? 0,
                'credit_limit' => $data['credit_limit'] ?? 0,
                'is_active'    => !empty($data['is_active']),
                'code'         => $data['code'] ?? uniqid('cus-'),
            ]);

            // ✅ PERF-02: إبطال كاش قائمة العملاء النشطين
            Cache::forget('customers.active.list');

            return $customer;
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error creating customer: ' . $e->getMessage());
            throw new RuntimeException('حدث خطأ أثناء حفظ العميل. يرجى المحاولة مرة أخرى.');
        } catch (\Exception $e) {
            \Log::error('Unexpected error creating customer: ' . $e->getMessage());
            throw new RuntimeException('حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.');
        }
    }

/**
 * تحديث بيانات العميل
 */
public function update(int $customerId, array $data): Customer
{
    try {
        $customer = Customer::findOrFail($customerId);

        // تحقق من عدم وجود عميل آخر بنفس الاسم أو الهاتف
        $this->ensureUniqueCustomer($data['name'], $data['phone'] ?? null, $customerId);

        $customer->update([
            'name'         => $data['name'],
            'phone'        => $data['phone'] ?? null,
            'email'        => $data['email'] ?? null,
            'address'      => $data['address'] ?? null,
            'balance'      => $data['balance'] ?? $customer->balance,
            'credit_limit' => $data['credit_limit'] ?? $customer->credit_limit,
            'is_active'    => !empty($data['is_active']),
            'code'         => $data['code'] ?? $customer->code,
        ]);

        // ✅ PERF-02: إبطال كاش قائمة العملاء النشطين (لو تغيّر الاسم أو الحالة)
        Cache::forget('customers.active.list');

        return $customer->fresh();
    } catch (RuntimeException $e) {
        throw $e;
    } catch (\Illuminate\Database\QueryException $e) {
        $this->handleDbError($e, 'تحديث');
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Unexpected error updating customer', [
            'customer_id' => $customerId,
            'error'       => $e->getMessage(),
        ]);
        throw new RuntimeException('حدث خطأ غير متوقع أثناء تحديث العميل.');
    }
}

/**
 * حذف عميل
 */
public function delete(int $customerId): bool
{
    try {
        $customer = Customer::findOrFail($customerId);

        if ($customer->salesInvoices()->exists()) {
            throw new BusinessLogicException('لا يمكن حذف العميل - لديه فواتير مسجلة', ['customer_id' => $customerId, 'invoices_count' => $customer->salesInvoices()->count()]);
        }

        $deleteResult = $customer->delete();

        return (bool) $deleteResult;
    } catch (RuntimeException $e) {
        throw $e;
    } catch (\Illuminate\Database\QueryException $e) {
        $this->handleDbError($e, 'حذف');
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Unexpected error deleting customer', [
            'customer_id' => $customerId,
            'error'       => $e->getMessage(),
        ]);
        throw new RuntimeException('حدث خطأ غير متوقع أثناء حذف العميل.');
    }
}

    /**
     * الحصول على رصيد العميل
     */
    public function getBalance(int $customerId): float
    {
        return (float) Customer::findOrFail($customerId)->balance;
    }

/**
 * تحديث رصيد العميل (add | subtract | set)
 */
public function updateBalance(int $customerId, float $amount, string $type = 'add'): float
{
    try {
        return DB::transaction(function () use ($customerId, $amount, $type) {

            if ($amount < 0) {
                throw new BusinessLogicException('القيمة غير صالحة', ['amount' => $amount]);
            }

            $customer = Customer::where('id', $customerId)
                ->lockForUpdate()
                ->firstOrFail();

            $newBalance = match ($type) {
                'add'      => $customer->balance + $amount,
                'subtract' => $customer->balance - $amount,
                'set'      => $amount,
                default    => throw new BusinessLogicException('نوع التحديث غير صحيح', ['type' => $type]),
            };

            $this->validateCreditLimit($customer, $newBalance);

            $customer->update([
                'balance' => $newBalance,
            ]);

            return $newBalance;
        });
    } catch (RuntimeException $e) {
        throw $e;
    } catch (\Illuminate\Database\QueryException $e) {
        $this->handleDbError($e, 'تحديث رصيد');
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Unexpected error updating customer balance', [
            'customer_id' => $customerId,
            'type'        => $type,
            'amount'      => $amount,
            'error'       => $e->getMessage(),
        ]);
        throw new RuntimeException('حدث خطأ غير متوقع أثناء تحديث رصيد العميل.');
    }
}

    /**
     * التحقق من الحد الائتماني
     */
    public function checkCreditLimit(int $customerId, float $newAmount): bool
    {
        $customer = Customer::findOrFail($customerId);
        $this->validateCreditLimit($customer, $newAmount);
        return true;
    }

/**
 * منطق التحقق المحاسبي للـ Credit Limit
 */
protected function validateCreditLimit(Customer $customer, float $newBalance): void
    {
        if ($customer->credit_limit <= 0) {
            return; // لا حد ائتماني مضبوط
        }

        // ✅ إصلاح المنطق: balance موجب = مبلغ مدين على العميل.
        //    (الإصلاح السابق كان يعكس الإشارة بـ abs() بشكل خاطئ)
        if ($newBalance > $customer->credit_limit) {
            throw new BusinessLogicException(
                "تجاوز الحد الائتماني. الحد: {$customer->credit_limit}، الرصيد المتوقع: {$newBalance}",
                ['customer_id' => $customer->id, 'credit_limit' => $customer->credit_limit, 'new_balance' => $newBalance]
            );
        }
    }

/**
 * Helper موحّد لمعالجة أخطاء قاعدة البيانات.
 */
protected function handleDbError(\Illuminate\Database\QueryException $e, string $action): void
{
    \Illuminate\Support\Facades\Log::error("DB error during {$action} customer", [
        'error_code' => $e->getCode(),
        'error'      => $e->getMessage(),
    ]);
    throw new RuntimeException("حدث خطأ أثناء {$action} العميل. يرجى المحاولة مرة أخرى.");
}

    /**
     * العملاء المدينين
     */
    public function getDebtors()
    {
        return Customer::where('balance', '<', 0)
            ->orderBy('balance')
            ->get();
    }

    /**
     * البحث عن عميل
     */
    public function search(string $query)
    {
        return Customer::where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('phone', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%");
        })->get();
    }

    /**
     * تحقق من عدم وجود عميل بنفس الاسم أو الهاتف
     */
    protected function ensureUniqueCustomer(string $name, ?string $phone = null, ?int $ignoreId = null): void
    {
        $query = Customer::where('name', $name);

        if ($phone) {
            $query->orWhere('phone', $phone);
        }

        if ($ignoreId) {
            $query->where('id', '<>', $ignoreId);
        }

        if ($query->exists()) {
            throw new BusinessLogicException('هذا العميل موجود بالفعل بنفس الاسم أو رقم الهاتف', ['name' => $name, 'phone' => $phone ?? null]);
        }
    }
}
