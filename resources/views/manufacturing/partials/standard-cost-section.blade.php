{{-- Gap 2 — Standard Costing section on BOM show page --}}
<div class="mfg-section mfg-card" style="border-color: #fde68a;">
    <div class="mfg-card-top" style="background: linear-gradient(135deg,#fef3c7 0%,#fff7ed 100%);">
        <div class="icon-wrap" style="background: #f59e0b; color: #fff;">
            <i class="fas fa-calculator"></i>
        </div>
        <h3>التكلفة المعيارية (Standard Costing) — Gap 2</h3>
    </div>
    <div class="mfg-card-body" style="padding: 22px;">
        @php
            $standardMaterial = (float) $manufacturingCost->standard_material_cost;
            $standardLabor    = (float) $manufacturingCost->standard_labor_cost;
            $standardOverhead = (float) $manufacturingCost->standard_overhead_cost;
            $standardTotal    = $manufacturingCost->getEffectiveStandardCostAttribute();
            $hasStandard      = $standardTotal > 0;
        @endphp

        @if($hasStandard)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div class="p-4 bg-amber-50 rounded-lg border border-amber-200">
                    <div class="text-[11px] text-amber-800 font-semibold uppercase tracking-wider">معياري — مواد خام</div>
                    <div class="text-2xl font-black text-amber-900 mt-1">{{ number_format($standardMaterial, 4) }}</div>
                </div>
                <div class="p-4 bg-amber-50 rounded-lg border border-amber-200">
                    <div class="text-[11px] text-amber-800 font-semibold uppercase tracking-wider">معياري — عمالة</div>
                    <div class="text-2xl font-black text-amber-900 mt-1">{{ number_format($standardLabor, 4) }}</div>
                </div>
                <div class="p-4 bg-amber-50 rounded-lg border border-amber-200">
                    <div class="text-[11px] text-amber-800 font-semibold uppercase tracking-wider">معياري — مصاريف إضافية</div>
                    <div class="text-2xl font-black text-amber-900 mt-1">{{ number_format($standardOverhead, 4) }}</div>
                </div>
                <div class="p-4 bg-amber-200 rounded-lg border-2 border-amber-400">
                    <div class="text-[11px] text-amber-900 font-bold uppercase tracking-wider">الإجمالي المعياري / وحدة</div>
                    <div class="text-2xl font-black text-amber-950 mt-1">{{ number_format($standardTotal, 4) }}</div>
                </div>
            </div>

            @if($manufacturingCost->standard_cost_effective_from)
                <p class="text-xs text-gray-600 mb-3">
                    سارية المفعول من: <span class="font-semibold">{{ $manufacturingCost->standard_cost_effective_from }}</span>
                    @if($manufacturingCost->standardCostUpdater)
                        · آخر تحديث بواسطة: <span class="font-semibold">{{ $manufacturingCost->standardCostUpdater->name }}</span>
                    @endif
                </p>
            @endif
        @else
            <p class="text-sm text-amber-700 mb-4">
                ⚠️ لم تُحدَّد تكلفة معيارية لهذا المنتج بعد. أوامر التصنيع المرتبطة ستُرحَّل بسلوك Actual Costing.
            </p>
        @endif

        <details class="mt-3">
            <summary class="cursor-pointer text-sm font-bold text-amber-800 hover:text-amber-900 select-none">
                <i class="fas fa-edit ml-1"></i> {{ $hasStandard ? 'تعديل التكلفة المعيارية' : 'تعيين تكلفة معيارية' }}
            </summary>
            <form method="POST" action="{{ route('manufacturing.standard-cost.update', $manufacturingCost) }}" class="mt-4 p-4 bg-amber-50 rounded-lg border border-amber-200">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">معياري — مواد خام / وحدة</label>
                        <input type="number" step="0.0001" min="0" name="standard_material_cost"
                               value="{{ old('standard_material_cost', $standardMaterial) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">معياري — عمالة / وحدة</label>
                        <input type="number" step="0.0001" min="0" name="standard_labor_cost"
                               value="{{ old('standard_labor_cost', $standardLabor) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">معياري — مصاريف إضافية / وحدة</label>
                        <input type="number" step="0.0001" min="0" name="standard_overhead_cost"
                               value="{{ old('standard_overhead_cost', $standardOverhead) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">تاريخ السريان</label>
                        <input type="date" name="standard_cost_effective_from"
                               value="{{ old('standard_cost_effective_from', $manufacturingCost->standard_cost_effective_from?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 font-mono">
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 font-semibold text-sm">
                        <i class="fas fa-save ml-1"></i> حفظ التكلفة المعيارية
                    </button>
                </div>
            </form>
        </details>

        @unless(\App\Models\AccountingSetting::first()?->standard_costing_enabled)
            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded text-xs text-blue-800">
                💡 نظام التكلفة المعيارية <strong>معطّل</strong> على مستوى الـ Tenant.
                لتفعيله اذهب إلى
                <a href="{{ route('accounting.settings.index') }}" class="font-bold underline">إعدادات المحاسبة</a>.
            </div>
        @endunless
    </div>
</div>
