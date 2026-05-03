@extends('layouts.app')

@section('title', 'إضافة دفعة خشب')

@push('styles')
<style>
    :root {
        --tf-bg: #f4f7fe;
        --tf-surface: #ffffff;
        --tf-border: #e4eaf7;
        --tf-indigo: #4f63d2;
        --tf-blue: #3a8ef0;
        --tf-green: #0faa7e;
        --tf-amber: #e8930a;
        --tf-text-h: #1a2140;
        --tf-text-b: #3d4f72;
        --tf-text-m: #7e90b0;
    }

    .tf-page {
        background: var(--tf-bg);
        background-image:
            radial-gradient(ellipse 80% 60% at 10% -10%,  rgba(79,99,210,0.12) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(58,142,240,0.1) 0%, transparent 50%);
        min-height: 100vh;
        padding: 26px 22px;
        padding-bottom: 100px;
    }

    @media (min-width: 768px) {
        .tf-page { padding-bottom: 26px; }
    }

    .tf-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .tf-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--tf-text-h);
    }

    .tf-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 22px;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.25s;
        font-size: 13.5px;
    }

    .tf-btn-secondary {
        background: #6b7280;
        color: #fff;
    }

    .tf-btn-secondary:hover {
        background: #4b5563;
        color: #fff;
    }

    .tf-btn-primary {
        background: linear-gradient(135deg, #6366f1, #3b82f6);
        color: #fff;
        box-shadow: 0 4px 18px rgba(99,102,241,0.35);
    }

    .tf-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(99,102,241,0.45);
        color: #fff;
    }

    .tf-card {
        background: var(--tf-surface);
        border-radius: 18px;
        border: 1px solid var(--tf-border);
        box-shadow: 0 2px 12px rgba(79,99,210,0.07);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .tf-card-head {
        padding: 20px;
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        color: white;
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .tf-card-body {
        padding: 20px;
    }

    .tf-form-grid {
        display: grid;
        gap: 16px;
    }

    @media (min-width: 640px) {
        .tf-form-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (min-width: 1024px) {
        .tf-form-grid { grid-template-columns: repeat(3, 1fr); }
    }

    .tf-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .tf-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--tf-text-h);
    }

    .tf-input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--tf-border);
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .tf-input:focus {
        outline: none;
        border-color: var(--tf-indigo);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }

    .tf-summary {
        background: #f8faff;
        border: 1px solid var(--tf-border);
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
    }

    .tf-summary-grid {
        display: grid;
        gap: 16px;
    }

    @media (min-width: 640px) {
        .tf-summary-grid { grid-template-columns: repeat(3, 1fr); }
    }

    .tf-summary-item {
        text-align: center;
    }

    .tf-summary-label {
        font-size: 11px;
        color: var(--tf-text-m);
        margin-bottom: 6px;
        font-weight: 600;
    }

    .tf-summary-value {
        font-size: 18px;
        font-weight: 900;
        color: var(--tf-indigo);
    }

    .tf-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 12px 16px;
        border-top: 1px solid var(--tf-border);
        box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
        z-index: 100;
        display: flex;
        gap: 10px;
    }

    @media (min-width: 768px) {
        .tf-footer {
            position: static;
            background: transparent;
            padding: 0;
            border: none;
            box-shadow: none;
            margin-top: 20px;
        }
    }
</style>
@endpush

