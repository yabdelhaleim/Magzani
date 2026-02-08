<?php
namespace App\Http\Controllers;

use App\Services\ReportingService;

class DashboardController extends Controller
{
    public function __construct(
        private ReportingService $reportingService
    ) {}

    public function index()
    {
        $summary = $this->reportingService->dashboardSummary();
        
        return view('Dashboard.dashboard', compact('summary'));
    }
}
