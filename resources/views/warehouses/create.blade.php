@extends('layouts.app')

@section('title', 'إضافة مخزن جديد')
@section('page-title', 'إضافة مخزن جديد')

@push('styles')
<style>
    /* ── Breadcrumb ── */
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 24px;
        font-size: 13px;
        color: var(--text-muted);
    }
    .breadcrumb a {
        color: var(--text-muted);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        background: rgba(99,102,241,0.06);
        border: 1px solid rgba(99,102,241,0.1);
        font-weight: 600;
        transition: all 0.2s;
    }
    .breadcrumb a:hover {
        background: rgba(99,102,241,0.12);
        color: var(--accent);
    }
    .breadcrumb .sep { color: rgba(99,102,241,0.3); font-size: 16px; }
    .breadcrumb .current { color: var(--text-main); font-weight: 700; }

    /* ── Form Layout ── */
    .form-layout {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 1024px) { .form-layout { grid-template-columns: 1fr; } }

    /* ── Section Card ── */
    .form-section {
        background: #fff;
        border: 1px solid rgba(99,102,241,0.1);
        border-radius: 18px;
        overflow: hidden;
        margin-bottom: 20px;
        transition: box-shadow 0.2s;
    }
    .form-section:hover { box-shadow: 0 4px 24px rgba(99,102,241,0.08); }

    .section-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 22px;
        border-bottom: 1px solid rgba(99,102,241,0.07);
        background: rgba(99,102,241,0.02);
    }
    .section-icon {
        width: 38px; height: 38px;
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .section-icon.indigo { background: rgba(99,102,241,0.12); color: #6366f1; }
    .section-icon.green  { background: rgba(16,185,129,0.12);  color: #10b981; }
    .section-icon.purple { background: rgba(168,85,247,0.12);  color: #a855f7; }
    .section-icon.amber  { background: rgba(245,158,11,0.12);  color: #f59e0b; }

    .section-head h3 {
        font-size: 14px;
        font-weight: 800;
        color: var(--text-main);
        margin: 0 0 2px;
    }
    .section-head p {
        font-size: 11.5px;
        color: var(--text-muted);
        margin: 0;
    }

    .section-body { padding: 22px; }

    /* ── Form Grid ── */
    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }
    @media (max-width: 640px) { .form-grid-2 { grid-template-columns: 1fr; } }
    .col-span-2 { grid-column: 1 / -1; }

    /* ── Field ── */
    .field { display: flex; flex-direction: column; gap: 6px; }
    .field label {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .field label .req { color: #ef4444; font-size: 14px; }

    .field input,
    .field select,
    .field textarea {
        width: 100%;
        padding: 11px 14px;
        background: rgba(99,102,241,0.03);
        border: 1.5px solid rgba(99,102,241,0.15);
        border-radius: 11px;
        font-size: 13.5px;
        color: var(--text-main);
        font-family: 'Cairo', sans-serif;
        outline: none;
        transition: all 0.2s;
    }
    .field input::placeholder,
    .field textarea::placeholder { color: #b0b9cc; }
    .field input:focus,
    .field select:focus,
    .field textarea:focus {
        border-color: #6366f1;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
    }
    .field input.error,
    .field select.error { border-color: #ef4444; background: #fff5f5; }
    .field input.error:focus { box-shadow: 0 0 0 4px rgba(239,68,68,0.1); }

    .field .hint {
        font-size: 11px;
        color: var(--text-muted);
        display: flex; align-items: center; gap: 4px;
    }
    .field .err-msg {
        font-size: 11.5px;
        color: #ef4444;
        display: flex; align-items: center; gap: 4px;
        font-weight: 600;
    }

    /* Input with action button */
    .input-action {
        position: relative;
    }
    .input-action input { padding-left: 110px; }
    .input-action .action-btn {
        position: absolute;
        left: 8px; top: 50%; transform: translateY(-50%);
        background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(59,130,246,0.08));
        border: 1px solid rgba(99,102,241,0.2);
        color: #6366f1;
        padding: 5px 10px;
        border-radius: 7px;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        font-family: 'Cairo', sans-serif;
        white-space: nowrap;
        transition: all 0.2s;
    }
    .input-action .action-btn:hover {
        background: rgba(99,102,241,0.2);
        transform: translateY(-50%) scale(1.02);
    }

    /* Toggle switch for is_active */
    .toggle-field {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        background: rgba(99,102,241,0.03);
        border: 1.5px solid rgba(99,102,241,0.15);
        border-radius: 11px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .toggle-field:hover { background: rgba(99,102,241,0.06); }
    .toggle-field .tgl-info { display: flex; flex-direction: column; gap: 2px; }
    .toggle-field .tgl-label { font-size: 13px; font-weight: 700; color: var(--text-main); }
    .toggle-field .tgl-sub   { font-size: 11px; color: var(--text-muted); }

    .toggle-switch {
        position: relative;
        width: 46px; height: 26px;
        flex-shrink: 0;
    }
    .toggle-switch input { display: none; }
    .toggle-track {
        position: absolute; inset: 0;
        background: #e2e8f0;
        border-radius: 13px;
        transition: background 0.25s;
        cursor: pointer;
    }
    .toggle-track::after {
        content: '';
        position: absolute;
        top: 3px; right: 3px;
        width: 20px; height: 20px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        transition: transform 0.25s cubic-bezier(.4,0,.2,1);
    }
    .toggle-switch input:checked ~ .toggle-track { background: #6366f1; }
    .toggle-switch input:checked ~ .toggle-track::after { transform: translateX(-20px); }

    /* Select styled */
    .field select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: left 14px center;
        padding-left: 40px;
    }

    /* ── Sidebar ── */
    .form-sidebar { display: flex; flex-direction: column; gap: 18px; }

    /* Summary card */
    .summary-card {
        background: linear-gradient(135deg, #1e2d4a 0%, #0f1f3d 100%);
        border-radius: 18px;
        padding: 22px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .summary-card::before {
        content: '';
        position: absolute;
        top: -40px; left: -40px;
        width: 130px; height: 130px;
        background: rgba(99,102,241,0.15);
        border-radius: 50%;
    }
    .summary-card::after {
        content: '';
        position: absolute;
        bottom: -20px; right: -20px;
        width: 90px; height: 90px;
        background: rgba(59,130,246,0.1);
        border-radius: 50%;
    }
    .summary-card > * { position: relative; z-index: 1; }

    .summary-icon {
        width: 52px; height: 52px;
        background: linear-gradient(135deg, rgba(99,102,241,0.4), rgba(59,130,246,0.3));
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; color: #c7d2fe;
        margin-bottom: 14px;
    }
    .summary-card h3 { font-size: 15px; font-weight: 800; margin: 0 0 4px; }
    .summary-card p  { font-size: 12px; color: rgba(255,255,255,0.5); margin: 0 0 18px; }
    .summary-preview {
        display: flex; flex-direction: column; gap: 10px;
    }
    .preview-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 9px 12px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 9px;
        font-size: 12px;
    }
    .preview-row .pr-key { color: rgba(255,255,255,0.5); }
    .preview-row .pr-val { font-weight: 700; color: #fff; max-width: 140px; text-align: left; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* Tips card */
    .tips-card {
        background: #fff;
        border: 1px solid rgba(99,102,241,0.1);
        border-radius: 18px;
        padding: 20px;
    }
    .tips-card h4 {
        font-size: 13px; font-weight: 800; color: var(--text-main);
        margin: 0 0 14px;
        display: flex; align-items: center; gap: 8px;
    }
    .tips-card h4 i { color: #f59e0b; }
    .tip-item {
        display: flex; gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid rgba(99,102,241,0.06);
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.5;
    }
    .tip-item:last-child { border-bottom: none; padding-bottom: 0; }
    .tip-item .tip-num {
        width: 20px; height: 20px;
        background: rgba(99,102,241,0.1);
        color: #6366f1;
        border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        font-size: 10px; font-weight: 800;
        flex-shrink: 0; margin-top: 1px;
    }

    /* ── Error Banner ── */
    .error-banner {
        display: flex; align-items: flex-start; gap: 14px;
        padding: 16px 20px;
        background: #fef2f2;
        border: 1px solid #fca5a5;
        border-radius: 14px;
        margin-bottom: 22px;
        animation: fadeUp 0.3s ease;
    }
    .error-banner .err-icon {
        width: 36px; height: 36px;
        background: rgba(239,68,68,0.12);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; color: #ef4444;
        flex-shrink: 0;
    }
    .error-banner h4 { font-size: 13px; font-weight: 800; color: #b91c1c; margin: 0 0 6px; }
    .error-banner ul { margin: 0; padding: 0 16px 0 0; list-style: disc; }
    .error-banner li { font-size: 12.5px; color: #b91c1c; margin-bottom: 3px; }

    /* ── Action Buttons ── */
    .form-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 22px;
        background: rgba(99,102,241,0.03);
        border-top: 1px solid rgba(99,102,241,0.08);
    }
    .btn-save {
        display: flex; align-items: center; gap: 8px;
        padding: 12px 28px;
        background: linear-gradient(135deg, #6366f1, #3b82f6);
        color: #fff;
        border: none; border-radius: 12px;
        font-size: 14px; font-weight: 700;
        cursor: pointer;
        font-family: 'Cairo', sans-serif;
        box-shadow: 0 4px 18px rgba(99,102,241,0.35);
        transition: all 0.25s;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(99,102,241,0.45);
    }
    .btn-save:disabled {
        opacity: 0.75;
        cursor: not-allowed;
        transform: none;
    }
    .btn-cancel {
        display: flex; align-items: center; gap: 8px;
        padding: 12px 22px;
        background: rgba(99,102,241,0.07);
        color: var(--text-muted);
        border: 1px solid rgba(99,102,241,0.12);
        border-radius: 12px;
        font-size: 14px; font-weight: 600;
        text-decoration: none;
        font-family: 'Cairo', sans-serif;
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-cancel:hover {
        background: rgba(99,102,241,0.12);
        color: var(--text-main);
    }
    .btn-save .spinner {
        display: none;
        width: 18px; height: 18px;
        border: 2px solid rgba(255,255,255,0.4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="{{ route('warehouses.index') }}">
        <i class="fas fa-arrow-right"></i>
        المخازن
    </a>
    <span class="sep">›</span>
    <span class="current">مخزن جديد</span>
</div>

<!-- Error Banner -->
@if($errors->any())
<div class="error-banner">
    <div class="err-icon"><i class="fas fa-exclamation-triangle"></i></div>
    <div>
        <h4>يوجد أخطاء في النموذج</h4>
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<!-- Form -->
<form action="{{ route('warehouses.store') }}" method="POST" id="warehouseForm">
@csrf

<div class="form-layout">

    <!-- ── Right Column: Sections ── -->
    <div>

        <!-- المعلومات الأساسية -->
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon indigo"><i class="fas fa-warehouse"></i></div>
                <div>
                    <h3>المعلومات الأساسية</h3>
                    <p>البيانات الجوهرية للمخزن</p>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid-2">

                    <!-- اسم المخزن -->
                    <div class="field">
                        <label for="name">اسم المخزن <span class="req">*</span></label>
                        <input type="text" name="name" id="name"
                               value="{{ old('name') }}"
                               placeholder="مثال: المخزن الرئيسي"
                               class="{{ $errors->has('name') ? 'error' : '' }}"
                               autofocus required>
                        @error('name')
                            <span class="err-msg"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <!-- كود المخزن -->
                    <div class="field">
                        <label for="code">كود المخزن</label>
                        <div class="input-action">
                            <input type="text" name="code" id="code"
                                   value="{{ old('code') }}"
                                   placeholder="مثال: WH-001"
                                   class="{{ $errors->has('code') ? 'error' : '' }}">
                            <button type="button" class="action-btn" onclick="generateCode()">
                                <i class="fas fa-magic"></i> توليد
                            </button>
                        </div>
                        @error('code')
                            <span class="err-msg"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                        @else
                            <span class="hint"><i class="fas fa-info-circle"></i> يُنشأ تلقائياً إذا تُرك فارغاً</span>
                        @enderror
                    </div>

                    <!-- حالة المخزن -->
                    <div class="field">
                        <label for="status">حالة المخزن <span class="req">*</span></label>
                        <select name="status" id="status">
                            <option value="active"       {{ old('status','active') == 'active'      ? 'selected' : '' }}>نشط</option>
                            <option value="inactive"     {{ old('status') == 'inactive'              ? 'selected' : '' }}>متوقف</option>
                            <option value="maintenance"  {{ old('status') == 'maintenance'           ? 'selected' : '' }}>صيانة</option>
                        </select>
                    </div>

                    <!-- تفعيل المخزن (toggle) -->
                    <div class="field">
                        <label>تفعيل المخزن</label>
                        <label class="toggle-field" for="is_active">
                            <div class="tgl-info">
                                <span class="tgl-label">المخزن مفعّل</span>
                                <span class="tgl-sub">يظهر في قوائم التحديد</span>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                       {{ old('is_active', 1) ? 'checked' : '' }}>
                                <div class="toggle-track"></div>
                            </div>
                        </label>
                    </div>

                </div>
            </div>
        </div>

        <!-- معلومات الموقع -->
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon green"><i class="fas fa-map-marker-alt"></i></div>
                <div>
                    <h3>معلومات الموقع</h3>
                    <p>عنوان وتفاصيل الموقع الجغرافي</p>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid-2">

                    <div class="field">
                        <label for="city">المدينة</label>
                        <input type="text" name="city" id="city"
                               value="{{ old('city') }}" placeholder="مثال: القاهرة">
                    </div>

                    <div class="field">
                        <label for="area">المنطقة</label>
                        <input type="text" name="area" id="area"
                               value="{{ old('area') }}" placeholder="مثال: مدينة نصر">
                    </div>

                    <div class="field">
                        <label for="phone">رقم الهاتف</label>
                        <input type="tel" name="phone" id="phone"
                               value="{{ old('phone') }}" placeholder="01012345678"
                               class="{{ $errors->has('phone') ? 'error' : '' }}">
                        @error('phone')
                            <span class="err-msg"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="email">البريد الإلكتروني</label>
                        <input type="email" name="email" id="email"
                               value="{{ old('email') }}" placeholder="warehouse@example.com"
                               class="{{ $errors->has('email') ? 'error' : '' }}">
                        @error('email')
                            <span class="err-msg"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field col-span-2">
                        <label for="address">العنوان التفصيلي</label>
                        <textarea name="address" id="address" rows="3"
                                  placeholder="أدخل العنوان الكامل...">{{ old('address') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        <!-- معلومات المسؤول -->
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon purple"><i class="fas fa-user-tie"></i></div>
                <div>
                    <h3>معلومات المسؤول</h3>
                    <p>بيانات مدير المخزن</p>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid-2">

                    <div class="field">
                        <label for="manager_name">اسم المسؤول</label>
                        <input type="text" name="manager_name" id="manager_name"
                               value="{{ old('manager_name') }}" placeholder="مثال: أحمد محمد">
                    </div>

                    <div class="field">
                        <label for="description">الوصف</label>
                        <input type="text" name="description" id="description"
                               value="{{ old('description') }}" placeholder="وصف مختصر للمخزن">
                    </div>

                </div>
            </div>
        </div>

        <!-- ملاحظات -->
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon amber"><i class="fas fa-sticky-note"></i></div>
                <div>
                    <h3>ملاحظات إضافية</h3>
                    <p>أي معلومات أخرى تريد إضافتها</p>
                </div>
            </div>
            <div class="section-body">
                <div class="field">
                    <textarea name="notes" id="notes" rows="4"
                              placeholder="أضف أي ملاحظات أو تعليمات خاصة بهذا المخزن...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Action Buttons inside last section -->
            <div class="form-actions">
                <button type="submit" class="btn-save" id="submitBtn">
                    <div class="spinner" id="spinner"></div>
                    <i class="fas fa-check" id="saveIcon"></i>
                    <span id="saveText">حفظ المخزن</span>
                </button>
                <a href="{{ route('warehouses.index') }}" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    إلغاء
                </a>
            </div>
        </div>

    </div>

    <!-- ── Left Column: Sidebar ── -->
    <div class="form-sidebar">

        <!-- Live Preview Card -->
        <div class="summary-card">
            <div class="summary-icon"><i class="fas fa-warehouse"></i></div>
            <h3 id="previewName">اسم المخزن</h3>
            <p id="previewCode">كود المخزن</p>
            <div class="summary-preview">
                <div class="preview-row">
                    <span class="pr-key">الحالة</span>
                    <span class="pr-val" id="previewStatus">—</span>
                </div>
                <div class="preview-row">
                    <span class="pr-key">المدينة</span>
                    <span class="pr-val" id="previewCity">—</span>
                </div>
                <div class="preview-row">
                    <span class="pr-key">المسؤول</span>
                    <span class="pr-val" id="previewManager">—</span>
                </div>
                <div class="preview-row">
                    <span class="pr-key">الهاتف</span>
                    <span class="pr-val" id="previewPhone">—</span>
                </div>
            </div>
        </div>

        <!-- Tips Card -->
        <div class="tips-card">
            <h4><i class="fas fa-lightbulb"></i> نصائح مفيدة</h4>
            <div class="tip-item">
                <div class="tip-num">1</div>
                <span>اختر اسماً واضحاً يعكس موقع أو وظيفة المخزن.</span>
            </div>
            <div class="tip-item">
                <div class="tip-num">2</div>
                <span>الكود يُستخدم في التقارير والتحويلات — يفضّل أن يكون مختصراً.</span>
            </div>
            <div class="tip-item">
                <div class="tip-num">3</div>
                <span>تأكد من صحة رقم الهاتف للتواصل السريع مع المسؤول.</span>
            </div>
            <div class="tip-item">
                <div class="tip-num">4</div>
                <span>المخازن غير النشطة لن تظهر في فواتير المبيعات والمشتريات.</span>
            </div>
        </div>

    </div>

</div>
</form>

@endsection

@push('scripts')
<script>
    // ── Generate code ──
    function generateCode() {
        const code = 'WH-' + Math.floor(Math.random() * 9000 + 1000);
        document.getElementById('code').value = code;
        updatePreview();
    }

    // ── Live preview ──
    const statusMap = { active: 'نشط ✅', inactive: 'متوقف ⛔', maintenance: 'صيانة 🔧' };

    function updatePreview() {
        const name    = document.getElementById('name').value;
        const code    = document.getElementById('code').value;
        const status  = document.getElementById('status').value;
        const city    = document.getElementById('city').value;
        const manager = document.getElementById('manager_name').value;
        const phone   = document.getElementById('phone').value;

        document.getElementById('previewName').textContent    = name    || 'اسم المخزن';
        document.getElementById('previewCode').textContent    = code    || 'كود المخزن';
        document.getElementById('previewStatus').textContent  = statusMap[status] || '—';
        document.getElementById('previewCity').textContent    = city    || '—';
        document.getElementById('previewManager').textContent = manager || '—';
        document.getElementById('previewPhone').textContent   = phone   || '—';
    }

    ['name','code','status','city','manager_name','phone'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updatePreview);
        if (el) el.addEventListener('change', updatePreview);
    });

    updatePreview();

    // ── Prevent double submit ──
    let submitting = false;
    document.getElementById('warehouseForm').addEventListener('submit', function(e) {
        if (submitting) { e.preventDefault(); return; }
        submitting = true;
        const btn     = document.getElementById('submitBtn');
        const spinner = document.getElementById('spinner');
        const icon    = document.getElementById('saveIcon');
        const text    = document.getElementById('saveText');
        btn.disabled        = true;
        spinner.style.display = 'block';
        icon.style.display    = 'none';
        text.textContent      = 'جاري الحفظ...';
    });
</script>
@endpush