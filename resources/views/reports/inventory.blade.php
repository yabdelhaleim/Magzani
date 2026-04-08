@extends('layouts.app')

@section('title', 'تقرير المخزون')
@section('page-title', 'تقرير المخزون')

@push('styles')
<style>
    :root {
        --tf-bg:          #f4f7fe;
        --tf-surface:     #ffffff;
        --tf-surface2:    #f8faff;
        --tf-border:      #e4eaf7;
        --tf-border-soft: #edf0f9;

        --tf-indigo:      #4338ca;
        --tf-indigo-light:#6366f1;
        --tf-indigo-soft: #e0e7ff;

        --tf-blue:        #0ea5e9;
        --tf-blue-soft:   #e0f2fe;
        --tf-green:       #059669;
        --tf-green-soft:  #d1fae5;
        --tf-red:         #dc2626;
        --tf-red-soft:    #fee2e2;
        --tf-amber:       #d97706;
        --tf-amber-soft:  #fef3c7;
        --tf-violet:      #7c3aed;
        --tf-violet-soft: #ede9fe;

        --tf-text-h:      #1a2140;
        --tf-text-b:      #3d4f72;
        --tf-text-m:      #64748b;
        --tf-text-d:      #94a3b8;

        --tf-shadow-sm:   0 2px 12px rgba(79,99,210,0.07);
        --tf-shadow-card: 0 2px 0 0 rgba(0,0,0,0.04), 0 4px 20px rgba(79,99,210,0.08);
        --tf-shadow-lg:   0 8px 30px rgba(79,99,210,0.10);
    }

    .tf-page {
        background: var(--tf-bg);
        background-image:
            radial-gradient(ellipse 90% 70% at 5% -15%,  rgba(14,165,233,0.15) 0%, transparent 50%),
            radial-gradient(ellipse 70% 60% at 95% 115%, rgba(6,182,212,0.12) 0%, transparent 50%);
        min-height: 100vh;
        padding: 26px 22px;
    }

    @keyframes tfFadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .tf-section { animation: tfFadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .tf-section:nth-child(1) { animation-delay: 0.04s; }
    .tf-section:nth-child(2) { animation-delay: 0.12s; }
    .tf-section:nth-child(3) { animation-delay: 0.20s; }
    .tf-section:nth-child(4) { animation-delay: 0.28s; }

    .tf-card {
        background: var(--tf-surface); border-radius: 20px;
        border: 1px solid var(--tf-border);
        overflow: hidden; box-shadow: var(--tf-shadow-card);
        margin-bottom: 20px; position: relative;
        transition: all .35s cubic-bezier(.22,1,.36,1);
    }
    .tf-card:hover { transform: translateY(-3px); box-shadow: var(--tf-shadow-lg); }

    .tf-card-head {
        padding: 20px 24px;
        background: linear-gradient(135deg, var(--tf-blue), #06b6d4);
        color: white;
    }
    .tf-card-head h3 { margin: 0; font-size: 1.25rem; font-weight: 700; }

    .tf-stat-card {
        background: var(--tf-surface); border-radius: 20px;
        padding: 1.5rem; position: relative; overflow: hidden;
        box-shadow: var(--tf-shadow-card);
        transition: all .35s cubic-bezier(.22,1,.36,1);
    }
    .tf-stat-card:hover { transform: translateY(-4px); box-shadow: var(--tf-shadow-lg); }

    .tf-stat-icon {
        width: 56px; height: 56px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 1rem; box-shadow: var(--tf-shadow-sm);
    }

    .tf-stat-label { font-size: 13px; color: var(--tf-text-m); margin-bottom: 4px; }
    .tf-stat-value { font-size: 1.75rem; font-weight: 800; color: var(--tf-text-h); }

    .tf-input, .tf-select {
        width: 100%; padding: 12px 16px;
        background: var(--tf-surface2);
        border: 1px solid var(--tf-border);
        border-radius: 12px; font-size: 14px;
        color: var(--tf-text-h); transition: all .25s;
    }
    .tf-input:focus, .tf-select:focus {
        outline: none; border-color: var(--tf-blue);
        box-shadow: 0 0 0 3px rgba(14,165,233,0.12);
    }
    .tf-input::placeholder { color: var(--tf-text-d); }

    .tf-label {
        display: block; font-size: 13px; font-weight: 600;
        color: var(--tf-text-b); margin-bottom: 8px;
    }
    .tf-label i { margin-left: 6px; color: var(--tf-blue); }

    .tf-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 12px 20px; border-radius: 12px; font-size: 14px; font-weight: 600;
        cursor: pointer; transition: all .3s cubic-bezier(.22,1,.36,1);
        border: none;
    }
    .tf-btn-primary {
        background: linear-gradient(135deg, var(--tf-blue), #06b6d4);
        color: white;
    }
    .tf-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(14,165,233,0.35);
    }
    .tf-btn-secondary {
        background: var(--tf-surface2); color: var(--tf-text-b);
        border: 1px solid var(--tf-border);
    }
    .tf-btn-secondary:hover { background: var(--tf-border-soft); }

    .tf-table { width: 100%; border-collapse: collapse; }
    .tf-table thead {
        background: linear-gradient(to right, var(--tf-surface2), var(--tf-surface));
    }
    .tf-table th {
        padding: 16px 20px; text-align: right;
        font-size: 12px; font-weight: 700; text-transform: uppercase;
        color: var(--tf-text-m); letter-spacing: 0.5px;
    }
    .tf-table td {
        padding: 16px 20px; border-top: 1px solid var(--tf-border-soft);
        color: var(--tf-text-b); font-size: 14px;
    }
    .tf-table tbody tr { transition: all .25s; }
    .tf-table tbody tr:hover { background: var(--tf-surface2); }

    .tf-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;
    }
    .tf-badge-green { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-badge-red { background: var(--tf-red-soft); color: var(--tf-red); }
    .tf-badge-amber { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .tf-badge-violet { background: var(--tf-violet-soft); color: var(--tf-violet); }
    .tf-badge-blue { background: var(--tf-blue-soft); color: var(--tf-blue); }

    .tf-avatar {
        width: 42px; height: 42px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 15px; color: white;
    }

    .tf-tfoot {
        background: linear-gradient(to right, var(--tf-blue-soft), var(--tf-surface));
        border-top: 2px solid var(--tf-blue-soft);
    }

    .tf-empty-state { padding: 4rem 2rem; text-align: center; }
    .tf-empty-icon {
        width: 96px; height: 96px; border-radius: 50%;
        background: var(--tf-surface2); display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem;
    }
    .tf-empty-icon i { font-size: 2.5rem; color: var(--tf-text-d); }

    .tf-header-gradient {
        background: linear-gradient(135deg, #0284c7 0%, #06b6d4 50%, #14b8a6 100%);
        border-radius: 20px; padding: 2rem; color: white;
        position: relative; overflow-hidden;
    }
    .tf-header-gradient::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.15) 0%, transparent 40%),
                    radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 40%);
    }
    .tf-header-content { position: relative; z-index: 1; }

    @media print {
        .no-print { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="tf-page">
    <!-- Header -->
    <div class="tf-header-gradient tf-section mb-6">
        <div class="tf-header-content">
            <h2 class="text-4xl font-bold mb-2">تقرير المخزون الشامل</h2>
            <p class="text-white/80 text-lg">عرض تفصيلي لجميع الأصناف والكميات المتاحة</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="tf-card tf-section">
        <div class="p-6">
            <h3 class="text-xl font-bold mb-4" style="color: var(--tf-text-h);">
                <i class="fas fa-filter" style="color: var(--tf-blue); margin-left: 8px;"></i>
                تصفية التقرير
            </h3>
            <form method="GET" action="{{ route('reports.inventory') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                    <label class="tf-label"><i class="fas fa-warehouse"></i>المخزن</label>
                    <select name="warehouse_id" class="tf-select">
                        <option value="">كل المخازن</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="tf-label"><i class="fas fa-search"></i>بحث</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="اسم المنتج أو الكود..." class="tf-input">
                </div>
                <div class="md:col-span-3">
                    <label class="tf-label"><i class="fas fa-layer-group"></i>الحالة</label>
                    <select name="status" class="tf-select">
                        <option value="">الكل</option>
                        <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>مخزون منخفض</option>
                        <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>مخزون طبيعي</option>
                        <option value="zero" {{ request('status') == 'zero' ? 'selected' : '' }}>نفذ من المخزون</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex items-end gap-2">
                    <button type="submit" class="tf-btn tf-btn-primary flex-1">
                        <i class="fas fa-search"></i>بحث
                    </button>
                    <button type="button" onclick="window.print()" class="tf-btn tf-btn-secondary">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="tf-stat-card tf-section">
            <div class="tf-stat-icon" style="background: linear-gradient(135deg, var(--tf-blue), #38bdf8);">
                <i class="fas fa-boxes text-2xl text-white"></i>
            </div>
            <div class="tf-stat-label">إجمالي الأصناف</div>
            <div class="tf-stat-value">{{ $totalProducts }}</div>
        </div>
        <div class="tf-stat-card tf-section" style="animation-delay: 0.06s;">
            <div class="tf-stat-icon" style="background: linear-gradient(135deg, var(--tf-green), #34d399);">
                <i class="fas fa-dollar-sign text-2xl text-white"></i>
            </div>
            <div class="tf-stat-label">إجمالي القيمة</div>
            <div class="tf-stat-value" dir="ltr" style="text-align: right;">{{ number_format($totalValue, 0) }} <span style="font-size: 1rem; color: var(--tf-text-m);">ج.م</span></div>
        </div>
        <div class="tf-stat-card tf-section" style="animation-delay: 0.12s;">
            <div class="tf-stat-icon" style="background: linear-gradient(135deg, var(--tf-amber), #fbbf24);">
                <i class="fas fa-exclamation-triangle text-2xl text-white"></i>
            </div>
            <div class="tf-stat-label">مخزون منخفض</div>
            <div class="tf-stat-value" style="color: var(--tf-amber);">{{ $lowStockCount }}</div>
        </div>
        <div class="tf-stat-card tf-section" style="animation-delay: 0.18s;">
            <div class="tf-stat-icon" style="background: linear-gradient(135deg, var(--tf-violet), #a78bfa);">
                <i class="fas fa-warehouse text-2xl text-white"></i>
            </div>
            <div class="tf-stat-label">عدد المخازن</div>
            <div class="tf-stat-value">{{ $warehouses->count() }}</div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="tf-card tf-section" style="animation-delay: 0.20s;">
        <div class="tf-card-head">
            <h3><i class="fas fa-list-alt ml-2"></i>تفاصيل المخزون</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="tf-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-barcode ml-1"></i>الكود / الباركود</th>
                        <th><i class="fas fa-box ml-1"></i>اسم الصنف</th>
                        <th><i class="fas fa-warehouse ml-1"></i>المخزن</th>
                        <th class="text-center"><i class="fas fa-cubes ml-1"></i>الكمية</th>
                        <th class="text-center"><i class="fas fa-chart-line ml-1"></i>الحد الأدنى</th>
                        <th class="text-center"><i class="fas fa-money-bill ml-1"></i>سعر الشراء</th>
                        <th class="text-center"><i class="fas fa-tag ml-1"></i>سعر البيع</th>
                        <th class="text-center"><i class="fas fa-calculator ml-1"></i>القيمة الإجمالية</th>
                        <th class="text-center">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventory as $item)
                        <tr>
                            <td>
                                <div>
                                    <p class="font-semibold" style="color: var(--tf-text-h);">{{ $item->code }}</p>
                                    @if($item->barcode)
                                        <p class="text-xs" style="color: var(--tf-text-m);">{{ $item->barcode }}</p>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="tf-avatar" style="background: linear-gradient(135deg, var(--tf-blue), #38bdf8);">
                                        {{ mb_substr($item->name, 0, 1) }}
                                    </div>
                                    <span class="font-medium" style="color: var(--tf-text-b);">{{ $item->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="tf-badge tf-badge-violet">
                                    <i class="fas fa-warehouse text-xs"></i>
                                    {{ $item->warehouse_name }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="inline-flex items-center justify-center w-16 h-16" 
                                      style="background: linear-gradient(135deg, var(--tf-blue-soft), #e0f2fe); color: var(--tf-blue); border-radius: 12px; font-size: 1.25rem; font-weight: 700;">
                                    {{ $item->quantity }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span style="color: var(--tf-text-b); font-weight: 600;">{{ $item->min_stock }}</span>
                            </td>
                            <td class="text-center">
                                <span style="color: var(--tf-text-b); font-weight: 600;" dir="ltr">{{ number_format($item->purchase_price, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span style="color: var(--tf-green); font-weight: 700;" dir="ltr">{{ number_format($item->selling_price, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <div>
                                    <p style="color: var(--tf-blue); font-weight: 700;" dir="ltr">{{ number_format($item->total_value, 2) }} ج.م</p>
                                    <p class="text-xs" style="color: var(--tf-text-m);" dir="ltr">ربح: {{ number_format($item->potential_profit, 0) }}</p>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($item->quantity == 0)
                                    <span class="tf-badge tf-badge-red">
                                        <i class="fas fa-times-circle"></i>نفذ
                                    </span>
                                @elseif($item->quantity <= $item->min_stock)
                                    <span class="tf-badge tf-badge-amber">
                                        <i class="fas fa-exclamation-triangle"></i>منخفض
                                    </span>
                                @else
                                    <span class="tf-badge tf-badge-green">
                                        <i class="fas fa-check-circle"></i>متوفر
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="tf-empty-state">
                                    <div class="tf-empty-icon"><i class="fas fa-inbox"></i></div>
                                    <h3 style="color: var(--tf-text-h);">لا توجد بيانات</h3>
                                    <p style="color: var(--tf-text-m);">لم يتم العثور على أي منتجات في المخزون</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="tf-tfoot">
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-right font-bold" style="color: var(--tf-blue); font-size: 1.1rem;">
                            الإجمالي الكلي
                        </td>
                        <td class="px-6 py-4 text-center font-bold" style="color: var(--tf-blue); font-size: 1.25rem;" dir="ltr">
                            {{ number_format($totalValue, 2) }} ج.م
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection