@extends('layouts.app')

@section('title', 'تسجيل أصل ثابت جديد')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 font-sans">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">تسجيل أصل ثابت جديد</h2>
            <p class="text-gray-600 mt-1">أدخل بيانات الأصل الثابت والحسابات المحاسبية المرتبطة به لتهيئة احتساب الإهلاك.</p>
        </div>
        <a href="{{ route('accounting.fixed-assets.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors">
            إلغاء والعودة
        </a>
    </div>

    <!-- Errors -->
    @if ($errors->any())
        <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Registration Form -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <form method="POST" action="{{ route('accounting.fixed-assets.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اسم الأصل الثابت *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="مثال: سيارة توصيل بضائع، آلة كبس خشب"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Code -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">كود الأصل (فريد) *</label>
                    <input type="text" name="code" value="{{ old('code') }}" required placeholder="مثال: AST-VEH-01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Purchase Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الشراء *</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Purchase Cost -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تكلفة الشراء (جنيه) *</label>
                    <input type="number" step="0.01" name="purchase_cost" value="{{ old('purchase_cost') }}" required placeholder="0.00"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono">
                </div>

                <!-- Scrap Value -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">القيمة التخريدية (الخردة) *</label>
                    <input type="number" step="0.01" name="scrap_value" value="{{ old('scrap_value', '0.00') }}" required placeholder="0.00"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono">
                </div>

                <!-- Useful Life -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">العمر الإنتاجي (بالسنوات) *</label>
                    <input type="number" name="useful_life" value="{{ old('useful_life') }}" required placeholder="مثال: 5"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono">
                </div>
            </div>

            <hr class="border-gray-100">

            <div class="space-y-4">
                <h3 class="text-base font-bold text-gray-800">التوجيه والربط المحاسبي (Ledger Mapping)</h3>
                <p class="text-xs text-gray-500">اختر الحسابات المالية المناسبة لتسجيل تكلفة الأصل، تجميع الإهلاك، والمصروف السنوي.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Asset Account -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب الأصل الثابت</label>
                        <select name="asset_account_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- اختر حساب الأصل --</option>
                            @foreach($assetAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('asset_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Accumulated Depreciation Account -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب مجمع الإهلاك</label>
                        <select name="accumulated_depreciation_account_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- اختر حساب المجمع --</option>
                            @foreach($accumDepAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('accumulated_depreciation_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Depreciation Expense Account -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">حساب مصروف الإهلاك</label>
                        <select name="depreciation_expense_account_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- اختر حساب المصروف --</option>
                            @foreach($expenseAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('depreciation_expense_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                    تسجيل الأصل وتفعيل الحساب
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
