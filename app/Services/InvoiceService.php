<?php

namespace App\Services;

use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\Product;
use App\Models\ProductSellingUnit;
use App\Models\ProductPurchaseUnit;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use RuntimeException;

class InvoiceService
{
    public function __construct(
        private ProductService $productService,
        private CustomerService $customerService,
        private SupplierService $supplierService
    ) {}

    /* =====================================================================
     * 📊 SALES INVOICES - QUERIES
     * ===================================================================== */

    /**
     * ✅ جلب فواتير المبيعات مع الفلاتر - محسّن للأداء
     */
    public function getSalesInvoicesWithFilters(Request $request)
    {
        $query = SalesInvoice::query();
        
        // تطبيق الفلاتر
        $this->applySalesFiltersToQuery($query, $request);
        
        // الترتيب
        $sortBy = $request->input('sort_by', 'invoice_date');
        $sortOrder = $request->input('sort_order', 'desc');
        
        // ✅ التحقق من أن العمود موجود
        $allowedSortColumns = [
            'invoice_number', 'invoice_date', 'total', 
            'paid', 'created_at', 'customer_id', 'warehouse_id'
        ];
        
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'invoice_date';
        }
        
        $query->orderBy($sortBy, $sortOrder);
        
        // ✅ Eager loading محسّن - جلب الأعمدة الضرورية فقط
        $invoices = $query->with([
                'customer:id,name,phone,balance',
                'warehouse:id,name,code',
                'createdBy:id,name',
            ])
            ->select([
                'id', 'invoice_number', 'invoice_date', 'due_date',
                'customer_id', 'warehouse_id',
                'subtotal', 'discount_amount', 'tax_amount', 
                'shipping_cost', 'other_charges',
                'total', 'paid', 
                'status', 'payment_status',
                'created_by', 'created_at', 'updated_at'
            ])
            ->paginate($request->input('per_page', 20));
        
        // ✅ حساب الحقول المشتقة بكفاءة
        $invoices->getCollection()->transform(function ($invoice) {
            $invoice->remaining = round($invoice->total - $invoice->paid, 2);
            return $invoice;
        });
        
