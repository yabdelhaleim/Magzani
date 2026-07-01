{{-- Expects: $sectionTitle, $sectionHint (optional), $rows (ProductWarehouse collection), $emptyText --}}
<div class="table-card" style="margin-bottom:1.25rem;">
    <div class="table-head">
        <div class="table-head-left">
            <div class="card-ico purple" style="width:38px;height:38px;border-radius:9px">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:17px;height:17px">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <h3>{{ $sectionTitle }}</h3>
                @if(!empty($sectionHint))
                    <p style="font-size:.78rem;color:var(--text-muted);margin-top:4px;">{{ $sectionHint }}</p>
                @endif
            </div>
        </div>
        <span class="count-pill">{{ $rows->count() }} صنف</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:48px">#</th>
                    <th>اسم المنتج</th>
                    <th>كود المنتج</th>
                    <th>الكمية</th>
                    <th>متوسط التكلفة</th>
                    <th>القيمة الإجمالية</th>
                    <th>الحد الأدنى</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $index => $item)
                    <tr>
                        <td style="color:var(--text-muted);font-size:.78rem;font-weight:600">
                            {{ $index + 1 }}
                        </td>
                        <td>
                            <p class="product-name">{{ $item->product?->name ?? '[محذوف]' }}</p>
                            @if($item->product && optional($item->product->category)->name)
                                <p class="product-cat">{{ $item->product->category->name }}</p>
                            @endif
                        </td>
                        <td>
                            <span class="sku-badge">
                                {{ ($item->product?->code ?? $item->product?->sku) ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $qtyClass = $item->quantity <= 0
                                    ? 'zero'
                                    : ($item->quantity <= ($item->min_stock ?? 0) ? 'low' : 'ok');
                            @endphp
                            <span class="qty {{ $qtyClass }}">{{ number_format($item->quantity) }}</span>
                        </td>
                        <td style="font-weight:500">
                            {{ number_format($item->average_cost, 2) }}
                            <small style="color:var(--text-muted)">ج.م</small>
                        </td>
                        <td style="font-weight:700;color:var(--text-primary)">
                            {{ number_format($item->quantity * $item->average_cost, 2) }}
                            <small style="color:var(--text-muted)">ج.م</small>
                        </td>
                        <td style="font-weight:500">
                            {{ $item->min_stock ?? '—' }}
                        </td>
                        <td>
                            @if($item->quantity <= 0)
                                <span class="pill out-of-stock">
                                    <span class="pdot"></span> غير متوفر
                                </span>
                            @elseif($item->quantity <= ($item->min_stock ?? 0))
                                <span class="pill low-stock">
                                    <span class="pdot"></span> قريب من النفاد
                                </span>
                            @else
                                <span class="pill available">
                                    <span class="pdot"></span> متوفر
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-circle">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <h4>{{ $emptyText }}</h4>
                                <p>لا توجد أصناف في هذه الفئة داخل هذا المخزن</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
