@extends('layouts.app')

@section('title', 'الإشعارات')
@section('page-title', 'الإشعارات')

@push('styles')
<style>
    .notifications-page { max-width: 920px; margin: 0 auto; }
    .notifications-card { background: #fff; border: 1px solid rgba(99, 102, 241, .12); border-radius: 20px; box-shadow: 0 10px 35px rgba(15, 23, 42, .07); overflow: hidden; }
    .notifications-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 22px 24px; border-bottom: 1px solid #eef2ff; background: linear-gradient(135deg, #f8faff, #fff); }
    .notifications-head h2 { margin: 0 0 4px; font-size: 20px; }
    .notifications-head p { margin: 0; color: #64748b; font-size: 13px; }
    .notifications-read-all { border: 1px solid #c7d2fe; background: #eef2ff; color: #4338ca; border-radius: 10px; padding: 9px 13px; font-size: 12px; font-weight: 700; cursor: pointer; }
    .notifications-list { display: grid; }
    .notification-row { display: grid; grid-template-columns: 44px minmax(0, 1fr) auto; gap: 14px; align-items: start; padding: 18px 22px; color: inherit; text-decoration: none; border-bottom: 1px solid #f1f5f9; transition: background .18s ease; }
    .notification-row:last-child { border-bottom: 0; }
    .notification-row:hover { background: #f8faff; }
    .notification-row.unread { background: #f5f7ff; box-shadow: inset -3px 0 #6366f1; }
    .notification-row.unread:hover { background: #eef2ff; }
    .notification-row-icon { width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; background: #eef2ff; color: #4f46e5; }
    .notification-row.warning .notification-row-icon { background: #fff7ed; color: #d97706; }
    .notification-row.error .notification-row-icon { background: #fef2f2; color: #dc2626; }
    .notification-row.success .notification-row-icon { background: #ecfdf5; color: #059669; }
    .notification-row-title { display: flex; align-items: center; gap: 7px; margin: 0 0 5px; color: #172033; font-size: 14px; font-weight: 800; }
    .notification-unread-dot { width: 7px; height: 7px; border-radius: 999px; background: #ef4444; flex: 0 0 auto; }
    .notification-row-message { margin: 0; color: #64748b; font-size: 13px; line-height: 1.7; }
    .notification-row-time { color: #94a3b8; font-size: 11px; white-space: nowrap; padding-top: 4px; }
    .notifications-empty { padding: 70px 20px; text-align: center; color: #64748b; }
    .notifications-empty-icon { width: 66px; height: 66px; margin: 0 auto 14px; border-radius: 20px; display: grid; place-items: center; background: #eef2ff; color: #6366f1; font-size: 25px; }
    .notifications-pagination { padding: 18px 22px; border-top: 1px solid #eef2ff; }
    @media (max-width: 640px) {
        .notifications-head { align-items: flex-start; flex-direction: column; }
        .notification-row { grid-template-columns: 40px minmax(0, 1fr); padding: 15px; }
        .notification-row-time { grid-column: 2; padding: 0; }
    }
</style>
@endpush

@section('content')
<div class="notifications-page">
    <div class="notifications-card">
        <div class="notifications-head">
            <div>
                <h2>الإشعارات المهمة</h2>
                <p>تظهر هنا التنبيهات التي تحتاج متابعة، وليس رسائل نجاح العمليات العادية.</p>
            </div>

            @if($notifications->getCollection()->contains(fn ($notification) => ! $notification['is_read']))
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="notifications-read-all">
                        <i class="fas fa-check-double" aria-hidden="true"></i>
                        تعليم الكل كمقروء
                    </button>
                </form>
            @endif
        </div>

        <div class="notifications-list">
            @forelse($notifications as $notification)
                <a href="{{ route('notifications.open', $notification['id']) }}"
                   class="notification-row {{ $notification['type'] }} {{ $notification['is_read'] ? '' : 'unread' }}">
                    <span class="notification-row-icon" aria-hidden="true">
                        <i class="fas {{ $notification['icon'] }}"></i>
                    </span>
                    <span>
                        <span class="notification-row-title">
                            @if(! $notification['is_read'])
                                <span class="notification-unread-dot" aria-label="غير مقروء"></span>
                            @endif
                            {{ $notification['title'] }}
                        </span>
                        <span class="notification-row-message">{{ $notification['message'] }}</span>
                    </span>
                    <span class="notification-row-time">{{ $notification['created_at'] }}</span>
                </a>
            @empty
                <div class="notifications-empty">
                    <div class="notifications-empty-icon"><i class="fas fa-bell-slash" aria-hidden="true"></i></div>
                    <h3>لا توجد إشعارات مهمة</h3>
                    <p>عندما يوجد تنبيه يحتاج متابعة سيظهر هنا.</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="notifications-pagination">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
