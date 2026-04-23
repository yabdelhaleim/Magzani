@extends('layouts.app')

@section('title', 'حساب تكلفة التصنيع')
@section('page-title', 'حساب تكلفة التصنيع')

@push('styles')
<style>
    :root {
        --tf-bg:          #f4f7fe;
        --tf-surface:     #ffffff;
        --tf-border:      #e4eaf7;
        --tf-indigo:      #4f63d2;
        --tf-blue:        #3a8ef0;
        --tf-green:       #0faa7e;
        --tf-red:         #dc2626;
        --tf-amber:       #e8930a;
        --tf-text-h:      #1a2140;
        --tf-text-b:      #3d4f72;
        --tf-text-m:      #7e90b0;
        --tf-text-d:      #94a3b8;
        --tf-shadow-card: 0 2px 0 0 rgba(0,0,0,0.04), 0 4px 20px rgba(79,99,210,0.08);
    }
    .mfg-page {
        background: var(--tf-bg);
        min-height: 100vh;
        padding: 26px 22px;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .mfg-section { animation: fadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .mfg-section:nth-child(1) { animation-delay: 0.05s; }
    .mfg-section:nth-child(2) { animation-delay: 0.12s; }

    .mfg-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 24px; flex-wrap: wrap; gap: 16px;
    }
    .mfg-title { font-size: 24px; font-weight: 900; color: var(--tf-text-h); display: flex; align-items: center; gap: 12px; }
    .mfg-title i { color: var(--tf-indigo); }

    .mfg-card {
        background: var(--tf-surface);
        border-radius: 18px;
        border: 1px solid var(--tf-border);
        overflow: hidden;
    }
    .mfg-card-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--tf-border);
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: wrap; gap: 12px;
    }
    .mfg-card-title { font-size: 16px; font-weight: 700; color: var(--tf-text-h); }

    .mfg-table { width: 100%; border-collapse: collapse; }
    .mfg-table th {
        padding: 14px 18px;
        font-size: 12px; font-weight: 700;
        color: var(--tf-text-d);
        text-transform: uppercase;
        background: #f8faff;
        text-align: right;
        border-bottom: 1px solid var(--tf-border);
    }
    .mfg-table td {
        padding: 14px 18px;
        font-size: 14px;
        color: var(--tf-text-b);
        border-bottom: 1px solid #f0f4f8;
    }
    .mfg-table tbody tr { transition: background .2s; }
    .mfg-table tbody tr:hover { background: #f8faff; }

    .badge {
        display: inline-block; padding: 4px 12px; border-radius: 20px;
        font-size: 12px; font-weight: 700;
    }
    .badge-draft { background: #fff4e0; color: #b45309; }
    .badge-confirmed { background: #e6f8f3; color: #047857; }

    .btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 20px; border-radius: 12px; font-weight: 700;
        font-size: 14px; border: none; cursor: pointer;
        transition: all .3s; text-decoration: none;
    }
    .btn-primary { background: var(--tf-indigo); color: #fff; }
    .btn-primary:hover { background: #3b52c0; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(79,99,210,0.3); }
    .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 8px; }
    .btn-blue { background: var(--tf-blue-soft, #e8f2ff); color: var(--tf-blue); }
    .btn-green { background: #e6f8f3; color: #047857; }
    .btn-red { background: #fee2e2; color: #dc2626; }
    .btn-amber { background: #fff4e0; color: #b45309; }

    .mfg-filters {
        display: flex; gap: 12px; flex-wrap: wrap; align-items: center;
    }
    .mfg-filters input, .mfg-filters select {
        padding: 8px 14px; border-radius: 10px; border: 1px solid var(--tf-border);
        font-size: 13px; color: var(--tf-text-b); background: #fff;
        font-family: 'Cairo', sans-serif;
    }
    .mfg-filters input:focus, .mfg-filters select:focus {
        outline: none; border-color: var(--tf-indigo); box-shadow: 0 0 0 3px rgba(79,99,210,0.1);
    }

    .mfg-actions { display: flex; gap: 6px; }
    .price-val { font-weight: 800; color: var(--tf-indigo); }

    .pagination { padding: 16px 22px; display: flex; justify-content: center; gap: 6px; }
    .pagination a, .pagination span {
        padding: 8px 14px; border-radius: 10px; font-size: 13px; font-weight: 600;
        border: 1px solid var(--tf-border); color: var(--tf-text-b);
        text-decoration: none; transition: all .2s;
    }
    .pagination a:hover { background: var(--tf-indigo); color: #fff; border-color: var(--tf-indigo); }
    .pagination .active { background: var(--tf-indigo); color: #fff; border-color: var(--tf-indigo); }
</style>
@endpush

@section('content')
<div class="mfg-page">
    <div class="mfg-section">
        <div class="mfg-header">
            <h1 class="mfg-title">
                <i class="fas fa-industry"></i>
                حساب تكلفة التصنيع
            </h1>
            <a href="{{ route('manufacturing.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                حساب جديد
            </a>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#e6f8f3; color:#047857; padding:14px 20px; border-radius:12px; margin-bottom:20px; font-weight:700; font-size:14px; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div style="background:#fee2e2; color:#dc2626; padding:14px 20px; border-radius:12px; margin-bottom:20px; font-weight:700; font-size:14px; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <div class="mfg-section">
        <div class="mfg-card">
            <div class="mfg-card-header">
                <span class="mfg-card-title">قائمة الحسابات</span>
                <form method="GET" action="{{ route('manufacturing.index') }}" class="mfg-filters">
                    <input type="text" name="search" placeholder="بحث بالاسم..." value="{{ request('search') }}">
                    <select name="status">
                        <option value="">كل الحالات</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>مؤكد</option>
                    </select>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" title="من تاريخ">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" title="إلى تاريخ">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            @if($costs->count() > 0)
            <table class="mfg-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم المنتج</th>
                        <th>الحجم الكلي (م³)</th>
                        <th>تكلفة الخامات</th>
                        <th>التكلفة الإجمالية</th>
                        <th>السعر النهائي</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($costs as $cost)
                    <tr>
                        <td>{{ $cost->id }}</td>
                        <td style="font-weight:700;">{{ $cost->product_name }}</td>
                        <td>{{ number_format($cost->total_volume_m3, 6) }}</td>
                        <td>{{ number_format($cost->material_cost, 2) }}</td>
                        <td>{{ number_format($cost->total_cost, 2) }}</td>
                        <td class="price-val">{{ number_format($cost->final_price, 2) }}</td>
                        <td>
                            @if($cost->status === 'draft')
                            <span class="badge badge-draft">مسودة</span>
                            @else
                            <span class="badge badge-confirmed">مؤكد</span>
                            @endif
                        </td>
                        <td>{{ $cost->created_at->format('Y/m/d') }}</td>
                        <td>
                            <div class="mfg-actions">
                                <a href="{{ route('manufacturing.show', $cost) }}" class="btn btn-sm btn-blue" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($cost->status === 'draft')
                                <a href="{{ route('manufacturing.edit', $cost) }}" class="btn btn-sm btn-amber" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('manufacturing.confirm', $cost) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-green" title="تأكيد" onclick="return confirm('هل أنت متأكد من تأكيد هذا الحساب؟')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('manufacturing.destroy', $cost) }}" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-red" title="حذف" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div style="text-align:center; padding:60px 20px; color:var(--tf-text-m);">
                <i class="fas fa-industry" style="font-size:48px; opacity:0.3; display:block; margin-bottom:16px;"></i>
                <p style="font-size:16px; font-weight:700;">لا توجد حسابات تصنيع بعد</p>
                <p style="font-size:13px;">ابدأ بإضافة حساب تكلفة تصنيع جديد</p>
            </div>
            @endif

            <div class="pagination">
                {{ $costs->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
