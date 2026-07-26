<?php

namespace App\Http\Controllers;

use App\Support\NotificationPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $notifications = $request->user()
                ->notifications()
                ->whereIn('type', config('notifications.important_types', []))
                ->latest()
                ->paginate((int) config('notifications.per_page', 20))
                ->through(fn (DatabaseNotification $notification): array => NotificationPresenter::present($notification));

            return view('notifications.index', compact('notifications'));
        } catch (\Exception $e) {
            Log::error('Failed to load notifications', ['error' => $e->getMessage()]);

            return view('notifications.index', ['notifications' => collect()])
                ->with('error', 'حدث خطأ أثناء تحميل الإشعارات.');
        }
    }

    public function open(Request $request, string $notification)
    {
        try {
            $ownedNotification = $this->findOwnedImportantNotification($request, $notification);
            $ownedNotification->markAsRead();

            $actionUrl = $this->safeActionUrl($request, $ownedNotification->data['action_url'] ?? null);

            return $actionUrl
                ? redirect()->to($actionUrl)
                : redirect()->route('notifications.index');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'الإشعار غير موجود');
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to open notification', [
                'notification_id' => $notification,
                'error'           => $e->getMessage(),
            ]);
            return back()->with('error', 'حدث خطأ أثناء فتح الإشعار.');
        }
    }

    public function read(Request $request, string $notification)
    {
        try {
            $this->findOwnedImportantNotification($request, $notification)->markAsRead();

            return back();
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notification,
                'error'           => $e->getMessage(),
            ]);
            return back()->with('error', 'حدث خطأ أثناء تحديد الإشعار كمقروء.');
        }
    }

    public function readAll(Request $request)
    {
        try {
            $request->user()
                ->unreadNotifications()
                ->whereIn('type', config('notifications.important_types', []))
                ->update(['read_at' => now()]);

            return back()->with('success', 'تم تحديد جميع الإشعارات كمقروءة.');
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء تحديث الإشعارات.');
        }
    }

    private function findOwnedImportantNotification(Request $request, string $id): DatabaseNotification
    {
        $notification = $request->user()
            ->notifications()
            ->whereIn('type', config('notifications.important_types', []))
            ->whereKey($id)
            ->first();

        abort_if(! $notification, 404);

        return $notification;
    }

    private function safeActionUrl(Request $request, mixed $actionUrl): ?string
    {
        if (! is_string($actionUrl) || $actionUrl === '') {
            return null;
        }

        if (str_starts_with($actionUrl, '/')) {
            return $request->getSchemeAndHttpHost().$actionUrl;
        }

        $host = parse_url($actionUrl, PHP_URL_HOST);

        return is_string($host) && strcasecmp($host, $request->getHost()) === 0
            ? $actionUrl
            : null;
    }
}
