{{-- Gap 4 — Batch Genealogy section on Manufacturing Order show --}}
@php
    $finishedBatch = \App\Models\FinishedGoodBatch::where('manufacturing_order_id', $manufacturingOrder->id)->first();
    $sourceGenealogy = $finishedBatch
        ? $finishedBatch->genealogyLinks()->with('sourceBatch.product')->get()
        : collect();
@endphp

<div class="mfg-card mfg-section" style="border-color: #c4b5fd;">
    <div class="mfg-card-top" style="background: linear-gradient(135deg, #ede9fe 0%, #f5f3ff 100%);">
        <div class="icon-wrap" style="background: #7c3aed; color: #fff;">
            <i class="fas fa-project-diagram"></i>
        </div>
        <h3>تتبع الدفعات (Batch Genealogy) — Gap 4</h3>
    </div>
    <div class="mfg-card-body" style="padding: 22px;">
        @if($finishedBatch)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                    <div class="text-[11px] text-purple-800 font-bold uppercase tracking-wider">كود المنتج التام</div>
                    <div class="text-lg font-black text-purple-900 mt-1 font-mono">{{ $finishedBatch->batch_code }}</div>
                </div>
                <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                    <div class="text-[11px] text-purple-800 font-bold uppercase tracking-wider">كمية مُنتَجة</div>
                    <div class="text-lg font-black text-purple-900 mt-1 font-mono">{{ number_format($finishedBatch->quantity, 4) }}</div>
                </div>
                <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                    <div class="text-[11px] text-purple-800 font-bold uppercase tracking-wider">متبقي في المخزون</div>
                    <div class="text-lg font-black text-purple-900 mt-1 font-mono">{{ number_format($finishedBatch->remaining_qty, 4) }}</div>
                </div>
            </div>

            <h4 class="font-bold text-gray-800 mb-2 mt-3">المستهلك من دفعات الخامات:</h4>
            <table class="w-full text-sm">
                <thead class="bg-purple-50 text-purple-900">
                    <tr>
                        <th class="py-2 px-3 text-right font-bold">كود الدفعة الخام</th>
                        <th class="py-2 px-3 text-right font-bold">المادة الخام</th>
                        <th class="py-2 px-3 text-center font-bold">الكمية المستهلكة</th>
                        <th class="py-2 px-3 text-center font-bold">سعر الوحدة</th>
                        <th class="py-2 px-3 text-center font-bold">القيمة المساهمة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sourceGenealogy as $link)
                    <tr class="border-b border-purple-100">
                        <td class="py-2 px-3 font-mono text-purple-700 font-bold">
                            {{ $link->sourceBatch?->batch_code }}
                        </td>
                        <td class="py-2 px-3">{{ $link->sourceBatch?->product?->name ?? '—' }}</td>
                        <td class="py-2 px-3 text-center font-mono">{{ number_format($link->quantity_consumed, 4) }}</td>
                        <td class="py-2 px-3 text-center font-mono">{{ number_format($link->source_unit_cost_snapshot, 4) }}</td>
                        <td class="py-2 px-3 text-center font-mono font-bold">{{ number_format($link->contribution_value, 4) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p class="text-xs text-gray-500 mt-3">
                <i class="fas fa-external-link-alt"></i>
                بحث كامل في
                <a href="{{ route('reports.batch-traceability.index', ['mode' => 'finished', 'code' => $finishedBatch->batch_code]) }}"
                   class="text-purple-700 font-bold underline">تقرير تتبع الدفعات</a>.
            </p>
        @else
            <p class="text-sm text-amber-700">
                ⚠️ لم يُسجَّل تتبع دفعات لهذا الأمر. تأكد أن المادة الخام مرتبطة بدفعة مسجَّلة في النظام.
            </p>
        @endif
    </div>
</div>
