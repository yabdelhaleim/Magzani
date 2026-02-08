<?php
namespace App\Notifications\Transfer;

use App\Models\WarehouseTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransferInitiatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public WarehouseTransfer $transfer;

    public function __construct(WarehouseTransfer $transfer)
    {
        $this->transfer = $transfer;
        $this->queue = 'notifications';
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'تحويل مخزون جديد',
            'message' => "تم إنشاء طلب تحويل رقم {$this->transfer->transfer_number}",
            'transfer_id' => $this->transfer->id,
            'transfer_number' => $this->transfer->transfer_number,
            'from_warehouse' => $this->transfer->fromWarehouse->name,
            'to_warehouse' => $this->transfer->toWarehouse->name,
            'items_count' => $this->transfer->items->count(),
            'status' => 'pending',
            'action_url' => route('warehouses.transfer'),
            'icon' => 'truck',
            'type' => 'info',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('طلب تحويل مخزون جديد')
            ->line("تم إنشاء طلب تحويل رقم: {$this->transfer->transfer_number}")
            ->line("من: {$this->transfer->fromWarehouse->name}")
            ->line("إلى: {$this->transfer->toWarehouse->name}")
            ->line("عدد الأصناف: {$this->transfer->items->count()}")
            ->action('عرض التحويل', route('warehouses.transfer'));
    }
}
