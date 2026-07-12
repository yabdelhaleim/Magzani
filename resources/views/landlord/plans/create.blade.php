@extends('landlord.layout')

@section('title', 'إنشاء باقة اشتراك جديدة')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="glass-card rounded-2xl p-4 sm:p-6 lg:p-8 shadow-xl space-y-5 sm:space-y-6">
        <div class="flex flex-wrap justify-between items-center gap-2 border-b border-slate-800 pb-4">
            <h3 class="text-base sm:text-lg font-bold text-white">إعدادات الباقة الجديدة</h3>
            <a href="{{ route('super-admin.plans.index') }}" class="text-xs sm:text-sm text-slate-400 hover:text-white transition">إلغاء والعودة</a>
        </div>

        <form action="{{ route('super-admin.plans.store') }}" method="POST" class="space-y-5 sm:space-y-6">
            @csrf

            <!-- Plan Name -->
            <div class="space-y-2">
                <label for="name" class="block text-sm font-bold text-slate-300">اسم الباقة *</label>
                <input type="text" id="name" name="name" required class="w-full bg-slate-900 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-3 text-white outline-none transition" placeholder="مثال: الباقة الاحترافية">
                @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Plan Slug -->
            <div class="space-y-2">
                <label for="slug" class="block text-sm font-bold text-slate-300">المعرف اللاتيني الفريد (Slug) *</label>
                <input type="text" id="slug" name="slug" required class="w-full bg-slate-900 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-3 text-white outline-none transition text-left" placeholder="مثال: pro-plan" style="direction: ltr;">
                <span class="text-xs text-slate-500 block">يُستخدم هذا المعرف في روابط الدفع والبرمجة الداخلية ويجب أن يكون فريداً بالأحرف اللاتينية فقط وبدون مسافات.</span>
                @error('slug') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Price -->
                <div class="space-y-2">
                    <label for="price" class="block text-sm font-bold text-slate-300">السعر الشهري/السنوي ($) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required class="w-full bg-slate-900 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-3 text-white outline-none transition" placeholder="مثال: 39.00">
                    @error('price') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Billing Period -->
                <div class="space-y-2">
                    <label for="billing_period" class="block text-sm font-bold text-slate-300">دورة الدفع *</label>
                    <select id="billing_period" name="billing_period" class="w-full bg-slate-900 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-3 text-white outline-none transition">
                        <option value="monthly">شهرياً</option>
                        <option value="yearly">سنوياً</option>
                    </select>
                    @error('billing_period') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="space-y-2">
                <label for="description" class="block text-sm font-bold text-slate-300">وصف الباقة</label>
                <textarea id="description" name="description" rows="3" class="w-full bg-slate-900 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-3 text-white outline-none transition" placeholder="اكتب نبذة مختصرة عن الباقة لعرضها للمشتركين..."></textarea>
                @error('description') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Features Selection -->
            <div class="space-y-3 border-t border-slate-800/60 pt-6">
                <label class="block text-sm font-bold text-slate-200">تخصيص ميزات الباقة</label>
                <p class="text-xs text-slate-500 mb-3">اختر الميزات البرمجية التي سيتم تفعيلها تلقائياً للعملاء المشتركين في هذه الباقة:</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Feature: Sales -->
                    <label class="flex items-start gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="features[]" value="sales" class="w-5 h-5 accent-indigo-500 rounded cursor-pointer mt-0.5" checked>
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">المبيعات والعملاء (Sales)</span>
                            <span class="text-xs text-slate-500 block">فواتير المبيعات، المرتجعات، وإدارة حسابات وكشوفات العملاء.</span>
                        </div>
                    </label>

                    <!-- Feature: Purchases -->
                    <label class="flex items-start gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="features[]" value="purchases" class="w-5 h-5 accent-indigo-500 rounded cursor-pointer mt-0.5" checked>
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">المشتريات والموردين (Purchases)</span>
                            <span class="text-xs text-slate-500 block">فواتير المشتريات، المرتجعات، وإدارة حسابات الموردين.</span>
                        </div>
                    </label>

                    <!-- Feature: Warehouses -->
                    <label class="flex items-start gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="features[]" value="warehouses" class="w-5 h-5 accent-indigo-500 rounded cursor-pointer mt-0.5" checked>
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">المخازن والمخزون (Warehouses)</span>
                            <span class="text-xs text-slate-500 block">إدارة المستودعات، التحويلات، الجرد، والمنتجات.</span>
                        </div>
                    </label>

                    <!-- Feature: POS -->
                    <label class="flex items-start gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="features[]" value="pos" class="w-5 h-5 accent-indigo-500 rounded cursor-pointer mt-0.5">
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">نقاط البيع الكاشير (POS)</span>
                            <span class="text-xs text-slate-500 block">واجهة المبيعات السريعة والتعامل التلقائي مع الباركود.</span>
                        </div>
                    </label>

                    <!-- Feature: Manufacturing -->
                    <label class="flex items-start gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="features[]" value="manufacturing" class="w-5 h-5 accent-indigo-500 rounded cursor-pointer mt-0.5">
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">التصنيع وتكاليف الإنتاج</span>
                            <span class="text-xs text-slate-500 block">أوامر التصنيع، تتبع المواد الخام وتكاليف الإنتاج.</span>
                        </div>
                    </label>

                    <!-- Feature: Accounting -->
                    <label class="flex items-start gap-3 p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer select-none">
                        <input type="checkbox" name="features[]" value="accounting" class="w-5 h-5 accent-indigo-500 rounded cursor-pointer mt-0.5" checked>
                        <div>
                            <span class="font-bold text-sm text-slate-200 block">الحسابات والمالية (Accounting)</span>
                            <span class="text-xs text-slate-500 block">الخزينة، المصروفات، الأرباح والخسائر والتقارير المالية.</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Active Toggle -->
            <label class="flex items-center gap-3 cursor-pointer select-none border-t border-slate-800/60 pt-6">
                <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5 accent-indigo-500 rounded cursor-pointer">
                <span class="text-sm font-bold text-slate-300">تفعيل الباقة وعرضها للجمهور فوراً</span>
            </label>

            <!-- Pricing-page Presentation -->
            <div class="space-y-5 border-t border-slate-800/60 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-sm font-bold text-slate-200">عرض الباقة في صفحة الأسعار العامة</label>
                        <p class="text-xs text-slate-500 mt-1">هذه الحقول تتحكم في ظهور الباقة على /pricing وتُدار بالكامل من هنا بدون أي تعديل برمجي.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="display_label" class="block text-sm font-bold text-slate-300">التسمية الظاهرة فوق اسم الباقة</label>
                        <input type="text" id="display_label" name="display_label" maxlength="255" class="w-full bg-slate-900 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-3 text-white outline-none transition" placeholder="مثال: الباقة الأساسية">
                        <span class="text-xs text-slate-500 block">تظهر بخط صغير فوق اسم الباقة في الكارت. اختياري — لو تُرك فارغاً سيُعرض اسم الباقة.</span>
                        @error('display_label') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="sort_order" class="block text-sm font-bold text-slate-300">ترتيب العرض في صفحة الأسعار</label>
                        <input type="number" id="sort_order" name="sort_order" min="0" value="0" class="w-full bg-slate-900 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-3 text-white outline-none transition" placeholder="0">
                        <span class="text-xs text-slate-500 block">رقم أقل = يظهر أولاً. اتركه 0 لاتباع الترتيب الافتراضي حسب السعر.</span>
                        @error('sort_order') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <label class="flex items-center gap-3 cursor-pointer select-none p-4 rounded-xl bg-amber-500/5 border border-amber-500/20">
                    <input type="checkbox" name="is_featured" value="1" class="w-5 h-5 accent-amber-500 rounded cursor-pointer">
                    <div>
                        <span class="font-bold text-sm text-amber-300 block">تمييز الباقة كـ "الأكثر طلباً"</span>
                        <span class="text-xs text-slate-400 block">تظهر بحدة لونية مختلفة وشارة ⚡ فوق الكارت. باقة واحدة فقط يُفضّل أن تحمل هذا التمييز.</span>
                    </div>
                </label>

                <div class="space-y-3">
                    <label class="block text-sm font-bold text-slate-300">نقاط القيمة المعروضة في كارت الباقة (Value Props)</label>
                    <span class="text-xs text-slate-500 block -mt-2">اكتب كل نقطة في حقل منفصل. لو تُركت فارغة، سيتم عرض الميزات المختارة من قسم تخصيص الميزات أعلاه تلقائياً.</span>
                    <div class="space-y-2">
                        @for($i = 0; $i < 6; $i++)
                            <input type="text" name="value_props[]" maxlength="255" class="w-full bg-slate-900 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-2.5 text-white outline-none transition text-sm" placeholder="مثال: {{ ['كاشير احترافي', 'حتى 3 مستخدمين', 'مخزن واحد', 'تقارير أساسية', 'دعم بالبريد الإلكتروني', 'بدون رسوم إعداد'][$i] }}">
                        @endfor
                    </div>
                    @error('value_props.*') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="border-t border-slate-800/60 pt-6 flex flex-col sm:flex-row gap-3 sm:gap-4">
                <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition shadow-lg text-sm sm:text-base">
                    حفظ وإطلاق الباقة
                </button>
                <a href="{{ route('super-admin.plans.index') }}" class="flex-1 py-3 text-center bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-bold transition border border-slate-700/30 text-sm sm:text-base">
                    إلغاء
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
