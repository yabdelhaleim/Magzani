@extends('layouts.app')

@section('title', 'احتساب الإهلاك الشهري')

@section('content')
<div class="max-w-xl mx-auto space-y-6 font-sans">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">إهلاك الأصول الثابتة</h2>
            <p class="text-gray-600 mt-1">تشغيل أداة احتساب الإهلاك التلقائي لجميع الأصول الثابتة النشطة.</p>
        </div>
        <a href="{{ route('accounting.fixed-assets.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium border border-gray-200 transition-colors">
            إلغاء والعودة
        </a>
    </div>

    <!-- Instructions Card -->
    <div class="bg-blue-50/50 p-6 rounded-xl border border-blue-100 text-blue-900 space-y-3">
        <h3 class="font-bold flex items-center gap-2"><i class="fas fa-info-circle"></i> كيف يعمل إهلاك الأصول؟</h3>
        <ul class="list-disc list-inside space-y-1.5 text-sm">
            <li>سيقوم النظام بالمرور على جميع الأصول الثابتة التي حالتها <strong>"نشط"</strong> وتاريخ شرائها قبل أو يساوي التاريخ المختار.</li>
            <li>يتم احتساب القسط الشهري للأصل بناءً على طريقة القسط الثابت: <code>(تكلفة الشراء - الخردة) / (العمر الإنتاجي بالسنوات * 12)</code>.</li>
            <li>سيتم إنشاء قيد يومية معتمد لكل أصل: مدين لحساب مصروف الإهلاك المختار ودائن لحساب مجمع الإهلاك.</li>
            <li>العملية متكررة وآمنة (Idempotent)؛ إذا تم إهلاك أصل ما في الشهر المختار فلن يتم تكرار إهلاكه.</li>
        </ul>
    </div>

    <!-- Depreciate Form -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <form method="POST" action="{{ route('accounting.fixed-assets.depreciate.run') }}" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ تشغيل الإهلاك (تاريخ إقفال الشهر) *</label>
                <input type="date" name="date" value="{{ $defaultDate }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <span class="text-xs text-gray-400 mt-1.5 block">سيتم ترحيل قيد الإهلاك في هذا التاريخ (يُنصح باختيار نهاية الشهر المطلوب).</span>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" onclick="return confirm('هل أنت متأكد من رغبتك في تشغيل عملية إهلاك الأصول للشهر المختار؟')" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center gap-1.5">
                    <i class="fas fa-play"></i> تشغيل واحتساب الإهلاك
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
