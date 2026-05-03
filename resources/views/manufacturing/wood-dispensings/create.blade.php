@extends('layouts.app')

@section('title', 'صرف خشب')

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

    .tf-info-grid {
        display: grid;
        gap: 20px;
        margin-bottom: 20px;
    }

    @media (min-width: 640px) {
        .tf-info-grid { grid-template-columns: repeat(2, 1fr); }
    }

    .tf-info-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid var(--tf-border);
    }

    .tf-info-item:last-child {
        border-bottom: none;
    }

    .tf-info-label {
        font-size: 13px;
        color: var(--tf-text-m);
        font-weight: 600;
    }

    .tf-info-value {
        font-size: 14px;
        color: var(--tf-text-h);
        font-weight: 700;
    }

    .tf-form-grid {
        display: grid;
        gap: 16px;
    }

    @media (min-width: 640px) {
        .tf-form-grid { grid-template-columns: repeat(2, 1fr); }
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

    .tf-alert {
        background: #dbeafe;
        border: 1px solid #3b82f6;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        display: flex;
        gap: 12px;
        align-items: start;
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
<div class="tf-page" x-data="dispensingForm()">
    {{-- Header --}}
    <div class="tf-header">
        <div>
            <h1 class="tf-title">
                <i class="fas fa-dolly" style="color: var(--tf-amber);"></i>
                صرف خشب من دفعة #{{ $woodStock->id }}
            </h1>
            <p style="color: var(--tf-text-m); margin-top: 4px; font-size: 14px;">
                من دفعة: {{ $woodStock->purchase_reference ?? 'غير محدد' }}
            </p>
        </div>
        <a href="{{ route('manufacturing.wood-dispensings.index') }}" class="tf-btn tf-btn-secondary">
            <i class="fas fa-arrow-left"></i>
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

    {{-- Stock Info --}}
    <div class="tf-card">
        <div class="tf-card-head">
            <i class="fas fa-info-circle"></i>
            معلومات الدفعة
        </div>
        <div class="tf-card-body">
            <div class="tf-info-grid">
                <div class="tf-info-item">
                    <span class="tf-info-label">المورد:</span>
                    <span class="tf-info-value">{{ $woodStock->supplier->name ?? 'غير محدد' }}</span>
                </div>
                <div class="tf-info-item">
                    <span class="tf-info-label">الأبعاد:</span>
                    <span class="tf-info-value" dir="ltr">
                        {{ $woodStock->length_cm }} × {{ $woodStock->width_cm }} × {{ $woodStock->thickness_cm }} سم
                    </span>
                </div>
                <div class="tf-info-item">
                    <span class="tf-info-label">إجمالي الحجم:</span>
                    <span class="tf-info-value">{{ number_format($woodStock->volume_m3_total, 4) }} م³</span>
                </div>
                <div class="tf-info-item">
                    <span class="tf-info-label">المتاح:</span>
                    <span class="tf-info-value" style="color: var(--tf-green); font-weight: 900;">
                        {{ number_format($woodStock->remaining_m3, 4) }} م³
                    </span>
                </div>
                <div class="tf-info-item">
                    <span class="tf-info-label">سعر المتر المكعب:</span>
                    <span class="tf-info-value">{{ number_format($woodStock->unit_cost, 2) }} ج.م</span>
                </div>
                <div class="tf-info-item">
                    <span class="tf-info-label">تاريخ الاستلام:</span>
                    <span class="tf-info-value">{{ $woodStock->received_at->format('Y-m-d') }}</span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('manufacturing.wood-dispensings.store') }}" method="POST">
        @csrf
        <input type="hidden" name="wood_stock_id" value="{{ $woodStock->id }}">

        {{-- Dispensing Form --}}
        <div class="tf-card">
            <div class="tf-card-head" style="background: linear-gradient(135deg, var(--tf-amber), #f59e0b);">
                <i class="fas fa-dolly"></i>
                بيانات الصرف
            </div>
            <div class="tf-card-body">
                <div class="tf-alert">
                    <i class="fas fa-info-circle" style="margin-top: 2px;"></i>
                    <div>
                        <strong>متاح للصرف:</strong> {{ number_format($woodStock->remaining_m3, 4) }} م³
                        ({{ number_format($woodStock->remaining_m2, 2) }} م²)
                    </div>
                </div>

                <div class="tf-form-grid">
                    <div class="tf-field">
                        <label class="tf-label">الكمية المراد صرفها (م³) <span style="color: var(--tf-red);">*</span></label>
                        <input type="number"
                               name="volume_cm3_taken"
                               x-model="form.taken_m3"
                               @input="checkStock"
                               class="tf-input"
                               step="0.0001"
                               min="0.0001"
                               max="{{ $woodStock->remaining_m3 }}"
                               required
                               placeholder="أدخل الكمية بالمتر المكعب">
                        <small style="color: var(--tf-text-m); font-size: 12px; margin-top: 4px; display: block;">
                            الحد الأقصى المتاح: {{ number_format($woodStock->remaining_m3, 4) }} م³
                        </small>
                    </div>
                    <div class="tf-field">
                        <label class="tf-label">التاريخ <span style="color: var(--tf-red);">*</span></label>
                        <input type="date" name="dispensed_at" class="tf-input" value="{{ old('dispensed_at', date('Y-m-d')) }}" required>
                    </div>
                    <div class="tf-field">
                        <label class="tf-label">العميل</label>
                        <select name="client_id" class="tf-input">
                            <option value="">بدون عميل (للإنتاج)</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="tf-field">
                        <label class="tf-label">أمر التصنيع</label>
                        <select name="manufacturing_order_id" class="tf-input">
                            <option value="">بدون (صرف حر)</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}">
                                    {{ $order->order_number }} - {{ $order->product_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="margin-top: 16px;">
                    <div class="tf-field">
                        <label class="tf-label">ملاحظات</label>
                        <textarea name="notes" class="tf-input" rows="3" placeholder="أي ملاحظات حول عملية الصرف...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="tf-footer">
            <button type="submit" class="tf-btn tf-btn-primary" style="flex: 1;" :disabled="!isValid">
                <i class="fas fa-check"></i>
                تأكيد الصرف
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function dispensingForm() {
    return {
        remaining_m3: {{ $woodStock->remaining_m3 }},
        form: {
            taken_m3: {{ old('volume_cm3_taken', 0) }}
        },
        get isValid() {
            return this.form.taken_m3 > 0 && this.form.taken_m3 <= this.remaining_m3;
        },
        checkStock() {
            if (this.form.taken_m3 > this.remaining_m3) {
                alert('الكمية المطلوبة تتجاوز المتاح في الدفعة!');
                this.form.taken_m3 = this.remaining_m3;
            }
        }
    }
}
</script>
@endpush
@endsection
