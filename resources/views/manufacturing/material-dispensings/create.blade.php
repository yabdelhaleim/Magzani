@extends('layouts.app')

@section('title', 'صرف كمية مواد خام')

@section('content')
<div class="mfg-page mfg-section" style="max-width: 800px; margin: 0 auto;">
    <div class="mfg-header">
        <h1 class="mfg-title">
            <i class="fas fa-sign-out-alt"></i>
            صرف كمية من دفعة: {{ $batch->product->name }}
        </h1>
        <a href="{{ route('material-batches.index') }}" class="btn btn-outline" style="border: 1px solid var(--tf-border); padding: 10px 18px; border-radius: 8px; font-weight: bold; color: var(--tf-text-b); text-decoration: none;">
            <i class="fas fa-arrow-right"></i> عودة
        </a>
    </div>

    <div style="background: #fafbfe; border: 1px solid var(--tf-border); border-radius: 12px; padding: 16px; margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
        <div>
            <span style="color: var(--tf-text-m); font-size: 13px; display: block;">الرصيد المتبقي بالدفعة:</span>
            <span style="font-size: 18px; font-weight: bold; color: var(--tf-text-h);">{{ number_format($batch->remaining_qty, 2) }} {{ $batch->uom->name }}</span>
        </div>
        <div>
            <span style="color: var(--tf-text-m); font-size: 13px; display: block;">سعر التكلفة للوحدة:</span>
            <span style="font-size: 18px; font-weight: bold; color: var(--tf-text-h);">{{ number_format($batch->unit_cost, 2) }} د.إ</span>
        </div>
        <div>
            <span style="color: var(--tf-text-m); font-size: 13px; display: block;">المخزن الحالي:</span>
            <span style="font-size: 18px; font-weight: bold; color: var(--tf-text-h);">{{ $batch->warehouse->name }}</span>
        </div>
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
        <form action="{{ route('material-dispensings.store') }}" method="POST">
            @csrf
            <input type="hidden" name="material_batch_id" value="{{ $batch->id }}">

            <div style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; font-weight: bold; margin-bottom: 6px; color: var(--tf-text-h);">الكمية المراد سحبها / صرفها</label>
                <input type="number" name="quantity_taken" step="0.0001" max="{{ $batch->remaining_qty }}" class="tf-input" style="width: 100%; padding: 10px; border: 1px solid var(--tf-border); border-radius: 8px;" value="{{ old('quantity_taken') }}" required placeholder="أدخل الكمية المراد صرفها">
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; font-weight: bold; margin-bottom: 6px; color: var(--tf-text-h);">ربطها بأمر تصنيع (اختياري)</label>
                <select name="manufacturing_order_id" class="tf-input" style="width: 100%; padding: 10px; border: 1px solid var(--tf-border); border-radius: 8px; color: var(--tf-text-b);">
                    <option value="">صرف عام مباشر (لا يرتبط بأمر تصنيع)...</option>
                    @foreach($orders as $order)
                        <option value="{{ $order->id }}" {{ old('manufacturing_order_id') == $order->id ? 'selected' : '' }}>
                            أمر تصنيع: {{ $order->order_number }} - {{ $order->product_name }} (إنتاج {{ $order->quantity_produced }} وحدة)
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; font-weight: bold; margin-bottom: 6px; color: var(--tf-text-h);">تاريخ الصرف</label>
                <input type="date" name="dispensed_at" class="tf-input" style="width: 100%; padding: 10px; border: 1px solid var(--tf-border); border-radius: 8px;" value="{{ old('dispensed_at', date('Y-m-d')) }}" required>
            </div>

            <div style="margin-bottom: 24px;">
                <label class="form-label" style="display: block; font-weight: bold; margin-bottom: 6px; color: var(--tf-text-h);">ملاحظات الصرف</label>
                <textarea name="notes" class="tf-input" style="width: 100%; padding: 10px; border: 1px solid var(--tf-border); border-radius: 8px; height: 100px;" placeholder="أدخل الغرض من صرف الكمية أو تفاصيل إضافية">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; background: var(--tf-indigo); border: none; padding: 12px; border-radius: 8px; font-weight: bold; color: white; font-size: 16px;">
                <i class="fas fa-check"></i> تأكيد صرف الكمية
            </button>
        </form>
    </div>
</div>
@endsection
