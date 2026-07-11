@extends('layouts.app')

@section('title', 'دفاتر المواد الخام')
@section('page-title', 'مخزون المواد الخام')

@section('content')
<div class="mfg-page mfg-section">
    <div class="mfg-header">
        <h1 class="mfg-title">
            <i class="fas fa-boxes"></i>
            دفاتر المواد الخام (Batches)
        </h1>
        <a href="{{ route('material-batches.create') }}" class="btn btn-primary" style="background-color: var(--tf-indigo); border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; color: white;">
            <i class="fas fa-plus"></i> تسجيل دفعة جديدة
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="background: #e6f7ed; color: #0baa7e; border: 1px solid #c2ebd6; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: bold;">
            {{ session('success') }}
        </div>
    @endif

    <div class="mfg-card">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; text-align: right;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--tf-border); color: var(--tf-text-m); font-weight: bold; background: #fafbfe;">
                        <th style="padding: 14px 16px;">المادة الخام</th>
                        <th style="padding: 14px 16px;">الوحدة القياسية</th>
                        <th style="padding: 14px 16px;">الكمية المستلمة</th>
                        <th style="padding: 14px 16px;">الكمية المتبقية</th>
                        <th style="padding: 14px 16px;">تكلفة الوحدة</th>
                        <th style="padding: 14px 16px;">المخزن</th>
                        <th style="padding: 14px 16px;">المورد</th>
                        <th style="padding: 14px 16px;">تاريخ الاستلام</th>
                        <th style="padding: 14px 16px; text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        <tr style="border-bottom: 1px solid var(--tf-border); color: var(--tf-text-b);">
                            <td style="padding: 14px 16px; font-weight: bold; color: var(--tf-text-h);">{{ $batch->product->name ?? '—' }}</td>
                            <td style="padding: 14px 16px;">{{ $batch->uom->name ?? '—' }}</td>
                            <td style="padding: 14px 16px;">{{ number_format($batch->quantity, 2) }}</td>
                            <td style="padding: 14px 16px; font-weight: bold; color: {{ $batch->remaining_qty > 0 ? 'var(--tf-green)' : 'var(--tf-red)' }};">
                                {{ number_format($batch->remaining_qty, 2) }}
                            </td>
                            <td style="padding: 14px 16px;">{{ number_format($batch->unit_cost, 2) }} د.إ</td>
                            <td style="padding: 14px 16px;">{{ $batch->warehouse->name ?? '—' }}</td>
                            <td style="padding: 14px 16px;">{{ $batch->supplier->name ?? '—' }}</td>
                            <td style="padding: 14px 16px;">{{ $batch->received_at->format('Y-m-d') }}</td>
                            <td style="padding: 14px 16px; text-align: center;">
                                @if($batch->remaining_qty > 0)
                                    <a href="{{ route('material-dispensings.create', $batch->id) }}" class="btn btn-sm btn-outline-primary" style="border: 1px solid var(--tf-indigo); color: var(--tf-indigo); padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; text-decoration: none;">
                                        <i class="fas fa-hand-holding"></i> صرف تصنيع
                                    </a>
                                @else
                                    <span style="color: var(--tf-text-m); font-size: 12px;"><i class="fas fa-check-circle"></i> تم الصرف بالكامل</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding: 30px; text-align: center; color: var(--tf-text-m);">لا توجد دفعات مواد خام مسجلة حالياً.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())
            <div style="padding: 16px; border-top: 1px solid var(--tf-border);">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
