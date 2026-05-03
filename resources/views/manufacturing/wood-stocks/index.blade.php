@extends('layouts.app')

@section('title', 'مخزون الخشب الخام')

@push('styles')
<style>
    :root {
        --tf-bg: #f4f7fe;
        --tf-surface: #ffffff;
        --tf-border: #e4eaf7;
        --tf-indigo: #4f63d2;
        --tf-blue: #3a8ef0;
        --tf-green: #0faa7e;
        --tf-red: #dc2626;
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
    }

    .tf-card-body {
        padding: 20px;
    }

    .tf-grid {
        display: grid;
        gap: 20px;
        margin-bottom: 20px;
    }

    @media (min-width: 640px) {
        .tf-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (min-width: 1024px) {
        .tf-grid { grid-template-columns: repeat(4, 1fr); }
    }

    .tf-stat {
        text-align: center;
        padding: 20px;
        background: var(--tf-surface);
        border-radius: 16px;
        border: 1px solid var(--tf-border);
    }

    .tf-stat-label {
        font-size: 12px;
        color: var(--tf-text-m);
        margin-bottom: 8px;
        font-weight: 600;
    }

    .tf-stat-value {
        font-size: 24px;
        font-weight: 900;
        color: var(--tf-indigo);
    }

    .tf-table {
        width: 100%;
        border-collapse: collapse;
    }

    .tf-table th {
        padding: 12px 16px;
        text-align: right;
        font-size: 11px;
        font-weight: 700;
        color: var(--tf-text-m);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--tf-border);
        background: #f8faff;
    }

    .tf-table td {
        padding: 14px 16px;
        font-size: 13px;
        color: var(--tf-text-b);
        border-bottom: 1px solid var(--tf-border);
    }

    .tf-table tbody tr:hover {
        background: #f8faff;
    }

    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-secondary {
        background: #e5e7eb;
        color: #374151;
    }
</style>
@endpush

@section('content')
<div class="tf-page">
    {{-- Header --}}
    <div class="tf-header">
        <div>
            <h1 class="tf-title">
                <i class="fas fa-layer-group" style="color: var(--tf-indigo);"></i>
                مخزون الخشب الخام
            </h1>
            <p style="color: var(--tf-text-m); margin-top: 4px; font-size: 14px;">إدارة دفعات الخشب الخام وحركة الصرف</p>
        </div>
        <a href="{{ route('manufacturing.wood-stocks.create') }}" class="tf-btn tf-btn-primary">
            <i class="fas fa-plus"></i>
            إضافة دفعة جديدة
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="tf-grid">
        <div class="tf-stat">
            <div class="tf-stat-label">إجمالي المخزون m³</div>
            <div class="tf-stat-value">{{ number_format($summary['total_m3'], 2) }}</div>
        </div>
        <div class="tf-stat">
            <div class="tf-stat-label">المتاح m³</div>
            <div class="tf-stat-value" style="color: var(--tf-green);">{{ number_format($summary['remaining_m3'], 2) }}</div>
        </div>
        <div class="tf-stat">
            <div class="tf-stat-label">إجمالي m²</div>
            <div class="tf-stat-value" style="color: var(--tf-amber);">{{ number_format($summary['total_m2'], 2) }}</div>
        </div>
        <div class="tf-stat">
            <div class="tf-stat-label">عدد الدفعات</div>
            <div class="tf-stat-value" style="color: var(--tf-blue);">{{ $woodStocks->total() }}</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="tf-card">
        <div class="tf-card-head">
            <i class="fas fa-list"></i> قائمة دفعات الخشب
        </div>
        <div class="tf-card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="tf-table">
                    <thead>
                        <tr>
                            <th>رقم المحضر</th>
                            <th>المورد</th>
                            <th>الأبعاد (ط × ع × س)</th>
                            <th>الحجم الكلي m³</th>
                            <th>المتاح m³</th>
                            <th>المساحة m²</th>
                            <th>التكلفة</th>
                            <th>تاريخ الاستلام</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($woodStocks as $stock)
                        <tr>
                            <td>{{ $stock->purchase_reference ?? '-' }}</td>
                            <td>{{ $stock->supplier->name ?? 'غير محدد' }}</td>
                            <td dir="ltr">{{ $stock->length_cm }} × {{ $stock->width_cm }} × {{ $stock->thickness_cm }}</td>
                            <td>{{ number_format($stock->volume_m3_total, 4) }}</td>
                            <td>
                                @if($stock->remaining_m3 > 0)
                                    <span class="badge badge-success">{{ number_format($stock->remaining_m3, 4) }}</span>
                                @else
                                    <span class="badge badge-secondary">0</span>
                                @endif
                            </td>
                            <td>{{ number_format($stock->remaining_m2, 2) }}</td>
                            <td>{{ number_format($stock->total_cost, 2) }}</td>
                            <td>{{ $stock->received_at->format('Y-m-d') }}</td>
                            <td>
                                @if($stock->remaining_cm3 > 0)
                                    <a href="{{ route('manufacturing.wood-dispensings.create', $stock) }}"
                                       class="tf-btn tf-btn-primary"
                                       style="padding: 6px 14px; font-size: 12px;">
                                        <i class="fas fa-dolly"></i> صرف
                                    </a>
                                @else
                                    <span style="color: var(--tf-text-m); font-size: 12px;">نفذت الكمية</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: var(--tf-text-m);">
                                <i class="fas fa-box-open" style="font-size: 32px; margin-bottom: 12px; display: block; opacity: 0.5;"></i>
                                لا توجد دفعات خشب متاحة
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div style="padding: 20px;">
            {{ $woodStocks->links() }}
        </div>
    </div>
</div>
@endsection
