@extends('layouts.app')

@section('title', 'النسب المالية')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">النسب المالية</h2>
            <p class="text-gray-600 mt-1">تحليل نسب السيولة والربحية والنشاط حتى {{ $asOf }}</p>
        </div>
        <form method="GET" class="flex gap-3 items-end">
            <div>
                <label class="text-sm text-gray-600">حتى تاريخ</label>
                <input type="date" name="as_of" value="{{ $asOf }}" class="px-3 py-2 border rounded-lg text-sm">
            </div>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">عرض</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- نسب السيولة --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-tint text-blue-500"></i> نسب السيولة
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-700">نسبة التداول</span>
                        <p class="text-xs text-gray-500">Current Ratio</p>
                    </div>
                    <span class="text-xl font-bold {{ ($ratios['liquidity']['current_ratio'] ?? 0) >= 1 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $ratios['liquidity']['current_ratio'] ?? '—' }}
                    </span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-700">نسبة السيولة السريعة</span>
                        <p class="text-xs text-gray-500">Quick Ratio</p>
                    </div>
                    <span class="text-xl font-bold {{ ($ratios['liquidity']['quick_ratio'] ?? 0) >= 1 ? 'text-green-600' : 'text-yellow-600' }}">
                        {{ $ratios['liquidity']['quick_ratio'] ?? '—' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- نسب الربحية --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-chart-line text-green-500"></i> نسب الربحية
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-700">هامش الربح الإجمالي</span>
                        <p class="text-xs text-gray-500">Gross Margin</p>
                    </div>
                    <span class="text-xl font-bold text-gray-800">{{ $ratios['profitability']['gross_margin'] !== null ? $ratios['profitability']['gross_margin'] . '%' : '—' }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-700">هامش صافي الربح</span>
                        <p class="text-xs text-gray-500">Net Margin</p>
                    </div>
                    <span class="text-xl font-bold {{ ($ratios['profitability']['net_margin'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $ratios['profitability']['net_margin'] !== null ? $ratios['profitability']['net_margin'] . '%' : '—' }}
                    </span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-700">العائد على الأصول</span>
                        <p class="text-xs text-gray-500">ROA</p>
                    </div>
                    <span class="text-xl font-bold text-gray-800">{{ $ratios['profitability']['return_on_assets'] !== null ? $ratios['profitability']['return_on_assets'] . '%' : '—' }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-700">العائد على حقوق الملكية</span>
                        <p class="text-xs text-gray-500">ROE</p>
                    </div>
                    <span class="text-xl font-bold text-gray-800">{{ $ratios['profitability']['return_on_equity'] !== null ? $ratios['profitability']['return_on_equity'] . '%' : '—' }}</span>
                </div>
            </div>
        </div>

        {{-- نسب المديونية --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-balance-scale text-orange-500"></i> نسب المديونية
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-700">الديون إلى حقوق الملكية</span>
                        <p class="text-xs text-gray-500">Debt to Equity</p>
                    </div>
                    <span class="text-xl font-bold text-gray-800">{{ $ratios['leverage']['debt_to_equity'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-700">الديون إلى الأصول</span>
                        <p class="text-xs text-gray-500">Debt to Assets</p>
                    </div>
                    <span class="text-xl font-bold text-gray-800">{{ $ratios['leverage']['debt_to_assets'] ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- نسب النشاط --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-sync-alt text-purple-500"></i> نسب النشاط
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-700">معدل دوران الذمم المدينة</span>
                        <p class="text-xs text-gray-500">Receivables Turnover</p>
                    </div>
                    <span class="text-xl font-bold text-gray-800">{{ $ratios['activity']['receivables_turnover'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-700">معدل دوران المخزون</span>
                        <p class="text-xs text-gray-500">Inventory Turnover</p>
                    </div>
                    <span class="text-xl font-bold text-gray-800">{{ $ratios['activity']['inventory_turnover'] ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ملخص --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">ملخص الأرقام الرئيسية</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach(['total_assets' => 'الأصول', 'total_liabilities' => 'الخصوم', 'total_equity' => 'حقوق الملكية', 'total_revenue' => 'الإيرادات', 'net_income' => 'صافي الدخل'] as $key => $label)
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="text-lg font-bold text-gray-800">{{ number_format($ratios['summary'][$key], 2) }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
