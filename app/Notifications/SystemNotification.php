<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $title;
    public string $message;
    public ?string $actionUrl;
    public string $type;
    public string $icon;

    public function __construct(
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null,
        string $icon = 'bell'
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->actionUrl = $actionUrl;
        $this->icon = $icon;
        $this->queue = 'notifications';
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
            'icon' => $this->icon,
            'type' => $this->type,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
        ];
    }
}