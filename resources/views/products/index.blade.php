@extends('layouts.app')

@section('title', 'قائمة الأصناف')
@section('page-title', 'الأصناف')

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
        --tf-text-s:      #64748b;

        --tf-shadow-sm:   0 2px 12px rgba(79,99,210,0.07);
        --tf-shadow-card: 0 2px 0 0 rgba(0,0,0,0.04), 0 4px 20px rgba(79,99,210,0.08);
        --tf-shadow-lg:   0 8px 30px rgba(79,99,210,0.10);
    }

    .tf-page {
        background: var(--tf-bg);
        background-image:
            radial-gradient(ellipse 80% 60% at 10% -10%,  rgba(79,99,210,0.12) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(58,142,240,0.1) 0%, transparent 50%);
        min-height: 100vh;
        padding: 26px 22px;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
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

    .prod-section {
        animation: fadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both;
    }
    .prod-section:nth-child(1) { animation-delay: 0.05s; }
    .prod-section:nth-child(2) { animation-delay: 0.12s; }
    .prod-section:nth-child(3) { animation-delay: 0.19s; }

    /* Stat Cards */
    .stat-card {
        background: var(--tf-surface);
        border-radius: 18px;
        border: 1px solid var(--tf-border);
        padding: 20px;
        position: relative;
        overflow: hidden;
        transition: all .35s cubic-bezier(.22,1,.36,1);
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--tf-shadow-lg);
    }
    .stat-card::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
    }
    .stat-card.blue::after   { background: linear-gradient(90deg, var(--tf-indigo), var(--tf-blue)); }
    .stat-card.green::after  { background: linear-gradient(90deg, var(--tf-green), var(--tf-indigo-light)); }
    .stat-card.amber::after  { background: linear-gradient(90deg, var(--tf-amber), var(--tf-blue)); }
    .stat-card.red::after    { background: linear-gradient(90deg, var(--tf-red), var(--tf-amber)); }

    .stat-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px;
        transition: transform .4s cubic-bezier(.34,1.56,.64,1);
    }
    .stat-card:hover .stat-icon { animation: iconBounce .6s ease; }
    .stat-card.blue  .stat-icon { background: var(--tf-blue-soft); color: var(--tf-blue); }
    .stat-card.green .stat-icon { background: var(--tf-green-soft); color: var(--tf-green); }
    .stat-card.amber .stat-icon { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .stat-card.red   .stat-icon { background: var(--tf-red-soft); color: var(--tf-red); }

    .stat-val { font-size: 22px; font-weight: 900; color: var(--tf-text-h); line-height: 1.1; margin-top: 10px; }
    .stat-lbl { font-size: 11px; font-weight: 600; color: var(--tf-text-d); margin-top: 3px; }

    /* Section Card */
    .section-card {
        background: var(--tf-surface);
        border-radius: 18px;
        border: 1px solid var(--tf-border);
        overflow: hidden;
        transition: all .35s;
    }
    .section-card:hover { box-shadow: var(--tf-shadow-lg); }

    .section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 22px;
        border-bottom: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2);
    }
    .section-head-left {
        display: flex; align-items: center; gap: 12px;
    }
    .section-icon {
        width: 40px; height: 40px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px; flex-shrink: 0;
        transition: transform .4s cubic-bezier(.34,1.56,.64,1);
    }
    .section-card:hover .section-icon { animation: iconBounce .6s ease; }
    .section-icon.blue { background: var(--tf-blue-soft); color: var(--tf-blue); }

    .section-title { font-size: 15px; font-weight: 800; color: var(--tf-text-h); margin: 0; }
    .section-sub { font-size: 11px; color: var(--tf-text-d); margin: 2px 0 0; font-weight: 600; }

    /* Table */
    .prod-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .prod-table thead th {
        padding: 12px 16px;
        text-align: right;
        font-size: 11px;
        font-weight: 700;
        color: var(--tf-text-d);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2);
    }
    .prod-table tbody td {
        padding: 14px 16px;
        font-size: 13px;
        color: var(--tf-text-b);
        border-bottom: 1px solid var(--tf-border-soft);
        vertical-align: middle;
    }
    .prod-table tbody tr { transition: background 0.15s; }
    .prod-table tbody tr:hover { background: var(--tf-surface2); }
    .prod-table tbody tr:last-child td { border-bottom: none; }

    /* Badges */
    .badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; border-radius: 50px;
        font-size: 11px; font-weight: 700; white-space: nowrap;
    }
    .badge-green  { background: var(--tf-green-soft); color: var(--tf-green); }
    .badge-yellow { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .badge-red    { background: var(--tf-red-soft); color: var(--tf-red); }
    .badge-gray   { background: var(--tf-surface2); color: var(--tf-text-s); }
    .badge-purple { background: var(--tf-violet-soft); color: var(--tf-violet); }
    .badge-blue   { background: var(--tf-blue-soft); color: var(--tf-blue); }

    /* Action Buttons */
    .act-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 34px; height: 34px;
        border-radius: 10px; border: none;
        cursor: pointer; transition: all .2s;
    }
    .act-btn.view  { background: var(--tf-blue-soft); color: var(--tf-blue); }
    .act-btn.view:hover { background: var(--tf-blue); color: var(--tf-surface); }
    .act-btn.edit  { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .act-btn.edit:hover { background: var(--tf-amber); color: var(--tf-surface); }
    .act-btn.del   { background: var(--tf-red-soft); color: var(--tf-red); }
    .act-btn.del:hover { background: var(--tf-red); color: var(--tf-surface); }

    /* Btn Primary */
    .btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 11px 22px;
        background: linear-gradient(135deg, var(--tf-indigo), var(--tf-blue));
        color: var(--tf-surface); border-radius: 14px; font-size: 13px; font-weight: 800;
        text-decoration: none; border: none; cursor: pointer;
        box-shadow: 0 4px 18px rgba(58,142,240,0.35);
        transition: all .25s; white-space: nowrap;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(58,142,240,0.45);
        color: var(--tf-surface);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    .empty-icon {
        width: 70px; height: 70px;
        background: var(--tf-surface2); border-radius: 20px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 18px; font-size: 28px; color: var(--tf-text-d);
    }

    /* Pagination */
    .pagination-bar {
        padding: 16px 22px;
        border-top: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2);
        display: flex; align-items: center; justify-content: space-between;
    }

    /* Alert */
    .alert-toast {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 18px; border-radius: 14px; margin-bottom: 20px;
        animation: fadeUp 0.4s ease;
    }
    .alert-toast.success { background: var(--tf-green-soft); border: 1px solid rgba(15,170,126,0.2); }
    .alert-toast.error   { background: var(--tf-red-soft); border: 1px solid rgba(232,75,90,0.2); }

    @media (max-width: 768px) {
        .prod-table { display: block; overflow-x: auto; }
    }
