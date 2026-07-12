@extends('layouts.app')

@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')

@push('styles')
<style>
    /* Upgrade Plan Banner — يظهر فقط للـ tenants اللي على Starter أو Pro */
    .dash-upgrade-banner {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        padding: 0;
        margin-bottom: 1.5rem;
        background:
            linear-gradient(135deg, #1e1b4b 0%, #0f172a 40%, #312e81 100%);
        border: 1px solid rgba(245, 158, 11, 0.25);
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5), 0 0 0 1px rgba(245, 158, 11, .12);
        isolation: isolate;
    }
    .dash-upgrade-bg {
        position: absolute; inset: 0; pointer-events: none; z-index: 0;
    }
    .dash-upgrade-blob {
        position: absolute; border-radius: 9999px; filter: blur(80px); opacity: .5;
    }
    .dash-upgrade-blob.blob-1 {
        top: -40px; right: -40px; width: 220px; height: 220px;
        background: radial-gradient(circle, #f59e0b 0%, transparent 70%);
    }
    .dash-upgrade-blob.blob-2 {
        bottom: -40px; left: -40px; width: 260px; height: 260px;
        background: radial-gradient(circle, #6366f1 0%, transparent 70%);
    }
    .dash-upgrade-content {
        position: relative; z-index: 1;
        display: flex; align-items: center; gap: 1.5rem;
        padding: 1.5rem 2rem;
        flex-wrap: wrap;
    }
    .dash-upgrade-icon {
        width: 64px; height: 64px; flex-shrink: 0;
        border-radius: 18px;
        background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1.75rem;
        box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.5);
    }
    .dash-upgrade-text { flex: 1 1 280px; min-width: 0; }
    .dash-upgrade-text h3 {
        margin: 0 0 0.35rem 0;
        font-family: 'Cairo', sans-serif;
        font-size: 1.35rem; font-weight: 900;
        color: white; line-height: 1.2;
    }
    .dash-upgrade-text p {
        margin: 0;
        color: #cbd5e1; font-size: 0.95rem; line-height: 1.55;
    }
    .dash-upgrade-text p strong { color: #f59e0b; font-weight: 800; }
    .dash-upgrade-actions { flex-shrink: 0; }
    .dash-upgrade-btn {
        display: inline-flex; align-items: center; gap: 0.55rem;
        padding: 0.9rem 1.5rem;
        border-radius: 16px;
        font-weight: 800; font-size: 0.95rem;
        transition: transform .25s, box-shadow .25s;
        text-decoration: none; white-space: nowrap;
        font-family: 'Cairo', sans-serif;
    }
    .dash-upgrade-btn-primary {
        background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
        color: white;
        box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.5);
    }
    .dash-upgrade-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 35px -8px rgba(245, 158, 11, 0.65);
        color: white; text-decoration: none;
    }
    .dash-upgrade-arrow { font-size: 0.85rem; transition: transform .25s; }
    .dash-upgrade-btn:hover .dash-upgrade-arrow { transform: translateX(-4px); }

    @media (max-width: 768px) {
        .dash-upgrade-content {
            flex-direction: column; text-align: center;
            padding: 1.5rem 1.25rem;
        }
        .dash-upgrade-icon { width: 56px; height: 56px; font-size: 1.5rem; }
        .dash-upgrade-text h3 { font-size: 1.15rem; }
        .dash-upgrade-btn { width: 100%; justify-content: center; }
    }
</style>
@endpush

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'صباح الخير' : ($hour < 17 ? 'مساء الخير' : 'مساء النور');
    $companyName = \App\Models\Company::first()?->name ?? 'كيان';

    // Detect the active tenant's plan slug to decide whether to render the upgrade banner.
    $currentPlanSlug = null;
    try {
        if (function_exists('tenant')) {
            $t = tenant();
            if ($t) {
                $currentPlanSlug = $t->plan_id ?? data_get($t->data, 'plan_id');
            }
        }
    } catch (\Throwable $e) {
        $currentPlanSlug = null;
    }
    $pricingUrl = config('pricing.signup_url')
        ?: 'https://pricing.kayyan.com';
@endphp

