@extends('layouts.app')

@section('title', 'إدارة المستخدمين')

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
        --tf-slate:       #64748b;

        --tf-text-h:      #1e293b;
        --tf-text-b:      #334155;
        --tf-text-m:      #64748b;
        --tf-text-d:      #94a3b8;

        --tf-shadow-sm:   0 1px 3px rgba(0,0,0,0.05);
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
    }

    .tf-header-gradient {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border-radius: 16px 16px 0 0; padding: 1.5rem; color: white;
    }

    .tf-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        padding: 10px 18px; border-radius: 10px; font-size: 14px; font-weight: 600;
        cursor: pointer; border: none; text-decoration: none;
    }
    .tf-btn-primary {
        background: white; color: var(--tf-indigo);
    }
    .tf-btn-primary:hover { background: var(--tf-indigo-soft); }

    .tf-table { width: 100%; border-collapse: collapse; }
    .tf-table th {
        padding: 14px 16px; text-align: right;
        font-size: 12px; font-weight: 600; text-transform: uppercase;
        color: var(--tf-text-m); border-bottom: 1px solid var(--tf-border);
    }
    .tf-table td {
        padding: 14px 16px; color: var(--tf-text-b); font-size: 14px;
    }
    .tf-table tbody tr:hover { background: var(--tf-surface2); }

    .tf-avatar {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; color: white;
    }

    .tf-badge {
        display: inline-flex; padding: 4px 10px; border-radius: 16px; font-size: 12px; font-weight: 600;
    }

    .tf-action-btn {
        width: 32px; height: 32px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 12px; cursor: pointer; border: none;
    }

    .tf-alert {
        padding: 12px 16px; border-radius: 10px; margin-bottom: 16px;
        display: flex; align-items: center; gap: 8px; font-size: 14px;
    }
    .tf-alert-success { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-alert-danger { background: var(--tf-red-soft); color: var(--tf-red); }

    .tf-empty { padding: 3rem; text-align: center; }
    .tf-empty-icon {
        width: 80px; height: 80px; border-radius: 50%;
        background: var(--tf-surface2); display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem;
    }
</style>
@endpush

@section('content')
<div class="tf-page">
    <div class="tf-card">
        <div class="tf-header-gradient">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">إدارة المستخدمين</h2>
                    <p class="text-indigo-200 mt-1">إضافة وتعديل وإدارة حسابات المستخدمين</p>
                </div>
                <a href="{{ route('users.create') }}" class="tf-btn tf-btn-primary">
                    <i class="fas fa-plus"></i>إضافة مستخدم
                </a>
            </div>
        </div>

        <div class="p-5">
            @if(session('success'))
                <div class="tf-alert tf-alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="tf-alert tf-alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="tf-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الهاتف</th>
                            <th>الدور</th>
                            <th>الحالة</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td style="color: var(--tf-text-m);">{{ $loop->iteration }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="tf-avatar" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <span class="font-semibold" style="color: var(--tf-text-h);">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td style="color: var(--tf-text-m);">{{ $user->email }}</td>
                            <td style="color: var(--tf-text-m);">{{ $user->phone ?? '-' }}</td>
                            <td>
                                @php $roleBg = $user->role === 'admin' ? 'var(--tf-red-soft)' : 'var(--tf-indigo-soft)'; @endphp
                                <span class="tf-badge" style="background: @php echo $roleBg; @endphp; color: @php echo $user->role === 'admin' ? 'var(--tf-red)' : 'var(--tf-indigo)'; @endphp;">
                                    {{ $user->role_name }}
                                </span>
                            </td>
                            <td>
                                @php $activeBg = $user->is_active ? 'var(--tf-green-soft)' : 'var(--tf-surface2)'; @endphp
                                <span class="tf-badge" style="background: @php echo $activeBg; @endphp; color: @php echo $user->is_active ? 'var(--tf-green)' : 'var(--tf-text-m)'; @endphp;">
                                    {{ $user->is_active ? 'نشط' : 'غير نشط' }}
                                </span>
                            </td>
                            <td style="color: var(--tf-text-m); font-size: 13px;">{{ $user->created_at->format('Y-m-d') }}</td>
                            <td>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('users.show', $user->id) }}" 
                                       class="tf-action-btn" style="background: var(--tf-indigo-soft); color: var(--tf-indigo);" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user->id) }}" 
                                       class="tf-action-btn" style="background: var(--tf-amber-soft); color: var(--tf-amber);" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.toggle-active', $user->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                    @php
                                        $toggleBg = $user->is_active ? 'var(--tf-surface2)' : 'var(--tf-green-soft)';
                                        $toggleColor = $user->is_active ? 'var(--tf-text-m)' : 'var(--tf-green)';
                                    @endphp
                                    <button type="submit" class="tf-action-btn" 
                                            style="background: {{ $toggleBg }}; color: {{ $toggleColor }};" 
                                            title="{{ $user->is_active ? 'إلغاء تفعيل' : 'تفعيل' }}">
                                        <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                        </form>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="tf-action-btn" style="background: var(--tf-red-soft); color: var(--tf-red);" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($users->isEmpty())
                <div class="tf-empty">
                    <div class="tf-empty-icon"><i class="fas fa-users" style="color: var(--tf-text-d); font-size: 1.5rem;"></i></div>
                    <p style="color: var(--tf-text-m);">لا يوجد مستخدمون مسجلون</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection