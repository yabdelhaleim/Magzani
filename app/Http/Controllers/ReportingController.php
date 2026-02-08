<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use App\Models\Warehouse;
use Illuminate\Http\Request;

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
            
            // إحصائيات المخزون
            $totalValue = $inventory->sum('total_value') ?? 0;
            $lowStockCount = $inventory->filter(function($item) {
                return $item->quantity <= $item->min_stock;
            })->count();
            $totalProducts = $inventory->count();
            
            return view('reports.inventory', [
                'inventory' => $inventory,
                'warehouses' => $warehouses,
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

    public function profitLoss(Request $request)
    {
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : now();

        $report = $this->reportingService->profitLossReport($startDate, $endDate);
        $expensesByCategory = $this->reportingService->expensesByCategory($startDate, $endDate);
        
        return view('reports.profit-loss', [
            'report' => $report,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'expensesByCategory' => $expensesByCategory
        ]);
    }
}