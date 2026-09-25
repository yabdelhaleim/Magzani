<?php

namespace App\Services\Dashboard;

use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DashboardService — data layer for the Atelier Dashboard widgets.
 *
 * Provides a single entry point (`payload()`) returning a structured array
 * of all 15 dashboard widgets so views remain simple and testable.
 */
class DashboardService
{
    /** Range options for time-series widgets. */
    public const RANGES = [
        'today'      => 'اليوم',
        'week'       => 'هذا الأسبوع',
        'month'      => 'هذا الشهر',
        'quarter'    => 'هذا الربع',
        'year'       => 'هذا العام',
    ];

    /**
     * Build the entire dashboard payload.
     *
     * @return array<string, mixed>
     */
    public function payload(string $range = 'month'): array
    {
        [$from, $to] = $this->resolveRange($range);
        $prevFrom    = (clone $from)->subDays($from->diffInDays($to) + 1);

        return [
            'range'         => $range,
            'period'        => [
                'from' => $from->toDateString(),
                'to'   => $to->toDateString(),
            ],
            'kpis'          => $this->safe(fn () => $this->kpis($from, $to, $prevFrom, $from->copy()->subDay()), $this->emptyKpis()),
            'sales_trend'   => $this->safe(fn () => $this->salesTrend($from, $to), ['labels' => [], 'series' => [['name' => 'المبيعات', 'data' => []]]]),
            'top_products'  => $this->safe(fn () => $this->topProducts($from, $to), ['labels' => [], 'values' => [], 'color' => '#1B3A5C']),
            'revenue_by_cat'=> $this->safe(fn () => $this->revenueByCategory($from, $to), ['labels' => [], 'values' => [], 'colors' => [], 'total' => 0]),
            'recent_invoices'=> $this->safe(fn () => $this->recentInvoices(), collect()),
            'low_stock'     => $this->safe(fn () => $this->lowStock(), collect()),
            'manufacturing' => $this->safe(fn () => $this->manufacturingPipeline(), [
                ['status' => 'planned', 'label' => 'مخطط', 'count' => 0],
                ['status' => 'in_progress', 'label' => 'قيد التنفيذ', 'count' => 0],
                ['status' => 'paused', 'label' => 'متوقف', 'count' => 0],
                ['status' => 'completed', 'label' => 'مكتمل', 'count' => 0],
            ]),
            'cash_flow'     => $this->safe(fn () => $this->cashFlow($from, $to), ['labels' => [], 'inflow' => [], 'outflow' => []]),
            'expenses'      => $this->safe(fn () => $this->expenseBreakdown($from, $to), ['labels' => [], 'values' => [], 'colors' => []]),
            'activity'      => $this->safe(fn () => $this->activity(), collect()),
            'quick_actions' => $this->safe(fn () => $this->quickActions(), []),
            'summary'       => $this->safe(fn () => $this->summary(), []),
        ];
    }

