<?php
namespace App\Notifications\Transfer;

use App\Models\WarehouseTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransferCompletedNotification extends Notification implements ShouldQueue
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
            'title' => 'اكتمل تحويل المخزون',
            'message' => "تم إتمام التحويل رقم {$this->transfer->transfer_number}",
            'transfer_id' => $this->transfer->id,
            'transfer_number' => $this->transfer->transfer_number,
            'from_warehouse' => $this->transfer->fromWarehouse->name,
            'to_warehouse' => $this->transfer->toWarehouse->name,
            'action_url' => route('warehouses.transfer'),
            'icon' => 'check-circle',
            'type' => 'success',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->success()
            ->subject('✅ اكتمل تحويل المخزون')
            ->line("تم إتمام التحويل رقم: {$this->transfer->transfer_number}")
            ->line("من: {$this->transfer->fromWarehouse->name}")
            ->line("إلى: {$this->transfer->toWarehouse->name}")
            ->action('عرض التفاصيل', route('warehouses.transfer'));
    }
}
