@extends('layouts.app')

@section('title', 'إضافة حساب جديد')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">إضافة حساب جديد إلى الدليل</h2>
            <p class="text-gray-600 mt-1">تحديد رمز الحساب، الاسم، موقعه الهرمي، ونوع الحساب في المعادلة المحاسبية</p>
        </div>
        <a href="{{ route('accounting.coa.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors flex items-center gap-1.5">
            <i class="fas fa-arrow-right"></i> عودة للدليل
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('accounting.coa.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Account Code -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">رمز الحساب (Code) *</label>
                    <input type="text" name="code" value="{{ old('code') }}" required placeholder="مثال: 111005"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono">
                    <p class="text-xs text-gray-500 mt-1">يجب أن يعكس تسلسل المجموعة المحاسبية للأب.</p>
                </div>

                <!-- Account Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نوع الحساب *</label>
                    <select name="account_type_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- اختر النوع --</option>
                        @foreach($accountTypes as $type)
                            <option value="{{ $type->id }}" {{ old('account_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->code }} - {{ $type->name_ar }} ({{ $type->normal_balance === 'debit' ? 'مدين' : 'دائن' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Parent Account -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">الحساب الأب (Parent Account)</label>
                    <select name="parent_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- حساب جذري رئيسي (Root Node) --</option>
                        @foreach($parents as $p)
                            <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->code }} - {{ $p->name_ar }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">اتركه خالياً إذا كان هذا حساباً رئيسياً من المستوى الأول.</p>
                </div>

                <!-- Arabic Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الاسم بالعربية *</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar') }}" required placeholder="مثال: صندوق فرع الرياض"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- English Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الاسم بالإنجليزية (اختياري)</label>
                    <input type="text" name="name_en" value="{{ old('name_en') }}" placeholder="Example: Riyadh Branch Box"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">الوصف / ملاحظات</label>
                    <textarea name="description" rows="3" placeholder="ملاحظات حول طبيعة عمل هذا الحساب واستخداماته..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all shadow hover:shadow-md">
                <i class="fas fa-check-circle ml-1"></i> حفظ الحساب الجديد
            </button>
        </div>
    </form>
</div>
@endsection
