<?php
namespace App\Notifications\Return;

use App\Models\PurchaseReturn;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PurchaseReturnNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public PurchaseReturn $purchaseReturn;

    public function __construct(PurchaseReturn $purchaseReturn)
    {
        $this->purchaseReturn = $purchaseReturn;
        $this->queue = 'notifications';
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'مرتجع مشتريات جديد',
            'message' => "تم معالجة مرتجع رقم {$this->purchaseReturn->return_number}",
            'return_id' => $this->purchaseReturn->id,
            'return_number' => $this->purchaseReturn->return_number,
            'total' => $this->purchaseReturn->total,
            'action_url' => route('purchase-returns.index'),
            'icon' => 'rotate-cw',
            'type' => 'info',
        ];
    }
}
 