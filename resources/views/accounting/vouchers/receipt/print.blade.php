<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>سند قبض - {{ $journalEntry->entry_number }}</title>
    <style>
        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            background: #fff;
            color: #333;
            margin: 20px;
            direction: rtl;
        }
        .container {
            border: 2px solid #333;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            border-radius: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            position: relative;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }
        .voucher-no {
            position: absolute;
            left: 0;
            bottom: 15px;
            font-weight: bold;
            font-size: 14px;
            font-family: monospace;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .details-table td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        .label {
            font-weight: bold;
            background-color: #f9f9f9;
            width: 25%;
        }
        .amount-box {
            font-size: 22px;
            font-weight: bold;
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
            color: #2e7d32;
            padding: 10px;
            text-align: center;
            border-radius: 4px;
            font-family: monospace;
            margin-bottom: 25px;
        }
        .signatures {
            margin-top: 40px;
            display: grid;
            grid-template-cols: 1fr 1fr;
            gap: 20px;
            text-align: center;
            font-size: 14px;
        }
        .sig-block {
            border-top: 1px dashed #999;
            padding-top: 10px;
            margin-top: 50px;
        }
        @media print {
            body { margin: 0; }
            .container { border: none; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="container">
        <div class="header">
            <h1>سند قبض نقدي / بنكي</h1>
            <p>RECEIPT VOUCHER</p>
            <div class="voucher-no">رقم السند: {{ $journalEntry->entry_number }}</div>
        </div>

        <div class="amount-box">
            المبلغ: {{ number_format($journalEntry->total_debit, 2) }} {{ \App\Models\AccountingSetting::value('default_currency') ?? 'SAR' }}
        </div>

        <table class="details-table">
            <tr>
                <td class="label">تاريخ السند</td>
                <td>{{ $journalEntry->entry_date->toDateString() }}</td>
            </tr>
            @if($journalEntry->reference)
                <tr>
                    <td class="label">رقم المرجع (الشيك/التحويل)</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $journalEntry->reference }}</td>
                </tr>
            @endif
            <tr>
                <td class="label">استلمنا من الحساب</td>
                <td>
                    @php
                        // الحساب الدائن (المصدر) هو السطر الثاني في سندات القبض
                        $creditLine = $journalEntry->lines->firstWhere('credit', '>', 0);
                    @endphp
                    {{ $creditLine ? $creditLine->account->code . ' - ' . $creditLine->account->name_ar : '-' }}
                </td>
            </tr>
            <tr>
                <td class="label">وأودع في الحساب</td>
                <td>
                    @php
                        // الحساب المدين (المستلم) هو السطر الأول
                        $debitLine = $journalEntry->lines->firstWhere('debit', '>', 0);
                    @endphp
                    {{ $debitLine ? $debitLine->account->code . ' - ' . $debitLine->account->name_ar : '-' }}
                </td>
            </tr>
            <tr>
                <td class="label">البيان والشرح</td>
                <td>{{ $journalEntry->description }}</td>
            </tr>
        </table>

        <div class="signatures">
            <div>
                المستلم / أمين الصندوق
                <div class="sig-block"></div>
            </div>
            <div>
                المدير المالي / المعتمد
                <div class="sig-block"></div>
            </div>
        </div>
    </div>

</body>
</html>