</style>
@endpush

@section('content')

<!-- ══ Stats Bar ══ -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6 prod-section">

    <div class="stat-card blue">
        <div class="flex items-center gap-3">
            <div class="stat-icon"><i class="fas fa-boxes"></i></div>
            <div>
                <div class="stat-val">{{ number_format($products->total()) }}</div>
                <div class="stat-lbl">إجمالي الأصناف</div>
            </div>
        </div>
    </div>

    <div class="stat-card green">
        <div class="flex items-center gap-3">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-val">{{ $products->where('is_active', true)->count() }}</div>
                <div class="stat-lbl">نشط</div>
            </div>
        </div>
    </div>

    <div class="stat-card amber">
        <div class="flex items-center gap-3">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <div class="stat-val">{{ $products->where('stock_status', 'منخفض')->count() + $products->where('stock_status', 'نفذ')->count() }}</div>
                <div class="stat-lbl">مخزون منخفض</div>
            </div>
        </div>
    </div>

    <div class="stat-card red">
        <div class="flex items-center gap-3">
            <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
            <div>
                <div class="stat-val">{{ $products->pluck('category')->unique()->filter()->count() }}</div>
                <div class="stat-lbl">تصنيف</div>
            </div>
        </div>
    </div>

</div>

<!-- ══ Alerts ══ -->
@if(session('success'))
    <div class="alert-toast success prod-section">
        <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-check text-emerald-600 text-sm"></i>
        </div>
        <p class="font-bold text-emerald-800 text-sm flex-1">{{ session('success') }}</p>
        <button type="button" class="text-emerald-400 hover:text-emerald-600" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert-toast error prod-section">
        <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-600 text-sm"></i>
        </div>
        <p class="font-bold text-red-800 text-sm flex-1">{{ session('error') }}</p>
        <button type="button" class="text-red-400 hover:text-red-600" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

