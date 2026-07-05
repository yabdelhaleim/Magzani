@extends('layouts.app')

@section('title', 'إدارة الأصول الثابتة')

@section('content')
<div class="space-y-6 font-sans">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">الأصول الثابتة والإهلاك</h2>
            <p class="text-gray-600 mt-1">تسجيل ومتابعة الأصول الثابتة، احتساب الإهلاك شهرياً، واستبعاد الأصول.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('accounting.fixed-assets.depreciate.form') }}" class="px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg font-medium border border-purple-200 transition-colors">
                <i class="fas fa-calculator ml-1"></i> احتساب الإهلاك الشهري
            </a>
            <a href="{{ route('accounting.fixed-assets.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                <i class="fas fa-plus ml-1"></i> تسجيل أصل جديد
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
            {!! session('success') !!}
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">إجمالي تكلفة الشراء</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ number_format($totalCost, 2) }}</h3>
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center text-blue-600">
                <i class="fas fa-wallet text-xl"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">مجمع الإهلاك الكلي</p>
                <h3 class="text-2xl font-bold text-red-600">{{ number_format($totalAccumulated, 2) }}</h3>
            </div>
            <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center text-red-600">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">صافي القيمة الدفترية</p>
                <h3 class="text-2xl font-bold text-green-600">{{ number_format($netBookValue, 2) }}</h3>
            </div>
            <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center text-green-600">
                <i class="fas fa-book-open text-xl"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">أصول مكتملة الإهلاك</p>
                <h3 class="text-2xl font-bold text-purple-600">{{ $fullyDepreciatedCount }}</h3>
            </div>
            <div class="w-12 h-12 bg-purple-50 rounded-full flex items-center justify-center text-purple-600">
                <i class="fas fa-check-double text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Assets List Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600">
                    <tr>
                        <th class="px-6 py-4">كود الأصل</th>
                        <th class="px-6 py-4">اسم الأصل</th>
                        <th class="px-6 py-4">تاريخ الشراء</th>
                        <th class="px-6 py-4">تكلفة الشراء</th>
                        <th class="px-6 py-4">القيمة التخريدية</th>
                        <th class="px-6 py-4">مجمع الإهلاك</th>
                        <th class="px-6 py-4">القيمة الدفترية</th>
                        <th class="px-6 py-4 text-center">الحالة</th>
                        <th class="px-6 py-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-gray-800">{{ $asset->code }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $asset->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $asset->purchase_date->toDateString() }}</td>
                            <td class="px-6 py-4 font-mono">{{ number_format($asset->purchase_cost, 2) }}</td>
                            <td class="px-6 py-4 font-mono text-gray-500">{{ number_format($asset->scrap_value, 2) }}</td>
                            <td class="px-6 py-4 font-mono text-red-600">{{ number_format($asset->accumulated_depreciation, 2) }}</td>
                            <td class="px-6 py-4 font-mono font-bold text-green-600">{{ number_format($asset->book_value, 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($asset->status === 'active')
                                    <span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-green-50 text-green-700 border border-green-200">نشط</span>
                                @elseif($asset->status === 'fully_depreciated')
                                    <span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-purple-50 text-purple-700 border border-purple-200">مستهلك بالكامل</span>
                                @elseif($asset->status === 'disposed')
                                    <span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-red-50 text-red-700 border border-red-200">مستبعد/مباع</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('accounting.fixed-assets.show', $asset->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                    عرض التفاصيل
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-gray-500">لا توجد أصول ثابتة مسجلة بعد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-gray-50 border-t border-gray-100">
            {{ $assets->links() }}
        </div>
    </div>
</div>
@endsection
