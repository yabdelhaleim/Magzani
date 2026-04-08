@extends('layouts.app')

@section('title', 'تفاصيل المستخدم')

@push('styles')
<style>
    :root {
        --tf-bg:          #f8fafc;
        --tf-surface:     #ffffff;
        --tf-surface2:    #f1f5f9;
        --tf-border:      #e2e8f0;

        --tf-indigo:      #4f46e5;
        --tf-indigo-soft: #eef2ff;
        --tf-green:       #059669;
        --tf-green-soft:  #d1fae5;
        --tf-red:         #dc2626;
        --tf-red-soft:    #fef2f2;
        --tf-amber:       #d97706;
        --tf-amber-soft:  #fef3c7;
        --tf-violet:      #7c3aed;

        --tf-text-h:      #1e293b;
        --tf-text-b:      #334155;
        --tf-text-m:      #64748b;
        --tf-text-d:      #94a3b8;

        --tf-shadow-card: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.04);
    }

    .tf-page {
        background: var(--tf-bg);
        background-image:
            radial-gradient(ellipse 100% 80% at 0% -10%,  rgba(79,70,229,0.05) 0%, transparent 50%),
            radial-gradient(ellipse 80% 60% at 100% 110%,  rgba(124,58,237,0.04) 0%, transparent 50%);
        min-height: 100vh;
        padding: 24px 20px;
    }

    @keyframes tfFadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .tf-section { animation: tfFadeUp 0.4s cubic-bezier(0.22,1,0.36,1) both; }

    .tf-card {
        background: var(--tf-surface); border-radius: 16px;
        border: 1px solid var(--tf-border);
        overflow: hidden; box-shadow: var(--tf-shadow-card);
        max-width: 700px; margin: 0 auto;
    }

    .tf-header-gradient {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border-radius: 16px 16px 0 0; padding: 1.5rem;
    }

    .tf-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 600;
        cursor: pointer; border: none; text-decoration: none;
    }
    .tf-btn-primary {
        background: white; color: var(--tf-indigo);
    }
    .tf-btn-primary:hover { background: var(--tf-indigo-soft); }

    .tf-back-btn {
        width: 40px; height: 40px; border-radius: 10px;
        background: rgba(255,255,255,0.2); color: white;
        display: flex; align-items: center; justify-content: center;
    }
    .tf-back-btn:hover { background: rgba(255,255,255,0.3); }

    .tf-avatar {
        width: 80px; height: 80px; border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; font-weight: 700; color: white;
    }

    .tf-badge {
        display: inline-flex; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;
    }

    .tf-detail-card {
        background: var(--tf-surface2); border-radius: 12px; padding: 1rem;
    }

    .tf-detail-icon {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
    }
</style>
@endpush

@section('content')
<div class="tf-page">
    <div class="tf-card tf-section">
        <div class="tf-header-gradient">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('users.index') }}" class="tf-back-btn">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <div>
                        <h2 class="text-xl font-bold">تفاصيل المستخدم</h2>
                        <p class="text-indigo-200 text-sm mt-1">معلومات حساب المستخدم</p>
                    </div>
                </div>
                <a href="{{ route('users.edit', $user->id) }}" class="tf-btn tf-btn-primary">
                    <i class="fas fa-edit"></i>تعديل
                </a>
            </div>
        </div>

        <div class="p-6">
            <div class="flex items-center gap-5 mb-6 pb-6" style="border-bottom: 1px solid var(--tf-border);">
                <div class="tf-avatar" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h3 class="text-xl font-bold" style="color: var(--tf-text-h);">{{ $user->name }}</h3>
                    <p style="color: var(--tf-text-m);">{{ $user->email }}</p>
                    @php $roleBg = $user->role === 'admin' ? 'var(--tf-red-soft)' : 'var(--tf-indigo-soft)'; @endphp
                    <span class="tf-badge" style="background: @php echo $roleBg; @endphp; color: @php echo $user->role === 'admin' ? 'var(--tf-red)' : 'var(--tf-indigo)'; @endphp; margin-top: 8px;">
                        {{ $user->role_name }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="tf-detail-card">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="tf-detail-icon" style="background: var(--tf-indigo-soft); color: var(--tf-indigo);">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <span style="color: var(--tf-text-m); font-size: 13px;">البريد الإلكتروني</span>
                    </div>
                    <p style="color: var(--tf-text-h); font-weight: 600;">{{ $user->email }}</p>
                </div>

                <div class="tf-detail-card">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="tf-detail-icon" style="background: var(--tf-green-soft); color: var(--tf-green);">
                            <i class="fas fa-phone"></i>
                        </div>
                        <span style="color: var(--tf-text-m); font-size: 13px;">الهاتف</span>
                    </div>
                    <p style="color: var(--tf-text-h); font-weight: 600;">{{ $user->phone ?? '-' }}</p>
                </div>

                <div class="tf-detail-card">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="tf-detail-icon" style="background: var(--tf-indigo-soft); color: var(--tf-indigo);">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <span style="color: var(--tf-text-m); font-size: 13px;">الدور</span>
                    </div>
                    <p style="color: var(--tf-text-h); font-weight: 600;">{{ $user->role_name }}</p>
                </div>

                <div class="tf-detail-card">
                    <div class="flex items-center gap-3 mb-2">
                        @php $activeBg = $user->is_active ? 'var(--tf-green-soft)' : 'var(--tf-surface2)'; @endphp
                        <div class="tf-detail-icon" style="background: @php echo $activeBg; @endphp; color: @php echo $user->is_active ? 'var(--tf-green)' : 'var(--tf-text-m)'; @endphp;">
                            <i class="fas {{ $user->is_active ? 'fa-check-circle' : 'fa-ban' }}"></i>
                        </div>
                        <span style="color: var(--tf-text-m); font-size: 13px;">الحالة</span>
                    </div>
                    @php $statusBg = $user->is_active ? 'var(--tf-green-soft)' : 'var(--tf-surface2)'; @endphp
                    <span class="tf-badge" style="background: @php echo $statusBg; @endphp; color: @php echo $user->is_active ? 'var(--tf-green)' : 'var(--tf-text-m)'; @endphp;">
                        {{ $user->is_active ? 'نشط' : 'غير نشط' }}
                    </span>
                </div>

                <div class="tf-detail-card">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="tf-detail-icon" style="background: var(--tf-amber-soft); color: var(--tf-amber);">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <span style="color: var(--tf-text-m); font-size: 13px;">تاريخ الإنشاء</span>
                    </div>
                    <p style="color: var(--tf-text-h); font-weight: 600;">{{ $user->created_at->format('Y-m-d') }}</p>
                </div>

                <div class="tf-detail-card">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="tf-detail-icon" style="background: var(--tf-amber-soft); color: var(--tf-amber);">
                            <i class="fas fa-calendar-edit"></i>
                        </div>
                        <span style="color: var(--tf-text-m); font-size: 13px;">آخر تحديث</span>
                    </div>
                    <p style="color: var(--tf-text-h); font-weight: 600;">{{ $user->updated_at->format('Y-m-d') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection