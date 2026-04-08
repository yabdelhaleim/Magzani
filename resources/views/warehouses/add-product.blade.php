@extends('layouts.app')

@section('title', 'إضافة منتج للمخزن')
@section('page-title', 'إضافة منتج للمخزن')

@push('styles')
<style>
    /* ── Breadcrumb ── */
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 26px;
        font-size: 13px;
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
    .breadcrumb a:hover { background: rgba(99,102,241,0.12); color: var(--accent); }
    .breadcrumb .sep     { color: rgba(99,102,241,0.3); font-size: 16px; }
    .breadcrumb .current { color: var(--text-main); font-weight: 700; }

    /* ── Page Layout ── */
    .page-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 24px;
        align-items: start;
        max-width: 1000px;
    }
    @media (max-width: 900px) { .page-layout { grid-template-columns: 1fr; } }

    /* ── Warehouse Banner ── */
    .warehouse-banner {
        background: linear-gradient(135deg, #1e2d4a, #0f1f3d);
        border-radius: 16px;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }
    .warehouse-banner::before {
        content: '';
        position: absolute;
        top: -30px; right: -30px;
        width: 120px; height: 120px;
        background: rgba(99,102,241,0.12);
        border-radius: 50%;
    }
    .warehouse-banner > * { position: relative; z-index: 1; }
    .wh-banner-icon {
        width: 48px; height: 48px;
        background: linear-gradient(135deg, rgba(99,102,241,0.4), rgba(59,130,246,0.3));
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; color: #c7d2fe;
        flex-shrink: 0;
    }
    .wh-banner-info { flex: 1; }
    .wh-banner-info h3 { color: #fff; font-size: 15px; font-weight: 800; margin: 0 0 3px; }
    .wh-banner-info p  { color: rgba(255,255,255,0.5); font-size: 12px; margin: 0; }
    .wh-banner-badge {
        background: rgba(16,185,129,0.2);
        border: 1px solid rgba(16,185,129,0.3);
        color: #6ee7b7;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    /* ── Form Card ── */
    .form-card {
        background: #fff;
        border: 1px solid rgba(99,102,241,0.1);
        border-radius: 18px;
        overflow: hidden;
    }

    .form-card-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 22px;
        border-bottom: 1px solid rgba(99,102,241,0.07);
        background: rgba(99,102,241,0.02);
    }
    .head-icon {
        width: 38px; height: 38px;
        background: rgba(99,102,241,0.12);
        color: #6366f1;
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
    }
    .form-card-head h3 { font-size: 14px; font-weight: 800; color: var(--text-main); margin: 0 0 2px; }
    .form-card-head p  { font-size: 11.5px; color: var(--text-muted); margin: 0; }

    .form-card-body { padding: 24px; }

    /* ── Product Select ── */
    .product-select-wrap {
        position: relative;
        margin-bottom: 24px;
    }
    .product-select-wrap label {
        display: block;
        font-size: 12.5px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 8px;
    }
    .product-select-wrap label .req { color: #ef4444; }

    .select-box {
        width: 100%;
        padding: 13px 16px 13px 44px;
        background: rgba(99,102,241,0.03);
        border: 1.5px solid rgba(99,102,241,0.15);
        border-radius: 12px;
        font-size: 13.5px;
        color: var(--text-main);
        font-family: 'Cairo', sans-serif;
        outline: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: left 14px center;
        transition: all 0.2s;
        cursor: pointer;
    }
    .select-box:focus {
        border-color: #6366f1;
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
    }
    .select-box.error { border-color: #ef4444; }

    /* product preview strip */
    .product-preview {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        background: rgba(99,102,241,0.05);
        border: 1px solid rgba(99,102,241,0.15);
        border-radius: 11px;
        margin-top: 10px;
        animation: fadeUp 0.25s ease;
    }
    .product-preview.visible { display: flex; }
    .prod-avatar {
        width: 40px; height: 40px;
        background: linear-gradient(135deg, #6366f1, #3b82f6);
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; color: #fff;
        flex-shrink: 0;
    }
    .prod-name  { font-size: 13px; font-weight: 700; color: var(--text-main); margin: 0 0 2px; }
    .prod-sku   { font-size: 11.5px; color: var(--text-muted); margin: 0; }

    /* ── Number Fields ── */
    .num-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }
    @media (max-width: 580px) { .num-grid { grid-template-columns: 1fr; } }

    .num-field {
        background: rgba(99,102,241,0.02);
        border: 1.5px solid rgba(99,102,241,0.12);
        border-radius: 14px;
        padding: 16px;
        transition: all 0.2s;
        cursor: text;
        position: relative;
    }
    .num-field:focus-within {
        border-color: #6366f1;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
    }
    .num-field .nf-icon {
        width: 34px; height: 34px;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        margin-bottom: 12px;
    }
    .num-field.qty   .nf-icon { background: rgba(99,102,241,0.1);  color: #6366f1; }
    .num-field.minst .nf-icon { background: rgba(245,158,11,0.1);  color: #f59e0b; }
    .num-field.cost  .nf-icon { background: rgba(16,185,129,0.1);  color: #10b981; }

    .num-field label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .num-field label .req { color: #ef4444; }

    .num-field input {
        width: 100%;
        border: none;
        outline: none;
        background: none;
        font-size: 22px;
        font-weight: 800;
        color: var(--text-main);
        font-family: 'Cairo', sans-serif;
        padding: 0;
        line-height: 1;
    }
    .num-field input::placeholder { color: #d1d5db; font-weight: 500; font-size: 20px; }

    .num-field .nf-unit {
        position: absolute;
        bottom: 14px; left: 16px;
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
        background: rgba(99,102,241,0.08);
        border-radius: 5px;
        padding: 2px 7px;
    }
    .err-msg {
        font-size: 11.5px; color: #ef4444;
        display: flex; align-items: center; gap: 4px;
        font-weight: 600; margin-top: 6px;
    }

    /* ── Info Box ── */
    .info-box {
        display: flex;
        gap: 14px;
        padding: 16px 18px;
        background: rgba(59,130,246,0.05);
        border: 1px solid rgba(59,130,246,0.15);
        border-radius: 13px;
        margin-bottom: 24px;
    }
    .info-box-icon {
        width: 36px; height: 36px;
        background: rgba(59,130,246,0.12);
        color: #3b82f6;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }
    .info-box h5 { font-size: 12.5px; font-weight: 800; color: #1d4ed8; margin: 0 0 8px; }
    .info-row {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 12px;
        color: #2563eb;
        margin-bottom: 5px;
    }
    .info-row:last-child { margin-bottom: 0; }
    .info-row .dot {
        width: 6px; height: 6px;
        background: #3b82f6;
        border-radius: 50%;
        margin-top: 5px;
        flex-shrink: 0;
    }

    /* ── Action Buttons ── */
    .form-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 22px;
        border-top: 1px solid rgba(99,102,241,0.07);
        background: rgba(99,102,241,0.02);
    }
    .btn-save {
        display: flex; align-items: center; gap: 8px;
        padding: 12px 26px;
        background: linear-gradient(135deg, #6366f1, #3b82f6);
        color: #fff;
        border: none; border-radius: 12px;
        font-size: 14px; font-weight: 700;
        cursor: pointer; font-family: 'Cairo', sans-serif;
        box-shadow: 0 4px 18px rgba(99,102,241,0.35);
        transition: all 0.25s;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(99,102,241,0.45); }
    .btn-save:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
    .btn-cancel {
        display: flex; align-items: center; gap: 8px;
        padding: 12px 20px;
        background: rgba(99,102,241,0.07);
        color: var(--text-muted);
        border: 1px solid rgba(99,102,241,0.12);
        border-radius: 12px;
        font-size: 14px; font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-cancel:hover { background: rgba(99,102,241,0.12); color: var(--text-main); }

    /* Spinner */
    .spinner {
        display: none;
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,0.4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Sidebar ── */
    .sidebar-stack { display: flex; flex-direction: column; gap: 16px; }

    /* Summary card */
    .side-card {
        background: #fff;
        border: 1px solid rgba(99,102,241,0.1);
        border-radius: 16px;
        overflow: hidden;
    }
    .side-card-head {
        padding: 14px 18px;
        border-bottom: 1px solid rgba(99,102,241,0.07);
        font-size: 13px;
        font-weight: 800;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .side-card-head i { color: #6366f1; }
    .side-card-body { padding: 16px 18px; }

    /* Summary rows */
    .sum-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid rgba(99,102,241,0.06);
        font-size: 13px;
    }
    .sum-row:last-child { border-bottom: none; padding-bottom: 0; }
    .sum-row .sr-key { color: var(--text-muted); font-size: 12px; }
    .sum-row .sr-val { font-weight: 800; color: var(--text-main); }
    .sum-row .sr-val.highlight { color: #6366f1; font-size: 15px; }
    .sum-row .sr-val.amber     { color: #f59e0b; }
    .sum-row .sr-val.green     { color: #10b981; }

    /* Steps card */
    .step-item {
        display: flex;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid rgba(99,102,241,0.06);
    }
    .step-item:last-child { border-bottom: none; padding-bottom: 0; }
    .step-num {
        width: 24px; height: 24px;
        background: linear-gradient(135deg, #6366f1, #3b82f6);
        color: #fff;
        border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 800;
        flex-shrink: 0; margin-top: 1px;
    }
    .step-text { font-size: 12px; color: var(--text-muted); line-height: 1.5; }
    .step-text strong { color: var(--text-main); display: block; margin-bottom: 2px; font-size: 12.5px; }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="{{ route('warehouses.index') }}">
        <i class="fas fa-warehouse"></i> المخازن
    </a>
    <span class="sep">›</span>
    <a href="{{ route('warehouses.show', $warehouse->id) }}">
        <i class="fas fa-eye"></i> {{ $warehouse->name }}
    </a>
    <span class="sep">›</span>
    <span class="current">إضافة منتج</span>
</div>

<!-- Warehouse Banner -->
<div class="warehouse-banner">
    <div class="wh-banner-icon"><i class="fas fa-warehouse"></i></div>
    <div class="wh-banner-info">
        <h3>{{ $warehouse->name }}</h3>
        <p>{{ $warehouse->code }} @if($warehouse->address) · {{ Str::limit($warehouse->address, 40) }} @endif</p>
    </div>
    <span class="wh-banner-badge">
        <i class="fas fa-circle" style="font-size:7px;margin-left:4px;"></i>
        {{ $warehouse->is_active ? 'نشط' : 'غير نشط' }}
    </span>
</div>

<!-- Page Layout -->
<div class="page-layout">

    <!-- ── Form Column ── -->
    <form action="{{ route('warehouses.add-product', $warehouse->id) }}" method="POST" id="addProductForm">
    @csrf

    <div class="form-card">
        <div class="form-card-head">
            <div class="head-icon"><i class="fas fa-box-open"></i></div>
            <div>
                <h3>بيانات المنتج</h3>
                <p>اختر المنتج وأدخل كمياته وتكلفته</p>
            </div>
        </div>
        <div class="form-card-body">

            <!-- Product Select -->
            <div class="product-select-wrap">
                <label for="product_id">المنتج <span class="req">*</span></label>
                <select name="product_id" id="product_id"
                        class="select-box {{ $errors->has('product_id') ? 'error' : '' }}"
                        required onchange="onProductChange(this)">
                    <option value="">— اختر المنتج —</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}"
                            data-sku="{{ $product->code ?? $product->sku }}"
                            {{ old('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->name }}
                    </option>
                    @endforeach
                </select>
                @error('product_id')
                    <span class="err-msg"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                @enderror

                <!-- Product Preview -->
                <div class="product-preview" id="productPreview">
                    <div class="prod-avatar"><i class="fas fa-box"></i></div>
                    <div>
                        <p class="prod-name" id="prevProdName">—</p>
                        <p class="prod-sku"  id="prevProdSku">—</p>
                    </div>
                </div>
            </div>

            <!-- Number Fields -->
            <div class="num-grid">

                <!-- الكمية -->
                <div class="num-field qty">
                    <div class="nf-icon"><i class="fas fa-cubes"></i></div>
                    <label for="quantity">الكمية <span class="req">*</span></label>
                    <input type="number" name="quantity" id="quantity"
                           value="{{ old('quantity', 0) }}"
                           min="0" step="0.01"
                           placeholder="0"
                           oninput="updateSummary()"
                           class="{{ $errors->has('quantity') ? 'error' : '' }}"
                           required>
                    <span class="nf-unit">وحدة</span>
                    @error('quantity')
                        <span class="err-msg"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                </div>

                <!-- الحد الأدنى -->
                <div class="num-field minst">
                    <div class="nf-icon"><i class="fas fa-bell"></i></div>
                    <label for="min_stock">حد التنبيه</label>
                    <input type="number" name="min_stock" id="min_stock"
                           value="{{ old('min_stock', 10) }}"
                           min="0" step="0.01"
                           placeholder="10"
                           oninput="updateSummary()"
                           class="{{ $errors->has('min_stock') ? 'error' : '' }}">
                    <span class="nf-unit">وحدة</span>
                    @error('min_stock')
                        <span class="err-msg"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                </div>

                <!-- التكلفة -->
                <div class="num-field cost">
                    <div class="nf-icon"><i class="fas fa-coins"></i></div>
                    <label for="average_cost">متوسط التكلفة</label>
                    <input type="number" name="average_cost" id="average_cost"
                           value="{{ old('average_cost', 0) }}"
                           min="0" step="0.01"
                           placeholder="0"
                           oninput="updateSummary()"
                           class="{{ $errors->has('average_cost') ? 'error' : '' }}">
                    <span class="nf-unit">ج.م</span>
                    @error('average_cost')
                        <span class="err-msg"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                    @enderror
                </div>

            </div>

            <!-- Info Box -->
            <div class="info-box">
                <div class="info-box-icon"><i class="fas fa-circle-info"></i></div>
                <div>
                    <h5>ملاحظات هامة</h5>
                    <div class="info-row"><span class="dot"></span><span>الكمية: عدد الوحدات المتوفرة حالياً من المنتج في هذا المخزن.</span></div>
                    <div class="info-row"><span class="dot"></span><span>حد التنبيه: يُرسل تنبيه عند نزول المخزون لهذا الرقم أو أقل.</span></div>
                    <div class="info-row"><span class="dot"></span><span>متوسط التكلفة: سعر شراء الوحدة الواحدة — يُستخدم في حساب قيمة المخزون.</span></div>
                </div>
            </div>

        </div>

        <!-- Actions -->
        <div class="form-actions">
            <button type="submit" class="btn-save" id="submitBtn">
                <div class="spinner" id="spinner"></div>
                <i class="fas fa-plus" id="saveIcon"></i>
                <span id="saveText">إضافة المنتج</span>
            </button>
            <a href="{{ route('warehouses.show', $warehouse->id) }}" class="btn-cancel">
                <i class="fas fa-arrow-right"></i>
                رجوع
            </a>
        </div>
    </div>

    </form>

    <!-- ── Sidebar ── -->
    <div class="sidebar-stack">

        <!-- Live Summary -->
        <div class="side-card">
            <div class="side-card-head">
                <i class="fas fa-chart-pie"></i>
                ملخص مباشر
            </div>
            <div class="side-card-body">
                <div class="sum-row">
                    <span class="sr-key">الكمية</span>
                    <span class="sr-val highlight" id="sumQty">0</span>
                </div>
                <div class="sum-row">
                    <span class="sr-key">حد التنبيه</span>
                    <span class="sr-val amber" id="sumMin">10</span>
                </div>
                <div class="sum-row">
                    <span class="sr-key">التكلفة / وحدة</span>
                    <span class="sr-val green" id="sumCost">0.00 ج.م</span>
                </div>
                <div class="sum-row">
                    <span class="sr-key">إجمالي القيمة</span>
                    <span class="sr-val" id="sumTotal" style="color:#6366f1;font-size:16px;">0.00 ج.م</span>
                </div>
            </div>
        </div>

        <!-- Steps -->
        <div class="side-card">
            <div class="side-card-head">
                <i class="fas fa-list-ol"></i>
                خطوات الإضافة
            </div>
            <div class="side-card-body" style="padding-top:12px;">
                <div class="step-item">
                    <div class="step-num">1</div>
                    <div class="step-text">
                        <strong>اختر المنتج</strong>
                        من القائمة المنسدلة أو ابحث بالاسم.
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">2</div>
                    <div class="step-text">
                        <strong>أدخل الكمية</strong>
                        عدد الوحدات الموجودة حالياً.
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">3</div>
                    <div class="step-text">
                        <strong>حدد حد التنبيه</strong>
                        لاستقبال تنبيه انخفاض المخزون.
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">4</div>
                    <div class="step-text">
                        <strong>أدخل التكلفة</strong>
                        لحساب قيمة المخزون بدقة.
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
    // ── Product select preview ──
    function onProductChange(sel) {
        const opt     = sel.options[sel.selectedIndex];
        const preview = document.getElementById('productPreview');
        if (!sel.value) { preview.classList.remove('visible'); return; }
        document.getElementById('prevProdName').textContent = opt.text;
        document.getElementById('prevProdSku').textContent  = opt.dataset.sku || '';
        preview.classList.add('visible');
    }

    // Init if old value
    const initSel = document.getElementById('product_id');
    if (initSel && initSel.value) onProductChange(initSel);

    // ── Live summary ──
    function updateSummary() {
        const qty  = parseFloat(document.getElementById('quantity').value)      || 0;
        const min  = parseFloat(document.getElementById('min_stock').value)     || 0;
        const cost = parseFloat(document.getElementById('average_cost').value)  || 0;
        const total = qty * cost;

        document.getElementById('sumQty').textContent   = qty.toLocaleString('ar-EG');
        document.getElementById('sumMin').textContent   = min.toLocaleString('ar-EG');
        document.getElementById('sumCost').textContent  = cost.toFixed(2) + ' ج.م';
        document.getElementById('sumTotal').textContent = total.toFixed(2) + ' ج.م';
    }
    updateSummary();

    // ── Prevent double submit ──
    let submitting = false;
    document.getElementById('addProductForm').addEventListener('submit', function(e) {
        if (submitting) { e.preventDefault(); return; }
        submitting = true;
        const btn = document.getElementById('submitBtn');
        document.getElementById('spinner').style.display = 'block';
        document.getElementById('saveIcon').style.display = 'none';
        document.getElementById('saveText').textContent = 'جاري الإضافة...';
        btn.disabled = true;
    });
</script>
@endpush