<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\CashTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ReportingService
{
    /**
     * تقرير المخزون الحالي - محسّن للأداء
     */
    public function inventoryReport(int $warehouseId = null)
    {
        $cacheKey = "inventory_report_" . ($warehouseId ?? 'all') . '_' . now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 1800, function () use ($warehouseId) {
            $query = DB::table('product_warehouse')
                ->join('products', 'products.id', '=', 'product_warehouse.product_id')
                ->join('warehouses', 'warehouses.id', '=', 'product_warehouse.warehouse_id')
                ->select(
                    'products.id',
                    'products.name',
                    'products.code',
                    'products.barcode',
                    'warehouses.name as warehouse_name',
                    'product_warehouse.quantity',
                    'product_warehouse.min_stock',
                    'products.purchase_price',
                    'products.selling_price',
                    DB::raw('product_warehouse.quantity * products.purchase_price as total_value'),
                    DB::raw('product_warehouse.quantity * products.selling_price as expected_revenue'),
                    DB::raw('(products.selling_price - products.purchase_price) * product_warehouse.quantity as potential_profit')
                );

            if ($warehouseId) {
                $query->where('product_warehouse.warehouse_id', $warehouseId);
            }

            return $query->orderBy('total_value', 'desc')->get();
        });
    }

    /**
     * تقرير الأرباح والخسائر - محسّن ودقيق
     */
/**
 * تقرير الأرباح والخسائر - مبسط وآمن
 */
