<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Notifications\Payment\PaymentReceivedNotification;
use App\Notifications\SystemNotification;
use App\Services\NotificationDeliveryService;
use Tests\TestCase;

class NotificationDeliveryPolicyTest extends TestCase
{
    public function test_only_important_notifications_pass_the_delivery_policy(): void
    {
        $service = app(NotificationDeliveryService::class);

        $this->assertContains(SystemNotification::class, config('notifications.important_types'));
        $this->assertTrue($service->isImportant(new SystemNotification('عنوان', 'رسالة')));
        $this->assertFalse($service->isImportant(new PaymentReceivedNotification(new Payment)));
    }
}
