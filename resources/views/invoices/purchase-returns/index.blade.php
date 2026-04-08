@extends('layouts.app')

@section('title', 'مرتجعات المشتريات')
@section('page-title', 'مرتجعات المشتريات')

@push('styles')
<style>
    :root {
        --tf-bg:          #f4f7fe;
        --tf-surface:     #ffffff;
        --tf-surface2:    #f8faff;
        --tf-border:      #e4eaf7;
        --tf-border-soft: #edf0f9;

        --tf-indigo:      #4f63d2;
        --tf-indigo-light:#7088e8;
        --tf-indigo-soft: #eef0fc;

        --tf-blue:        #3a8ef0;
        --tf-blue-soft:   #e8f2ff;
        --tf-green:       #0faa7e;
        --tf-green-soft:  #e6f8f3;
        --tf-red:         #dc2626;
        --tf-red-soft:    #fee2e2;
        --tf-amber:       #e8930a;
        --tf-amber-soft:  #fff4e0;
        --tf-violet:      #7c5cec;
        --tf-violet-soft: #f0ecff;

        --tf-text-h:      #1a2140;
        --tf-text-b:      #3d4f72;
        --tf-text-m:      #7e90b0;
        --tf-text-d:      #94a3b8;

        --tf-shadow-sm:   0 2px 12px rgba(79,99,210,0.07);
        --tf-shadow-card: 0 2px 0 0 rgba(0,0,0,0.04), 0 4px 20px rgba(79,99,210,0.08);
        --tf-shadow-lg:   0 8px 30px rgba(79,99,210,0.10);
    }

    .tf-page {
        background: var(--tf-bg);
        background-image:
            radial-gradient(ellipse 80% 60% at 10% -10%,  rgba(124,92,236,0.12) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(79,99,210,0.1) 0%, transparent 50%);
        min-height: 100vh;
        padding: 26px 22px;
    }

    @keyframes tfFadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes tfShimmer {
        0%   { background-position: -600px 0; }
        100% { background-position: 600px 0; }
    }
    @keyframes iconBounce {
        0%,100% { transform: translateY(0) rotate(0deg); }
        30%     { transform: translateY(-4px) rotate(-8deg); }
        60%     { transform: translateY(-2px) rotate(4deg); }
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
        display: flex; justify-content: space-between; align-items: center;
        padding: 20px 24px; border-bottom: 1px solid var(--tf-border-soft);
    }
    .tf-card-body { padding: 24px; }

    .tf-input {
        width: 100%; padding: 12px 16px;
        background: var(--tf-surface2);
        border: 1px solid var(--tf-border);
        border-radius: 12px; font-size: 14px;
        color: var(--tf-text-h); transition: all .25s;
    }
    .tf-input:focus {
        outline: none; border-color: var(--tf-violet);
        box-shadow: 0 0 0 3px rgba(124,92,236,0.12);
    }
    .tf-input::placeholder { color: var(--tf-text-d); }

    .tf-label {
        display: block; font-size: 13px; font-weight: 600;
        color: var(--tf-text-b); margin-bottom: 8px;
    }
    .tf-label i { margin-right: 6px; color: var(--tf-violet); }

    .tf-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 12px 20px; border-radius: 12px; font-size: 14px; font-weight: 600;
        cursor: pointer; transition: all .3s cubic-bezier(.22,1,.36,1);
        border: none;
    }
    .tf-btn-primary {
        background: linear-gradient(135deg, var(--tf-violet), #9575fa);
        color: white;
    }
    .tf-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(124,92,236,0.35);
    }
    .tf-btn-secondary {
        background: var(--tf-surface2); color: var(--tf-text-b);
        border: 1px solid var(--tf-border);
    }
    .tf-btn-secondary:hover {
        background: var(--tf-border-soft); transform: translateY(-2px);
    }

    .tf-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;
    }
    .tf-badge-violet {
        background: var(--tf-violet-soft); color: var(--tf-violet);
    }
    .tf-badge-red {
        background: var(--tf-red-soft); color: var(--tf-red);
    }
    .tf-badge-blue {
        background: var(--tf-blue-soft); color: var(--tf-blue);
    }

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
    .tf-table tbody tr {
        transition: all .25s;
    }
    .tf-table tbody tr:hover {
        background: var(--tf-surface2);
    }

    .tf-avatar {
        width: 42px; height: 42px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 15px; color: white;
        box-shadow: var(--tf-shadow-sm);
    }

    .tf-paginate {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 24px; border-top: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2);
    }
    .tf-paginate-info { font-size: 14px; color: var(--tf-text-m); }
    .tf-paginate-info strong { color: var(--tf-text-h); }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<div class="tf-page">
    <!-- Header Section -->
    <div class="tf-section flex items-center justify-between mb-6">
        <div>
            <h2 class="text-3xl font-bold" style="color: var(--tf-text-h);">مرتجعات المشتريات</h2>
            <p class="mt-1" style="color: var(--tf-text-m);">إدارة ومتابعة جميع مرتجعات المشتريات من الموردين</p>
        </div>
        <div class="tf-badge tf-badge-violet px-6 py-3" style="font-size: 15px;">
            <i class="fas fa-undo-alt"></i>
            <span>إجمالي المرتجعات: <strong>{{ $returns->total() }}</strong></span>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="tf-card tf-section">
        <form method="GET" action="{{ route('invoices.purchase-returns.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-5">
                <label class="tf-label">
                    <i class="fas fa-search"></i>
                    البحث
                </label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       class="tf-input"
                       placeholder="ابحث باسم المورد أو الصنف...">
            </div>
            
            <div class="md:col-span-3">
                <label class="tf-label">
                    <i class="fas fa-calendar"></i>
                    التاريخ
                </label>
                <input type="date" 
                       name="date" 
                       value="{{ request('date') }}" 
                       class="tf-input">
            </div>
            
            <div class="md:col-span-4 flex items-end gap-2">
                <button type="submit" class="tf-btn tf-btn-primary flex-1">
                    <i class="fas fa-filter"></i>
                    تصفية
                </button>
                <a href="{{ route('invoices.purchase-returns.index') }}" 
                   class="tf-btn tf-btn-secondary flex-1 text-center">
                    <i class="fas fa-redo"></i>
                    إعادة تعيين
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="tf-card tf-section" style="animation-delay: 0.12s;">
        <div class="overflow-x-auto">
            <table class="tf-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><i class="fas fa-truck ml-1"></i>المورد</th>
                        <th><i class="fas fa-box ml-1"></i>الصنف</th>
                        <th class="text-center"><i class="fas fa-cubes ml-1"></i>الكمية المرتجعة</th>
                        <th><i class="fas fa-comment-alt ml-1"></i>سبب الإرجاع</th>
                        <th class="text-center"><i class="fas fa-calendar-day ml-1"></i>تاريخ الإرجاع</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $index => $return)
                        <tr>
                            <td>
                                <span class="inline-flex items-center justify-center w-8 h-8" 
                                      style="background: var(--tf-surface2); border-radius: 10px; font-weight: 600; color: var(--tf-text-b);">
                                    {{ $returns->firstItem() + $index }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="tf-avatar" style="background: linear-gradient(135deg, var(--tf-violet), #9575fa);">
                                        {{ mb_substr($return->purchaseInvoice->supplier->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold" style="color: var(--tf-text-h);">{{ $return->purchaseInvoice->supplier->name }}</p>
                                        <p class="text-xs" style="color: var(--tf-text-d);">مورد معتمد</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-tag" style="color: var(--tf-text-d);"></i>
                                    <span class="font-medium" style="color: var(--tf-text-b);">{{ $return->item_name }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="tf-badge tf-badge-red">
                                    <i class="fas fa-undo text-xs"></i>
                                    {{ $return->quantity }} وحدة
                                </span>
                            </td>
                            <td>
                                <p class="text-sm line-clamp-2" style="color: var(--tf-text-m);">
                                    {{ Str::limit($return->reason, 50) }}
                                </p>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-2" style="color: var(--tf-text-m);">
                                    <i class="fas fa-calendar" style="color: var(--tf-blue);"></i>
                                    <span class="text-sm font-medium">{{ $return->created_at->format('Y-m-d') }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('invoices.purchase-returns.show', $return->id) }}" 
                                   class="tf-btn tf-btn-primary">
                                    <i class="fas fa-eye"></i>
                                    عرض التفاصيل
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-16">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-24 h-24 rounded-full flex items-center justify-center mb-4" 
                                         style="background: var(--tf-surface2);">
                                        <i class="fas fa-inbox text-5xl" style="color: var(--tf-text-d);"></i>
                                    </div>
                                    <h3 class="text-xl font-bold mb-2" style="color: var(--tf-text-h);">لا توجد بيانات</h3>
                                    <p style="color: var(--tf-text-m);">لم يتم العثور على أي مرتجعات مشتريات</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
            <div class="tf-paginate">
                <div class="tf-paginate-info">
                    عرض <strong>{{ $returns->firstItem() ?? 0 }}</strong> 
                    إلى <strong>{{ $returns->lastItem() ?? 0 }}</strong> 
                    من أصل <strong>{{ $returns->total() }}</strong> مرتجع
                </div>
                <div>
                    {{ $returns->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
