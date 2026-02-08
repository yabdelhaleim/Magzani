<?php

namespace App\Http\Controllers;

use App\Models\SupplierPayment;
use App\Http\Requests\SupplierPaymentRequest;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    /**
     * عرض قائمة سدادات مورد معين
     */
    public function index(Request $request)
    {
        $query = SupplierPayment::with('supplier');

        // تصفية حسب المورد
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // تصفية حسب التاريخ
        if ($request->has('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        $payments = $query->latest('payment_date')->paginate(20);

        return view('suppliers.index', compact('payments'));
    }

    /**
     * تسجيل سداد جديد لمورد
     */
    public function store(SupplierPaymentRequest $request)
    {
        try {
            $payment = SupplierPayment::create($request->validated());

            return back()->with('success', 'تم تسجيل السداد بنجاح');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تسجيل السداد: ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل سداد معين
     */
    public function show(SupplierPayment $payment)
    {
        $payment->load('supplier');
        
        return view('suppliers.show', compact('payment'));
    }

    /**
     * تحديث بيانات سداد
     */
    public function update(SupplierPaymentRequest $request, SupplierPayment $payment)
    {
        try {
            $payment->update($request->validated());

            return back()->with('success', 'تم تحديث السداد بنجاح');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث السداد: ' . $e->getMessage());
        }
    }

    /**
     * حذف سداد
     */
    public function destroy(SupplierPayment $payment)
    {
        try {
            $payment->delete();

            return back()->with('success', 'تم حذف السداد بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حذف السداد: ' . $e->getMessage());
        }
    }
}