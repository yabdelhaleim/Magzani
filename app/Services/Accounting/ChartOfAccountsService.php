<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/**
 * ChartOfAccountsService
 *
 * مسؤول عن إدارة دليل الحسابات:
 *  - إضافة / تعديل حسابات
 *  - بناء الشجرة الهيكلية (nested tree)
 *  - التحقق من قواعد الهيكل (leaf, parent, level)
 *  - منع حذف الحسابات ذات أرصدة أو حركات
 */
class ChartOfAccountsService
{
    /**
     * إنشاء حساب جديد
     */
    public function create(array $data): Account
    {
        return DB::transaction(function () use ($data) {
            // التحقق من الرمز الفريد
            if (Account::where('code', $data['code'])->exists()) {
                throw new RuntimeException("رمز الحساب [{$data['code']}] مستخدم بالفعل.");
            }

            // التحقق من الأب
            $parent = null;
            if (!empty($data['parent_id'])) {
                $parent = Account::findOrFail($data['parent_id']);

                // الحساب الأب يصبح غير ورقي (is_leaf = false)
                if ($parent->is_leaf) {
                    $parent->update(['is_leaf' => false]);
                }
            }

            $level = $parent ? $parent->level + 1 : 1;

            return Account::create([
                'code'            => $data['code'],
                'name_ar'         => $data['name_ar'],
                'name_en'         => $data['name_en'] ?? null,
                'account_type_id' => $data['account_type_id'],
                'parent_id'       => $data['parent_id'] ?? null,
                'level'           => $level,
                'is_leaf'         => true, // الجديد دائماً ورقي
                'is_system'       => false,
                'is_active'       => true,
                'description'     => $data['description'] ?? null,
                'created_by'      => Auth::id(),
            ]);
        });
    }

    /**
     * تعديل حساب موجود
     */
    public function update(Account $account, array $data): Account
    {
        DB::transaction(function () use ($account, $data) {
            if ($account->is_system) {
                // السماح بتعديل الاسم فقط للحسابات النظامية
                $account->update([
                    'name_ar'    => $data['name_ar']    ?? $account->name_ar,
                    'name_en'    => $data['name_en']    ?? $account->name_en,
                    'description'=> $data['description'] ?? $account->description,
                    'updated_by' => Auth::id(),
                ]);
            } else {
                // التحقق من التكرار في الرمز
                if (isset($data['code']) && $data['code'] !== $account->code) {
                    if (Account::where('code', $data['code'])->where('id', '!=', $account->id)->exists()) {
                        throw new RuntimeException("رمز الحساب [{$data['code']}] مستخدم بالفعل.");
                    }
                }

                $account->update(array_merge($data, ['updated_by' => Auth::id()]));
            }
        });

        return $account->fresh();
    }

    /**
     * حذف حساب (soft delete) — فقط إذا لم تكن هناك حركات مرتبطة
     */
    public function delete(Account $account): void
    {
        if ($account->is_system) {
            throw new RuntimeException("لا يمكن حذف الحسابات النظامية.");
        }

        if ($account->children()->exists()) {
            throw new RuntimeException("لا يمكن حذف حساب يملك حسابات فرعية.");
        }

        if ($account->lines()->exists()) {
            throw new RuntimeException("لا يمكن حذف حساب يحتوي على قيود محاسبية مسجلة.");
        }

        DB::transaction(function () use ($account) {
            $account->delete();

            // إعادة التحقق: هل الأب أصبح بلا أبناء؟ إذن نجعله ورقياً مرة أخرى
            if ($account->parent_id) {
                $parent = Account::find($account->parent_id);
                if ($parent && !$parent->children()->exists()) {
                    $parent->update(['is_leaf' => true]);
                }
            }
        });
    }

    /**
     * تفعيل / إلغاء تفعيل حساب
     */
    public function toggleActive(Account $account): Account
    {
        $account->update(['is_active' => !$account->is_active]);
        return $account->fresh();
    }

    /**
     * بناء شجرة الحسابات الهيكلية للعرض في الواجهة
     * @return Collection<Account>
     */
    public function getTree(): Collection
    {
        $accounts = Account::with(['accountType', 'children' => fn($q) => $q->with('accountType')])
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        return $accounts;
    }

    /**
     * شجرة مسطحة (flat) مع المسافات البادئة للـ select dropdown
     */
    public function getFlatList(bool $leafOnly = false): array
    {
        $query = Account::with('accountType')
            ->where('is_active', true)
            ->orderBy('code');

        if ($leafOnly) {
            $query->where('is_leaf', true);
        }

        $accounts = $query->get();
        $result   = [];

        foreach ($accounts as $account) {
            $indent  = str_repeat('— ', $account->level - 1);
            $result[] = [
                'id'           => $account->id,
                'code'         => $account->code,
                'name_ar'      => $account->name_ar,
                'name_en'      => $account->name_en,
                'display_name' => "{$indent}{$account->code} - {$account->name_ar}",
                'is_leaf'      => $account->is_leaf,
                'type'         => $account->accountType?->code,
            ];
        }

        return $result;
    }

    /**
     * جلب أنواع الحسابات
     */
    public function getAccountTypes(): Collection
    {
        return AccountType::orderBy('sort_order')->get();
    }

    /**
     * البحث عن حساب برمزه
     */
    public function findByCode(string $code): ?Account
    {
        return Account::where('code', $code)->first();
    }

    /**
     * جلب الحساب برمزه أو رمي استثناء
     */
    public function findByCodeOrFail(string $code): Account
    {
        return Account::where('code', $code)->firstOrFail();
    }
}