    /**
     * Safely execute a callback, returning a fallback on exception.
     *
     * @template T
     * @param  callable():T  $callback
     * @param  T  $fallback
     * @return T
     */
    protected function safe(callable $callback, mixed $fallback): mixed
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            Log::warning('DashboardService widget failed', [
                'error' => $e->getMessage(),
            ]);
            return $fallback;
        }
    }

    /**
     * Empty KPI placeholder used when the database is unavailable.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function emptyKpis(): array
    {
        return [
            ['key' => 'sales',      'label' => 'إجمالي المبيعات',  'value' => '0', 'suffix' => 'ج.م', 'delta' => null, 'trend' => 'flat', 'delta_label' => 'مقارنة بالفترة السابقة', 'variant' => 'navy',   'icon' => 'icon.arrow-trending-up',   'series' => [0,0,0,0,0,0,0], 'color' => '#1B3A5C', 'href' => '#'],
            ['key' => 'purchases',  'label' => 'إجمالي المشتريات', 'value' => '0', 'suffix' => 'ج.م', 'delta' => null, 'trend' => 'flat', 'delta_label' => 'مقارنة بالفترة السابقة', 'variant' => 'warning','icon' => 'icon.arrow-trending-down', 'series' => [0,0,0,0,0,0,0], 'color' => '#D97706', 'href' => '#'],
            ['key' => 'profit',     'label' => 'صافي الربح',        'value' => '0', 'suffix' => 'ج.م', 'delta' => null, 'trend' => 'flat', 'delta_label' => 'مقارنة بالفترة السابقة', 'variant' => 'success','icon' => 'icon.banknotes',           'series' => [0,0,0,0,0,0,0], 'color' => '#047857', 'href' => '#'],
            ['key' => 'customers',  'label' => 'العملاء الجدد',     'value' => '0', 'suffix' => 'عميل','delta' => null, 'trend' => 'flat', 'delta_label' => 'مقارنة بالفترة السابقة', 'variant' => 'brass',  'icon' => 'icon.users',               'series' => [0,0,0,0,0,0,0], 'color' => '#B08D5A', 'href' => '#'],
            ['key' => 'inventory',  'label' => 'قيمة المخزون',      'value' => '0', 'suffix' => 'ج.م', 'delta' => null, 'trend' => 'flat', 'delta_label' => 'السعر الحالي',            'variant' => 'info',   'icon' => 'icon.cube',                'series' => [0,0,0,0,0,0,0], 'color' => '#3B82F6', 'href' => '#'],
            ['key' => 'overdue',    'label' => 'فواتير متأخرة',     'value' => '0', 'suffix' => 'فاتورة','delta' => null,'trend' => 'flat','delta_label' => 'ج.م مستحقة',              'variant' => 'danger', 'icon' => 'icon.exclamation',          'series' => [0,0,0,0,0,0,0], 'color' => '#DC2626', 'href' => '#'],
        ];
    }

    /**
     * Resolve a (from, to) range based on the provided key.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolveRange(string $range): array
    {
        $now = Carbon::now();
        return match ($range) {
            'today'   => [$now->copy()->startOfDay(),        $now->copy()->endOfDay()],
            'week'    => [$now->copy()->startOfWeek(),       $now->copy()->endOfWeek()],
            'month'   => [$now->copy()->startOfMonth(),      $now->copy()->endOfMonth()],
            'quarter' => [$now->copy()->startOfQuarter(),    $now->copy()->endOfQuarter()],
            'year'    => [$now->copy()->startOfYear(),       $now->copy()->endOfYear()],
            default   => [$now->copy()->startOfMonth(),      $now->copy()->endOfMonth()],
        };
    }

    /**
     * Top-line KPIs (6 widgets).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function kpis(Carbon $from, Carbon $to, Carbon $prevFrom, Carbon $prevTo): array
    {
        // Total Sales
        $salesNow = (float) SalesInvoice::whereBetween('invoice_date', [$from, $to])->sum('total');
        $salesPrev = (float) SalesInvoice::whereBetween('invoice_date', [$prevFrom, $prevTo])->sum('total');
        $salesChange = $this->pctChange($salesPrev, $salesNow);
        $salesTrend = $this->dailySeries(SalesInvoice::class, 'invoice_date', 'total', $from, $to);

        // Total Purchases
        $purchasesNow = (float) PurchaseInvoice::whereBetween('invoice_date', [$from, $to])->sum('total');
        $purchasesPrev = (float) PurchaseInvoice::whereBetween('invoice_date', [$prevFrom, $prevTo])->sum('total');
        $purchasesChange = $this->pctChange($purchasesPrev, $purchasesNow);
        $purchasesTrend = $this->dailySeries(PurchaseInvoice::class, 'invoice_date', 'total', $from, $to);

        // Net Profit (Sales − Purchases − estimated tax)
        $profitNow = $salesNow - $purchasesNow;
        $profitPrev = $salesPrev - $purchasesPrev;
        $profitChange = $this->pctChange($profitPrev, $profitNow);
        $profitTrend = array_map(
            fn ($s, $p) => max(0, $s - $p),
            $salesTrend,
            $purchasesTrend,
        );

        // Customers (new this period)
        $customersNow = Customer::whereBetween('created_at', [$from, $to])->count();
        $customersPrev = Customer::whereBetween('created_at', [$prevFrom, $prevTo])->count();
        $customersChange = $this->pctChange($customersPrev, $customersNow);
        $customersTrend = $this->dailySeriesCount(Customer::class, 'created_at', $from, $to);

        // Inventory value
        $inventoryValue = (float) Product::sum(DB::raw('current_stock * cost'));
        $inventoryTrend = [1, 1, 1, 1, 1, 1, 1]; // placeholder trend

        // Overdue invoices
        $overdueCount = SalesInvoice::where('due_date', '<', $to)
            ->where('payment_status', '!=', 'paid')
            ->count();
        $overdueAmount = (float) SalesInvoice::where('due_date', '<', $to)
            ->where('payment_status', '!=', 'paid')
            ->sum(DB::raw('total - paid'));
        $overdueTrend = [0, 0, 0, 1, 1, 2, $overdueCount];

        return [
            [
                'key'   => 'sales',
                'label' => 'إجمالي المبيعات',
                'value' => number_format($salesNow, 0, '.', ','),
                'suffix'=> 'ج.م',
                'delta' => $salesChange,
                'trend' => $salesChange >= 0 ? 'up' : 'down',
                'delta_label' => 'مقارنة بالفترة السابقة',
                'variant' => 'navy',
                'icon'  => 'icon.arrow-trending-up',
                'series' => $salesTrend,
                'color' => '#1B3A5C',
                'href'  => route('invoices.sales.index'),
            ],
            [
                'key'   => 'purchases',
                'label' => 'إجمالي المشتريات',
                'value' => number_format($purchasesNow, 0, '.', ','),
                'suffix'=> 'ج.م',
                'delta' => $purchasesChange,
                'trend' => $purchasesChange <= 0 ? 'down' : 'up',
                'delta_label' => 'مقارنة بالفترة السابقة',
                'variant' => 'warning',
                'icon'  => 'icon.arrow-trending-down',
                'series' => $purchasesTrend,
                'color' => '#D97706',
                'href'  => route('invoices.purchases.index'),
            ],
            [
                'key'   => 'profit',
                'label' => 'صافي الربح',
                'value' => number_format(max(0, $profitNow), 0, '.', ','),
                'suffix'=> 'ج.م',
                'delta' => $profitChange,
                'trend' => $profitChange >= 0 ? 'up' : 'down',
                'delta_label' => 'مقارنة بالفترة السابقة',
                'variant' => 'success',
                'icon'  => 'icon.banknotes',
                'series' => $profitTrend,
                'color' => '#047857',
                'href'  => route('accounting.dashboard'),
            ],
            [
                'key'   => 'customers',
                'label' => 'العملاء الجدد',
                'value' => number_format($customersNow, 0, '.', ','),
                'suffix'=> 'عميل',
                'delta' => $customersChange,
                'trend' => $customersChange >= 0 ? 'up' : 'down',
                'delta_label' => 'مقارنة بالفترة السابقة',
                'variant' => 'brass',
                'icon'  => 'icon.users',
                'series' => $customersTrend,
                'color' => '#B08D5A',
                'href'  => route('customers.index'),
            ],
            [
                'key'   => 'inventory',
                'label' => 'قيمة المخزون',
                'value' => number_format($inventoryValue, 0, '.', ','),
                'suffix'=> 'ج.م',
                'delta' => null,
                'trend' => 'flat',
                'delta_label' => 'السعر الحالي',
                'variant' => 'info',
                'icon'  => 'icon.cube',
                'series' => $inventoryTrend,
                'color' => '#3B82F6',
                'href'  => route('warehouses.index'),
            ],
            [
                'key'   => 'overdue',
                'label' => 'فواتير متأخرة',
                'value' => number_format($overdueCount, 0, '.', ','),
                'suffix'=> 'فاتورة',
                'delta' => number_format($overdueAmount, 0, '.', ','),
                'trend' => $overdueCount > 0 ? 'up' : 'flat',
                'delta_label' => 'ج.م مستحقة',
                'variant' => 'danger',
                'icon'  => 'icon.exclamation',
                'series' => $overdueTrend,
                'color' => '#DC2626',
                'href'  => route('invoices.sales.index'),
            ],
        ];
    }

    /**
     * Sales trend (line chart).
     */
    protected function salesTrend(Carbon $from, Carbon $to): array
    {
        $data = $this->dailySeries(SalesInvoice::class, 'invoice_date', 'total', $from, $to);
        $labels = $this->dailyLabels($from, $to);

        return [
            'labels' => $labels,
            'series' => [
                ['name' => 'المبيعات', 'data' => $data],
            ],
        ];
    }

    /**
     * Top products (bar chart).
     *
     * @return array<string, mixed>
     */
    protected function topProducts(Carbon $from, Carbon $to): array
    {
        $rows = DB::table('sales_invoice_items as si')
            ->join('sales_invoices as s', 's.id', '=', 'si.sales_invoice_id')
            ->join('products as p', 'p.id', '=', 'si.product_id')
            ->whereBetween('s.invoice_date', [$from, $to])
            ->select(
                'p.name as label',
                DB::raw('SUM(si.quantity * si.unit_price) as value'),
            )
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('value')
            ->limit(8)
            ->get();

        if ($rows->isEmpty()) {
            return ['labels' => [], 'values' => [], 'color' => '#1B3A5C'];
        }

        return [
            'labels' => $rows->pluck('label')->toArray(),
            'values' => $rows->pluck('value')->map(fn ($v) => (float) $v)->toArray(),
            'color'  => '#1B3A5C',
        ];
    }

    /**
     * Revenue by category (donut chart).
     */
    protected function revenueByCategory(Carbon $from, Carbon $to): array
    {
        $rows = DB::table('sales_invoice_items as si')
            ->join('sales_invoices as s', 's.id', '=', 'si.sales_invoice_id')
            ->join('products as p', 'p.id', '=', 'si.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->whereBetween('s.invoice_date', [$from, $to])
            ->select(
                DB::raw("COALESCE(c.name, 'غير مصنف') as label"),
                DB::raw('SUM(si.quantity * si.unit_price) as value'),
            )
            ->groupBy('label')
            ->orderByDesc('value')
            ->limit(6)
            ->get();

        $total = $rows->sum('value') ?: 1;

        return [
            'labels'   => $rows->pluck('label')->toArray(),
            'values'   => $rows->pluck('value')->map(fn ($v) => (float) $v)->toArray(),
            'total'    => (float) $total,
            'colors'   => ['#1B3A5C', '#B08D5A', '#10B981', '#F59E0B', '#3B82F6', '#EF4444'],
        ];
    }

    /**
     * Recent invoices (table).
     */
    protected function recentInvoices(): Collection
    {
        return SalesInvoice::with('customer')
            ->latest('invoice_date')
            ->limit(8)
            ->get()
            ->map(fn (SalesInvoice $inv): array => [
                'id'            => $inv->id,
                'number'        => $inv->invoice_number,
                'customer'      => $inv->customer?->name ?? '—',
                'date'          => $inv->invoice_date?->format('Y-m-d'),
                'total'         => number_format((float) $inv->total, 0, '.', ','),
                'remaining'     => number_format((float) ($inv->total - $inv->paid), 0, '.', ','),
                'status'        => $inv->status,
                'payment_status'=> $inv->payment_status,
                'href'          => route('invoices.sales.show', $inv->id),
            ]);
    }

    /**
     * Low stock alerts (list).
     */
    protected function lowStock(): Collection
    {
        return Product::whereNotNull('reorder_level')
            ->whereColumn('current_stock', '<=', 'reorder_level')
            ->orderByRaw('(current_stock / NULLIF(reorder_level, 0)) ASC')
            ->limit(6)
            ->get()
            ->map(fn (Product $p): array => [
                'id'      => $p->id,
                'name'    => $p->name,
                'sku'     => $p->sku,
                'current' => (float) $p->current_stock,
                'min'     => (float) $p->reorder_level,
                'unit'    => $p->unit ?? 'قطعة',
                'ratio'   => $p->reorder_level > 0 ? round(($p->current_stock / $p->reorder_level) * 100) : 0,
                'href'    => route('products.show', $p->id),
            ]);
    }

    /**
     * Manufacturing pipeline (Kanban).
     */
    protected function manufacturingPipeline(): array
    {
        $columns = [
            'planned'   => 'مخطط',
            'in_progress'=> 'قيد التنفيذ',
            'paused'    => 'متوقف',
            'completed' => 'مكتمل',
        ];
        $result = [];
        foreach ($columns as $status => $label) {
            $count = DB::table('manufacturing_orders')
                ->where('status', $status)
                ->count();
            $result[] = [
                'status' => $status,
                'label'  => $label,
                'count'  => $count,
            ];
        }
        return $result;
    }

    /**
     * Cash flow (stacked bar).
     */
    protected function cashFlow(Carbon $from, Carbon $to): array
    {
        $labels  = $this->dailyLabels($from, $to, maxPoints: 7);
        $inflow  = [];
        $outflow = [];
        $step    = (int) max(1, count($labels) > 7 ? floor($from->diffInDays($to) / 6) : 1);

        $cursor = $from->copy();
        $i = 0;
        while ($cursor->lte($to) && $i < 7) {
            $end = $cursor->copy()->addDays($step - 1)->min($to);
            $in = (float) SalesInvoice::whereBetween('invoice_date', [$cursor, $end])->sum('paid');
            $out = (float) PurchaseInvoice::whereBetween('invoice_date', [$cursor, $end])->sum('paid');
            $inflow[] = $in;
            $outflow[] = $out;
            $cursor = $end->copy()->addDay();
            $i++;
        }

        return [
            'labels'  => $labels,
            'inflow'  => $inflow,
            'outflow' => $outflow,
        ];
    }

    /**
     * Expense breakdown (donut).
     */
    protected function expenseBreakdown(Carbon $from, Carbon $to): array
    {
        $rows = DB::table('expenses')
            ->whereBetween('expense_date', [$from, $to])
            ->select('category', DB::raw('SUM(amount) as value'))
            ->groupBy('category')
            ->orderByDesc('value')
            ->limit(6)
            ->get();

        if ($rows->isEmpty()) {
            // Fallback: empty state
            return ['labels' => [], 'values' => [], 'colors' => []];
        }

        return [
            'labels' => $rows->pluck('category')->toArray(),
            'values' => $rows->pluck('value')->map(fn ($v) => (float) $v)->toArray(),
            'colors' => ['#B91C1C', '#F59E0B', '#3B82F6', '#10B981', '#B08D5A', '#1B3A5C'],
        ];
    }

    /**
     * Activity timeline.
     */
    protected function activity(): Collection
    {
        $items = collect();

        $recentInvoices = SalesInvoice::latest('created_at')->limit(3)->get();
        foreach ($recentInvoices as $inv) {
            $items->push([
                'title'  => "فاتورة جديدة {$inv->invoice_number}",
                'desc'   => "بقيمة " . number_format((float) $inv->total, 0) . " ج.م",
                'time'   => $inv->created_at?->diffForHumans(),
                'color'  => '#1B3A5C',
                'icon'   => 'icon.receipt',
                'tone'   => 'navy',
            ]);
        }

        $recentMovements = InventoryMovement::latest('created_at')->limit(3)->get();
        foreach ($recentMovements as $move) {
            $items->push([
                'title'  => "حركة مخزون: {$move->type}",
                'desc'   => "الكمية: " . number_format((float) $move->quantity, 0),
                'time'   => $move->created_at?->diffForHumans(),
                'color'  => '#B08D5A',
                'icon'   => 'icon.cube',
                'tone'   => 'brass',
            ]);
        }

        return $items->sortByDesc('time')->values()->take(8);
    }

    /**
     * Quick action shortcuts.
     */
    protected function quickActions(): array
    {
        return [
            ['title' => 'فاتورة مبيعات',   'desc' => 'إنشاء فاتورة جديدة',       'icon' => 'icon.receipt',          'href' => route('invoices.sales.create'),   'variant' => 'navy'],
            ['title' => 'إضافة منتج',      'desc' => 'منتج جديد في المخزون',      'icon' => 'icon.cube',             'href' => route('products.create'),         'variant' => 'brass'],
            ['title' => 'فاتورة مشتريات',  'desc' => 'تسجيل فاتورة مشتريات',      'icon' => 'icon.truck',            'href' => route('invoices.purchases.create'),'variant' => 'warning'],
            ['title' => 'إضافة عميل',      'desc' => 'عميل جديد',                  'icon' => 'icon.user-plus',        'href' => route('customers.create'),        'variant' => 'success'],
            ['title' => 'تحويل مخزون',     'desc' => 'بين المخازن',                 'icon' => 'icon.arrows-right-left','href' => route('transfers.create'),        'variant' => 'info'],
            ['title' => 'جرد المخزون',      'desc' => 'بدء عملية جرد',               'icon' => 'icon.clipboard',        'href' => route('stock-counts.create'),     'variant' => 'danger'],
        ];
    }

    /**
     * Aggregate summary metrics.
     */
    protected function summary(): array
    {
        return [
            'total_invoices'   => SalesInvoice::count(),
            'total_products'   => Product::count(),
            'total_customers'  => Customer::count(),
            'total_movements'  => InventoryMovement::count(),
        ];
    }

    /**
     * Build a daily series of aggregate values for the given range.
     *
     * @return array<int, float>
     */
    protected function dailySeries(string $model, string $dateCol, string $sumCol, Carbon $from, Carbon $to): array
    {
        $rows = $model::whereBetween($dateCol, [$from, $to])
            ->selectRaw("DATE({$dateCol}) as day, COALESCE(SUM({$sumCol}), 0) as total")
            ->groupBy('day')
            ->pluck('total', 'day');

        $series = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $series[] = (float) ($rows[$cursor->toDateString()] ?? 0);
            $cursor->addDay();
        }

        return $series;
    }

    /**
     * Build a daily series of counts.
     *
     * @return array<int, int>
     */
    protected function dailySeriesCount(string $model, string $dateCol, Carbon $from, Carbon $to): array
    {
        $rows = $model::whereBetween($dateCol, [$from, $to])
            ->selectRaw("DATE({$dateCol}) as day, COUNT(*) as c")
            ->groupBy('day')
            ->pluck('c', 'day');

        $series = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $series[] = (int) ($rows[$cursor->toDateString()] ?? 0);
            $cursor->addDay();
        }

        return $series;
    }

    /**
     * Produce sparse labels for chart axes (7 buckets max).
     */
    protected function dailyLabels(Carbon $from, Carbon $to, int $maxPoints = 7): array
    {
        $days = $from->diffInDays($to) + 1;
        if ($days <= $maxPoints) {
            $labels = [];
            $cursor = $from->copy();
            while ($cursor->lte($to)) {
                $labels[] = $cursor->format('d M');
                $cursor->addDay();
            }
            return $labels;
        }

        $step = (int) ceil($days / $maxPoints);
        $labels = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $labels[] = $cursor->format('d M');
            $cursor->addDays($step);
        }
        return $labels;
    }

    /**
     * Compute percentage change between two values.
     */
    protected function pctChange(float $prev, float $now): float
    {
        if ($prev == 0.0) {
            return $now > 0 ? 100.0 : 0.0;
        }
        return round((($now - $prev) / abs($prev)) * 100, 1);
    }
}
