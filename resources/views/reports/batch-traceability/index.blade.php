@extends('layouts.app')

@section('title', 'تقرير تتبع الدفعات')
@section('page-title', 'تقرير تتبع الدفعات (Batch Traceability)')

@section('content')
<div class="px-2 md:px-6 py-4">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
            <i class="fas fa-project-diagram text-purple-600"></i>
            تتبع الدفعات
        </h1>
        <p class="text-sm text-gray-600 mt-1">
            بحث ثنائي الاتجاه: دفعة خامات → منتج تام، أو العكس.
        </p>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('reports.batch-traceability.index') }}" class="bg-white rounded-2xl border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">وضع البحث</label>
                <select name="mode" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="source" {{ $mode === 'source' ? 'selected' : '' }}>من خامات إلى منتج تام</option>
                    <option value="finished" {{ $mode === 'finished' ? 'selected' : '' }}>من منتج تام إلى خامات</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-700 mb-1">
                    كود الدفعة
                    <span class="text-gray-400">(مثل B-2026-00001 أو FGB-2026-00001)</span>
                </label>
                <input type="text" name="code" value="{{ $code }}"
                       placeholder="أدخل كود الدفعة..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 font-mono">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold text-sm">
                    <i class="fas fa-search ml-1"></i> بحث
                </button>
            </div>
        </div>
    </form>

    @if($notFound)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center">
            <i class="fas fa-exclamation-circle text-amber-500 text-3xl"></i>
            <p class="mt-3 text-amber-900 font-bold">لم يُعثر على نتائج للكود "{{ $code }}"</p>
            <p class="text-xs text-amber-700 mt-1">تأكد من صحة الكود أو أن الدفعة مسجَّلة في النظام.</p>
        </div>
    @elseif($results->isNotEmpty())
        {{-- Results --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-l from-purple-50 to-purple-100 border-b border-gray-200">
                <h3 class="font-bold text-purple-900 flex items-center gap-2">
                    <i class="fas fa-sitemap"></i>
                    @if($mode === 'source')
                        أثر "{{ $code }}" في المنتجات التامة
                    @else
                        المواد الخام في "{{ $code }}"
                    @endif
                </h3>
                <p class="text-xs text-purple-700 mt-1">{{ $results->count() }} نتيجة</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-purple-50 text-purple-900">
                        <tr>
                            @if($mode === 'source')
                                <th class="py-3 px-4 text-right font-bold">كود الدفعة الخام</th>
                                <th class="py-3 px-4 text-right font-bold">المنتج التام</th>
                                <th class="py-3 px-4 text-center font-bold">الكمية المُستهلكة</th>
                                <th class="py-3 px-4 text-center font-bold">سعر الوحدة</th>
                                <th class="py-3 px-4 text-center font-bold">القيمة</th>
                                <th class="py-3 px-4 text-center font-bold">تاريخ الاستهلاك</th>
                            @else
                                <th class="py-3 px-4 text-right font-bold">المنتج التام</th>
                                <th class="py-3 px-4 text-right font-bold">كود الدفعة الخام</th>
                                <th class="py-3 px-4 text-right font-bold">المادة الخام</th>
                                <th class="py-3 px-4 text-center font-bold">الكمية المُستهلكة</th>
                                <th class="py-3 px-4 text-center font-bold">سعر الوحدة</th>
                                <th class="py-3 px-4 text-center font-bold">القيمة</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $r)
                        <tr class="border-b border-gray-100 hover:bg-purple-50/40">
                            @if($mode === 'source')
                                <td class="py-2 px-4 font-mono">{{ $r['source_batch_code'] }}</td>
                                <td class="py-2 px-4 font-mono">
                                    <span class="text-purple-700 font-bold">{{ $r['finished_batch_code'] }}</span>
                                    <span class="text-gray-500 block text-xs">{{ $r['finished_product_name'] }}</span>
                                </td>
                                <td class="py-2 px-4 text-center font-mono">{{ number_format($r['quantity_consumed'], 4) }}</td>
                                <td class="py-2 px-4 text-center font-mono">{{ number_format($r['source_unit_cost'], 2) }}</td>
                                <td class="py-2 px-4 text-center font-mono font-bold">{{ number_format($r['quantity_consumed'] * $r['source_unit_cost'], 2) }}</td>
                                <td class="py-2 px-4 text-center text-gray-500 text-xs">{{ $r['consumed_at'] }}</td>
                            @else
                                <td class="py-2 px-4 font-mono text-purple-700 font-bold">{{ $r['finished_batch_code'] }}</td>
                                <td class="py-2 px-4 font-mono">{{ $r['source_batch_code'] }}</td>
                                <td class="py-2 px-4">{{ $r['source_product_name'] }}</td>
                                <td class="py-2 px-4 text-center font-mono">{{ number_format($r['quantity_consumed'], 4) }}</td>
                                <td class="py-2 px-4 text-center font-mono">{{ number_format($r['source_unit_cost'], 2) }}</td>
                                <td class="py-2 px-4 text-center font-mono font-bold">{{ number_format($r['quantity_consumed'] * $r['source_unit_cost'], 2) }}</td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 text-center">
            <i class="fas fa-info-circle text-blue-500 text-3xl"></i>
            <p class="mt-3 text-blue-900 font-bold">أدخل كود الدفعة للبحث</p>
            <p class="text-xs text-blue-700 mt-1">مثال للبحث في دفعات الخامات: <code class="bg-white px-2 py-1 rounded">B-2026-00001</code> · للمنتجات التامة: <code class="bg-white px-2 py-1 rounded">FGB-2026-00001</code></p>
        </div>
    @endif
</div>
@endsection
