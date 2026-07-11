@extends('layouts.app')

@section('title', 'الإعدادات المحاسبية')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">الإعدادات المحاسبية</h2>
            <p class="text-gray-600 mt-1">تحديد الحسابات الافتراضية للنظام والتحكم في آلية الترحيل للدفاتر العامة</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('accounting.settings.update') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf
        @method('PUT')

        <div class="p-6 space-y-8">
            <!-- Section 1: Core System Accounts -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <i class="fas fa-project-diagram text-blue-600"></i>
                    <span>ربط الحسابات الأساسية (الذمم والمخازن)</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- AR Account -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب الذمم المدينة (العملاء)</label>
                        <select name="ar_account_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (optional($settings)->ar_account_id == $acc->id) ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">الرصيد الافتراضي: مدين. لتسجيل استحقاقات مبيعات الآجل.</p>
                    </div>

                    <!-- AP Account -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب الذمم الدائنة (الموردين)</label>
                        <select name="ap_account_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (optional($settings)->ap_account_id == $acc->id) ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">الرصيد الافتراضي: دائن. لتسجيل استحقاقات مشتريات الآجل.</p>
                    </div>

                    <!-- Cash Account -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب الصندوق الرئيسي</label>
                        <select name="cash_account_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (optional($settings)->cash_account_id == $acc->id) ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">الخزينة النقدية الرئيسية للمنشأة.</p>
                    </div>

                    <!-- Inventory Account -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب المخزون</label>
                        <select name="inventory_account_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (optional($settings)->inventory_account_id == $acc->id) ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">لتقييم المخزون المستمر (Perpetual Inventory).</p>
                    </div>
                </div>
            </div>

            <!-- Section 2: P&L Accounts -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <i class="fas fa-coins text-blue-600"></i>
                    <span>حسابات الإيرادات وتكلفة المبيعات</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Sales Revenue -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب إيرادات المبيعات</label>
                        <select name="sales_revenue_account_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (optional($settings)->sales_revenue_account_id == $acc->id) ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- COGS Account -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب تكلفة البضاعة المباعة (COGS)</label>
                        <select name="cogs_account_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (optional($settings)->cogs_account_id == $acc->id) ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- WIP Account -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب بضاعة تحت التصنيع (WIP)</label>
                        <select name="wip_account_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- غير محدد --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (optional($settings)->wip_account_id == $acc->id) ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">حساب وسيط لتسجيل تكاليف الإنتاج تحت التشغيل.</p>
                    </div>

                    <!-- Retained Earnings -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب الأرباح المحتجزة</label>
                        <select name="retained_earnings_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (optional($settings)->retained_earnings_id == $acc->id) ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">لترحيل صافي الأرباح السنوية في قيد الإقفال.</p>
                    </div>
                </div>
            </div>

            <!-- Section 3: Taxes & Auxiliary -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <i class="fas fa-percent text-blue-600"></i>
                    <span>الضرائب والرسوم والتقريب</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tax Output -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب ضريبة المخرجات (المبيعات)</label>
                        <select name="tax_account_output_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- غير محدد --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (optional($settings)->tax_account_output_id == $acc->id) ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tax Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب ضريبة المدخلات (المشتريات)</label>
                        <select name="tax_account_input_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- غير محدد --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (optional($settings)->tax_account_input_id == $acc->id) ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sales Discount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب الخصم المسموح به (مبيعات)</label>
                        <select name="sales_discount_account_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- غير محدد --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (optional($settings)->sales_discount_account_id == $acc->id) ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Rounding -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب تقريب الفروقات</label>
                        <select name="rounding_account_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- غير محدد --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (optional($settings)->rounding_account_id == $acc->id) ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 4: Auto-Posting & Control Configuration -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <i class="fas fa-toggle-on text-blue-600"></i>
                    <span>إعدادات الترحيل والتحكم لدفتر الأستاذ العام (GL)</span>
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                    <!-- Post Invoices -->
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <input type="checkbox" name="auto_post_invoices" id="auto_post_invoices" value="1" 
                               {{ optional($settings)->auto_post_invoices ? 'checked' : '' }}
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">
                        <div>
                            <label for="auto_post_invoices" class="font-semibold text-gray-800 text-sm block cursor-pointer">ترحيل الفواتير تلقائياً</label>
                            <span class="text-xs text-gray-500">ترحيل المبيعات والمشتريات فور تأكيد الفاتورة.</span>
                        </div>
                    </div>

                    <!-- Post Payments -->
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <input type="checkbox" name="auto_post_payments" id="auto_post_payments" value="1" 
                               {{ optional($settings)->auto_post_payments ? 'checked' : '' }}
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">
                        <div>
                            <label for="auto_post_payments" class="font-semibold text-gray-800 text-sm block cursor-pointer">ترحيل المدفوعات تلقائياً</label>
                            <span class="text-xs text-gray-500">ترحيل سندات القبض والصرف التابعة للفواتير فور تسجيلها.</span>
                        </div>
                    </div>

                    <!-- Post Expenses -->
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <input type="checkbox" name="auto_post_expenses" id="auto_post_expenses" value="1" 
                               {{ optional($settings)->auto_post_expenses ? 'checked' : '' }}
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">
                        <div>
                            <label for="auto_post_expenses" class="font-semibold text-gray-800 text-sm block cursor-pointer">ترحيل عمليات الصندوق تلقائياً</label>
                            <span class="text-xs text-gray-500">ترحيل حركات السحب والإيداع النقدية والمصروفات.</span>
                        </div>
                    </div>

                    <!-- Post Manufacturing -->
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <input type="checkbox" name="auto_post_manufacturing" id="auto_post_manufacturing" value="1" 
                               {{ optional($settings)->auto_post_manufacturing ? 'checked' : '' }}
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">
                        <div>
                            <label for="auto_post_manufacturing" class="font-semibold text-gray-800 text-sm block cursor-pointer">ترحيل عمليات التصنيع تلقائياً</label>
                            <span class="text-xs text-gray-500">ترحيل قيود أوامر التصنيع وتكاليف التشغيل تلقائياً.</span>
                        </div>
                    </div>

                    <!-- Strict Posting Mode -->
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <input type="checkbox" name="strict_posting_mode" id="strict_posting_mode" value="1" 
                               {{ optional($settings)->strict_posting_mode ? 'checked' : '' }}
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">
                        <div>
                            <label for="strict_posting_mode" class="font-semibold text-gray-800 text-sm block cursor-pointer text-red-700">تفعيل وضع الصرامة المحاسبي</label>
                            <span class="text-xs text-gray-500">منع الفواتير والتصنيع عند تخطي حد القيود الفاشلة المعلقة.</span>
                        </div>
                    </div>

                    <!-- Max Posting Failures -->
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <div class="w-full">
                            <label for="max_posting_failures" class="font-semibold text-gray-800 text-sm block mb-1">الحد الأقصى لأخطاء الترحيل المعلقة</label>
                            <input type="number" name="max_posting_failures" id="max_posting_failures" min="0" value="{{ optional($settings)->max_posting_failures ?? 5 }}" 
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-semibold">
                            <span class="text-[10px] text-gray-500">عدد الأخطاء المسموح بها قبل الإغلاق.</span>
                        </div>
                    </div>

                    <!-- Gap 2 — Standard Costing Toggle -->
                    <div class="flex items-start gap-3 p-4 bg-amber-50 rounded-lg border border-amber-200">
                        <input type="checkbox" name="standard_costing_enabled" id="standard_costing_enabled" value="1"
                               {{ optional($settings)->standard_costing_enabled ? 'checked' : '' }}
                               class="w-5 h-5 text-amber-600 border-gray-300 rounded focus:ring-amber-500 mt-0.5">
                        <div class="w-full">
                            <label for="standard_costing_enabled" class="font-semibold text-gray-800 text-sm block cursor-pointer">تفعيل نظام التكلفة المعيارية (Standard Costing)</label>
                            <span class="text-xs text-gray-600 block mt-1">
                                عند التفعيل، تُقارن التكلفة الفعلية لكل أمر تصنيع بالتكلفة المعيارية المسجّلة مسبقاً،
                                ويُرحَّل الفرق إلى <span class="font-semibold">حساب 5160 (انحراف تكلفة التصنيع)</span> في قائمة الدخل.
                            </span>
                            <span class="text-[10px] text-amber-700 block mt-1">⚠️ Tenants الحاليون يستمرون بسلوك Actual Costing افتراضياً — لا تأثير على القيود القائمة.</span>
                        </div>
                    </div>

                    <!-- Variance Posting Account Override -->
                    <div class="flex items-start gap-3 p-4 bg-amber-50 rounded-lg border border-amber-200">
                        <div class="w-full">
                            <label for="variance_posting_account_id" class="font-semibold text-gray-800 text-sm block mb-1">
                                حساب ترحيل الانحراف (اختياري)
                            </label>
                            <select name="variance_posting_account_id" id="variance_posting_account_id"
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 font-semibold">
                                <option value="">— الافتراضي: 5160 (انحراف تكلفة التصنيع) —</option>
                                @foreach($accounts->whereIn('code', ['5160','5161','5162','5163']) as $acc)
                                    <option value="{{ $acc->id }}" {{ (optional($settings)->variance_posting_account_id == $acc->id) ? 'selected' : '' }}>
                                        {{ $acc->code }} - {{ $acc->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-[10px] text-gray-500">اتركه فارغاً لاستخدام الحساب الافتراضي 5160.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 5: General ERP Settings -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <i class="fas fa-cogs text-blue-600"></i>
                    <span>إعدادات النظام المالية العامة</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">العملة الافتراضية</label>
                        <input type="text" name="default_currency" value="{{ optional($settings)->default_currency ?? 'SAR' }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-center">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">شهر بداية السنة المالية</label>
                        <select name="fiscal_year_start_month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ (optional($settings)->fiscal_year_start_month == $i) ? 'selected' : '' }}>
                                    {{ $i }} - {{ Carbon\Carbon::create(null, $i, 1)->locale('ar')->isoFormat('MMMM') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">بادئة ترقيم قيود اليومية</label>
                        <input type="text" name="numbering_prefix_je" value="{{ optional($settings)->numbering_prefix_je ?? 'JE' }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-center">
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all shadow hover:shadow-md">
                <i class="fas fa-save ml-1.5"></i> حفظ الإعدادات المحاسبية
            </button>
        </div>
    </form>
</div>
@endsection
