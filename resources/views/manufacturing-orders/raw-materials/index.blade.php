@extends('layouts.app')

@section('title', 'الخامات')
@section('page-title', 'الخامات')

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

    .mfg-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 20px; flex-wrap: wrap; gap: 12px;
    }
    .mfg-title {
        font-size: 18px; font-weight: 900; color: var(--tf-text-h);
        display: flex; align-items: center; gap: 10px;
    }
    @media (min-width: 768px) { .mfg-title { font-size: 24px; } }
    .mfg-title i { color: var(--tf-indigo); }

    .mfg-card {
        background: var(--tf-surface); border-radius: 16px;
        border: 1px solid var(--tf-border); overflow: hidden; margin-bottom: 16px;
    }
    .mfg-card-header {
        padding: 12px 16px; border-bottom: 1px solid var(--tf-border);
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .mfg-card-title { font-size: 14px; font-weight: 800; margin: 0; }

    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .mfg-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    @media (min-width: 768px) { .mfg-table { font-size: 14px; } }

    .mfg-table th {
        background: var(--tf-bg); padding: 10px 12px; text-align: right;
        font-weight: 700; font-size: 11px; color: var(--tf-text-h); white-space: nowrap;
    }
    .mfg-table td {
        padding: 10px 12px; font-size: 12px; color: var(--tf-text-b);
        border-bottom: 1px solid #f0f4f8;
    }

    .btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; padding: 8px 16px; border-radius: 10px; font-weight: 700;
        font-size: 13px; border: none; cursor: pointer; transition: all .3s;
        text-decoration: none;
    }
    .btn-primary { background: var(--tf-indigo); color: #fff; }
    .btn-green { background: var(--tf-green); color: #fff; }
    .btn-amber { background: var(--tf-amber); color: #fff; }
    .btn-red { background: var(--tf-red); color: #fff; }
    .btn-sm { padding: 6px 12px; font-size: 12px; }

    @media (max-width: 767px) {
        .mfg-table thead { display: none; }
        .mfg-table tbody tr {
            display: block; background: #f8faff; border-radius: 12px;
            padding: 12px; margin-bottom: 12px; border: 1px solid var(--tf-border);
        }
        .mfg-table tbody td {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 0; border-top: none; text-align: right;
        }
        .mfg-table tbody td::before {
            content: attr(data-label); font-weight: 700; font-size: 11px;
            color: var(--tf-text-h); white-space: nowrap; flex-shrink: 0; min-width: 70px;
        }
        .mfg-table tbody td:last-child {
            justify-content: flex-end; padding-top: 8px;
            border-top: 1px solid var(--tf-border); margin-top: 4px;
        }
        .mfg-table tbody td:last-child::before { display: none; }
    }
</style>
@endpush

@section('content')
<div class="mfg-page">
    <div class="mfg-header">
        <div class="mfg-title">
            <i class="fas fa-boxes-stacked"></i>
            الخامات
        </div>
        <a href="{{ route('manufacturing-orders.raw-materials.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> إضافة خامة جديدة
        </a>
    </div>

    @if(session('success'))
    <div style="background:#ecfdf5; color:#047857; padding:14px 20px; border-radius:12px; margin-bottom:16px; font-weight:700;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div style="background:#fee2e2; color:#dc2626; padding:14px 20px; border-radius:12px; margin-bottom:16px; font-weight:700;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <div class="mfg-card">
        <div class="mfg-card-header">
            <h3 class="mfg-card-title">جميع الخامات ({{ $templates->total() }})</h3>
        </div>
        <div class="table-responsive">
            <table class="mfg-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الخامة</th>
                        <th>الكمية</th>
                        <th>سعر الشراء</th>
                        <th>سعر البيع</th>
                        <th>تاريخ الإنشاء</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                    <tr>
                        <td data-label="#">{{ $template->id }}</td>
                        <td data-label="اسم الخامة">
                            <a href="{{ route('manufacturing-orders.raw-materials.show', $template->id) }}"
                               style="color:var(--tf-indigo); font-weight:700; text-decoration:none;">
                                {{ $template->name }}
                            </a>
                        </td>
                        <td data-label="الكمية">{{ number_format($template->quantity) }}</td>
                        <td data-label="سعر الشراء">{{ number_format($template->buy_price, 2) }} ج.م</td>
                        <td data-label="سعر البيع">{{ number_format($template->sale_price, 2) }} ج.م</td>
                        <td data-label="تاريخ الإنشاء">{{ $template->created_at->format('Y-m-d') }}</td>
                        <td data-label="إجراءات">
                            <a href="{{ route('manufacturing-orders.raw-materials.edit', $template->id) }}" class="btn btn-amber btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('manufacturing-orders.raw-materials.destroy', $template->id) }}"
                                  style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه الخامة؟')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-red btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:40px; color:var(--tf-text-m);">
                            <i class="fas fa-inbox" style="font-size:32px; display:block; margin-bottom:10px;"></i>
                            لا توجد خامات بعد
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $templates->links() }}
</div>
@endsection
