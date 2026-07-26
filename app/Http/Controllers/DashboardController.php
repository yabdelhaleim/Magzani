<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct(
        private ReportingService $reportingService
    ) {}

    public function index()
    {
        try {
            $summary = $this->reportingService->dashboardSummary();

            return view('Dashboard.dashboard', compact('summary'));
        } catch (\Exception $e) {
            Log::error('Failed to load dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // تقديم بيانات افتراضية فارغة لتجنب انهيار الصفحة
            $summary = [
                'totalSales' => 0,
                'totalPurchases' => 0,
                'totalCustomers' => 0,
                'totalProducts' => 0,
                'lowStockCount' => 0,
                'overdueInvoices' => 0,
                'error' => true,
            ];

            return view('Dashboard.dashboard', compact('summary'))
                ->with('error', 'حدث خطأ أثناء تحميل لوحة التحكم. يتم عرض بيانات افتراضية.');
        }
    }
}
