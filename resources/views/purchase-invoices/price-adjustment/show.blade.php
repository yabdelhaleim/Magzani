@extends('layouts.app')

@section('title', 'تسوية سعر فاتورة متأخرة')
@section('page-title', 'تسوية سعر متأخرة — بند #' . $item->id)

@section('content')
<div class="px-2 md:px-6 py-4 max-w-4xl">

    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
            <i class="fas fa-balance-scale-right text-amber-600"></i>
            تسوية سعر متأخرة
        </h1>
        <p class="text-sm text-gray-600 mt-1">
            ربط فرق السعر بدفعة المواد الخام وقياس التأثير على المخزون و COGS.
        </p>
    </div>

    {{-- Invoice context --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="text-xs text-gray-500">الفاتورة</div>
                <div class="font-bold">{{ $invoice->invoice_number }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">المنتج</div>
                <div class="font-bold">{{ $item->product?->name ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">سعر البند المسجَّل</div>
                <div class="font-mono">{{ number_format($item->unit_cost, 4) }}</div>
            </div>
        </div>
    </div>

    @if(!$batch)
        <div class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center">
            <i class="fas fa-unlink text-red-500 text-3xl"></i>
            <p class="mt-3 text-red-900 font-bold">لا توجد دفعة مواد خام مرتبطة بهذا البند</p>
            <p class="text-xs text-red-700 mt-1">يجب إنشاء ربط في <code>material_batch_purchase_links</code> قبل تسجيل التسوية.</p>
        </div>
    @else
        {{-- Batch info --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
            <h3 class="font-bold text-gray-800 mb-3">الدفعة المرتبطة</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-xs text-gray-500">كود الدفعة</div>
                    <div class="font-mono font-bold text-purple-700">{{ $batch->batch_code }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">السعر الحالي</div>
                    <div class="font-mono">{{ number_format($batch->unit_cost, 4) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">السعر الأصلي (مُقفَل)</div>
                    <div class="font-mono text-gray-600">{{ number_format($batch->getOriginalPriceSnapshotAttribute(), 4) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">متبقي في المخزون</div>
                    <div class="font-mono">{{ number_format($batch->remaining_qty, 4) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">عدد المنتجات التامة المشتقة</div>
                    <div class="font-mono"><span class="px-2 py-0.5 bg-purple-100 rounded">{{ $descendantCount }}</span></div>
                </div>
            </div>
        </div>

        {{-- Apply form --}}
        <form method="POST" action="{{ route('purchase.price-adjustment.apply', [$invoice, $item]) }}" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-4">
            @csrf
            <h3 class="font-bold text-amber-900 mb-3">سعر جديد</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">السعر الجديد / وحدة (صافي قبل الضريبة)</label>
                    <input type="number" step="0.0001" min="0" name="new_unit_cost"
                           value="{{ old('new_unit_cost', $item->unit_cost) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 font-mono text-lg"
                           required>
                    <p class="text-[10px] text-gray-500 mt-1">سيُرحَّل قيد مستقل لهذا البند فقط (Q5).</p>
                </div>
                <div>
                    <button type="submit" class="w-full px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 font-semibold">
                        <i class="fas fa-calculator ml-1"></i> ترحيل التسوية
                    </button>
                </div>
            </div>
        </form>

        {{-- Previous adjustments --}}
        @if($previousAdjustments->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b">
                <h3 class="font-bold text-gray-800">التسويات السابقة على هذه الدفعة</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 text-right">التاريخ</th>
                        <th class="py-2 px-4 text-right">من</th>
                        <th class="py-2 px-4 text-right">إلى</th>
                        <th class="py-2 px-4 text-center">الفرق/وحدة</th>
                        <th class="py-2 px-4 text-center">مخزون</th>
                        <th class="py-2 px-4 text-center">COGS</th>
                        <th class="py-2 px-4 text-center">القيد</th>
                        <th class="py-2 px-4 text-center">نوع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($previousAdjustments as $adj)
                    <tr class="border-b">
                        <td class="py-2 px-4 text-xs">{{ $adj->applied_at?->format('Y-m-d H:i') }}</td>
                        <td class="py-2 px-4 font-mono">{{ number_format($adj->original_unit_cost, 4) }}</td>
                        <td class="py-2 px-4 font-mono">{{ number_format($adj->new_unit_cost, 4) }}</td>
                        <td class="py-2 px-4 text-center font-mono {{ $adj->price_diff > 0 ? 'text-red-700' : 'text-green-700' }}">
                            {{ $adj->price_diff > 0 ? '+' : '' }}{{ number_format($adj->price_diff, 4) }}
                        </td>
                        <td class="py-2 px-4 text-center font-mono text-xs">{{ number_format($adj->inventory_impact, 2) }}</td>
                        <td class="py-2 px-4 text-center font-mono text-xs">{{ number_format($adj->cogs_impact, 2) }}</td>
                        <td class="py-2 px-4 text-center font-mono text-xs">
                            @if($adj->journal_entry_id)
                                <a href="#" class="text-blue-600 underline">#{{ $adj->journal_entry_id }}</a>
                            @else — @endif
                        </td>
                        <td class="py-2 px-4 text-center">
                            @if($adj->fallback_used)
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs rounded font-bold">Fallback 5160</span>
                            @else
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded font-bold">مفصَّل</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    @endif
</div>
@endsection
