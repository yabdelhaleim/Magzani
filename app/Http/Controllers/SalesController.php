<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\SalesInvoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {}

    /**
     * عرض قائمة الفواتير مع الفلاتر والبحث الذكي
     */
    public function index(Request $request)
    {
        // بناء الاستعلام الأساسي
        $query = SalesInvoice::with(['customer', 'warehouse', 'items.product']);

        // 🔍 البحث الذكي - يبحث في عدة حقول
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // فلتر رقم الفاتورة
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        // فلتر العميل
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // فلتر المخزن
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // فلتر الحالة
        if ($request->filled('status')) {
            $status = $request->status;
            
            if ($status === 'paid') {
                $query->where('payment_status', 'paid')
                      ->where('status', '!=', 'cancelled');
            } elseif ($status === 'pending') {
                $query->whereIn('payment_status', ['unpaid', 'partial'])
                      ->where('status', '!=', 'cancelled');
            } elseif ($status === 'cancelled') {
                $query->where('status', 'cancelled');
            }
        }

        // فلتر التاريخ من
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        // فلتر التاريخ إلى
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        // فلتر نطاق المبلغ
        if ($request->filled('amount_from')) {
            $query->where('total', '>=', $request->amount_from);
        }

        if ($request->filled('amount_to')) {
            $query->where('total', '<=', $request->amount_to);
        }

        // الترتيب
        $sortBy = $request->get('sort_by', 'invoice_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // جلب الفواتير مع الترقيم
        $invoices = $query->paginate(20)->withQueryString();

        // حساب التفاصيل لكل فاتورة
        foreach ($invoices as $invoice) {
            $invoice->calculated_details = $this->invoiceService->calculateInvoiceDetails($invoice);
        }

        // حساب الإحصائيات
        $statistics = $this->calculateStatistics($request);

        // جلب البيانات للفلاتر
        $customers = Customer::where('is_active', 1)->get();
        $warehouses = Warehouse::where('is_active', 1)->get();

        return view('invoices.sales.index', compact('invoices', 'statistics', 'customers', 'warehouses'));
    }

    /**
     * حساب الإحصائيات
     */
    private function calculateStatistics(Request $request): array
    {
        $baseQuery = SalesInvoice::query();

        // تطبيق نفس الفلاتر
        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('customer_id')) {
            $baseQuery->where('customer_id', $request->customer_id);
        }

        if ($request->filled('warehouse_id')) {
            $baseQuery->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('date_from')) {
            $baseQuery->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $baseQuery->whereDate('invoice_date', '<=', $request->date_to);
        }

        $allInvoices = (clone $baseQuery)->with('items')->get();
        
        $stats = [
            'total_count' => 0,
            'paid_count' => 0,
            'pending_count' => 0,
            'cancelled_count' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
            'remaining_amount' => 0,
            'today_count' => 0,
            'today_amount' => 0,
            'this_month_count' => 0,
            'this_month_amount' => 0,
        ];

        foreach ($allInvoices as $invoice) {
            $details = $this->invoiceService->calculateInvoiceDetails($invoice);
            
            if ($invoice->status === 'cancelled') {
                $stats['cancelled_count']++;
            } else {
                $stats['total_count']++;
                $stats['total_amount'] += $details['net_total'];
                $stats['paid_amount'] += $details['paid'];
                $stats['remaining_amount'] += $details['remaining'];
                
                if ($invoice->payment_status === 'paid') {
                    $stats['paid_count']++;
                } else {
                    $stats['pending_count']++;
                }

                // إحصائيات اليوم
                if ($invoice->invoice_date->isToday()) {
                    $stats['today_count']++;
                    $stats['today_amount'] += $details['net_total'];
                }

                // إحصائيات الشهر
                if ($invoice->invoice_date->isCurrentMonth()) {
                    $stats['this_month_count']++;
                    $stats['this_month_amount'] += $details['net_total'];
                }
            }
        }

        // تطبيق فلتر الحالة على الإحصائيات
        if ($request->filled('status')) {
            $statusFilter = $request->status;
            
            if ($statusFilter === 'cancelled') {
                $stats['total_count'] = $stats['cancelled_count'];
                $stats['paid_count'] = 0;
                $stats['pending_count'] = 0;
            } elseif ($statusFilter === 'paid') {
                $stats['total_count'] = $stats['paid_count'];
                $stats['pending_count'] = 0;
                $stats['cancelled_count'] = 0;
            } elseif ($statusFilter === 'pending') {
                $stats['total_count'] = $stats['pending_count'];
                $stats['paid_count'] = 0;
                $stats['cancelled_count'] = 0;
            }
        }

        // تقريب المبالغ
        foreach (['total_amount', 'paid_amount', 'remaining_amount', 'today_amount', 'this_month_amount'] as $key) {
            $stats[$key] = round($stats[$key], 2);
        }

        return $stats;
    }

    /**
     * ✅ عرض صفحة إنشاء فاتورة - FIXED
     */
    public function create()
    {
        $customers = Customer::where('is_active', 1)->get();
        $warehouses = Warehouse::where('is_active', 1)->get();
        
        // ✅ جلب المنتجات مع الوحدات والمخزون والوحدة الأساسية - متوافق مع Product Model
        $products = Product::active()
            ->with([
                'baseunit',
                'basePricing', // جلب السعر الحالي
                'activeSellingUnits' => function($q) {
                    $q->ordered(); // مرتبة حسب display_order
                },
                'warehouses' => function($q) {
                    $q->where('warehouses.is_active', true);
                }
            ])
            ->get();
        
        return view('invoices.sales.create', compact('customers', 'warehouses', 'products'));
    }

    /**
     * ✅ حفظ الفاتورة - FIXED
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'invoice_date' => 'nullable|date',
                'discount_value' => 'nullable|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'shipping_cost' => 'nullable|numeric|min:0',
                'other_charges' => 'nullable|numeric|min:0',
                'paid' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.selling_unit_id' => 'nullable|exists:product_selling_units,id',
                'items.*.quantity' => 'required|numeric|min:0.001',
                'items.*.price' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:100',
                'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            ]);

            // ✅ استخدام InvoiceService لإنشاء الفاتورة
            $invoice = $this->invoiceService->createSalesInvoice($validated);
            
            return redirect()
                ->route('invoices.sales.show', $invoice->id)
                ->with('success', '✅ تم إنشاء فاتورة المبيعات بنجاح');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            return back()
                ->with('error', '❌ حدث خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض تفاصيل الفاتورة
     */
    public function show($id)
    {
        $invoice = SalesInvoice::with([
                'customer',
                'warehouse',
                'items.product',
                'items.sellingUnit',
                'payments'
            ])
            ->findOrFail($id);
        
        $invoice->calculated_details = $this->invoiceService->calculateInvoiceDetails($invoice);
        
        return view('invoices.sales.show', compact('invoice'));
    }

    /**
     * ✅ عرض صفحة التعديل - FIXED
     */
    public function edit($id)
    {
        $invoice = SalesInvoice::with(['items.product', 'items.sellingUnit'])->findOrFail($id);
        
        // منع التعديل للفواتير الملغاة أو المكتملة
        if ($invoice->status === 'cancelled') {
            return redirect()
                ->route('invoices.sales.index')
                ->with('error', '❌ لا يمكن تعديل فاتورة ملغاة');
        }

        if ($invoice->payment_status === 'paid') {
            return redirect()
                ->route('invoices.sales.show', $invoice->id)
                ->with('error', '❌ لا يمكن تعديل فاتورة مكتملة (مدفوعة بالكامل)');
        }
        
        $invoice->calculated_details = $this->invoiceService->calculateInvoiceDetails($invoice);
        
        $customers = Customer::where('is_active', 1)->get();
        $warehouses = Warehouse::where('is_active', 1)->get();
        
        // ✅ جلب المنتجات - نفس طريقة create
        $products = Product::active()
            ->with([
                'baseunit',
                'basePricing',
                'activeSellingUnits' => function($q) {
                    $q->ordered();
                },
                'warehouses' => function($q) {
                    $q->where('warehouses.is_active', true);
                }
            ])
            ->get();
        
        return view('invoices.sales.edit', compact('invoice', 'customers', 'warehouses', 'products'));
    }

    /**
     * تحديث الفاتورة
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'invoice_date' => 'nullable|date',
                'discount_value' => 'nullable|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'shipping_cost' => 'nullable|numeric|min:0',
                'other_charges' => 'nullable|numeric|min:0',
                'paid' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.selling_unit_id' => 'nullable|exists:product_selling_units,id',
                'items.*.quantity' => 'required|numeric|min:0.001',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0|max:100',
                'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            ]);

            $invoice = $this->invoiceService->updateSalesInvoice($id, $validated);
            
            return redirect()
                ->route('invoices.sales.show', $invoice->id)
                ->with('success', '✅ تم تحديث الفاتورة بنجاح');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            return back()
                ->with('error', '❌ حدث خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * حذف/إلغاء الفاتورة
     */
    public function destroy($id)
    {
        try {
            $this->invoiceService->cancelSalesInvoice($id);
            
            return redirect()
                ->route('invoices.sales.index')
                ->with('success', '⚠️ تم إلغاء الفاتورة بنجاح');

        } catch (\Exception $e) {
            return back()
                ->with('error', '❌ حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * ✅ API: البحث عن المنتجات بالوحدات - FIXED
     */
    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');
        $warehouseId = $request->get('warehouse_id');

        $products = Product::active()
            ->with(['activeSellingUnits', 'warehouses', 'basePricing'])
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('code', 'like', '%' . $search . '%')
                      ->orWhere('sku', 'like', '%' . $search . '%');
            })
            ->limit(20)
            ->get()
            ->map(function ($product) use ($warehouseId) {
                $stock = 0;
                
                if ($warehouseId) {
                    $productWarehouse = $product->warehouses->firstWhere('id', $warehouseId);
                    $stock = $productWarehouse ? $productWarehouse->pivot->quantity : 0;
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code ?? $product->sku,
                    'base_price' => $product->base_selling_price,
                    'stock' => $stock,
                    'base_unit' => $product->base_unit_label ?? 'قطعة',
                    'selling_units' => $product->activeSellingUnits->map(function($unit) use ($product) {
                        return [
                            'id' => $unit->id,
                            'unit_name' => $unit->unit_name,
                            'conversion_factor' => $unit->quantity_in_base_unit,
                            'selling_price' => round($product->base_selling_price * $unit->quantity_in_base_unit, 2),
                            'purchase_price' => round($product->base_purchase_price * $unit->quantity_in_base_unit, 2),
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
}