<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\BatchPriceAdjustment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Services\LateInvoicePriceAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PurchaseInvoicePriceAdjustmentController — Gap 4.
 *
 * Hosts the "Late Invoice Price Adjustment" UI flow:
 *   - show()    → preview screen with audit + manual batch selector fallback
 *   - apply()   → execute the adjustment for ONE PurchaseInvoiceItem per
 *                 click (each click ⇒ one JE in the GL via the
 *                 LateInvoicePriceAdjustmentService)
 *
 * Per Q5: each invoice item = one JE. The UI may display multiple items
 * in a single form, but the apply() action is invoked per-item.
 */
class PurchaseInvoicePriceAdjustmentController extends Controller
{
    public function __construct(
        private LateInvoicePriceAdjustmentService $priceAdjustmentService,
    ) {}

    /**
     * Preview / detail page for an invoice item — exposes the linked
     * MaterialBatch and (if any) the genealogy subgraph downstream.
     */
    public function show(PurchaseInvoice $invoice, PurchaseInvoiceItem $item): View
    {
        $batch = \App\Models\MaterialBatch::query()
            ->whereHas('purchaseLinks', fn ($q) => $q->where('purchase_invoice_item_id', $item->id))
            ->with(['genealogyLinks.finishedBatch.product', 'priceAdjustments.journalEntry'])
            ->first();

        $previousAdjustments = $batch?->priceAdjustments ?? collect();
        $descendantCount = $batch ? $batch->genealogyLinks()->count() : 0;

        return view('purchase-invoices.price-adjustment.show', compact(
            'invoice', 'item', 'batch', 'previousAdjustments', 'descendantCount'
        ));
    }

    /**
     * Apply ONE late-invoice price adjustment. Per Q5 — one item = one JE.
     */
    public function apply(Request $request, PurchaseInvoice $invoice, PurchaseInvoiceItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'new_unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            $adjustment = $this->priceAdjustmentService
                ->adjustLateInvoicePrice($item, (float) $validated['new_unit_cost']);

            return back()->with(
                $adjustment->fallback_used ? 'warning' : 'success',
                $adjustment->fallback_used
                    ? "⚠️ تم ترحيل التسوية إلى حساب 5160 (Fallback). السبب: {$adjustment->fallback_reason}."
                    : '✅ تم تسجيل التسوية. القيد #' . ($adjustment->journal_entry_id ?? 'pending') . ' مُرحَّل.'
            );
        } catch (\Throwable $e) {
            return back()->with('error', '❌ ' . $e->getMessage())->withInput();
        }
    }
}
