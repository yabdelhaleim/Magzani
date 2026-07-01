@extends('landlord.layout')

@section('title', 'إنشاء باقة اشتراك جديدة')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="glass-card rounded-2xl p-8 shadow-xl space-y-6">
        <div class="flex justify-between items-center border-b border-slate-800 pb-4">
            <h3 class="text-lg font-bold text-white">إعدادات الباقة الجديدة</h3>
            <a href="{{ route('super-admin.plans.index') }}" class="text-sm text-slate-400 hover:text-white transition">إلغاء والعودة</a>
        </div>

        <form action="{{ route('super-admin.plans.store') }}" method="POST" class="space-y-6">
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

            <!-- Submit Button -->
            <div class="border-t border-slate-800/60 pt-6 flex gap-4">
                <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition shadow-lg">
                    حفظ وإطلاق الباقة
                </button>
                <a href="{{ route('super-admin.plans.index') }}" class="flex-1 py-3 text-center bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-bold transition border border-slate-700/30">
                    إلغاء
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
