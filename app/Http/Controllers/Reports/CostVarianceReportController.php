<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\ManufacturingOrder;
use App\Services\StandardCostingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Cost Variance Report — Gap 2.
 *
 * Exposes analytical views of every completed MO alongside its variance
 * snapshot. Drives both the on-screen report and a CSV export for ad-hoc
 * finance review.
 *
 * Filtering: by date range, by product, and by variance type.
 * Aggregates: net variance %, total favorable, total unfavorable,
 *             per-product top losers.
 */
class CostVarianceReportController extends Controller
{
    public function __construct(
        private StandardCostingService $standardCostingService,
    ) {}

    public function index(Request $request): View
    {
        $standardCostingOn = $this->standardCostingService->isEnabled();

        $query = ManufacturingOrder::query()
            ->with(['product', 'completer'])
            ->completed()
            ->whereNotNull('total_variance');

        // Filters
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('produced_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('produced_at', '<=', $dateTo);
        }
        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }
        if ($varianceType = $request->input('variance_type')) {
            if (in_array($varianceType, ['favorable', 'unfavorable', 'none'], true)) {
                $query->where('variance_type', $varianceType);
            }
        }

        $orders = $query->latest('produced_at')->paginate(25)->withQueryString();

        // Aggregate KPIs over the same filter set (without pagination)
        $baseClone = ManufacturingOrder::query()->completed()->whereNotNull('total_variance');
        if ($dateFrom = $request->input('date_from')) {
            $baseClone->whereDate('produced_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $baseClone->whereDate('produced_at', '<=', $dateTo);
        }
        if ($productId = $request->input('product_id')) {
            $baseClone->where('product_id', $productId);
        }

        $favorableTotal = (float) $baseClone->clone()->where('variance_type', 'favorable')->sum('total_variance');
        $unfavorableTotal = (float) $baseClone->clone()->where('variance_type', 'unfavorable')->sum('total_variance');
        $standardSum = (float) $baseClone->sum('standard_cost_at_completion');
        $actualSum = (float) $baseClone->sum('actual_cost_at_completion');

        $netVariance = $favorableTotal + $unfavorableTotal;
        $netVariancePct = $actualSum > 0 ? round(($netVariance / $actualSum) * 100, 2) : 0.0;

        // Top 5 products by absolute unfavorable variance (for dashboard callout)
        $topLoss = $baseClone->clone()
            ->selectRaw('product_id, product_name, SUM(total_variance) as sum_variance, COUNT(*) as orders_count')
            ->where('variance_type', 'unfavorable')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('sum_variance')
            ->limit(5)
            ->get();

        return view('reports.cost-variance.index', compact(
            'orders',
            'standardCostingOn',
            'favorableTotal',
            'unfavorableTotal',
            'netVariance',
            'netVariancePct',
            'standardSum',
            'actualSum',
            'topLoss'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filename = 'cost-variance-' . now()->format('Ymd-His') . '.csv';
        $standardCostingOn = $this->standardCostingService->isEnabled();

        $query = ManufacturingOrder::query()
            ->with(['product'])
            ->completed()
            ->whereNotNull('total_variance');

        foreach (['date_from' => 'produced_at', 'product_id' => 'product_id'] as $key => $column) {
            if ($val = $request->input($key)) {
                $query->when($column === 'produced_at',
                    fn($q) => $q->whereDate($column, '>=', $val),
                    fn($q) => $q->where($column, $val),
                );
            }
        }
        if ($val = $request->input('date_to')) {
            $query->whereDate('produced_at', '<=', $val);
        }
        if ($val = $request->input('variance_type')) {
            $query->where('variance_type', $val);
        }

        $orders = $query->latest('produced_at')->get();

        $callback = function () use ($orders) {
            $out = fopen('php://output', 'w');
            // BOM for Excel UTF-8 detection
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, [
                'Order #',
                'Product',
                'Completed At',
                'Quantity',
                'Standard Cost',
                'Actual Cost',
                'Total Variance',
                'Variance Type',
                'Material Variance',
                'Labor/Overhead Variance',
                'Journal Entry',
            ]);

            foreach ($orders as $o) {
                fputcsv($out, [
                    $o->order_number,
                    $o->product_name,
                    optional($o->produced_at)->format('Y-m-d H:i'),
                    $o->quantity_produced,
                    number_format((float) $o->standard_cost_at_completion, 4, '.', ''),
                    number_format((float) $o->actual_cost_at_completion, 4, '.', ''),
                    number_format((float) $o->total_variance, 4, '.', ''),
                    $o->variance_type,
                    number_format((float) $o->material_variance, 4, '.', ''),
                    number_format((float) $o->labor_overhead_variance, 4, '.', ''),
                    $o->variance_journal_entry_id,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