        return $invoices;
    }

    /**
     * ✅ تطبيق الفلاتر على Query - DRY Principle
     */
    private function applySalesFiltersToQuery($query, Request $request): void
    {
        // 🔍 البحث الذكي
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
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
        
        // فلتر حالة الدفع المباشرة
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // فلتر التاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }
        
        // فلتر المبلغ
        if ($request->filled('amount_from')) {
            $query->where('total', '>=', $request->amount_from);
        }
        
        if ($request->filled('amount_to')) {
            $query->where('total', '<=', $request->amount_to);
        }
    }

    /**
     * ✅ حساب إحصائيات المبيعات - محسّن بـ SQL Aggregation
     */
    public function getSalesStatisticsWithFilters(Request $request): array
    {
        $baseQuery = SalesInvoice::query();
        
        // تطبيق نفس الفلاتر
        $this->applySalesFiltersToQuery($baseQuery, $request);
        
        // ==================== حساب الإحصائيات بـ SQL مباشرة ====================
        
        // الفواتير النشطة (غير الملغاة)
        $activeQuery = (clone $baseQuery)->where('status', '!=', 'cancelled');
        
        // ✅ استخدام DB::raw للحسابات المعقدة
        $aggregates = $activeQuery->selectRaw('
            COUNT(*) as total_count,
            SUM(CASE WHEN payment_status = "paid" THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN payment_status IN ("unpaid", "partial") THEN 1 ELSE 0 END) as pending_count,
            SUM(total) as total_amount,
            SUM(paid) as paid_amount,
            SUM(total - paid) as remaining_amount,
            SUM(discount_amount) as total_discount,
            SUM(tax_amount) as total_tax
        ')->first();
        
        // الفواتير الملغاة
        $cancelledCount = (clone $baseQuery)->where('status', 'cancelled')->count();
        
        // إحصائيات اليوم
        $todayQuery = (clone $activeQuery)->whereDate('invoice_date', today());
        $todayStats = $todayQuery->selectRaw('
            COUNT(*) as today_count,
            COALESCE(SUM(total), 0) as today_amount
        ')->first();
        
        // إحصائيات الشهر
        $monthQuery = (clone $activeQuery)
            ->whereYear('invoice_date', now()->year)
            ->whereMonth('invoice_date', now()->month);
        
        $monthStats = $monthQuery->selectRaw('
            COUNT(*) as month_count,
            COALESCE(SUM(total), 0) as month_amount
        ')->first();
        
        // ✅ حساب الربح بكفاءة
        $totalProfit = $this->calculateSalesTotalProfit($activeQuery);
        
        $stats = [
            'total_count' => (int) $aggregates->total_count,
            'paid_count' => (int) $aggregates->paid_count,
            'pending_count' => (int) $aggregates->pending_count,
            'cancelled_count' => $cancelledCount,
            
            'total_amount' => round((float) $aggregates->total_amount, 2),
            'paid_amount' => round((float) $aggregates->paid_amount, 2),
            'remaining_amount' => round((float) $aggregates->remaining_amount, 2),
            'total_discount' => round((float) $aggregates->total_discount, 2),
            'total_tax' => round((float) $aggregates->total_tax, 2),
            
            'today_count' => (int) $todayStats->today_count,
            'today_amount' => round((float) $todayStats->today_amount, 2),
            
            'month_count' => (int) $monthStats->month_count,
            'month_amount' => round((float) $monthStats->month_amount, 2),
            
            'total_profit' => $totalProfit,
        ];
        
        // تطبيق فلتر الحالة على الإحصائيات
        if ($request->filled('status')) {
            $stats = $this->applyStatusFilterToStats($stats, $request->status);
        }
        
        return $stats;
    }

    /**
     * ✅ حساب الربح الإجمالي بكفاءة
     */
    private function calculateSalesTotalProfit($query): float
    {
        try {
            // ✅ استخدام JOIN للحصول على الربح مباشرة من SQL
            $profit = DB::table('sales_invoices as si')
                ->join('sales_invoice_items as sii', 'si.id', '=', 'sii.sales_invoice_id')
                ->join('products as p', 'sii.product_id', '=', 'p.id')
                ->join('product_selling_units as psu', 'sii.selling_unit_id', '=', 'psu.id')
                ->whereIn('si.id', $query->pluck('id'))
                ->where('si.status', '!=', 'cancelled')
                ->selectRaw('
                    SUM(
                        (sii.quantity * sii.price) - 
                        (sii.quantity * COALESCE(p.purchase_price, 0) * psu.conversion_factor) - 
                        COALESCE(sii.discount, 0)
                    ) as total_profit
                ')
                ->value('total_profit');
            
            return round($profit ?? 0, 2);
            
        } catch (\Exception $e) {
            // في حالة الخطأ، نرجع 0
            \Log::error('Error calculating profit: ' . $e->getMessage());
            return 0.00;
        }
    }

    /**
     * ✅ تطبيق فلتر الحالة على الإحصائيات
     */
    private function applyStatusFilterToStats(array $stats, string $status): array
    {
        if ($status === 'cancelled') {
            return [
                'total_count' => $stats['cancelled_count'],
                'paid_count' => 0,
                'pending_count' => 0,
                'cancelled_count' => $stats['cancelled_count'],
                'total_amount' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'total_discount' => 0,
                'total_tax' => 0,
                'today_count' => 0,
                'today_amount' => 0,
                'month_count' => 0,
                'month_amount' => 0,
                'total_profit' => 0,
            ];
        } elseif ($status === 'paid') {
            return array_merge($stats, [
                'total_count' => $stats['paid_count'],
                'pending_count' => 0,
                'cancelled_count' => 0,
            ]);
        } elseif ($status === 'pending') {
            return array_merge($stats, [
                'total_count' => $stats['pending_count'],
                'paid_count' => 0,
                'cancelled_count' => 0,
            ]);
        }
        
        return $stats;
    }

    /**
     * ✅ حساب تفاصيل فاتورة واحدة
     */
    public function calculateInvoiceDetails(SalesInvoice $invoice): array
    {
        // ✅ استخدام القيم المحفوظة في DB أولاً
        $subtotal = $invoice->subtotal ?? 0;
        $totalDiscount = $invoice->discount_amount ?? 0;
        $totalTax = $invoice->tax_amount ?? 0;
        $shippingCost = $invoice->shipping_cost ?? 0;
        $otherCharges = $invoice->other_charges ?? 0;
        
        // الإجمالي النهائي
        $netTotal = $invoice->total;
        $paid = $invoice->paid ?? 0;
        $remaining = $netTotal - $paid;
        
        // تحديد الحالة
        $displayStatus = 'unknown';
        if ($invoice->status === 'cancelled') {
            $displayStatus = 'cancelled';
        } elseif ($invoice->payment_status === 'paid') {
            $displayStatus = 'paid';
        } else {
            $displayStatus = 'pending';
        }
        
        return [
            'subtotal' => round($subtotal, 2),
            'total_discount' => round($totalDiscount, 2),
            'total_tax' => round($totalTax, 2),
            'shipping_cost' => round($shippingCost, 2),
            'other_charges' => round($otherCharges, 2),
            'net_total' => round($netTotal, 2),
            'paid' => round($paid, 2),
            'remaining' => round($remaining, 2),
            'payment_status' => $invoice->payment_status,
            'display_status' => $displayStatus,
            'items_count' => $invoice->items->count(),
        ];
    }

    /**
     * ✅ جلب فاتورة واحدة مع التفاصيل
     */
    public function getSalesInvoiceWithDetails(int $invoiceId): SalesInvoice
    {
        return SalesInvoice::with([
                'customer:id,name,phone,email,address,balance,credit_limit',
                'warehouse:id,name,code,address',
                'items' => function($query) {
                    $query->select([
                        'id', 'sales_invoice_id', 'product_id', 'selling_unit_id',
                        'quantity', 'base_quantity', 'unit_code', 'conversion_factor',
                        'price', 'discount', 'tax', 'subtotal', 'total'
                    ]);
                },
                'items.product:id,name,sku,barcode,purchase_price',
                'items.sellingUnit:id,product_id,unit_name,unit_code,conversion_factor',
                'payments:id,invoice_id,amount,payment_method,payment_date,reference',
                'createdBy:id,name',
                'confirmedBy:id,name',
            ])
            ->findOrFail($invoiceId);
    }

    /* =====================================================================
     * 💰 CREATE SALES INVOICE
     * ===================================================================== */

    /**
     * ✅ إنشاء فاتورة مبيعات - محسّنة للأداء والأمان
     */
    public function createSalesInvoice(array $data): SalesInvoice
    {
        return DB::transaction(function () use ($data) {
            
            // ==================== 1️⃣ جلب البيانات مرة واحدة ====================
            $productIds = array_column($data['items'], 'product_id');
            // بعض العناصر قد لا تحتوي selling_unit_id (أو تكون قيمة فارغة)
            $unitIds = [];
            foreach ($data['items'] as $it) {
                if (!empty($it['selling_unit_id'])) {
                    $unitIds[] = $it['selling_unit_id'];
                }
            }
            
            // 🔒 جلب المنتجات مع المخزون بقفل
            $products = Product::with(['warehouses' => function($query) use ($data) {
                    $query->where('warehouse_id', $data['warehouse_id'])
                          ->select('product_id', 'warehouse_id', 'quantity', 'reserved_quantity');
                }])
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            
            // جلب وحدات البيع (إذا وجدت)
            $sellingUnits = collect();
            if (!empty($unitIds)) {
                $sellingUnits = ProductSellingUnit::whereIn('id', $unitIds)
                    ->where('is_active', true)
                    ->get()
                    ->keyBy('id');
            }
            
            // ==================== 2️⃣ التحقق من البيانات ====================
            $this->validateSalesInvoiceData($data, $products, $sellingUnits);
            
            // ==================== 3️⃣ حساب الإجماليات ====================
            $totals = $this->calculateInvoiceTotals($data);
            
            $paid = round($data['paid'] ?? 0, 2);
            $remaining = round($totals['grand_total'] - $paid, 2);
            
            // ==================== 4️⃣ التحقق من حد الائتمان ====================
            if ($remaining < 0) {
                throw new RuntimeException('المبلغ المدفوع أكبر من الإجمالي');
            }
            
            if ($remaining > 0) {
                $customer = Customer::lockForUpdate()->findOrFail($data['customer_id']);
                
                $newBalance = $customer->balance + $remaining;
                
                if ($customer->credit_limit > 0 && $newBalance > $customer->credit_limit) {
                    throw new RuntimeException(
                        "تجاوز حد الائتمان المسموح. الحد: {$customer->credit_limit}، الرصيد الجديد: {$newBalance}"
                    );
                }
            }
            
            // ==================== 5️⃣ تحديد حالة الدفع ====================
            $paymentStatus = $this->determinePaymentStatus($paid, $totals['grand_total']);
            
            // ==================== 6️⃣ توليد رقم الفاتورة ====================
            $invoiceNumber = $this->generateInvoiceNumber('sales');
            
            // ==================== 7️⃣ إنشاء الفاتورة ====================
            $invoice = SalesInvoice::create([
                'customer_id'     => $data['customer_id'],
                'warehouse_id'    => $data['warehouse_id'],
                'invoice_number'  => $invoiceNumber,
                'invoice_date'    => $data['invoice_date'] ?? now(),
                'due_date'        => $data['due_date'] ?? null,
                'subtotal'        => $totals['subtotal'],
                'discount_type'   => $data['discount_type'] ?? 'fixed',
                'discount_value'  => $totals['general_discount'],
                'discount_amount' => $totals['total_discount'],
                'tax_rate'        => $data['tax_rate'] ?? 0,
                'tax_amount'      => $totals['total_tax'],
                'shipping_cost'   => $totals['shipping_cost'],
                'other_charges'   => $totals['other_charges'],
                'total'           => $totals['grand_total'],
                'paid'            => $paid,
                'status'          => 'confirmed',
                'payment_status'  => $paymentStatus,
                'notes'           => $data['notes'] ?? null,
                'terms_conditions'=> $data['terms_conditions'] ?? null,
                'created_by'      => auth()->id(),
                'confirmed_by'    => auth()->id(),
                'confirmed_at'    => now(),
            ]);
            
            // ==================== 8️⃣ إضافة الأصناف والتحقق من المخزون ====================
            $invoiceItems = [];
            $stockUpdates = [];
            
            foreach ($data['items'] as $item) {
                $product = $products[$item['product_id']];
                
                // معالجة الوحدة والكمية - نفس المنطق لكل المنتجات
                $sellingUnitIdInput = $item['selling_unit_id'] ?? null;
                $sellingUnit = (!empty($sellingUnitIdInput) && isset($sellingUnits[$sellingUnitIdInput]))
                    ? $sellingUnits[$sellingUnitIdInput]
                    : null;
                $quantity = round($item['quantity'] ?? 0, 6);
                $factor = $sellingUnit ? $sellingUnit->conversion_factor : 1;
                $factor = max($factor, 0.000001);
                $baseQuantity = round($quantity * $factor, 6);
                $sellingUnitId = $item['selling_unit_id'] ?? null;
                $unitCode = $sellingUnit?->unit_code ?? ($product->base_unit ?? 'piece');
                $conversionFactor = $factor;
                
                // السعر: من وحدة البيع أو من المنتج
                $price = $sellingUnit ? $sellingUnit->unit_selling_price : ($product->base_selling_price ?? 0);
                if (isset($item['price']) && $item['price'] > 0) {
                    $price = $item['price'];
                }
                
                // التحقق من المخزون
                $warehouse = $product->warehouses->first();
                if (!$warehouse) {
                    throw new RuntimeException("المنتج {$product->name} غير موجود في المخزن المحدد");
                }
                
                $availableQty = $warehouse->quantity - ($warehouse->reserved_quantity ?? 0);
                
                if ($availableQty < $baseQuantity) {
                    throw new RuntimeException(
                        "الكمية غير متاحة للمنتج: {$product->name}. المطلوب: {$baseQuantity}، المتوفر: {$availableQty}"
                    );
                }
                
                // حساب قيم الصنف
                $itemCalc = $this->calculateItemTotals($item);
                
                // تجهيز بيانات الصنف للإدراج الجماعي
                $invoiceItems[] = [
                    'sales_invoice_id' => $invoice->id,
                    'product_id'       => $item['product_id'],
                    'selling_unit_id'  => $sellingUnitId,
                    'quantity'         => $quantity,
                    'base_quantity'    => $baseQuantity,
                    'unit_code'        => $unitCode,
                    'conversion_factor'=> $conversionFactor,
                    'unit_price'       => round($price, 2),
                    'discount_type'    => 'percentage',
                    'discount_value'   => $item['discount'] ?? 0,
                    'discount_amount'  => $itemCalc['discount'],
                    'tax_rate'         => $item['tax_rate'] ?? 0,
                    'tax_amount'       => $itemCalc['tax'],
                    'subtotal'         => $itemCalc['subtotal'],
                    'total'            => $itemCalc['total'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
                
                // تجهيز تحديث المخزون
                $stockUpdates[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $baseQuantity
                ];
            }
            
            // ==================== 9️⃣ إدراج الأصناف دفعة واحدة ====================
            DB::table('sales_invoice_items')->insert($invoiceItems);
            
            // ==================== 🔟 تحديث المخزون دفعة واحدة ====================
            foreach ($stockUpdates as $update) {
                DB::table('product_warehouse')
                    ->where('product_id', $update['product_id'])
                    ->where('warehouse_id', $data['warehouse_id'])
                    ->decrement('quantity', $update['quantity']);
            }
            
            // ==================== 1️⃣1️⃣ تحديث رصيد العميل ====================
            if ($remaining > 0) {
                DB::table('customers')
                    ->where('id', $data['customer_id'])
                    ->increment('balance', $remaining);
            }
            
            // ==================== 1️⃣2️⃣ تسجيل النشاط ====================
            $this->logInvoiceActivity($invoice->id, 'sales', 'created', [
                'invoice_number' => $invoiceNumber,
                'total' => $totals['grand_total'],
                'items_count' => count($data['items'])
            ]);
            
            // ==================== 1️⃣3️⃣ مسح الـ Cache ====================
            $this->clearSalesInvoiceCache($data['customer_id']);
            
            return $invoice->fresh([
                'items.product', 
                'items.sellingUnit', 
                'customer', 
                'warehouse'
            ]);
        });
    }

    /**
     * ✅ التحقق من بيانات فاتورة المبيعات
     */
    private function validateSalesInvoiceData(array $data, $products, $sellingUnits): void
    {
        foreach ($data['items'] as $index => $item) {
            // التحقق من وجود المنتج
            if (!isset($products[$item['product_id']])) {
                throw new RuntimeException("المنتج غير موجود: ID {$item['product_id']}");
            }
            
            // إذا كانت هناك وحدة بيع، تحقق من ملكيتها للمنتج
            if (!empty($item['selling_unit_id']) && isset($sellingUnits[$item['selling_unit_id']])) {
                $unit = $sellingUnits[$item['selling_unit_id']];
                if ($unit->product_id != $item['product_id']) {
                    throw new RuntimeException("وحدة البيع لا تنتمي للمنتج المحدد في الصنف رقم " . ($index + 1));
                }
            }
            
            // التحقق من الكمية
            $quantity = $item['quantity'] ?? 0;
            if ($quantity <= 0) {
                throw new RuntimeException("الكمية يجب أن تكون أكبر من صفر في الصنف رقم " . ($index + 1));
            }
            
            // التحقق من السعر
            if (($item['price'] ?? 0) < 0) {
                throw new RuntimeException("السعر لا يمكن أن يكون سالباً في الصنف رقم " . ($index + 1));
            }
        }
    }

    /**
     * ✅ حساب إجماليات الفاتورة
     */
    private function calculateInvoiceTotals(array $data): array
    {
        $subtotal = 0;
        $totalItemsDiscount = 0;
        $totalItemsTax = 0;
        
        foreach ($data['items'] as $item) {
            $itemCalc = $this->calculateItemTotals($item);
            
            $subtotal += $itemCalc['subtotal'];
            $totalItemsDiscount += $itemCalc['discount'];
            $totalItemsTax += $itemCalc['tax'];
        }
        
        $generalDiscount = round($data['discount_value'] ?? 0, 2);
        $generalTax = round($data['tax_amount'] ?? 0, 2);
        $shippingCost = round($data['shipping_cost'] ?? 0, 2);
        $otherCharges = round($data['other_charges'] ?? 0, 2);
        
        $totalDiscount = $totalItemsDiscount + $generalDiscount;
        $totalTax = $totalItemsTax + $generalTax;
        
        $grandTotal = $subtotal - $totalDiscount + $totalTax + $shippingCost + $otherCharges;
        
        return [
            'subtotal' => round($subtotal, 2),
            'items_discount' => round($totalItemsDiscount, 2),
            'general_discount' => $generalDiscount,
            'total_discount' => round($totalDiscount, 2),
            'items_tax' => round($totalItemsTax, 2),
            'general_tax' => $generalTax,
            'total_tax' => round($totalTax, 2),
            'shipping_cost' => $shippingCost,
            'other_charges' => $otherCharges,
            'grand_total' => round($grandTotal, 2),
        ];
    }

    /**
     * ✅ حساب قيم الصنف الواحد
     */
    private function calculateItemTotals(array $item): array
    {
        $quantity = $item['quantity'] ?? 0;
        $price = $item['price'] ?? 0;
        $discountPercent = $item['discount'] ?? 0;
        $taxRate = $item['tax_rate'] ?? 0;
        
        $subtotal = max(0.001, $quantity * $price);
        $discount = $subtotal * ($discountPercent / 100);
        $afterDiscount = max(0.001, $subtotal - $discount);
        $tax = $afterDiscount * ($taxRate / 100);
        $total = $afterDiscount + $tax;
        
        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'after_discount' => round($afterDiscount, 2),
            'tax' => round($tax, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * ✅ تحديد حالة الدفع
     */
    private function determinePaymentStatus(float $paid, float $total): string
    {
        if ($paid >= $total) {
            return 'paid';
        } elseif ($paid > 0) {
            return 'partial';
        }
        return 'unpaid';
    }

    /* =====================================================================
     * ❌ CANCEL SALES INVOICE
     * ===================================================================== */

    /**
     * ✅ إلغاء فاتورة مبيعات مع معالجة كاملة
     */
    public function cancelSalesInvoice(int $invoiceId, ?string $cancellationReason = null): bool
    {
        return DB::transaction(function () use ($invoiceId, $cancellationReason) {
            
            // 🔒 قفل الفاتورة
            $invoice = SalesInvoice::with(['items', 'payments'])
                ->lockForUpdate()
                ->findOrFail($invoiceId);
            
            // ==================== التحقق ====================
            if ($invoice->status === 'cancelled') {
                throw new RuntimeException('الفاتورة ملغاة بالفعل');
            }
            
            // ==================== إرجاع المخزون ====================
            $stockUpdates = [];
            
            foreach ($invoice->items as $item) {
                $baseQuantity = $item->base_quantity ?? $item->quantity;
                
                $stockUpdates[] = [
                    'product_id' => $item->product_id,
                    'quantity' => $baseQuantity
                ];
            }
            
            // تحديث المخزون دفعة واحدة
            foreach ($stockUpdates as $update) {
                DB::table('product_warehouse')
                    ->where('product_id', $update['product_id'])
                    ->where('warehouse_id', $invoice->warehouse_id)
                    ->increment('quantity', $update['quantity']);
            }
            
            // ==================== معالجة رصيد العميل ====================
            $remaining = $invoice->total - $invoice->paid;
            
            if ($remaining > 0) {
                // إلغاء الدين
                DB::table('customers')
                    ->where('id', $invoice->customer_id)
                    ->decrement('balance', $remaining);
            }
            
            // ==================== معالجة الدفعات ====================
            if ($invoice->paid > 0) {
                // تسجيل رد المبلغ
                DB::table('customer_refunds')->insert([
                    'customer_id' => $invoice->customer_id,
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->paid,
                    'status' => 'pending',
                    'reason' => 'إلغاء الفاتورة: ' . ($cancellationReason ?? 'غير محدد'),
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // إضافة المبلغ لرصيد العميل
                DB::table('customers')
                    ->where('id', $invoice->customer_id)
                    ->increment('balance', $invoice->paid);
            }
            
            // ==================== تحديث الفاتورة ====================
            $invoice->update([
                'status' => 'cancelled',
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
                'cancellation_reason' => $cancellationReason,
                'updated_by' => auth()->id(),
            ]);
            
            // ==================== تسجيل النشاط ====================
            $this->logInvoiceActivity($invoice->id, 'sales', 'cancelled', [
                'reason' => $cancellationReason,
                'refund_amount' => $invoice->paid,
                'items_count' => $invoice->items->count()
            ]);
            
            // مسح الـ Cache
            $this->clearSalesInvoiceCache($invoice->customer_id);
            
            return true;
        });
    }

    /* =====================================================================
     * 💳 PAYMENT MANAGEMENT
     * ===================================================================== */

    /**
     * ✅ إضافة دفعة لفاتورة مبيعات
     */
    public function addSalesInvoicePayment(int $invoiceId, array $paymentData): SalesInvoice
    {
        return DB::transaction(function () use ($invoiceId, $paymentData) {
            
            // 🔒 قفل الفاتورة
            $invoice = SalesInvoice::lockForUpdate()->findOrFail($invoiceId);
            
            // ==================== التحقق ====================
            if ($invoice->status === 'cancelled') {
                throw new RuntimeException('لا يمكن إضافة دفعة لفاتورة ملغاة');
            }
            
            $amount = round($paymentData['amount'], 2);
            $remaining = round($invoice->total - $invoice->paid, 2);
            
            if ($amount <= 0) {
                throw new RuntimeException('المبلغ يجب أن يكون أكبر من صفر');
            }
            
            if ($amount > $remaining) {
                throw new RuntimeException("المبلغ المدفوع ({$amount}) أكبر من المبلغ المتبقي ({$remaining})");
            }
            
            // ==================== تحديث الفاتورة ====================
            $newPaid = $invoice->paid + $amount;
            $newRemaining = $invoice->total - $newPaid;
            
            $paymentStatus = $this->determinePaymentStatus($newPaid, $invoice->total);
            
            $invoice->update([
                'paid' => $newPaid,
                'payment_status' => $paymentStatus,
                'updated_by' => auth()->id(),
            ]);
            
            // ==================== تسجيل الدفعة ====================
            DB::table('invoice_payments')->insert([
                'invoice_id' => $invoice->id,
                'invoice_type' => 'sales',
                'amount' => $amount,
                'payment_method' => $paymentData['payment_method'] ?? 'cash',
                'payment_date' => $paymentData['payment_date'] ?? now(),
                'reference' => $paymentData['reference'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // ==================== تحديث رصيد العميل ====================
            DB::table('customers')
                ->where('id', $invoice->customer_id)
                ->decrement('balance', $amount);
            
            // ==================== تسجيل النشاط ====================
            $this->logInvoiceActivity($invoice->id, 'sales', 'payment_added', [
                'amount' => $amount,
                'payment_method' => $paymentData['payment_method'] ?? 'cash',
                'new_paid' => $newPaid,
                'new_remaining' => $newRemaining
            ]);
            
            // مسح الـ Cache
            $this->clearSalesInvoiceCache($invoice->customer_id);
            
            return $invoice->fresh(['items', 'customer', 'payments']);
        });
    }

    /* =====================================================================
     * 📦 PURCHASE INVOICES
     * ===================================================================== */

    /**
     * ✅ جلب فواتير المشتريات مع الفلاتر
     */
    public function getPurchaseInvoicesWithFilters(Request $request)
    {
        $query = PurchaseInvoice::query();
        
        // تطبيق الفلاتر
        $this->applyPurchaseFiltersToQuery($query, $request);
        
        // الترتيب
        $sortBy = $request->input('sort_by', 'invoice_date');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $allowedSortColumns = [
            'invoice_number', 'invoice_date', 'total', 
            'paid', 'created_at', 'supplier_id', 'warehouse_id'
        ];
        
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'invoice_date';
        }
        
        $query->orderBy($sortBy, $sortOrder);
        
        // Eager loading
        $invoices = $query->with([
                'supplier:id,name,phone,email',
                'warehouse:id,name,code',
                'createdBy:id,name',
            ])
            ->select([
                'id', 'invoice_number', 'invoice_date', 'due_date',
                'supplier_id', 'warehouse_id',
                'subtotal', 'discount_amount', 'tax_amount',
                'shipping_cost', 'other_charges',
                'total', 'paid',
                'status', 'payment_status',
                'created_by', 'created_at', 'updated_at'
            ])
            ->paginate($request->input('per_page', 20));
        
        // حساب الحقول المشتقة
        $invoices->getCollection()->transform(function ($invoice) {
            $invoice->remaining = round($invoice->total - $invoice->paid, 2);
            return $invoice;
        });
        
        return $invoices;
    }

    /**
     * ✅ تطبيق فلاتر المشتريات
     */
    private function applyPurchaseFiltersToQuery($query, Request $request): void
    {
        // البحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }
        
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        
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
        
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }
        
        if ($request->filled('amount_from')) {
            $query->where('total', '>=', $request->amount_from);
        }
        
        if ($request->filled('amount_to')) {
            $query->where('total', '<=', $request->amount_to);
        }
    }

    /**
     * ✅ إنشاء فاتورة مشتريات
     */
    public function createPurchaseInvoice(array $data): PurchaseInvoice
    {
        return DB::transaction(function () use ($data) {
            
            // جلب البيانات
            $productIds = array_column($data['items'], 'product_id');
            $unitIds = array_column($data['items'], 'purchase_unit_id');
            
            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            
            $purchaseUnits = ProductPurchaseUnit::whereIn('id', $unitIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');
            
            // التحقق من البيانات
            $this->validatePurchaseInvoiceData($data, $products, $purchaseUnits);
            
            // حساب الإجماليات
            $totals = $this->calculateInvoiceTotals($data);
            
            $paid = round($data['paid'] ?? 0, 2);
            $remaining = round($totals['grand_total'] - $paid, 2);
            
            if ($remaining < 0) {
                throw new RuntimeException('المبلغ المدفوع أكبر من الإجمالي');
            }
            
            // تحديد حالة الدفع
            $paymentStatus = $this->determinePaymentStatus($paid, $totals['grand_total']);
            
            // توليد رقم الفاتورة
            $invoiceNumber = $data['invoice_number'] ?? $this->generateInvoiceNumber('purchase');
            
            // إنشاء الفاتورة
            $invoice = PurchaseInvoice::create([
                'supplier_id'     => $data['supplier_id'],
                'warehouse_id'    => $data['warehouse_id'],
                'invoice_number'  => $invoiceNumber,
                'invoice_date'    => $data['invoice_date'] ?? now(),
                'due_date'        => $data['due_date'] ?? null,
                'subtotal'        => $totals['subtotal'],
                'discount_type'   => $data['discount_type'] ?? 'fixed',
                'discount_value'  => $totals['general_discount'],
                'discount_amount' => $totals['total_discount'],
                'tax_rate'        => $data['tax_rate'] ?? 0,
                'tax_amount'      => $totals['total_tax'],
                'shipping_cost'   => $totals['shipping_cost'],
                'other_charges'   => $totals['other_charges'],
                'total'           => $totals['grand_total'],
                'paid'            => $paid,
                'status'          => 'confirmed',
                'payment_status'  => $paymentStatus,
                'notes'           => $data['notes'] ?? null,
                'created_by'      => auth()->id(),
                'confirmed_by'    => auth()->id(),
                'confirmed_at'    => now(),
            ]);
            
            // إضافة الأصناف وتحديث المخزون
            $invoiceItems = [];
            $stockUpdates = [];
            
            foreach ($data['items'] as $item) {
                $product = $products[$item['product_id']];
                $purchaseUnit = $purchaseUnits[$item['purchase_unit_id']];
                
                $baseQuantity = round($item['quantity'] * $purchaseUnit->conversion_factor, 3);
                
                $itemCalc = $this->calculateItemTotals($item);
                
                $invoiceItems[] = [
                    'purchase_invoice_id' => $invoice->id,
                    'product_id'       => $item['product_id'],
                    'purchase_unit_id' => $item['purchase_unit_id'],
                    'quantity'         => round($item['quantity'], 3),
                    'base_quantity'    => $baseQuantity,
                    'unit_code'        => $purchaseUnit->unit_code ?? 'piece',
                    'conversion_factor'=> $purchaseUnit->conversion_factor,
                    'unit_cost'        => round($item['cost'], 2),
                    'discount_type'    => 'percentage',
                    'discount_value'   => $item['discount'] ?? 0,
                    'discount_amount'  => $itemCalc['discount'],
                    'tax_rate'         => $item['tax_rate'] ?? 0,
                    'tax_amount'       => $itemCalc['tax'],
                    'subtotal'         => $itemCalc['subtotal'],
                    'total'            => $itemCalc['total'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
                
                $stockUpdates[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $baseQuantity
                ];
            }
            
            // إدراج الأصناف
            DB::table('purchase_invoice_items')->insert($invoiceItems);
            
            // تحديث المخزون (إضافة)
            foreach ($stockUpdates as $update) {
                $exists = DB::table('product_warehouse')
                    ->where('product_id', $update['product_id'])
                    ->where('warehouse_id', $data['warehouse_id'])
                    ->exists();
                
                if ($exists) {
                    DB::table('product_warehouse')
                        ->where('product_id', $update['product_id'])
                        ->where('warehouse_id', $data['warehouse_id'])
                        ->increment('quantity', $update['quantity']);
                } else {
                    DB::table('product_warehouse')->insert([
                        'product_id' => $update['product_id'],
                        'warehouse_id' => $data['warehouse_id'],
                        'quantity' => $update['quantity'],
                        'reserved_quantity' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            // تحديث رصيد المورد
            if ($remaining > 0) {
                DB::table('suppliers')
                    ->where('id', $data['supplier_id'])
                    ->increment('balance', $remaining);
            }
            
            // تسجيل النشاط
            $this->logInvoiceActivity($invoice->id, 'purchase', 'created', [
                'invoice_number' => $invoiceNumber,
                'total' => $totals['grand_total'],
                'items_count' => count($data['items'])
            ]);
            
            return $invoice->fresh(['items.product', 'items.purchaseUnit', 'supplier', 'warehouse']);
        });
    }

    /**
     * ✅ التحقق من بيانات فاتورة المشتريات
     */
    private function validatePurchaseInvoiceData(array $data, $products, $purchaseUnits): void
    {
        foreach ($data['items'] as $index => $item) {
            if (!isset($products[$item['product_id']])) {
                throw new RuntimeException("المنتج غير موجود: ID {$item['product_id']}");
            }
            
            if (!isset($purchaseUnits[$item['purchase_unit_id']])) {
                throw new RuntimeException("وحدة الشراء غير موجودة أو غير نشطة في الصنف رقم " . ($index + 1));
            }
            
            $unit = $purchaseUnits[$item['purchase_unit_id']];
            if ($unit->product_id != $item['product_id']) {
                throw new RuntimeException("وحدة الشراء لا تنتمي للمنتج المحدد في الصنف رقم " . ($index + 1));
            }
            
            if ($item['quantity'] <= 0) {
                throw new RuntimeException("الكمية يجب أن تكون أكبر من صفر في الصنف رقم " . ($index + 1));
            }
            
            if ($item['cost'] < 0) {
                throw new RuntimeException("التكلفة لا يمكن أن تكون سالبة في الصنف رقم " . ($index + 1));
            }
        }
    }

    /**
     * ✅ إلغاء فاتورة مشتريات
     */
    public function cancelPurchaseInvoice(int $invoiceId, ?string $cancellationReason = null): bool
    {
        return DB::transaction(function () use ($invoiceId, $cancellationReason) {
            
            $invoice = PurchaseInvoice::with(['items'])
                ->lockForUpdate()
                ->findOrFail($invoiceId);
            
            if ($invoice->status === 'cancelled') {
                throw new RuntimeException('الفاتورة ملغاة بالفعل');
            }
            
            // خصم المخزون
            foreach ($invoice->items as $item) {
                $baseQuantity = $item->base_quantity ?? $item->quantity;
                
                DB::table('product_warehouse')
                    ->where('product_id', $item->product_id)
                    ->where('warehouse_id', $invoice->warehouse_id)
                    ->decrement('quantity', $baseQuantity);
            }
            
            // تحديث رصيد المورد
            $remaining = $invoice->total - $invoice->paid;
            
            if ($remaining > 0) {
                DB::table('suppliers')
                    ->where('id', $invoice->supplier_id)
                    ->decrement('balance', $remaining);
            }
            
            // تحديث الفاتورة
            $invoice->update([
                'status' => 'cancelled',
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
                'cancellation_reason' => $cancellationReason,
            ]);
            
            $this->logInvoiceActivity($invoice->id, 'purchase', 'cancelled', [
                'reason' => $cancellationReason
            ]);
            
            return true;
        });
    }

    /* =====================================================================
     * 🛠️ HELPER METHODS
     * ===================================================================== */

    /**
     * ✅ توليد رقم فاتورة آمن - باستخدام جدول sequences
     */
    private function generateInvoiceNumber(string $type): string
    {
        return DB::transaction(function() use ($type) {
            $prefix = $type === 'sales' ? 'S' : 'P';
            $year = date('Y');
            
            // 🔒 قفل الـ sequence
            $sequence = DB::table('invoice_sequences')
                ->where('type', $type)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();
            
            if (!$sequence) {
                DB::table('invoice_sequences')->insert([
                    'type' => $type,
                    'year' => $year,
                    'last_number' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $number = 1;
            } else {
                $number = $sequence->last_number + 1;
                DB::table('invoice_sequences')
                    ->where('id', $sequence->id)
                    ->update([
                        'last_number' => $number,
                        'updated_at' => now()
                    ]);
            }
            
            return sprintf('%s%s%05d', $prefix, $year, $number);
        });
    }

    /**
     * ✅ تسجيل نشاط الفاتورة
     */
    private function logInvoiceActivity(
        int $invoiceId, 
        string $invoiceType, 
        string $action, 
        array $metadata = []
    ): void {
        try {
            DB::table('invoice_activity_log')->insert([
                'invoice_id' => $invoiceId,
                'invoice_type' => $invoiceType,
                'action' => $action,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => json_encode($metadata),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // في حالة فشل التسجيل، لا نوقف العملية
            \Log::error('Failed to log invoice activity', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ مسح الـ Cache
     */
    private function clearSalesInvoiceCache(int $customerId): void
    {
        try {
            Cache::tags(['invoices', "customer_{$customerId}"])->flush();
        } catch (\Exception $e) {
            \Log::warning('Failed to clear cache', ['error' => $e->getMessage()]);
        }
    }

    /**
     * ✅ مسح cache المشتريات
     */
    private function clearPurchaseInvoiceCache(int $supplierId): void
    {
        try {
            Cache::tags(['invoices', "supplier_{$supplierId}"])->flush();
        } catch (\Exception $e) {
            \Log::warning('Failed to clear cache', ['error' => $e->getMessage()]);
        }
    }

    /**
     * ✅ تحديث فاتورة مبيعات
     */
    public function updateSalesInvoice(int $invoiceId, array $data): SalesInvoice
    {
        return DB::transaction(function () use ($invoiceId, $data) {
            
            $invoice = SalesInvoice::with('items')->lockForUpdate()->findOrFail($invoiceId);

            // منع التعديل للفواتير الملغاة
            if ($invoice->status === 'cancelled') {
                throw new RuntimeException('لا يمكن تعديل فاتورة ملغاة');
            }

            // ==================== حفظ قيم قبل التعديل (لرصيد العميل) ====================
            $oldCustomerId = (int) $invoice->customer_id;
            $oldRemaining = round(max(0, (float) $invoice->total - (float) ($invoice->paid ?? 0)), 2);

            // إرجاع المخزون القديم أولاً
            foreach ($invoice->items as $oldItem) {
                DB::table('product_warehouse')
                    ->where('product_id', $oldItem->product_id)
                    ->where('warehouse_id', $invoice->warehouse_id)
                    ->increment('quantity', $oldItem->base_quantity);
            }

            // حذف الأصناف القديمة
            $invoice->items()->delete();

            // ==================== جلب البيانات ====================
            $productIds = array_column($data['items'], 'product_id');
            // بعض العناصر قد لا تحتوي selling_unit_id (عند اختيار "-- اختر الوحدة --")
            $unitIds = [];
            foreach ($data['items'] as $it) {
                if (!empty($it['selling_unit_id'])) {
                    $unitIds[] = $it['selling_unit_id'];
                }
            }
            
            $products = Product::with(['warehouses' => function($query) use ($data) {
                    $query->where('warehouse_id', $data['warehouse_id'])
                          ->select('product_id', 'warehouse_id', 'quantity');
                }])
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            
            $sellingUnits = collect();
            if (!empty($unitIds)) {
                $sellingUnits = ProductSellingUnit::whereIn('id', $unitIds)
                    ->where('is_active', true)
                    ->get()
                    ->keyBy('id');
            }

            // التحقق من البيانات
            $this->validateSalesInvoiceData($data, $products, $sellingUnits);
            
            // حساب الإجماليات
            $totals = $this->calculateInvoiceTotals($data);
            
            $paid = round($data['paid'] ?? 0, 2);
            $remaining = round($totals['grand_total'] - $paid, 2);
            
            if ($remaining < 0) {
                $paid = $totals['grand_total'];
                $remaining = 0;
            }
            
            // ==================== تحديث رصيد العميل + التحقق من حد الائتمان ====================
            // المنطق الصحيح: رصيد العميل يتغير بمقدار (متبقي جديد - متبقي قديم) حتى لو المتبقي الجديد = 0
            $newCustomerId = (int) $data['customer_id'];
            $newRemaining = round(max(0, (float) $totals['grand_total'] - (float) $paid), 2);

            // ملاحظة محاسبية: في النظام ده "المديونية" مخزنة كرصيد سالب (balance < 0)
            // لذلك المتبقي (remaining) يخصم من الرصيد (يجعله أكثر سلبية)، والسداد يزيد الرصيد نحو الصفر.
            if ($newCustomerId === $oldCustomerId) {
                $customer = Customer::lockForUpdate()->findOrFail($newCustomerId);
                $newBalance = round((float) $customer->balance + $oldRemaining - $newRemaining, 2);

                if ($customer->credit_limit > 0 && $newBalance < 0 && abs($newBalance) > (float) $customer->credit_limit) {
                    throw new RuntimeException(
                        "تجاوز حد الائتمان المسموح. الحد: {$customer->credit_limit}، الرصيد الجديد: {$newBalance}"
                    );
                }

                $customer->update(['balance' => $newBalance]);
            } else {
                // لو تم تغيير العميل: نرجع المتبقي القديم للعميل القديم، ونخصم المتبقي الجديد من العميل الجديد
                $oldCustomer = Customer::lockForUpdate()->findOrFail($oldCustomerId);
                $oldCustomerNewBalance = round((float) $oldCustomer->balance + $oldRemaining, 2);
                $oldCustomer->update(['balance' => $oldCustomerNewBalance]);

                $newCustomer = Customer::lockForUpdate()->findOrFail($newCustomerId);
                $newCustomerNewBalance = round((float) $newCustomer->balance - $newRemaining, 2);

                if ($newCustomer->credit_limit > 0 && $newCustomerNewBalance < 0 && abs($newCustomerNewBalance) > (float) $newCustomer->credit_limit) {
                    throw new RuntimeException(
                        "تجاوز حد الائتمان المسموح. الحد: {$newCustomer->credit_limit}، الرصيد الجديد: {$newCustomerNewBalance}"
                    );
                }

                $newCustomer->update(['balance' => $newCustomerNewBalance]);
            }

            // تحديد حالة الدفع
            $paymentStatus = $this->determinePaymentStatus($paid, $totals['grand_total']);

            // تحديث الفاتورة
            $invoice->update([
                'customer_id'     => $data['customer_id'],
                'warehouse_id'    => $data['warehouse_id'],
                'invoice_date'    => $data['invoice_date'] ?? $invoice->invoice_date,
                'due_date'        => $data['due_date'] ?? $invoice->due_date,
                'subtotal'        => $totals['subtotal'],
                'discount_value'  => $totals['general_discount'],
                'discount_amount' => $totals['total_discount'],
                'tax_rate'        => $data['tax_rate'] ?? 0,
                'tax_amount'      => $totals['total_tax'],
                'shipping_cost'   => $totals['shipping_cost'],
                'other_charges'   => $totals['other_charges'],
                'total'           => $totals['grand_total'],
                'paid'            => $paid,
                'payment_status'  => $paymentStatus,
                'notes'           => $data['notes'] ?? $invoice->notes,
                'updated_by'      => auth()->id(),
            ]);

            // ==================== إضافة الأصناف الجديدة وتحديث المخزون ====================
            $invoiceItems = [];
            $stockUpdates = [];
            
            foreach ($data['items'] as $item) {
                $product = $products[$item['product_id']];
                $sellByWeight = isset($item['sell_by_weight']) && $item['sell_by_weight'] == 1;
                
                if ($sellByWeight) {
                    $baseUnit = $item['base_unit'] ?? 'kg';
                    $weightInput = $item['weight'] ?? $item['quantity'] ?? 0;
                    
                    // تحويل الوزن إلى الوحدة الأساسية بشكل صحيح
                    switch ($baseUnit) {
                        case 'ton':
                            // الوزن المدخل بالطن مباشرة، لا تحتاج تحويل
                            $quantity = round($weightInput, 6);
                            break;
                        case 'quintal':
                            // الوزن المدخل بالقنطار مباشرة
                            $quantity = round($weightInput, 6);
                            break;
                        case 'gram':
                            // الوزن المدخل بالجرام، نحول لكيلو
                            $quantity = round($weightInput / 1000, 6);
                            break;
                        case 'kg':
                        default:
                            // الوزن المدخل بالكيلو مباشرة
                            $quantity = round($weightInput, 6);
                            break;
                    }
                    
                    $baseQuantity = $quantity;
                    $sellingUnitId = null;
                    $unitCode = $baseUnit;
                    $conversionFactor = 1;
                } else {
                    $sellingUnitIdInput = $item['selling_unit_id'] ?? null;
                    $sellingUnit = (!empty($sellingUnitIdInput) && isset($sellingUnits[$sellingUnitIdInput]))
                        ? $sellingUnits[$sellingUnitIdInput]
                        : null;
                    $quantity = round($item['quantity'], 6);
                    $factor = $sellingUnit ? $sellingUnit->conversion_factor : 1;
                    // التأكد من ان معامل التحويل صالح
                    $factor = max($factor, 0.000001);
                    $baseQuantity = round($quantity * $factor, 6);
                    $sellingUnitId = $sellingUnit?->id;
                    $unitCode = $sellingUnit?->unit_code ?? 'piece';
                    $conversionFactor = $factor;
                }
                
                $itemCalc = $this->calculateItemTotals($item);
                
                $invoiceItems[] = [
                    'sales_invoice_id' => $invoice->id,
                    'product_id'       => $item['product_id'],
                    'selling_unit_id'  => $sellingUnitId,
                    'quantity'         => $quantity,
                    'base_quantity'    => $baseQuantity,
                    'unit_code'        => $unitCode,
                    'conversion_factor'=> $conversionFactor,
                    'unit_price'       => round($item['price'], 2),
                    'discount_type'    => 'percentage',
                    'discount_value'   => $item['discount'] ?? 0,
                    'discount_amount' => $itemCalc['discount'],
                    'tax_rate'         => $item['tax_rate'] ?? 0,
                    'tax_amount'       => $itemCalc['tax'],
                    'subtotal'         => $itemCalc['subtotal'],
                    'total'            => $itemCalc['total'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];

                $stockUpdates[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $baseQuantity
                ];
            }

            // إدراج الأصناف الجديدة
            DB::table('sales_invoice_items')->insert($invoiceItems);

            // خصم المخزون الجديد
            foreach ($stockUpdates as $update) {
                DB::table('product_warehouse')
                    ->where('product_id', $update['product_id'])
                    ->where('warehouse_id', $data['warehouse_id'])
                    ->decrement('quantity', $update['quantity']);
            }

            // مسح الـ Cache
            $this->clearSalesInvoiceCache($invoice->customer_id);
            
            // تسجيل النشاط
            $this->logInvoiceActivity($invoice->id, 'sales', 'updated', []);

            return $invoice->fresh(['items.product', 'items.sellingUnit']);
        });
    }
}