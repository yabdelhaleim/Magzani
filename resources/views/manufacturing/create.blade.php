@extends('layouts.app')

@section('title', 'حساب تكلفة تصنيع جديد')
@section('page-title', 'حساب تكلفة تصنيع جديد')

@push('styles')
<style>
    :root {
        --tf-bg:          #f4f7fe;
        --tf-surface:     #ffffff;
        --tf-border:      #e4eaf7;
        --tf-indigo:      #4f63d2;
        --tf-indigo-light:#7088e8;
        --tf-blue:        #3a8ef0;
        --tf-green:       #0faa7e;
        --tf-red:         #dc2626;
        --tf-amber:       #e8930a;
        --tf-violet:      #7c5cec;
        --tf-text-h:      #1a2140;
        --tf-text-b:      #3d4f72;
        --tf-text-m:      #7e90b0;
        --tf-shadow-card: 0 2px 0 0 rgba(0,0,0,0.04), 0 4px 20px rgba(79,99,210,0.08);
    }
    .mfg-page { background: var(--tf-bg); min-height: 100vh; padding: 26px 22px; }
    @keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    .mfg-section { animation: fadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .mfg-section:nth-child(1) { animation-delay: 0.04s; }
    .mfg-section:nth-child(2) { animation-delay: 0.12s; }
    .mfg-section:nth-child(3) { animation-delay: 0.20s; }
    .mfg-section:nth-child(4) { animation-delay: 0.28s; }

    .mfg-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px; }
    .mfg-title { font-size:24px; font-weight:900; color:var(--tf-text-h); display:flex; align-items:center; gap:12px; }
    .mfg-title i { color:var(--tf-indigo); }

    .mfg-card {
        background: var(--tf-surface); border-radius: 18px;
        border: 1px solid var(--tf-border); overflow: hidden; margin-bottom: 20px;
    }
    .mfg-card-top {
        padding: 16px 22px; border-bottom: 1px solid var(--tf-border);
        display: flex; align-items: center; gap: 10px;
    }
    .mfg-card-top .icon-wrap {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; font-size: 16px;
    }
    .mfg-card-top h3 { font-size: 16px; font-weight: 800; margin: 0; }
    .mfg-card-body { padding: 22px; }

    .icon-blue { background: #e8f2ff; color: var(--tf-blue); }
    .icon-green { background: #e6f8f3; color: var(--tf-green); }
    .icon-violet { background: #f0ecff; color: var(--tf-violet); }
    .icon-amber { background: #fff4e0; color: var(--tf-amber); }

    .mfg-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .mfg-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

    .mfg-field { display: flex; flex-direction: column; gap: 6px; }
    .mfg-field.full { grid-column: 1 / -1; }
    .mfg-label { font-size: 13px; font-weight: 700; color: var(--tf-text-b); }
    .mfg-input {
        padding: 10px 14px; border-radius: 10px; border: 1px solid var(--tf-border);
        font-size: 14px; color: var(--tf-text-h); font-family: 'Cairo', sans-serif;
        transition: border-color .2s, box-shadow .2s; width: 100%;
    }
    .mfg-input:focus { outline: none; border-color: var(--tf-indigo); box-shadow: 0 0 0 3px rgba(79,99,210,0.1); }

    .comp-table { width: 100%; border-collapse: collapse; }
    .comp-table th {
        padding: 10px 12px; font-size: 12px; font-weight: 700;
        color: var(--tf-text-m); background: #f8faff; text-align: right;
        border-bottom: 1px solid var(--tf-border);
    }
    .comp-table td { padding: 8px 6px; border-bottom: 1px solid #f0f4f8; }
    .comp-table input {
        width: 100%; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--tf-border);
        font-size: 13px; font-family: 'Cairo', sans-serif; color: var(--tf-text-h);
    }
    .comp-table input:focus { outline: none; border-color: var(--tf-indigo); }
    .comp-vol { font-weight: 800; font-size: 13px; color: var(--tf-indigo); white-space: nowrap; }

    .btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 20px; border-radius: 12px; font-weight: 700;
        font-size: 14px; border: none; cursor: pointer;
        transition: all .3s; text-decoration: none;
    }
    .btn-primary { background: var(--tf-indigo); color: #fff; }
    .btn-primary:hover { background: #3b52c0; box-shadow: 0 4px 16px rgba(79,99,210,0.3); }
    .btn-secondary { background: #f0f4f8; color: var(--tf-text-b); }
    .btn-secondary:hover { background: #e4eaf7; }
    .btn-green { background: var(--tf-green); color: #fff; }
    .btn-green:hover { background: #0d946d; }
    .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 8px; }
    .btn-add {
        background: #eef0fc; color: var(--tf-indigo); border: 1px dashed var(--tf-indigo-light);
        padding: 10px 18px; border-radius: 10px; font-weight: 700; font-size: 13px;
        cursor: pointer; transition: all .2s; width: 100%; text-align: center;
    }
    .btn-add:hover { background: #e0e4fa; }
    .btn-remove { background: #fee2e2; color: #dc2626; padding: 6px 10px; border-radius: 8px; border: none; cursor: pointer; font-size: 12px; }

    .summary-panel {
        background: linear-gradient(135deg, #f8faff, #eef0fc);
        border-radius: 14px; padding: 20px; border: 1px solid var(--tf-border);
    }
    .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; }
    .summary-row + .summary-row { border-top: 1px dashed #e4eaf7; }
    .summary-label { font-size: 14px; font-weight: 600; color: var(--tf-text-b); }
    .summary-value { font-size: 15px; font-weight: 800; color: var(--tf-text-h); }
    .summary-total { background: var(--tf-indigo); color: #fff; border-radius: 12px; padding: 14px 20px; margin-top: 12px; }
    .summary-total .summary-label { color: rgba(255,255,255,0.8); }
    .summary-total .summary-value { color: #fff; font-size: 22px; }

    .mfg-footer { display: flex; justify-content: space-between; gap: 12px; margin-top: 24px; }

    .vol-total-bar {
        background: #f0f4f8; border-radius: 10px; padding: 12px 18px;
        display: flex; justify-content: space-between; align-items: center;
        margin-top: 12px; font-weight: 700; font-size: 14px; color: var(--tf-text-b);
    }
    .vol-total-bar span { color: var(--tf-indigo); font-size: 16px; }
</style>
@endpush

@section('content')
<div class="mfg-page" x-data="manufacturingForm()">
    <form method="POST" action="{{ route('manufacturing.store') }}" @submit.prevent="submitForm">
        @csrf

        <div class="mfg-section">
            <div class="mfg-header">
                <h1 class="mfg-title">
                    <i class="fas fa-industry"></i>
                    حساب تكلفة تصنيع جديد
                </h1>
                <div style="display:flex; gap:8px;">
                    <a href="{{ route('manufacturing.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> رجوع
                    </a>
                </div>
            </div>
        </div>

        @if($errors->any())
        <div style="background:#fee2e2; color:#dc2626; padding:14px 20px; border-radius:12px; margin-bottom:20px; font-size:14px;">
            <strong>خطأ في البيانات:</strong>
            <ul style="margin:6px 0 0 16px; font-size:13px;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Card 1: Product Info -->
        <div class="mfg-section">
            <div class="mfg-card">
                <div class="mfg-card-top">
                    <div class="icon-wrap icon-blue"><i class="fas fa-box"></i></div>
                    <h3>معلومات المنتج</h3>
                </div>
                <div class="mfg-card-body">
                    <div class="mfg-grid">
                        <div class="mfg-field full">
                            <label class="mfg-label">اسم المنتج</label>
                            <input type="text" name="product_name" class="mfg-input"
                                   x-model="form.product_name" placeholder="مثال: باليت خشبي" required>
                        </div>
                        <div class="mfg-field">
                            <label class="mfg-label">سعر المتر المكعب (ج.م)</label>
                            <input type="number" name="price_per_cubic_meter" class="mfg-input"
                                   x-model="form.price_per_cubic_meter" step="0.01" min="0"
                                   @input="calculate()" placeholder="0.00" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: BOM Components -->
        <div class="mfg-section">
            <div class="mfg-card">
                <div class="mfg-card-top">
                    <div class="icon-wrap icon-green"><i class="fas fa-cubes"></i></div>
                    <h3>قطع المنتج (BOM)</h3>
                </div>
                <div class="mfg-card-body">
                    <table class="comp-table">
                        <thead>
                            <tr>
                                <th style="width:180px;">اسم القطعة</th>
                                <th style="width:80px;">الكمية</th>
                                <th style="width:100px;">الطول (سم)</th>
                                <th style="width:100px;">العرض (سم)</th>
                                <th style="width:100px;">السمك (سم)</th>
                                <th style="width:120px;">الحجم (سم³)</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(comp, index) in form.components" :key="index">
                                <tr>
                                    <td>
                                        <input type="text" :name="'components[' + index + '][component_name]'"
                                               x-model="comp.component_name" @input="calculate()" placeholder="اسم القطعة" required>
                                    </td>
                                    <td>
                                        <input type="number" :name="'components[' + index + '][quantity]'"
                                               x-model="comp.quantity" @input="calculate()" min="0.01" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" :name="'components[' + index + '][length_cm]'"
                                               x-model="comp.length_cm" @input="calculate()" min="0.01" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" :name="'components[' + index + '][width_cm]'"
                                               x-model="comp.width_cm" @input="calculate()" min="0.01" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" :name="'components[' + index + '][thickness_cm]'"
                                               x-model="comp.thickness_cm" @input="calculate()" min="0.01" step="0.01" required>
                                    </td>
                                    <td class="comp-vol" x-text="formatNumber(compVolume(comp))"></td>
                                    <td>
                                        <button type="button" class="btn-remove" @click="removeComponent(index)" x-show="form.components.length > 1">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <button type="button" class="btn-add" @click="addComponent()" style="margin-top:12px;">
                        <i class="fas fa-plus"></i> إضافة قطعة جديدة
                    </button>

                    <div class="vol-total-bar">
                        إجمالي الحجم: <span x-text="formatNumber(results.total_volume_cm3) + ' سم³'"></span>
                        &nbsp;|&nbsp;
                        بالأمتار المكعبة: <span x-text="formatNumber(results.total_volume_m3, 6) + ' م³'"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Additional Costs -->
        <div class="mfg-section">
            <div class="mfg-card">
                <div class="mfg-card-top">
                    <div class="icon-wrap icon-violet"><i class="fas fa-coins"></i></div>
                    <h3>التكاليف الإضافية</h3>
                </div>
                <div class="mfg-card-body">
                    <div class="mfg-grid">
                        <div class="mfg-field">
                            <label class="mfg-label">تكلفة العمالة</label>
                            <input type="number" name="labor_cost" class="mfg-input"
                                   x-model="form.labor_cost" @input="calculate()" step="0.01" min="0" value="0">
                        </div>
                        <div class="mfg-field">
                            <label class="mfg-label">المسامير والعتاد</label>
                            <input type="number" name="nails_hardware_cost" class="mfg-input"
                                   x-model="form.nails_hardware_cost" @input="calculate()" step="0.01" min="0" value="0">
                        </div>
                        <div class="mfg-field">
                            <label class="mfg-label">تكلفة النقل</label>
                            <input type="number" name="transportation_cost" class="mfg-input"
                                   x-model="form.transportation_cost" @input="calculate()" step="0.01" min="0" value="0">
                        </div>
                        <div class="mfg-field">
                            <label class="mfg-label">تكلفة التطهير</label>
                            <input type="number" name="fumigation_cost" class="mfg-input"
                                   x-model="form.fumigation_cost" @input="calculate()" step="0.01" min="0" value="0">
                        </div>
                        <div class="mfg-field">
                            <label class="mfg-label">إكراميات ومتنوعة</label>
                            <input type="number" name="tips_misc_cost" class="mfg-input"
                                   x-model="form.tips_misc_cost" @input="calculate()" step="0.01" min="0" value="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Pricing & Summary -->
        <div class="mfg-section">
            <div class="mfg-card">
                <div class="mfg-card-top">
                    <div class="icon-wrap icon-amber"><i class="fas fa-calculator"></i></div>
                    <h3>التسعير والربح</h3>
                </div>
                <div class="mfg-card-body">
                    <div class="mfg-grid" style="margin-bottom:20px;">
                        <div class="mfg-field">
                            <label class="mfg-label">نسبة الربح (%)</label>
                            <input type="number" name="profit_percentage" class="mfg-input"
                                   x-model="form.profit_percentage" @input="calculate()" step="0.01" min="0" max="100" required>
                        </div>
                        <div class="mfg-field">
                            <label class="mfg-label">ملاحظات</label>
                            <input type="text" name="notes" class="mfg-input" placeholder="ملاحظات اختيارية...">
                        </div>
                    </div>

                    <div class="summary-panel">
                        <div class="summary-row">
                            <span class="summary-label">تكلفة الخامات (الحجم × السعر)</span>
                            <span class="summary-value" x-text="formatNumber(results.material_cost) + ' ج.م'"></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">إجمالي التكاليف الإضافية</span>
                            <span class="summary-value" x-text="formatNumber(results.additional_costs_total) + ' ج.م'"></span>
                        </div>
                        <div class="summary-row" style="border-top: 2px solid var(--tf-border); padding-top:12px;">
                            <span class="summary-label" style="font-weight:800;">التكلفة الإجمالية</span>
                            <span class="summary-value" style="color:var(--tf-amber);" x-text="formatNumber(results.total_cost) + ' ج.م'"></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">الربح (<span x-text="form.profit_percentage"></span>%)</span>
                            <span class="summary-value" style="color:var(--tf-green);" x-text="formatNumber(results.profit_amount) + ' ج.م'"></span>
                        </div>
                        <div class="summary-total">
                            <div class="summary-row" style="border:none;">
                                <span class="summary-label" style="font-size:16px;">السعر النهائي للبيع</span>
                                <span class="summary-value" x-text="formatNumber(results.final_price) + ' ج.م'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mfg-footer">
            <a href="{{ route('manufacturing.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> إلغاء
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> حفظ كمسودة
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function manufacturingForm() {
    return {
        form: {
            product_name: '',
            price_per_cubic_meter: 0,
            labor_cost: 0,
            nails_hardware_cost: 0,
            transportation_cost: 0,
            tips_misc_cost: 0,
            fumigation_cost: 0,
            profit_percentage: 25,
            components: [
                { component_name: '', quantity: 1, length_cm: 0, width_cm: 0, thickness_cm: 0 }
            ]
        },
        results: {
            total_volume_cm3: 0,
            total_volume_m3: 0,
            material_cost: 0,
            additional_costs_total: 0,
            total_cost: 0,
            profit_amount: 0,
            final_price: 0
        },

        addComponent() {
            this.form.components.push({ component_name: '', quantity: 1, length_cm: 0, width_cm: 0, thickness_cm: 0 });
        },

        removeComponent(index) {
            if (this.form.components.length > 1) {
                this.form.components.splice(index, 1);
                this.calculate();
            }
        },

        compVolume(comp) {
            return (parseFloat(comp.length_cm) || 0) * (parseFloat(comp.width_cm) || 0) * (parseFloat(comp.thickness_cm) || 0) * (parseFloat(comp.quantity) || 0);
        },

        calculate() {
            let totalVolCm3 = 0;
            this.form.components.forEach(c => {
                totalVolCm3 += this.compVolume(c);
            });
            const totalVolM3 = totalVolCm3 / 1000000;
            const pricePerM3 = parseFloat(this.form.price_per_cubic_meter) || 0;
            const materialCost = totalVolM3 * pricePerM3;
            const additionalTotal =
                (parseFloat(this.form.labor_cost) || 0) +
                (parseFloat(this.form.nails_hardware_cost) || 0) +
                (parseFloat(this.form.transportation_cost) || 0) +
                (parseFloat(this.form.tips_misc_cost) || 0) +
                (parseFloat(this.form.fumigation_cost) || 0);
            const totalCost = materialCost + additionalTotal;
            const profitPct = parseFloat(this.form.profit_percentage) || 0;
            const profitAmount = totalCost * (profitPct / 100);
            const finalPrice = totalCost + profitAmount;

            this.results = {
                total_volume_cm3: totalVolCm3,
                total_volume_m3: totalVolM3,
                material_cost: materialCost,
                additional_costs_total: additionalTotal,
                total_cost: totalCost,
                profit_amount: profitAmount,
                final_price: finalPrice
            };
        },

        formatNumber(val, decimals = 2) {
            if (val === undefined || val === null || isNaN(val)) return '0';
            return parseFloat(val).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },

        submitForm(e) {
            e.target.submit();
        },

        init() {
            this.calculate();
        }
    }
}
</script>
@endpush
@endsection
