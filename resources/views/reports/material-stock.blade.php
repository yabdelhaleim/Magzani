@extends('layouts.app')

@section('title', 'تقرير مخزون المواد الخام')
@section('page-title', 'تقرير مخزون المواد الخام')

@section('content')
<div class="mfg-page mfg-section">
    <div class="mfg-header">
        <h1 class="mfg-title">
            <i class="fas fa-chart-pie"></i>
            تقرير مخزون المواد الخام الحالي
        </h1>
    </div>

    <!-- Filters -->
    <div class="mfg-card" style="padding: 16px; margin-bottom: 20px; background: #fafbfe;">
        <form method="GET" action="{{ route('material-stock') }}" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <div>
                <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">المورد</label>
                <select name="supplier_id" style="padding: 8px; border: 1px solid var(--tf-border); border-radius: 6px;">
                    <option value="">كل الموردين</option>
                    @foreach($suppliers as $supp)
                        <option value="{{ $supp->id }}" {{ request('supplier_id') == $supp->id ? 'selected' : '' }}>{{ $supp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">المخزن</label>
                <select name="warehouse_id" style="padding: 8px; border: 1px solid var(--tf-border); border-radius: 6px;">
                    <option value="">كل المخازن</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="background: var(--tf-indigo); border: none; padding: 8px 16px; border-radius: 6px; color: white; font-weight: bold;">
                <i class="fas fa-filter"></i> تصفية
            </button>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
        <div class="mfg-card" style="padding: 20px; text-align: center;">
            <span style="color: var(--tf-text-m); font-size: 13px;">إجمالي الدفعات</span>
            <h3 style="font-size: 24px; font-weight: bold; margin: 6px 0; color: var(--tf-text-h);">{{ $summary['total_batches'] }}</h3>
        </div>
        <div class="mfg-card" style="padding: 20px; text-align: center;">
            <span style="color: var(--tf-text-m); font-size: 13px;">إجمالي الكمية المستلمة</span>
            <h3 style="font-size: 24px; font-weight: bold; margin: 6px 0; color: var(--tf-text-h);">{{ number_format($summary['total_qty'], 2) }}</h3>
        </div>
        <div class="mfg-card" style="padding: 20px; text-align: center;">
            <span style="color: var(--tf-text-m); font-size: 13px;">الكمية المتبقية حالياً</span>
            <h3 style="font-size: 24px; font-weight: bold; margin: 6px 0; color: var(--tf-green);">{{ number_format($summary['remaining_qty'], 2) }}</h3>
        </div>
        <div class="mfg-card" style="padding: 20px; text-align: center;">
            <span style="color: var(--tf-text-m); font-size: 13px;">القيمة المالية التقديرية</span>
            <h3 style="font-size: 24px; font-weight: bold; margin: 6px 0; color: var(--tf-text-h);">{{ number_format($summary['remaining_value'], 2) }} د.إ</h3>
        </div>
    </div>

    <!-- Table -->
    <div class="mfg-card">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; text-align: right;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--tf-border); color: var(--tf-text-m); font-weight: bold; background: #fafbfe;">
                        <th style="padding: 14px 16px;">رقم الدفعة</th>
                        <th style="padding: 14px 16px;">الصنف</th>
                        <th style="padding: 14px 16px;">المخزن</th>
                        <th style="padding: 14px 16px;">المورد</th>
                        <th style="padding: 14px 16px;">الكمية الأصلية</th>
                        <th style="padding: 14px 16px;">الكمية المتبقية</th>
                        <th style="padding: 14px 16px;">تكلفة الوحدة</th>
                        <th style="padding: 14px 16px;">قيمة المخزون المتبقي</th>
                        <th style="padding: 14px 16px;">تاريخ الاستلام</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        <tr style="border-bottom: 1px solid var(--tf-border); color: var(--tf-text-b);">
                            <td style="padding: 14px 16px;">#{{ $stock['id'] }}</td>
                            <td style="padding: 14px 16px; font-weight: bold; color: var(--tf-text-h);">{{ $stock['product_name'] }}</td>
                            <td style="padding: 14px 16px;">{{ $stock['warehouse_name'] }}</td>
                            <td style="padding: 14px 16px;">{{ $stock['supplier_name'] ?: '—' }}</td>
                            <td style="padding: 14px 16px;">{{ number_format($stock['total_qty'], 2) }} {{ $stock['uom_name'] }}</td>
                            <td style="padding: 14px 16px; font-weight: bold; color: var(--tf-green);">{{ number_format($stock['remaining_qty'], 2) }} {{ $stock['uom_name'] }}</td>
                            <td style="padding: 14px 16px;">{{ number_format($stock['unit_cost'], 2) }} د.إ</td>
                            <td style="padding: 14px 16px; font-weight: bold; color: var(--tf-text-h);">{{ number_format($stock['remaining_value'], 2) }} د.إ</td>
                            <td style="padding: 14px 16px;">{{ $stock['received_at']->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding: 30px; text-align: center; color: var(--tf-text-m);">لا توجد بيانات مطابقة للبحث.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
