@extends('layouts.app')

@section('title', 'قائمة دخل مقارنة')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">قائمة الدخل المقارنة</h2>
            <p class="text-gray-600 mt-1">مقارنة الأداء المالي بين فترتين</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="text-sm text-gray-600">الفترة الحالية (من)</label>
                <input type="date" name="current_from" value="{{ $currentFrom }}" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm text-gray-600">الفترة الحالية (إلى)</label>
                <input type="date" name="current_to" value="{{ $currentTo }}" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm text-gray-600">الفترة السابقة (من)</label>
                <input type="date" name="previous_from" value="{{ $previousFrom }}" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm text-gray-600">الفترة السابقة (إلى)</label>
                <input type="date" name="previous_to" value="{{ $previousTo }}" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">عرض المقارنة</button>
        </div>
    </form>

    {{-- Report --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-4 py-3 text-right font-semibold text-gray-700 w-1/3">البند</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">الفترة الحالية</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">الفترة السابقة</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">التغيير</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">%</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                {{-- الإيرادات --}}
                <tr class="bg-blue-50 font-bold">
                    <td colspan="5" class="px-4 py-2 text-blue-800">الإيرادات</td>
                </tr>
                @foreach($data['revenues'] as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 pr-8 text-gray-800">{{ $item['name'] }}</td>
                    <td class="px-4 py-2 text-center">{{ number_format($item['current_balance'], 2) }}</td>
                    <td class="px-4 py-2 text-center text-gray-500">{{ number_format($item['previous_balance'], 2) }}</td>
                    <td class="px-4 py-2 text-center {{ $item['change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $item['change'] >= 0 ? '+' : '' }}{{ number_format($item['change'], 2) }}
                    </td>
                    <td class="px-4 py-2 text-center {{ ($item['change_pct'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $item['change_pct'] !== null ? $item['change_pct'] . '%' : '—' }}
                    </td>
                </tr>
                @endforeach
                <tr class="font-bold bg-blue-50 border-t-2 border-blue-200">
                    <td class="px-4 py-2 text-blue-800">إجمالي الإيرادات</td>
                    <td class="px-4 py-2 text-center">{{ number_format($data['current_revenue'], 2) }}</td>
                    <td class="px-4 py-2 text-center text-gray-600">{{ number_format($data['previous_revenue'], 2) }}</td>
                    <td class="px-4 py-2 text-center {{ $data['revenue_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $data['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($data['revenue_change'], 2) }}
                    </td>
                    <td class="px-4 py-2 text-center">{{ $data['revenue_change_pct'] !== null ? $data['revenue_change_pct'] . '%' : '—' }}</td>
                </tr>

                {{-- المصروفات --}}
                <tr class="bg-red-50 font-bold">
                    <td colspan="5" class="px-4 py-2 text-red-800">المصروفات</td>
                </tr>
                @foreach($data['expenses'] as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 pr-8 text-gray-800">{{ $item['name'] }}</td>
                    <td class="px-4 py-2 text-center">{{ number_format($item['current_balance'], 2) }}</td>
                    <td class="px-4 py-2 text-center text-gray-500">{{ number_format($item['previous_balance'], 2) }}</td>
                    <td class="px-4 py-2 text-center {{ $item['change'] <= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $item['change'] >= 0 ? '+' : '' }}{{ number_format($item['change'], 2) }}
                    </td>
                    <td class="px-4 py-2 text-center">{{ $item['change_pct'] !== null ? $item['change_pct'] . '%' : '—' }}</td>
                </tr>
                @endforeach
                <tr class="font-bold bg-red-50 border-t-2 border-red-200">
                    <td class="px-4 py-2 text-red-800">إجمالي المصروفات</td>
                    <td class="px-4 py-2 text-center">{{ number_format($data['current_expense'], 2) }}</td>
                    <td class="px-4 py-2 text-center text-gray-600">{{ number_format($data['previous_expense'], 2) }}</td>
                    <td class="px-4 py-2 text-center {{ $data['expense_change'] <= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $data['expense_change'] >= 0 ? '+' : '' }}{{ number_format($data['expense_change'], 2) }}
                    </td>
                    <td class="px-4 py-2 text-center">—</td>
                </tr>

                {{-- صافي الدخل --}}
                <tr class="font-bold bg-gray-100 border-t-2 border-gray-300 text-lg">
                    <td class="px-4 py-3">صافي الدخل</td>
                    <td class="px-4 py-3 text-center {{ $data['current_income'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ number_format($data['current_income'], 2) }}
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ number_format($data['previous_income'], 2) }}</td>
                    <td class="px-4 py-3 text-center {{ $data['income_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $data['income_change'] >= 0 ? '+' : '' }}{{ number_format($data['income_change'], 2) }}
                    </td>
                    <td class="px-4 py-3 text-center">{{ $data['income_change_pct'] !== null ? $data['income_change_pct'] . '%' : '—' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
