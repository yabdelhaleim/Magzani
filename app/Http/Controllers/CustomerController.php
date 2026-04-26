<?php

namespace App\Http\Controllers;

use App\Services\CustomerService;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Exports\CustomerStatementExport;
use App\Exports\CustomersExport;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerService $customerService
    ) {}

    /**
     * عرض قائمة العملاء
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // Apply search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply filters
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('type')) {
            $query->where('customer_type', $request->type);
        }

        $customers = $query->latest()->paginate(20);

        return view('customers.index', compact('customers'));
    }

    /**
     * تصدير قائمة العملاء إلى Excel
     */
    public function export(Request $request)
    {
        return Excel::download(
            new CustomersExport($request),
            'customers-' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * عرض صفحة إنشاء عميل جديد
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * حفظ عميل جديد
     */
    public function store(Request $request)
    {
        try {
            $customer = $this->customerService->create($request->all());
            return redirect()->route('customers.index')
                ->with('success', 'تم إنشاء العميل بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * عرض تفاصيل العميل
     */
    public function show($id)
    {
        $customer = Customer::with('salesInvoices')->findOrFail($id);
        return view('customers.show', compact('customer'));
    }

    /**
     * عرض صفحة تعديل العميل
     */
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    /**
     * تحديث بيانات العميل
     */
    public function update(Request $request, $id)
    {
        try {
            $customer = $this->customerService->update($id, $request->all());
            return redirect()->route('customers.index')
                ->with('success', 'تم تحديث العميل بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * حذف العميل
     */
    public function destroy($id)
    {
        try {
            $this->customerService->delete($id);
            return redirect()->route('customers.index')
                ->with('success', 'تم حذف العميل بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * عرض كشف حساب العميل
     */
    public function statement(Request $request, $id)
    {
        $customer = Customer::with('salesInvoices')->findOrFail($id);
        
        // Apply filters to the relationship
        $query = $customer->salesInvoices();
        
        if ($request->date_from) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }
        
        if ($request->status) {
            $query->where('payment_status', $request->status);
        }
        
        $filteredInvoices = $query->orderBy('invoice_date', 'asc')->get();
        
        // Replace the relation with filtered results
        $customer->setRelation('salesInvoices', $filteredInvoices);

        return view('customers.statement', compact('customer'));
    }

    /**
     * تصدير كشف حساب العميل إلى Excel
     */
    public function exportStatement(Request $request, $id)
    {
        $customer = Customer::with('salesInvoices')->findOrFail($id);
        
        $filters = $request->only(['date_from', 'date_to', 'status']);
        
        return Excel::download(
            new CustomerStatementExport($customer, $filters),
            'statement-customer-' . $customer->code . '-' . date('Y-m-d') . '.xlsx'
        );
    }
}
