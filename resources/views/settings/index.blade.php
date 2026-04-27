@extends('layouts.app')

@section('title', 'إعدادات النظام')
@section('page-title', 'الإعدادات')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">إعدادات النظام</h2>
        <p class="text-gray-600 mt-1">إدارة إعدادات الشركة والنظام</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            <p class="font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-xl">
            <ul class="list-disc list-inside font-semibold">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-sm mb-6" x-data="{ tab: 'company' }">
        <div class="border-b border-gray-200">
            <nav class="flex gap-2 md:gap-4 px-3 md:px-6 overflow-x-auto">
                <button @click="tab = 'company'"
                        :class="tab === 'company' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                        class="py-3 md:py-4 px-2 border-b-2 font-semibold transition whitespace-nowrap text-sm md:text-base">
                    <i class="fas fa-building ml-1 md:ml-2"></i>
                    الشركة
                </button>
                <button @click="tab = 'system'"
                        :class="tab === 'system' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                        class="py-3 md:py-4 px-2 border-b-2 font-semibold transition whitespace-nowrap text-sm md:text-base">
                    <i class="fas fa-cog ml-1 md:ml-2"></i>
                    النظام
                </button>
                <button @click="tab = 'users'"
                        :class="tab === 'users' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                        class="py-3 md:py-4 px-2 border-b-2 font-semibold transition whitespace-nowrap text-sm md:text-base">
                    <i class="fas fa-users-cog ml-1 md:ml-2"></i>
                    المستخدمين
                </button>
                <button @click="tab = 'backup'"
                        :class="tab === 'backup' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                        class="py-3 md:py-4 px-2 border-b-2 font-semibold transition whitespace-nowrap text-sm md:text-base">
                    <i class="fas fa-database ml-1 md:ml-2"></i>
                    النسخ الاحتياطي
                </button>
            </nav>
        </div>

        <!-- Company Info Tab -->
        <div x-show="tab === 'company'" class="p-4 md:p-6">
            <div style="background: linear-gradient(135deg, #ecfdf5, #f0f9ff); padding: 16px; border-radius: 12px; margin-bottom: 20px; border: 2px solid #10b981;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-info-circle" style="color: #10b981; font-size: 24px;"></i>
                    <div>
                        <h3 style="margin: 0; color: #065f46; font-size: 15px; font-weight: 800;">بيانات الشركة للفاتورة</h3>
                        <p style="margin: 3px 0 0 0; color: #047857; font-size: 12px;">
                            هذه البيانات ستظهر في طباعة الفواتير - تأكد من صحتها
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-building ml-1" style="color: #4f63d2;"></i>
                            اسم الشركة / النشاط التجاري
                        </label>
                        <input type="text" name="name" value="{{ old('name', $company->name ?? '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="مثال: شركة ماجزاني للأخشاب والمواد البناءية" required>
                        <small class="text-gray-500">هذا الاسم سيظهر كعنوان رئيسي في الفاتورة</small>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-phone ml-1" style="color: #10b981;"></i>
                            رقم الهاتف
                        </label>
                        <input type="tel" name="phone" value="{{ old('phone', $company->phone ?? '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="01XXXXXXXXX">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-envelope ml-1" style="color: #3a8ef0;"></i>
                            البريد الإلكتروني
                        </label>
                        <input type="email" name="email" value="{{ old('email', $company->email ?? '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="info@company.com">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt ml-1" style="color: #e8930a;"></i>
                            العنوان الكامل
                        </label>
                        <textarea name="address" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="المحافظة - المدينة - الشارع - رقم المبنى">{{ old('address', $company->address ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-receipt ml-1" style="color: #7c5cec;"></i>
                            الرقم الضريبي
                        </label>
                        <input type="text" name="tax_number" value="{{ old('tax_number', $company->tax_number ?? '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="XXX-XXX-XXX">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-file-contract ml-1" style="color: #dc2626;"></i>
                            السجل التجاري
                        </label>
                        <input type="text" name="commercial_register" value="{{ old('commercial_register', $company->commercial_register ?? '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="رقم السجل">
                    </div>

                    <div class="md:col-span-2" style="background: linear-gradient(135deg, #f8faff, #ffffff); padding: 16px md:p-6; border-radius: 16px; border: 2px dashed #4f63d2;">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-image ml-1" style="color: #4f63d2;"></i>
                            شعار الشركة (Logo)
                            <span style="color: #dc2626; font-size: 11px;">* هام جداً للطباعة</span>
                        </label>
                        <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                            @if(isset($company->logo) && $company->logo)
                                <div style="position: relative; display: inline-block;">
                                    <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" class="w-24 h-24 sm:w-32 sm:h-32 rounded-xl border-4 border-white shadow-lg object-contain p-3" style="background: white;">
                                    <span style="position: absolute; bottom: -8px; right: -8px; background: #10b981; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 800;">
                                        نشط
                                    </span>
                                </div>
                            @else
                                <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-xl border-4 border-dashed border-gray-300 bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center text-gray-400">
                                    <i class="fas fa-image text-3xl mb-1"></i>
                                    <span style="font-size: 10px; font-weight: 700;">لا يوجد شعار</span>
                                </div>
                            @endif
                            <div style="flex: 1; text-align: center; sm:text-align: start;">
                                <input type="file" name="logo" id="logo-input" class="hidden" accept="image/*">
                                <button type="button" onclick="document.getElementById('logo-input').click()" class="px-4 py-2 sm:px-6 sm:py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold transition shadow-lg text-sm sm:text-base" style="display: inline-flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    رفع شعار جديد
                                </button>
                                <div style="margin-top: 10px; display: flex; gap: 6px; flex-wrap: wrap; justify-content: center;">
                                    <span class="text-xs text-gray-500">PNG شفاف</span>
                                    <span class="text-xs text-gray-500">200×200px</span>
                                    <span class="text-xs text-gray-500">2MB max</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-save ml-2"></i>
                        حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>

        <!-- System Settings Tab -->
        <div x-show="tab === 'system'" class="p-4 md:p-6">
            <form action="{{ route('settings.system.update') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-4">الإعدادات العامة</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">العملة الافتراضية</label>
                            <select name="default_currency" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="ج.م" {{ (old('default_currency', $system->default_currency ?? '') == 'ج.م') ? 'selected' : '' }}>جنيه مصري (ج.م)</option>
                                <option value="$" {{ (old('default_currency', $system->default_currency ?? '') == '$') ? 'selected' : '' }}>دولار أمريكي ($)</option>
                                <option value="€" {{ (old('default_currency', $system->default_currency ?? '') == '€') ? 'selected' : '' }}>يورو (€)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">التنسيق الافتراضي للتاريخ</label>
                            <select name="date_format" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="Y-m-d" {{ (old('date_format', $system->date_format ?? '') == 'Y-m-d') ? 'selected' : '' }}>YYYY-MM-DD</option>
                                <option value="d/m/Y" {{ (old('date_format', $system->date_format ?? '') == 'd/m/Y') ? 'selected' : '' }}>DD/MM/YYYY</option>
                                <option value="m/d/Y" {{ (old('date_format', $system->date_format ?? '') == 'm/d/Y') ? 'selected' : '' }}>MM/DD/YYYY</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">نسبة الضريبة الافتراضية (%)</label>
                            <input type="number" name="default_tax" value="{{ old('default_tax', $system->default_tax ?? 0) }}" step="0.01"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">عدد الأسطر في الصفحة</label>
                            <select name="rows_per_page" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="10" {{ (old('rows_per_page', $system->rows_per_page ?? 25) == 10) ? 'selected' : '' }}>10</option>
                                <option value="25" {{ (old('rows_per_page', $system->rows_per_page ?? 25) == 25) ? 'selected' : '' }}>25</option>
                                <option value="50" {{ (old('rows_per_page', $system->rows_per_page ?? 25) == 50) ? 'selected' : '' }}>50</option>
                                <option value="100" {{ (old('rows_per_page', $system->rows_per_page ?? 25) == 100) ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200">

                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-4">إعدادات المخزون</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="low_stock_alert" value="1" {{ old('low_stock_alert', $system->low_stock_alert ?? false) ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="text-gray-700 font-semibold">تفعيل تنبيهات المخزون المنخفض</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="allow_negative_stock" value="1" {{ old('allow_negative_stock', $system->allow_negative_stock ?? false) ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="text-gray-700 font-semibold">السماح بالبيع من المخزون السالب</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="confirm_before_delete" value="1" {{ old('confirm_before_delete', $system->confirm_before_delete ?? false) ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="text-gray-700 font-semibold">طلب تأكيد قبل حذف الأصناف</span>
                        </label>
                    </div>
                </div>

                <hr class="border-gray-200">

                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-4">إعدادات الفواتير</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="auto_invoice_number" value="1" {{ old('auto_invoice_number', $system->auto_invoice_number ?? false) ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="text-gray-700 font-semibold">ترقيم تلقائي للفواتير</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="auto_print_invoice" value="1" {{ old('auto_print_invoice', $system->auto_print_invoice ?? false) ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="text-gray-700 font-semibold">طباعة تلقائية بعد الحفظ</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="auto_email_invoice" value="1" {{ old('auto_email_invoice', $system->auto_email_invoice ?? false) ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="text-gray-700 font-semibold">إرسال الفاتورة بالبريد الإلكتروني تلقائياً</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-save ml-2"></i>
                        حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>

        <!-- Users & Permissions Tab -->
        <div x-show="tab === 'users'" class="p-4 md:p-6">
            <div class="flex items-center justify-between mb-4 md:mb-6">
                <h3 class="text-lg font-bold text-gray-800">المستخدمين</h3>
                <button class="px-4 py-2 md:px-6 md:py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition text-sm md:text-base">
                    <i class="fas fa-plus ml-1 md:ml-2"></i>
                    إضافة مستخدم
                </button>
            </div>

            <div class="space-y-3 md:space-y-4">
                <!-- User 1 -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 md:p-4 border border-gray-200 rounded-lg hover:bg-gray-50 gap-3">
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=Ahmed+Admin&background=667eea&color=fff"
                             class="w-10 h-10 md:w-12 md:h-12 rounded-full flex-shrink-0">
                        <div>
                            <p class="font-semibold text-gray-800 text-sm md:text-base">أحمد المدير</p>
                            <p class="text-xs md:text-sm text-gray-600">admin@company.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 md:gap-4 flex-wrap">
                        <span class="px-2 md:px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs md:text-sm font-semibold">مدير النظام</span>
                        <span class="px-2 md:px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs md:text-sm font-semibold">نشط</span>
                        <button class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg">
                            <i class="fas fa-edit text-gray-600"></i>
                        </button>
                    </div>
                </div>

                <!-- User 2 -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 md:p-4 border border-gray-200 rounded-lg hover:bg-gray-50 gap-3">
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=Mohamed+Ali&background=16a34a&color=fff"
                             class="w-10 h-10 md:w-12 md:h-12 rounded-full flex-shrink-0">
                        <div>
                            <p class="font-semibold text-gray-800 text-sm md:text-base">محمد علي</p>
                            <p class="text-xs md:text-sm text-gray-600">mohamed@company.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 md:gap-4 flex-wrap">
                        <span class="px-2 md:px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs md:text-sm font-semibold">محاسب</span>
                        <span class="px-2 md:px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs md:text-sm font-semibold">نشط</span>
                        <button class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg">
                            <i class="fas fa-edit text-gray-600"></i>
                        </button>
                    </div>
                </div>

                <!-- User 3 -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 md:p-4 border border-gray-200 rounded-lg hover:bg-gray-50 gap-3">
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=Fatima+Hassan&background=dc2626&color=fff"
                             class="w-10 h-10 md:w-12 md:h-12 rounded-full flex-shrink-0">
                        <div>
                            <p class="font-semibold text-gray-800 text-sm md:text-base">فاطمة حسن</p>
                            <p class="text-xs md:text-sm text-gray-600">fatima@company.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 md:gap-4 flex-wrap">
                        <span class="px-2 md:px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs md:text-sm font-semibold">مدير مخزن</span>
                        <span class="px-2 md:px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs md:text-sm font-semibold">نشط</span>
                        <button class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg">
                            <i class="fas fa-edit text-gray-600"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup Tab -->
        <div x-show="tab === 'backup'" class="p-4 md:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-6">
                <div class="p-4 md:p-6 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center gap-3 mb-3 md:mb-4">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-download text-white text-lg md:text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-sm md:text-base">نسخ احتياطي يدوي</h4>
                            <p class="text-xs md:text-sm text-gray-600">إنشاء نسخة احتياطية الآن</p>
                        </div>
                    </div>
                    <button class="w-full px-4 py-2 md:px-6 md:py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition text-sm md:text-base">
                        <i class="fas fa-database ml-2"></i>
                        إنشاء نسخة احتياطية
                    </button>
                </div>

                <div class="p-4 md:p-6 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center gap-3 mb-3 md:mb-4">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-upload text-white text-lg md:text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-sm md:text-base">استعادة نسخة</h4>
                            <p class="text-xs md:text-sm text-gray-600">استرجاع من نسخة احتياطية</p>
                        </div>
                    </div>
                    <button class="w-full px-4 py-2 md:px-6 md:py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition text-sm md:text-base">
                        <i class="fas fa-file-upload ml-2"></i>
                        اختيار ملف للاستعادة
                    </button>
                </div>
            </div>

            <h3 class="text-base md:text-lg font-bold text-gray-800 mb-3 md:mb-4">النسخ الاحتياطية السابقة</h3>
            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 md:p-4 border border-gray-200 rounded-lg hover:bg-gray-50 gap-2">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-archive text-2xl md:text-3xl text-blue-600 flex-shrink-0"></i>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm md:text-base">backup_2026-01-19.sql</p>
                            <p class="text-xs md:text-sm text-gray-600">19 يناير 2026 - 14:30 | 45.8 MB</p>
                        </div>
                    </div>
                    <div class="flex gap-2 self-end sm:self-auto">
                        <button class="p-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="p-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 md:p-4 border border-gray-200 rounded-lg hover:bg-gray-50 gap-2">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-archive text-2xl md:text-3xl text-gray-400 flex-shrink-0"></i>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm md:text-base">backup_2026-01-12.sql</p>
                            <p class="text-xs md:text-sm text-gray-600">12 يناير 2026 - 10:15 | 42.3 MB</p>
                        </div>
                    </div>
                    <div class="flex gap-2 self-end sm:self-auto">
                        <button class="p-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="p-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection