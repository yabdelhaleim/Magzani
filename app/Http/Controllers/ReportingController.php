<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use App\Models\Category;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Exports\InventoryReportExport;
use App\Exports\FinancialReportExport;
use App\Exports\ProfitLossReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportingController extends Controller
{
    protected $reportingService;

    public function __construct(ReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    public function inventory(Request $request)
    {
        try {
            $warehouseId = $request->warehouse_id;
            
            // جلب المخزون
            $inventory = $this->reportingService->inventoryReport($warehouseId);
            
            // جلب المخازن
            $warehouses = Warehouse::all();
            $categories = Category::all();
            
            // إحصائيات المخزون
            $totalValue = $inventory->sum('total_value') ?? 0;
            $lowStockCount = $inventory->filter(function($item) {
                return $item->quantity <= $item->min_stock;
            })->count();
            $totalProducts = $inventory->count();
            
            return view('reports.inventory', [
                'inventory' => $inventory,
                'warehouses' => $warehouses,
                'categories' => $categories,
                'totalValue' => $totalValue,
                'lowStockCount' => $lowStockCount,
                'totalProducts' => $totalProducts
            ]);
            
        } catch (\Exception $e) {
            dd([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * تصدير تقرير المخزون إلى Excel
     */
    public function exportInventory(Request $request)
    {
        $warehouseId = $request->warehouse_id;
        $inventory = $this->reportingService->inventoryReport($warehouseId);
        
        return Excel::download(
            new InventoryReportExport($inventory, $warehouseId),
            'inventory-report-' . ($warehouseId ? "warehouse-{$warehouseId}-" : '') . date('Y-m-d') . '.xlsx'
        );
    }

   public function financial(Request $request)
    {
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : now();

        $report = $this->reportingService->profitLossReport($startDate, $endDate);
        $warehouses = Warehouse::all();
        
        // إضافة البيانات الإضافية
        $topProducts = $this->reportingService->topSellingProducts($startDate, $endDate, 5);
        $topCustomers = $this->reportingService->topCustomers($startDate, $endDate, 5);
        $dailySales = $this->reportingService->dailySalesReport($startDate, $endDate);
        
        return view('reports.financial', [
            'report' => $report,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'warehouses' => $warehouses,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'dailySales' => $dailySales
        ]);
    }

    /**
     * تصدير التقرير المالي إلى Excel
     */
    public function exportFinancial(Request $request)
    {
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : now();

        $report = $this->reportingService->profitLossReport($startDate, $endDate);
        
        return Excel::download(
            new FinancialReportExport($report, $startDate, $endDate),
            'financial-report-' . date('Y-m-d') . '.xlsx'
        );
    }

    public function profitLoss(Request $request)
    {
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : now();

        $report = $this->reportingService->profitLossReport($startDate, $endDate);
        $expensesByCategory = $this->reportingService->expensesByCategory($startDate, $endDate);
        $warehouses = Warehouse::all();

        return view('reports.profit-loss', [
            'report' => $report,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'expensesByCategory' => $expensesByCategory,
            'warehouses' => $warehouses
        ]);
    }

    /**
     * تصدير تقرير الأرباح والخسائر إلى Excel
     */
    public function exportProfitLoss(Request $request)
    {
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : now();

        $report = $this->reportingService->profitLossReport($startDate, $endDate);
        $expensesByCategory = $this->reportingService->expensesByCategory($startDate, $endDate);

        return Excel::download(
            new ProfitLossReportExport($report, $startDate, $endDate, $expensesByCategory),
            'profit-loss-report-' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Wood Stock Report - مخزون الخشب الحالي
     */
    public function woodStock(Request $request)
    {
        $filters = [
            'supplier_id' => $request->supplier_id,
            'warehouse_id' => $request->warehouse_id,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'min_remaining_m3' => $request->min_remaining_m3,
        ];

        $stocks = $this->reportingService->woodStockReport($filters);

        // Get suppliers and warehouses for filters
        $suppliers = \App\Models\Supplier::all();
        $warehouses = Warehouse::all();

        // Calculate summary
        $summary = [
            'total_batches' => $stocks->count(),
            'total_m3' => $stocks->sum('total_m3'),
            'remaining_m3' => $stocks->sum('remaining_m3'),
            'remaining_value' => $stocks->sum('remaining_value'),
        ];

        return view('reports.wood-stock', [
            'stocks' => $stocks,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'summary' => $summary,
        ]);
    }

    /**
     * Wood Movement Report - حركة الخشب
     */
    public function woodMovement(Request $request)
    {
        $filters = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'user_id' => $request->user_id,
            'client_id' => $request->client_id,
        ];

        $movements = $this->reportingService->woodMovementReport($filters);

        // Get users and customers for filters
        $users = \App\Models\User::all();
        $customers = \App\Models\Customer::all();

        return view('reports.wood-movement', [
            'movements' => $movements,
            'users' => $users,
            'customers' => $customers,
        ]);
    }

    /**
     * Wood Cost in Production Report - تكلفة الخشب في الإنتاج
     */
    public function woodCostProduction(Request $request)
    {
        $filters = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'status' => $request->status,
        ];

        $report = $this->reportingService->woodCostInProductionReport($filters);

        return view('reports.wood-cost-production', [
            'report' => $report,
        ]);
    }
}