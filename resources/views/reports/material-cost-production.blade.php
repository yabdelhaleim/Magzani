@extends('layouts.app')

@section('title', 'تحليل تكلفة المواد الخام في التصنيع')
@section('page-title', 'تكلفة التصنيع')

@section('content')
<div class="mfg-page mfg-section">
    <div class="mfg-header">
        <h1 class="mfg-title">
            <i class="fas fa-sack-dollar"></i>
            تحليل تكلفة المواد الخام المباشرة في أوامر التصنيع
        </h1>
    </div>

    <!-- Table -->
    <div class="mfg-card">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; text-align: right;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--tf-border); color: var(--tf-text-m); font-weight: bold; background: #fafbfe;">
                        <th style="padding: 14px 16px;">أمر التصنيع</th>
                        <th style="padding: 14px 16px;">المنتج المصنع</th>
                        <th style="padding: 14px 16px;">الكمية المنتجة</th>
                        <th style="padding: 14px 16px;">عدد السحوبات</th>
                        <th style="padding: 14px 16px;">إجمالي الكمية المستهلكة</th>
                        <th style="padding: 14px 16px;">تكلفة المواد المباشرة</th>
                        <th style="padding: 14px 16px;">إجمالي تكلفة الأمر</th>
                        <th style="padding: 14px 16px;">نسبة المواد من التكلفة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report as $row)
                        <tr style="border-bottom: 1px solid var(--tf-border); color: var(--tf-text-b);">
                            <td style="padding: 14px 16px; font-weight: bold; color: var(--tf-indigo);">{{ $row['order_number'] }}</td>
                            <td style="padding: 14px 16px; font-weight: bold; color: var(--tf-text-h);">{{ $row['product_name'] }}</td>
                            <td style="padding: 14px 16px;">{{ number_format($row['quantity_produced'], 2) }} unit</td>
                            <td style="padding: 14px 16px;">{{ $row['batches_used'] }} سحب</td>
                            <td style="padding: 14px 16px;">{{ number_format($row['total_qty'], 2) }}</td>
                            <td style="padding: 14px 16px; font-weight: bold; color: var(--tf-red);">{{ number_format($row['material_cost'], 2) }} د.إ</td>
                            <td style="padding: 14px 16px;">{{ number_format($row['total_cost'], 2) }} د.إ</td>
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-weight: bold;">{{ $row['material_cost_percentage'] }}%</span>
                                    <div style="flex: 1; background: #e4eaf7; height: 6px; border-radius: 3px; min-width: 60px; overflow: hidden;">
                                        <div style="background: var(--tf-indigo); width: {{ min(100, $row['material_cost_percentage']) }}%; height: 100%;"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding: 30px; text-align: center; color: var(--tf-text-m);">لا توجد حركات إنتاج مسجلة حالياً.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
