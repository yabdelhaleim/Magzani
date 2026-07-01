@extends('landlord.layout')

@section('title', 'لوحة الإحصائيات العامة للمنصة')

@section('content')
<div class="space-y-8">

    <!-- Stats Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Widget 1: Tenants Count -->
        <div class="glass-card p-6 rounded-2xl flex items-center justify-between shadow-lg">
            <div>
                <span class="text-slate-400 font-semibold block text-sm">الشركات المشتركة</span>
                <span class="text-3xl font-extrabold text-white mt-1 block">{{ $tenantsCount }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-hotel"></i>
            </div>
        </div>

        <!-- Widget 2: Plans Count -->
        <div class="glass-card p-6 rounded-2xl flex items-center justify-between shadow-lg">
            <div>
                <span class="text-slate-400 font-semibold block text-sm">الباقات النشطة</span>
                <span class="text-3xl font-extrabold text-white mt-1 block">{{ $plansCount }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-tags"></i>
            </div>
        </div>

        <!-- Widget 3: Total Subscriptions Value -->
        <div class="glass-card p-6 rounded-2xl flex items-center justify-between shadow-lg">
            <div>
                <span class="text-slate-400 font-semibold block text-sm">الإيراد الشهري التقديري</span>
                <span class="text-3xl font-extrabold text-white mt-1 block">${{ $estimatedRevenue }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts & Recent Tenants -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Recent Tenants List -->
        <div class="glass-card p-6 rounded-2xl shadow-xl lg:col-span-2 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-200">آخر الشركات المسجلة</h3>
                <a href="{{ route('super-admin.tenants.index') }}" class="text-xs text-indigo-400 hover:text-purple-400 transition font-semibold">عرض الكل</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead>
                        <tr class="text-slate-500 border-b border-slate-800/50">
                            <th class="pb-3">اسم الشركة / المعرف</th>
                            <th class="pb-3">الرابط المخصص</th>
                            <th class="pb-3">الباقة الحالية</th>
                            <th class="pb-3">تاريخ التسجيل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/40">
                        @forelse($recentTenants as $tenant)
                        <tr class="text-slate-300 hover:bg-slate-800/20 transition">
                            <td class="py-3 font-semibold text-white">{{ $tenant->id }}</td>
                            <td class="py-3 font-mono text-xs text-purple-300">
                                @if($tenant->domains->first())
                                <a href="http://{{ $tenant->domains->first()->domain }}:8000" target="_blank" class="hover:underline">
                                    {{ $tenant->domains->first()->domain }}
                                </a>
                                @else
                                -
                                @endif
                            </td>
                            <td class="py-3">
                                @if(isset($tenant->data['plan_id']))
                                    @if($tenant->data['plan_id'] === 'custom')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">مخصصة</span>
                                    @else
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">{{ strtoupper($tenant->data['plan_id']) }}</span>
                                    @endif
                                @else
                                <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="py-3 text-xs text-slate-500">{{ $tenant->created_at ? $tenant->created_at->format('Y-m-d') : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-slate-500">لا يوجد مستأجرين مسجلين بعد.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Shortcuts Panel -->
        <div class="glass-card p-6 rounded-2xl shadow-xl space-y-6">
            <h3 class="text-base font-bold text-slate-200 border-b border-slate-800 pb-3">إجراءات سريعة</h3>
            
            <div class="space-y-3">
                <a href="{{ route('super-admin.tenants.create') }}" class="flex items-center justify-between p-4 rounded-xl bg-indigo-600/10 border border-indigo-500/20 hover:bg-indigo-600/20 transition text-indigo-400">
                    <span class="font-bold text-sm">تسجيل شركة جديدة</span>
                    <i class="fa-solid fa-plus-circle text-lg"></i>
                </a>

                <a href="{{ route('super-admin.plans.create') }}" class="flex items-center justify-between p-4 rounded-xl bg-purple-600/10 border border-purple-500/20 hover:bg-purple-600/20 transition text-purple-400">
                    <span class="font-bold text-sm">إنشاء باقة جديدة</span>
                    <i class="fa-solid fa-plus-circle text-lg"></i>
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
