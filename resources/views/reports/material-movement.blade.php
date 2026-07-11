@extends('layouts.app')

@section('title', 'تقرير حركة المواد الخام')
@section('page-title', 'تقرير حركة المواد الخام')

@section('content')
<div class="mfg-page mfg-section">
    <div class="mfg-header">
        <h1 class="mfg-title">
            <i class="fas fa-arrows-spin"></i>
            حركات صرف وسحب المواد الخام
        </h1>
    </div>

    <!-- Table -->
    <div class="mfg-card">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; text-align: right;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--tf-border); color: var(--tf-text-m); font-weight: bold; background: #fafbfe;">
                        <th style="padding: 14px 16px;">التاريخ</th>
                        <th style="padding: 14px 16px;">المادة الخام</th>
                        <th style="padding: 14px 16px;">الكمية المصروفة</th>
                        <th style="padding: 14px 16px;">أمر التصنيع</th>
                        <th style="padding: 14px 16px;">الجهة المستلمة / المستخدم</th>
                        <th style="padding: 14px 16px;">ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $mov)
                        <tr style="border-bottom: 1px solid var(--tf-border); color: var(--tf-text-b);">
                            <td style="padding: 14px 16px;">{{ $mov['date']->format('Y-m-d') }}</td>
                            <td style="padding: 14px 16px; font-weight: bold; color: var(--tf-text-h);">{{ $mov['product_name'] }}</td>
                            <td style="padding: 14px 16px; font-weight: bold; color: var(--tf-red);">{{ number_format($mov['quantity'], 2) }} {{ $mov['uom_name'] }}</td>
                            <td style="padding: 14px 16px; font-weight: bold; color: var(--tf-indigo);">
                                @if($mov['order_number'])
                                    {{ $mov['order_number'] }}
                                @else
                                    <span style="color: var(--tf-text-m); font-style: italic; font-size: 13px;">صرف مباشر</span>
                                @endif
                            </td>
                            <td style="padding: 14px 16px;">{{ $mov['user_name'] ?: '—' }}</td>
                            <td style="padding: 14px 16px; color: var(--tf-text-m);">{{ $mov['notes'] ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 30px; text-align: center; color: var(--tf-text-m);">لا توجد حركات صرف مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