@section('content')
<div class="tf-page" x-data="woodStockForm()">
    {{-- Header --}}
    <div class="tf-header">
        <div>
            <h1 class="tf-title">
                <i class="fas fa-plus-circle" style="color: var(--tf-indigo);"></i>
                إضافة دفعة خشب خام
            </h1>
            <p style="color: var(--tf-text-m); margin-top: 4px; font-size: 14px;">تسجيل دفعة خشب جديدة في المخزون</p>
        </div>
        <a href="{{ route('manufacturing.wood-stocks.index') }}" class="tf-btn tf-btn-secondary">
            <i class="fas fa-arrow-right"></i>
            عودة
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <ul style="margin: 0; padding-right: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('manufacturing.wood-stocks.store') }}" method="POST">
        @csrf

        {{-- Basic Info --}}
        <div class="tf-card">
            <div class="tf-card-head">
                <i class="fas fa-info-circle"></i>
                بيانات الدفعة
            </div>
            <div class="tf-card-body">
                <div class="tf-form-grid">
                    <div class="tf-field">
                        <label class="tf-label">المورد</label>
                        <select name="supplier_id" class="tf-input">
                            <option value="">بدون مورد (رصيد افتتاحي)</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="tf-field">
                        <label class="tf-label">المخزن</label>
                        <select name="warehouse_id" class="tf-input">
                            <option value="">اختر المخزن</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="tf-field">
                        <label class="tf-label">رقم المحضر</label>
                        <input type="text" name="purchase_reference" class="tf-input" value="{{ old('purchase_reference') }}">
                    </div>
                    <div class="tf-field">
                        <label class="tf-label">تاريخ الاستلام <span style="color: var(--tf-red);">*</span></label>
                        <input type="date" name="received_at" class="tf-input" value="{{ old('received_at', date('Y-m-d')) }}" required>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dimensions & Cost --}}
        <div class="tf-card">
            <div class="tf-card-head" style="background: linear-gradient(135deg, var(--tf-amber), #f59e0b);">
                <i class="fas fa-ruler-combined"></i>
                الأبعاد والتكلفة
            </div>
            <div class="tf-card-body">
                <div class="tf-form-grid">
                    <div class="tf-field">
                        <label class="tf-label">طول cm <span style="color: var(--tf-red);">*</span></label>
                        <input type="number" name="length_cm" x-model="form.length" @input="calculate" class="tf-input" step="0.01" required>
                    </div>
                    <div class="tf-field">
                        <label class="tf-label">عرض cm <span style="color: var(--tf-red);">*</span></label>
                        <input type="number" name="width_cm" x-model="form.width" @input="calculate" class="tf-input" step="0.01" required>
                    </div>
                    <div class="tf-field">
                        <label class="tf-label">سماكة cm <span style="color: var(--tf-red);">*</span></label>
                        <input type="number" name="thickness_cm" x-model="form.thickness" @input="calculate" class="tf-input" step="0.01" required>
                    </div>
                    <div class="tf-field">
                        <label class="tf-label">عدد الألواح <span style="color: var(--tf-red);">*</span></label>
                        <input type="number" name="quantity" x-model="form.quantity" @input="calculate" class="tf-input" step="1" min="1" required>
                    </div>
                    <div class="tf-field">
                        <label class="tf-label">سعر الـ m³</label>
                        <input type="number" name="unit_cost" x-model="form.price_per_m3" @input="calculate" class="tf-input" step="0.01">
                    </div>
                    <div class="tf-field">
                        <label class="tf-label">ملاحظات</label>
                        <input type="text" name="notes" class="tf-input" value="{{ old('notes') }}">
                    </div>
                </div>

                {{-- Summary --}}
                <div class="tf-summary">
                    <h4 style="margin: 0 0 16px 0; color: var(--tf-text-h);">ملخص الحسابات</h4>
                    <div class="tf-summary-grid">
                        <div class="tf-summary-item">
                            <div class="tf-summary-label">الحجم الكلي (m³)</div>
                            <div class="tf-summary-value" x-text="results.m3 + ' m³'"></div>
                        </div>
                        <div class="tf-summary-item">
                            <div class="tf-summary-label">المساحة الكلية (m²)</div>
                            <div class="tf-summary-value" x-text="results.m2 + ' m²'"></div>
                        </div>
                        <div class="tf-summary-item">
                            <div class="tf-summary-label">التكلفة الإجمالية</div>
                            <div class="tf-summary-value" x-text="formatNumber(results.total_cost)"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="tf-footer">
            <button type="submit" class="tf-btn tf-btn-primary" style="flex: 1;">
                <i class="fas fa-save"></i>
                حفظ الدفعة
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function woodStockForm() {
    return {
        form: {
            length: {{ old('length_cm', 0) }},
            width: {{ old('width_cm', 0) }},
            thickness: {{ old('thickness_cm', 0) }},
            quantity: {{ old('quantity', 1) }},
            price_per_m3: {{ old('unit_cost', 0) }}
        },
        results: {
            m3: 0,
            m2: 0,
            total_cost: 0
        },
        calculate() {
            const l = parseFloat(this.form.length) || 0;
            const w = parseFloat(this.form.width) || 0;
            const t = parseFloat(this.form.thickness) || 0;
            const q = parseInt(this.form.quantity) || 0;

            const cm3 = l * w * t * q;
            this.results.m3 = (cm3 / 1000000).toFixed(4);
            this.results.m2 = t > 0 ? (cm3 / t / 10000).toFixed(4) : 0;

            const price = parseFloat(this.form.price_per_m3) || 0;
            this.results.total_cost = (this.results.m3 * price).toFixed(2);
        },
        formatNumber(val) {
            return parseFloat(val).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        },
        init() {
            this.calculate();
        }
    }
}
</script>
@endpush
@endsection
