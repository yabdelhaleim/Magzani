<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>قيد يومية - {{ $journalEntry->entry_number }}</title>
    <style>
        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            background: #fff;
            color: #333;
            margin: 20px;
            direction: rtl;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }
        .meta-grid {
            display: grid;
            grid-template-cols: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .meta-item span {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 13px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: right;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .signatures {
            margin-top: 50px;
            display: grid;
            grid-template-cols: 1fr 1fr 1fr;
            gap: 20px;
            text-align: center;
            font-size: 14px;
        }
        .sig-block {
            border-top: 1px dashed #999;
            padding-top: 10px;
            margin-top: 40px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h1>سند قيد اليومية العام</h1>
        <p>{{ $journalEntry->entry_number }}</p>
    </div>

    <div class="meta-grid">
        <div class="meta-item">
            <span>تاريخ القيد:</span> {{ $journalEntry->entry_date->toDateString() }}
        </div>
        <div class="meta-item">
            <span>المرجع:</span> {{ $journalEntry->reference ?? 'بدون مرجع' }}
        </div>
        <div class="meta-item" style="grid-column: span 2;">
            <span>البيان العام:</span> {{ $journalEntry->description }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">رمز الحساب</th>
                <th style="width: 35%;">اسم الحساب</th>
                <th style="width: 15%; text-align: center;">مدين (+)</th>
                <th style="width: 15%; text-align: center;">دائن (-)</th>
                <th style="width: 20%;">البيان التفصيلي</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $sumD = 0; 
                $sumC = 0; 
            @endphp
            @foreach($journalEntry->lines as $line)
                <tr>
                    <td>{{ $line->account->code }}</td>
                    <td>{{ $line->account->name_ar }}</td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}
                    </td>
                    <td>{{ $line->description ?? '-' }}</td>
                </tr>
                @php
                    $sumD += $line->debit;
                    $sumC += $line->credit;
                @endphp
            @endforeach
            <tr class="total-row">
                <td colspan="2" style="text-align: left;">الإجمالي:</td>
                <td style="text-align: center;">{{ number_format($sumD, 2) }}</td>
                <td style="text-align: center;">{{ number_format($sumC, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="signatures">
        <div>
            منشئ القيد
            <div class="sig-block"></div>
        </div>
        <div>
            المحاسب المسؤول
            <div class="sig-block"></div>
        </div>
        <div>
            المدير المالي / المعتمد
            <div class="sig-block"></div>
        </div>
    </div>

</body>
</html>
