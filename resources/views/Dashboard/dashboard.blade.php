@extends('layouts.app')

@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Tajawal:wght@400;500;700;800&display=swap');

    :root {
        --bg:           #f4f7fe;
        --bg2:          #eef1fa;
        --surface:      #ffffff;
        --surface2:     #f8faff;
        --border:       #e4eaf7;
        --border-soft:  #edf0f9;

        --indigo:       #4f63d2;
        --indigo-light: #7088e8;
        --indigo-soft:  #eef0fc;

        --blue:         #3a8ef0;
        --blue-soft:    #e8f2ff;

        --emerald:      #0faa7e;
        --emerald-soft: #e6f8f3;

        --violet:       #7c5cec;
        --violet-soft:  #f0ecff;

        --amber:        #e8930a;
        --amber-soft:   #fff4e0;

        --rose:         #dc2626;
        --rose-soft:    #fee2e2;

        --pink:         #d446a2;
        --pink-soft:    #fdf0f9;

        --teal:         #0ba5a0;
        --teal-soft:    #e6f7f7;

        --text-h:       #1a2140;
        --text-b:       #3d4f72;
        --text-m:       #7e90b0;
        --text-d:       #b4bed4;

        --shadow-sm:    0 2px 12px rgba(79,99,210,0.07);
        --shadow-md:    0 8px 30px rgba(79,99,210,0.10);
        --shadow-lg:    0 16px 48px rgba(79,99,210,0.13);
        --shadow-card:  0 2px 0 0 rgba(0,0,0,0.04), 0 4px 20px rgba(79,99,210,0.08);
    }

    * { box-sizing: border-box; }

    body {
        font-family: 'Cairo', 'Tajawal', sans-serif;
        direction: rtl;
        background: var(--bg);
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(22px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes shimmerLight {
        0%   { background-position: -600px 0; }
        100% { background-position: 600px 0; }
    }
    @keyframes beatDot {
        0%,100% { transform: scale(1);    box-shadow: 0 0 0 0 rgba(232,75,90,0.5); }
        50%     { transform: scale(1.25); box-shadow: 0 0 0 7px rgba(232,75,90,0); }
    }
    @keyframes floatBadge {
        0%,100% { transform: translateY(0); }
        50%     { transform: translateY(-2px); }
    }
    @keyframes barSlide { from { width: 0; } }
    @keyframes iconBounce {
        0%,100% { transform: translateY(0) rotate(0deg); }
        30%     { transform: translateY(-4px) rotate(-8deg); }
        60%     { transform: translateY(-2px) rotate(4deg); }
    }

    .dash-section { animation: fadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .dash-section:nth-child(1) { animation-delay: 0.04s; }
    .dash-section:nth-child(2) { animation-delay: 0.13s; }
    .dash-section:nth-child(3) { animation-delay: 0.22s; }
    .dash-section:nth-child(4) { animation-delay: 0.31s; }

    .dash-page {
        background: var(--bg);
        background-image:
            radial-gradient(ellipse 80% 60% at 10% -10%,  rgba(79,99,210,0.12) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(58,142,240,0.1) 0%, transparent 50%);
        min-height: 100vh;
        padding: 26px 22px;
    }

    /* ── Stat Cards ── */
    .stat-card {
        background: var(--surface);
        border-radius: 20px;
        border: 1px solid var(--border);
        padding: 22px 20px 18px;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-card);
        transition: transform .35s cubic-bezier(.22,1,.36,1), box-shadow .35s ease;
    }
    .stat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 3px;
        border-radius: 20px 20px 0 0;
    }
    .stat-card-bg-icon {
        position: absolute; bottom: -8px; left: -6px;
        font-size: 72px; line-height: 1; opacity: .045;
        pointer-events: none; transform: rotate(-10deg);
        transition: opacity .35s, transform .5s;
    }
    .stat-card:hover .stat-card-bg-icon { opacity: .08; transform: rotate(-5deg) scale(1.05); }
    .stat-card::after {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.45) 50%, transparent 60%);
        background-size: 600px 100%; opacity: 0; pointer-events: none; transition: opacity .3s;
    }
    .stat-card:hover::after { opacity: 1; animation: shimmerLight .7s ease forwards; }

    .stat-card.c-blue::before   { background: linear-gradient(90deg,#3a8ef0,#6cbfff); }
    .stat-card.c-green::before  { background: linear-gradient(90deg,#0faa7e,#34d399); }
    .stat-card.c-violet::before { background: linear-gradient(90deg,#7c5cec,#a78bfa); }
    .stat-card.c-amber::before  { background: linear-gradient(90deg,#e8930a,#fbbf24); }

    .stat-icon {
        width: 50px; height: 50px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 19px; flex-shrink: 0;
        transition: transform .4s cubic-bezier(.34,1.56,.64,1);
    }
    .stat-card:hover .stat-icon { animation: iconBounce .6s ease; }
    .stat-card.c-blue   .stat-icon { background: var(--blue-soft);    color: var(--blue); }
    .stat-card.c-green  .stat-icon { background: var(--emerald-soft); color: var(--emerald); }
    .stat-card.c-violet .stat-icon { background: var(--violet-soft);  color: var(--violet); }
    .stat-card.c-amber  .stat-icon { background: var(--amber-soft);   color: var(--amber); }

    .stat-label-row { display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 1; }
    .stat-tag {
        font-size: 9px; font-weight: 800; letter-spacing: .8px;
        text-transform: uppercase; padding: 3px 8px; border-radius: 50px;
    }
    .stat-card.c-blue   .stat-tag { background: var(--blue-soft);    color: var(--blue); }
    .stat-card.c-green  .stat-tag { background: var(--emerald-soft); color: var(--emerald); }
    .stat-card.c-violet .stat-tag { background: var(--violet-soft);  color: var(--violet); }
    .stat-card.c-amber  .stat-tag { background: var(--amber-soft);   color: var(--amber); }

    .stat-val {
        font-size: 28px; font-weight: 900; color: var(--text-h);
        letter-spacing: -1px; line-height: 1.1; margin-top: 14px;
        position: relative; z-index: 1; font-family: 'Cairo', sans-serif;
    }
    .stat-lbl { font-size: 12px; font-weight: 700; color: var(--text-m); margin-top: 5px; position: relative; z-index: 1; }

    /* ── Mini Cards ── */
    .mini-card {
        background: var(--surface); border-radius: 16px; border: 1px solid var(--border);
        padding: 16px 18px; display: flex; align-items: center; gap: 14px;
        box-shadow: var(--shadow-sm); transition: transform .3s ease, box-shadow .3s ease;
        position: relative; overflow: hidden;
    }
    .mini-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .mini-card::before {
        content: ''; position: absolute; bottom: 0; left: 0; right: 0;
        height: 2px; border-radius: 0 0 16px 16px;
    }
    .mini-card.c-rose::before  { background: linear-gradient(90deg,var(--rose),#fb7185); }
    .mini-card.c-amber::before { background: linear-gradient(90deg,var(--amber),#fbbf24); }
    .mini-card.c-pink::before  { background: linear-gradient(90deg,var(--pink),#f472b6); }
    .mini-card.c-teal::before  { background: linear-gradient(90deg,var(--teal),#2dd4bf); }

    .mini-icon {
        width: 46px; height: 46px; border-radius: 13px;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px; flex-shrink: 0;
    }
    .mini-card.c-rose  .mini-icon { background: var(--rose-soft);  color: var(--rose); }
    .mini-card.c-amber .mini-icon { background: var(--amber-soft); color: var(--amber); }
    .mini-card.c-pink  .mini-icon { background: var(--pink-soft);  color: var(--pink); }
    .mini-card.c-teal  .mini-icon { background: var(--teal-soft);  color: var(--teal); }

    .mini-val { font-size: 22px; font-weight: 900; line-height: 1; font-family: 'Cairo', sans-serif; }
    .mini-lbl { font-size: 10px; font-weight: 700; color: var(--text-m); margin-top: 4px; text-transform: uppercase; letter-spacing: .3px; }
    .mini-card.c-rose  .mini-val { color: var(--rose); }
    .mini-card.c-amber .mini-val { color: var(--amber); }
    .mini-card.c-pink  .mini-val { color: var(--pink); }
    .mini-card.c-teal  .mini-val { color: var(--teal); }

    /* ── Section Cards ── */
    .section-card {
        background: var(--surface); border-radius: 22px; border: 1px solid var(--border);
        overflow: hidden; box-shadow: var(--shadow-card); transition: box-shadow .3s ease;
    }
    .section-card:hover { box-shadow: var(--shadow-lg); }

    .section-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 18px 22px; border-bottom: 1px solid var(--border-soft); background: var(--surface2);
    }
    .section-head-left { display: flex; align-items: center; gap: 13px; }
    .section-icon {
        width: 40px; height: 40px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px; flex-shrink: 0;
    }
    .section-icon.blue   { background: var(--blue-soft);    color: var(--blue); }
    .section-icon.green  { background: var(--emerald-soft); color: var(--emerald); }
    .section-icon.red    { background: var(--rose-soft);    color: var(--rose); }
    .section-icon.purple { background: var(--violet-soft);  color: var(--violet); }
    .section-icon.amber  { background: var(--amber-soft);   color: var(--amber); }

    .section-title { font-size: 15px; font-weight: 800; color: var(--text-h); margin: 0; font-family: 'Cairo', sans-serif; }
    .section-sub   { font-size: 11px; color: var(--text-m); margin: 3px 0 0; font-weight: 600; }

    .section-link {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 11px; font-weight: 800; color: var(--indigo); text-decoration: none;
        padding: 6px 14px; border-radius: 50px;
        border: 1px solid rgba(79,99,210,.18); background: var(--indigo-soft);
        transition: all .25s ease; letter-spacing: .2px;
    }
    .section-link:hover {
        background: var(--indigo); color: #fff; border-color: var(--indigo);
        box-shadow: 0 6px 20px rgba(79,99,210,.3); transform: translateY(-1px);
    }

    /* ── Table ── */
    .dash-table { width: 100%; border-collapse: collapse; }
    .dash-table thead th {
        padding: 12px 20px; text-align: right; font-size: 10px; font-weight: 800;
        color: var(--text-m); text-transform: uppercase; letter-spacing: .7px;
        border-bottom: 1px solid var(--border-soft); background: var(--surface2);
    }
    .dash-table tbody td { padding: 15px 20px; font-size: 13px; color: var(--text-b); border-bottom: 1px solid var(--border-soft); }
    .dash-table tbody tr { transition: background .18s ease; }
    .dash-table tbody tr:hover { background: #f6f8fe; }
    .dash-table tbody tr:hover td { color: var(--text-h); }
    .dash-table tbody tr:last-child td { border-bottom: none; }

    .inv-ref {
        font-weight: 800; color: var(--indigo); font-size: 13px; font-family: 'Cairo', sans-serif;
        background: var(--indigo-soft); padding: 3px 10px; border-radius: 7px;
        border: 1px solid rgba(79,99,210,.12);
    }

    /* ── Badges ── */
    .badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 11px; border-radius: 50px; font-size: 10px; font-weight: 800; letter-spacing: .2px;
    }
    .badge-paid    { background: var(--emerald-soft); color: var(--emerald); border: 1px solid rgba(15,170,126,.2); }
    .badge-pending { background: var(--amber-soft);   color: var(--amber);   border: 1px solid rgba(232,147,10,.2); animation: floatBadge 2.5s ease-in-out infinite; }
    .badge-gray    { background: #f1f4f9; color: var(--text-m); border: 1px solid var(--border); }

    /* ── Alert Items ── */
    .alert-item {
        display: flex; align-items: center; gap: 12px; padding: 14px;
        border-radius: 14px; border: 1px solid rgba(232,75,90,.12);
        background: #fff; box-shadow: 0 2px 8px rgba(232,75,90,.05);
        transition: all .25s ease; margin-bottom: 10px;
    }
    .alert-item:last-child { margin-bottom: 0; }
    .alert-item:hover {
        background: var(--rose-soft); border-color: rgba(232,75,90,.28);
        transform: translateX(-3px); box-shadow: 0 4px 16px rgba(232,75,90,.1);
    }
    .alert-dot {
        width: 9px; height: 9px; border-radius: 50%; background: var(--rose);
        flex-shrink: 0; animation: beatDot 2s ease-in-out infinite;
    }
    .alert-name { font-size: 13px; font-weight: 800; color: var(--text-h); font-family: 'Cairo', sans-serif; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .alert-wh   { font-size: 10px; color: var(--text-m); font-weight: 600; margin-top: 2px; }
    .alert-qty-row { display: flex; justify-content: space-between; font-size: 10px; font-weight: 800; margin-top: 6px; }

    .progress-track { height: 4px; background: rgba(0,0,0,.06); border-radius: 10px; overflow: hidden; margin-top: 8px; }
    .progress-fill  {
        height: 100%; border-radius: 10px;
        animation: barSlide 1.2s cubic-bezier(.22,1,.36,1) both; position: relative;
    }
    .progress-fill::after {
        content: ''; position: absolute; top: 0; right: 0;
        width: 20px; height: 100%; background: rgba(255,255,255,.6); filter: blur(3px);
    }

    /* ── Quick Actions ── */
    .quick-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; }
    .quick-btn {
        display: flex; flex-direction: column; align-items: center; gap: 11px;
        padding: 22px 10px 18px; border-radius: 18px; background: var(--surface2);
        border: 1px solid var(--border); text-decoration: none;
        transition: all .35s cubic-bezier(.22,1,.36,1);
        position: relative; overflow: hidden;
    }
    .quick-btn::before {
        content: ''; position: absolute; inset: 0; opacity: 0; transition: opacity .35s ease;
    }
    .quick-btn.qb-blue::before   { background: radial-gradient(circle at center,rgba(58,142,240,.1) 0%,transparent 70%); }
    .quick-btn.qb-green::before  { background: radial-gradient(circle at center,rgba(15,170,126,.1) 0%,transparent 70%); }
    .quick-btn.qb-amber::before  { background: radial-gradient(circle at center,rgba(232,147,10,.1) 0%,transparent 70%); }
    .quick-btn.qb-violet::before { background: radial-gradient(circle at center,rgba(124,92,236,.1) 0%,transparent 70%); }
    .quick-btn.qb-pink::before   { background: radial-gradient(circle at center,rgba(212,70,162,.1) 0%,transparent 70%); }
    .quick-btn.qb-teal::before   { background: radial-gradient(circle at center,rgba(11,165,160,.1) 0%,transparent 70%); }
    .quick-btn.qb-indigo::before { background: radial-gradient(circle at center,rgba(79,99,210,.1) 0%,transparent 70%); }
    .quick-btn.qb-rose::before   { background: radial-gradient(circle at center,rgba(232,75,90,.1) 0%,transparent 70%); }

    .quick-btn:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); background: var(--surface); border-color: var(--border); }
    .quick-btn:hover::before { opacity: 1; }

    .quick-btn-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center; font-size: 18px;
        transition: transform .4s cubic-bezier(.34,1.56,.64,1); position: relative; z-index: 1;
    }
    .quick-btn:hover .quick-btn-icon { transform: scale(1.12) rotate(-7deg); }

    .quick-btn-label {
        font-size: 11px; font-weight: 800; color: var(--text-b); text-align: center;
        line-height: 1.3; transition: color .25s; position: relative; z-index: 1;
        font-family: 'Cairo', sans-serif;
    }
    .quick-btn:hover .quick-btn-label { color: var(--indigo); }

    /* ── Empty State ── */
    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 48px 24px; gap: 10px; }
    .empty-icon  { width: 62px; height: 62px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 22px; }

    /* ── Scrollbar ── */
    .custom-scroll { scrollbar-width: thin; scrollbar-color: #d4dcf0 transparent; }
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-track { background: transparent; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #d4dcf0; border-radius: 10px; }

    @media (max-width: 768px) {
        .dash-page { padding: 16px 12px; }
        .quick-grid { grid-template-columns: repeat(2,1fr); }
        .stat-val { font-size: 22px; }
        .mini-val { font-size: 18px; }
    }
</style>
@endpush

@section('content')
<div class="dash-page">

    {{-- Primary Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-5 dash-section">

        <div class="stat-card c-blue">
            <div class="stat-card-bg-icon"><i class="fas fa-chart-line"></i></div>
            <div class="stat-label-row">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <span class="stat-tag">اليوم</span>
            </div>
            <div class="stat-val">{{ number_format($summary['today_sales'] ?? 0, 2) }}</div>
            <div class="stat-lbl">مبيعات اليوم &nbsp;<span style="color:var(--text-d);">ج.م</span></div>
        </div>

        <div class="stat-card c-green">
            <div class="stat-card-bg-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-label-row">
                <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                <span class="stat-tag">الشهر</span>
            </div>
            <div class="stat-val">{{ number_format($summary['month_sales'] ?? 0, 2) }}</div>
            <div class="stat-lbl">مبيعات الشهر &nbsp;<span style="color:var(--text-d);">ج.م</span></div>
        </div>

        <div class="stat-card c-violet">
            <div class="stat-card-bg-icon"><i class="fas fa-users"></i></div>
            <div class="stat-label-row">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="stat-val">{{ number_format($summary['total_customers'] ?? 0) }}</div>
            <div class="stat-lbl">إجمالي العملاء</div>
        </div>

        <div class="stat-card c-amber">
            <div class="stat-card-bg-icon"><i class="fas fa-boxes"></i></div>
            <div class="stat-label-row">
                <div class="stat-icon"><i class="fas fa-boxes"></i></div>
            </div>
            <div class="stat-val">{{ number_format($summary['total_products'] ?? 0) }}</div>
            <div class="stat-lbl">إجمالي المنتجات</div>
        </div>

    </div>

    {{-- Mini Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6 dash-section">

        <div class="mini-card c-rose">
            <div class="mini-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <div class="mini-val">{{ $summary['low_stock_count'] ?? 0 }}</div>
                <div class="mini-lbl">مخزون منخفض</div>
            </div>
        </div>

        <div class="mini-card c-amber">
            <div class="mini-icon"><i class="fas fa-exchange-alt"></i></div>
            <div>
                <div class="mini-val">{{ $summary['pending_transfers'] ?? 0 }}</div>
                <div class="mini-lbl">تحويلات معلقة</div>
            </div>
        </div>

        <div class="mini-card c-pink">
            <div class="mini-icon"><i class="fas fa-credit-card"></i></div>
            <div>
                <div class="mini-val" style="font-size:17px;">{{ number_format($summary['total_debt'] ?? 0) }}</div>
                <div class="mini-lbl">إجمالي الديون ج.م</div>
            </div>
        </div>

        <div class="mini-card c-teal">
            <div class="mini-icon"><i class="fas fa-wallet"></i></div>
            <div>
                <div class="mini-val" style="font-size:17px;">{{ number_format($summary['cash_balance'] ?? 0) }}</div>
                <div class="mini-lbl">رصيد الخزينة ج.م</div>
            </div>
        </div>

    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5 dash-section">

        {{-- Invoices --}}
        <div class="lg:col-span-2 section-card">
            <div class="section-head">
                <div class="section-head-left">
                    <div class="section-icon blue"><i class="fas fa-file-invoice"></i></div>
                    <div>
                        <h3 class="section-title">آخر الفواتير</h3>
                        <p class="section-sub">أحدث العمليات المالية المسجلة</p>
                    </div>
                </div>
                <a href="{{ route('invoices.sales.index') }}" class="section-link">
                    عرض الكل &nbsp;<i class="fas fa-arrow-left" style="font-size:9px;"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>العميل</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary['recent_invoices'] ?? [] as $invoice)
                        <tr>
                            <td><span class="inv-ref">#{{ $invoice->reference ?? 'N/A' }}</span></td>
                            <td style="font-weight:700;color:var(--text-h);">{{ $invoice->customer->name ?? $invoice->party_name ?? 'غير محدد' }}</td>
                            <td>
                                <span style="font-weight:800;color:var(--text-h);">{{ number_format($invoice->total ?? 0, 2) }}</span>
                                <span style="font-size:10px;color:var(--text-m);"> ج.م</span>
                            </td>
                            <td>
                                @php $s = $invoice->status ?? ''; @endphp
                                @if($s=='paid')
                                    <span class="badge badge-paid"><i class="fas fa-check" style="font-size:8px;"></i> مدفوع</span>
                                @elseif($s=='pending')
                                    <span class="badge badge-pending"><i class="fas fa-clock" style="font-size:8px;"></i> معلق</span>
                                @else
                                    <span class="badge badge-gray">{{ $s ?: 'غير محدد' }}</span>
                                @endif
                            </td>
                            <td style="font-size:11px;font-weight:700;color:var(--text-m);">
                                {{ $invoice->created_at ? $invoice->created_at->format('Y-m-d') : 'N/A' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5">
                            <div class="empty-state">
                                <div class="empty-icon" style="background:var(--blue-soft);color:var(--blue);"><i class="fas fa-file-invoice"></i></div>
                                <p style="font-weight:800;font-size:14px;color:var(--text-m);">لا توجد فواتير حالياً</p>
                                <p style="font-size:11px;font-weight:600;color:var(--text-d);">ستظهر هنا أحدث الفواتير بعد إضافتها</p>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Stock Alerts --}}
        <div class="section-card">
            <div class="section-head">
                <div class="section-head-left">
                    <div class="section-icon red"><i class="fas fa-bell"></i></div>
                    <div>
                        <h3 class="section-title">تنبيهات المخزون</h3>
                        <p class="section-sub">منتجات تحتاج تجديد</p>
                    </div>
                </div>
                @if(!empty($summary['low_stock_products']))
                <span class="badge badge-pending">{{ count($summary['low_stock_products']) }} تنبيه</span>
                @endif
            </div>
            <div class="p-4 custom-scroll" style="max-height:400px;overflow-y:auto;">
                @forelse($summary['low_stock_products'] ?? [] as $product)
                <div class="alert-item">
                    <div class="alert-dot"></div>
                    <div class="flex-1 min-w-0">
                        <div class="alert-name">{{ $product->name ?? 'منتج غير محدد' }}</div>
                        <div class="alert-wh"><i class="fas fa-warehouse" style="font-size:9px;margin-left:3px;"></i>{{ $product->warehouse->name ?? 'مخزن غير محدد' }}</div>
                        <div class="alert-qty-row">
                            <span style="color:var(--rose);">متوفر: {{ $product->qty ?? 0 }}</span>
                            <span style="color:var(--text-m);">الحد: {{ $product->min_qty ?? 0 }}</span>
                        </div>
                        @php
                            $qty=$product->qty??0; $min=$product->min_qty??1;
                            $pct=min(100,($qty/max(1,$min))*100);
                            $barClr=$pct<50?'linear-gradient(90deg,#e84b5a,#fb7185)':'linear-gradient(90deg,#e8930a,#fbbf24)';
                        @endphp
                        <div class="progress-track">
                            <div class="progress-fill" style="width:{{ $pct }}%;background:{{ $barClr }};"></div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state" style="padding:42px 24px;">
                    <div class="empty-icon" style="background:var(--emerald-soft);color:var(--emerald);"><i class="fas fa-check-circle"></i></div>
                    <p style="font-weight:800;font-size:14px;color:var(--text-b);">المخزون ممتاز</p>
                    <p style="font-size:11px;font-weight:600;color:var(--text-m);">جميع المنتجات فوق الحد الأدنى</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Quick Actions --}}
    <div class="section-card dash-section" style="animation-delay:.38s;">
        <div class="section-head">
            <div class="section-head-left">
                <div class="section-icon amber"><i class="fas fa-bolt"></i></div>
                <div>
                    <h3 class="section-title">إجراءات سريعة</h3>
                    <p class="section-sub">الوصول الفوري للعمليات الأساسية</p>
                </div>
            </div>
        </div>
        <div class="p-5">
            <div class="quick-grid">

                <a href="{{ route('invoices.sales.create') }}" class="quick-btn qb-blue">
                    <div class="quick-btn-icon" style="background:var(--blue-soft);color:var(--blue);"><i class="fas fa-file-invoice-dollar"></i></div>
                    <span class="quick-btn-label">فاتورة بيع</span>
                </a>

                <a href="{{ route('invoices.purchases.create') }}" class="quick-btn qb-green">
                    <div class="quick-btn-icon" style="background:var(--emerald-soft);color:var(--emerald);"><i class="fas fa-shopping-cart"></i></div>
                    <span class="quick-btn-label">فاتورة شراء</span>
                </a>

                <a href="{{ route('products.create') }}" class="quick-btn qb-amber">
                    <div class="quick-btn-icon" style="background:var(--amber-soft);color:var(--amber);"><i class="fas fa-plus-circle"></i></div>
                    <span class="quick-btn-label">إضافة صنف</span>
                </a>

                <a href="{{ route('transfers.create') }}" class="quick-btn qb-violet">
                    <div class="quick-btn-icon" style="background:var(--violet-soft);color:var(--violet);"><i class="fas fa-exchange-alt"></i></div>
                    <span class="quick-btn-label">تحويل مخزون</span>
                </a>

                <a href="{{ route('stock-counts.create') }}" class="quick-btn qb-pink">
                    <div class="quick-btn-icon" style="background:var(--pink-soft);color:var(--pink);"><i class="fas fa-clipboard-list"></i></div>
                    <span class="quick-btn-label">جرد جديد</span>
                </a>

                <a href="{{ route('customers.create') }}" class="quick-btn qb-teal">
                    <div class="quick-btn-icon" style="background:var(--teal-soft);color:var(--teal);"><i class="fas fa-user-plus"></i></div>
                    <span class="quick-btn-label">عميل جديد</span>
                </a>

                <a href="{{ route('products.index') }}" class="quick-btn qb-indigo">
                    <div class="quick-btn-icon" style="background:var(--indigo-soft);color:var(--indigo);"><i class="fas fa-boxes"></i></div>
                    <span class="quick-btn-label">الأصناف</span>
                </a>

                <a href="{{ route('reports.financial') }}" class="quick-btn qb-rose">
                    <div class="quick-btn-icon" style="background:var(--rose-soft);color:var(--rose);"><i class="fas fa-chart-bar"></i></div>
                    <span class="quick-btn-label">التقارير</span>
                </a>

            </div>
        </div>
    </div>

</div>
@endsection