<?php

namespace App\Services\Accounting;

use App\Models\AccountingPostingFailure;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\ManufacturingOrder;
use App\Models\Payment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\SalesInvoice;
use App\Models\SalesReturn;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * إعادة محاولة ترحيل العمليات الفاشلة — يُستخدم من UI والأمر المجدول
 */
class PostingFailureRetryService
{
    public function __construct(
        private PostingService $postingService,
    ) {}

    /**
     * @return array{success: bool, entry: ?JournalEntry, message: string}
     */
    public function retry(AccountingPostingFailure $failure): array
    {
        if ($failure->resolved) {
            return ['success' => true, 'entry' => null, 'message' => 'تم حل هذه العملية مسبقاً.'];
        }

        $eventKey = $failure->source_event_key ?? $failure->event_key;
        if (!$eventKey) {
            return ['success' => false, 'entry' => null, 'message' => 'لا يوجد مفتاح حدث للترحيل.'];
        }

        $existing = JournalEntry::where('source_event_key', $eventKey)->first();
        if ($existing) {
            $this->markResolved($failure);

            return [
                'success' => true,
                'entry'   => $existing,
                'message' => "الترحيل موجود بالفعل (قيد #{$existing->entry_number}).",
            ];
        }

        try {
            $entry = $this->retryByEventKey($eventKey, $failure);

            if ($entry) {
                $this->markResolved($failure);

                return [
                    'success' => true,
                    'entry'   => $entry,
                    'message' => "تم الترحيل بنجاح — قيد #{$entry->entry_number}.",
                ];
            }

            $failure->increment('attempts');

            return ['success' => false, 'entry' => null, 'message' => 'فشلت إعادة المحاولة — المصدر غير موجود أو الترحيل معطّل.'];
        } catch (\Throwable $e) {
            $failure->increment('attempts');
            $failure->update([
                'error_message' => $e->getMessage(),
                'error_class'   => get_class($e),
                'failed_at'     => now(),
            ]);

            Log::error("[PostingFailureRetry] Failed for key={$eventKey}: " . $e->getMessage());

            return ['success' => false, 'entry' => null, 'message' => $e->getMessage()];
        }
    }

    /**
     * تحليل مفتاح الحدث: sales_invoice:123:confirmed
     *
     * @return array{type: string, id: int, action: string}|null
     */
    public static function parseEventKey(string $key): ?array
    {
        $parts = explode(':', $key, 3);
        if (count($parts) < 2) {
            return null;
        }

        return [
            'type'   => $parts[0],
            'id'     => (int) $parts[1],
            'action' => $parts[2] ?? 'confirmed',
        ];
    }

    private function retryByEventKey(string $eventKey, AccountingPostingFailure $failure): ?JournalEntry
    {
        $parsed = self::parseEventKey($eventKey);
        if (!$parsed || $parsed['id'] <= 0) {
            return null;
        }

        return match ($parsed['type']) {
            'sales_invoice' => match ($parsed['action']) {
                'cogs'  => $this->retrySalesInvoiceCogs($parsed['id']),
                default => ($inv = SalesInvoice::find($parsed['id']))
                    ? $this->postingService->postSalesInvoice($inv) : null,
            },
            'purchase_invoice' => ($inv = PurchaseInvoice::find($parsed['id']))
                ? $this->postingService->postPurchaseInvoice($inv) : null,
            'payment' => ($p = Payment::find($parsed['id']))
                ? $this->postingService->postCustomerPayment($p) : null,
            'supplier_payment' => ($p = SupplierPayment::find($parsed['id']))
                ? $this->postingService->postSupplierPayment($p) : null,
            'sales_return' => ($r = SalesReturn::find($parsed['id']))
                ? $this->postingService->postSalesReturn($r) : null,
            'purchase_return' => ($r = PurchaseReturn::find($parsed['id']))
                ? $this->postingService->postPurchaseReturn($r) : null,
            'cash_tx' => ($tx = CashTransaction::find($parsed['id']))
                ? $this->postingService->postCashTransaction($tx) : null,
            'manufacturing' => match ($parsed['action']) {
                'completed' => ($o = ManufacturingOrder::find($parsed['id']))
                    ? $this->postingService->postManufacturingComplete($o) : null,
                default => ($o = ManufacturingOrder::find($parsed['id']))
                    ? $this->postingService->postManufacturingConfirm($o) : null,
            },
            default => null,
        };
    }

    private function retrySalesInvoiceCogs(int $invoiceId): ?JournalEntry
    {
        $invoice = SalesInvoice::with('items.product')->find($invoiceId);
        if (!$invoice) {
            return null;
        }

        $cogsAmount = 0.0;
        foreach ($invoice->items as $item) {
            $purchasePrice = (float) ($item->product?->purchase_price ?? 0);
            $baseQty       = (float) ($item->base_quantity ?? $item->quantity ?? 0);
            $cogsAmount   += $purchasePrice * $baseQty;
        }

        if ($cogsAmount <= 0) {
            return null;
        }

        return $this->postingService->postSalesInvoiceCogs($invoice, $cogsAmount);
    }

    private function markResolved(AccountingPostingFailure $failure): void
    {
        $failure->update([
            'resolved'    => true,
            'resolved_at' => now(),
            'resolved_by' => Auth::id(),
        ]);
    }
}
