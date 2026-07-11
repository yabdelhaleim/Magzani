@extends('layouts.app')

@section('title', 'حركات صرف المواد الخام')
@section('page-title', 'صرف التصنيع')

@section('content')
<div class="mfg-page mfg-section">
    <div class="mfg-header">
        <h1 class="mfg-title">
            <i class="fas fa-hand-holding-hand"></i>
            حركات صرف المواد الخام للتصنيع
        </h1>
        <a href="{{ route('material-batches.index') }}" class="btn btn-outline" style="border: 1px solid var(--tf-border); padding: 10px 18px; border-radius: 8px; font-weight: bold; color: var(--tf-text-b); text-decoration: none;">
            <i class="fas fa-arrow-right"></i> الذهاب للمخزون المتاح للصرف
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
                        <th style="padding: 14px 16px;">المادة الخام الصادرة</th>
                        <th style="padding: 14px 16px;">الكمية المصروفة</th>
                        <th style="padding: 14px 16px;">الوحدة القياسية</th>
                        <th style="padding: 14px 16px;">أمر التصنيع</th>
                        <th style="padding: 14px 16px;">تاريخ الصرف</th>
                        <th style="padding: 14px 16px;">ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dispensings as $disp)
                        <tr style="border-bottom: 1px solid var(--tf-border); color: var(--tf-text-b);">
                            <td style="padding: 14px 16px; font-weight: bold; color: var(--tf-text-h);">{{ $disp->batch->product->name ?? '—' }}</td>
                            <td style="padding: 14px 16px;">{{ number_format($disp->quantity_taken, 2) }}</td>
                            <td style="padding: 14px 16px;">{{ $disp->batch->uom->name ?? '—' }}</td>
                            <td style="padding: 14px 16px;">
                                @if($disp->manufacturingOrder)
                                    <span style="font-weight: bold; color: var(--tf-indigo);">{{ $disp->manufacturingOrder->order_number }}</span>
                                @else
                                    <span style="color: var(--tf-text-m); font-style: italic;">سحب عام (خارج أمر تصنيع)</span>
                                @endif
                            </td>
                            <td style="padding: 14px 16px;">{{ $disp->dispensed_at->format('Y-m-d') }}</td>
                            <td style="padding: 14px 16px; color: var(--tf-text-m);">{{ $disp->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 30px; text-align: center; color: var(--tf-text-m);">لا توجد حركات صرف مسجلة حالياً.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($dispensings->hasPages())
            <div style="padding: 16px; border-top: 1px solid var(--tf-border);">
                {{ $dispensings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
