<?php

namespace App\Http\Controllers;

use App\Models\SalesReturn;
use App\Models\SalesInvoice;
use App\Services\ReturnService;
use Illuminate\Http\Request;

class SalesReturnsController extends Controller
{
    public function __construct(
        private ReturnService $returnService
    ) {}

    public function index(Request $request)
    {
        // جلب البيانات من الـ Service
        $returns = $this->returnService->getSalesReturnsWithFilters($request);
        $statistics = $this->returnService->getSalesReturnsStatistics($request);

        return view('invoices.sales-returns.index', compact('returns', 'statistics'));
    }

    public function create()
    {
        // جلب الفواتير (المؤكدة والغير مدفوعة بالكامل)
        $invoices = SalesInvoice::with(['customer', 'items.product', 'returns.items'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->latest()
            ->get();

        // تجهيز البيانات للـ Blade
        $invoicesData = $invoices->map(function($inv) {
            // حساب المدفوع والمتبقي
            $total = (float) $inv->total;
            $paid = (float) ($inv->paid ?? 0);
            $remaining = max(0, $total - $paid);
            
            return [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'invoice_date' => $inv->invoice_date->format('Y-m-d'),
                'customer_name' => $inv->customer->name ?? '',
                'total' => $total,
                'paid' => $paid,
                'remaining' => $remaining,
                'items' => $inv->items->map(function($item) use ($inv) {
                    $returnedQty = $inv->returns->flatMap(function($return) {
                        return $return->items;
                    })->where('product_id', $item->product_id)->sum('quantity_returned');
                    
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name ?? '',
                        'original_quantity' => (float)$item->quantity,
                        'returned_quantity' => (float)$returnedQty,
                        'available_quantity' => (float)($item->quantity - $returnedQty),
                        'price' => (float)$item->unit_price,
                    ];
                })->values()->toArray()
            ];
        })->values()->toArray();

        return view('invoices.sales-returns.create', compact('invoices', 'invoicesData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_invoice_id' => 'required|exists:sales_invoices,id',
            'return_date' => 'nullable|date',
            'return_type' => 'nullable|in:full,partial,exchange', // ✅ نوع الإرجاع
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|max:2048',
        ]);

        try {
            $return = $this->returnService->createSalesReturn($validated);

            return redirect()
                ->route('invoices.sales-returns.show', $return->id)
                ->with('success', 'تم إنشاء مرتجع المبيعات بنجاح');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(SalesReturn $salesReturn)
    {
        $salesReturn->load(['salesInvoice.customer', 'salesInvoice.warehouse', 'items.product']);
        $salesReturn->calculated_details = $this->returnService->calculateReturnDetails($salesReturn);

        // ✅ تصحيح: show بدلاً من index
        return view('invoices.sales-returns.show', compact('salesReturn'));
    }

    public function destroy(SalesReturn $salesReturn)
    {
        try {
            $this->returnService->cancelSalesReturn($salesReturn->id);

            return redirect()
                ->route('invoices.sales-returns.index')
                ->with('success', 'تم إلغاء المرتجع بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}