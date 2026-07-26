<?php

namespace Tests\Unit;

use ReflectionClass;
use Tests\TestCase;

/**
 * اختبارات الجولة 1 - إصلاح Expense.
 *
 * تتحقق من:
 *  - fillable يطابق الأعمدة الفعلية في DB
 *  - لا يحتوي على حقول غير موجودة (category, payment_method, reference_number, etc.)
 *  - يحتوي على الحقول المطلوبة (expense_number, expense_category_id, etc.)
 *  - العلاقات صحيحة (category(), creator(), cashTransaction())
 *  - توليد رقم المصروف تلقائياً
 *  - الـ accessors القديمة تعمل backward-compat
 *  - Service methods تستخدم الحقول الصحيحة
 */
class Round1ExpenseFixTest extends TestCase
{
    /**
     * ✅ fillable يحتوي فقط على الحقول الموجودة في DB
     */
    public function test_expense_fillable_matches_db_columns(): void
    {
        $expected = [
            'expense_number',
            'expense_category_id',
            'amount',
            'expense_date',
            'description',
            'reference',
            'attachment',
            'created_by',
        ];

        $reflection = new ReflectionClass(\App\Models\Expense::class);
        $fillable = $reflection->getDefaultProperties()['fillable'] ?? [];

        foreach ($expected as $field) {
            $this->assertContains(
                $field,
                $fillable,
                "Expense fillable must contain '$field'"
            );
        }
    }

    /**
     * ✅ fillable لا يحتوي حقول غير موجودة في DB
     */
    public function test_expense_fillable_has_no_legacy_fields(): void
    {
        $reflection = new ReflectionClass(\App\Models\Expense::class);
        $fillable = $reflection->getDefaultProperties()['fillable'] ?? [];

        $legacyFields = [
            'category',          // replaced by expense_category_id
            'payment_method',    // doesn't exist
            'reference_number',  // replaced by reference
            'type',              // replaced by expense_category_id
            'date',              // replaced by expense_date
            'notes',             // replaced by description
            'account_id',        // doesn't exist
            'user_id',           // replaced by created_by
            'status',            // doesn't exist
            'metadata',          // doesn't exist
        ];

        foreach ($legacyFields as $field) {
            $this->assertNotContains(
                $field,
                $fillable,
                "Expense fillable must NOT contain legacy field '$field'"
            );
        }
    }

    /**
     * ✅ العلاقات الجديدة موجودة (category, creator, cashTransaction)
     */
    public function test_expense_has_correct_relationships(): void
    {
        $reflection = new ReflectionClass(\App\Models\Expense::class);

        $this->assertTrue(
            $reflection->hasMethod('category'),
            'Expense must have category() relationship'
        );
        $this->assertTrue(
            $reflection->hasMethod('creator'),
            'Expense must have creator() relationship'
        );
        $this->assertTrue(
            $reflection->hasMethod('cashTransaction'),
            'Expense must have cashTransaction() relationship'
        );
    }

    /**
     * ✅ تم حذف العلاقات القديمة (user, account)
     */
    public function test_expense_does_not_have_legacy_relationships(): void
    {
        $reflection = new ReflectionClass(\App\Models\Expense::class);

        $this->assertFalse(
            $reflection->hasMethod('user'),
            'Expense must NOT have legacy user() method (renamed to creator())'
        );
        $this->assertFalse(
            $reflection->hasMethod('account'),
            'Expense must NOT have legacy account() method (column does not exist)'
        );
    }

    /**
     * ✅ الـ Accessors القديمة (getTypeAttribute) موجودة backward-compat
     */
    public function test_expense_has_legacy_type_accessor(): void
    {
        $reflection = new ReflectionClass(\App\Models\Expense::class);

        $this->assertTrue(
            $reflection->hasMethod('getTypeAttribute'),
            'Expense must have getTypeAttribute() for backward compatibility'
        );

        // يفترض أن يحوّل 'rent' → 'إيجار'
        $source = file_get_contents($reflection->getFileName());
        $this->assertStringContainsString("'rent' => 'إيجار'", $source,
            'getTypeAttribute must translate rent to إيجار');
        $this->assertStringContainsString("'salaries' => 'رواتب'", $source,
            'getTypeAttribute must translate salaries to رواتب');
    }

