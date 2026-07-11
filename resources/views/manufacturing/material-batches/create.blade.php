@extends('layouts.app')

@section('title', 'تسجيل دفعة مواد خام')

@section('content')
<div class="mfg-page mfg-section" style="max-width: 800px; margin: 0 auto;">
    <div class="mfg-header">
        <h1 class="mfg-title">
            <i class="fas fa-boxes-packing"></i>
            تسجيل دفعة مواد خام جديدة
        </h1>
        <a href="{{ route('material-batches.index') }}" class="btn btn-outline" style="border: 1px solid var(--tf-border); padding: 10px 18px; border-radius: 8px; font-weight: bold; color: var(--tf-text-b); text-decoration: none;">
            <i class="fas fa-arrow-right"></i> عودة للقائمة
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="background: #fdf2f2; color: #dc2626; border: 1px solid #fde2e2; padding: 14px 16px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-right: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mfg-card" style="padding: 24px;">
        <form action="{{ route('material-batches.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="form-label" style="display: block; font-weight: bold; margin-bottom: 6px; color: var(--tf-text-h);">المادة الخام (المنتج)</label>
                    <select name="product_id" class="tf-input" style="width: 100%; padding: 10px; border: 1px solid var(--tf-border); border-radius: 8px; color: var(--tf-text-b);" required>
                        <option value="">اختر المادة الخام...</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" {{ old('product_id') == $prod->id ? 'selected' : '' }}>
                                {{ $prod->name }} ({{ $prod->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label" style="display: block; font-weight: bold; margin-bottom: 6px; color: var(--tf-text-h);">وحدة القياس الأساسية</label>
                    <select name="uom_id" class="tf-input" style="width: 100%; padding: 10px; border: 1px solid var(--tf-border); border-radius: 8px; color: var(--tf-text-b);" required>
                        <option value="">اختر وحدة القياس...</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('uom_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }} ({{ $unit->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="form-label" style="display: block; font-weight: bold; margin-bottom: 6px; color: var(--tf-text-h);">الكمية المستلمة</label>
                    <input type="number" name="quantity" step="0.0001" class="tf-input" style="width: 100%; padding: 10px; border: 1px solid var(--tf-border); border-radius: 8px;" value="{{ old('quantity') }}" required placeholder="أدخل الكمية">
                </div>

                <div>
                    <label class="form-label" style="display: block; font-weight: bold; margin-bottom: 6px; color: var(--tf-text-h);">تكلفة الوحدة (د.إ)</label>
                    <input type="number" name="unit_cost" step="0.01" class="tf-input" style="width: 100%; padding: 10px; border: 1px solid var(--tf-border); border-radius: 8px;" value="{{ old('unit_cost') }}" required placeholder="أدخل تكلفة الوحدة">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="form-label" style="display: block; font-weight: bold; margin-bottom: 6px; color: var(--tf-text-h);">المخزن المستلم</label>
                    <select name="warehouse_id" class="tf-input" style="width: 100%; padding: 10px; border: 1px solid var(--tf-border); border-radius: 8px; color: var(--tf-text-b);" required>
                        <option value="">اختر المخزن...</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label" style="display: block; font-weight: bold; margin-bottom: 6px; color: var(--tf-text-h);">المورد</label>
                    <select name="supplier_id" class="tf-input" style="width: 100%; padding: 10px; border: 1px solid var(--tf-border); border-radius: 8px; color: var(--tf-text-b);">
                        <option value="">اختر المورد (اختياري)...</option>
                        @foreach($suppliers as $supp)
                            <option value="{{ $supp->id }}" {{ old('supplier_id') == $supp->id ? 'selected' : '' }}>
                                {{ $supp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label class="form-label" style="display: block; font-weight: bold; margin-bottom: 6px; color: var(--tf-text-h);">مرجع الشراء / رقم الفاتورة</label>
                    <input type="text" name="purchase_reference" class="tf-input" style="width: 100%; padding: 10px; border: 1px solid var(--tf-border); border-radius: 8px;" value="{{ old('purchase_reference') }}" placeholder="رقم الفاتورة أو إذن الاستلام">
                </div>

                <div>
                    <label class="form-label" style="display: block; font-weight: bold; margin-bottom: 6px; color: var(--tf-text-h);">تاريخ الاستلام</label>
                    <input type="date" name="received_at" class="tf-input" style="width: 100%; padding: 10px; border: 1px solid var(--tf-border); border-radius: 8px;" value="{{ old('received_at', date('Y-m-d')) }}" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; background: var(--tf-indigo); border: none; padding: 12px; border-radius: 8px; font-weight: bold; color: white; font-size: 16px;">
                <i class="fas fa-save"></i> حفظ دفعة المواد الخام
            </button>
        </form>
    </div>
</div>
@endsection
