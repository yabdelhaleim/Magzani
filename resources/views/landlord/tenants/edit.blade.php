@extends('landlord.layout')

@section('title', 'تخصيص باقة الشركة: ' . $tenant->id)

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="glass-card rounded-2xl p-4 sm:p-6 lg:p-8 shadow-xl space-y-5 sm:space-y-6">
        <div class="flex flex-wrap justify-between items-center gap-2 border-b border-slate-800 pb-4">
            <h3 class="text-base sm:text-lg font-bold text-white">تعديل اشتراك الشركة</h3>
            <a href="{{ route('super-admin.tenants.index') }}" class="text-xs sm:text-sm text-slate-400 hover:text-white transition font-semibold">إلغاء والعودة</a>
        </div>

        <form action="{{ route('super-admin.tenants.update', $tenant->id) }}" method="POST" class="space-y-5 sm:space-y-6">
            @csrf
            @method('PUT')

            <!-- Tenant ID / Subdomain (Readonly) -->
            <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-400">معرف الشركة / النطاق الفرعي</label>
                <div class="flex bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-3 items-center select-none opacity-60">
                    <span class="text-slate-300 font-mono font-semibold" style="direction: ltr;">{{ $tenant->id }}</span>
                    <span class="text-slate-500 font-mono text-sm ml-2" style="direction: ltr;">.{{ config('tenancy.tenant_domain_suffix', 'localhost') }}</span>
                </div>
            </div>

            <!-- Choose Plan -->
            <div class="space-y-2">
                <label for="plan_id" class="block text-sm font-bold text-slate-300">باقة الاشتراك الحالية *</label>
                @php
                    $currentPlanId = $tenant->plan_id ?? ($tenant->data['plan_id'] ?? '');
                @endphp
                <select id="plan_id" name="plan_id" onchange="togglePlanFeatures(this.value)" class="w-full bg-slate-900 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-3 text-white outline-none transition">
                    <option value="">-- اختر باقة --</option>
                    @foreach($plans as $plan)
                    <option value="{{ $plan->slug }}" {{ $currentPlanId === $plan->slug ? 'selected' : '' }}>
                        {{ $plan->name }} - (${{ $plan->price }}/شهر)
                    </option>
                    @endforeach
                    <option value="custom" {{ $currentPlanId === 'custom' ? 'selected' : '' }}>باقة مخصصة (Custom Plan) 🛠️</option>
                </select>
                @error('plan_id') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Custom Plan Features Checklist -->
            @php
                $customFeatures = $tenant->custom_features ?? ($tenant->data['custom_features'] ?? []);
                if (!is_array($customFeatures)) {
                    $customFeatures = [];
                }
            @endphp
            <div id="custom-features-wrapper" class="{{ $currentPlanId === 'custom' ? '' : 'hidden' }} space-y-3 border-t border-slate-800/60 pt-6">
                <label class="block text-sm font-bold text-slate-200">تخصيص ميزات الباقة المخصصة</label>
                <p class="text-xs text-slate-500 mb-3">اختر الميزات المفتوحة لهذه الشركة فقط:</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-center gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="custom_features[]" value="sales" {{ in_array('sales', $customFeatures) ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500 rounded cursor-pointer">
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">المبيعات والعملاء (Sales)</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="custom_features[]" value="purchases" {{ in_array('purchases', $customFeatures) ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500 rounded cursor-pointer">
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">المشتريات والموردين (Purchases)</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="custom_features[]" value="warehouses" {{ in_array('warehouses', $customFeatures) ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500 rounded cursor-pointer">
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">المخازن والمخزون (Warehouses)</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="custom_features[]" value="pos" {{ in_array('pos', $customFeatures) ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500 rounded cursor-pointer">
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">نقاط البيع الكاشير (POS)</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="custom_features[]" value="manufacturing" {{ in_array('manufacturing', $customFeatures) ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500 rounded cursor-pointer">
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">التصنيع وتكاليف الإنتاج</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="custom_features[]" value="accounting" {{ in_array('accounting', $customFeatures) ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500 rounded cursor-pointer">
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">الحسابات والمالية (Accounting)</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="border-t border-slate-800/60 pt-6 flex flex-col sm:flex-row gap-3 sm:gap-4">
                <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition shadow-lg text-sm sm:text-base">
                    حفظ وتحديث الاشتراك
                </button>
                <a href="{{ route('super-admin.tenants.index') }}" class="flex-1 py-3 text-center bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-bold transition border border-slate-700/30 text-sm sm:text-base">
                    إلغاء
                </a>
            </div>
        </form>
    </div>

</div>

<script>
    function togglePlanFeatures(val) {
        var wrapper = document.getElementById('custom-features-wrapper');
        if (val === 'custom') {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    }
</script>
@endsection
