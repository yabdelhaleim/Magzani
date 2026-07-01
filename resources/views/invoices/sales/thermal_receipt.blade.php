<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة رقم {{ $invoice->invoice_number }}</title>
    
    <!-- Google Fonts: Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Cairo', sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #ffffff;
            color: #000000;
            width: 80mm;
            padding: 4mm;
            font-size: 11px;
            line-height: 1.4;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .header {
            margin-bottom: 5mm;
            border-bottom: 1px dashed #000;
            padding-bottom: 4mm;
        }
        .company-name {
            font-size: 16px;
            font-weight: 900;
            margin-bottom: 1mm;
        }
        .company-info {
            font-size: 9px;
            color: #333;
            margin-bottom: 0.5mm;
        }
        .invoice-title {
            font-size: 12px;
            font-weight: 700;
            margin: 3mm 0 1mm;
            border: 1px solid #000;
            padding: 1px 4px;
            display: inline-block;
        }
        .details-table {
            width: 100%;
            margin-bottom: 4mm;
            font-size: 10px;
        }
        .details-table td {
            padding: 0.5mm 0;
        }
        .details-label {
            font-weight: 700;
            width: 35%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4mm;
            font-size: 10px;
        }
        .items-table th, .items-table td {
            padding: 1.5mm 0;
            text-align: right;
        }
        .items-table th {
            border-bottom: 1px solid #000;
            border-top: 1px solid #000;
            font-weight: 700;
        }
        .items-table td {
            border-bottom: 1px dashed #ddd;
        }
        .item-name {
            font-weight: 700;
            word-break: break-all;
        }
        .item-meta {
            font-size: 8.5px;
            color: #444;
        }
        .summary {
            border-top: 1px dashed #000;
            padding-top: 2mm;
            margin-bottom: 5mm;
            font-size: 10.5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5mm 0;
        }
        .summary-row.total {
            font-size: 13px;
            font-weight: 900;
            border-top: 1px double #000;
            border-bottom: 1px double #000;
            padding: 1.5mm 0;
            margin-top: 1mm;
        }
        .footer {
            border-top: 1px dashed #000;
            padding-top: 4mm;
            margin-top: 4mm;
            font-size: 9.5px;
        }
        .qr-code {
            margin-top: 3mm;
            display: flex;
            justify-content: center;
        }
        
        /* Print styling rules */
        @media print {
            body {
                width: 80mm;
                padding: 2mm;
            }
            @page {
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Header / Store Info -->
    <div class="header text-center">
        <h1 class="company-name">{{ $company->name ?? 'مخزني' }}</h1>
        @if(!empty($company->address))
            <p class="company-info">{{ $company->address }}</p>
        @endif
        @if(!empty($company->phone))
            <p class="company-info">تليفون: {{ $company->phone }}</p>
        @endif
        @if(!empty($company->vat_number))
            <p class="company-info">السجل الضريبي: {{ $company->vat_number }}</p>
        @endif
        
        <div class="invoice-title">فاتورة مبيعات مبسطة</div>
    </div>

    <!-- Invoice and Customer Details -->
    <table class="details-table">
        <tr>
            <td class="details-label">رقم الفاتورة:</td>
            <td>{{ $invoice->invoice_number }}</td>
        </tr>
        <tr>
            <td class="details-label">تاريخ البيع:</td>
            <td style="direction: ltr; text-align: right;">{{ $invoice->invoice_date->format('Y-m-d h:i A') }}</td>
        </tr>
        <tr>
            <td class="details-label">الكاشير:</td>
            <td>{{ $invoice->createdBy->name ?? 'مدير النظام' }}</td>
        </tr>
        <tr>
            <td class="details-label">المستودع:</td>
            <td>{{ $invoice->warehouse->name }}</td>
        </tr>
        <tr>
            <td class="details-label">العميل:</td>
            <td>{{ $invoice->customer->name }}</td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">الصنف</th>
                <th style="width: 25%; text-align: center;">الكمية</th>
                <th style="width: 25%; text-align: left;">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <span class="item-name">{{ $item->product->name }}</span>
                        @if($item->discount_amount > 0)
                            <div class="item-meta">خصم صنف: {{ number_format($item->discount_amount, 2) }} ج.م</div>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        {{ floatval($item->quantity) }} 
                        <span class="item-meta">{{ $item->unit_code }}</span>
                        <div class="item-meta">x {{ number_format($item->unit_price, 2) }}</div>
                    </td>
                    <td style="text-align: left; font-weight: 700;">
                        {{ number_format($item->total, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Financial Summary -->
    <div class="summary">
        <div class="summary-row">
            <span>المجموع الفرعي:</span>
            <span>{{ number_format($invoice->subtotal, 2) }} ج.م</span>
        </div>
        @if($invoice->discount_amount > 0)
            <div class="summary-row">
                <span>إجمالي الخصم:</span>
                <span>-{{ number_format($invoice->discount_amount, 2) }} ج.م</span>
            </div>
        @endif
        @if($invoice->tax_amount > 0)
            <div class="summary-row">
                <span>الضريبة ({{ floatval($invoice->tax_rate) }}%):</span>
                <span>+{{ number_format($invoice->tax_amount, 2) }} ج.م</span>
            </div>
        @endif
        @if($invoice->shipping_cost > 0)
            <div class="summary-row">
                <span>تكلفة الشحن:</span>
                <span>+{{ number_format($invoice->shipping_cost, 2) }} ج.m</span>
            </div>
        @endif
        @if($invoice->other_charges > 0)
            <div class="summary-row">
                <span>رسوم إضافية:</span>
                <span>+{{ number_format($invoice->other_charges, 2) }} ج.م</span>
            </div>
        @endif
        
        <div class="summary-row total">
            <span>المطلوب (الإجمالي):</span>
            <span>{{ number_format($invoice->total, 2) }} ج.م</span>
        </div>
        
        <div class="summary-row" style="margin-top: 1.5mm; font-weight: 700;">
            <span>المدفوع:</span>
            <span>{{ number_format($invoice->paid, 2) }} ج.م</span>
        </div>
        
        <div class="summary-row" style="color: #444;">
            <span>المتبقي (الآجل):</span>
            <span>{{ number_format(max(0, $invoice->total - $invoice->paid), 2) }} ج.م</span>
        </div>
    </div>

    <!-- Footer message -->
    <div class="footer text-center">
        <div class="qr-code" style="margin-bottom: 4mm;">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode("المتجر: " . ($company->name ?? 'مخزني') . "\nرقم الفاتورة: " . $invoice->invoice_number . "\nالتاريخ: " . $invoice->invoice_date->format('Y-m-d H:i') . "\nالإجمالي: " . number_format($invoice->total, 2) . " ج.م") }}" alt="QR Code" style="width: 30mm; height: 30mm; display: block; margin: 0 auto;" />
        </div>
        <p>شكراً لتعاملكم معنا لزيارتكم القادمة!</p>
        <p style="margin-top: 1mm; font-size: 8.5px; color: #555;">برمجيات مخزني السحابية Magzany SaaS</p>
        
        <!-- Auto trigger print script -->
        <script>
            window.onload = function() {
                window.print();
                // Optional: Auto close the print window after print dialog is closed
                window.onafterprint = function() {
                    window.close();
                }
            }
        </script>
    </div>

</body>
</html>
