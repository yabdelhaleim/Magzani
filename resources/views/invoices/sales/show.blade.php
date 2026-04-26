@extends('layouts.app')

@section('title', 'عرض فاتورة مبيعات #' . $invoice->invoice_number)
@section('page-title', 'فاتورة مبيعات #' . $invoice->invoice_number)

@push('styles')
<style>
    :root {
        --tf-bg:          #f4f7fe;
        --tf-surface:     #ffffff;
        --tf-surface2:    #f8faff;
        --tf-border:      #e4eaf7;
        --tf-border-soft: #edf0f9;

        --tf-indigo:      #4f63d2;
        --tf-indigo-light:#7088e8;
        --tf-indigo-soft: #eef0fc;

        --tf-blue:        #3a8ef0;
        --tf-blue-soft:   #e8f2ff;
        --tf-green:       #0faa7e;
        --tf-green-soft:  #e6f8f3;
        --tf-red:         #dc2626;
        --tf-red-soft:    #fee2e2;
        --tf-amber:       #e8930a;
        --tf-amber-soft:  #fff4e0;
        --tf-violet:      #7c5cec;
        --tf-violet-soft: #f0ecff;

        --tf-text-h:      #1a2140;
        --tf-text-b:      #3d4f72;
        --tf-text-m:      #7e90b0;
        --tf-text-d:      #94a3b8;

        --tf-shadow-sm:   0 2px 12px rgba(79,99,210,0.07);
        --tf-shadow-card: 0 2px 0 0 rgba(0,0,0,0.04), 0 4px 20px rgba(79,99,210,0.08);
        --tf-shadow-lg:   0 8px 30px rgba(79,99,210,0.10);
    }

    .tf-page {
        background: var(--tf-bg);
        background-image:
            radial-gradient(ellipse 80% 60% at 10% -10%,  rgba(79,99,210,0.12) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(58,142,240,0.1) 0%, transparent 50%);
        min-height: 100vh;
        padding: 26px 22px;
    }

    @keyframes tfFadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes tfShimmer {
        0%   { background-position: -600px 0; }
        100% { background-position: 600px 0; }
    }
    @keyframes iconBounce {
        0%,100% { transform: translateY(0) rotate(0deg); }
        30%     { transform: translateY(-4px) rotate(-8deg); }
        60%     { transform: translateY(-2px) rotate(4deg); }
    }

    .tf-section { animation: tfFadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .tf-section:nth-child(1) { animation-delay: 0.04s; }
    .tf-section:nth-child(2) { animation-delay: 0.12s; }
    .tf-section:nth-child(3) { animation-delay: 0.20s; }

    .tf-card {
        background: var(--tf-surface); border-radius: 20px;
        border: 1px solid var(--tf-border);
        overflow: hidden; box-shadow: var(--tf-shadow-card);
        margin-bottom: 20px; position: relative;
        transition: all .35s cubic-bezier(.22,1,.36,1);
    }
    .tf-card:hover { transform: translateY(-3px); box-shadow: var(--tf-shadow-lg); }
    .tf-card::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.45) 50%, transparent 60%);
        background-size: 600px 100%; opacity: 0; pointer-events: none; transition: opacity .3s;
    }
    .tf-card:hover::after { opacity: 1; animation: tfShimmer .7s ease forwards; }

    .tf-card-head {
        display: flex; justify-content: space-between; align-items: center;
        padding: 20px 24px; border-bottom: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2); flex-wrap: wrap; gap: 12px;
    }
    .tf-card-title { display: flex; align-items: center; gap: 12px; }
    .tf-card-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        transition: transform .4s cubic-bezier(.34,1.56,.64,1);
    }
    .tf-card:hover .tf-card-icon { animation: iconBounce .6s ease; }
    .tf-card.blue .tf-card-icon { background: var(--tf-blue-soft); color: var(--tf-blue); }
    .tf-card.green .tf-card-icon { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-card.indigo .tf-card-icon { background: var(--tf-indigo-soft); color: var(--tf-indigo); }
    .tf-card.violet .tf-card-icon { background: var(--tf-violet-soft); color: var(--tf-violet); }

    .tf-title-text { font-size: 18px; font-weight: 800; color: var(--tf-text-h); }
    .tf-title-sub { font-size: 12px; color: var(--tf-text-m); font-weight: 600; }

    .tf-card-body { padding: 20px; }

    .tf-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 20px; border-radius: 14px;
        font-size: 14px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif; text-decoration: none;
        transition: all .25s; border: none;
    }
    .tf-btn-primary {
        background: linear-gradient(135deg, var(--tf-green), #0d8a6e);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(15,170,126,0.35);
    }
    .tf-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15,170,126,0.45); }
    .tf-btn-secondary {
        background: var(--tf-surface); color: var(--tf-text-b);
        border: 1.5px solid var(--tf-border);
    }
    .tf-btn-secondary:hover { background: var(--tf-surface2); }
    .tf-btn-blue {
        background: linear-gradient(135deg, var(--tf-blue), #2d7de0);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(58,142,240,0.35);
    }
    .tf-btn-blue:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(58,142,240,0.45); }

    .tf-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    @media (max-width: 900px) { .tf-grid-3 { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px) { .tf-grid-3 { grid-template-columns: 1fr; } }

    .tf-table-wrapper { overflow-x: auto; }
    .tf-table { width: 100%; border-collapse: collapse; }
    .tf-table thead th {
        padding: 14px 16px; text-align: right;
        font-size: 11px; font-weight: 800; color: var(--tf-text-m);
        text-transform: uppercase; letter-spacing: .5px;
        border-bottom: 1.5px solid var(--tf-border-soft);
        background: var(--tf-surface2); white-space: nowrap;
    }
    .tf-table tbody tr { transition: background .18s; }
    .tf-table tbody tr:hover { background: var(--tf-surface2); }
    .tf-table tbody td { padding: 14px 16px; border-bottom: 1px solid var(--tf-border-soft); vertical-align: middle; }

    .tf-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 50px;
        font-size: 11px; font-weight: 800;
    }
    .tf-badge.green  { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-badge.amber  { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .tf-badge.red    { background: var(--tf-red-soft); color: var(--tf-red); }
    .tf-badge.blue   { background: var(--tf-blue-soft); color: var(--tf-blue); }

    .tf-info-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 0; border-bottom: 1px solid var(--tf-border-soft);
    }
    .tf-info-row:last-child { border-bottom: none; }
    .tf-info-label { font-size: 14px; color: var(--tf-text-m); font-weight: 600; }
    .tf-info-value { font-size: 14px; font-weight: 800; color: var(--tf-text-h); }

    .tf-total-card {
        padding: 20px; border-radius: 16px;
        background: var(--tf-surface); border: 1px solid var(--tf-border);
    }
    .tf-total-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 0; border-bottom: 1px solid var(--tf-border-soft);
    }
    .tf-total-row:last-child { border-bottom: none; }
    .tf-total-label { font-size: 14px; color: var(--tf-text-m); font-weight: 600; }
    .tf-total-value { font-size: 14px; font-weight: 800; color: var(--tf-text-h); }
    .tf-total-value.green { color: var(--tf-green); }
    .tf-total-value.red { color: var(--tf-red); }
    .tf-total-value.blue { color: var(--tf-blue); }
    .tf-grand-total {
        font-size: 28px; font-weight: 900; color: var(--tf-green);
        text-shadow: 0 2px 4px rgba(15, 170, 126, 0.1);
    }

    /* تحسين شكل جدول الإجماليات */
    .totals-card {
        background: linear-gradient(135deg, #ffffff, #fcfdfe);
        border-radius: 24px;
        padding: 30px;
        border: 1px solid var(--tf-border);
        box-shadow: var(--tf-shadow-card);
    }
    .totals-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--tf-text-h);
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--tf-border-soft);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .total-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }
    .total-item label {
        font-size: 14px;
        font-weight: 600;
        color: var(--tf-text-m);
    }
    .total-item .value {
        font-size: 15px;
        font-weight: 800;
        color: var(--tf-text-h);
    }
    .grand-total-item {
        margin-top: 25px;
        padding-top: 20px;
        border-top: 2px dashed var(--tf-border-soft);
    }

    /* Footer Styles */
    .invoice-footer {
        margin-top: 50px;
        padding: 40px;
        text-align: center;
        border-top: 1px solid var(--tf-border-soft);
        color: var(--tf-text-m);
    }
    .footer-links {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-bottom: 20px;
    }
    .footer-links a {
        color: var(--tf-text-m);
        text-decoration: none;
        font-weight: 700;
        font-size: 13px;
        transition: color 0.2s;
    }
    .footer-links a:hover { color: var(--tf-indigo); }
    
    .powered-by {
        font-size: 12px;
        font-weight: 600;
        opacity: 0.8;
    }
    .powered-by span { color: var(--tf-indigo); font-weight: 800; }

    /* هيدر الشركة الاحترافي (للشاشة) */
    .company-invoice-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(to left, #ffffff, #f8faff);
        padding: 30px 40px;
        border-radius: 24px;
        margin-bottom: 30px;
        border: 1px solid var(--tf-border);
        box-shadow: var(--tf-shadow-sm);
        position: relative;
        overflow: hidden;
    }
    .company-invoice-header::before {
        content: '';
        position: absolute;
        top: 0; right: 0; width: 8px; height: 100%;
        background: linear-gradient(to bottom, var(--tf-indigo), var(--tf-blue));
    }
    .company-invoice-header::after {
        content: '\f571';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        left: -20px;
        bottom: -20px;
        font-size: 120px;
        color: rgba(79, 99, 210, 0.03);
        transform: rotate(-15deg);
        pointer-events: none;
    }
    .header-info h1 {
        font-size: 28px;
        font-weight: 900;
        color: var(--tf-text-h);
        margin: 0 0 8px 0;
        letter-spacing: -0.5px;
    }
    .header-info p {
        font-size: 14px;
        color: var(--tf-text-m);
        margin: 4px 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .header-logo img {
        max-height: 85px;
        filter: drop-shadow(0 10px 15px rgba(0,0,0,0.08));
    }
    .header-badge {
        background: white;
        color: var(--tf-indigo);
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 15px;
        box-shadow: 0 4px 12px rgba(79, 99, 210, 0.1);
        border: 1px solid var(--tf-indigo-soft);
    }

    .quick-setup-btn {
        position: absolute;
        top: 20px;
        left: 20px;
        width: 35px;
        height: 35px;
        background: white;
        border: 1px solid var(--tf-border);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--tf-text-m);
        cursor: pointer;
        transition: all 0.2s;
        z-index: 10;
    }
    .quick-setup-btn:hover {
        background: var(--tf-indigo);
        color: white;
        border-color: var(--tf-indigo);
        transform: rotate(45deg);
    }

    /* Modal Styles */
    .tf-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 20px;
    }
    .tf-modal {
        background: white;
        border-radius: 24px;
        width: 100%;
        max-width: 500px;
        box-shadow: var(--tf-shadow-lg);
        overflow: hidden;
        animation: tfFadeUp 0.3s ease-out;
    }
    .tf-modal-head {
        padding: 25px;
        background: var(--tf-surface2);
        border-bottom: 1px solid var(--tf-border-soft);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .tf-modal-body { padding: 25px; }
    .tf-modal-foot {
        padding: 20px 25px;
        background: var(--tf-surface2);
        border-top: 1px solid var(--tf-border-soft);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .tf-invoice-num {
        display: inline-block; padding: 8px 16px;
        border-radius: 50px; font-size: 14px; font-weight: 800;
        background: var(--tf-indigo-soft); color: var(--tf-indigo);
    }

    .tf-avatar {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 800; color: var(--tf-surface);
        background: linear-gradient(135deg, var(--tf-blue), var(--tf-indigo));
    }

    /* Print Styles */
    @media print {
        @page { size: A4; margin: 0.8cm; }
        body { background: white !important; font-size: 11pt !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .tf-page { padding: 0 !important; background: white !important; min-height: auto !important; }
        .no-print { display: none !important; }

        /* Remove shadows and animations */
        .tf-card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            margin-bottom: 12px !important;
            page-break-inside: avoid;
        }
        .tf-card-head {
            background: #f8f9fa !important;
            border-bottom: 2px solid #333 !important;
            padding: 12px 0 !important;
        }
        .tf-card-icon { display: none !important; }
        .tf-grid-3 {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 12px !important;
        }
        .tf-badge {
            border: 1px solid #999 !important;
            background: white !important;
            color: black !important;
            padding: 4px 10px !important;
        }
        .tf-table thead th {
            background: #e9ecef !important;
            color: black !important;
            border: 1px solid #999 !important;
            font-size: 9pt !important;
        }
        .tf-table td {
            border: 1px solid #ddd !important;
            font-size: 10pt !important;
        }

        /* Print Header */
        .print-header {
            display: flex !important;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 3px solid #333;
        }
        .print-logo {
            max-height: 70px !important;
            max-width: 200px !important;
            object-fit: contain !important;
        }
        .print-company-details {
            flex: 1;
            text-align: right;
        }
        .print-title {
            text-align: center;
            font-size: 20pt !important;
            font-weight: 900 !important;
            margin: 18px 0 !important;
            color: #1a1a1a !important;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .tf-total-card {
            border: 1px solid #ddd !important;
            page-break-inside: avoid;
        }
        .totals-card {
            page-break-inside: avoid;
        }

        /* Footer */
        .invoice-footer {
            margin-top: 30px !important;
            padding: 20px !important;
            border-top: 2px solid #333 !important;
            text-align: center !important;
        }

        /* Avoid page breaks */
        .tf-section { page-break-inside: avoid; }
        .tf-table-wrapper { page-break-inside: avoid; }
    }
    
    .print-only { display: none; }
    @media print { .print-only { display: block !important; } }
</style>
@endpush

@section('content')
<div class="tf-page" x-data="{ showConfigModal: false }">
    <!-- هيدر الشركة الاحترافي (للشاشة فقط) -->
    <div class="company-invoice-header tf-section no-print">
        <button type="button" @click="showConfigModal = true" class="quick-setup-btn" title="تعديل بيانات الفاتورة">
            <i class="fas fa-cog"></i>
        </button>

        <div class="header-info">
            <h1>{{ $company->name ?? 'نظام ماجزني لإدارة المخازن' }}</h1>
            <p><i class="fas fa-map-marker-alt" style="color: var(--tf-indigo);"></i> {{ $company->address ?? 'العنوان غير مسجل' }}</p>
            <p><i class="fas fa-phone" style="color: var(--tf-indigo);"></i> {{ $company->phone ?? '01XXXXXXXXX' }}</p>
            <div class="header-badge">
                <i class="fas fa-shield-alt"></i>
                نظام الفواتير المعتمد
            </div>
        </div>
        <div class="header-logo">
            @if(isset($company->logo) && $company->logo)
                <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo">
            @else
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--tf-indigo-soft), #e0e7ff); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-weight: 900; color: var(--tf-indigo); font-size: 32px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
                    {{ substr($company->name ?? 'M', 0, 1) }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Quick Setup -->
    <div x-show="showConfigModal" 
         class="tf-modal-backdrop" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        
        <div class="tf-modal" @click.away="showConfigModal = false">
            <div class="tf-modal-head">
                <h3 class="tf-title-text">تعديل بيانات الهوية</h3>
                <button type="button" @click="showConfigModal = false" class="tf-text-m hover:text-red-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="tf-modal-body">
                    <div style="margin-bottom: 20px;">
                        <label class="tf-label">اسم الشركة / النشاط</label>
                        <input type="text" name="name" value="{{ $company->name ?? '' }}" class="tf-input" required>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label class="tf-label">شعار الفاتورة (Logo)</label>
                        <input type="file" name="logo" class="tf-input" accept="image/*">
                        <p style="font-size: 11px; color: var(--tf-text-m); margin-top: 5px;">يفضل استخدام صورة شفافة PNG</p>
                    </div>
                    <div class="tf-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label class="tf-label">رقم الهاتف</label>
                            <input type="text" name="phone" value="{{ $company->phone ?? '' }}" class="tf-input">
                        </div>
                        <div>
                            <label class="tf-label">العنوان</label>
                            <input type="text" name="address" value="{{ $company->address ?? '' }}" class="tf-input">
                        </div>
                    </div>
                </div>
                <div class="tf-modal-foot">
                    <button type="button" @click="showConfigModal = false" class="tf-btn tf-btn-secondary">إلغاء</button>
                    <button type="submit" class="tf-btn tf-btn-primary">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Header الطباعة -->
    <div class="print-only">
        <div class="print-header">
            <div class="print-company-details">
                @if(isset($company->logo) && $company->logo)
                    <div style="text-align: center; margin-bottom: 15px;">
                        <img src="{{ asset('storage/' . $company->logo) }}" class="print-logo" alt="Logo" style="max-height: 90px;">
                    </div>
                @endif
                <h1 style="font-size: 32px; font-weight: 900; margin: 0 0 8px 0; color: #1a1a1a;">{{ $company->name ?? 'اسم الشركة' }}</h1>
                <p style="margin: 6px 0; color: #666; font-size: 13px;">{{ $company->address ?? 'العنوان غير مسجل' }}</p>
                <p style="margin: 6px 0; color: #666; font-size: 13px;">
                    هاتف: {{ $company->phone ?? '---' }}
                    @if($company->email) | {{ $company->email }} @endif
                </p>
                @if($company->tax_number || $company->commercial_register)
                <p style="margin: 6px 0; color: #666; font-size: 12px;">
                    @if($company->tax_number) الرقم الضريبي: {{ $company->tax_number }} @endif
                    @if($company->tax_number && $company->commercial_register) | @endif
                    @if($company->commercial_register) السجل التجاري: {{ $company->commercial_register }} @endif
                </p>
                @endif
            </div>
        </div>
        <div class="print-title">فاتورة مبيعات #{{ $invoice->invoice_number }}</div>
        <div style="text-align: center; margin: 15px 0; font-size: 13px; color: #888;">
            تاريخ الفاتورة: {{ $invoice->invoice_date->format('Y-m-d') }}
        </div>
    </div>

    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon blue"><i class="fas fa-file-invoice"></i></div>
                <div>
                    <h2 class="tf-title-text">فاتورة مبيعات #{{ $invoice->invoice_number }}</h2>
                    <p class="tf-title-sub">تاريخ: {{ $invoice->invoice_date->format('Y-m-d') }}</p>
                </div>
            </div>
            <div style="display: flex; gap: 10px;" class="no-print">
                <a href="{{ route('invoices.sales.index') }}" class="tf-btn tf-btn-secondary">
                    <i class="fas fa-arrow-right"></i> عودة
                </a>
                <a href="{{ route('invoices.sales.edit', $invoice->id) }}" class="tf-btn tf-btn-secondary">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <button onclick="window.print()" class="tf-btn tf-btn-blue">
                    <i class="fas fa-print"></i> طباعة
                </button>
            </div>
        </div>
    </div>

    <div class="tf-grid-3 tf-section">
        <div class="tf-card">
            <div class="tf-card-head">
                <div class="tf-card-title">
                    <div class="tf-card-icon green"><i class="fas fa-user"></i></div>
                    <div>
                        <h3 class="tf-title-text">بيانات العميل</h3>
                        <p class="tf-title-sub">معلومات العميل</p>
                    </div>
                </div>
            </div>
            <div class="tf-card-body">
                <div class="tf-info-row">
                    <span class="tf-info-label">الاسم:</span>
                    <span class="tf-info-value">{{ $invoice->customer->name ?? 'غير محدد' }}</span>
                </div>
                @if($invoice->customer && $invoice->customer->phone)
                <div class="tf-info-row">
                    <span class="tf-info-label">الهاتف:</span>
                    <span class="tf-info-value">{{ $invoice->customer->phone }}</span>
                </div>
                @endif
                @if($invoice->customer && $invoice->customer->email)
                <div class="tf-info-row">
                    <span class="tf-info-label">البريد:</span>
                    <span class="tf-info-value">{{ $invoice->customer->email }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="tf-card">
            <div class="tf-card-head">
                <div class="tf-card-title">
                    <div class="tf-card-icon indigo"><i class="fas fa-info-circle"></i></div>
                    <div>
                        <h3 class="tf-title-text">حالة الفاتورة</h3>
                        <p class="tf-title-sub">معلومات الفاتورة</p>
                    </div>
                </div>
            </div>
            <div class="tf-card-body">
                <div class="tf-info-row">
                    <span class="tf-info-label">الحالة:</span>
                    <span class="tf-badge {{ $invoice->payment_status == 'paid' ? 'green' : ($invoice->payment_status == 'partial' ? 'amber' : 'red') }}">
                        {{ $invoice->payment_status == 'paid' ? 'مدفوعة بالكامل' : ($invoice->payment_status == 'partial' ? 'مدفوعة جزئياً' : 'غير مدفوعة') }}
                    </span>
                </div>
                <div class="tf-info-row">
                    <span class="tf-info-label">تاريخ الإنشاء:</span>
                    <span class="tf-info-value">{{ $invoice->created_at->format('Y-m-d H:i') }}</span>
                </div>
                @if($invoice->creator)
                <div class="tf-info-row">
                    <span class="tf-info-label">أنشأها:</span>
                    <span class="tf-info-value">{{ $invoice->creator->name }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="tf-card">
            <div class="tf-card-head">
                <div class="tf-card-title">
                    <div class="tf-card-icon violet"><i class="fas fa-calculator"></i></div>
                    <div>
                        <h3 class="tf-title-text">الملخص المالي</h3>
                        <p class="tf-title-sub">تفاصيل المبالغ</p>
                    </div>
                </div>
            </div>
            <div class="tf-card-body">
                <div class="tf-info-row">
                    <span class="tf-info-label">الإجمالي:</span>
                    <span class="tf-info-value">{{ number_format($invoice->total, 2) }} ج.م</span>
                </div>
                <div class="tf-info-row">
                    <span class="tf-info-label">الخصم:</span>
                    <span class="tf-info-value red">{{ number_format($invoice->discount_amount ?? 0, 2) }} ج.م</span>
                </div>
                <div class="tf-info-row">
                    <span class="tf-info-label">الضريبة:</span>
                    <span class="tf-info-value blue">{{ number_format($invoice->tax_amount ?? 0, 2) }} ج.م</span>
                </div>
                <div class="tf-info-row">
                    <span class="tf-info-label">المدفوع:</span>
                    <span class="tf-info-value green">{{ number_format($invoice->paid ?? 0, 2) }} ج.م</span>
                </div>
                <div class="tf-info-row">
                    <span class="tf-info-label">الباقي:</span>
                    <span class="tf-info-value {{ $invoice->remaining > 0 ? 'red' : 'green' }}">
                        {{ number_format($invoice->remaining ?? 0, 2) }} ج.م
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="tf-card tf-section">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon blue"><i class="fas fa-list"></i></div>
                <div>
                    <h3 class="tf-title-text">تفاصيل الفاتورة</h3>
                    <p class="tf-title-sub">الأصناف المُنتجة</p>
                </div>
            </div>
        </div>
        <div class="tf-table-wrapper">
            <table class="tf-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المنتج</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>الخصم</th>
                        <th>الضريبة</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div style="font-weight: 700;">{{ $item->product?->name ?? 'منتج محذوف' }}</div>
                            @if($item->product?->sku)
                            <div style="font-size: 11px; color: var(--tf-text-m);">كود: {{ $item->product->sku }}</div>
                            @endif
                        </td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ number_format($item->unit_price, 2) }} ج.م</td>
                        <td style="color: var(--tf-red);">{{ number_format($item->discount_value ?? 0, 2) }} ج.م</td>
                        <td style="color: var(--tf-blue);">{{ number_format($item->tax_amount ?? 0, 2) }} ج.م</td>
                        <td style="font-weight: 800;">{{ number_format($item->total, 2) }} ج.م</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--tf-text-m);">
                            لا توجد عناصر في هذه الفاتورة
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background: var(--tf-surface2);">
                        <td colspan="6" style="font-weight: 800; text-align: left;">الإجمالي:</td>
                        <td style="font-weight: 900; font-size: 18px; color: var(--tf-green);">
                            {{ number_format($invoice->total, 2) }} ج.م
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="tf-grid-3 tf-section">
        <div class="totals-card">
            <h4 class="totals-title">
                <i class="fas fa-calculator" style="color: var(--tf-green);"></i> الحسابات
            </h4>
            <div class="total-item">
                <label>إجمالي المنتجات:</label>
                <span class="value">{{ number_format($invoice->total, 2) }} ج.م</span>
            </div>
            <div class="total-item">
                <label style="color: var(--tf-red);">إجمالي الخصم:</label>
                <span class="value red">{{ number_format($invoice->discount_amount ?? 0, 2) }} ج.م</span>
            </div>
            <div class="total-item">
                <label style="color: var(--tf-blue);">إجمالي الضريبة:</label>
                <span class="value blue">{{ number_format($invoice->tax_amount ?? 0, 2) }} ج.م</span>
            </div>
            <div class="grand-total-item">
                <div class="total-item" style="margin-bottom: 0;">
                    <label style="font-size: 16px; color: var(--tf-text-h);">الإجمالي النهائي:</label>
                    <span class="tf-grand-total">{{ number_format($invoice->total, 2) }} ج.م</span>
                </div>
            </div>
        </div>

        <div class="totals-card">
            <h4 class="totals-title">
                <i class="fas fa-wallet" style="color: var(--tf-blue);"></i> الدفع
            </h4>
            <div class="total-item">
                <label>المبلغ المدفوع:</label>
                <span class="value green" style="font-size: 18px;">{{ number_format($invoice->paid ?? 0, 2) }} ج.م</span>
            </div>
            <div style="margin-top: 20px;">
                <label class="tf-label">المبلغ المتبقي</label>
                <div style="font-size: 28px; font-weight: 900; padding: 15px; border-radius: 18px; text-align: center; transition: all 0.3s;
                     background: {{ ($invoice->remaining ?? 0) > 0 ? 'var(--tf-red-soft)' : 'var(--tf-green-soft)' }}; 
                     color: {{ ($invoice->remaining ?? 0) > 0 ? 'var(--tf-red)' : 'var(--tf-green)' }};">
                     {{ number_format($invoice->remaining ?? 0, 2) }} ج.م
                </div>
            </div>
            <div style="margin-top: 15px; text-align: center;">
                <span class="tf-badge {{ $invoice->payment_status == 'paid' ? 'green' : ($invoice->payment_status == 'partial' ? 'amber' : 'red') }}" style="padding: 8px 20px; font-size: 13px;">
                    {{ $invoice->payment_status == 'paid' ? 'مدفوعة بالكامل' : ($invoice->payment_status == 'partial' ? 'مدفوعة جزئياً' : 'غير مدفوعة') }}
                </span>
            </div>
        </div>

        <div class="totals-card">
            <h4 class="totals-title">
                <i class="fas fa-sticky-note" style="color: var(--tf-violet);"></i> ملاحظات الفاتورة
            </h4>
            <div style="min-height: 100px; color: var(--tf-text-m); font-size: 14px; line-height: 1.6;">
                {{ $invoice->notes ?: 'لا توجد ملاحظات مسجلة لهذه الفاتورة.' }}
            </div>
        </div>
    </div>

    @if($invoice->payments && $invoice->payments->count() > 0)
    <div class="tf-card tf-section no-print">
        <div class="tf-card-head">
            <div class="tf-card-title">
                <div class="tf-card-icon violet"><i class="fas fa-history"></i></div>
                <div>
                    <h3 class="tf-title-text">سجل المدفوعات</h3>
                    <p class="tf-title-sub">تتبع عمليات التحصيل</p>
                </div>
            </div>
        </div>
        <div class="tf-table-wrapper">
            <table class="tf-table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>المبلغ</th>
                        <th>طريقة الدفع</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $payment)
                    <tr>
                        <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                        <td style="font-weight: 800; color: var(--tf-green);">{{ number_format($payment->amount, 2) }} ج.م</td>
                        <td>{{ $payment->payment_method ?? 'نقدي' }}</td>
                        <td style="color: var(--tf-text-m);">{{ $payment->notes ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <footer class="invoice-footer tf-section">
        <div class="footer-links">
            <a href="#">الدعم الفني</a>
            <a href="#">سياسة الاستخدام</a>
            <a href="#">دليل المستخدم</a>
        </div>
        <p class="powered-by">تم التطوير بواسطة <span>نظام ماجزني الذكي</span> &copy; {{ date('Y') }}</p>
    </footer>
</div>
@endsection
