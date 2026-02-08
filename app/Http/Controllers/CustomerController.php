<?php
namespace App\Http\Controllers;

use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerService $customerService
    ) {}

    public function index()
    {
        $customers = \App\Models\Customer::latest()->paginate(20);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

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

    public function show($id)
    {
        $customer = \App\Models\Customer::with('salesInvoices')->findOrFail($id);
        return view('customers.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = \App\Models\Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

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

    public function statement($id)
    {
        $customer = \App\Models\Customer::with('salesInvoices')->findOrFail($id);
        return view('customers.statement', compact('customer'));
    }
}