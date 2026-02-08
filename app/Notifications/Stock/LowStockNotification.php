<?php

namespace App\Notifications\Stock;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Product $product;
    public Warehouse $warehouse;
    public int $currentQuantity;
    public int $minimumStock;
    public string $severity;

    public function __construct(
        Product $product, 
        Warehouse $warehouse, 
        int $currentQuantity, 
        int $minimumStock,
        string $severity
    ) {
        $this->product = $product;
        $this->warehouse = $warehouse;
        $this->currentQuantity = $currentQuantity;
        $this->minimumStock = $minimumStock;
        $this->severity = $severity;
        $this->queue = 'high-priority';
    }

    public function via($notifiable): array
    {
        $channels = ['database', 'broadcast'];
        
        // إضافة البريد في حالة critical
        if ($this->severity === 'critical') {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    public function toDatabase($notifiable): array
    {
        $shortage = $this->minimumStock - $this->currentQuantity;
        
        return [
            'title' => $this->getTitleBySeverity(),
            'message' => "المنتج {$this->product->name} وصل لحد النفاد في مخزن {$this->warehouse->name}",
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'warehouse_name' => $this->warehouse->name,
            'current_quantity' => $this->currentQuantity,
            'minimum_stock' => $this->minimumStock,
            'shortage' => $shortage,
            'severity' => $this->severity,
            'action_url' => route('products.show', $this->product->id),
            'icon' => $this->getIconBySeverity(),
            'type' => $this->getTypeBySeverity(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => $this->getTitleBySeverity(),
            'message' => "{$this->product->name} - متبقي {$this->currentQuantity} فقط",
            'severity' => $this->severity,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $shortage = $this->minimumStock - $this->currentQuantity;
        
        return (new MailMessage)
            ->error()
            ->subject('⚠️ تنبيه: مخزون منخفض جداً')
            ->greeting('تحذير مهم!')
            ->line("المنتج: **{$this->product->name}** ({$this->product->code})")
            ->line("المخزن: {$this->warehouse->name}")
            ->line("الكمية الحالية: **{$this->currentQuantity}**")
            ->line("الحد الأدنى: {$this->minimumStock}")
            ->line("النقص: {$shortage} وحدة")
            ->line('⚠️ يرجى اتخاذ الإجراء اللازم فوراً')
            ->action('عرض المنتج', route('products.show', $this->product->id));
    }

    private function getTitleBySeverity(): string
    {
        return match($this->severity) {
            'critical' => '🚨 تحذير: مخزون منتهي',
            'high' => '⚠️ تنبيه: مخزون منخفض جداً',
            'medium' => '⚡ تنبيه: مخزون منخفض',
            default => 'ℹ️ ملاحظة: مخزون قارب على النفاد',
        };
    }

    private function getIconBySeverity(): string
    {
        return match($this->severity) {
            'critical' => 'alert-triangle',
            'high' => 'alert-circle',
            'medium' => 'info',
            default => 'bell',
        };
    }

    private function getTypeBySeverity(): string
    {
        return match($this->severity) {
            'critical' => 'error',
            'high' => 'error',
            'medium' => 'warning',
            default => 'info',
        };
    }
}
