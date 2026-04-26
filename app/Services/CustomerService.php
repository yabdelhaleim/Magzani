<?php

namespace App\Services;

use App\Models\Customer;
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

            return Customer::create([
                'name'         => $data['name'],
                'phone'        => $data['phone'] ?? null,
                'email'        => $data['email'] ?? null,
                'address'      => $data['address'] ?? null,
                'balance'      => $data['balance'] ?? 0,
                'credit_limit' => $data['credit_limit'] ?? 0,
                'is_active'    => !empty($data['is_active']),
                'code'         => $data['code'] ?? uniqid('cus-'),
            ]);
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

        return $customer->fresh();
    }

    /**
     * حذف عميل
     */
    public function delete(int $customerId): bool
    {
        $customer = Customer::findOrFail($customerId);

        if ($customer->salesInvoices()->exists()) {
            throw new RuntimeException('لا يمكن حذف العميل - لديه فواتير مسجلة');
        }

        return (bool) $customer->delete();
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
        return DB::transaction(function () use ($customerId, $amount, $type) {

            if ($amount < 0) {
                throw new RuntimeException('القيمة غير صالحة');
            }

            $customer = Customer::where('id', $customerId)
                ->lockForUpdate()
                ->firstOrFail();

            $newBalance = match ($type) {
                'add'      => $customer->balance + $amount,
                'subtract' => $customer->balance - $amount,
                'set'      => $amount,
                default    => throw new RuntimeException('نوع التحديث غير صحيح'),
            };

            $this->validateCreditLimit($customer, $newBalance);

            $customer->update([
                'balance' => $newBalance
            ]);

            return $newBalance;
        });
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
            return;
        }

        $debt = $newBalance < 0 ? abs($newBalance) : 0;

        if ($debt > $customer->credit_limit) {
            throw new RuntimeException('تجاوز الحد الائتماني المسموح للعميل');
        }
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
            throw new RuntimeException('هذا العميل موجود بالفعل بنفس الاسم أو رقم الهاتف');
        }
    }
}
