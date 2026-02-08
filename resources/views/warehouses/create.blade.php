@extends('layouts.app')

@section('title', 'إضافة مخزن جديد')
@section('page-title', 'إنشاء مخزن جديد')

@section('content')

<!-- عرض الأخطاء -->
@if ($errors->any())
    <div class="bg-red-50 border-r-4 border-red-500 rounded-lg p-4 mb-6 shadow-sm">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="flex-1">
                <h3 class="text-red-800 font-bold mb-2">يوجد أخطاء في النموذج:</h3>
                <ul class="list-disc list-inside space-y-1 text-red-700 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="bg-red-50 border-r-4 border-red-500 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <p class="text-red-800 font-semibold">{{ session('error') }}</p>
        </div>
    </div>
@endif

<!-- Header -->
<div class="mb-6">
    <div class="flex items-center gap-3 mb-2">
        <a href="{{ route('warehouses.index') }}" 
           class="text-gray-600 hover:text-gray-800 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">إضافة مخزن جديد</h2>
            <p class="text-gray-600 text-sm mt-1">أدخل معلومات المخزن الجديد</p>
        </div>
    </div>
</div>

<!-- Form -->
<div class="max-w-5xl">
    <form action="{{ route('warehouses.store') }}" method="POST" id="warehouseForm">
        @csrf

        <!-- معلومات أساسية -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b">
                <div class="bg-blue-100 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">المعلومات الأساسية</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- اسم المخزن -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        اسم المخزن <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name"
                           value="{{ old('name') }}"
                           class="w-full px-4 py-3 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="مثال: المخزن الرئيسي"
                           required
                           autofocus>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- كود المخزن -->
                <div>
                    <label for="code" class="block text-sm font-semibold text-gray-700 mb-2">
                        كود المخزن
                    </label>
                    <div class="relative">
                        <input type="text" 
                               name="code" 
                               id="code"
                               value="{{ old('code') }}"
                               class="w-full px-4 py-3 border @error('code') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="مثال: WH-001">
                        <button type="button" 
                                onclick="generateCode()"
                                class="absolute left-2 top-1/2 -translate-y-1/2 bg-blue-100 hover:bg-blue-200 text-blue-600 px-3 py-1.5 rounded text-xs font-semibold transition-colors">
                            توليد تلقائي
                        </button>
                    </div>
                    @error('code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-xs mt-1">سيتم إنشاء كود تلقائي إذا تركت الحقل فارغاً</p>
                </div>

                <!-- الحالة -->
                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                        حالة المخزن <span class="text-red-500">*</span>
                    </label>
                    <select name="status" 
                            id="status"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            required>
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>✅ نشط</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>⛔ متوقف</option>
                        <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>🔧 صيانة</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- النشاط -->
                <div>
                    <label for="is_active" class="block text-sm font-semibold text-gray-700 mb-2">
                        مفعّل؟ <span class="text-red-500">*</span>
                    </label>
                    <select name="is_active" 
                            id="is_active"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            required>
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>نعم</option>
                        <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>لا</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- معلومات الموقع -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b">
                <div class="bg-green-100 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">معلومات الموقع</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- المدينة -->
                <div>
                    <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">
                        المدينة
                    </label>
                    <input type="text" 
                           name="city" 
                           id="city"
                           value="{{ old('city') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="مثال: القاهرة">
                </div>

                <!-- المنطقة -->
                <div>
                    <label for="area" class="block text-sm font-semibold text-gray-700 mb-2">
                        المنطقة
                    </label>
                    <input type="text" 
                           name="area" 
                           id="area"
                           value="{{ old('area') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="مثال: مدينة نصر">
                </div>

                <!-- الموقع -->
                <div>
                    <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">
                        الموقع
                    </label>
                    <input type="text" 
                           name="location" 
                           id="location"
                           value="{{ old('location') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="معلومات إضافية عن الموقع">
                </div>

                <!-- الهاتف -->
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                        رقم الهاتف
                    </label>
                    <input type="tel" 
                           name="phone" 
                           id="phone"
                           value="{{ old('phone') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="01012345678">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- البريد الإلكتروني -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        البريد الإلكتروني
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email"
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="warehouse@example.com">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- العنوان -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                        العنوان التفصيلي
                    </label>
                    <textarea name="address" 
                              id="address"
                              rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                              placeholder="أدخل العنوان الكامل...">{{ old('address') }}</textarea>
                </div>
            </div>
        </div>

        <!-- معلومات المسؤول -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b">
                <div class="bg-purple-100 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">معلومات المسؤول</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="manager_name" class="block text-sm font-semibold text-gray-700 mb-2">
                        اسم مسؤول المخزن
                    </label>
                    <input type="text" 
                           name="manager_name" 
                           id="manager_name"
                           value="{{ old('manager_name') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="مثال: أحمد محمد">
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        الوصف
                    </label>
                    <input type="text" 
                           name="description" 
                           id="description"
                           value="{{ old('description') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="وصف المخزن">
                </div>
            </div>
        </div>

        <!-- ملاحظات -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b">
                <div class="bg-orange-100 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">ملاحظات</h3>
            </div>

            <textarea name="notes" 
                      id="notes"
                      rows="4"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                      placeholder="أضف أي ملاحظات...">{{ old('notes') }}</textarea>
        </div>

        <!-- الأزرار -->
        <div class="flex items-center gap-3">
            <button type="submit" 
                    id="submitBtn"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2 shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>حفظ المخزن</span>
            </button>
            
            <a href="{{ route('warehouses.index') }}" 
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-8 py-3 rounded-lg font-semibold transition-colors">
                إلغاء
            </a>
        </div>
    </form>
</div>

<script>
function generateCode() {
    const code = 'WH-' + Math.floor(Math.random() * 9000 + 1000);
    document.getElementById('code').value = code;
}

// Prevent double submission
let isSubmitting = false;
document.getElementById('warehouseForm').addEventListener('submit', function(e) {
    if (isSubmitting) {
        e.preventDefault();
        return false;
    }
    
    isSubmitting = true;
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>جاري الحفظ...</span>
    `;
});
</script>

@endsection