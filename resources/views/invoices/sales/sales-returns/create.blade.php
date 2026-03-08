@extends('layouts.app')

@section('title', 'إضافة مرتجع مبيعات')
@section('page-title', 'إضافة مرتجع مبيعات')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@300;400;500;700;800&display=swap');

    :root {
        --primary:      #1a1f3a;
        --accent:       #4f6ef7;
        --accent-light: #6b84ff;
        --accent-soft:  rgba(79,110,247,0.10);
        --success:      #059669;
        --success-soft: rgba(5,150,105,0.10);
        --danger:       #e03355;
        --danger-soft:  rgba(224,51,85,0.10);
        --warning:      #d97706;
        --warning-soft: rgba(217,119,6,0.10);
        --surface:      #ffffff;
        --surface-2:    #f8f9fd;
        --border:       rgba(0,0,0,0.07);
        --text-main:    #1a1f3a;
        --text-muted:   #8b92a5;
        --shadow-sm:    0 2px 8px rgba(0,0,0,0.06);
        --shadow-md:    0 8px 24px rgba(0,0,0,0.10);
        --radius:       18px;
        --radius-sm:    10px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Cairo', 'Tajawal', sans-serif;
        background: var(--surface-2);
        color: var(--text-main);
        direction: rtl;
    }

    /* ── PAGE ── */
    .ret-page {
        max-width: 720px;
        margin: 0 auto;
        padding: 24px 16px 90px;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    /* ── HEADER ── */
    .ret-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .ret-header-title h2 {
        font-size: 1.55rem;
        font-weight: 800;
        color: var(--primary);
        letter-spacing: -0.5px;
        line-height: 1.2;
    }

    .ret-header-title p { font-size: 0.82rem; color: var(--text-muted); margin-top: 3px; }

    .btn-back {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 10px 18px;
        background: var(--surface); color: var(--text-muted);
        border: 1.5px solid var(--border); border-radius: 50px;
        font-family: 'Cairo', sans-serif;
        font-size: 0.83rem; font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-back:hover { background: var(--accent-soft); border-color: var(--accent); color: var(--accent); }
    .btn-back svg { width: 15px; height: 15px; }

    /* ── ALERT BOX ── */
    .warning-notice {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: var(--warning-soft);
        border: 1px solid rgba(217,119,6,0.2);
        border-radius: var(--radius-sm);
        padding: 14px 16px;
    }

    .warning-notice svg { width: 20px; height: 20px; color: var(--warning); flex-shrink: 0; margin-top: 1px; }
    .warning-notice p { font-size: 0.83rem; color: #92400e; font-weight: 500; line-height: 1.5; }

    /* ── ERROR ALERT ── */
    .error-alert {
        background: var(--danger-soft);
        border: 1px solid rgba(224,51,85,0.2);
        border-radius: var(--radius-sm);
        padding: 13px 16px;
        color: #b91c3c;
        font-size: 0.85rem;
    }

    .error-alert ul { padding-right: 18px; }
    .error-alert li { margin-bottom: 3px; }

    /* ── CARD ── */
    .ret-card {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
        overflow: hidden;
        animation: fadeUp 0.35s ease both;
    }

    .ret-card:nth-child(3) { animation-delay: 0.05s; }
    .ret-card:nth-child(4) { animation-delay: 0.10s; }

    .ret-card-header {
        padding: 16px 22px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: 10px;
    }

    .card-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .card-icon svg { width: 17px; height: 17px; }
    .ret-card-header h3 { font-size: 0.93rem; font-weight: 700; color: var(--primary); }

    .ret-card-body { padding: 22px; display: flex; flex-direction: column; gap: 18px; }

    /* ── FORM ── */
    .form-group { display: flex; flex-direction: column; gap: 6px; }

    .form-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .form-label .req { color: var(--danger); margin-right: 2px; }

    .form-control {
        width: 100%;
        padding: 11px 14px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        font-family: 'Cairo', sans-serif;
        font-size: 0.88rem;
        background: var(--surface);
        color: var(--text-main);
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        direction: rtl;
        appearance: none;
        -webkit-appearance: none;
    }

    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(79,110,247,0.10);
    }

    .form-control.error { border-color: var(--danger); }

    textarea.form-control { resize: vertical; min-height: 100px; line-height: 1.7; }

    .form-error { font-size: 0.75rem; color: var(--danger); font-weight: 500; }
    .form-hint  { font-size: 0.75rem; color: var(--text-muted); }

    /* ── INVOICE SELECT - with preview ── */
    .invoice-select-wrap { position: relative; }

    .invoice-preview {
        display: none;
        margin-top: 10px;
        background: var(--accent-soft);
        border: 1.5px solid rgba(79,110,247,0.18);
        border-radius: var(--radius-sm);
        padding: 12px 14px;
        gap: 14px;
        align-items: center;
        flex-wrap: wrap;
    }

    .invoice-preview.show { display: flex; }

    .inv-preview-chip {
        display: flex; align-items: center; gap: 6px;
        font-size: 0.78rem; font-weight: 600; color: var(--accent);
    }

    .inv-preview-chip svg { width: 13px; height: 13px; }

    /* ── AMOUNT DISPLAY ── */
    .amount-wrap { position: relative; }

    .amount-wrap .currency-tag {
        position: absolute;
        left: 12px; top: 50%; transform: translateY(-50%);
        font-size: 0.78rem; font-weight: 700;
        color: var(--text-muted);
        background: var(--surface-2);
        padding: 3px 8px; border-radius: 6px;
        pointer-events: none;
    }

    .amount-wrap .form-control { padding-left: 56px; }

    /* ── REASON CHIPS ── */
    .reason-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-bottom: 8px;
    }

    .reason-chip {
        padding: 6px 13px;
        border-radius: 50px;
        border: 1.5px solid var(--border);
        background: var(--surface-2);
        font-family: 'Cairo', sans-serif;
        font-size: 0.78rem; font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.18s;
    }

    .reason-chip:hover, .reason-chip.active {
        background: var(--danger-soft);
        border-color: rgba(224,51,85,0.3);
        color: var(--danger);
    }

    /* ── FILE UPLOAD ── */
    .file-drop {
        border: 2px dashed var(--border);
        border-radius: var(--radius-sm);
        padding: 28px 20px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
        position: relative;
    }

    .file-drop:hover, .file-drop.dragover {
        border-color: var(--accent);
        background: var(--accent-soft);
    }

    .file-drop input[type="file"] {
        position: absolute; inset: 0;
        opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }

    .file-drop-icon {
        width: 44px; height: 44px;
        background: var(--accent-soft);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 10px;
    }

    .file-drop-icon svg { width: 22px; height: 22px; color: var(--accent); }
    .file-drop-title { font-size: 0.88rem; font-weight: 700; color: var(--primary); margin-bottom: 4px; }
    .file-drop-sub   { font-size: 0.75rem; color: var(--text-muted); }

    .file-preview-list {
        display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;
    }

    .file-chip {
        display: flex; align-items: center; gap: 6px;
        background: var(--surface-2);
        border: 1.5px solid var(--border);
        border-radius: 8px;
        padding: 5px 10px;
        font-size: 0.75rem; font-weight: 600;
        color: var(--text-main);
    }

    .file-chip svg { width: 13px; height: 13px; color: var(--accent); }

    /* ── FORM ACTIONS ── */
    .form-actions {
        display: flex; gap: 10px; align-items: center;
        flex-wrap: wrap;
    }

    .btn-save {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 13px 28px;
        background: linear-gradient(135deg, var(--danger), #f43f5e);
        color: #fff; border: none; border-radius: 50px;
        font-family: 'Cairo', sans-serif;
        font-size: 0.9rem; font-weight: 800;
        cursor: pointer;
        box-shadow: 0 5px 18px rgba(224,51,85,0.32);
        transition: transform 0.2s, box-shadow 0.2s;
        white-space: nowrap;
    }

    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 9px 26px rgba(224,51,85,0.42); }
    .btn-save svg { width: 17px; height: 17px; }

    /* ── MOBILE FAB ── */
    .mobile-fab {
        display: none;
        position: fixed;
        bottom: 20px; left: 50%;
        transform: translateX(-50%);
        z-index: 100;
        background: linear-gradient(135deg, var(--danger), #f43f5e);
        color: #fff;
        padding: 14px 32px;
        border-radius: 50px;
        font-family: 'Cairo', sans-serif;
        font-size: 0.9rem; font-weight: 800;
        border: none; cursor: pointer;
        box-shadow: 0 8px 28px rgba(224,51,85,0.40);
        align-items: center; gap: 9px;
        white-space: nowrap;
        transition: transform 0.2s;
    }

    .mobile-fab svg { width: 18px; height: 18px; }

    /* ── ANIMATION ── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 640px) {
        .ret-page { padding: 14px 12px 90px; }
        .ret-header-title h2 { font-size: 1.3rem; }
        .ret-card-body { padding: 16px; gap: 14px; }
        .btn-save.desktop-save { display: none; }
        .mobile-fab { display: inline-flex; }
        .reason-chips { gap: 6px; }
        .reason-chip { font-size: 0.74rem; padding: 5px 11px; }
    }
</style>
@endpush

@section('content')
<div class="ret-page">

    {{-- ── HEADER ── --}}
    <div class="ret-header">
        <div class="ret-header-title">
            <h2>إضافة مرتجع مبيعات</h2>
            <p>إنشاء طلب مرتجع مرتبط بفاتورة بيع</p>
        </div>
        <a href="{{ route('invoices.sales-returns.index') }}" class="btn-back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            رجوع
        </a>
    </div>

    {{-- ── WARNING NOTICE ── --}}
    <div class="warning-notice">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p>سيتم إعادة الكميات المرتجعة إلى المخزن تلقائياً بعد حفظ المرتجع. تأكد من صحة البيانات قبل الحفظ.</p>
    </div>

    {{-- ── ERRORS ── --}}
    @if($errors->any())
    <div class="error-alert">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('invoices.sales-returns.store') }}"
          enctype="multipart/form-data" id="returnForm">
        @csrf

        {{-- ── CARD 1: INVOICE & AMOUNT ── --}}
        <div class="ret-card">
            <div class="ret-card-header">
                <div class="card-icon" style="background:var(--accent-soft)">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--accent)">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3>بيانات المرتجع</h3>
            </div>
            <div class="ret-card-body">

                {{-- Invoice Select --}}
                <div class="form-group">
                    <label class="form-label">فاتورة البيع <span class="req">*</span></label>
                    <div class="invoice-select-wrap">
                        <select name="sales_invoice_id"
                                class="form-control @error('sales_invoice_id') error @enderror"
                                id="invoiceSelect"
                                required>
                            <option value="">اختر فاتورة البيع</option>
                            @foreach($invoices as $invoice)
                                <option value="{{ $invoice->id }}"
                                        data-num="{{ $invoice->invoice_number }}"
                                        data-customer="{{ $invoice->customer->name }}"
                                        data-date="{{ $invoice->invoice_date->format('Y/m/d') }}"
                                        data-total="{{ number_format($invoice->total, 2) }}"
                                        {{ old('sales_invoice_id') == $invoice->id ? 'selected' : '' }}>
                                    {{ $invoice->invoice_number }} — {{ $invoice->customer->name }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Invoice Preview --}}
                        <div class="invoice-preview" id="invoicePreview">
                            <div class="inv-preview-chip">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                </svg>
                                <span id="prevNum">—</span>
                            </div>
                            <div class="inv-preview-chip">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span id="prevCustomer">—</span>
                            </div>
                            <div class="inv-preview-chip">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span id="prevDate">—</span>
                            </div>
                            <div class="inv-preview-chip" style="margin-right:auto">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                </svg>
                                إجمالي: <strong id="prevTotal" style="margin-right:3px">—</strong> جنيه
                            </div>
                        </div>
                    </div>
                    @error('sales_invoice_id')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Amount --}}
                <div class="form-group">
                    <label class="form-label">قيمة المرتجع <span class="req">*</span></label>
                    <div class="amount-wrap">
                        <input type="number"
                               name="total_amount"
                               step="0.01" min="0.01"
                               value="{{ old('total_amount') }}"
                               placeholder="0.00"
                               class="form-control @error('total_amount') error @enderror"
                               required>
                        <span class="currency-tag">جنيه</span>
                    </div>
                    @error('total_amount')<span class="form-error">{{ $message }}</span>@enderror
                </div>

            </div>
        </div>

        {{-- ── CARD 2: REASON & IMAGES ── --}}
        <div class="ret-card">
            <div class="ret-card-header">
                <div class="card-icon" style="background:var(--danger-soft)">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--danger)">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3>سبب المرتجع والمرفقات</h3>
            </div>
            <div class="ret-card-body">

                {{-- Quick reason chips --}}
                <div class="form-group">
                    <label class="form-label">سبب المرتجع <span class="req">*</span></label>
                    <div class="reason-chips" id="reasonChips">
                        <button type="button" class="reason-chip" data-reason="عيب في المنتج">عيب في المنتج</button>
                        <button type="button" class="reason-chip" data-reason="غير مطابق للمواصفات">غير مطابق للمواصفات</button>
                        <button type="button" class="reason-chip" data-reason="تلف أثناء الشحن">تلف أثناء الشحن</button>
                        <button type="button" class="reason-chip" data-reason="خطأ في الطلب">خطأ في الطلب</button>
                        <button type="button" class="reason-chip" data-reason="رغبة العميل">رغبة العميل</button>
                    </div>
                    <textarea name="reason"
                              id="reasonTextarea"
                              rows="3"
                              class="form-control @error('reason') error @enderror"
                              placeholder="اكتب سبب المرتجع أو اختر من الأعلى..."
                              required>{{ old('reason') }}</textarea>
                    @error('reason')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- File Upload --}}
                <div class="form-group">
                    <label class="form-label">صور داعمة (اختياري)</label>
                    <div class="file-drop" id="fileDrop">
                        <input type="file" name="images[]" multiple accept="image/*" id="fileInput">
                        <div class="file-drop-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="file-drop-title">اسحب الصور هنا أو اضغط للاختيار</div>
                        <div class="file-drop-sub">PNG, JPG, WEBP — يمكن رفع أكثر من صورة</div>
                    </div>
                    <div class="file-preview-list" id="filePreviewList"></div>
                </div>

                {{-- Actions --}}
                <div class="form-actions">
                    <button type="submit" class="btn-save desktop-save">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        حفظ المرتجع
                    </button>
                    <a href="{{ route('invoices.sales-returns.index') }}" class="btn-back">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        إلغاء
                    </a>
                </div>

            </div>
        </div>

    </form>
</div>

{{-- ── MOBILE FAB ── --}}
<button type="submit" form="returnForm" class="mobile-fab">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
    </svg>
    حفظ المرتجع
</button>

@push('scripts')
<script>
    /* ── Invoice preview on select ── */
    const sel = document.getElementById('invoiceSelect');
    const preview = document.getElementById('invoicePreview');

    function updatePreview() {
        const opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) { preview.classList.remove('show'); return; }
        document.getElementById('prevNum').textContent      = opt.dataset.num      || '—';
        document.getElementById('prevCustomer').textContent = opt.dataset.customer || '—';
        document.getElementById('prevDate').textContent     = opt.dataset.date     || '—';
        document.getElementById('prevTotal').textContent    = opt.dataset.total    || '—';
        preview.classList.add('show');
    }

    sel.addEventListener('change', updatePreview);
    updatePreview(); // init on page load (old value)

    /* ── Reason chips ── */
    document.querySelectorAll('.reason-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('.reason-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            document.getElementById('reasonTextarea').value = chip.dataset.reason;
        });
    });

    /* ── File drop ── */
    const fileInput = document.getElementById('fileInput');
    const fileList  = document.getElementById('filePreviewList');
    const dropZone  = document.getElementById('fileDrop');

    fileInput.addEventListener('change', renderFiles);

    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        fileInput.files = e.dataTransfer.files;
        renderFiles();
    });

    function renderFiles() {
        fileList.innerHTML = '';
        Array.from(fileInput.files).forEach(f => {
            const chip = document.createElement('div');
            chip.className = 'file-chip';
            chip.innerHTML = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>${f.name}`;
            fileList.appendChild(chip);
        });
    }
</script>
@endpush
@endsection