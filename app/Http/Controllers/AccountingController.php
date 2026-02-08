<?php

namespace App\Http\Controllers;

use App\Services\AccountingService;
use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class AccountingController extends Controller
{
    protected AccountingService $accountingService;

    /**
     * Constructor
     */
    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Display treasury page
     *
     * @return View
     */
    public function treasury()
    {
        $cashBalance = $this->accountingService->getCashBalance();
        $bankBalance = $this->accountingService->getBankBalance();
        $todayTransactions = $this->accountingService->getTodayTransactions();
        $categories = $this->accountingService->getCategories();
        $statistics = $this->accountingService->getCashStatistics();

        // إجمالي السيولة
        $totalLiquidity = $cashBalance + $bankBalance;

        // جميع الحركات لعرضها في الجدول
        $transactions = $this->accountingService->getTodayTransactions();

        return view('accounting.treasury', compact(
            'cashBalance',
            'bankBalance',
            'todayTransactions',
            'categories',
            'statistics',
            'totalLiquidity',
            'transactions'
        ));
    }

    /**
     * Store a new deposit
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function storeDeposit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'transaction_date' => 'nullable|date|before_or_equal:today',
            'reference' => 'nullable|string|max:100',
        ], [
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'amount.max' => 'المبلغ كبير جداً',
            'transaction_date.before_or_equal' => 'التاريخ لا يمكن أن يكون في المستقبل',
        ]);

        try {
            $this->accountingService->addDeposit(
                $validated['amount'],
                $validated['description'] ?? null,
                $validated['category'] ?? null,
                $validated['transaction_date'] ?? null,
                $validated['reference'] ?? null
            );

            return redirect()->back()->with('success', 'تم إضافة الإيداع بنجاح');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Store a new withdrawal
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function storeWithdrawal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'transaction_date' => 'nullable|date|before_or_equal:today',
            'reference' => 'nullable|string|max:100',
        ], [
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'amount.max' => 'المبلغ كبير جداً',
            'transaction_date.before_or_equal' => 'التاريخ لا يمكن أن يكون في المستقبل',
        ]);

        try {
            $this->accountingService->addWithdrawal(
                $validated['amount'],
                $validated['description'] ?? null,
                $validated['category'] ?? null,
                $validated['transaction_date'] ?? null,
                $validated['reference'] ?? null
            );

            return redirect()->back()->with('success', 'تم إضافة السحب بنجاح');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Get transactions list
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = CashTransaction::with('creator')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('type')) {
            $query->where('transaction_type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('start_date')) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(20);
        $categories = $this->accountingService->getCategories();

        // Treasury summary values used by the payments dashboard
        $cashBalance = $this->accountingService->getCashBalance();
        $bankBalance = $this->accountingService->getBankBalance();
        $todayTransactions = $this->accountingService->getTodayTransactions();
        $totalLiquidity = $cashBalance + $bankBalance;

        return view('accounting.payments', compact(
            'transactions',
            'categories',
            'cashBalance',
            'bankBalance',
            'todayTransactions',
            'totalLiquidity'
        ));
    }

    /**
     * Update a transaction
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'transaction_date' => 'required|date|before_or_equal:today',
            'reference' => 'nullable|string|max:100',
        ]);

        try {
            $this->accountingService->updateTransaction($id, $validated);
            return redirect()->back()->with('success', 'تم تحديث المعاملة بنجاح');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Delete a transaction
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->accountingService->deleteTransaction($id);
            return redirect()->back()->with('success', 'تم حذف المعاملة بنجاح');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Get statistics (API endpoint)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $statistics = $this->accountingService->getCashStatistics(
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب الإحصائيات'
            ], 500);
        }
    }

    /**
     * Display expenses page
     *
     * @param Request $request
     * @return View
     */
    public function expenses(Request $request): View
    {
        // استخدام الـ scopes الموجودة في CashTransaction Model
        $query = CashTransaction::withdrawals()
            ->with('creator')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply date filters
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'today':
                    $query->today();
                    break;
                case 'month':
                    $query->thisMonth();
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->where('transaction_date', '>=', $request->start_date);
                    }
                    if ($request->filled('end_date')) {
                        $query->where('transaction_date', '<=', $request->end_date);
                    }
                    break;
            }
        }

        // Apply category filter
        if ($request->filled('type')) {
            $query->byCategory($request->type);
        }

        // Apply search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $expenses = $query->paginate(20)->withQueryString();

        // Calculate statistics using AccountingService
        $todayExpenses = CashTransaction::withdrawals()
            ->today()
            ->sum('amount');

        $monthExpenses = CashTransaction::withdrawals()
            ->thisMonth()
            ->sum('amount');

        $totalExpenses = CashTransaction::withdrawals()
            ->sum('amount');

        return view('accounting.expenses', compact(
            'expenses',
            'todayExpenses',
            'monthExpenses',
            'totalExpenses'
        ));
    }

    /**
     * Store a new expense
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function storeExpense(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|string|max:50',
            'beneficiary' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ], [
            'type.required' => 'نوع المصروف مطلوب',
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'date.required' => 'التاريخ مطلوب',
            'date.before_or_equal' => 'التاريخ لا يمكن أن يكون في المستقبل',
            'payment_method.required' => 'طريقة الدفع مطلوبة',
        ]);

        try {
            // بناء وصف شامل للمصروف
            $descriptionParts = [];
            
            // الترجمة العربية لأنواع المصروفات
            $expenseTypes = [
                'rent' => 'إيجار',
                'salaries' => 'رواتب',
                'utilities' => 'مرافق (كهرباء، مياه، غاز)',
                'maintenance' => 'صيانة',
                'supplies' => 'مستلزمات',
                'marketing' => 'تسويق ودعاية',
                'transportation' => 'مواصلات',
                'communication' => 'اتصالات',
                'insurance' => 'تأمينات',
                'taxes' => 'ضرائب ورسوم',
                'other' => 'أخرى',
            ];

            $descriptionParts[] = $expenseTypes[$validated['type']] ?? $validated['type'];

            // إضافة تفاصيل إضافية
            if (!empty($validated['beneficiary'])) {
                $descriptionParts[] = 'المستفيد: ' . $validated['beneficiary'];
            }

            // الترجمة العربية لطرق الدفع
            $paymentMethods = [
                'cash' => 'نقدي',
                'bank' => 'تحويل بنكي',
                'credit_card' => 'بطاقة ائتمان',
                'check' => 'شيك',
            ];

            $descriptionParts[] = 'طريقة الدفع: ' . ($paymentMethods[$validated['payment_method']] ?? $validated['payment_method']);

            if (!empty($validated['invoice_number'])) {
                $descriptionParts[] = 'رقم الفاتورة: ' . $validated['invoice_number'];
            }

            if (!empty($validated['notes'])) {
                $descriptionParts[] = $validated['notes'];
            }

            $description = implode(' | ', $descriptionParts);

            // استخدام AccountingService لإضافة السحب
            $this->accountingService->addWithdrawal(
                $validated['amount'],
                $description,
                $validated['type'], // category
                $validated['date'],
                $validated['invoice_number'] ?? null
            );

            return redirect()->back()->with('success', 'تم إضافة المصروف بنجاح');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Update an expense
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function updateExpense(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|string|max:50',
            'beneficiary' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ], [
            'type.required' => 'نوع المصروف مطلوب',
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'date.required' => 'التاريخ مطلوب',
            'date.before_or_equal' => 'التاريخ لا يمكن أن يكون في المستقبل',
            'payment_method.required' => 'طريقة الدفع مطلوبة',
        ]);

        try {
            // بناء وصف شامل للمصروف (نفس الطريقة)
            $descriptionParts = [];
            
            $expenseTypes = [
                'rent' => 'إيجار',
                'salaries' => 'رواتب',
                'utilities' => 'مرافق (كهرباء، مياه، غاز)',
                'maintenance' => 'صيانة',
                'supplies' => 'مستلزمات',
                'marketing' => 'تسويق ودعاية',
                'transportation' => 'مواصلات',
                'communication' => 'اتصالات',
                'insurance' => 'تأمينات',
                'taxes' => 'ضرائب ورسوم',
                'other' => 'أخرى',
            ];

            $descriptionParts[] = $expenseTypes[$validated['type']] ?? $validated['type'];

            if (!empty($validated['beneficiary'])) {
                $descriptionParts[] = 'المستفيد: ' . $validated['beneficiary'];
            }

            $paymentMethods = [
                'cash' => 'نقدي',
                'bank' => 'تحويل بنكي',
                'credit_card' => 'بطاقة ائتمان',
                'check' => 'شيك',
            ];

            $descriptionParts[] = 'طريقة الدفع: ' . ($paymentMethods[$validated['payment_method']] ?? $validated['payment_method']);

            if (!empty($validated['invoice_number'])) {
                $descriptionParts[] = 'رقم الفاتورة: ' . $validated['invoice_number'];
            }

            if (!empty($validated['notes'])) {
                $descriptionParts[] = $validated['notes'];
            }

            $description = implode(' | ', $descriptionParts);

            // استخدام AccountingService للتحديث
            $this->accountingService->updateTransaction($id, [
                'amount' => $validated['amount'],
                'description' => $description,
                'category' => $validated['type'],
                'transaction_date' => $validated['date'],
                'reference' => $validated['invoice_number'] ?? null,
            ]);

            return redirect()->back()->with('success', 'تم تحديث المصروف بنجاح');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Delete an expense
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroyExpense(int $id): RedirectResponse
    {
        try {
            // استخدام AccountingService للحذف
            $this->accountingService->deleteTransaction($id);
            
            return redirect()->back()->with('success', 'تم حذف المصروف بنجاح');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}