<div class="dash-v3">

    {{-- Upgrade Banner — يظهر فقط للـ tenants اللي على Starter أو Pro --}}
    @if(in_array($currentPlanSlug, ['starter', 'pro'], true))
        <div class="dash-upgrade-banner dash-animate" dir="rtl">
            <div class="dash-upgrade-bg">
                <div class="dash-upgrade-blob blob-1"></div>
                <div class="dash-upgrade-blob blob-2"></div>
            </div>
            <div class="dash-upgrade-content">
                <div class="dash-upgrade-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <div class="dash-upgrade-text">
                    <h3>افتح إمكانيات عملك الكاملة 🚀</h3>
                    <p>
                        @if($currentPlanSlug === 'starter')
                            باقتك الحالية <strong>Starter</strong> - قم بالترقية لفتح التصنيع، المحاسبة المتقدمة، والتقارير المالية.
                        @else
                            باقتك الحالية <strong>Pro</strong> - قم بالترقية لـ Enterprise للحصول على مستودعات غير محدودة ودعم VIP.
                        @endif
                    </p>
                </div>
                <div class="dash-upgrade-actions">
                    <a href="{{ $pricingUrl }}" target="_blank" rel="noopener" class="dash-upgrade-btn dash-upgrade-btn-primary">
                        <i class="fas fa-crown"></i>
                        <span>شاهد الباقات</span>
                        <i class="fas fa-arrow-left dash-upgrade-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ترحيب --}}
    <div class="dash-welcome dash-animate">
        <div class="dash-welcome-text">
            <h2>{{ $greeting }}، {{ Auth::user()->name }}</h2>
            <p>نظرة سريعة على أداء عملك اليوم</p>
        </div>
        <div class="dash-welcome-meta">
            <span class="dash-company-pill">
                <i class="fas fa-building"></i>
                {{ $companyName }}
            </span>
            <span class="dash-date-pill">
                <i class="fas fa-calendar-day"></i>
                {{ now()->translatedFormat('l، d F Y') }}
            </span>
        </div>
    </div>

    {{-- إحصائيات رئيسية --}}
    <div class="dash-stats">
        <div class="mz-stat c-teal dash-animate">
            <div class="mz-stat-top">
                <div class="mz-stat-icon"><i class="fas fa-chart-line"></i></div>
                <span class="mz-stat-tag">اليوم</span>
            </div>
            <div class="mz-stat-value">{{ number_format($summary['today_sales'] ?? 0, 2) }}</div>
            <div class="mz-stat-label">مبيعات اليوم (ج.م)</div>
        </div>

        <div class="mz-stat c-green dash-animate">
            <div class="mz-stat-top">
                <div class="mz-stat-icon"><i class="fas fa-calendar-alt"></i></div>
                <span class="mz-stat-tag">الشهر</span>
            </div>
            <div class="mz-stat-value">{{ number_format($summary['month_sales'] ?? 0, 2) }}</div>
            <div class="mz-stat-label">مبيعات الشهر (ج.م)</div>
        </div>

        <div class="mz-stat c-violet dash-animate">
            <div class="mz-stat-top">
                <div class="mz-stat-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="mz-stat-value">{{ number_format($summary['total_customers'] ?? 0) }}</div>
            <div class="mz-stat-label">إجمالي العملاء</div>
        </div>

        <div class="mz-stat c-amber dash-animate">
            <div class="mz-stat-top">
                <div class="mz-stat-icon"><i class="fas fa-boxes"></i></div>
            </div>
            <div class="mz-stat-value">{{ number_format($summary['total_products'] ?? 0) }}</div>
            <div class="mz-stat-label">إجمالي المنتجات</div>
        </div>
    </div>

    {{-- إحصائيات ثانوية --}}
    <div class="dash-mini-stats dash-animate">
        <div class="mz-mini c-danger">
            <div class="mz-mini-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <div class="mz-mini-val">{{ $summary['low_stock_count'] ?? 0 }}</div>
                <div class="mz-mini-lbl">مخزون منخفض</div>
            </div>
        </div>

        <div class="mz-mini c-warning">
            <div class="mz-mini-icon"><i class="fas fa-exchange-alt"></i></div>
            <div>
                <div class="mz-mini-val">{{ $summary['pending_transfers'] ?? 0 }}</div>
                <div class="mz-mini-lbl">تحويلات معلقة</div>
            </div>
        </div>

        <div class="mz-mini c-info">
            <div class="mz-mini-icon"><i class="fas fa-credit-card"></i></div>
            <div>
                <div class="mz-mini-val">{{ number_format($summary['total_debt'] ?? 0) }}</div>
                <div class="mz-mini-lbl">إجمالي الديون (ج.م)</div>
            </div>
        </div>

        <div class="mz-mini c-teal">
            <div class="mz-mini-icon"><i class="fas fa-wallet"></i></div>
            <div>
                <div class="mz-mini-val">{{ number_format($summary['cash_balance'] ?? 0) }}</div>
                <div class="mz-mini-lbl">رصيد الخزينة (ج.م)</div>
            </div>
        </div>

        <a href="{{ route('manufacturing.material-batches.index') }}" class="mz-mini c-wood">
            <div class="mz-mini-icon"><i class="fas fa-boxes"></i></div>
            <div>
                <div class="mz-mini-val">{{ number_format($summary['material_remaining_qty'] ?? 0, 2) }}</div>
                <div class="mz-mini-lbl">مواد خام متاحة</div>
            </div>
        </a>
    </div>

    {{-- المحتوى الرئيسي --}}
    <div class="dash-grid dash-animate">

        {{-- آخر الفواتير --}}
        <div class="mz-card">
            <div class="mz-card-head">
                <div class="mz-card-head-left">
                    <div class="mz-card-icon teal"><i class="fas fa-file-invoice"></i></div>
                    <div>
                        <h3 class="mz-card-title">آخر الفواتير</h3>
                        <p class="mz-card-sub">أحدث العمليات المالية المسجلة</p>
                    </div>
                </div>
                <a href="{{ route('invoices.sales.index') }}" class="mz-card-link">
                    عرض الكل <i class="fas fa-arrow-left text-[10px]"></i>
                </a>
            </div>
            <div style="overflow-x:auto;">
                <table class="mz-table">
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
                            <td data-label="رقم الفاتورة"><span class="mz-ref">#{{ $invoice->reference ?? 'N/A' }}</span></td>
                            <td data-label="العميل" class="font-bold">{{ $invoice->customer->name ?? $invoice->party_name ?? 'غير محدد' }}</td>
                            <td data-label="المبلغ">
                                <span class="font-extrabold">{{ number_format($invoice->total ?? 0, 2) }}</span>
                                <span class="text-xs text-muted"> ج.م</span>
                            </td>
                            <td data-label="الحالة">
                                @php $s = $invoice->status ?? ''; @endphp
                                @if($s == 'paid')
                                    <span class="mz-badge mz-badge-paid"><i class="fas fa-check"></i> مدفوع</span>
                                @elseif($s == 'pending')
                                    <span class="mz-badge mz-badge-pending"><i class="fas fa-clock"></i> معلق</span>
                                @else
                                    <span class="mz-badge mz-badge-muted">{{ $s ?: 'غير محدد' }}</span>
                                @endif
                            </td>
                            <td data-label="التاريخ" class="text-xs font-semibold text-muted">
                                {{ $invoice->created_at ? $invoice->created_at->format('Y-m-d') : 'N/A' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="mz-empty">
                                    <div class="mz-empty-icon"><i class="fas fa-file-invoice"></i></div>
                                    <p class="mz-empty-title">لا توجد فواتير حالياً</p>
                                    <p class="mz-empty-sub">ستظهر هنا أحدث الفواتير بعد إضافتها</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- تنبيهات المخزون --}}
        <div class="mz-card">
            <div class="mz-card-head">
                <div class="mz-card-head-left">
                    <div class="mz-card-icon danger"><i class="fas fa-bell"></i></div>
                    <div>
                        <h3 class="mz-card-title">تنبيهات المخزون</h3>
                        <p class="mz-card-sub">منتجات تحتاج تجديد</p>
                    </div>
                </div>
                @if(!empty($summary['low_stock_products']))
                <span class="mz-badge mz-badge-pending">{{ count($summary['low_stock_products']) }} تنبيه</span>
                @endif
            </div>
            <div class="mz-alerts">
                @forelse($summary['low_stock_products'] ?? [] as $product)
                <div class="mz-alert-item">
                    <div class="mz-alert-dot"></div>
                    <div class="flex-1 min-w-0">
                        <div class="mz-alert-name">{{ $product->name ?? 'منتج غير محدد' }}</div>
                        <div class="mz-alert-meta">
                            <i class="fas fa-warehouse text-[10px] ms-1"></i>
                            {{ $product->warehouse ?? 'مخزن غير محدد' }}
                        </div>
                        <div class="mz-alert-progress-meta">
                            <span class="text-mz-danger">متوفر: {{ $product->quantity ?? 0 }}</span>
                            <span class="text-mz-muted">الحد: {{ $product->min_stock ?? 0 }}</span>
                        </div>
                        @php
                            $qty = $product->quantity ?? 0;
                            $min = $product->min_stock ?? 1;
                            $pct = min(100, ($qty / max(1, $min)) * 100);
                            $barColor = $pct < 50 ? 'var(--mz-danger)' : 'var(--mz-warning)';
                        @endphp
                        <div class="mz-progress">
                            <div class="mz-progress-bar" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
                        </div>
                    </div>
                </div>
