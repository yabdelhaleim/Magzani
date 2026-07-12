@extends('landlord.layout')

@section('title', 'تسجيل شركة جديدة في المنصة')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="glass-card rounded-2xl p-4 sm:p-6 lg:p-8 shadow-xl space-y-5 sm:space-y-6">
        <div class="flex flex-wrap justify-between items-center gap-2 border-b border-slate-800 pb-4">
            <h3 class="text-base sm:text-lg font-bold text-white">إعدادات حساب الشركة الجديد</h3>
            <a href="{{ route('super-admin.tenants.index') }}" class="text-xs sm:text-sm text-slate-400 hover:text-white transition">إلغاء والعودة</a>
        </div>

        <form action="{{ route('super-admin.tenants.store') }}" method="POST" class="space-y-5 sm:space-y-6">
            @csrf

            <!-- Tenant ID / Subdomain -->
            <div class="space-y-2">
                <label for="tenant_id" class="block text-sm font-bold text-slate-300">معرف الشركة / النطاق الفرعي *</label>
                <div class="flex bg-slate-900 border border-slate-800 focus-within:border-indigo-500 rounded-xl px-4 py-1 items-center transition">
                    <input type="text" id="tenant_id" name="tenant_id" required class="bg-transparent border-none outline-none text-white py-2 flex-grow text-left font-semibold" placeholder="مثال: my-store" style="direction: ltr;">
                    <span class="text-slate-500 font-mono text-sm ml-2" style="direction: ltr;">.{{ config('tenancy.tenant_domain_suffix', 'localhost') }}</span>
                </div>
                <span class="text-xs text-slate-500 block">اكتب أحرفاً إنجليزية صغيرة فقط وبدون مسافات. سيكون هذا هو اسم رابط تسجيل الدخول وقاعدة البيانات.</span>
                @error('tenant_id') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Choose Plan -->
            <div class="space-y-2">
                <label for="plan_id" class="block text-sm font-bold text-slate-300">اختر باقة الاشتراك *</label>
                <select id="plan_id" name="plan_id" onchange="togglePlanFeatures(this.value)" class="w-full bg-slate-900 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-3 text-white outline-none transition">
                    <option value="">-- اختر باقة --</option>
                    @foreach($plans as $plan)
                    <option value="{{ $plan->slug }}">{{ $plan->name }} - (${{ $plan->price }}/شهر)</option>
                    @endforeach
                    <option value="custom">باقة مخصصة (Custom Plan) 🛠️</option>
                </select>
                @error('plan_id') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Custom Plan Features Checklist (Initially hidden) -->
            <div id="custom-features-wrapper" class="hidden space-y-3 border-t border-slate-800/60 pt-6">
                <label class="block text-sm font-bold text-slate-200">تخصيص ميزات الباقة المخصصة</label>
                <p class="text-xs text-slate-500 mb-3">اختر الميزات المفتوحة لهذه الباقة المخصصة فقط:</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-center gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="custom_features[]" value="sales" class="w-5 h-5 accent-indigo-500 rounded cursor-pointer" checked>
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">المبيعات والعملاء (Sales)</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="custom_features[]" value="purchases" class="w-5 h-5 accent-indigo-500 rounded cursor-pointer" checked>
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">المشتريات والموردين (Purchases)</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="custom_features[]" value="warehouses" class="w-5 h-5 accent-indigo-500 rounded cursor-pointer" checked>
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">المخازن والمخزون (Warehouses)</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="custom_features[]" value="pos" class="w-5 h-5 accent-indigo-500 rounded cursor-pointer">
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">نقاط البيع الكاشير (POS)</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="custom_features[]" value="manufacturing" class="w-5 h-5 accent-indigo-500 rounded cursor-pointer">
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">التصنيع وتكاليف الإنتاج</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="custom_features[]" value="accounting" class="w-5 h-5 accent-indigo-500 rounded cursor-pointer" checked>
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">الحسابات والمالية (Accounting)</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Auto-seed User Admin Info Notification -->
            <div class="p-4 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 rounded-xl text-xs leading-relaxed flex gap-3">
                <i class="fa-solid fa-circle-info text-base"></i>
                <div>
                    <span class="font-bold block mb-1">تنبيه إنشاء حساب المدير تلقائياً:</span>
                    عند إنشاء الشركة، سيقوم النظام تلقائياً بإنشاء حساب المدير العام للشركة بالبيانات التالية لتتمكن من الدخول مباشرة:
                    <br>البريد: <span class="font-mono font-bold text-white">admin@اسم_الشركة.com</span>
                    <br>كلمة المرور: <span class="font-mono font-bold text-white">password</span>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="border-t border-slate-800/60 pt-6 flex flex-col sm:flex-row gap-3 sm:gap-4">
                <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition shadow-lg text-sm sm:text-base">
                    تسجيل الشركة وبناء قاعدة البيانات
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