<!-- ══ Products Table ══ -->
<div class="section-card prod-section">

    <div class="section-head">
        <div class="section-head-left">
            <div class="section-icon blue"><i class="fas fa-box-open"></i></div>
            <div>
                <h3 class="section-title">قائمة المنتجات</h3>
                <p class="section-sub">إجمالي: {{ $products->total() }} منتج</p>
            </div>
        </div>
        <a href="{{ route('products.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            إضافة صنف جديد
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="prod-table">
            <thead>
                <tr>
                    <th style="width:100px;">الكود</th>
                    <th>الاسم</th>
                    <th style="width:120px;">التصنيف</th>
                    <th style="width:110px;">سعر الشراء</th>
                    <th style="width:110px;">سعر البيع</th>
                    <th style="width:110px;">المخزون</th>
                    <th style="width:90px;">الحالة</th>
                    <th style="width:130px;" class="text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                @php
                    $totalStock = $product->total_stock;
                    $status = $product->stock_status;
                @endphp
                <tr>
                    {{-- الكود --}}
                    <td>
                        <span class="font-bold text-blue-600 text-xs" style="letter-spacing:0.5px;">#{{ $product->code }}</span>
                    </td>

                    {{-- الاسم --}}
                    <td>
                        <div class="flex items-center gap-3">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                     class="w-9 h-9 rounded-lg object-cover border border-slate-200">
                            @else
                                <div class="w-9 h-9 bg-slate-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-box text-slate-400 text-sm"></i>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-bold text-slate-800 text-sm truncate">{{ $product->name }}</p>
                                @if($product->sku)
                                    <p class="text-[10px] text-slate-400 font-semibold">SKU: {{ $product->sku }}</p>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- التصنيف --}}
                    <td>
                        <span class="badge badge-purple">{{ $product->category }}</span>
                    </td>

                    {{-- سعر الشراء --}}
                    <td>
                        <span class="font-bold text-slate-700 text-sm">{{ number_format($product->purchase_price, 2) }}</span>
                        <span class="text-slate-400 text-xs">ج.م</span>
                    </td>

                    {{-- سعر البيع --}}
                    <td>
                        <span class="font-bold text-blue-600 text-sm">{{ number_format($product->selling_price, 2) }}</span>
                        <span class="text-slate-400 text-xs">ج.م</span>
                    </td>

                    {{-- المخزون --}}
                    <td>
                        @if($status === 'نفذ')
                            <span class="badge badge-red"><i class="fas fa-times-circle text-[9px]"></i> نفذ</span>
                        @elseif($status === 'منخفض')
                            <span class="badge badge-yellow"><i class="fas fa-exclamation-triangle text-[9px]"></i> {{ number_format($totalStock) }}</span>
                        @else
                            <span class="badge badge-green"><i class="fas fa-check text-[9px]"></i> {{ number_format($totalStock) }}</span>
                        @endif
                    </td>

                    {{-- الحالة --}}
                    <td>
                        @if($product->is_active)
                            <span class="badge badge-green"><i class="fas fa-circle text-[7px]"></i> نشط</span>
                        @else
                            <span class="badge badge-gray"><i class="fas fa-circle text-[7px]"></i> غير نشط</span>
                        @endif
                    </td>

                    {{-- الإجراءات --}}
                    <td>
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('products.show', $product) }}" class="act-btn view" title="عرض">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('products.edit', $product) }}" class="act-btn edit" title="تعديل">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            <button type="button" onclick="confirmDelete({{ $product->id }}, '{{ $product->name }}')" class="act-btn del" title="حذف">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                            <form id="delete-form-{{ $product->id }}" action="{{ route('products.destroy', $product) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-box-open"></i></div>
                            <h3 class="text-lg font-bold text-slate-700 mb-1">لا توجد منتجات حالياً</h3>
                            <p class="text-slate-400 text-sm font-semibold mb-5">ابدأ بإضافة منتج جديد لإدارة مخزونك</p>
                            <a href="{{ route('products.create') }}" class="btn-primary" style="display:inline-flex;">
                                <i class="fas fa-plus"></i>
                                إضافة منتج جديد
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
    <div class="pagination-bar">
        <div class="text-xs text-slate-400 font-semibold">
            عرض {{ $products->firstItem() }} إلى {{ $products->lastItem() }} من {{ $products->total() }}
        </div>
        <div>{{ $products->links() }}</div>
    </div>
    @endif

</div>

@endsection

@push('scripts')
<script>
function confirmDelete(id, name) {
    if (confirm(`هل أنت متأكد من حذف: ${name}؟\nلا يمكن التراجع!`)) {
        document.getElementById(`delete-form-${id}`).submit();
    }
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert-toast').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'all 0.4s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });
});
</script>
@endpush