public function profitLossReport($startDate, $endDate)
{
    // إجمالي المبيعات
    $totalSales = SalesInvoice::whereBetween('invoice_date', [$startDate, $endDate])
        ->sum('total') ?? 0;
    
    $salesCount = SalesInvoice::whereBetween('invoice_date', [$startDate, $endDate])
        ->count();

    // صافي المبيعات (بدون مرتجعات لو الجدول مش موجود)
    $salesReturns = 0;
    $netSales = $totalSales;

    // تكلفة المبيعات (COGS) - معدلة لتقرأ متوسط التكلفة التاريخية من حركات المخزن
    $costOfSales = DB::table('inventory_movements')
        ->join('sales_invoices', function ($join) {
            $join->on('sales_invoices.id', '=', 'inventory_movements.reference_id')
                 ->where('inventory_movements.reference_type', '=', SalesInvoice::class);
        })
        ->join('products', 'products.id', '=', 'inventory_movements.product_id')
        ->whereBetween('sales_invoices.invoice_date', [$startDate, $endDate])
        ->where('inventory_movements.movement_type', '=', 'sale')
        ->sum(DB::raw('ABS(inventory_movements.quantity_change) * COALESCE(inventory_movements.unit_cost_snapshot, products.purchase_price)')) ?? 0;

    // إجمالي المشتريات
    $totalPurchases = PurchaseInvoice::whereBetween('invoice_date', [$startDate, $endDate])
        ->sum('total') ?? 0;
    
    $purchasesCount = PurchaseInvoice::whereBetween('invoice_date', [$startDate, $endDate])
        ->count();

    // المصروفات حسب الفئة
    $expensesByCategory = CashTransaction::where('transaction_type', 'withdrawal')
        ->whereBetween('transaction_date', [$startDate, $endDate])
        ->select('category', DB::raw('SUM(amount) as total'))
        ->groupBy('category')
        ->pluck('total', 'category')
        ->toArray();

    $totalExpenses = array_sum($expensesByCategory);

    // حساب الأرباح
    $grossProfit = $netSales - $costOfSales;
    $netProfit = $grossProfit - $totalExpenses;
    $profitMargin = $netSales > 0 ? ($netProfit / $netSales) * 100 : 0;

    return [
        // المبيعات
        'total_sales' => $totalSales,
        'sales_returns' => $salesReturns,
        'net_sales' => $netSales,
        'sales_count' => $salesCount,
        
        // المشتريات
        'total_purchases' => $totalPurchases,
        'purchase_returns' => 0,
        'net_purchases' => $totalPurchases,
        'purchases_count' => $purchasesCount,
        
        // التكاليف والأرباح
        'cost_of_sales' => $costOfSales,
        'gross_profit' => $grossProfit,
        'total_expenses' => $totalExpenses,
        'expenses_by_category' => $expensesByCategory,
        'net_profit' => $netProfit,
        'profit_margin' => round($profitMargin, 2),
    ];
}    /**
     * أفضل المنتجات مبيعاً - محسّن
     */
    public function topSellingProducts($startDate = null, $endDate = null, $limit = 10)
    {
        $cacheKey = "top_products_" . ($startDate ? Carbon::parse($startDate)->format('Ymd') : 'all') . '_' . $limit;
        
        return Cache::remember($cacheKey, 600, function () use ($startDate, $endDate, $limit) {
            $query = DB::table('sales_invoice_items')
                ->join('products', 'products.id', '=', 'sales_invoice_items.product_id')
                ->join('sales_invoices', 'sales_invoices.id', '=', 'sales_invoice_items.sales_invoice_id')
                ->select(
                    'products.id',
                    'products.name',
                    'products.code',
                    'products.barcode',
                    DB::raw('SUM(sales_invoice_items.quantity) as total_quantity'),
                    DB::raw('SUM(sales_invoice_items.total) as total_revenue'),
                    DB::raw('SUM(sales_invoice_items.quantity * products.purchase_price) as total_cost'),
                    DB::raw('SUM(sales_invoice_items.total - (sales_invoice_items.quantity * products.purchase_price)) as total_profit'),
                    DB::raw('COUNT(DISTINCT sales_invoice_items.sales_invoice_id) as number_of_orders'),
                    DB::raw('AVG(sales_invoice_items.unit_price) as average_price')
                )
                ->groupBy('products.id', 'products.name', 'products.code', 'products.barcode');

            if ($startDate && $endDate) {
                $query->whereBetween('sales_invoices.invoice_date', [$startDate, $endDate]);
            }

            return $query->orderBy('total_revenue', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * أفضل العملاء - محسّن
     */
    public function topCustomers($startDate = null, $endDate = null, $limit = 10)
    {
        $cacheKey = "top_customers_" . ($startDate ? Carbon::parse($startDate)->format('Ymd') : 'all') . '_' . $limit;
        
        return Cache::remember($cacheKey, 600, function () use ($startDate, $endDate, $limit) {
            $query = DB::table('sales_invoices')
                ->join('customers', 'customers.id', '=', 'sales_invoices.customer_id')
                ->select(
                    'customers.id',
                    'customers.name',
                    'customers.phone',
                    'customers.email',
                    DB::raw('COUNT(sales_invoices.id) as total_invoices'),
                    DB::raw('SUM(sales_invoices.total) as total_spent'),
                    DB::raw('AVG(sales_invoices.total) as average_invoice'),
                    DB::raw('MAX(sales_invoices.invoice_date) as last_purchase_date')
                )
                ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.email');

            if ($startDate && $endDate) {
                $query->whereBetween('sales_invoices.invoice_date', [$startDate, $endDate]);
            }

            return $query->orderBy('total_spent', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * تقرير المبيعات اليومية - محسّن
     */
    public function dailySalesReport($startDate, $endDate)
    {
        return DB::table('sales_invoices')
            ->select(
                DB::raw('DATE(invoice_date) as date'),
                DB::raw('COUNT(*) as total_invoices'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('SUM(paid) as total_paid'),
                DB::raw('SUM(total - paid) as total_remaining'),
                DB::raw('AVG(total) as average_invoice')
            )
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(invoice_date)'))
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * تقرير الديون
     */
    public function debtorsReport()
    {
        return DB::table('customers')
            ->select(
                'id',
                'name',
                'phone',
                'email',
                'balance',
                'credit_limit',
                DB::raw('ABS(balance) as debt_amount')
            )
            ->where('balance', '<', 0)
            ->orderBy('balance', 'asc')
            ->get();
    }

    /**
     * تقرير الدائنين
     */
    public function creditorsReport()
    {
        return DB::table('suppliers')
            ->select(
                'id',
                'name',
                'phone',
                'email',
                'balance',
                DB::raw('balance as credit_amount')
            )
            ->where('balance', '>', 0)
            ->orderBy('balance', 'desc')
            ->get();
    }

    /**
     * المصروفات حسب الفئة
     */
    public function expensesByCategory($startDate = null, $endDate = null)
    {
        $query = CashTransaction::withdrawals()
            ->select(
                'category',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('AVG(amount) as average_amount'),
                DB::raw('MAX(amount) as max_amount'),
                DB::raw('MIN(amount) as min_amount')
            )
            ->groupBy('category');

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        return $query->orderBy('total_amount', 'desc')->get();
    }

    /**
     * لوحة المعلومات - محسّنة
     */
    public function dashboardSummary()
    {
        return Cache::remember('dashboard_summary', 300, function () {
            $today = now()->toDateString();
            $thisMonth = now()->startOfMonth()->toDateString();
            $thisYear = now()->startOfYear()->toDateString();

            return [
                // مبيعات
                'today_sales' => SalesInvoice::whereDate('invoice_date', $today)->sum('total'),
                'month_sales' => SalesInvoice::whereDate('invoice_date', '>=', $thisMonth)->sum('total'),
                'year_sales' => SalesInvoice::whereDate('invoice_date', '>=', $thisYear)->sum('total'),
                'sales_total' => SalesInvoice::sum('total'),

                // مشتريات
                'today_purchases' => PurchaseInvoice::whereDate('invoice_date', $today)->sum('total'),
                'month_purchases' => PurchaseInvoice::whereDate('invoice_date', '>=', $thisMonth)->sum('total'),
                'purchases_total' => PurchaseInvoice::sum('total'),

                // أرباح تقريبية
                'net_profit' => SalesInvoice::sum('total') - PurchaseInvoice::sum('total'),

                // عدادات
                'total_customers' => DB::table('customers')->count(),
                'total_suppliers' => DB::table('suppliers')->count(),
                'total_products' => DB::table('products')->count(),
                'products_count' => DB::table('products')->count(),

                // المخزون
                'low_stock_count' => DB::table('product_warehouse')
                    ->whereColumn('quantity', '<=', 'min_stock')
                    ->count(),

                'low_stock_products' => DB::table('product_warehouse')
                    ->join('products', 'products.id', '=', 'product_warehouse.product_id')
                    ->join('warehouses', 'warehouses.id', '=', 'product_warehouse.warehouse_id')
                    ->whereColumn('product_warehouse.quantity', '<=', 'product_warehouse.min_stock')
                    ->select('products.name', 'warehouses.name as warehouse', 'product_warehouse.quantity', 'product_warehouse.min_stock')
                    ->limit(10)
                    ->get(),

                // التحويلات
                'pending_transfers' => DB::table('warehouse_transfers')
                    ->where('status', 'pending')
                    ->count(),

                // الديون
                'total_debt' => abs(DB::table('customers')->where('balance', '<', 0)->sum('balance')),
                'total_credit' => DB::table('suppliers')->where('balance', '>', 0)->sum('balance'),

                // الخزينة
                'cash_balance' => $this->getCashBalance(),

                // الخشب 🪵
                'wood_total_m3' => DB::table('wood_stocks')->sum('volume_cm3') / 1000000,
                'wood_remaining_m3' => DB::table('wood_stocks')
                    ->selectRaw('SUM(volume_cm3 - COALESCE((SELECT SUM(volume_cm3_taken) FROM wood_dispensings WHERE wood_dispensings.wood_stock_id = wood_stocks.id), 0)) as remaining')
                    ->value('remaining') / 1000000,
                'wood_batches_count' => DB::table('wood_stocks')->count(),
                'wood_last_received' => DB::table('wood_stocks')->max('received_at'),
                'wood_dispensed_today' => DB::table('wood_dispensings')
                    ->whereDate('dispensed_at', $today)
                    ->sum('volume_cm3_taken') / 1000000,

                // آخر الفواتير
                'recent_invoices' => SalesInvoice::with('customer')
                    ->latest()
                    ->take(5)
                    ->get(),
            ];
        });
    }

    /**
     * رصيد الخزينة
     */
    private function getCashBalance(): float
    {
        $deposits = CashTransaction::deposits()->sum('amount');
        $withdrawals = CashTransaction::withdrawals()->sum('amount');
        return round($deposits - $withdrawals, 2);
    }

    /**
     * مسح الـ Cache
     */
    public function clearReportsCache()
    {
        Cache::forget('dashboard_summary');
        Cache::flush();
    }

    /**
     * Get all available categories
     */
    public function getCategories(): array
    {
        return CashTransaction::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();
    }

    /**
     * Wood Stock Report - مخزون الخشب الحالي
     */
    public function woodStockReport(array $filters = [])
    {
        $query = DB::table('wood_stocks')
            ->join('suppliers', 'suppliers.id', '=', 'wood_stocks.supplier_id')
            ->leftJoin('warehouses', 'warehouses.id', '=', 'wood_stocks.warehouse_id')
            ->select(
                'wood_stocks.id',
                'wood_stocks.purchase_reference',
                'suppliers.name as supplier_name',
                'warehouses.name as warehouse_name',
                'wood_stocks.length_cm',
                'wood_stocks.width_cm',
                'wood_stocks.thickness_cm',
                'wood_stocks.quantity',
                'wood_stocks.volume_cm3',
                'wood_stocks.unit_cost',
                'wood_stocks.total_cost',
                'wood_stocks.received_at',
                DB::raw('(wood_stocks.volume_cm3 - COALESCE((SELECT SUM(volume_cm3_taken) FROM wood_dispensings WHERE wood_dispensings.wood_stock_id = wood_stocks.id), 0)) as remaining_cm3')
            );

        // Apply filters
        if (!empty($filters['supplier_id'])) {
            $query->where('wood_stocks.supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('wood_stocks.warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('wood_stocks.received_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('wood_stocks.received_at', '<=', $filters['date_to']);
        }

        // Filter by minimum remaining stock
        if (!empty($filters['min_remaining_m3'])) {
            $query->havingRaw('(wood_stocks.volume_cm3 - COALESCE((SELECT SUM(volume_cm3_taken) FROM wood_dispensings WHERE wood_dispensings.wood_stock_id = wood_stocks.id), 0)) / 1000000 >= ?', [$filters['min_remaining_m3']]);
        }

        $stocks = $query->orderBy('wood_stocks.received_at', 'desc')->get();

        // Calculate additional fields
        return $stocks->map(function ($stock) {
            $remainingCm3 = $stock->remaining_cm3;
            $remainingM3 = $remainingCm3 / 1000000;
            $remainingM2 = $stock->thickness_cm > 0 ? $remainingCm3 / $stock->thickness_cm / 10000 : 0;

            return [
                'id' => $stock->id,
                'purchase_reference' => $stock->purchase_reference,
                'supplier_name' => $stock->supplier_name,
                'warehouse_name' => $stock->warehouse_name,
                'dimensions' => "{$stock->length_cm}×{$stock->width_cm}×{$stock->thickness_cm} سم",
                'total_m3' => round($stock->volume_cm3 / 1000000, 4),
                'dispensed_m3' => round(($stock->volume_cm3 - $remainingCm3) / 1000000, 4),
                'remaining_m3' => round($remainingM3, 4),
                'remaining_m2' => round($remainingM2, 4),
                'unit_cost' => $stock->unit_cost,
                'total_cost' => $stock->total_cost,
                'remaining_value' => round($remainingM3 * $stock->unit_cost, 2),
                'received_at' => $stock->received_at,
            ];
        });
    }

    /**
     * Wood Movement Report - حركة الخشب
     */
    public function woodMovementReport(array $filters = [])
    {
        $query = DB::table('wood_dispensings')
            ->join('wood_stocks', 'wood_stocks.id', '=', 'wood_dispensings.wood_stock_id')
            ->leftJoin('users', 'users.id', '=', 'wood_dispensings.user_id')
            ->leftJoin('customers', 'customers.id', '=', 'wood_dispensings.client_id')
            ->leftJoin('manufacturing_orders', 'manufacturing_orders.id', '=', 'wood_dispensings.manufacturing_order_id')
            ->leftJoin('sales_invoices', 'sales_invoices.id', '=', 'wood_dispensings.sales_invoice_id')
            ->select(
                'wood_dispensings.id',
                'wood_dispensings.dispensed_at',
                'wood_dispensings.volume_cm3_taken',
                'wood_dispensings.notes',
                'wood_stocks.length_cm',
                'wood_stocks.width_cm',
                'wood_stocks.thickness_cm',
                'wood_stocks.purchase_reference',
                'users.name as user_name',
                'customers.name as client_name',
                'manufacturing_orders.order_number',
                'sales_invoices.invoice_number as sales_invoice_number'
            );

        // Apply filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('wood_dispensings.dispensed_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('wood_dispensings.dispensed_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('wood_dispensings.user_id', $filters['user_id']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('wood_dispensings.client_id', $filters['client_id']);
        }

        return $query->orderBy('wood_dispensings.dispensed_at', 'desc')
            ->get()
            ->map(function ($dispensing) {
                return [
                    'id' => $dispensing->id,
                    'date' => $dispensing->dispensed_at,
                    'dimensions' => "{$dispensing->length_cm}×{$dispensing->width_cm}×{$dispensing->thickness_cm} سم",
                    'volume_m3' => round($dispensing->volume_cm3_taken / 1000000, 4),
                    'user_name' => $dispensing->user_name,
                    'client_name' => $dispensing->client_name,
                    'order_number' => $dispensing->order_number,
                    'invoice_number' => $dispensing->sales_invoice_number,
                    'notes' => $dispensing->notes,
                ];
            });
    }

    /**
     * Wood Cost in Production Report - تكلفة الخشب في الإنتاج
     */
    public function woodCostInProductionReport(array $filters = [])
    {
        $query = DB::table('manufacturing_orders')
            ->leftJoin('wood_dispensings', 'wood_dispensings.manufacturing_order_id', '=', 'manufacturing_orders.id')
            ->leftJoin('wood_stocks', 'wood_stocks.id', '=', 'wood_dispensings.wood_stock_id')
            ->select(
                'manufacturing_orders.id',
                'manufacturing_orders.order_number',
                'manufacturing_orders.product_name',
                'manufacturing_orders.quantity_produced',
                'manufacturing_orders.total_cost',
                DB::raw('COUNT(DISTINCT wood_dispensings.id) as wood_batches_count'),
                DB::raw('COALESCE(SUM(wood_dispensings.volume_cm3_taken), 0) as total_wood_cm3'),
                DB::raw('COALESCE(SUM(wood_dispensings.volume_cm3_taken * wood_stocks.unit_cost / 1000000), 0) as total_wood_cost')
            )
            ->groupBy('manufacturing_orders.id', 'manufacturing_orders.order_number', 'manufacturing_orders.product_name', 'manufacturing_orders.quantity_produced', 'manufacturing_orders.total_cost');

        // Apply filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('manufacturing_orders.created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('manufacturing_orders.created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['status'])) {
            $query->where('manufacturing_orders.status', $filters['status']);
        }

        return $query->orderBy('manufacturing_orders.created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'order_number' => $order->order_number,
                    'product_name' => $order->product_name,
                    'quantity_produced' => $order->quantity_produced,
                    'wood_batches_used' => $order->wood_batches_count,
                    'total_wood_m3' => round($order->total_wood_cm3 / 1000000, 4),
                    'wood_cost' => round($order->total_wood_cost, 2),
                    'total_cost' => $order->total_cost,
                    'wood_cost_percentage' => $order->total_cost > 0 ? round(($order->total_wood_cost / $order->total_cost) * 100, 1) : 0,
                ];
            });
    }
}



