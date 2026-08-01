<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification as LaravelNotification;
use Illuminate\Support\Facades\Notification;

class NotificationDeliveryService
{
    public function sendToAdmins(LaravelNotification $notification): int
    {
        if (! $this->isImportant($notification)) {
            return 0;
        }

        $admins = User::query()
            ->active()
            ->admins()
            ->get();

        if ($admins->isEmpty()) {
            return 0;
        }

        Notification::send($admins, $notification);

        return $admins->count();
    }

    public function isImportant(LaravelNotification $notification): bool
    {
        return in_array(
            $notification::class,
            config('notifications.important_types', []),
            true
        );
    }
}
