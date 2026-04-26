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
    .mfg-page { background: var(--tf-bg); min-height: 100vh; padding: 26px 22px; }

    .mfg-title { font-size:24px; font-weight:900; color:var(--tf-text-h); display:flex; align-items:center; gap:12px; margin-bottom:24px; }
    .mfg-title i { color:var(--tf-indigo); }

    .mfg-card {
        background: var(--tf-surface); border-radius: 18px;
        border: 1px solid var(--tf-border); overflow: hidden; margin-bottom: 20px;
    }
    .mfg-card-header {
        padding: 16px 22px; border-bottom: 1px solid var(--tf-border);
        display: flex; align-items: center; gap: 10px;
    }
    .mfg-card-title { font-size: 16px; font-weight: 800; margin: 0; }
    .mfg-card-body { padding: 22px; }

    .btn {
        display:inline-flex; align-items:center; gap:8px;
        padding:10px 20px; border-radius:12px; font-weight:700;
        font-size:14px; border:none; cursor:pointer;
        transition:all .3s; text-decoration:none;
    }
    .btn-primary { background:var(--tf-indigo); color:#fff; }
    .btn-amber { background:var(--tf-amber); color:#fff; }
    .btn-red { background:var(--tf-red); color:#fff; }
    .btn-green { background:var(--tf-green); color:#fff; }
    .btn-sm { padding:6px 12px; font-size:12px; }

    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 14px; font-weight: 700; color: var(--tf-text-h); margin-bottom: 8px; }
    .form-control {
        width: 100%; padding: 10px 14px; border: 1px solid var(--tf-border);
        border-radius: 10px; font-size: 14px; transition: all 0.3s;
        background: #fff;
    }
    .form-control:focus { outline: none; border-color: var(--tf-indigo); box-shadow: 0 0 0 3px rgba(79,99,210,0.1); }

    .input-sm { width: 100%; padding: 8px 12px; border: 1px solid var(--tf-border); border-radius: 8px; font-size: 13px; text-align: center; }
    .input-sm:focus { outline: none; border-color: var(--tf-indigo); }

    .component-row {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr 0.8fr 0.8fr 0.8fr 1fr 1fr 1fr auto;
        gap: 10px;
        align-items: center;
        margin-bottom: 12px;
        padding: 14px;
        background: #f8faff;
        border-radius: 12px;
    }

    .section-heading {
        font-size:16px; font-weight:800; color:var(--tf-text-h);
        margin:24px 0 12px 0; padding-bottom:8px;
        border-bottom:2px solid var(--tf-border);
    }
</style>
@endpush

@section('content')
<div class="mfg-page">
    <div class="mfg-title">
        <i class="fas fa-edit"></i>
        تعديل أمر التصنيع: {{ $order->order_number }}
    </div>

    <form method="POST" action="{{ route('manufacturing-orders.update', $order->id) }}">
        @method('PUT')
        @csrf

        <!-- Product Specs -->
        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-cube" style="color:var(--tf-blue);"></i>
                <h3 class="mfg-card-title">مواصفات المنتج</h3>
            </div>
            <div class="mfg-card-body">
                <div style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap:20px;">
                    <div class="form-group">
                        <label class="form-label">اسم المنتج</label>
                        <input type="text" name="product_name" class="form-control" required value="{{ $order->product_name }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">عدد البالتات المنتجة</label>
                        <input type="number" name="quantity_produced" id="quantity_produced" class="form-control" required step="any" min="0.01" value="{{ $order->quantity_produced }}" oninput="recalculateAll()">
                    </div>
                </div>
            </div>
        </div>

        <!-- Wood Components -->
        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-cubes" style="color:var(--tf-green);"></i>
                <h3 class="mfg-card-title">مكونات الخشب</h3>
            </div>
            <div class="mfg-card-body">
                <button type="button" class="btn btn-primary btn-sm" onclick="addComponent()">
                    <i class="fas fa-plus"></i> [+ إضافة قطعة خشب]
                </button>

                <div style="margin-top:16px; overflow-x:auto;">
                    <table class="mfg-table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>النوع</th>
                                <th>العدد</th>
                                <th>التخانة (سم)</th>
                                <th>العرض (سم)</th>
                                <th>الطول (سم)</th>
                                <th>التكعيب (سم³)</th>
                                <th>سعر المتر</th>
                                <th>الإجمالي</th>
                                <th>حذف</th>
                            </tr>
                        </thead>
                        <tbody id="components-body">
                            @foreach($order->components as $index => $component)
                            <tr>
                                <td style="padding:8px;">
                                    <select name="components[{{ $index }}][component_type]" class="input-sm" required style="width:100%;">
                                        @foreach(['فرش','روباط','شاسية','دكم'] as $type)
                                        <option value="{{ $type }}" {{ $component->component_type === $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="padding:8px;"><input type="number" name="components[{{ $index }}][quantity]" class="input-sm" value="{{ $component->quantity }}" step="any" min="0.0001" required oninput="recalculateComponent(this)"></td>
                                <td style="padding:8px;"><input type="number" name="components[{{ $index }}][thickness_cm]" class="input-sm" value="{{ $component->thickness_cm }}" step="any" min="0" required oninput="recalculateComponent(this)"></td>
                                <td style="padding:8px;"><input type="number" name="components[{{ $index }}][width_cm]" class="input-sm" value="{{ $component->width_cm }}" step="any" min="0" required oninput="recalculateComponent(this)"></td>
                                <td style="padding:8px;"><input type="number" name="components[{{ $index }}][length_cm]" class="input-sm" value="{{ $component->length_cm }}" step="any" min="0" required oninput="recalculateComponent(this)"></td>
                                <td style="padding:8px;"><span class="cubic-display">{{ number_format($component->volume_cm3, 0) }}</span> cm³</td>
                                <td style="padding:8px;"><input type="number" name="components[{{ $index }}][price_per_cubic_meter]" class="input-sm" value="{{ $component->price_per_cubic_meter }}" step="any" min="0" required oninput="recalculateComponent(this)"></td>
                                <td style="padding:8px;"><span class="component-total">{{ number_format($component->total_cost, 2) }}</span> ج.م</td>
                                <td style="text-align:center; padding:8px;">
                                    <button type="button" class="btn btn-sm btn-red" onclick="removeRow(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="background:#f0f4f8; font-weight:800;">
                            <tr>
                                <td colspan="7" style="text-align:left; padding:12px;">إجمالي تكلفة الخشب (للواحدة)</td>
                                <td id="components-total-display" style="text-align:right; padding:12px; color:var(--tf-green);">{{ number_format($order->components->sum('total_cost'), 2) }} ج.م</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Additional Costs -->
        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-coins" style="color:var(--tf-amber);"></i>
                <h3 class="mfg-card-title">التكاليف الإضافية (للبالتة الواحدة)</h3>
            </div>
            <div class="mfg-card-body">
                <div style="display:grid; grid-template-columns: repeat(6, 1fr); gap:16px;">
                    <div>
                        <label class="form-label">الخسارة</label>
                        <input type="number" name="waste_cost" class="input-sm" value="{{ $order->waste_cost }}" step="any" min="0" oninput="recalculateAll()">
                    </div>
                    <div>
                        <label class="form-label">المصانعية</label>
                        <input type="number" name="labor_cost" class="input-sm" value="{{ $order->labor_cost }}" step="any" min="0" oninput="recalculateAll()">
                    </div>
                    <div>
                        <label class="form-label">مسمار</label>
                        <input type="number" name="nails_cost" class="input-sm" value="{{ $order->nails_cost }}" step="any" min="0" oninput="recalculateAll()">
                    </div>
                    <div>
                        <label class="form-label">اكرمية</label>
                        <input type="number" name="tips_cost" class="input-sm" value="{{ $order->tips_cost }}" step="any" min="0" oninput="recalculateAll()">
                    </div>
                    <div>
                        <label class="form-label">نقل</label>
                        <input type="number" name="transport_cost" class="input-sm" value="{{ $order->transport_cost }}" step="any" min="0" oninput="recalculateAll()">
                    </div>
                    <div>
                        <label class="form-label">تبخير</label>
                        <input type="number" name="fumigation_cost" class="input-sm" value="{{ $order->fumigation_cost }}" step="any" min="0" oninput="recalculateAll()">
                    </div>
                </div>
                <div style="margin-top:16px; text-align:left; font-weight:700; color:var(--tf-indigo);">
                    إجمالي التكاليف الإضافية: <span id="additional-total">{{ number_format($order->waste_cost + $order->labor_cost + $order->nails_cost + $order->tips_cost + $order->transport_cost + $order->fumigation_cost, 2) }}</span> ج.م
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div style="background: linear-gradient(135deg, var(--tf-indigo), #3b52c0); color: white; padding: 20px; border-radius: 16px; display: flex; flex-direction: column; gap: 12px; margin-bottom:24px;">
            <div style="display:flex; justify-content:space-between;">
                <span>تكلفة البالتة الواحدة (بدون ربح)</span>
                <span id="cost-per-unit-display">{{ number_format($order->cost_per_unit + 0, 2) }} ج.م</span>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span>نسبة الربح %</span>
                <span>
                    <input type="number" name="profit_margin" id="profit_margin" class="input-sm" value="{{ $order->profit_margin }}" step="any" min="0" style="width:80px; text-align:center;" oninput="recalculateAll()">%
                </span>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span>مبلغ الربح (للواحدة)</span>
                <span id="profit-per-unit">{{ number_format(($order->selling_price_per_unit - $order->cost_per_unit), 2) }} ج.م</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:18px; font-weight:900; border-top:1px solid rgba(255,255,255,0.2); padding-top:12px; margin-top:8px;">
                <span>سعر البيع للواحدة 🔥</span>
                <span id="selling-price-display" style="color:#ffdd57;">{{ number_format($order->selling_price_per_unit, 2) }} ج.م</span>
            </div>
            <input type="hidden" name="cost_per_unit" id="cost_per_unit_hidden" value="{{ $order->cost_per_unit }}">
            <input type="hidden" name="total_cost" id="total_cost_hidden" value="{{ $order->total_cost }}">
            <input type="hidden" name="selling_price_per_unit" id="selling_price_per_unit_hidden" value="{{ $order->selling_price_per_unit }}">
            <input type="hidden" name="profit_amount" id="profit_amount_hidden" value="{{ $order->profit_amount }}">
        </div>

        <!-- Actions -->
        <div style="display:flex; gap:12px; justify-content:flex-end;">
            <a href="{{ route('manufacturing-orders.show', $order->id) }}" class="btn btn-amber">
                <i class="fas fa-times"></i> إلغاء
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> حفظ التعديلات
            </button>
        </div>
    </form>
</div>

<script>
let componentIndex = {{ $order->components->count() }};

function recalculateComponent(input) {
    const row = input.closest('tr');
    const qty = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const thickness = parseFloat(row.querySelector('input[name*="[thickness_cm]"]').value) || 0;
    const width = parseFloat(row.querySelector('input[name*="[width_cm]"]').value) || 0;
    const length = parseFloat(row.querySelector('input[name*="[length_cm]"]').value) || 0;
    const pricePerCubicMeter = parseFloat(row.querySelector('input[name*="[price_per_cubic_meter]"]').value) || 0;

    const cubicCm = qty * thickness * width * length;
    const componentTotal = (cubicCm / 1000000) * pricePerCubicMeter;

    row.querySelector('.cubic-display').textContent = cubicCm.toLocaleString('en-US', {maximumFractionDigits: 0});
    row.querySelector('.component-total').textContent = componentTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    recalculateAll();
}

function getAdditionalTotal() {
    const fields = ['waste_cost','labor_cost','nails_cost','tips_cost','transport_cost','fumigation_cost'];
    return fields.reduce((sum, field) => sum + (parseFloat(document.querySelector(`[name="${field}"]`).value) || 0), 0);
}

function getComponentsTotal() {
    let total = 0;
    document.querySelectorAll('#components-body tr').forEach(row => {
        const valText = row.querySelector('.component-total')?.textContent || '0';
        const val = parseFloat(valText.replace(/,/g, '')) || 0;
        total += val;
    });
    return total;
}

function recalculateAll() {
    const componentsTotal = getComponentsTotal();
    const additionalTotal = getAdditionalTotal();
    const costPerUnit = componentsTotal + additionalTotal;
    const profitMargin = parseFloat(document.getElementById('profit_margin').value) || 0;
    const profitPerUnit = costPerUnit * (profitMargin / 100);
    const sellingPrice = costPerUnit + profitPerUnit;
    const quantityProduced = parseFloat(document.getElementById('quantity_produced').value) || 1;
    const totalCost = costPerUnit * quantityProduced;
    const totalProfit = profitPerUnit * quantityProduced;

    document.getElementById('components-total-display').textContent = componentsTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج.م';
    document.getElementById('additional-total').textContent = additionalTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج.م';
    document.getElementById('cost-per-unit-display').textContent = costPerUnit.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج.م';
    document.getElementById('profit-per-unit').textContent = profitPerUnit.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج.م';
    document.getElementById('selling-price-display').textContent = sellingPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج.م';

    document.getElementById('cost_per_unit_hidden').value = costPerUnit.toFixed(4);
    document.getElementById('total_cost_hidden').value = totalCost.toFixed(4);
    document.getElementById('selling_price_per_unit_hidden').value = sellingPrice.toFixed(4);
    document.getElementById('profit_amount_hidden').value = totalProfit.toFixed(4);
}

// Recalculate on load
document.addEventListener('DOMContentLoaded', recalculateAll);

function addComponent() {
    const container = document.getElementById('components-body');
    const row = document.createElement('tr');
    const types = ['فرش','روباط','شاسية','دكم'];
    row.innerHTML = `
        <td style="padding:8px;">
            <select name="components[${componentIndex}][component_type]" class="input-sm" required style="width:100%;">
                ${types.map(type => `<option value="${type}">${type}</option>`).join('')}
            </select>
        </td>
        <td style="padding:8px;"><input type="number" name="components[${componentIndex}][quantity]" class="input-sm" value="1" step="any" min="0.0001" required oninput="recalculateComponent(this)"></td>
        <td style="padding:8px;"><input type="number" name="components[${componentIndex}][thickness_cm]" class="input-sm" value="0" step="any" min="0" required oninput="recalculateComponent(this)"></td>
        <td style="padding:8px;"><input type="number" name="components[${componentIndex}][width_cm]" class="input-sm" value="0" step="any" min="0" required oninput="recalculateComponent(this)"></td>
        <td style="padding:8px;"><input type="number" name="components[${componentIndex}][length_cm]" class="input-sm" value="0" step="any" min="0" required oninput="recalculateComponent(this)"></td>
        <td style="padding:8px;"><span class="cubic-display">0</span> cm³</td>
        <td style="padding:8px;"><input type="number" name="components[${componentIndex}][price_per_cubic_meter]" class="input-sm" value="14000" step="any" min="0" required oninput="recalculateComponent(this)"></td>
        <td style="padding:8px;"><span class="component-total">0.00</span> ج.م</td>
        <td style="text-align:center; padding:8px;">
            <button type="button" class="btn btn-sm btn-red" onclick="removeRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    container.appendChild(row);
    componentIndex++;
    recalculateAll();
}

function removeRow(btn) {
    btn.closest('tr').remove();
    recalculateAll();
}
</script>
@endsection