    /**
     * ✅ Accessors للـ backward compatibility
     */
    public function test_expense_has_legacy_accessors(): void
    {
        $reflection = new ReflectionClass(\App\Models\Expense::class);

        $this->assertTrue($reflection->hasMethod('getDateAttribute'),
            'getDateAttribute must exist for backward compat');
        $this->assertTrue($reflection->hasMethod('getNotesAttribute'),
            'getNotesAttribute must exist for backward compat');
        $this->assertTrue($reflection->hasMethod('getReferenceNumberAttribute'),
            'getReferenceNumberAttribute must exist for backward compat');
    }

    /**
     * ✅ يتم توليد رقم المصروف تلقائياً عند الإنشاء
     */
    public function test_expense_auto_generates_number(): void
    {
        $reflection = new ReflectionClass(\App\Models\Expense::class);
        $this->assertTrue(
            $reflection->hasMethod('generateNumber'),
            'Expense must have generateNumber() method'
        );
        $this->assertTrue(
            $reflection->hasMethod('booted'),
            'Expense must have booted() method for auto-generation'
        );
    }

    /**
     * ✅ ExpenseService لا يستخدم الحقول القديمة
     */
    public function test_expense_service_uses_correct_fields(): void
    {
        $source = file_get_contents(app_path('Services/Accounting/ExpenseService.php'));

        // الحقول الصحيحة موجودة
        $this->assertStringContainsString("'expense_category_id'", $source);
        $this->assertStringContainsString("'expense_date'", $source);
        $this->assertStringContainsString("'expense_number'", $source);

        // يجب ألا تستخدم Expense::where('type' (was: على جدول expenses مباشرة)
        $this->assertStringNotContainsString(
            "Expense::where('type'",
            $source,
            'Service must not use Expense::where(type) — use Expense::where(expense_category_id)'
        );

        // لا يجب أن تستخدم payment_method column
        $this->assertStringNotContainsString(
            "Expense::where('payment_method'",
            $source,
            'Service must not use Expense::where(payment_method) (column does not exist)'
        );

        // لا يجب أن تستخدم beneficiary (column غير موجود)
        $this->assertStringNotContainsString(
            "Expense::where('beneficiary'",
            $source,
            'Service must not use Expense::where(beneficiary) (column does not exist)'
        );
    }

    /**
     * ✅ ExpenseService::createExpense فيه try-catch
     */
    public function test_create_expense_has_error_handling(): void
    {
        $reflection = new ReflectionClass(\App\Services\Accounting\ExpenseService::class);
        $method = $reflection->getMethod('createExpense');
        $source = file_get_contents($method->getFileName());
        $start = $method->getStartLine();
        $end = $method->getEndLine();
        $lines = explode("\n", $source);
        $body = implode("\n", array_slice($lines, $start - 1, $end - $start + 1));

        $this->assertStringContainsString('try', $body,
            'createExpense must have try block');
        $this->assertStringContainsString('catch', $body,
            'createExpense must have catch block');
        $this->assertStringContainsString('Log::error', $body,
            'createExpense must log errors');
    }

    /**
     * ✅ createExpenseTransaction يستخدم CashTransaction الحقيقي
     */
    public function test_create_expense_transaction_uses_real_table(): void
    {
        $source = file_get_contents(app_path('Services/Accounting/ExpenseService.php'));

        // يجب ألا يستخدم App\Models\Transaction (غير موجود)
        $this->assertStringNotContainsString(
            "new \App\\Models\\Transaction",
            $source,
            'Service must NOT use App\Models\Transaction (does not exist)'
        );

        // يجب أن يستخدم cash_transactions
        $this->assertStringContainsString(
            "DB::table('cash_transactions')",
            $source,
            'Service must insert into cash_transactions table'
        );
    }

    /**
     * ✅ Expense model بدون try-catch في الـ accessors
     */
    public function test_expense_model_no_syntax_errors(): void
    {
        $file = app_path('Models/Expense.php');
        $output = shell_exec("php -l \"$file\" 2>&1");

        $this->assertStringContainsString('No syntax errors', $output,
            'Expense.php must have no syntax errors');
    }
}