@empty
                <div class="mz-empty">
                    <div class="mz-empty-icon mz-empty-icon-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <p class="mz-empty-title">المخزون ممتاز</p>
                    <p class="mz-empty-sub">جميع المنتجات فوق الحد الأدنى</p>
                </div>
            @endforelse
            </div>
        </div>
    </div>

    {{-- إجراءات سريعة --}}
    <div class="mz-card dash-animate">
        <div class="mz-card-head">
            <div class="mz-card-head-left">
                <div class="mz-card-icon amber"><i class="fas fa-bolt"></i></div>
                <div>
                    <h3 class="mz-card-title">إجراءات سريعة</h3>
                    <p class="mz-card-sub">الوصول الفوري للعمليات الأساسية</p>
                </div>
            </div>
        </div>
        <div class="mz-quick-grid">
            <a href="{{ route('invoices.sales.create') }}" class="mz-quick">
                <div class="mz-quick-icon" style="background:var(--mz-info-soft);color:var(--mz-info);"><i class="fas fa-file-invoice-dollar"></i></div>
                <span class="mz-quick-label">فاتورة بيع</span>
            </a>
            <a href="{{ route('invoices.purchases.create') }}" class="mz-quick">
                <div class="mz-quick-icon" style="background:var(--mz-success-soft);color:var(--mz-success);"><i class="fas fa-shopping-cart"></i></div>
                <span class="mz-quick-label">فاتورة شراء</span>
            </a>
            <a href="{{ route('products.create') }}" class="mz-quick">
                <div class="mz-quick-icon" style="background:var(--mz-accent-soft);color:var(--mz-accent);"><i class="fas fa-plus-circle"></i></div>
                <span class="mz-quick-label">إضافة صنف</span>
            </a>
            <a href="{{ route('transfers.create') }}" class="mz-quick">
                <div class="mz-quick-icon" style="background:var(--mz-violet-soft);color:var(--mz-violet);"><i class="fas fa-exchange-alt"></i></div>
                <span class="mz-quick-label">تحويل مخزون</span>
            </a>
            <a href="{{ route('stock-counts.create') }}" class="mz-quick">
                <div class="mz-quick-icon" style="background:var(--mz-danger-soft);color:var(--mz-danger);"><i class="fas fa-clipboard-list"></i></div>
                <span class="mz-quick-label">جرد جديد</span>
            </a>
            <a href="{{ route('customers.create') }}" class="mz-quick">
                <div class="mz-quick-icon" style="background:var(--mz-primary-soft);color:var(--mz-primary-dark);"><i class="fas fa-user-plus"></i></div>
                <span class="mz-quick-label">عميل جديد</span>
            </a>
            <a href="{{ route('products.index') }}" class="mz-quick">
                <div class="mz-quick-icon" style="background:var(--mz-primary-muted);color:var(--mz-primary);"><i class="fas fa-boxes"></i></div>
                <span class="mz-quick-label">الأصناف</span>
            </a>
            <a href="{{ route('reports.financial') }}" class="mz-quick">
                <div class="mz-quick-icon" style="background:var(--mz-slate-100);color:var(--mz-slate-700);"><i class="fas fa-chart-bar"></i></div>
                <span class="mz-quick-label">التقارير</span>
            </a>
        </div>
    </div>

</div>
@endsection
