@extends('layouts.app')

@section('title', 'سجل صرف الخشب')

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

    .tf-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 10px;
        font-weight: 700;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 12px;
    }

    .tf-btn-primary {
        background: linear-gradient(135deg, #6366f1, #3b82f6);
        color: #fff;
    }

    .tf-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99,102,241,0.3);
        color: #fff;
    }

    .tf-btn-success {
        background: var(--tf-green);
        color: white;
    }

    .tf-btn-success:hover {
        background: #059669;
        color: white;
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="tf-page">
    {{-- Header --}}
    <div class="tf-header">
        <div>
            <h1 class="tf-title">
                <i class="fas fa-clipboard-list" style="color: var(--tf-indigo);"></i>
                سجل صرف الخشب
            </h1>
            <p style="color: var(--tf-text-m); margin-top: 4px; font-size: 14px;">تتبع جميع عمليات صرف الخشب</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="tf-card">
        <div class="tf-card-head">
            <i class="fas fa-list"></i> عمليات الصرف
        </div>
        <div class="tf-card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="tf-table">
                    <thead>
                        <tr>
                            <th>رقم الحركة</th>
                            <th>التاريخ</th>
                            <th>دفعة الخشب</th>
                            <th>الأبعاد</th>
                            <th>الكمية m³</th>
                            <th>الموظف</th>
                            <th>العميل</th>
                            <th>أمر التصنيع</th>
                            <th>الفاتورة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dispensings as $dispensing)
                        <tr>
                            <td>#{{ $dispensing->id }}</td>
                            <td>{{ $dispensing->dispensed_at->format('Y-m-d') }}</td>
                            <td>
                                @if($dispensing->woodStock)
                                    <span class="badge" style="background: #f0f0f0; color: #333;">
                                        {{ $dispensing->woodStock->purchase_reference ?? 'دفعة #' . $dispensing->woodStock->id }}
                                    </span>
                                @else
                                    <span style="color: var(--tf-text-m);">-</span>
                                @endif
                            </td>
                            <td dir="ltr">
                                @if($dispensing->woodStock)
                                    {{ $dispensing->woodStock->length_cm }}×{{ $dispensing->woodStock->width_cm }}×{{ $dispensing->woodStock->thickness_cm }} سم
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <strong style="color: var(--tf-indigo);">{{ round($dispensing->volume_cm3_taken / 1000000, 4) }}</strong> م³
                            </td>
                            <td>{{ $dispensing->user->name ?? '-' }}</td>
                            <td>{{ $dispensing->client->name ?? '-' }}</td>
                            <td>{{ $dispensing->manufacturingOrder->order_number ?? '-' }}</td>
                            <td>
                                @if($dispensing->client_id)
                                    @if($dispensing->sales_invoice_id)
                                        <a href="{{ route('invoices.sales.show', $dispensing->sales_invoice_id) }}"
                                           class="tf-btn tf-btn-success">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                            #{{ $dispensing->sales_invoice_id }}
                                        </a>
                                    @else
                                        <a href="{{ route('manufacturing.wood-dispensings.create-invoice', $dispensing) }}"
                                           class="tf-btn tf-btn-primary">
                                            <i class="fas fa-file-invoice"></i>
                                            إنشاء فاتورة
                                        </a>
                                    @endif
                                @else
                                    <span style="color: var(--tf-text-m); font-size: 12px;">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: var(--tf-text-m);">
                                <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 12px; display: block; opacity: 0.5;"></i>
                                لا توجد حركات صرف
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div style="padding: 20px;">
            {{ $dispensings->links() }}
        </div>
    </div>
</div>
@endsection
