<?php

namespace Tests\Unit;

use App\Support\NotificationPresenter;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificationPresenterTest extends TestCase
{
    public function test_it_normalizes_legacy_overdue_notification_data(): void
    {
        $notification = new DatabaseNotification;
        $notification->forceFill([
            'id' => 'notification-id',
            'data' => [
                'type' => 'overdue_invoice',
                'invoice_number' => 'S-100',
                'days_overdue' => 5,
            ],
            'read_at' => null,
            'created_at' => now(),
        ]);

        $presented = NotificationPresenter::present($notification);

        $this->assertSame('فاتورة مبيعات متأخرة', $presented['title']);
        $this->assertSame('الفاتورة رقم S-100 متأخرة 5 يوم', $presented['message']);
        $this->assertSame('warning', $presented['type']);
        $this->assertFalse($presented['is_read']);
    }
}
