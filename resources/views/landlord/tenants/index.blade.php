@extends('landlord.layout')

@section('title', 'إدارة الشركات المشتركة')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-bold text-slate-200">العملاء الحاليين في النظام</h3>
        <a href="{{ route('super-admin.tenants.create') }}" class="py-2 px-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition flex items-center gap-2 shadow-lg">
            <i class="fa-solid fa-user-plus text-sm"></i>
            <span>تسجيل شركة جديدة</span>
        </a>
    </div>

    <!-- Tenants List Table -->
    <div class="glass-card rounded-2xl p-6 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead>
                    <tr class="text-slate-500 border-b border-slate-800 pb-3">
                        <th class="pb-3">معرف الشركة / الاسم</th>
                        <th class="pb-3">الرابط الفرعي (Subdomain)</th>
                        <th class="pb-3">الباقة الحالية</th>
                        <th class="pb-3">حالة الاشتراك</th>
                        <th class="pb-3 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($tenants as $tenant)
                    <tr class="text-slate-300 hover:bg-slate-800/10 transition">
                        <!-- ID -->
                        <td class="py-4 font-bold text-white text-base">
                            {{ $tenant->id }}
                        </td>
                        
                        <!-- Domain -->
                        <td class="py-4 font-mono text-xs text-purple-300">
                            @if($tenant->domains->first())
                            <a href="http://{{ $tenant->domains->first()->domain }}:8000" target="_blank" class="hover:underline flex items-center gap-1.5">
                                <span>{{ $tenant->domains->first()->domain }}</span>
                                <i class="fa-solid fa-up-right-from-square text-[10px] text-slate-500"></i>
                            </a>
                            @else
                            <span class="text-slate-500">لا يوجد نطاق</span>
                            @endif
                        </td>

                        <!-- Plan -->
                        <td class="py-4">
                            @if(isset($tenant->plan_id))
                                @if($tenant->plan_id === 'custom')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center gap-1.5 w-fit">
                                    <i class="fa-solid fa-sliders"></i>
                                    <span>مخصصة</span>
                                </span>
                                @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center gap-1.5 w-fit">
                                    <i class="fa-solid fa-certificate"></i>
                                    <span>{{ strtoupper($tenant->plan_id) }}</span>
                                </span>
                                @endif
                            @else
                            <span class="text-slate-500">غير محددة</span>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="py-4">
                            @if($tenant->is_suspended)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">معطل / موقوف</span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">نشط / يعمل</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('super-admin.tenants.edit', $tenant->id) }}" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-bold transition flex items-center gap-1 border border-slate-700/20" title="تعديل الباقة والصلاحيات">
                                    <i class="fa-solid fa-sliders"></i>
                                    <span>تخصيص الباقة</span>
                                </a>

                                <form action="{{ route('super-admin.tenants.toggle-status', $tenant->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-2 {{ $tenant->is_suspended ? 'bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border-amber-500/20' }} rounded-lg text-xs font-bold transition border" title="تعطيل/تنشيط الحساب">
                                        @if($tenant->is_suspended)
                                        <i class="fa-solid fa-play"></i>
                                        <span>تنشيط</span>
                                        @else
                                        <i class="fa-solid fa-pause"></i>
                                        <span>إيقاف</span>
                                        @endif
                                    </button>
                                </form>

                                <form action="{{ route('super-admin.tenants.destroy', $tenant->id) }}" method="POST" onsubmit="return confirm('⚠️ تحذير حرج جداً: حذف هذه الشركة سيقوم بمسح قاعدة بياناتها بالكامل وحذف جميع منتجاتها وفواتيرها بشكل نهائي! هل تريد المتابعة؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg text-xs font-bold transition border border-rose-500/20" title="حذف الشركة نهائياً">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-500">
                            <div class="w-16 h-16 bg-slate-800 text-slate-500 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">
                                <i class="fa-solid fa-hotel"></i>
                            </div>
                            <h4 class="text-base font-bold text-slate-300">لا يوجد أي عملاء مسجلين حالياً</h4>
                            <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">ابدأ بإضافة أول شركة ونطاق فرعي لتجربة عزل قواعد البيانات وتخصيص الباقات.</p>
                            <a href="{{ route('super-admin.tenants.create') }}" class="mt-4 inline-flex items-center gap-2 py-2 px-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm transition">
                                <i class="fa-solid fa-user-plus"></i>
                                <span>تسجيل شركة جديدة</span>
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
