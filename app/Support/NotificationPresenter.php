<?php

namespace App\Support;

use Illuminate\Notifications\DatabaseNotification;

class NotificationPresenter
{
    public static function present(DatabaseNotification $notification): array
    {
        $data = $notification->data;
        $legacyType = $data['type'] ?? null;
        $type = in_array($legacyType, ['success', 'error', 'warning', 'info'], true)
            ? $legacyType
            : ($legacyType === 'overdue_invoice' ? 'warning' : 'info');
        $icon = self::iconClass((string) ($data['icon'] ?? 'bell'));

        return [
            'id' => $notification->id,
            'title' => self::title($data),
            'message' => self::message($data),
            'type' => $type,
            'icon' => $icon,
            'action_url' => is_string($data['action_url'] ?? null) ? $data['action_url'] : null,
            'is_read' => $notification->read_at !== null,
            'created_at' => $notification->created_at?->diffForHumans() ?? '',
        ];
    }

    private static function title(array $data): string
    {
        if (is_string($data['title'] ?? null) && $data['title'] !== '') {
            return $data['title'];
        }

        return ($data['type'] ?? null) === 'overdue_invoice'
            ? 'فاتورة مبيعات متأخرة'
            : 'إشعار جديد';
    }

    private static function message(array $data): string
    {
        if (is_string($data['message'] ?? null) && $data['message'] !== '') {
            return $data['message'];
        }

        if (($data['type'] ?? null) === 'overdue_invoice') {
            $number = $data['invoice_number'] ?? 'غير محدد';
            $days = $data['days_overdue'] ?? 0;

            return "الفاتورة رقم {$number} متأخرة {$days} يوم";
        }

        return 'لديك تحديث يحتاج إلى المراجعة.';
    }

    private static function iconClass(string $icon): string
    {
        return match ($icon) {
            'receipt' => 'fa-receipt',
            'shopping-cart' => 'fa-cart-shopping',
            'x-circle' => 'fa-circle-xmark',
            'dollar-sign' => 'fa-dollar-sign',
            'rotate-ccw' => 'fa-rotate-left',
            'rotate-cw' => 'fa-rotate-right',
            'alert-triangle' => 'fa-triangle-exclamation',
            'alert-circle' => 'fa-circle-exclamation',
            'info' => 'fa-circle-info',
            'truck' => 'fa-truck',
            'check-circle' => 'fa-circle-check',
            'clock' => 'fa-clock',
            default => 'fa-bell',
        };
    }
}
