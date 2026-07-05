@extends('layouts.app')

@section('title', 'إعداد المحاسبة — الترحيل التلقائي')

@section('content')
<div class="space-y-6">
    @include('accounting.setup.partials.wizard-header', ['currentStep' => 4])

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
                <i class="fas fa-robot text-blue-600"></i>
                إعدادات الترحيل التلقائي
            </h3>
            <p class="text-gray-600 mb-6">حدد أنواع العمليات التي يتم ترحيلها تلقائياً لدفتر الأستاذ العام عند تأكيدها.</p>

            <form method="POST" action="{{ route('accounting.setup.save-auto-posting') }}">
                @csrf

                <div class="space-y-4">
                    {{-- الفواتير --}}
                    <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
                        <div>
                            <span class="font-medium text-gray-800">ترحيل الفواتير تلقائياً</span>
                            <p class="text-sm text-gray-500 mt-1">فواتير المبيعات والمشتريات والمرتجعات</p>
                        </div>
                        <input type="checkbox" name="auto_post_invoices" value="1"
                            {{ $settings?->auto_post_invoices ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                    </label>

                    {{-- المدفوعات --}}
                    <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
                        <div>
                            <span class="font-medium text-gray-800">ترحيل المدفوعات تلقائياً</span>
                            <p class="text-sm text-gray-500 mt-1">دفعات العملاء ومدفوعات الموردين</p>
                        </div>
                        <input type="checkbox" name="auto_post_payments" value="1"
                            {{ $settings?->auto_post_payments ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                    </label>

                    {{-- المصروفات --}}
                    <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
                        <div>
                            <span class="font-medium text-gray-800">ترحيل عمليات الخزينة تلقائياً</span>
                            <p class="text-sm text-gray-500 mt-1">إيداعات وسحوبات ومصروفات نقدية</p>
                        </div>
                        <input type="checkbox" name="auto_post_expenses" value="1"
                            {{ $settings?->auto_post_expenses ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                    </label>

                    {{-- التصنيع --}}
                    <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
                        <div>
                            <span class="font-medium text-gray-800">ترحيل أوامر التصنيع تلقائياً</span>
                            <p class="text-sm text-gray-500 mt-1">نقل المواد الخام والإنتاج التام</p>
                        </div>
                        <input type="checkbox" name="auto_post_manufacturing" value="1"
                            {{ $settings?->auto_post_manufacturing ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                    </label>
                </div>

                <div class="flex justify-between pt-4 mt-6 border-t border-gray-100">
                    <a href="{{ route('accounting.setup.step', 3) }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-right ml-1"></i> السابقة
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2">
                        حفظ والمتابعة
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
