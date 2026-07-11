@extends('layouts.app')

@section('title', 'تقرير تحليل انحرافات التكلفة')
@section('page-title', 'تقرير تحليل انحرافات التكلفة (Standard Costing)')

@push('styles')
<style>
    .variance-fav   { background: #dcfce7; color: #065f46; }
    .variance-unfav { background: #fee2e2; color: #991b1b; }
    .variance-none  { background: #f3f4f6; color: #374151; }
    .kpi-card { background:#fff; border-radius:16px; border:1px solid #e4eaf7; padding:18px 20px; }
    .kpi-card .label { font-size:11px; font-weight:700; color:#7e90b0; text-transform:uppercase; letter-spacing:.5px; }
    .kpi-card .value { font-size:24px; font-weight:900; color:#1a2140; margin-top:4px; }
    .filter-bar { background:#fff; border-radius:14px; border:1px solid #e4eaf7; padding:18px; margin-bottom:18px; }
</style>
@endpush

@section('content')
<div class="px-2 md:px-6 py-4">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <i class="fas fa-balance-scale text-amber-600"></i>
                تحليل انحرافات التكلفة
            </h1>
            <p class="text-sm text-gray-600 mt-1">مقارنة التكلفة المعيارية بالتكلفة الفعلية لكل أمر تصنيع مكتمل — حساب 5160.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('reports.cost-variance.export', request()->only(['date_from','date_to','product_id','variance_type'])) }}"
               class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold text-sm shadow">
                <i class="fas fa-file-csv ml-1"></i> تصدير CSV
            </a>
        </div>
    </div>

    {{-- Standard Costing not enabled banner --}}
    @unless($standardCostingOn)
    <div class="filter-bar" style="border-color:#fde68a; background:#fffbeb;">
        <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-amber-600 text-xl mt-1"></i>
            <div>
                <h3 class="font-bold text-amber-900">نظام التكلفة المعيارية معطّل على مستوى الـ Tenant</h3>
                <p class="text-sm text-amber-800 mt-1">
                    التقرير يعرض البيانات الموجودة فقط. لتفعيل النظام وبدء احتساب الانحرافات، اذهب إلى
                    <a href="{{ route('accounting.settings.index') }}" class="font-bold underline">إعدادات المحاسبة</a>.
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- KPI strip --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="kpi-card">
            <div class="label">إجمالي المواقِقة (Favorable)</div>
            <div class="value text-green-700">{{ number_format(abs($favorableTotal), 2) }} د.إ</div>
            <div class="text-xs text-gray-500 mt-1">{{ $orders->total() }} أمر</div>
        </div>
        <div class="kpi-card">
            <div class="label">إجمالي غير المواقِقة (Unfavorable)</div>
            <div class="value text-red-700">{{ number_format(abs($unfavorableTotal), 2) }} د.إ</div>
            <div class="text-xs text-gray-500 mt-1">Over-budget</div>
        </div>
        <div class="kpi-card">
            <div class="label">صافي الانحراف</div>
            <div class="value {{ $netVariance > 0 ? 'text-red-700' : 'text-green-700' }}">
                {{ $netVariance > 0 ? '+' : '' }}{{ number_format($netVariance, 2) }} د.إ
            </div>
            <div class="text-xs text-gray-500 mt-1">Net variance</div>
        </div>
        <div class="kpi-card">
            <div class="label">صافي الانحراف %</div>
            <div class="value {{ abs($netVariancePct) > 2 ? 'text-amber-700' : '' }}">
                {{ $netVariancePct > 0 ? '+' : '' }}{{ number_format($netVariancePct, 2) }}%
            </div>
            <div class="text-xs text-gray-500 mt-1">من التكلفة الفعلية</div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports.cost-variance.index') }}" class="filter-bar">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">من تاريخ</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 font-mono">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">إلى تاريخ</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 font-mono">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">نوع الانحراف</label>
                <select name="variance_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    <option value="">— الكل —</option>
                    <option value="favorable"   {{ request('variance_type') === 'favorable' ? 'selected' : '' }}>مواقِقة</option>
                    <option value="unfavorable" {{ request('variance_type') === 'unfavorable' ? 'selected' : '' }}>غير مواقِقة</option>
                    <option value="none"        {{ request('variance_type') === 'none' ? 'selected' : '' }}>تطابق تام</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 font-semibold text-sm">
                    <i class="fas fa-filter ml-1"></i> تطبيق
                </button>
            </div>
            <div class="flex items-end">
                <a href="{{ route('reports.cost-variance.index') }}" class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold text-sm text-center">
                    إعادة تعيين
                </a>
            </div>
        </div>
    </form>

    {{-- Top 5 Losers --}}
    @if($topLoss->count() > 0)
    <div class="filter-bar mb-6">
        <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
            <i class="fas fa-exclamation-triangle text-red-600"></i>
            أعلى 5 منتجات من حيث الانحراف السلبي
        </h3>
        <table class="w-full text-sm">
            <thead class="text-gray-500 border-b border-gray-200">
                <tr>
                    <th class="text-right py-2">المنتج</th>
                    <th class="text-center">عدد الأوامر</th>
                    <th class="text-left">إجمالي الانحراف</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topLoss as $row)
                <tr class="border-b border-gray-100">
                    <td class="py-2 font-semibold">{{ $row->product_name }}</td>
                    <td class="text-center font-mono">{{ $row->orders_count }}</td>
                    <td class="text-left font-mono text-red-700 font-bold">+{{ number_format($row->sum_variance, 2) }} د.إ</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Orders Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gradient-to-l from-amber-50 to-amber-100 text-amber-900">
                    <tr>
                        <th class="py-3 px-4 text-right font-bold">الأمر</th>
                        <th class="py-3 px-4 text-right font-bold">المنتج</th>
                        <th class="py-3 px-4 text-right font-bold">تاريخ الإكمال</th>
                        <th class="py-3 px-4 text-center font-bold">الكمية</th>
                        <th class="py-3 px-4 text-left font-bold">معياري</th>
                        <th class="py-3 px-4 text-left font-bold">فعلي</th>
                        <th class="py-3 px-4 text-left font-bold">انحراف</th>
                        <th class="py-3 px-4 text-center font-bold">النوع</th>
                        <th class="py-3 px-4 text-left font-bold">مواد</th>
                        <th class="py-3 px-4 text-left font-bold">عمالة/مصاريف</th>
                        <th class="py-3 px-4 text-center font-bold">القيد</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                    <tr class="border-b border-gray-100 hover:bg-amber-50/50">
                        <td class="py-2 px-4 font-mono font-semibold">{{ $o->order_number }}</td>
                        <td class="py-2 px-4">{{ $o->product_name }}</td>
                        <td class="py-2 px-4 text-gray-600">{{ $o->produced_at?->format('Y-m-d') }}</td>
                        <td class="py-2 px-4 text-center font-mono">{{ number_format($o->quantity_produced, 0) }}</td>
                        <td class="py-2 px-4 text-left font-mono">{{ number_format($o->standard_cost_at_completion, 2) }}</td>
                        <td class="py-2 px-4 text-left font-mono">{{ number_format($o->actual_cost_at_completion, 2) }}</td>
                        <td class="py-2 px-4 text-left font-mono font-bold {{ (float) $o->total_variance > 0 ? 'text-red-700' : ((float) $o->total_variance < 0 ? 'text-green-700' : '') }}">
                            {{ (float) $o->total_variance > 0 ? '+' : ((float) $o->total_variance < 0 ? '−' : '') }}{{ number_format(abs((float) $o->total_variance), 2) }}
                        </td>
                        <td class="py-2 px-4 text-center">
                            <span class="inline-block px-2 py-1 rounded-full text-[11px] font-bold
                                {{ $o->variance_type === 'favorable' ? 'variance-fav' : ($o->variance_type === 'unfavorable' ? 'variance-unfav' : 'variance-none') }}">
                                @if($o->variance_type === 'favorable') مواتٍ
                                @elseif($o->variance_type === 'unfavorable') غير مواتٍ
                                @else تطابق تام
                                @endif
                            </span>
                        </td>
                        <td class="py-2 px-4 text-left font-mono text-xs">{{ number_format($o->material_variance, 2) }}</td>
                        <td class="py-2 px-4 text-left font-mono text-xs">{{ number_format($o->labor_overhead_variance, 2) }}</td>
                        <td class="py-2 px-4 text-center font-mono text-xs">
                            @if($o->variance_journal_entry_id)
                                <span class="px-2 py-1 bg-gray-100 rounded">#{{ $o->variance_journal_entry_id }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl text-gray-300"></i>
                            <p class="mt-3">لا توجد أوامر تصنيع مكتملة بانحراف مُسجَّل في النطاق المحدد.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
