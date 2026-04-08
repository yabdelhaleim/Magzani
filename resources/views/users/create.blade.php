@extends('layouts.app')

@section('title', 'إضافة مستخدم جديد')

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
        --tf-red:         #dc2626;
        --tf-red-soft:    #fef2f2;
        --tf-amber:       #d97706;
        --tf-amber-soft:  #fef3c7;

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
        max-width: 600px; margin: 0 auto;
    }

    .tf-header-gradient {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border-radius: 16px 16px 0 0; padding: 1.5rem;
    }

    .tf-input, .tf-select {
        width: 100%; padding: 12px 16px;
        background: var(--tf-surface2);
        border: 1px solid var(--tf-border);
        border-radius: 10px; font-size: 14px;
        color: var(--tf-text-h); transition: all .2s;
    }
    .tf-input:focus, .tf-select:focus {
        outline: none; border-color: var(--tf-indigo);
        box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
    }
    .tf-input::placeholder { color: var(--tf-text-d); }
    .tf-input.error { border-color: var(--tf-red); }

    .tf-label {
        display: block; font-size: 13px; font-weight: 600;
        color: var(--tf-text-b); margin-bottom: 8px;
    }
    .tf-label i { margin-left: 6px; color: var(--tf-indigo); }
    .tf-label .required { color: var(--tf-red); }

    .tf-error {
        color: var(--tf-red); font-size: 12px; margin-top: 4px;
    }

    .tf-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 12px 24px; border-radius: 10px; font-size: 14px; font-weight: 600;
        cursor: pointer; border: none; text-decoration: none;
    }
    .tf-btn-primary {
        background: linear-gradient(135deg, var(--tf-indigo), #7c3aed);
        color: white;
    }
    .tf-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79,70,229,0.3); }
    .tf-btn-secondary {
        background: var(--tf-surface2); color: var(--tf-text-b);
        border: 1px solid var(--tf-border);
    }
    .tf-btn-secondary:hover { background: var(--tf-border); }

    .tf-back-btn {
        width: 40px; height: 40px; border-radius: 10px;
        background: rgba(255,255,255,0.2); color: white;
        display: flex; align-items: center; justify-content: center;
    }
    .tf-back-btn:hover { background: rgba(255,255,255,0.3); }

    .tf-checkbox-wrapper {
        display: flex; align-items: center; gap: 12px; cursor: pointer;
    }
    .tf-checkbox {
        width: 44px; height: 24px; background: #cbd5e1; border-radius: 12px;
        position: relative; transition: all .3s;
    }
    .tf-checkbox input { opacity: 0; width: 0; height: 0; }
    .tf-checkbox-dot {
        width: 18px; height: 18px; background: white; border-radius: 50%;
        position: absolute; top: 3px; left: 3px;
        transition: all .3s; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    .tf-checkbox input:checked + .tf-checkbox { background: var(--tf-green); }
    .tf-checkbox input:checked + .tf-checkbox .tf-checkbox-dot { transform: translateX(20px); }
</style>
@endpush

@section('content')
<div class="tf-page">
    <div class="tf-card tf-section">
        <div class="tf-header-gradient">
            <div class="flex items-center gap-4">
                <a href="{{ route('users.index') }}" class="tf-back-btn">
                    <i class="fas fa-arrow-right"></i>
                </a>
                <div>
                    <h2 class="text-xl font-bold">إضافة مستخدم جديد</h2>
                    <p class="text-indigo-200 text-sm mt-1">إنشاء حساب مستخدم جديد في النظام</p>
                </div>
            </div>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="p-6">
            @csrf

            <div class="space-y-5">
                <div>
                    <label for="name" class="tf-label">
                        <i class="fas fa-user"></i>الاسم <span class="required">*</span>
                    </label>
                    <input type="text" class="tf-input @error('name') error @enderror" 
                           id="name" name="name" value="{{ old('name') }}" 
                           placeholder="أدخل اسم المستخدم" required>
                    @error('name') <p class="tf-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="tf-label">
                        <i class="fas fa-envelope"></i>البريد الإلكتروني <span class="required">*</span>
                    </label>
                    <input type="email" class="tf-input @error('email') error @enderror" 
                           id="email" name="email" value="{{ old('email') }}" 
                           placeholder="أدخل البريد الإلكتروني" required>
                    @error('email') <p class="tf-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="phone" class="tf-label">
                            <i class="fas fa-phone"></i>الهاتف
                        </label>
                        <input type="text" class="tf-input @error('phone') error @enderror" 
                               id="phone" name="phone" value="{{ old('phone') }}" 
                               placeholder="أدخل رقم الهاتف">
                        @error('phone') <p class="tf-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="role" class="tf-label">
                            <i class="fas fa-user-tag"></i>الدور <span class="required">*</span>
                        </label>
                        <select class="tf-input @error('role') error @enderror" id="role" name="role" required>
                            <option value="">اختر الدور</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>مدير النظام</option>
                            <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>موظف</option>
                        </select>
                        @error('role') <p class="tf-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="password" class="tf-label">
                            <i class="fas fa-lock"></i>كلمة المرور <span class="required">*</span>
                        </label>
                        <input type="password" class="tf-input @error('password') error @enderror" 
                               id="password" name="password" placeholder="أدخل كلمة المرور" required>
                        @error('password') <p class="tf-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="tf-label">
                            <i class="fas fa-lock"></i>تأكيد كلمة المرور <span class="required">*</span>
                        </label>
                        <input type="password" class="tf-input" 
                               id="password_confirmation" name="password_confirmation" 
                               placeholder="أعد إدخال كلمة المرور" required>
                    </div>
                </div>

                <div class="tf-checkbox-wrapper">
                    <label class="tf-checkbox">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <div class="tf-checkbox-dot"></div>
                    </label>
                    <span style="color: var(--tf-text-b); font-weight: 600;">حساب نشط</span>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-8 pt-6" style="border-top: 1px solid var(--tf-border);">
                <button type="submit" class="tf-btn tf-btn-primary">
                    <i class="fas fa-save"></i>حفظ
                </button>
                <a href="{{ route('users.index') }}" class="tf-btn tf-btn-secondary">
                    <i class="fas fa-times"></i>إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection