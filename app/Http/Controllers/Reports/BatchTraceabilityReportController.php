<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\BatchTraceabilityReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Batch Traceability Report — Gap 4.
 *
 * Bidirectional traversal:
 *   - mode=source  → "أبحث عن دفعة خامات وأريد رؤية كل المنتجات التامة المشتقة منها"
 *   - mode=finished → "أبحث عن منتج تام وأريد رؤية كل الدفعات الخام المصدر"
 *
 * Both modes share the same UI skeleton; the service does the actual join.
 */
class BatchTraceabilityReportController extends Controller
{
    public function __construct(
        private BatchTraceabilityReportService $traceabilityService,
    ) {}

    public function index(Request $request): View
    {
        $mode = $request->input('mode', 'source');     // source | finished
        $code = trim((string) $request->input('code', ''));
        $results = collect();
        $notFound = false;

        if ($code !== '') {
            $results = $mode === 'finished'
                ? $this->traceabilityService->traceBackwards($code)
                : $this->traceabilityService->traceForwards($code);

            if ($results->isEmpty()) {
                $notFound = true;
            }
        }

        return view('reports.batch-traceability.index', compact(
            'mode', 'code', 'results', 'notFound'
        ));
    }
}
