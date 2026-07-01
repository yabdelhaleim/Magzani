@extends('layouts.app')

@section('title', 'تعديل أمر التصنيع')
@section('page-title', 'تعديل أمر التصنيع')

@push('styles')
<style>
    :root {
        --tf-bg: #f4f7fe; --tf-surface: #ffffff; --tf-border: #e4eaf7;
        --tf-indigo: #4f63d2; --tf-blue: #3a8ef0; --tf-green: #0faa7e;
        --tf-red: #dc2626; --tf-amber: #e8930a;
        --tf-text-h: #1a2140; --tf-text-b: #3d4f72; --tf-text-m: #7e90b0;
    }
    .mfg-page { background: var(--tf-bg); min-height: 100vh; padding: 16px; }
    @media (min-width: 1024px) { .mfg-page { padding: 26px 22px; } }
    @media (max-width: 767px) { .mfg-page { padding-bottom: 100px; } }

    .mfg-title { font-size: 20px; font-weight: 900; color: var(--tf-text-h); display: flex; align-items: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
    @media (min-width: 768px) { .mfg-title { font-size: 24px; } }
    .mfg-title i { color: var(--tf-indigo); }

    .mfg-card { background: var(--tf-surface); border-radius: 16px; border: 1px solid var(--tf-border); overflow: hidden; margin-bottom: 16px; }
    @media (min-width: 768px) { .mfg-card { margin-bottom: 20px; border-radius: 18px; } }
    .mfg-card-header { padding: 12px 16px; border-bottom: 1px solid var(--tf-border); display: flex; align-items: center; gap: 10px; }
    @media (min-width: 768px) { .mfg-card-header { padding: 16px 22px; } }
    .mfg-card-title { font-size: 14px; font-weight: 800; margin: 0; }
    @media (min-width: 768px) { .mfg-card-title { font-size: 16px; } }
    .mfg-card-body { padding: 16px; }
    @media (min-width: 768px) { .mfg-card-body { padding: 22px; } }

    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 8px 16px; border-radius: 10px; font-weight: 700; font-size: 13px; border: none; cursor: pointer; transition: all .3s; text-decoration: none; flex-wrap: wrap; }
    @media (min-width: 768px) { .btn { padding: 10px 20px; font-size: 14px; } }
    .btn-primary { background: var(--tf-indigo); color: #fff; }
    .btn-amber { background: var(--tf-amber); color: #fff; }
    .btn-red { background: var(--tf-red); color: #fff; }
    .btn-green { background: var(--tf-green); color: #fff; }
    .btn-sm { padding: 6px 12px; font-size: 12px; }
    .btn-block { width: 100%; }

    .form-group { margin-bottom: 16px; }
    @media (min-width: 768px) { .form-group { margin-bottom: 20px; } }
    .form-label { display: block; font-size: 13px; font-weight: 700; color: var(--tf-text-h); margin-bottom: 6px; }
    .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--tf-border); border-radius: 10px; font-size: 14px; background: #fff; }
    .form-control:focus { outline: none; border-color: var(--tf-indigo); box-shadow: 0 0 0 3px rgba(79,99,210,0.1); }

    .input-sm { width: 100%; padding: 8px 10px; border: 1px solid var(--tf-border); border-radius: 8px; font-size: 13px; text-align: center; color: var(--tf-text-b); background: #fff; }

    .grid-2 { display: grid; gap: 12px; }
    .grid-4 { display: grid; gap: 12px; }
    @media (min-width: 640px) { .grid-2 { grid-template-columns: repeat(2, 1fr); } .grid-4 { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 1024px) { .grid-2 { gap: 20px; } .grid-4 { grid-template-columns: repeat(2, 1fr); gap: 20px; } }

    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .mfg-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    @media (min-width: 768px) { .mfg-table { font-size: 14px; } }
    .mfg-table th { background: var(--tf-bg); padding: 10px 8px; text-align: right; font-weight: 700; font-size: 11px; color: var(--tf-text-h); white-space: nowrap; }
    .mfg-table td { padding: 8px; border-top: 1px solid var(--tf-border); }

    .remove-btn { background: var(--tf-red); color: white; border: none; border-radius: 8px; padding: 6px 10px; font-size: 12px; cursor: pointer; }

    .summary-box { background: linear-gradient(135deg, var(--tf-indigo), #3b52c0); color: white; padding: 16px; border-radius: 12px; display: flex; flex-direction: column; gap: 10px; }
    @media (min-width: 768px) { .summary-box { padding: 20px; border-radius: 16px; gap: 12px; } }
    .summary-row { display: flex; justify-content: space-between; font-size: 13px; align-items: center; }
    .summary-row.total { font-size: 16px; font-weight: 900; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 10px; margin-top: 6px; }
    .summary-label { opacity: 0.9; }
    .summary-value { font-weight: 800; }
    .summary-value.price { color: #ffdd57; font-size: 16px; }

    .action-buttons { display: flex; flex-direction: column; gap: 10px; position: fixed; bottom: 0; left: 0; right: 0; background: white; padding: 12px 16px; border-top: 1px solid var(--tf-border); box-shadow: 0 -4px 12px rgba(0,0,0,0.1); z-index: 100; }
    @media (min-width: 768px) { .action-buttons { position: static; flex-direction: row; background: transparent; padding: 0; border: none; box-shadow: none; } }

    .alert-draft { background: #fef3c7; color: #92400e; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-weight: 700; }
</style>
@endpush

@section('content')
<div class="mfg-page">
    <div class="mfg-title">
        <i class="fas fa-edit"></i>
        تعديل أمر التصنيع: {{ $order->order_number }}
    </div>

    @if(!$order->can_edit)
        <div class="alert-draft">هذا الأمر لا يقبل التعديل في حالته الحالية.</div>
    @endif

    @if(session('error'))
    <div style="background:#fee2e2; color:#dc2626; padding:14px 20px; border-radius:12px; margin-bottom:16px; font-weight:700;">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div style="background:#fee2e2; color:#dc2626; padding:14px 20px; border-radius:12px; margin-bottom:16px;">
        <ul style="margin:0; padding-right:18px;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('manufacturing-orders.update', $order->id) }}" id="mfg-edit-form">
        @csrf
        @method('PUT')

        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-cube" style="color:var(--tf-blue);"></i>
                <h3 class="mfg-card-title">مواصفات المنتج</h3>
            </div>
            <div class="mfg-card-body">
                <div class="grid-4">
                    <div class="form-group">
                        <label class="form-label">اسم المنتج</label>
                        <input type="text" name="product_name" class="form-control" required value="{{ old('product_name', $order->product_name) }}" @disabled(!$order->can_edit)>
                    </div>
                    <div class="form-group">
                        <label class="form-label">المستودع</label>
                        <select name="warehouse_id" id="warehouse_id" class="form-control" onchange="refreshAllWoodStockSelects()" @disabled(!$order->can_edit)>
                            <option value="">— اختياري —</option>
                            @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" @selected(old('warehouse_id', $order->warehouse_id) == $w->id)>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">العميل <span style="font-weight:500;color:var(--tf-text-m);">(تتبع الخشب)</span></label>
                        <select name="customer_id" class="form-control" @disabled(!$order->can_edit)>
                            <option value="">— اختياري —</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id', $order->customer_id) == $c->id)>{{ $c->name }}@if($c->phone) — {{ $c->phone }}@endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">عدد البالتات</label>
                        <input type="number" name="quantity_produced" id="quantity_produced" class="form-control" required step="any" min="0.01" value="{{ old('quantity_produced', $order->quantity_produced) }}" oninput="recalculateAll()" @disabled(!$order->can_edit)>
                    </div>
                </div>
            </div>
        </div>

        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-cubes" style="color:var(--tf-green);"></i>
                <h3 class="mfg-card-title">مكونات الخشب <span style="font-size:12px;font-weight:500;color:var(--tf-text-m);">(للبالة الواحدة)</span></h3>
            </div>
            <div class="mfg-card-body">
                <p style="font-size:13px;color:var(--tf-text-b);margin-bottom:12px;">تُصفّى دفعات الخشب حسب <strong>المستودع</strong> المختار؛ الدفعة الحالية تبقى ظاهرة حتى لو كانت من مستودع آخر.</p>
                @if($order->can_edit)
                <button type="button" class="btn btn-primary btn-sm btn-block" onclick="addComponent()" style="margin-bottom:16px;">
                    <i class="fas fa-plus"></i> إضافة قطعة خشب
                </button>
                @endif
                <div class="table-responsive">
                    <table class="mfg-table" id="components-table">
                        <thead>
                            <tr>
                                <th>دفعة الخشب</th>
                                <th>نوع الخام</th>
                                <th>السمك (سم)</th>
                                <th>العرض (سم)</th>
                                <th>الطول (سم)</th>
                                <th>العدد</th>
                                <th>م³</th>
                                <th>م²</th>
                                <th>ج.م/م³</th>
                                <th>التكلفة</th>
                                @if($order->can_edit)<th>إجراء</th>@endif
                            </tr>
                        </thead>
                        <tbody id="components-body">
                            @foreach($order->components as $idx => $component)
                            <tr>
                                <td data-label="دفعة الخشب">
                                    <select name="components[{{ $idx }}][wood_stock_id]" class="form-control wood-stock-select" style="padding:8px 12px;" onchange="onWoodStockChange(this)" @disabled(!$order->can_edit)>
                                        <option value="">— بدون دفعة —</option>
                                        @foreach($woodLots as $lot)
                                        <option value="{{ $lot['id'] }}" data-unit-cost="{{ $lot['unit_cost'] }}" @selected((string) old('components.'.$idx.'.wood_stock_id', $component->wood_stock_id ?? '') === (string) $lot['id'])>{{ $lot['label'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td data-label="نوع الخام">
                                    <select name="components[{{ $idx }}][component_type]" class="form-control component-type-select" style="padding:8px 12px;" onchange="onComponentTypeChange(this)" @disabled(!$order->can_edit)>
                                        @foreach($rawMaterials as $material)
                                        <option value="{{ $material->name }}" data-buy-price="{{ $material->buy_price }}" data-sale-price="{{ $material->sale_price }}" @selected(old('components.'.$idx.'.component_type', $component->component_type) === $material->name)>{{ $material->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td data-label="السمك (سم)"><input type="number" name="components[{{ $idx }}][thickness_cm]" class="input-sm" step="0.1" value="{{ old('components.'.$idx.'.thickness_cm', $component->thickness_cm) }}" oninput="recalculateAll()" @disabled(!$order->can_edit)></td>
                                <td data-label="العرض (سم)"><input type="number" name="components[{{ $idx }}][width_cm]" class="input-sm" step="0.1" value="{{ old('components.'.$idx.'.width_cm', $component->width_cm) }}" oninput="recalculateAll()" @disabled(!$order->can_edit)></td>
                                <td data-label="الطول (سم)"><input type="number" name="components[{{ $idx }}][length_cm]" class="input-sm" step="0.1" value="{{ old('components.'.$idx.'.length_cm', $component->length_cm) }}" oninput="recalculateAll()" @disabled(!$order->can_edit)></td>
                                <td data-label="العدد"><input type="number" name="components[{{ $idx }}][quantity]" class="input-sm" value="{{ old('components.'.$idx.'.quantity', $component->quantity) }}" min="0.0001" step="any" oninput="recalculateAll()" @disabled(!$order->can_edit)></td>
                                <td data-label="م³"><span class="vol-m3-display">0</span></td>
                                <td data-label="م²"><span class="vol-m2-display">0</span></td>
                                <td data-label="ج.م/م³">
                                    <span class="price-m3-readout">0</span>
                                    <input type="hidden" class="price-per-m3-hidden" name="components[{{ $idx }}][price_per_cubic_meter]" value="{{ old('components.'.$idx.'.price_per_cubic_meter', $component->price_per_cubic_meter) }}">
                                </td>
                                <td data-label="التكلفة"><span class="cost-display">0.00</span></td>
                                @if($order->can_edit)
                                <td data-label="إجراء"><button type="button" class="remove-btn" onclick="removeComponent(this)"><i class="fas fa-trash"></i></button></td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-coins" style="color:var(--tf-amber);"></i>
                <h3 class="mfg-card-title">التكاليف الإضافية (للبالتة الواحدة)</h3>
            </div>
            <div class="mfg-card-body">
                <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:12px;">
                    @php
                        $extraLabels = [
                            'waste_cost' => 'الخسارة / الفاقد',
                            'labor_cost' => 'العمالة',
                            'nails_cost' => 'المسامير',
                            'tips_cost' => 'إكراميات',
                            'transport_cost' => 'النقل',
                            'fumigation_cost' => 'التبخير / تعقيم',
                        ];
                    @endphp
                    @foreach($extraLabels as $field => $label)
                    <div class="form-group">
                        <label class="form-label">{{ $label }}</label>
                        @if($order->can_edit)
                        <input type="number" name="{{ $field }}" id="input-{{ $field }}" class="form-control" value="{{ old($field, $order->$field) }}" min="0" step="0.01" oninput="recalculateAll()">
                        @else
                        <input type="number" name="{{ $field }}" id="input-{{ $field }}" class="form-control" readonly value="{{ number_format($order->$field, 2, '.', '') }}">
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mfg-card">
            <div class="mfg-card-body">
                <div class="summary-box">
                    <div class="summary-row">
                        <span class="summary-label">تكلفة الخشب (للبالة):</span>
                        <span class="summary-value" id="disp-wood">0 ج.م</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">التكاليف الإضافية:</span>
                        <span class="summary-value" id="disp-extra">0 ج.م</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">تكلفة البالة (بدون ربح):</span>
                        <span class="summary-value" id="disp-cost-unit">0 ج.م</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">نسبة الربح %</span>
                        <span class="summary-value">
                            @if($order->can_edit)
                            <input type="number" name="profit_margin" id="profit_margin" class="input-sm" style="width:100px;background:rgba(255,255,255,0.15);color:#fff;border-color:rgba(255,255,255,0.3);" value="{{ old('profit_margin', $order->profit_margin) }}" min="0" step="0.01" oninput="recalculateAll()">
                            @else
                            <input type="hidden" id="profit_margin" value="{{ $order->profit_margin }}">
                            {{ number_format($order->profit_margin, 2) }}
                            @endif
                        </span>
                    </div>
                    <div class="summary-row total">
                        <span class="summary-label">سعر البيع للبالة</span>
                        <span class="summary-value price" id="disp-sell">0 ج.م</span>
                    </div>
                </div>
                <input type="hidden" name="cost_per_unit" id="cost_per_unit_hidden" value="{{ $order->cost_per_unit }}">
                <input type="hidden" name="total_cost" id="total_cost_hidden" value="{{ $order->total_cost }}">
                <input type="hidden" name="selling_price_per_unit" id="selling_price_per_unit_hidden" value="{{ $order->selling_price_per_unit }}">
                <input type="hidden" name="profit_amount" id="profit_amount_hidden" value="{{ $order->profit_amount }}">
            </div>
        </div>

        <div class="action-buttons">
            @if($order->can_edit)
            <button type="submit" class="btn btn-green btn-block">
                <i class="fas fa-save"></i> حفظ التعديلات
            </button>
            @endif
            <a href="{{ route('manufacturing-orders.show', $order->id) }}" class="btn btn-amber btn-block">
                <i class="fas fa-arrow-right"></i> رجوع للتفاصيل
            </a>
        </div>
    </form>
</div>

<script>
window.MAGZANI_WOOD_LOTS = @json($woodLots ?? []);

function getFilteredWoodLots() {
    const wid = document.getElementById('warehouse_id')?.value || '';
    const lots = window.MAGZANI_WOOD_LOTS || [];
    if (!wid) return lots;
    return lots.filter(function (l) { return String(l.warehouse_id) === String(wid); });
}

function woodStockSelectOptionsHtml(selectedId) {
    selectedId = selectedId ? String(selectedId) : '';
    const filtered = getFilteredWoodLots();
    const all = window.MAGZANI_WOOD_LOTS || [];
    let html = '<option value="">— بدون دفعة —</option>';
    filtered.forEach(function (l) {
        const sel = String(l.id) === selectedId ? ' selected' : '';
        html += '<option value="' + l.id + '" data-unit-cost="' + l.unit_cost + '"' + sel + '>' + (l.label || ('#' + l.id)) + '</option>';
    });
    if (selectedId && !filtered.some(function (l) { return String(l.id) === selectedId; })) {
        const orphan = all.find(function (l) { return String(l.id) === selectedId; });
        if (orphan) {
            html += '<option value="' + orphan.id + '" data-unit-cost="' + orphan.unit_cost + '" selected>(مستودع آخر) ' + (orphan.label || ('#' + orphan.id)) + '</option>';
        }
    }
    return html;
}

function refreshAllWoodStockSelects() {
    document.querySelectorAll('#components-body tr').forEach(function (row) {
        const sel = row.querySelector('.wood-stock-select');
        if (!sel) return;
        const cur = sel.value;
        sel.innerHTML = woodStockSelectOptionsHtml(cur);
        syncPriceHiddenFromRow(row);
    });
    recalculateAll();
}

function syncPriceHiddenFromRow(row) {
    const woodSel = row.querySelector('.wood-stock-select');
    const typeSel = row.querySelector('.component-type-select');
    const hidden = row.querySelector('.price-per-m3-hidden');
    const readout = row.querySelector('.price-m3-readout');
    let p = 0;
    if (woodSel && woodSel.value) {
        const o = woodSel.options[woodSel.selectedIndex];
        p = parseFloat(o?.dataset?.unitCost) || 0;
    } else if (typeSel && typeSel.value) {
        const o = typeSel.options[typeSel.selectedIndex];
        p = parseFloat(o?.dataset?.buyPrice) || 0;
    }
    if (hidden) hidden.value = p > 0 ? p.toFixed(4) : '0';
    if (readout) readout.textContent = p > 0 ? p.toFixed(2) : '0';
}

function onWoodStockChange(select) {
    syncPriceHiddenFromRow(select.closest('tr'));
    recalculateAll();
}

function onComponentTypeChange(select) {
    const row = select.closest('tr');
    const woodSel = row.querySelector('.wood-stock-select');
    if (woodSel && !woodSel.value) {
        syncPriceHiddenFromRow(row);
    }
    recalculateAll();
}

function addComponent() {
    const tbody = document.getElementById('components-body');
    const idx = tbody.querySelectorAll('tr').length;
    const row = document.createElement('tr');
    row.innerHTML = `
        <td data-label="دفعة الخشب">
            <select name="components[${idx}][wood_stock_id]" class="form-control wood-stock-select" style="padding:8px 12px;" onchange="onWoodStockChange(this)">
                ${woodStockSelectOptionsHtml('')}
            </select>
        </td>
        <td data-label="نوع الخام">
            <select name="components[${idx}][component_type]" class="form-control component-type-select" style="padding:8px 12px;" onchange="onComponentTypeChange(this)">
                @foreach($rawMaterials as $material)
                <option value="{{ $material->name }}" data-buy-price="{{ $material->buy_price }}" data-sale-price="{{ $material->sale_price }}">{{ $material->name }}</option>
                @endforeach
            </select>
        </td>
        <td data-label="السمك (سم)"><input type="number" name="components[${idx}][thickness_cm]" class="input-sm" step="0.1" value="0" oninput="recalculateAll()"></td>
        <td data-label="العرض (سم)"><input type="number" name="components[${idx}][width_cm]" class="input-sm" step="0.1" value="0" oninput="recalculateAll()"></td>
        <td data-label="الطول (سم)"><input type="number" name="components[${idx}][length_cm]" class="input-sm" step="0.1" value="0" oninput="recalculateAll()"></td>
        <td data-label="العدد"><input type="number" name="components[${idx}][quantity]" class="input-sm" value="1" min="0.0001" step="any" oninput="recalculateAll()"></td>
        <td data-label="م³"><span class="vol-m3-display">0</span></td>
        <td data-label="م²"><span class="vol-m2-display">0</span></td>
        <td data-label="ج.م/م³"><span class="price-m3-readout">0</span><input type="hidden" class="price-per-m3-hidden" name="components[${idx}][price_per_cubic_meter]" value="0"></td>
        <td data-label="التكلفة"><span class="cost-display">0.00</span></td>
        <td data-label="إجراء"><button type="button" class="remove-btn" onclick="removeComponent(this)"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
}

function removeComponent(btn) {
    const tbody = document.getElementById('components-body');
    if (tbody.querySelectorAll('tr').length <= 1) return;
    btn.closest('tr').remove();
    reindexComponentRows();
    recalculateAll();
}

function reindexComponentRows() {
    document.querySelectorAll('#components-body tr').forEach(function (row, idx) {
        row.querySelectorAll('[name^="components["]').forEach(function (el) {
            el.name = el.name.replace(/components\[\d+]/, 'components[' + idx + ']');
        });
    });
}

function recalculateAll() {
    let woodCost = 0;
    document.querySelectorAll('#components-body tr').forEach(function (row) {
        syncPriceHiddenFromRow(row);
        const t = parseFloat(row.querySelector('[name*="[thickness_cm]"]')?.value) || 0;
        const w = parseFloat(row.querySelector('[name*="[width_cm]"]')?.value) || 0;
        const l = parseFloat(row.querySelector('[name*="[length_cm]"]')?.value) || 0;
        const q = parseFloat(row.querySelector('[name*="[quantity]"]')?.value) || 0;
        const volCm3 = t * w * l * q;
        const volM3 = volCm3 / 1000000;
        const thicknessM = t / 100;
        const m2 = thicknessM > 0 ? volM3 / thicknessM : 0;
        const price = parseFloat(row.querySelector('.price-per-m3-hidden')?.value) || 0;
        const cost = volM3 * price;
        woodCost += cost;
        const m3el = row.querySelector('.vol-m3-display');
        const m2el = row.querySelector('.vol-m2-display');
        if (m3el) m3el.textContent = volM3.toFixed(4);
        if (m2el) m2el.textContent = m2.toFixed(4);
        const display = row.querySelector('.cost-display');
        if (display) display.textContent = cost.toFixed(2);
    });

    const fields = ['waste_cost','labor_cost','nails_cost','tips_cost','transport_cost','fumigation_cost'];
    let extra = 0;
    fields.forEach(function (f) {
        const el = document.getElementById('input-' + f) || document.querySelector('[name="' + f + '"]');
        extra += parseFloat(el?.value) || 0;
    });

    const costPerUnit = woodCost + extra;
    const profitMargin = parseFloat(document.getElementById('profit_margin')?.value) || 0;
    const profitPerUnit = costPerUnit * (profitMargin / 100);
    const selling = costPerUnit + profitPerUnit;
    const qty = parseFloat(document.getElementById('quantity_produced')?.value) || 1;
    const totalCost = costPerUnit * qty;
    const totalProfit = profitPerUnit * qty;

    const dw = document.getElementById('disp-wood');
    const de = document.getElementById('disp-extra');
    const du = document.getElementById('disp-cost-unit');
    const ds = document.getElementById('disp-sell');
    if (dw) dw.textContent = woodCost.toFixed(2) + ' ج.م';
    if (de) de.textContent = extra.toFixed(2) + ' ج.م';
    if (du) du.textContent = costPerUnit.toFixed(2) + ' ج.م';
    if (ds) ds.textContent = selling.toFixed(2) + ' ج.م';

    const h1 = document.getElementById('cost_per_unit_hidden');
    const h2 = document.getElementById('total_cost_hidden');
    const h3 = document.getElementById('selling_price_per_unit_hidden');
    const h4 = document.getElementById('profit_amount_hidden');
    if (h1) h1.value = costPerUnit.toFixed(4);
    if (h2) h2.value = totalCost.toFixed(4);
    if (h3) h3.value = selling.toFixed(4);
    if (h4) h4.value = totalProfit.toFixed(4);
}

document.addEventListener('DOMContentLoaded', function () {
    refreshAllWoodStockSelects();
    recalculateAll();
});

@if($order->components->isEmpty() && $order->can_edit)
addComponent();
@endif
</script>
@endsection
