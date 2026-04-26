@extends('layouts.app')

@section('title', 'أوامر التصنيع')
@section('page-title', 'أوامر التصنيع')

@push('styles')
<style>
    :root {
        --tf-bg: #f4f7fe; --tf-surface: #ffffff; --tf-border: #e4eaf7;
        --tf-indigo: #4f63d2; --tf-blue: #3a8ef0; --tf-green: #0faa7e;
        --tf-red: #dc2626; --tf-amber: #e8930a;
        --tf-text-h: #1a2140; --tf-text-b: #3d4f72; --tf-text-m: #7e90b0;
    }

    .mfg-page {
        background: var(--tf-bg);
        min-height: 100vh;
        padding: 16px;
    }

    @media (min-width: 1024px) {
        .mfg-page { padding: 26px 22px; }
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .mfg-section { animation: fadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .mfg-section:nth-child(1) { animation-delay: 0.04s; }

    .mfg-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }

    @media (min-width: 768px) {
        .mfg-header { margin-bottom: 24px; gap: 16px; }
    }

    .mfg-title {
        font-size: 18px;
        font-weight: 900;
        color: var(--tf-text-h);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    @media (min-width: 768px) {
        .mfg-title { font-size: 24px; gap: 12px; }
    }

    .mfg-title i { color: var(--tf-indigo); }

    .mfg-card {
        background: var(--tf-surface);
        border-radius: 16px;
        border: 1px solid var(--tf-border);
        overflow: hidden;
        margin-bottom: 16px;
    }

    @media (min-width: 768px) {
        .mfg-card { border-radius: 18px; margin-bottom: 20px; }
    }

    .mfg-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid var(--tf-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    @media (min-width: 768px) {
        .mfg-card-header { padding: 16px 22px; gap: 12px; }
    }

    .mfg-card-title {
        font-size: 14px;
        font-weight: 800;
        margin: 0;
    }

    @media (min-width: 768px) {
        .mfg-card-title { font-size: 16px; }
    }

    /* Table */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .mfg-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    @media (min-width: 640px) {
        .mfg-table { font-size: 12px; }
    }

    @media (min-width: 1024px) {
        .mfg-table { font-size: 14px; }
    }

    .mfg-table th {
        padding: 10px 12px;
        font-size: 10px;
        font-weight: 700;
        color: var(--tf-text-m);
        background: #f8faff;
        text-align: right;
        border-bottom: 1px solid var(--tf-border);
        white-space: nowrap;
    }

    @media (min-width: 640px) {
        .mfg-table th { padding: 12px 16px; font-size: 12px; }
    }

    .mfg-table td {
        padding: 10px 12px;
        font-size: 12px;
        color: var(--tf-text-b);
        border-bottom: 1px solid #f0f4f8;
    }

    @media (min-width: 640px) {
        .mfg-table td { padding: 12px 16px; font-size: 14px; }
    }

    /* Badge */
    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
    }

    @media (min-width: 640px) {
        .badge { padding: 4px 12px; font-size: 12px; }
    }

    .badge-draft { background: #fff4e0; color: #b45309; }
    .badge-confirmed { background: #e6f8f3; color: #047857; }
    .badge-completed { background: #dbeafe; color: #1e40af; }
    .badge-cancelled { background: #fee2e2; color: #dc2626; }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 12px;
        border: none;
        cursor: pointer;
        transition: all .3s;
        text-decoration: none;
    }

    @media (min-width: 768px) {
        .btn { padding: 10px 20px; font-size: 14px; gap: 8px; }
    }

    .btn-primary { background: var(--tf-indigo); color: #fff; }
    .btn-primary:hover { background: #3b52c0; }

    .btn-sm {
        padding: 6px 10px;
        font-size: 11px;
        border-radius: 8px;
    }

    @media (min-width: 640px) {
        .btn-sm { padding: 6px 12px; font-size: 12px; }
    }

    .btn-green { background: var(--tf-green); color: #fff; }
    .btn-blue { background: var(--tf-blue); color: #fff; }
    .btn-red { background: var(--tf-red); color: #fff; }

    .btn-block { width: 100%; }

    /* Mobile card view */
    .mobile-card {
        display: block;
        background: #f8faff;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 12px;
        border: 1px solid var(--tf-border);
    }

    @media (min-width: 768px) {
        .mobile-card { display: none; }
    }

    /* Desktop table view */
    .desktop-table {
        display: none;
    }

    @media (min-width: 768px) {
        .desktop-table { display: block; }
    }

    /* Pagination */
    .pagination {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination a,
    .pagination span {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }

    @media (min-width: 640px) {
        .pagination a,
        .pagination span { padding: 8px 16px; font-size: 14px; }
    }

    .pagination a {
        background: var(--tf-surface);
        color: var(--tf-text-h);
        border: 1px solid var(--tf-border);
    }

    .pagination a:hover {
        background: var(--tf-indigo);
        color: white;
    }

    .pagination .active {
        background: var(--tf-indigo);
        color: white;
    }

    /* Action buttons on mobile */
    .mobile-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
        flex-wrap: wrap;
    }

    @media (min-width: 768px) {
        .mobile-actions { display: none; }
    }

    /* Alert */
    .alert {
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 16px;
        font-weight: 700;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    @media (min-width: 768px) {
        .alert { padding: 14px 20px; margin-bottom: 20px; font-size: 14px; }
    }

    .alert-success { background: #e6f8f3; color: #047857; }
    .alert-error { background: #fee2e2; color: #dc2626; }

    /* FAB button */
    .fab {
        position: fixed;
        bottom: 20px;
        left: 20px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--tf-indigo);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(79,99,210,0.4);
        z-index: 100;
        font-size: 24px;
    }

    @media (min-width: 768px) {
        .fab { display: none; }
    }
</style>
@endpush

@section('content')
<div class="mfg-page">
    <div class="mfg-section">
        <div class="mfg-header">
            <h1 class="mfg-title">
                <i class="fas fa-industry"></i>
                أوامر التصنيع
            </h1>
            <div class="desktop-table">
                <a href="{{ route('manufacturing-orders.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> إنشاء أمر جديد
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
        @endif

        <div class="mfg-card">
            <div class="mfg-card-header">
                <h3 class="mfg-card-title">جميع الأوامر ({{ $orders->total() }})</h3>
            </div>

            <!-- Desktop Table -->
            <div class="desktop-table table-responsive">
                <table class="mfg-table">
                    <thead>
                        <tr>
                            <th>رقم الأمر</th>
                            <th>اسم المنتج</th>
                            <th>الكمية</th>
                            <th>التكلفة</th>
                            <th>سعر البيع</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td style="font-weight:700;">#{{ $order->id }}</td>
                            <td>{{ $order->product_name }}</td>
                            <td>{{ number_format($order->quantity_produced, 2) }}</td>
                            <td>{{ number_format($order->cost_per_unit, 2) }} ج.م</td>
                            <td>{{ number_format($order->selling_price_per_unit, 2) }} ج.م</td>
                            <td>
                                @if($order->status === 'draft')
                                    <span class="badge badge-draft">مسودة</span>
                                @elseif($order->status === 'confirmed')
                                    <span class="badge badge-confirmed">مؤكد</span>
                                @elseif($order->status === 'completed')
                                    <span class="badge badge-completed">مكتمل</span>
                                @elseif($order->status === 'cancelled')
                                    <span class="badge badge-cancelled">ملغي</span>
                                @endif
                            </td>
                            <td style="font-size:12px;">{{ $order->created_at->format('Y/m/d') }}</td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <a href="{{ route('manufacturing-orders.show', $order->id) }}" class="btn btn-sm btn-blue" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($order->can_edit)
                                    <a href="{{ route('manufacturing-orders.edit', $order->id) }}" class="btn btn-sm btn-primary" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:40px; color:var(--tf-text-m);">
                                <i class="fas fa-box-open" style="font-size:48px; margin-bottom:16px; display:block;"></i>
                                <p style="font-weight:700;">لا توجد أوامر تصنيع حالياً</p>
                                <a href="{{ route('manufacturing-orders.create') }}" class="btn btn-primary" style="margin-top:16px;">
                                    <i class="fas fa-plus"></i> إنشاء أمر جديد
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="mobile-card" style="padding: 16px;">
                @forelse($orders as $order)
                <div class="mobile-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <strong style="font-size:14px; color:var(--tf-indigo);">#{{ $order->id }}</strong>
                        @if($order->status === 'draft')
                            <span class="badge badge-draft">مسودة</span>
                        @elseif($order->status === 'confirmed')
                            <span class="badge badge-confirmed">مؤكد</span>
                        @elseif($order->status === 'completed')
                            <span class="badge badge-completed">مكتمل</span>
                        @elseif($order->status === 'cancelled')
                            <span class="badge badge-cancelled">ملغي</span>
                        @endif
                    </div>
                    <div style="font-size:13px; margin-bottom:6px;">
                        <strong>{{ $order->product_name }}</strong>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px; font-size:12px; margin-bottom:12px;">
                        <div>
                            <span style="color:var(--tf-text-m);">الكمية:</span>
                            <strong>{{ number_format($order->quantity_produced, 2) }}</strong>
                        </div>
                        <div>
                            <span style="color:var(--tf-text-m);">التكلفة:</span>
                            <strong>{{ number_format($order->cost_per_unit, 2) }} ج.م</strong>
                        </div>
                        <div>
                            <span style="color:var(--tf-text-m);">سعر البيع:</span>
                            <strong>{{ number_format($order->selling_price_per_unit, 2) }} ج.م</strong>
                        </div>
                        <div>
                            <span style="color:var(--tf-text-m);">التاريخ:</span>
                            <strong>{{ $order->created_at->format('Y/m/d') }}</strong>
                        </div>
                    </div>
                    <div class="mobile-actions">
                        <a href="{{ route('manufacturing-orders.show', $order->id) }}" class="btn btn-sm btn-blue" style="flex:1; justify-content:center;">
                            <i class="fas fa-eye"></i> عرض
                        </a>
                        @if($order->can_edit)
                        <a href="{{ route('manufacturing-orders.edit', $order->id) }}" class="btn btn-sm btn-primary" style="flex:1; justify-content:center;">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        @endif
                    </div>
                </div>
                @empty
                <div style="text-align:center; padding:30px; color:var(--tf-text-m);">
                    <i class="fas fa-box-open" style="font-size:36px; margin-bottom:12px; display:block;"></i>
                    <p style="font-weight:700;">لا توجد أوامر تصنيع</p>
                </div>
                @endforelse
            </div>
        </div>

        @if($orders->hasPages())
        <div style="padding:16px; border-top:1px solid var(--tf-border);">
            <div style="display:flex; flex-direction:column; gap:12px; align-items:center;">
                <div style="font-size:12px; color:var(--tf-text-m);">
                    عرض {{ $orders->firstItem() }} إلى {{ $orders->lastItem() }} من {{ $orders->total() }}
                </div>
                <div class="pagination">{{ $orders->links() }}</div>
            </div>
        </div>
        @endif
    </div>

    <!-- Floating Action Button for Mobile -->
    <a href="{{ route('manufacturing-orders.create') }}" class="fab">
        <i class="fas fa-plus"></i>
    </a>
</div>
@endsection
