@extends('layouts.app')

@section('title', 'إنشاء أمر تصنيع جديد')
@section('page-title', 'إنشاء أمر تصنيع جديد')

@push('styles')
<style>
    :root {
        --tf-bg: #f4f7fe; --tf-surface: #ffffff; --tf-border: #e4eaf7;
        --tf-indigo: #4f63d2; --tf-blue: #3a8ef0; --tf-green: #0faa7e;
        --tf-red: #dc2626; --tf-amber: #e8930a;
        --tf-text-h: #1a2140; --tf-text-b: #3d4f72; --tf-text-m: #7e90b0;
    }

    .mfg-page {
        background: var(--tf-bg);
        min-height: 100vh;
        padding: 16px;
    }

    /* Desktop */
    @media (min-width: 1024px) {
        .mfg-page { padding: 26px 22px; }
    }

    /* Make room for fixed action buttons on mobile */
    @media (max-width: 767px) {
        .mfg-page { padding-bottom: 100px; }
    }

    .mfg-title {
        font-size: 20px;
        font-weight: 900;
        color: var(--tf-text-h);
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    @media (min-width: 768px) {
        .mfg-title { font-size: 24px; }
    }

    .mfg-title i { color: var(--tf-indigo); }

    .mfg-card {
        background: var(--tf-surface);
        border-radius: 16px;
        border: 1px solid var(--tf-border);
        overflow: hidden;
        margin-bottom: 16px;
    }

    @media (min-width: 768px) {
        .mfg-card { margin-bottom: 20px; border-radius: 18px; }
    }

    .mfg-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid var(--tf-border);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    @media (min-width: 768px) {
        .mfg-card-header { padding: 16px 22px; }
    }

    .mfg-card-title {
        font-size: 14px;
        font-weight: 800;
        margin: 0;
    }

    @media (min-width: 768px) {
        .mfg-card-title { font-size: 16px; }
    }

    .mfg-card-body {
        padding: 16px;
    }

    @media (min-width: 768px) {
        .mfg-card-body { padding: 22px; }
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        border: none;
        cursor: pointer;
        transition: all .3s;
        text-decoration: none;
        flex-wrap: wrap;
    }

    @media (min-width: 768px) {
        .btn { padding: 10px 20px; font-size: 14px; }
    }

    .btn-primary { background: var(--tf-indigo); color: #fff; }
    .btn-amber { background: var(--tf-amber); color: #fff; }
    .btn-red { background: var(--tf-red); color: #fff; }
    .btn-green { background: var(--tf-green); color: #fff; }
    .btn-sm { padding: 6px 12px; font-size: 12px; }
    .btn-block { width: 100%; }

    /* Form */
    .form-group { margin-bottom: 16px; }
    @media (min-width: 768px) { .form-group { margin-bottom: 20px; } }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: var(--tf-text-h);
        margin-bottom: 6px;
    }

    @media (min-width: 768px) {
        .form-label { font-size: 14px; margin-bottom: 8px; }
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--tf-border);
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s;
        background: #fff;
    }

    @media (min-width: 768px) {
        .form-control { padding: 10px 14px; }
    }

    .form-control:focus {
        outline: none;
        border-color: var(--tf-indigo);
        box-shadow: 0 0 0 3px rgba(79,99,210,0.1);
    }

    .input-sm {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid var(--tf-border);
        border-radius: 8px;
        font-size: 13px;
        text-align: center;
        color: var(--tf-text-b);
        background: #fff;
    }

    /* Grid layouts */
    .grid-2 { display: grid; gap: 12px; }
    .grid-3 { display: grid; gap: 12px; }
    .grid-4 { display: grid; gap: 12px; }

    @media (min-width: 640px) {
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(2, 1fr); }
        .grid-4 { grid-template-columns: repeat(2, 1fr); }
    }

    @media (min-width: 1024px) {
        .grid-2 { grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .grid-3 { grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .grid-4 { grid-template-columns: 2fr 1fr 1fr 1fr; gap: 20px; }
    }

    /* Table */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .mfg-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    @media (min-width: 768px) {
        .mfg-table { font-size: 14px; }
    }

    .mfg-table th {
        background: var(--tf-bg);
        padding: 10px 8px;
        text-align: right;
        font-weight: 700;
        font-size: 11px;
        color: var(--tf-text-h);
        white-space: nowrap;
    }

    @media (min-width: 768px) {
        .mfg-table th { padding: 12px 10px; font-size: 12px; }
    }

    .mfg-table td {
        padding: 8px;
        border-top: 1px solid var(--tf-border);
    }

    @media (min-width: 768px) {
        .mfg-table td { padding: 10px; }
    }

    /* ==========================================
       MOBILE: Card-style table rows (< 768px)
       ========================================== */
    @media (max-width: 767px) {
        .mfg-table thead {
            display: none;
        }

        .mfg-table tbody tr {
            display: block;
            background: #f8faff;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 12px;
            border: 1px solid var(--tf-border);
        }

        .mfg-table tbody td {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 0;
            border-top: none;
            text-align: right;
        }

        .mfg-table tbody td::before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 11px;
            color: var(--tf-text-h);
            white-space: nowrap;
            flex-shrink: 0;
            min-width: 70px;
        }

        .mfg-table tbody td .input-sm,
        .mfg-table tbody td .form-control,
        .mfg-table tbody td select.form-control {
            flex: 1;
            min-width: 0;
        }

        .mfg-table tbody td .cost-display {
            font-weight: 700;
            color: var(--tf-indigo);
        }

        .mfg-table tbody td:last-child {
            justify-content: flex-end;
            padding-top: 8px;
            border-top: 1px solid var(--tf-border);
            margin-top: 4px;
        }

        .mfg-table tbody td:last-child::before {
            display: none;
        }

        /* Cost display suffix for components table (column 7 of 8) */
        #components-table tbody td:nth-child(7)::after {
            content: ' ج.م';
            font-size: 11px;
            color: var(--tf-text-m);
        }

        /* Cost display suffix for additional table (column 4 of 5) */
        #additional-table tbody td:nth-child(4)::after {
            content: ' ج.م';
            font-size: 11px;
            color: var(--tf-text-m);
        }
    }

    /* Summary */
    .summary-box {
        background: linear-gradient(135deg, var(--tf-indigo), #3b52c0);
        color: white;
        padding: 16px;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    @media (min-width: 768px) {
        .summary-box { padding: 20px; border-radius: 16px; gap: 12px; }
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        align-items: center;
    }

    @media (min-width: 768px) {
        .summary-row { font-size: 14px; }
    }

    .summary-row.total {
        font-size: 16px;
        font-weight: 900;
        border-top: 1px solid rgba(255,255,255,0.2);
        padding-top: 10px;
        margin-top: 6px;
    }

    @media (min-width: 768px) {
        .summary-row.total { font-size: 18px; padding-top: 12px; margin-top: 8px; }
    }

    .summary-label { opacity: 0.9; }
    .summary-value { font-weight: 800; }
    .summary-value.price { color: #ffdd57; font-size: 16px; }

    @media (min-width: 768px) {
        .summary-value.price { font-size: 18px; }
    }

    /* Info box */
    .info-box {
        background: linear-gradient(135deg, #ecfdf5, #f0f9ff);
        padding: 14px 16px;
        border-radius: 12px;
        margin-bottom: 16px;
        border: 2px solid var(--tf-green);
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    @media (min-width: 768px) {
        .info-box { padding: 18px 22px; margin-bottom: 24px; border-radius: 16px; gap: 16px; }
    }

    .info-box i {
        color: var(--tf-green);
        font-size: 24px;
        flex-shrink: 0;
    }

    @media (min-width: 768px) {
        .info-box i { font-size: 32px; }
    }

    .info-box h4 {
        margin: 0 0 6px 0;
        color: var(--tf-text-h);
        font-size: 14px;
    }

    @media (min-width: 768px) {
        .info-box h4 { margin: 0 0 8px 0; font-size: 16px; }
    }

    .info-box p {
        margin: 0;
        color: var(--tf-text-b);
        font-size: 13px;
        line-height: 1.6;
    }

    @media (min-width: 768px) {
        .info-box p { font-size: 14px; line-height: 1.7; }
    }

    /* Section heading */
    .section-heading {
        font-size: 14px;
        font-weight: 800;
        color: var(--tf-text-h);
        margin: 20px 0 10px 0;
        padding-bottom: 6px;
        border-bottom: 2px solid var(--tf-border);
    }

    @media (min-width: 768px) {
        .section-heading { font-size: 16px; margin: 24px 0 12px 0; padding-bottom: 8px; }
    }

    /* Action buttons */
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 12px 16px;
        border-top: 1px solid var(--tf-border);
        box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
        z-index: 100;
    }

    @media (min-width: 768px) {
        .action-buttons {
            position: static;
            flex-direction: row;
            background: transparent;
            padding: 0;
            border: none;
            box-shadow: none;
        }
    }

    /* Remove button */
    .remove-btn {
        background: var(--tf-red);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 12px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
</style>
@endpush

@section('content')
<div class="mfg-page">
    <div class="mfg-title">
        <i class="fas fa-industry"></i>
        إنشاء أمر تصنيع جديد
    </div>

    <!-- Info Box -->
    <div class="info-box">
        <i class="fas fa-lightbulb"></i>
        <div>
            <h4>كيف يعمل النظام؟ 🎯</h4>
            <p>
                <strong style="color:var(--tf-green);">المكونات التي تدخلها هي للبالة الواحدة فقط.</strong>
                <br>
                مثال: لو منتِج <strong>50 بالة</strong>، كل بالة هتاخد نفس المكونات اللي هتدخلها بالأسود.
                <br>
                <span style="color:var(--tf-text-m); font-size:12px;">النظام هيتولى حساب الإجمالي تلقائياً: (تكلفة البالة × عدد البالات)</span>
            </p>
        </div>
    </div>

    @if(session('error'))
    <div style="background:#fee2e2; color:#dc2626; padding:14px 20px; border-radius:12px; margin-bottom:16px; font-weight:700;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div style="background:#fee2e2; color:#dc2626; padding:14px 20px; border-radius:12px; margin-bottom:16px; font-weight:700;">
        <i class="fas fa-exclamation-triangle"></i> أخطاء في النموذج:
        <ul style="margin:10px 0 0 20px; padding:0;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('manufacturing-orders.store') }}" id="mfg-form">
        @csrf

        <!-- Product Specs -->
        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-cube" style="color:var(--tf-blue);"></i>
                <h3 class="mfg-card-title">مواصفات المنتج</h3>
            </div>
            <div class="mfg-card-body">
                <div class="grid-4">
                    <div class="form-group">
                        <label class="form-label">اسم المنتج</label>
                        <input type="text" name="product_name" class="form-control" required placeholder="مثال: بالاطة 110×120">
                    </div>
                    <div class="form-group">
                        <label class="form-label">المستودع</label>
                        <select name="warehouse_id" class="form-control">
                            <option value="">-- اختر المستودع --</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            عدد البالتات
                            <span style="color:var(--tf-blue); font-size:11px; font-weight:normal;">
                                (للبالة الواحدة)
                            </span>
                        </label>
                        <input type="number" name="quantity_produced" id="quantity_produced" class="form-control" required step="any" min="0.01" value="1" oninput="recalculateAll()">
                        <small style="color:var(--tf-text-m); font-size:11px; display:block; margin-top:4px;">
                            💡 النظام سيقوم تلقائياً بضرب تكلفة البالة في هذا العدد
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wood Components -->
        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-cubes" style="color:var(--tf-green);"></i>
                <h3 class="mfg-card-title">
                    مكونات الخشب
                    <span style="color:var(--tf-blue); font-size:12px; font-weight:normal; margin-right:8px;">
                        📦 Recipe للبالة الواحدة
                    </span>
                </h3>
            </div>
            <div class="mfg-card-body">
                <div style="background:linear-gradient(135deg, #e3f2fd, #f3e5f5); padding:12px 16px; border-radius:12px; margin-bottom:16px; border-left:4px solid var(--tf-indigo);">
                    <div style="font-size:13px; color:var(--tf-text-h);">
                        <strong>ملاحظة هامة:</strong> المكونات أدناه تمثل الخامات اللازمة لـ <strong>بالة واحدة</strong> فقط.
                    </div>
                </div>

                <button type="button" class="btn btn-primary btn-sm btn-block" onclick="addComponent()" style="margin-bottom:16px;">
                    <i class="fas fa-plus"></i> [+ إضافة قطعة خشب]
                </button>

                <div class="table-responsive">
                    <table class="mfg-table" id="components-table">
                        <thead>
                            <tr>
                                <th>النوع</th>
                                <th>السمك (سم)</th>
                                <th>العرض (سم)</th>
                                <th>الطول (م)</th>
                                <th>العدد</th>
                                <th>سعر المتر</th>
                                <th>التكلفة</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody id="components-body">
                            <!-- Components will be added here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Additional Components -->
        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-tools" style="color:var(--tf-amber);"></i>
                <h3 class="mfg-card-title">مكونات إضافية (غراء، مسامير، إلخ)</h3>
            </div>
            <div class="mfg-card-body">
                <button type="button" class="btn btn-amber btn-sm btn-block" onclick="addAdditionalComponent()" style="margin-bottom:16px;">
                    <i class="fas fa-plus"></i> [+ إضافة مكون إضافي]
                </button>

                <div class="table-responsive">
                    <table class="mfg-table" id="additional-table">
                        <thead>
                            <tr>
                                <th>اسم المكون</th>
                                <th>العدد/الكمية</th>
                                <th>سعر الوحدة</th>
                                <th>التكلفة</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody id="additional-body">
                            <!-- Additional components will be added here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-calculator" style="color:var(--tf-indigo);"></i>
                <h3 class="mfg-card-title">ملخص التكاليف</h3>
            </div>
            <div class="mfg-card-body">
                <div class="grid-2" style="margin-bottom:16px;">
                    <div class="summary-box">
                        <div class="summary-row">
                            <span class="summary-label">تكلفة الخشب:</span>
                            <span class="summary-value" id="wood-cost">0.00 ج.م</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">المكونات الإضافية:</span>
                            <span class="summary-value" id="additional-cost">0.00 ج.م</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">تكلفة البالة الواحدة:</span>
                            <span class="summary-value" id="pallet-cost">0.00 ج.م</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">نسبة الخامات (وفرة)⁉:</span>
                            <span class="summary-value"><input type="number" name="waste_cost" id="input-waste" value="0" min="0" step="0.01" class="input-sm" style="width:100px; text-align:center;" oninput="recalculateAll()"></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">تكلفة العمالة:</span>
                            <span class="summary-value"><input type="number" name="labor_cost" id="input-labor" value="0" min="0" step="0.01" class="input-sm" style="width:100px; text-align:center;" oninput="recalculateAll()"></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">تكلفة المسامير:</span>
                            <span class="summary-value"><input type="number" name="nails_cost" id="input-nails" value="0" min="0" step="0.01" class="input-sm" style="width:100px; text-align:center;" oninput="recalculateAll()"></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">الskills (نصائح)⁉:</span>
                            <span class="summary-value"><input type="number" name="tips_cost" id="input-tips" value="0" min="0" step="0.01" class="input-sm" style="width:100px; text-align:center;" oninput="recalculateAll()"></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">التنقل/النقل⁉:</span>
                            <span class="summary-value"><input type="number" name="transport_cost" id="input-transport" value="0" min="0" step="0.01" class="input-sm" style="width:100px; text-align:center;" oninput="recalculateAll()"></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">تكلفة التقدير/التعقيم⁉:</span>
                            <span class="summary-value"><input type="number" name="fumigation_cost" id="input-fumigation" value="0" min="0" step="0.01" class="input-sm" style="width:100px; text-align:center;" oninput="recalculateAll()"></span>
                        </div>
                        <div class="summary-row total">
                            <span class="summary-label">المجموع الفرعي (قبل الربح):</span>
                            <span class="summary-value price" id="subtotal-before-profit">0.00 ج.م</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">نسبة الربح (%):</span>
                            <span class="summary-value"><input type="number" name="profit_margin" id="input-profit-margin" value="0" min="0" step="0.01" class="input-sm" style="width:100px; text-align:center;" oninput="recalculateAll()"></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">مبلغ الربح:</span>
                            <span class="summary-value price" id="profit-amount">0.00 ج.م</span>
                        </div>
                        <div class="summary-row total" style="background:rgba(255,255,255,0.1); padding:12px; border-radius:8px; margin-top:8px;">
                            <span class="summary-label">تكلفة البالة النهائية:</span>
                            <span class="summary-value price" style="font-size:20px;" id="final-pallet-cost">0.00 ج.م</span>
                        </div>
                    </div>

                    <div class="summary-box" style="background: linear-gradient(135deg, var(--tf-green), #059669);">
                        <div class="summary-row">
                            <span class="summary-label">عدد البالات المنتجة:</span>
                            <span class="summary-value" id="summary-quantity">1</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">تكلفة البالة (وحدة):</span>
                            <span class="summary-value" id="summary-pallet">0.00 ج.م</span>
                        </div>
                        <div class="summary-row total">
                            <span class="summary-label">الإجمالي الكلي (للطلب):</span>
                            <span class="summary-value price" style="font-size:22px;" id="summary-total">0.00 ج.م</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button type="submit" class="btn btn-green">
                <i class="fas fa-check"></i> حفظ أمر التصنيع
            </button>
            <a href="{{ route('manufacturing-orders.index') }}" class="btn btn-red">
                <i class="fas fa-times"></i> إلغاء
            </a>
        </div>
    </form>
</div>

<script>
let componentIndex = 0;
let additionalIndex = 0;

function addComponent() {
    componentIndex++;
    const tbody = document.getElementById('components-body');

    const row = document.createElement('tr');
    row.innerHTML = `
        <td data-label="النوع">
            <select name="components[${componentIndex}][type]" class="form-control" style="padding:6px 8px;">
                <option value="الفرش1">الفرش1</option>
                <option value="الروباط ">الروباط </option>
                <option value="الشاسيه">الشاسيه</option>
                <option value="دكم او عوارض">دكم او عوارض</option>
            </select>
        </td>
        <td data-label="السمك (سم)"><input type="number" name="components[${componentIndex}][thickness]" class="input-sm" step="0.1" placeholder="2.5"></td>
        <td data-label="العرض (سم)"><input type="number" name="components[${componentIndex}][width]" class="input-sm" step="0.1" placeholder="12"></td>
        <td data-label="الطول (م)"><input type="number" name="components[${componentIndex}][length]" class="input-sm" step="0.01" placeholder="4.00"></td>
        <td data-label="العدد"><input type="number" name="components[${componentIndex}][quantity]" class="input-sm" value="1"></td>
        <td data-label="سعر المتر"><input type="number" name="components[${componentIndex}][price]" class="input-sm" step="0.01" placeholder="0" oninput="recalculateAll()"></td>
        <td data-label="التكلفة"><span class="cost-display">0.00</span></td>
        <td data-label="إجراء"><button type="button" class="remove-btn" onclick="removeComponent(${componentIndex}, this)"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
}

function removeComponent(index, btn) {
    const row = btn.closest('tr');
    if (row) row.remove();

    recalculateAll();
}

function addAdditionalComponent() {
    additionalIndex++;
    const tbody = document.getElementById('additional-body');

    const row = document.createElement('tr');
    row.innerHTML = `
        <td data-label="اسم المكون"><input type="text" name="additional[${additionalIndex}][name]" class="input-sm" placeholder="غراء، مسامير، إلخ" style="text-align:right;"></td>
        <td data-label="العدد/الكمية"><input type="number" name="additional[${additionalIndex}][quantity]" class="input-sm" value="1"></td>
        <td data-label="سعر الوحدة"><input type="number" name="additional[${additionalIndex}][price]" class="input-sm" step="0.01" placeholder="0" oninput="recalculateAll()"></td>
        <td data-label="التكلفة"><span class="cost-display">0.00</span></td>
        <td data-label="إجراء"><button type="button" class="remove-btn" onclick="removeAdditional(${additionalIndex}, this)"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
}

function removeAdditional(index, btn) {
    const row = btn.closest('tr');
    if (row) row.remove();

    recalculateAll();
}

function recalculateAll() {
    let woodCost = 0;
    let additionalTableCost = 0;

    // Calculate wood components
    document.querySelectorAll('#components-body tr').forEach(row => {
        const inputs = row.querySelectorAll('input');
        const length = parseFloat(inputs[2]?.value) || 0;
        const quantity = parseFloat(inputs[3]?.value) || 0;
        const price = parseFloat(inputs[4]?.value) || 0;
        const cost = length * quantity * price;
        woodCost += cost;

        const display = row.querySelector('.cost-display');
        if (display) display.textContent = cost.toFixed(2);
    });

    // Calculate additional components (glue, nails, etc.)
    document.querySelectorAll('#additional-body tr').forEach(row => {
        const inputs = row.querySelectorAll('input');
        const quantity = parseFloat(inputs[0]?.value) || 0;
        const price = parseFloat(inputs[1]?.value) || 0;
        const cost = quantity * price;
        additionalTableCost += cost;

        const display = row.querySelector('.cost-display');
        if (display) display.textContent = cost.toFixed(2);
    });

    // Read additional cost inputs from the summary section
    const wasteCost     = parseFloat(document.getElementById('input-waste')?.value) || 0;
    const laborCost     = parseFloat(document.getElementById('input-labor')?.value) || 0;
    const nailsCost     = parseFloat(document.getElementById('input-nails')?.value) || 0;
    const tipsCost      = parseFloat(document.getElementById('input-tips')?.value) || 0;
    const transportCost = parseFloat(document.getElementById('input-transport')?.value) || 0;
    const fumigationCost = parseFloat(document.getElementById('input-fumigation')?.value) || 0;
    const profitMargin  = parseFloat(document.getElementById('input-profit-margin')?.value) || 0;

    const allAdditionalCosts = wasteCost + laborCost + nailsCost + tipsCost + transportCost + fumigationCost;

    // Base wood cost from table + additional components from table
    const tableComponentsTotal = woodCost + additionalTableCost;

    // palletCost = wood table cost + additional table cost (per pallet, before extras)
    const palletCost = tableComponentsTotal;

    // subtotal (before profit) = palletCost + all additional costs
    const subtotalBeforeProfit = palletCost + allAdditionalCosts;

    // profit amount from margin
    const profitAmount = subtotalBeforeProfit * (profitMargin / 100);

    // final pallet cost
    const finalPalletCost = subtotalBeforeProfit + profitAmount;

    const quantityProduced = parseFloat(document.getElementById('quantity_produced').value) || 1;
    const totalCost = finalPalletCost * quantityProduced;

    // Update wood/additional/pallet display
    document.getElementById('wood-cost').textContent = woodCost.toFixed(2) + ' ج.م';
    document.getElementById('additional-cost').textContent = additionalTableCost.toFixed(2) + ' ج.م';
    document.getElementById('pallet-cost').textContent = palletCost.toFixed(2) + ' ج.م';

    // Update subtotal/profit/final display
    document.getElementById('subtotal-before-profit').textContent = subtotalBeforeProfit.toFixed(2) + ' ج.م';
    document.getElementById('profit-amount').textContent = profitAmount.toFixed(2) + ' ج.م';
    document.getElementById('final-pallet-cost').textContent = finalPalletCost.toFixed(2) + ' ج.م';

    // Update green summary box
    document.getElementById('summary-quantity').textContent = quantityProduced;
    document.getElementById('summary-pallet').textContent = finalPalletCost.toFixed(2) + ' ج.م';
    document.getElementById('summary-total').textContent = totalCost.toFixed(2) + ' ج.م';
}

// Add one component by default
addComponent();
</script>
@endsection
