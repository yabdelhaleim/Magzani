<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أذن إدخال بضاعة - {{ $order->order_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                font-size: 12px;
            }
            .page-break {
                page-break-before: always;
            }
        }

        body {
            background: #f5f5f5;
        }

        .print-container {
            background: white;
            max-width: 210mm;
            margin: 20px auto;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .print-header {
            border-bottom: 3px double #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-logo {
            max-height: 80px;
            max-width: 200px;
        }

        .document-title {
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            margin: 20px 0;
        }

        .info-box {
            background: #f8f9fa;
            border-right: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        table {
            font-size: 11px;
        }

        .table th {
            background: #f8f9fa;
            border-top: 2px solid #dee2e6;
            font-weight: bold;
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            font-weight: bold;
        }

        @page {
            margin: 15mm;
        }
    </style>
</head>
<body>
    <div class="no-print mb-3 text-center">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i>
            طباعة الأذن
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            إغلاق
        </button>
    </div>

    <div class="print-container">
        <!-- رأس الصفحة -->
        <div class="print-header">
            <div class="row align-items-center">
                @if(session('company_logo'))
                <div class="col-2">
                    <img src="{{ session('company_logo') }}" alt="شعار الشركة" class="company-logo">
                </div>
                @endif
                <div class="@if(session('company_logo')) col-10 @else col-12 @endif">
                    <h2 class="mb-1">{{ session('company_name', 'اسم الشركة') }}</h2>
                    @if(session('company_address'))
                    <p class="mb-1 text-muted">{{ session('company_address') }}</p>
                    @endif
                    @if(session('company_tax_number'))
                    <p class="mb-0 text-muted">الرقم الضريبي: {{ session('company_tax_number') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- عنوان الوثيقة -->
        <div class="document-title">
            <h2 class="mb-0">
                <i class="fas fa-arrow-down"></i>
                أذن إدخال بضاعة
            </h2>
            <p class="mb-0 mt-2">رقم: {{ $order->order_number }}</p>
        </div>

        <!-- معلومات الأذن -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="info-box">
                    <div class="row">
                        <div class="col-6"><strong>التاريخ:</strong></div>
                        <div class="col-6">{{ $order->order_date->format('Y-m-d') }}</div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6"><strong>المخزن:</strong></div>
                        <div class="col-6">{{ $order->warehouse->name }}</div>
                    </div>
                    @if($order->reference_number)
                    <div class="row mt-2">
                        <div class="col-6"><strong>رقم المرجع:</strong></div>
                        <div class="col-6">{{ $order->reference_number }}</div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <div class="row">
                        <div class="col-6"><strong>الحالة:</strong></div>
                        <div class="col-6">
                            @if($order->status === 'completed')
                            <span class="status-badge" style="background: #d4edda; color: #155724;">مكتمل</span>
                            @elseif($order->status === 'pending')
                            <span class="status-badge" style="background: #fff3cd; color: #856404;">معلق</span>
                            @else
                            <span class="status-badge" style="background: #f8d7da; color: #721c24;">ملغي</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6"><strong>عدد الأصناف:</strong></div>
                        <div class="col-6">{{ $order->items->count() }}</div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6"><strong>تم الإنشاء بواسطة:</strong></div>
                        <div class="col-6">{{ $order->creator->name }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- جدول الأصناف -->
        <h5 class="mb-3">
            <i class="fas fa-boxes text-warning"></i>
            الأصناف المستلمة
        </h5>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصنف</th>
                    <th>الكمية</th>
                    <th>الوحدة</th>
                    @if($order->items->whereNotNull('unit_cost')->isNotEmpty())
                    <th>التكلفة</th>
                    <th>الإجمالي</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ number_format($item->quantity, 3) }}</td>
                    <td>{{ $item->unit }}</td>
                    @if($order->items->whereNotNull('unit_cost')->isNotEmpty())
                    <td>{{ $item->unit_cost ? number_format($item->unit_cost, 2) : '-' }}</td>
                    <td>{{ $item->total_cost ? number_format($item->total_cost, 2) : '-' }}</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
            @if($order->items->whereNotNull('total_cost')->isNotEmpty())
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">الإجمالي:</th>
                    <th>{{ number_format($order->items->sum('total_cost'), 2) }}</th>
                </tr>
            </tfoot>
            @endif
        </table>

        <!-- الملاحظات -->
        @if($order->notes)
        <div class="info-box mt-4">
            <strong><i class="fas fa-sticky-note"></i> ملاحظات:</strong>
            <p class="mb-0 mt-2">{{ $order->notes }}</p>
        </div>
        @endif

        <!-- التوقيعات -->
        <div class="row mt-5 pt-4">
            <div class="col-md-4">
                <div class="text-center">
                    <p class="mb-4">أمين المخزن</p>
                    <div style="border-top: 1px solid #333; width: 200px; margin: 0 auto;"></div>
                    <p class="mb-0 mt-2">التوقيع / الختم</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <p class="mb-4">مدير المستودع</p>
                    <div style="border-top: 1px solid #333; width: 200px; margin: 0 auto;"></div>
                    <p class="mb-0 mt-2">التوقيع / الختم</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <p class="mb-4">المستلم</p>
                    <div style="border-top: 1px solid #333; width: 200px; margin: 0 auto;"></div>
                    <p class="mb-0 mt-2">التوقيع / الختم</p>
                </div>
            </div>
        </div>

        <!-- تذييل الصفحة -->
        <div class="mt-5 pt-3 border-top text-center text-muted">
            <p class="mb-0">تم إصدار هذا الأذن بواسطة نظام إدارة المخازن</p>
            <p class="mb-0">تاريخ الإصدار: {{ $order->created_at->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
