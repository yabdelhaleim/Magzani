<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة جرد #{{ $stock_count->count_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
            background: #f3f4f6;
            color: #1a1a1a;
            line-height: 1.6;
            font-size: 12pt;
        }

        .print-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 25px 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        /* هيدر التقرير */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4f63d2;
        }

        .company-info h1 {
            font-size: 24pt;
            font-weight: 900;
            color: #4f63d2;
            margin: 0 0 10px 0;
        }

        .company-info p {
            margin: 4px 0;
            font-size: 10pt;
            color: #666;
        }

        .company-logo {
            max-width: 120px;
            max-height: 80px;
        }

        /* عنوان التقرير */
        .report-title {
            text-align: center;
            margin: 30px 0;
        }

        .report-title h2 {
            font-size: 20pt;
            font-weight: 900;
            color: #1a1a1a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .report-title p {
            font-size: 12pt;
            color: #666;
            margin-top: 5px;
        }

        /* معلومات الجرد */
        .info-section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 25px 0;
            padding: 20px;
            background: #f9fafb;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }

        .info-box {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 9pt;
            color: #666;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 12pt;
            font-weight: 800;
            color: #1a1a1a;
        }

        /* الإحصائيات */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin: 25px 0;
        }

        .stat-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }

        .stat-label {
            font-size: 9pt;
            color: #666;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 18pt;
            font-weight: 900;
            color: #4f63d2;
        }

        .stat-card.success .stat-value { color: #0faa7e; }
        .stat-card.warning .stat-value { color: #e8930a; }
        .stat-card.danger .stat-value { color: #dc2626; }

        /* الفروقات المالية */
        .financial-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }

        .financial-box {
            border-radius: 10px;
            padding: 15px;
            border: 2px solid;
            text-align: center;
        }

        .financial-box.surplus {
            background: #d1fae5;
            border-color: #0faa7e;
        }

        .financial-box.shortage {
            background: #fee2e2;
            border-color: #dc2626;
        }

        .financial-label {
            font-size: 10pt;
            color: #065f46;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .financial-box.shortage .financial-label {
            color: #991b1b;
        }

        .financial-value {
            font-size: 20pt;
            font-weight: 900;
        }

        .financial-box.surplus .financial-value { color: #0faa7e; }
        .financial-box.shortage .financial-value { color: #dc2626; }

        /* الجدول */
        .table-section {
            margin: 25px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            page-break-inside: auto;
        }

        thead {
            background: #4f63d2;
            color: white;
        }

        th {
            padding: 12px 8px;
            text-align: center;
            font-weight: 800;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #4f63d2;
        }

        td {
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .product-name {
            font-weight: 800;
            font-size: 10pt;
            color: #1a1a1a;
        }

        .product-code {
            font-size: 8pt;
            color: #888;
        }

        .variance-positive {
            color: #0faa7e;
            font-weight: 900;
        }

        .variance-negative {
            color: #dc2626;
            font-weight: 900;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-counted { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-adjusted { background: #dbeafe; color: #1e40af; }

        /* التذييل */
        .footer-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            font-size: 9pt;
            color: #666;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 80px;
        }

        .signature-box {
            width: 30%;
            text-align: center;
        }

        .signature-line {
            border-top: 2px solid #1a1a1a;
            margin-top: 80px;
            padding-top: 10px;
            font-weight: 700;
        }

        /* أزرار الشاشة */
        .screen-controls {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #4f63d2;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-size: 14pt;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .print-btn:hover {
            background: #3a4fb8;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(79,99,210,0.3);
        }

        .print-btn svg {
            width: 24px;
            height: 24px;
        }

        /* تحسينات الطباعة */
        @media print {
            @page {
                size: A4;
                margin: 15mm 20mm;
            }

            body {
                background: white;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .print-container {
                box-shadow: none;
                margin: 0;
                padding: 10px;
                max-width: 100%;
            }

            .screen-controls {
                display: none !important;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .stat-card,
            .info-section,
            .financial-summary {
                page-break-inside: avoid;
            }
        }

        @media screen {
            body {
                background: #f3f4f6;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- أزرار الشاشة -->
        <div class="screen-controls">
            <button onclick="window.print()" class="print-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                طباعة التقرير
            </button>
        </div>

        <!-- هيدر الطباعة -->
        <div class="report-header">
            <div class="company-info">
                <h1>{{ $company->name ?? 'نظام ماجزني لإدارة المخازن' }}</h1>
                <p>{{ $company->address ?? 'العنوان غير مسجل' }}</p>
                <p>هاتف: {{ $company->phone ?? '---' }}</p>
                @if($company->commercial_register)
                    <p>سجل تجاري: {{ $company->commercial_register }}</p>
                @endif
            </div>
            <div class="company-logo">
                @if(isset($company->logo) && $company->logo)
                    <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" class="company-logo">
                @else
                    <div style="width: 120px; height: 80px; background: linear-gradient(135deg, #4f63d2, #7088e8); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 900; color: white; font-size: 24px;">M</div>
                @endif
            </div>
        </div>

        <!-- عنوان التقرير -->
        <div class="report-title">
            <h2>تقرير جرد المخزون</h2>
            <p>رقم الجرد: {{ $stock_count->count_number }}</p>
        </div>

        <!-- معلومات الجرد -->
        <div class="info-section">
            <div class="info-box">
                <span class="info-label">رقم الجرد</span>
                <span class="info-value">{{ $stock_count->count_number }}</span>
            </div>
            <div class="info-box">
                <span class="info-label">المخزن</span>
                <span class="info-value">{{ $stock_count->warehouse->name ?? 'غير محدد' }}</span>
            </div>
            <div class="info-box">
                <span class="info-label">تاريخ الجرد</span>
                <span class="info-value">{{ $stock_count->count_date->format('Y-m-d') }}</span>
            </div>
            <div class="info-box">
                <span class="info-label">نوع الجرد</span>
                <span class="info-value">{{ $stock_count->count_type == 'full' ? 'جرد كامل' : 'جرد جزئي' }}</span>
            </div>
            <div class="info-box">
                <span class="info-label">الحالة</span>
                <span class="info-value">
                    @if($stock_count->status == 'completed')
                        <span style="color: #0faa7e; font-weight: 900;">✓ مكتمل</span>
                    @elseif($stock_count->status == 'in_progress')
                        <span style="color: #e8930a; font-weight: 900;">⟳ قيد التنفيذ</span>
                    @elseif($stock_count->status == 'cancelled')
                        <span style="color: #dc2626; font-weight: 900;">✕ ملغى</span>
                    @else
                        <span style="color: #666; font-weight: 900;">○ مسودة</span>
                    @endif
                </span>
            </div>
            <div class="info-box">
                <span class="info-label">تاريخ الإنشاء</span>
                <span class="info-value">{{ $stock_count->created_at->format('Y-m-d H:i') }}</span>
            </div>
            @if($stock_count->creator)
            <div class="info-box">
                <span class="info-label">المنشئ</span>
                <span class="info-value">{{ $stock_count->creator->name }}</span>
            </div>
            @endif
            @if($stock_count->notes)
            <div class="info-box" style="grid-column: span 2;">
                <span class="info-label">ملاحظات</span>
                <span class="info-value">{{ $stock_count->notes }}</span>
            </div>
            @endif
        </div>

        <!-- الإحصائيات -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-label">إجمالي الأصناف</div>
                <div class="stat-value">{{ $summary['total_items'] }}</div>
            </div>
            <div class="stat-card success">
                <div class="stat-label">تم الجرد</div>
                <div class="stat-value">{{ $summary['items_counted'] }}</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-label">الفروقات</div>
                <div class="stat-value">{{ $summary['items_with_variance'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">نسبة الإنجاز</div>
                <div class="stat-value">{{ number_format($summary['progress_percentage'], 1) }}%</div>
            </div>
        </div>

        <!-- الفروقات المالية -->
        <div class="financial-summary">
            <div class="financial-box surplus">
                <div class="financial-label">إجمالي الفائض</div>
                <div class="financial-value">+{{ number_format($summary['total_surplus'], 2) }}</div>
            </div>
            <div class="financial-box shortage">
                <div class="financial-label">إجمالي العجز</div>
                <div class="financial-value">-{{ number_format($summary['total_shortage'], 2) }}</div>
            </div>
        </div>

        <!-- جدول الأصناف -->
        <div class="table-section">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المنتج</th>
                        <th>كود المنتج</th>
                        <th>كمية النظام</th>
                        <th>الكمية الفعلية</th>
                        <th>الفرق</th>
                        <th>الحالة</th>
                        @if($stock_count->count_type == 'partial')
                        <th>ملاحظات</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="text-align: right;">
                            <div class="product-name">{{ $item->product->name ?? 'منتج محذوف' }}</div>
                            <div class="product-code">{{ $item->product->sku ?? $item->product->code ?? '' }}</div>
                        </td>
                        <td>{{ number_format($item->system_quantity, 2) }}</td>
                        <td style="font-weight: 900; color: #4f63d2;">
                            {{ $item->actual_quantity !== null ? number_format($item->actual_quantity, 2) : '-' }}
                        </td>
                        <td>
                            @if($item->variance != 0)
                                <span class="{{ $item->variance > 0 ? 'variance-positive' : 'variance-negative' }}">
                                    {{ $item->variance > 0 ? '+' : '' }}{{ number_format($item->variance, 2) }}
                                </span>
                            @else
                                <span style="color: #666;">0.00</span>
                            @endif
                        </td>
                        <td>
                            @if($item->status == 'counted')
                                <span class="status-badge status-counted">تم الجرد</span>
                            @elseif($item->status == 'adjusted')
                                <span class="status-badge status-adjusted">تمت التسوية</span>
                            @else
                                <span class="status-badge status-pending">معلق</span>
                            @endif
                        </td>
                        @if($stock_count->count_type == 'partial')
                        <td style="max-width: 150px; word-wrap: break-word; font-size: 9pt; color: #666;">
                            {{ $item->notes ?? '-' }}
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- التذييل -->
        <div class="footer-section">
            <div>تم إنشاء هذا التقرير في: {{ now()->format('Y-m-d H:i:s') }}</div>
            <div>رقم الجرد: {{ $stock_count->count_number }}</div>
        </div>

        <!-- توقيعات -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">مسؤول المخزن</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">مدير المخازن</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">المراجع المالي</div>
            </div>
        </div>
    </div>

    <script>
        // زر الطباعة يعمل عند النقر (مسموح به كإجراء مستخدم)
        function printReport() {
            window.print();
        }
    </script>
</body>
</html>
