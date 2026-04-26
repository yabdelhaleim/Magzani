<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Requests\SupplierRequest;
use App\Services\SupplierService;
use App\Exports\SupplierStatementExport;
use App\Exports\SuppliersExport;
use Maatwebsite\Excel\Facades\Excel;

class SupplierController extends Controller
{
    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * عرض قائمة الموردين
     */
    public function index(Request $request)
    {
        $suppliers = $this->supplierService->getSuppliers($request->all());
        $statistics = $this->supplierService->getStatistics();

        return view('suppliers.index', compact('suppliers', 'statistics'));
    }

    /**
     * عرض صفحة إضافة مورد جديد
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * حفظ مورد جديد
     */
    public function store(SupplierRequest $request)
    {
        try {
            $supplier = $this->supplierService->create($request->validated());

            return redirect()
                ->route('suppliers.show', $supplier->id)
                ->with('success', 'تم إنشاء المورد بنجاح');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء المورد: ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل مورد
     */
    public function show(Supplier $supplier)
    {
        $summary = $this->supplierService->getFinancialSummary($supplier);

        // أحدث الفواتير
        $recentInvoices = $supplier->purchaseInvoices()
            ->with('warehouse')
            ->latest('invoice_date')
            ->limit(5)
            ->get();

        // أحدث المدفوعات
        $recentPayments = $supplier->payments()
            ->latest('payment_date')
            ->limit(5)
            ->get();

        return view('suppliers.show', compact('supplier', 'summary', 'recentInvoices', 'recentPayments'));
    }

    /**
     * عرض صفحة تعديل مورد
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * تحديث بيانات مورد
     */
    public function update(SupplierRequest $request, Supplier $supplier)
    {
        try {
            $this->supplierService->update($supplier, $request->validated());

            return redirect()
                ->route('suppliers.show', $supplier->id)
                ->with('success', 'تم تحديث بيانات المورد بنجاح');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث المورد: ' . $e->getMessage());
        }
    }

    /**
     * حذف مورد
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $this->supplierService->delete($supplier);

            return redirect()
                ->route('suppliers.index')
                ->with('success', 'تم حذف المورد بنجاح');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'حدث خطأ أثناء حذف المورد: ' . $e->getMessage());
        }
    }

    /**
     * عرض كشف حساب المورد
     */
    public function statement(Request $request, Supplier $supplier)
    {
        $statement = $this->supplierService->getStatement($supplier, $request->all());
        $summary = $this->supplierService->getFinancialSummary($supplier);
        $company = \App\Models\Company::first();

        return view('suppliers.statement', compact('supplier', 'statement', 'summary', 'company'));
    }

    /**
     * تصدير كشف حساب المورد إلى Excel
     */
    public function exportStatement(Request $request, Supplier $supplier)
    {
        $filters = $request->only(['date_from', 'date_to', 'type']);

        return Excel::download(
            new SupplierStatementExport($supplier, $filters),
            'statement-supplier-' . $supplier->code . '-' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * تفعيل/إيقاف مورد
     */
    public function toggleStatus(Supplier $supplier)
    {
        try {
            $this->supplierService->toggleStatus($supplier);

            $status = $supplier->is_active ? 'تم تفعيل' : 'تم إيقاف';

            return back()->with('success', "{$status} المورد بنجاح");
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * تصدير قائمة الموردين لـ Excel
     */
    public function export(Request $request)
    {
        return Excel::download(
            new SuppliersExport($request),
            'suppliers-' . date('Y-m-d') . '.xlsx'
        );
    }
}
