@extends('layouts.app')

@section('title', 'تعديل بيانات العميل')
@section('page-title', 'تعديل بيانات العميل')

@push('styles')
<style>
    .edit-grid {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 1023px) {
        .edit-grid { grid-template-columns: 1fr; }
    }

    /* Card */
    .edit-card {
        background: white;
        border-radius: 18px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .edit-card-header {
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .edit-card-header-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        background: #eef2ff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6366f1;
        font-size: 14px;
        flex-shrink: 0;
    }
    .edit-card-header-title { font-size: 15px; font-weight: 700; color: #1e293b; }
    .edit-card-header-sub { font-size: 12px; color: #94a3b8; margin-top: 2px; }
    .edit-card-body { padding: 24px; }
    .edit-card-footer {
        padding: 16px 24px;
        border-top: 1px solid #f1f5f9;
        background: #fafafa;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Cover + Profile */
    .profile-cover {
        height: 76px;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    }
    .profile-body {
        padding: 0 20px 20px;
        text-align: center;
    }
    .profile-avatar {
        width: 68px; height: 68px;
        border-radius: 18px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        font-weight: 700;
        color: white;
        margin: -34px auto 12px;
        border: 4px solid white;
        box-shadow: 0 6px 20px rgba(99,102,241,0.35);
    }
    .profile-name { font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 3px; }
    .profile-sub { font-size: 12px; color: #94a3b8; }

    /* Tips */
    .tips-box {
        margin: 16px;
        background: #eef2ff;
        border-radius: 14px;
        border: 1px solid #c7d2fe;
        padding: 16px;
    }
    .tips-title {
        font-size: 13px;
        font-weight: 700;
        color: #4338ca;
        margin: 0 0 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .tips-list { margin: 0; padding: 0; list-style: none; }
    .tips-list li {
        font-size: 12px;
        color: #4f46e5;
        padding: 3px 0;
        display: flex;
        align-items: flex-start;
        gap: 6px;
    }
    .tips-list li::before { content: '•'; color: #a5b4fc; flex-shrink: 0; }

    /* Field */
    .field-group { margin-bottom: 18px; }
    .field-group:last-child { margin-bottom: 0; }

    .field-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 7px;
    }
    .req { color: #ef4444; margin-right: 2px; }

    .field-wrap { position: relative; }
    .field-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 30px; height: 30px;
        background: #f1f5f9;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 12px;
        pointer-events: none;
        z-index: 2;
    }
    .field-wrap.is-textarea .field-icon {
        top: 13px;
        transform: none;
    }

    .form-field {
        width: 100%;
        padding: 10px 52px 10px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 14px;
        font-family: 'Cairo', sans-serif;
        color: #1e293b;
        background: #f8fafc;
        transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        outline: none;
        box-sizing: border-box;
    }
    .form-field:focus {
        border-color: #6366f1;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.13);
    }
    .form-field.is-invalid {
        border-color: #f87171;
        background: #fff5f5;
    }
    .form-field.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(239,68,68,0.12);
    }
    textarea.form-field {
        resize: none;
        padding-top: 10px;
        padding-bottom: 10px;
        min-height: 86px;
        line-height: 1.6;
    }

    .field-error-msg {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 5px;
        font-size: 12px;
        color: #ef4444;
    }

    /* Error Alert */
    .errors-alert {
        background: #fef2f2;
        border: 1.5px solid #fecaca;
        border-radius: 14px;
        padding: 14px 16px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 20px;
    }
    .errors-alert-icon {
        width: 32px; height: 32px;
        background: #fee2e2;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ef4444;
        font-size: 13px;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .errors-alert-title { font-size: 13px; font-weight: 700; color: #b91c1c; margin: 0 0 5px; }
    .errors-alert-list { margin: 0; padding: 0; list-style: none; }
    .errors-alert-list li { font-size: 12px; color: #dc2626; padding: 2px 0; }

    /* Buttons */
    .btn-save {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 22px;
        border-radius: 12px;
        background: #6366f1;
        color: white;
        font-size: 14px;
        font-weight: 600;
        font-family: 'Cairo', sans-serif;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s, box-shadow 0.2s;
    }
    .btn-save:hover { background: #4f46e5; box-shadow: 0 4px 14px rgba(99,102,241,0.35); color: white; }

    .btn-cancel {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 18px;
        border-radius: 12px;
        background: white;
        color: #475569;
        font-size: 14px;
        font-weight: 600;
        font-family: 'Cairo', sans-serif;
        border: 1.5px solid #e2e8f0;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s, color 0.2s;
    }
    .btn-cancel:hover { background: #f1f5f9; color: #1e293b; }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 14px;
        border-radius: 12px;
        color: #94a3b8;
        font-size: 13px;
        font-family: 'Cairo', sans-serif;
        border: none;
        background: none;
        cursor: pointer;
        text-decoration: none;
        transition: color 0.15s;
        margin-right: auto;
    }
    .btn-back:hover { color: #475569; }

    /* Breadcrumb */
    .page-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #94a3b8;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }
    .page-breadcrumb a {
        color: #94a3b8;
        text-decoration: none;
        transition: color 0.15s;
    }
    .page-breadcrumb a:hover { color: #6366f1; }
    .page-breadcrumb .current { color: #334155; font-weight: 600; }
    .page-breadcrumb i { font-size: 10px; }
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<nav class="page-breadcrumb">
    <a href="{{ route('customers.index') }}">العملاء</a>
    <i class="fas fa-chevron-left"></i>
    <a href="{{ route('customers.show', $customer->id) }}">{{ $customer->name }}</a>
    <i class="fas fa-chevron-left"></i>
    <span class="current">تعديل</span>
</nav>

<div class="edit-grid">

    {{-- ===== LEFT: Profile + Tips ===== --}}
    <div>
        <div class="edit-card">
            <div class="profile-cover"></div>
            <div class="profile-body">
                <div class="profile-avatar">{{ mb_substr($customer->name, 0, 1) }}</div>
                <p class="profile-name">{{ $customer->name }}</p>
                <p class="profile-sub">مسجل {{ $customer->created_at->diffForHumans() }}</p>
            </div>
            <div class="tips-box">
                <div class="tips-title">
                    <i class="fas fa-info-circle"></i> تعليمات
                </div>
                <ul class="tips-list">
                    <li>الحقول المميزة بـ <strong style="color:#ef4444;">*</strong> إلزامية</li>
                    <li>تأكد من صحة رقم الهاتف</li>
                    <li>البريد لإرسال الفواتير للعميل</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ===== RIGHT: Form ===== --}}
    <div class="edit-card">

        <div class="edit-card-header">
            <div class="edit-card-header-icon">
                <i class="fas fa-user-edit"></i>
            </div>
            <div>
                <div class="edit-card-header-title">تعديل بيانات العميل</div>
                <div class="edit-card-header-sub">قم بتحديث المعلومات ثم اضغط حفظ</div>
            </div>
        </div>

        <form action="{{ route('customers.update', $customer->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="edit-card-body">

                {{-- Errors --}}
                @if ($errors->any())
                <div class="errors-alert">
                    <div class="errors-alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div>
                        <p class="errors-alert-title">يوجد أخطاء في البيانات</p>
                        <ul class="errors-alert-list">
                            @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                {{-- Name --}}
                <div class="field-group">
                    <label class="field-label">
                        اسم العميل <span class="req">*</span>
                    </label>
                    <div class="field-wrap">
                        <div class="field-icon"><i class="fas fa-user"></i></div>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $customer->name) }}"
                               placeholder="أدخل اسم العميل الكامل"
                               required
                               class="form-field {{ $errors->has('name') ? 'is-invalid' : '' }}">
                    </div>
                    @error('name')
                    <div class="field-error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Phone --}}
                <div class="field-group">
                    <label class="field-label">رقم الهاتف</label>
                    <div class="field-wrap">
                        <div class="field-icon"><i class="fas fa-phone-alt"></i></div>
                        <input type="text"
                               name="phone"
                               value="{{ old('phone', $customer->phone) }}"
                               placeholder="05xxxxxxxx"
                               class="form-field {{ $errors->has('phone') ? 'is-invalid' : '' }}">
                    </div>
                    @error('phone')
                    <div class="field-error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="field-group">
                    <label class="field-label">البريد الإلكتروني</label>
                    <div class="field-wrap">
                        <div class="field-icon"><i class="fas fa-envelope"></i></div>
                        <input type="email"
                               name="email"
                               value="{{ old('email', $customer->email) }}"
                               placeholder="example@email.com"
                               class="form-field {{ $errors->has('email') ? 'is-invalid' : '' }}">
                    </div>
                    @error('email')
                    <div class="field-error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Address --}}
                <div class="field-group">
                    <label class="field-label">العنوان</label>
                    <div class="field-wrap is-textarea">
                        <div class="field-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <textarea name="address"
                                  rows="3"
                                  placeholder="المدينة، الحي، الشارع..."
                                  class="form-field {{ $errors->has('address') ? 'is-invalid' : '' }}">{{ old('address', $customer->address) }}</textarea>
                    </div>
                    @error('address')
                    <div class="field-error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Notes --}}
                <div class="field-group">
                    <label class="field-label">ملاحظات</label>
                    <div class="field-wrap is-textarea">
                        <div class="field-icon"><i class="fas fa-sticky-note"></i></div>
                        <textarea name="notes"
                                  rows="2"
                                  placeholder="أي ملاحظات إضافية..."
                                  class="form-field">{{ old('notes', $customer->notes ?? '') }}</textarea>
                    </div>
                </div>

            </div>

            {{-- Buttons --}}
            <div class="edit-card-footer">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i>
                    حفظ التعديلات
                </button>

                <a href="{{ route('customers.show', $customer->id) }}" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    إلغاء
                </a>

                <a href="{{ route('customers.index') }}" class="btn-back">
                    <i class="fas fa-arrow-right"></i>
                    قائمة العملاء
                </a>
            </div>

        </form>
    </div>

</div>

@endsection