<div class="space-y-8 animate-fade-in">
    {{-- ──────────────────────────────────────────────────────────
         1) Page Header + Range Selector
         ────────────────────────────────────────────────────────── --}}
    <x-ui.page-header
        :title="__('dashboard.title')"
        :subtitle="__('dashboard.subtitle', ['from' => $payload['period']['from'], 'to' => $payload['period']['to']])"
    >
        <x-slot:actions>
            {{-- Range pills --}}
            <div class="inline-flex items-center gap-1 p-1 bg-white border border-ink-200 rounded-xl shadow-1">
                @foreach($ranges as $key => $label)
                    <button
                        type="button"
                        wire:click="setRange('{{ $key }}')"
                        @class([
                            'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200',
                            'bg-navy-700 text-white shadow-1' => $range === $key,
                            'text-ink-600 hover:text-ink-900' => $range !== $key,
                        ])
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <x-ui.button variant="primary" size="md" icon="icon.arrow-down-tray">
                تصدير
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- ──────────────────────────────────────────────────────────
         Section: المؤشرات الرئيسية (KPI)
         ────────────────────────────────────────────────────────── --}}
    <section class="animate-sec-fade-up delay-1">
        <div class="sec-head">
            <div class="sec-head__main">
                <span class="sec-head__bar"></span>
                <div>
                    <h3 class="sec-head__title">المؤشرات الرئيسية</h3>
                    <div class="sec-head__sub">نظرة سريعة على أداء الفترة المحددة</div>
                </div>
            </div>
        </div>

        {{-- 6 KPI cards in 2 rows × 3 cols for editorial breathing room --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6 gap-4">
            @foreach($payload['kpis'] as $kpi)
                <x-ui.kpi-card
                    :label="$kpi['label']"
                    :value="$kpi['value']"
                    :suffix="$kpi['suffix'] ?? null"
                    :delta="$kpi['delta']"
                    :delta-label="$kpi['delta_label']"
                    :trend="$kpi['trend']"
                    :icon="$kpi['icon']"
                    :variant="$kpi['variant']"
                    :href="$kpi['href']"
                >
                    <x-slot:sparkline>
                        <x-ui.sparkline :data="$kpi['series']" :color="$kpi['color']" :height="28" />
                    </x-slot:sparkline>
                </x-ui.kpi-card>
            @endforeach
        </div>
    </section>

    {{-- ──────────────────────────────────────────────────────────
         Section: المبيعات والتحليلات (Charts row)
         ────────────────────────────────────────────────────────── --}}
    <section class="animate-sec-fade-up delay-2">
        <div class="sec-head">
            <div class="sec-head__main">
                <span class="sec-head__bar"></span>
                <div>
                    <h3 class="sec-head__title">المبيعات والتحليلات</h3>
                    <div class="sec-head__sub">اتجاه المبيعات والمنتجات الأكثر مبيعاً</div>
                </div>
            </div>
            <a href="{{ route('reports.sales') }}" class="sec-head__action">
                التقارير الكاملة
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Sales Trend (Line) - 2 cols --}}
            <x-ui.card class="lg:col-span-2">
                <x-slot:header>
                    <div>
                        <div class="card__title">اتجاه المبيعات</div>
                        <div class="card__subtitle">المبيعات اليومية خلال الفترة</div>
                    </div>
                    <x-ui.tag variant="success" dot>+12.5%</x-ui.tag>
                </x-slot:header>
                <x-ui.chart-line
                    :data="$payload['sales_trend']"
                    :colors="['#1B3A5C']"
                    :height="280"
                />
            </x-ui.card>

            {{-- Top Products (Bar) - 1 col --}}
            <x-ui.card>
                <x-slot:header>
                    <div>
                        <div class="card__title">أفضل المنتجات</div>
                        <div class="card__subtitle">حسب قيمة المبيعات</div>
                    </div>
                </x-slot:header>
                @if(count($payload['top_products']['labels'] ?? []))
                    <x-ui.chart-bar
                        :labels="$payload['top_products']['labels']"
                        :values="$payload['top_products']['values']"
                        :color="$payload['top_products']['color']"
                        :height="280"
                    />
                @else
                    <x-ui.empty-state
                        icon="icon.cube"
                        title="لا توجد بيانات"
                        desc="لم يتم تسجيل مبيعات في هذه الفترة."
                    />
                @endif
            </x-ui.card>
        </div>
    </section>

    {{-- ──────────────────────────────────────────────────────────
         Section: الفواتير والفئات
         ────────────────────────────────────────────────────────── --}}
    <section class="animate-sec-fade-up delay-3">
        <div class="sec-head">
            <div class="sec-head__main">
                <span class="sec-head__bar"></span>
                <div>
                    <h3 class="sec-head__title">الفواتير والفئات</h3>
                    <div class="sec-head__sub">آخر الفواتير وتوزيع الإيرادات</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Revenue by Category (Donut) --}}
            <x-ui.card>
                <x-slot:header>
                    <div>
                        <div class="card__title">الإيرادات حسب الفئة</div>
                        <div class="card__subtitle">توزيع المبيعات</div>
                    </div>
                </x-slot:header>
                @if(count($payload['revenue_by_cat']['labels'] ?? []))
                    <x-ui.chart-donut
                        :labels="$payload['revenue_by_cat']['labels']"
                        :values="$payload['revenue_by_cat']['values']"
                        :colors="$payload['revenue_by_cat']['colors']"
                        :center-title="'إجمالي'"
                        :center-value="number_format($payload['revenue_by_cat']['total'], 0, '.', ',')"
                    />
                @else
                    <x-ui.empty-state
                        icon="icon.chart-pie"
                        title="لا توجد بيانات"
                        desc="ابدأ بتسجيل المبيعات لرؤية التوزيع."
                    />
                @endif
            </x-ui.card>

            {{-- Recent Invoices (Table) - 2 cols --}}
            <x-ui.card class="lg:col-span-2" :padding="false">
                <x-slot:header>
                    <div class="px-6 pt-5 pb-3 flex items-start justify-between gap-3">
                        <div>
                            <div class="card__title">آخر الفواتير</div>
                            <div class="card__subtitle">أحدث 8 فواتير مبيعات</div>
                        </div>
                        <a href="{{ route('invoices.sales.index') }}" class="sec-head__action">
                            عرض الكل
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </x-slot:header>
                @if($payload['recent_invoices']->isNotEmpty())
                    <div class="overflow-hidden">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>رقم</th>
                                    <th>العميل</th>
                                    <th>التاريخ</th>
                                    <th>الإجمالي</th>
                                    <th>المتبقي</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payload['recent_invoices'] as $inv)
                                    <tr>
                                        <td>
                                            <a href="{{ $inv['href'] }}" class="cell-id">
                                                {{ $inv['number'] }}
                                            </a>
                                        </td>
                                        <td class="text-ink-800">{{ $inv['customer'] }}</td>
                                        <td class="cell-num">{{ $inv['date'] }}</td>
                                        <td class="cell-amt">{{ $inv['total'] }} <span class="text-ink-400 font-normal">ج.م</span></td>
                                        <td>
                                            @if((float) str_replace(',', '', $inv['remaining']) > 0)
                                                <span class="cell-amt text-danger-600">{{ $inv['remaining'] }} <span class="text-danger-400 font-normal">ج.م</span></span>
                                            @else
                                                <span class="tag tag--success tag--dot">مدفوعة</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $paymentVariant = match($inv['payment_status']) {
                                                    'paid'    => 'success',
                                                    'partial' => 'warning',
                                                    'unpaid'  => 'danger',
                                                    default   => 'default',
                                                };
                                                $paymentLabel = match($inv['payment_status']) {
                                                    'paid'    => 'مدفوعة',
                                                    'partial' => 'جزئية',
                                                    'unpaid'  => 'غير مدفوعة',
                                                    default   => $inv['payment_status'],
                                                };
                                            @endphp
                                            <x-ui.tag :variant="$paymentVariant" dot>{{ $paymentLabel }}</x-ui.tag>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6">
                        <x-ui.empty-state
                            icon="icon.receipt"
                            title="لا توجد فواتير"
                            desc="ابدأ بإنشاء أول فاتورة لمشاهدة آخر الفواتير هنا."
                        >
                            <x-ui.button variant="primary" :href="route('invoices.sales.create')">
                                فاتورة جديدة
                            </x-ui.button>
                        </x-ui.empty-state>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </section>

    {{-- ──────────────────────────────────────────────────────────
         Section: المخزون والتصنيع
         ────────────────────────────────────────────────────────── --}}
    <section class="animate-sec-fade-up delay-4">
        <div class="sec-head">
            <div class="sec-head__main">
                <span class="sec-head__bar"></span>
                <div>
                    <h3 class="sec-head__title">المخزون والتصنيع</h3>
                    <div class="sec-head__sub">تنبيهات المخزون وحالة أوامر التصنيع</div>
                </div>
            </div>
            <a href="{{ route('reports.inventory') }}" class="sec-head__action">
                تقرير المخزون
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Low Stock Alerts --}}
            <x-ui.card class="lg:col-span-2">
                <x-slot:header>
                    <div>
                        <div class="card__title flex items-center gap-2">
                            <svg class="w-4 h-4 text-warning-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                            تنبيهات المخزون المنخفض
                        </div>
                        <div class="card__subtitle">منتجات وصلت للحد الأدنى</div>
                    </div>
                </x-slot:header>
                @if($payload['low_stock']->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($payload['low_stock'] as $item)
                            <div class="flex items-center gap-3 p-3 rounded-xl border border-ink-200 hover:border-warning-300 hover:shadow-2 transition-all">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 bg-warning-soft text-warning-700">
                                    <x-ui.icon name="icon.cube" class="w-5 h-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ $item['href'] }}" class="text-sm font-semibold text-ink-900 hover:text-navy-700 truncate block">{{ $item['name'] }}</a>
                                    <div class="text-xs text-ink-500 mt-0.5 num">SKU: {{ $item['sku'] ?? '—' }}</div>
                                </div>
                                <div class="w-32">
                                    <x-ui.progress :value="$item['current']" :max="max($item['min'], 1)" variant="warning" />
                                    <div class="text-xs text-ink-500 mt-1 text-end num">
                                        {{ $item['current'] }} / {{ $item['min'] }} {{ $item['unit'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state
                        icon="icon.check-circle"
                        title="المخزون بحالة جيدة"
                        desc="لا توجد منتجات تحت الحد الأدنى."
                    />
                @endif
            </x-ui.card>

            {{-- Manufacturing Pipeline --}}
            <x-ui.card>
                <x-slot:header>
                    <div>
                        <div class="card__title">خط التصنيع</div>
                        <div class="card__subtitle">حالة أوامر التصنيع</div>
                    </div>
                </x-slot:header>
                <div class="space-y-3">
                    @foreach($payload['manufacturing'] as $col)
                        @php
                            $variant = match($col['status']) {
                                'planned'     => 'default',
                                'in_progress' => 'info',
                                'paused'      => 'warning',
                                'completed'   => 'success',
                                default       => 'default',
                            };
                        @endphp
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-transparent bg-ink-50 hover:bg-white hover:border-ink-200 hover:shadow-2 transition-all">
                            <x-ui.tag :variant="$variant" dot>{{ $col['label'] }}</x-ui.tag>
                            <span class="ms-auto text-2xl font-semibold text-ink-900 num">
                                {{ $col['count'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        </div>
    </section>

    {{-- ──────────────────────────────────────────────────────────
         Section: التدفق النقدي والمصروفات
         ────────────────────────────────────────────────────────── --}}
    <section class="animate-sec-fade-up delay-5">
        <div class="sec-head">
            <div class="sec-head__main">
                <span class="sec-head__bar"></span>
                <div>
                    <h3 class="sec-head__title">التدفق النقدي والمصروفات</h3>
                    <div class="sec-head__sub">الإيرادات مقابل المصروفات خلال الفترة</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Cash Flow (Bar) --}}
            <x-ui.card>
                <x-slot:header>
                    <div>
                        <div class="card__title">التدفق النقدي</div>
                        <div class="card__subtitle">الإيرادات مقابل المصروفات</div>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-sm bg-navy"></span>
                            <span class="text-ink-600 font-medium">إيرادات</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-sm bg-danger"></span>
                            <span class="text-ink-600 font-medium">مصروفات</span>
                        </div>
                    </div>
                </x-slot:header>
                @if(count($payload['cash_flow']['inflow'] ?? []))
                    <div class="cash-flow">
                        @foreach($payload['cash_flow']['labels'] as $i => $label)
                            @php
                                $maxBar = max(
                                    $payload['cash_flow']['inflow'][$i] ?? 0,
                                    $payload['cash_flow']['outflow'][$i] ?? 0,
                                    1
                                );
                                $inVal  = (float) ($payload['cash_flow']['inflow'][$i]  ?? 0);
                                $outVal = (float) ($payload['cash_flow']['outflow'][$i] ?? 0);
                                $inPct  = ($inVal  / $maxBar) * 100;
                                $outPct = ($outVal / $maxBar) * 100;
                            @endphp
                            <div class="cash-flow__col">
                                <div class="cash-flow__bars">
                                    <div class="cash-flow__bar cash-flow__bar--in"  style="height: {{ $inPct }}%;"  title="إيراد: {{ number_format($inVal, 0) }}"></div>
                                    <div class="cash-flow__bar cash-flow__bar--out" style="height: {{ $outPct }}%;" title="مصروف: {{ number_format($outVal, 0) }}"></div>
                                </div>
                                <div class="cash-flow__label">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state icon="icon.banknotes" title="لا توجد بيانات" desc="لم يتم تسجيل حركات نقدية في هذه الفترة." />
                @endif
            </x-ui.card>

            {{-- Expense Breakdown (Donut) --}}
            <x-ui.card>
                <x-slot:header>
                    <div>
                        <div class="card__title">توزيع المصروفات</div>
                        <div class="card__subtitle">حسب الفئة</div>
                    </div>
                </x-slot:header>
                @if(count($payload['expenses']['labels'] ?? []))
                    <x-ui.chart-donut
                        :labels="$payload['expenses']['labels']"
                        :values="$payload['expenses']['values']"
                        :colors="$payload['expenses']['colors']"
                    />
                @else
                    <x-ui.empty-state
                        icon="icon.credit-card"
                        title="لا توجد مصروفات"
                        desc="ابدأ بتسجيل المصروفات لمشاهدة التوزيع."
                    />
                @endif
            </x-ui.card>
        </div>
    </section>

    {{-- ──────────────────────────────────────────────────────────
         Section: إجراءات سريعة ونشاطات
         ────────────────────────────────────────────────────────── --}}
    <section class="animate-sec-fade-up delay-6">
        <div class="sec-head">
            <div class="sec-head__main">
                <span class="sec-head__bar"></span>
                <div>
                    <h3 class="sec-head__title">إجراءات سريعة ونشاطات</h3>
                    <div class="sec-head__sub">العمليات الأكثر استخداماً وآخر الأحداث</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Quick Actions - 2 cols --}}
            <x-ui.card class="lg:col-span-2">
                <x-slot:header>
                    <div>
                        <div class="card__title">إجراءات سريعة</div>
                        <div class="card__subtitle">العمليات الأكثر استخداماً</div>
                    </div>
                </x-slot:header>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($payload['quick_actions'] as $action)
                        <a href="{{ $action['href'] }}" class="quick-action quick-action--{{ $action['variant'] ?? 'navy' }}">
                            <div class="quick-action__icon">
                                <x-dynamic-component :component="$action['icon']" class="w-5 h-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="quick-action__title">{{ $action['title'] }}</div>
                                <div class="quick-action__desc">{{ $action['desc'] }}</div>
                            </div>
                            <span class="quick-action__chev" aria-hidden="true">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            </x-ui.card>

            {{-- Activity Timeline --}}
            <x-ui.card>
                <x-slot:header>
                    <div>
                        <div class="card__title">آخر النشاطات</div>
                        <div class="card__subtitle">آخر الأحداث على النظام</div>
                    </div>
                </x-slot:header>
                @if($payload['activity']->isNotEmpty())
                    <x-ui.activity-timeline :items="$payload['activity']" />
                @else
                    <x-ui.empty-state
                        icon="icon.clock"
                        title="لا توجد نشاطات"
                        desc="ابدأ باستخدام النظام لرؤية النشاطات هنا."
                    />
                @endif
            </x-ui.card>
        </div>
    </section>
</div>
