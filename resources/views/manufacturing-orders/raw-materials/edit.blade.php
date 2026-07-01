@extends('layouts.app')

@section('title', 'تعديل خامة')
@section('page-title', 'تعديل خامة')

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

    .mfg-title {
        font-size: 20px; font-weight: 900; color: var(--tf-text-h);
        display: flex; align-items: center; gap: 12px; margin-bottom: 20px;
    }
    @media (min-width: 768px) { .mfg-title { font-size: 24px; } }
    .mfg-title i { color: var(--tf-indigo); }

    .mfg-card {
        background: var(--tf-surface); border-radius: 16px;
        border: 1px solid var(--tf-border); overflow: hidden; margin-bottom: 16px;
    }
    .mfg-card-header {
        padding: 12px 16px; border-bottom: 1px solid var(--tf-border);
        display: flex; align-items: center; gap: 10px;
    }
    .mfg-card-title { font-size: 14px; font-weight: 800; margin: 0; }
    .mfg-card-body { padding: 16px; }
    @media (min-width: 768px) { .mfg-card-body { padding: 22px; } }

    .btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; padding: 8px 16px; border-radius: 10px; font-weight: 700;
        font-size: 13px; border: none; cursor: pointer; transition: all .3s;
        text-decoration: none;
    }
    .btn-primary { background: var(--tf-indigo); color: #fff; }
    .btn-red { background: var(--tf-red); color: #fff; }
    .btn-green { background: var(--tf-green); color: #fff; }

    .form-group { margin-bottom: 16px; }
    @media (min-width: 768px) { .form-group { margin-bottom: 20px; } }

    .form-label {
        display: block; font-size: 13px; font-weight: 700;
        color: var(--tf-text-h); margin-bottom: 6px;
    }

    .form-control {
        width: 100%; padding: 10px 12px; border: 1px solid var(--tf-border);
        border-radius: 10px; font-size: 14px; transition: all 0.3s; background: #fff;
    }
    .form-control:focus {
        outline: none; border-color: var(--tf-indigo);
        box-shadow: 0 0 0 3px rgba(79,99,210,0.1);
    }

    .grid-2 { display: grid; gap: 12px; }
    @media (min-width: 640px) { .grid-2 { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 1024px) { .grid-2 { gap: 20px; } }

    .action-buttons {
        display: flex; flex-direction: column; gap: 10px;
        position: fixed; bottom: 0; left: 0; right: 0;
        background: white; padding: 12px 16px;
        border-top: 1px solid var(--tf-border);
        box-shadow: 0 -4px 12px rgba(0,0,0,0.1); z-index: 100;
    }
    @media (min-width: 768px) {
        .action-buttons {
            position: static; flex-direction: row; background: transparent;
            padding: 0; border: none; box-shadow: none;
        }
    }
</style>
@endpush

@section('content')
<div class="mfg-page">
    <div class="mfg-title">
        <i class="fas fa-boxes-stacked"></i>
        تعديل خامة: {{ $template->name }}
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

    <form method="POST" action="{{ route('manufacturing-orders.raw-materials.update', $template->id) }}">
        @csrf @method('PUT')

        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-box" style="color:var(--tf-blue);"></i>
                <h3 class="mfg-card-title">بيانات الخامة</h3>
            </div>
            <div class="mfg-card-body">
                <p style="font-size:12px;color:var(--tf-text-m);margin:0 0 16px;line-height:1.5;">
                    عند التحديث تُحدَّث كمية الخام في المخزن المختار وتظهر في صفحة المخزن ضمن «خامات التصنيع».
                </p>
                <div class="form-group">
                    <label class="form-label">اسم الخامة</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $template->name) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">المخزن</label>
                    <select name="warehouse_id" class="form-control" required>
                        <option value="">— اختر المخزن —</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" @selected(old('warehouse_id', $template->warehouse_id) == $wh->id)>
                                {{ $wh->name }}@if($wh->code) ({{ $wh->code }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">الكمية</label>
                        <input type="number" name="quantity" class="form-control" required step="0.01" min="0" value="{{ old('quantity', $template->quantity) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">سعر الشراء</label>
                        <input type="number" name="buy_price" class="form-control" required step="0.01" min="0" value="{{ old('buy_price', $template->buy_price) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">سعر البيع</label>
                        <input type="number" name="sale_price" class="form-control" required step="0.01" min="0" value="{{ old('sale_price', $template->sale_price) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <button type="submit" class="btn btn-green">
                <i class="fas fa-check"></i> حفظ التعديلات
            </button>
            <a href="{{ route('manufacturing-orders.raw-materials.index') }}" class="btn btn-red">
                <i class="fas fa-times"></i> إلغاء
            </a>
        </div>
    </form>
</div>
@endsection
