<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SalesInvoice;
use App\Models\PosShift;
use App\Services\ReturnService;
use Illuminate\Support\Facades\Log;

class PosReturnPanel extends Component
{
    public $searchQuery = '';
    public $invoiceId = null;
    public $invoiceDetails = null;
    
    public $returnQuantities = [];
    public $returnNotes = '';

    public function mount()
    {
        // Require active shift
        if (!PosShift::getActiveShift()) {
            return redirect()->route('pos.index')->with('error', 'يجب فتح وردية أولاً قبل إجراء أي مرتجعات من الكاشير.');
        }
    }

    public function searchInvoice()
    {
        $this->reset(['invoiceId', 'invoiceDetails', 'returnQuantities', 'returnNotes']);
        
        if (empty($this->searchQuery)) {
            $this->dispatch('pos-alert', type: 'error', message: 'يرجى إدخال رقم الفاتورة أو رقم المرجع.');
            return;
        }

        $invoice = SalesInvoice::with(['items.product', 'returns.items', 'customer'])
            ->where('invoice_number', $this->searchQuery)
            ->orWhere('reference_number', $this->searchQuery)
            ->first();

        if (!$invoice) {
            $this->dispatch('pos-alert', type: 'error', message: 'لم يتم العثور على الفاتورة.');
            return;
        }

        if ($invoice->status === 'cancelled') {
            $this->dispatch('pos-alert', type: 'error', message: 'هذه الفاتورة ملغاة ولا يمكن إرجاعها.');
            return;
        }

        $this->invoiceId = $invoice->id;
        
        // Cache basic details to avoid holding full model in Livewire state
        $items = [];
        foreach ($invoice->items as $item) {
            $previousReturned = $invoice->returns->flatMap->items->where('sales_invoice_item_id', $item->id)->sum('quantity_returned');
            $availableQty = max(0, $item->quantity - $previousReturned);
            
            $items[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'غير معروف',
                'unit_price' => $item->unit_price,
                'quantity_sold' => $item->quantity,
                'quantity_returned_previously' => $previousReturned,
                'available_qty' => $availableQty,
            ];
            
            $this->returnQuantities[$item->id] = 0;
        }

        $this->invoiceDetails = [
            'invoice_number' => $invoice->invoice_number,
            'customer_name' => $invoice->customer ? $invoice->customer->name : 'عميل نقدي',
            'date' => $invoice->invoice_date,
            'total' => $invoice->total,
            'items' => $items
        ];
    }

    public function confirmReturn(ReturnService $returnService)
    {
        if (!PosShift::getActiveShift()) {
            $this->dispatch('pos-alert', type: 'error', message: 'تأكد من وجود وردية مفتوحة.');
            return;
        }

        if (!$this->invoiceId || !$this->invoiceDetails) {
            return;
        }

        $itemsToReturn = [];
        foreach ($this->returnQuantities as $itemId => $qty) {
            $qty = (float) $qty;
            if ($qty > 0) {
                // Find item details
                $itemDetail = collect($this->invoiceDetails['items'])->firstWhere('id', $itemId);
                
                if ($itemDetail) {
                    if ($qty > $itemDetail['available_qty']) {
                        $this->dispatch('pos-alert', type: 'error', message: "الكمية المرتجعة للصنف {$itemDetail['product_name']} أكبر من المتاح ({$itemDetail['available_qty']}).");
                        return;
                    }

                    $itemsToReturn[] = [
                        'sales_invoice_item_id' => $itemDetail['id'],
                        'product_id'            => $itemDetail['product_id'],
                        'quantity'              => $qty,
                        'price'                 => $itemDetail['unit_price'],
                        'item_condition'        => 'good'
                    ];
                }
            }
        }

        if (empty($itemsToReturn)) {
            $this->dispatch('pos-alert', type: 'error', message: 'لم يتم تحديد أي كميات للإرجاع.');
            return;
        }

        $data = [
            'sales_invoice_id' => $this->invoiceId,
            'notes'            => $this->returnNotes ?? 'مرتجع من نقطة البيع (POS)',
            'items'            => $itemsToReturn,
        ];

        try {
            $returnService->createSalesReturn($data);

            $this->reset(['invoiceId', 'invoiceDetails', 'searchQuery', 'returnQuantities', 'returnNotes']);
            $this->dispatch('pos-alert', type: 'success', message: '✅ تم إتمام المرتجع بنجاح، وخصم المبلغ من الوردية.');
            
        } catch (\Exception $e) {
            Log::error('POS Return Error: ' . $e->getMessage());
            $this->dispatch('pos-alert', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pos-return-panel')->layout('layouts.app');
    }
}
