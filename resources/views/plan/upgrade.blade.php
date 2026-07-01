@extends('layouts.app')

@section('title', 'ترقية باقة متجرك')
@section('page-title', 'ترقية الباقة')

@section('content')
<div class="max-w-6xl mx-auto py-4">

    <!-- رسالة الحجب والتنبيه -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-amber-500/30 rounded-2xl p-6 mb-10 shadow-2xl relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-amber-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl"></div>
        
        <div class="flex flex-col md:flex-row items-center gap-6 relative z-10">
            <div class="w-16 h-16 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex items-center justify-center text-amber-500 text-3xl shrink-0 animate-pulse">
                <i class="fas fa-lock"></i>
            </div>
            <div class="text-center md:text-right shrink">
                <h2 class="text-2xl font-black text-white mb-2 font-cairo">هذه الميزة غير متاحة في باقتك الحالية!</h2>
                @if($reason)
                    <p class="text-slate-300 text-sm font-medium">
                        لقد حاولت الوصول إلى: 
                        <span class="text-amber-400 font-bold bg-amber-500/10 px-3 py-1 rounded-md border border-amber-500/20 mr-1">{{ $reason }}</span>
                    </p>
                @else
                    <p class="text-slate-300 text-sm font-medium">أنت بحاجة إلى ترقية باقتك للوصول إلى هذه الخاصية والاستفادة من الميزات المتقدمة للنظام.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- كروت مقارنة الباقات -->
    <div class="text-center mb-8">
        <h3 class="text-3xl font-black text-slate-800 font-cairo mb-2">اختر الباقة المناسبة لنمو أعمالك</h3>
        <p class="text-slate-500 text-sm max-w-lg mx-auto">قارن بين باقات نظام مخزني المتميزة واختر ما يلبي طموحاتك واحتياجات تجارتك.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12 items-stretch">
        
        <!-- باقة Starter -->
        <div class="bg-white rounded-3xl p-8 border {{ $currentPlanId === 'starter' ? 'border-indigo-500 ring-4 ring-indigo-500/10' : 'border-slate-100' }} shadow-xl flex flex-col justify-between relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
            @if($currentPlanId === 'starter')
                <div class="absolute top-4 left-4 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full">باقتك الحالية</div>
            @endif
            <div>
                <span class="text-indigo-600 text-xs font-black tracking-widest uppercase">الباقة الأساسية</span>
                <h4 class="text-2xl font-black text-slate-900 mt-2 font-cairo">Starter</h4>
                <p class="text-slate-400 text-xs mt-2 min-h-[32px]">باقة المبتدئين - تشمل الكاشير والمشتريات الأساسية</p>
                
                <div class="my-6">
                    <span class="text-4xl font-extrabold text-slate-900">99</span>
                    <span class="text-slate-400 text-sm font-bold">ريال / شهرياً</span>
                </div>
                
                <hr class="border-slate-100 my-6">
                
                <ul class="space-y-4">
                    <li class="flex items-center gap-3 text-slate-600 text-sm">
                        <i class="fas fa-check-circle text-emerald-500"></i>
                        <span>نقاط البيع شاشة الكاشير (POS)</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-600 text-sm">
                        <i class="fas fa-check-circle text-emerald-500"></i>
                        <span>فواتير المبيعات والمشتريات</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-400 text-sm line-through decoration-slate-300">
                        <i class="fas fa-lock text-slate-300"></i>
                        <span>تعدد المستودعات والجرد</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-400 text-sm line-through decoration-slate-300">
                        <i class="fas fa-lock text-slate-300"></i>
                        <span>إدارة عمليات التصنيع التلقائية</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-400 text-sm line-through decoration-slate-300">
                        <i class="fas fa-lock text-slate-300"></i>
                        <span>الحسابات والمصروفات المتقدمة</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-400 text-sm line-through decoration-slate-300">
                        <i class="fas fa-lock text-slate-300"></i>
                        <span>التقارير المالية والأرباح والخسائر</span>
                    </li>
                </ul>
            </div>
            
            <div class="mt-8">
                @if($currentPlanId === 'starter')
                    <button class="w-full bg-slate-100 text-slate-400 font-bold py-3 px-6 rounded-2xl cursor-not-allowed text-center" disabled>نشطة حالياً</button>
                @else
                    <a href="https://wa.me/966500000000?text={{ urlencode('أريد ترقية باقة متجري في نظام مخزني إلى باقة Starter') }}" target="_blank"
                       class="block w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-6 rounded-2xl transition text-center shadow-lg hover:shadow-xl">طلب الباقة</a>
                @endif
            </div>
        </div>

        <!-- باقة Pro (الموصى بها) -->
        <div class="bg-slate-900 rounded-3xl p-8 border {{ $currentPlanId === 'pro' ? 'border-amber-500 ring-4 ring-amber-500/10' : 'border-slate-800' }} shadow-2xl flex flex-col justify-between relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
            <div class="absolute -right-8 -top-8 w-24 h-24 bg-amber-500/10 rounded-full blur-xl"></div>
            <div class="absolute top-4 left-4 bg-gradient-to-r from-amber-500 to-amber-600 text-white text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full animate-bounce">الأكثر طلباً ⚡</div>
            
            <div>
                <span class="text-amber-500 text-xs font-black tracking-widest uppercase">باقة المحترفين</span>
                <h4 class="text-2xl font-black text-white mt-2 font-cairo">Pro</h4>
                <p class="text-slate-400 text-xs mt-2 min-h-[32px]">إدارة كاملة للمخازن، عمليات التصنيع، والحسابات بحدود مرنة</p>
                
                <div class="my-6">
                    <span class="text-4xl font-extrabold text-white">299</span>
                    <span class="text-slate-400 text-sm font-bold">ريال / شهرياً</span>
                </div>
                
                <hr class="border-slate-800 my-6">
                
                <ul class="space-y-4">
                    <li class="flex items-center gap-3 text-slate-300 text-sm">
                        <i class="fas fa-check-circle text-amber-500"></i>
                        <span>نقاط البيع شاشة الكاشير (POS)</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-300 text-sm">
                        <i class="fas fa-check-circle text-amber-500"></i>
                        <span>فواتير المبيعات والمشتريات المتقدمة</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-300 text-sm">
                        <i class="fas fa-check-circle text-amber-500"></i>
                        <span>تعدد المستودعات (حتى 5 مستودعات)</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-300 text-sm">
                        <i class="fas fa-check-circle text-amber-500"></i>
                        <span>إدارة عمليات التصنيع وتكاليف الخامات</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-300 text-sm">
                        <i class="fas fa-check-circle text-amber-500"></i>
                        <span>الحسابات والمصروفات والخزينة</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-300 text-sm">
                        <i class="fas fa-check-circle text-amber-500"></i>
                        <span>التقرير المالي والأرباح والخسائر</span>
                    </li>
                </ul>
            </div>
            
            <div class="mt-8">
                @if($currentPlanId === 'pro')
                    <button class="w-full bg-slate-800 text-slate-500 font-bold py-3 px-6 rounded-2xl cursor-not-allowed text-center" disabled>نشطة حالياً</button>
                @else
                    <a href="https://wa.me/966500000000?text={{ urlencode('أريد ترقية باقة متجري في نظام مخزني إلى باقة Pro الاحترافية') }}" target="_blank"
                       class="block w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-3 px-6 rounded-2xl transition text-center shadow-lg hover:shadow-amber-500/20">ترقية الباقة الآن</a>
                @endif
            </div>
        </div>

        <!-- باقة Enterprise -->
        <div class="bg-white rounded-3xl p-8 border {{ $currentPlanId === 'enterprise' ? 'border-indigo-500 ring-4 ring-indigo-500/10' : 'border-slate-100' }} shadow-xl flex flex-col justify-between relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
            @if($currentPlanId === 'enterprise')
                <div class="absolute top-4 left-4 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full">باقتك الحالية</div>
            @endif
            <div>
                <span class="text-indigo-600 text-xs font-black tracking-widest uppercase">باقة الشركات والنمو</span>
                <h4 class="text-2xl font-black text-slate-900 mt-2 font-cairo">Enterprise</h4>
                <p class="text-slate-400 text-xs mt-2 min-h-[32px]">الباقة اللامحدودة لكافة الميزات والخصائص دون أي قيود</p>
                
                <div class="my-6">
                    <span class="text-4xl font-extrabold text-slate-900">599</span>
                    <span class="text-slate-400 text-sm font-bold">ريال / شهرياً</span>
                </div>
                
                <hr class="border-slate-100 my-6">
                
                <ul class="space-y-4">
                    <li class="flex items-center gap-3 text-slate-600 text-sm">
                        <i class="fas fa-check-circle text-emerald-500"></i>
                        <span>كل ما تشمله الباقة الاحترافية Pro</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-600 text-sm">
                        <i class="fas fa-check-circle text-emerald-500"></i>
                        <span>عدد مستودعات وتصنيع لا نهائي</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-600 text-sm">
                        <i class="fas fa-check-circle text-emerald-500"></i>
                        <span>مزامنة جرد المستودعات المتقدمة</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-600 text-sm">
                        <i class="fas fa-check-circle text-emerald-500"></i>
                        <span>تقارير حركة الخشب والتحليل الدقيق</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-600 text-sm">
                        <i class="fas fa-check-circle text-emerald-500"></i>
                        <span>سجل عمليات وأمان متكامل للمستخدمين</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-600 text-sm">
                        <i class="fas fa-check-circle text-emerald-500"></i>
                        <span>دعم فني خاص على مدار الساعة</span>
                    </li>
                </ul>
            </div>
            
            <div class="mt-8">
                @if($currentPlanId === 'enterprise')
                    <button class="w-full bg-slate-100 text-slate-400 font-bold py-3 px-6 rounded-2xl cursor-not-allowed text-center" disabled>نشطة حالياً</button>
                @else
                    <a href="https://wa.me/966500000000?text={{ urlencode('أريد ترقية باقة متجري في نظام مخزني إلى باقة Enterprise الشاملة') }}" target="_blank"
                       class="block w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-6 rounded-2xl transition text-center shadow-lg hover:shadow-xl">تواصل للترقية</a>
                @endif
            </div>
        </div>

    </div>

    <!-- قسم اتصل بنا للتواصل اليدوي والسريع للترقية -->
    <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-xl text-center max-w-3xl mx-auto relative overflow-hidden">
        <div class="absolute -right-20 -bottom-20 w-48 h-48 bg-emerald-500/5 rounded-full blur-2xl"></div>
        <h3 class="text-xl font-black text-slate-800 font-cairo mb-3">هل ترغب بمميزات مخصصة لشركتك؟</h3>
        <p class="text-slate-500 text-sm mb-6 max-w-lg mx-auto">فريق الدعم الفني جاهز لمساعدتك في تخصيص باقة تناسب حجم متجرك وتطلعاتك، تواصل معنا الآن عبر قنوات الاتصال المباشرة.</p>
        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="https://wa.me/966500000000?text={{ urlencode('مرحباً، أريد الاستفسار عن تفاصيل ترقية الباقة لمتجري في نظام مخزني') }}" target="_blank"
               class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold py-3 px-8 rounded-2xl transition shadow-lg hover:shadow-emerald-600/20 shrink-0">
                <i class="fab fa-whatsapp text-lg"></i>
                <span>تواصل معنا عبر واتساب</span>
            </a>
            
            <a href="tel:+966500000000"
               class="flex items-center gap-2 border border-slate-200 hover:bg-slate-50 text-slate-700 font-extrabold py-3 px-8 rounded-2xl transition shrink-0">
                <i class="fas fa-phone-alt text-sm"></i>
                <span>اتصال مباشر بالدعم الفني</span>
            </a>
        </div>
    </div>

</div>
@endsection
