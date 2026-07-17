@extends('landlord.layout')

@section('title', 'إدارة باقات الاشتراك')

@section('content')
<div class="space-y-5 sm:space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-wrap justify-between items-center gap-3">
        <h3 class="text-base sm:text-lg font-bold text-slate-200">الباقات الحالية في المنصة</h3>
        <a href="{{ route('super-admin.plans.create') }}" class="py-2 px-4 sm:px-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition flex items-center gap-2 shadow-lg text-sm">
            <i class="fa-solid fa-plus text-xs sm:text-sm"></i>
            <span>إضافة باقة جديدة</span>
        </a>
    </div>

    <!-- Plans List Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5 lg:gap-6">
        @forelse($plans as $plan)
        <div class="glass-card rounded-2xl p-4 sm:p-5 lg:p-6 flex flex-col justify-between shadow-xl relative overflow-hidden">
            @if(!$plan->is_active)
            <div class="absolute top-3 left-3 sm:top-4 sm:left-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 px-2.5 py-0.5 rounded-full text-[11px] sm:text-xs font-semibold">مغلقة</div>
            @else
            <div class="absolute top-3 left-3 sm:top-4 sm:left-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-2.5 py-0.5 rounded-full text-[11px] sm:text-xs font-semibold">نشطة</div>
            @endif

            <div class="space-y-3 sm:space-y-4">
                <div class="pr-20">
                    <h4 class="text-lg sm:text-xl font-extrabold text-white">{{ $plan->name }}</h4>
                    <span class="text-xs text-slate-500 font-mono block mt-1 truncate">slug: {{ $plan->slug }}</span>
                </div>

                <div class="text-xl sm:text-2xl font-black text-purple-400">
                    {{ $plan->price }} <span class="text-xs text-slate-500 font-normal">ج.م / {{ $plan->billing_period === 'monthly' ? 'شهرياً' : 'سنوياً' }}</span>
                </div>

                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">{{ $plan->description ?: 'لا يوجد وصف لهذه الباقة.' }}</p>

                <!-- Features list -->
                <div class="border-t border-slate-800/60 pt-3 sm:pt-4">
                    <span class="text-xs text-slate-500 font-semibold block mb-2">الميزات المفعلة في الباقة:</span>
                    <ul class="space-y-1.5 text-xs text-slate-300">
                        @if(is_array($plan->features) && count($plan->features) > 0)
                            @foreach($plan->features as $feature)
                            <li class="flex items-center gap-2">
                                <i class="fa-solid fa-square-check text-emerald-400 shrink-0"></i>
                                <span>
                                    @switch($feature)
                                        @case('pos') نظام نقاط البيع POS @break
                                        @case('manufacturing') نظام التصنيع والتكاليف @break
                                        @case('accounting') الحسابات والتقارير المالية @break
                                        @default {{ $feature }}
                                    @endswitch
                                </span>
                            </li>
                            @endforeach
                        @else
                            <li class="text-slate-500 italic">لا توجد ميزات مفعلة (باقة فارغة).</li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 border-t border-slate-800/60 pt-4 sm:pt-6 mt-4 sm:mt-6">
                <a href="{{ route('super-admin.plans.edit', $plan->id) }}" class="flex-1 py-2 text-center bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 border border-slate-700/30">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>تعديل</span>
                </a>

                <form action="{{ route('super-admin.plans.destroy', $plan->id) }}" method="POST" class="flex-1" onsubmit="return confirm('هل أنت متأكد من حذف هذه الباقة؟ لن يؤثر الحذف على اشتراكات العملاء الحالية ولكنها لن تكون متاحة للمشتركين الجدد.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 border border-rose-500/20">
                        <i class="fa-solid fa-trash-can"></i>
                        <span>حذف</span>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full glass-card p-8 sm:p-12 text-center rounded-2xl">
            <div class="w-14 h-14 sm:w-16 sm:h-16 bg-slate-800 text-slate-500 rounded-2xl flex items-center justify-center text-2xl sm:text-3xl mx-auto mb-4">
                <i class="fa-solid fa-tags"></i>
            </div>
            <h4 class="text-base sm:text-lg font-bold text-slate-300">لا توجد باقات اشتراك حالياً</h4>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-sm mx-auto">قم بإنشاء أول باقة في المنصة لتتمكن من تخصيصها للمستأجرين وعرضها في صفحة التسعير.</p>
            <a href="{{ route('super-admin.plans.create') }}" class="mt-4 inline-flex items-center gap-2 py-2 px-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm transition">
                <i class="fa-solid fa-plus"></i>
                <span>إنشاء باقة جديدة</span>
            </a>
        </div>
        @endforelse
    </div>

</div>
@endsection
