@extends('layouts.app')

@section('title', 'الميزانية العمومية')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">الميزانية العمومية (Balance Sheet)</h2>
            <p class="text-gray-600 mt-1">تقرير المركز المالي للمنشأة موضحاً الأصول والالتزامات وحقوق الملكية في تاريخ محدد</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors no-print">
            <i class="fas fa-print ml-1"></i> طباعة التقرير
        </button>
    </div>

    <!-- Filters (No Print) -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 no-print">
        <form method="GET" action="{{ route('accounting.reports.balance-sheet') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">في تاريخ (As of)</label>
                <input type="date" name="as_of" value="{{ request('as_of', $asOf) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-1.5 w-full">
                    <i class="fas fa-filter"></i> عرض الميزانية
                </button>
            </div>
        </form>
    </div>

    <!-- Report Sheet Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-5xl mx-auto p-8 space-y-8 font-sans">
        <!-- Letterhead -->
        <div class="text-center pb-6 border-b-2 border-gray-200">
            <h3 class="text-2xl font-bold text-gray-800">الميزانية العمومية (قائمة المركز المالي)</h3>
            <p class="text-sm text-gray-500 mt-2">كما هي في تاريخ: <span class="font-mono font-semibold">{{ $data['as_of'] }}</span></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- LEFT COLUMN: ASSETS -->
            <div class="space-y-6">
                <div>
                    <h4 class="text-lg font-bold text-gray-800 bg-gray-50 p-2.5 rounded-lg mb-3">1. الأصول (Assets)</h4>
                    <div class="space-y-2.5 font-mono text-sm px-3">
                        @foreach($data['assets'] as $asset)
                            <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                                <span class="text-gray-700 font-sans font-medium">{{ $asset['code'] }} - {{ $asset['name'] }}</span>
                                <span class="text-gray-900 font-bold">{{ number_format($asset['balance'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="p-4 bg-blue-50 border border-blue-100 rounded-lg flex justify-between items-center font-bold text-base text-blue-900 font-mono">
                    <span class="font-sans">إجمالي الأصول (A):</span>
                    <span>{{ number_format($data['total_assets'], 2) }}</span>
                </div>
            </div>

            <!-- RIGHT COLUMN: LIABILITIES & EQUITY -->
            <div class="space-y-6 flex flex-col justify-between">
                <div>
                    <!-- Liabilities -->
                    <div class="mb-6">
                        <h4 class="text-lg font-bold text-gray-800 bg-gray-50 p-2.5 rounded-lg mb-3">2. الالتزامات (Liabilities)</h4>
                        <div class="space-y-2.5 font-mono text-sm px-3">
                            @foreach($data['liabilities'] as $liab)
                                <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                                    <span class="text-gray-700 font-sans font-medium">{{ $liab['code'] }} - {{ $liab['name'] }}</span>
                                    <span class="text-gray-900 font-bold">{{ number_format($liab['balance'], 2) }}</span>
                                </div>
                            @endforeach
                            <div class="flex justify-between items-center py-2.5 text-sm font-bold text-gray-700">
                                <span class="font-sans">إجمالي الالتزامات:</span>
                                <span>{{ number_format($data['total_liabilities'], 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Equity -->
                    <div>
                        <h4 class="text-lg font-bold text-gray-800 bg-gray-50 p-2.5 rounded-lg mb-3">3. حقوق الملكية (Equity)</h4>
                        <div class="space-y-2.5 font-mono text-sm px-3">
                            @foreach($data['equity'] as $eq)
                                <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                                    <span class="text-gray-700 font-sans font-medium">{{ $eq['code'] }} - {{ $eq['name'] }}</span>
                                    <span class="text-gray-900 font-bold">{{ number_format($eq['balance'], 2) }}</span>
                                </div>
                            @endforeach
                            <div class="flex justify-between items-center py-2.5 text-sm font-bold text-gray-700">
                                <span class="font-sans">إجمالي حقوق الملكية:</span>
                                <span>{{ number_format($data['total_equity'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-purple-50 border border-purple-100 rounded-lg flex justify-between items-center font-bold text-base text-purple-900 font-mono">
                    <span class="font-sans">إجمالي الالتزامات وحقوق الملكية (L + E):</span>
                    <span>{{ number_format($data['total_liabilities'] + $data['total_equity'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Verification Alert -->
        @if($data['is_balanced'])
            <div class="p-4 bg-green-50 text-green-800 border border-green-200 text-center font-bold text-sm rounded-xl">
                ✅ الميزانية متوازنة تماماً: الأصول ({{ number_format($data['total_assets'], 2) }}) = الالتزامات وحقوق الملكية ({{ number_format($data['total_liabilities'] + $data['total_equity'], 2) }})
            </div>
        @else
            <div class="p-4 bg-red-50 text-red-800 border border-red-200 text-center font-bold text-sm rounded-xl animate-pulse">
                ⚠️ خلل محاسبي: الميزانية غير متوازنة! فارق الاختلاف: {{ number_format(abs($data['total_assets'] - ($data['total_liabilities'] + $data['total_equity'])), 2) }}
            </div>
        @endif
    </div>
</div>

@style
@media print {
    .no-print { display: none !important; }
    body { background: #fff !important; }
}
@endstyle
@endsection
