@extends('layouts.app')

@section('title', 'إعدادات النظام')
@section('page-title', 'الإعدادات')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">إعدادات النظام</h2>
        <p class="text-gray-600 mt-1">إدارة إعدادات الشركة والنظام</p>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-sm mb-6" x-data="{ tab: 'company' }">
        <div class="border-b border-gray-200">
            <nav class="flex gap-4 px-6">
                <button @click="tab = 'company'" 
                        :class="tab === 'company' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                        class="py-4 px-2 border-b-2 font-semibold transition">
                    <i class="fas fa-building ml-2"></i>
                    معلومات الشركة
                </button>
                <button @click="tab = 'system'" 
                        :class="tab === 'system' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                        class="py-4 px-2 border-b-2 font-semibold transition">
                    <i class="fas fa-cog ml-2"></i>
                    إعدادات النظام
                </button>
                <button @click="tab = 'users'" 
                        :class="tab === 'users' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                        class="py-4 px-2 border-b-2 font-semibold transition">
                    <i class="fas fa-users-cog ml-2"></i>
                    المستخدمين والصلاحيات
                </button>
                <button @click="tab = 'backup'" 
                        :class="tab === 'backup' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                        class="py-4 px-2 border-b-2 font-semibold transition">
                    <i class="fas fa-database ml-2"></i>
                    النسخ الاحتياطي
                </button>
                <button @click="tab = 'terms'" 
                        :class="tab === 'terms' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                        class="py-4 px-2 border-b-2 font-semibold transition">
                    <i class="fas fa-file-contract ml-2"></i>
                    شروط وأحكام الفواتير
                </button>
            </nav>
        </div>

        <!-- Company Info Tab -->
        <div x-show="tab === 'company'" class="p-6">
            <form class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">اسم الشركة</label>
                        <input type="text" value="نظام إدارة المخازن" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">رقم الهاتف</label>
                        <input type="tel" value="02-12345678" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">البريد الإلكتروني</label>
                        <input type="email" value="info@company.com" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">العنوان</label>
                        <textarea rows="2" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">القاهرة - مدينة نصر - شارع عباس العقاد</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">الرقم الضريبي</label>
                        <input type="text" value="123-456-789" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">السجل التجاري</label>
                        <input type="text" value="987654321" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">شعار الشركة</label>
                        <div class="flex items-center gap-4">
                            <img src="https://via.placeholder.com/100" alt="Logo" class="w-24 h-24 rounded-lg border border-gray-300">
                            <div>
                                <button type="button" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                                    <i class="fas fa-upload ml-2"></i>
                                    تحميل شعار
                                </button>
                                <p class="text-sm text-gray-500 mt-2">PNG, JPG بحجم أقصى 2MB</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-save ml-2"></i>
                        حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>

        <!-- System Settings Tab -->
        <div x-show="tab === 'system'" class="p-6">
            <form class="space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-4">الإعدادات العامة</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">العملة الافتراضية</label>
                            <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option>جنيه مصري (ج.م)</option>
                                <option>دولار أمريكي ($)</option>
                                <option>يورو (€)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">التنسيق الافتراضي للتاريخ</label>
                            <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option>YYYY-MM-DD</option>
                                <option>DD/MM/YYYY</option>
                                <option>MM/DD/YYYY</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">نسبة الضريبة الافتراضية (%)</label>
                            <input type="number" value="14" step="0.01"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">عدد الأسطر في الصفحة</label>
                            <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option>10</option>
                                <option selected>25</option>
                                <option>50</option>
                                <option>100</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200">

                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-4">إعدادات المخزون</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" checked class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="text-gray-700 font-semibold">تفعيل تنبيهات المخزون المنخفض</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" checked class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="text-gray-700 font-semibold">السماح بالبيع من المخزون السالب</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="text-gray-700 font-semibold">طلب تأكيد قبل حذف الأصناف</span>
                        </label>
                    </div>
                </div>

                <hr class="border-gray-200">

                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-4">إعدادات الفواتير</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" checked class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="text-gray-700 font-semibold">ترقيم تلقائي للفواتير</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" checked class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="text-gray-700 font-semibold">طباعة تلقائية بعد الحفظ</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="text-gray-700 font-semibold">إرسال الفاتورة بالبريد الإلكتروني تلقائياً</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-save ml-2"></i>
                        حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>

        <!-- Users & Permissions Tab -->
        <div x-show="tab === 'users'" class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800">المستخدمين</h3>
                <button class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                    <i class="fas fa-plus ml-2"></i>
                    إضافة مستخدم
                </button>
            </div>

            <div class="space-y-4">
                <!-- User 1 -->
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center gap-4">
                        <img src="https://ui-avatars.com/api/?name=Ahmed+Admin&background=667eea&color=fff" 
                             class="w-12 h-12 rounded-full">
                        <div>
                            <p class="font-semibold text-gray-800">أحمد المدير</p>
                            <p class="text-sm text-gray-600">admin@company.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-semibold">مدير النظام</span>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold">نشط</span>
                        <button class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg">
                            <i class="fas fa-edit text-gray-600"></i>
                        </button>
                    </div>
                </div>

                <!-- User 2 -->
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center gap-4">
                        <img src="https://ui-avatars.com/api/?name=Mohamed+Ali&background=16a34a&color=fff" 
                             class="w-12 h-12 rounded-full">
                        <div>
                            <p class="font-semibold text-gray-800">محمد علي</p>
                            <p class="text-sm text-gray-600">mohamed@company.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">محاسب</span>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold">نشط</span>
                        <button class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg">
                            <i class="fas fa-edit text-gray-600"></i>
                        </button>
                    </div>
                </div>

                <!-- User 3 -->
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center gap-4">
                        <img src="https://ui-avatars.com/api/?name=Fatima+Hassan&background=dc2626&color=fff" 
                             class="w-12 h-12 rounded-full">
                        <div>
                            <p class="font-semibold text-gray-800">فاطمة حسن</p>
                            <p class="text-sm text-gray-600">fatima@company.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-semibold">مدير مخزن</span>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold">نشط</span>
                        <button class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg">
                            <i class="fas fa-edit text-gray-600"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup Tab -->
        <div x-show="tab === 'backup'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="p-6 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-download text-white text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">نسخ احتياطي يدوي</h4>
                            <p class="text-sm text-gray-600">إنشاء نسخة احتياطية الآن</p>
                        </div>
                    </div>
                    <button class="w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-database ml-2"></i>
                        إنشاء نسخة احتياطية
                    </button>
                </div>

                <div class="p-6 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-upload text-white text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">استعادة نسخة</h4>
                            <p class="text-sm text-gray-600">استرجاع من نسخة احتياطية</p>
                        </div>
                    </div>
                    <button class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-file-upload ml-2"></i>
                        اختيار ملف للاستعادة
                    </button>
                </div>
            </div>

            <h3 class="text-lg font-bold text-gray-800 mb-4">النسخ الاحتياطية السابقة</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-archive text-3xl text-blue-600"></i>
                        <div>
                            <p class="font-semibold text-gray-800">backup_2026-01-19.sql</p>
                            <p class="text-sm text-gray-600">19 يناير 2026 - 14:30 | 45.8 MB</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button class="p-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="p-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-archive text-3xl text-gray-400"></i>
                        <div>
                            <p class="font-semibold text-gray-800">backup_2026-01-12.sql</p>
                            <p class="text-sm text-gray-600">12 يناير 2026 - 10:15 | 42.3 MB</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
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
        
        <!-- Terms and Conditions Tab -->
        <div x-show="tab === 'terms'" class="p-6">
            <form method="POST" action="{{ route('settings.system.update') }}" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-4">شروط وأحكام الفواتير الافتراضية</h3>
                    <p class="text-sm text-gray-600 mb-6">سيتم استخدام هذه الشروط والأحكام تلقائياً في جميع الفواتير الجديدة ما لم يتم تحديد شروط مختلفة</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- شروط وأحكام فواتير المبيعات -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-arrow-up text-green-600 ml-2"></i>
                                فواتير المبيعات
                            </label>
                            <textarea 
                                name="default_sales_terms" 
                                rows="6"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="أدخل شروط وأحكام فواتير المبيعات..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">مثال: الدفع خلال 30 يوم من تاريخ الفاتورة</p>
                        </div>
                        
                        <!-- شروط وأحكام فواتير الشراء -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-arrow-down text-blue-600 ml-2"></i>
                                فواتير الشراء
                            </label>
                            <textarea 
                                name="default_purchase_terms" 
                                rows="6"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="أدخل شروط وأحكام فواتير الشراء..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">مثال: الدفع خلال 15 يوم من تاريخ الاستلام</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-save ml-2"></i>
                        حفظ شروط وأحكام الفواتير
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection