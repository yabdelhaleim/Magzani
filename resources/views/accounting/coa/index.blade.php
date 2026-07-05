@extends('layouts.app')

@section('title', 'دليل الحسابات')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">دليل الحسابات (Chart of Accounts)</h2>
            <p class="text-gray-600 mt-1">عرض هيكل الحسابات التنظيمي، مستويات الحسابات، الأرصدة، وإضافة وتعديل الحسابات</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('accounting.coa.export') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors flex items-center gap-2">
                <i class="fas fa-file-download"></i> تصدير JSON
            </a>
            <a href="{{ route('accounting.coa.create') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all shadow hover:shadow-md flex items-center gap-2">
                <i class="fas fa-plus-circle"></i> إضافة حساب جديد
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tree View & Details Container -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Accounts Tree Column -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                <i class="fas fa-sitemap text-blue-600"></i>
                <span>الهيكل الشجري للحسابات</span>
            </h3>

            <!-- Expand / Collapse All Controls -->
            <div class="flex gap-2 mb-4 justify-end">
                <button @click="$dispatch('expand-all')" class="text-xs text-blue-600 hover:underline">توسيع الكل</button>
                <span class="text-gray-300">|</span>
                <button @click="$dispatch('collapse-all')" class="text-xs text-blue-600 hover:underline">طي الكل</button>
            </div>

            <!-- Root Tree Node Container -->
            <div class="space-y-2 select-none" x-data="{ expandedNodes: {} }" 
                 @expand-all.window="expandedNodes = { @foreach(\App\Models\Account::pluck('id') as $id) '{{ $id }}': true, @endforeach }"
                 @collapse-all.window="expandedNodes = {}">
                
                @forelse($tree as $account)
                    @include('accounting.coa.partials.tree-node', ['node' => $account, 'level' => 1])
                @empty
                    <p class="text-center text-gray-500 py-10">لا توجد حسابات مسجلة في الدليل.</p>
                @endforelse
            </div>
        </div>

        <!-- Account Statistics Column -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <i class="fas fa-chart-pie text-blue-600"></i>
                    <span>إحصاءات الدليل</span>
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600 font-medium">إجمالي الحسابات</span>
                        <span class="font-bold text-gray-800">{{ $stats['total_accounts'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600 font-medium">الحسابات النشطة</span>
                        <span class="font-bold text-green-600">{{ $stats['active_accounts'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600 font-medium">الحسابات الرئيسية</span>
                        <span class="font-bold text-blue-600">{{ $stats['parent_accounts'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Info Panel -->
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 text-blue-800 text-sm">
                <h4 class="font-bold mb-2 flex items-center gap-1.5">
                    <i class="fas fa-info-circle text-blue-600"></i>
                    تنبيه محاسبي هام
                </h4>
                <p class="leading-relaxed">
                    يتم تقسيم دليل الحسابات إلى 5 مجموعات رئيسية وفق الأصول الدولية:
                </p>
                <ul class="list-disc list-inside mt-2 space-y-1 font-mono text-xs">
                    <li>1000 - الأصول (Asset) - مدين</li>
                    <li>2000 - الالتزامات (Liability) - دائن</li>
                    <li>3000 - حقوق الملكية (Equity) - دائن</li>
                    <li>4000 - الإيرادات (Revenue) - دائن</li>
                    <li>5000/6000 - المصروفات (Expense) - مدين</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
