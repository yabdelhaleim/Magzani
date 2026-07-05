@extends('layouts.app')

@section('title', 'تسوية ضريبة القيمة المضافة')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">تسوية ضريبة القيمة المضافة</h2>
            <p class="text-gray-600 mt-1">حساب صافي الضريبة المستحقة وإنشاء قيد التسوية</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">{{ session('error') }}</div>
    @endif

    <form method="GET" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium mb-1">من</label>
            <input type="date" name="from" value="{{ $from }}" class="px-3 py-2 border rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">إلى</label>
            <input type="date" name="to" value="{{ $to }}" class="px-3 py-2 border rounded-lg">
        </div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">عرض</button>
    </form>

    @if(isset($preview['error']))
        <div class="p-4 bg-red-50 text-red-700 rounded-lg">{{ $preview['error'] }}</div>
    @elseif($preview)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="p-4 bg-blue-50 rounded-lg text-center">
                    <div class="text-2xl font-bold text-blue-700">{{ number_format($preview['output_vat'], 2) }}</div>
                    <div class="text-sm text-gray-600">ضريبة مخرجات (2210)</div>
                </div>
                <div class="p-4 bg-green-50 rounded-lg text-center">
                    <div class="text-2xl font-bold text-green-700">{{ number_format($preview['input_vat'], 2) }}</div>
                    <div class="text-sm text-gray-600">ضريبة مدخلات (1320)</div>
                </div>
                <div class="p-4 {{ $preview['net_payable'] >= 0 ? 'bg-red-50' : 'bg-yellow-50' }} rounded-lg text-center">
                    <div class="text-2xl font-bold">{{ number_format(abs($preview['net_payable']), 2) }}</div>
                    <div class="text-sm text-gray-600">{{ $preview['net_payable'] >= 0 ? 'صافي مستحق للهيئة' : 'رصيد مسترد' }}</div>
                </div>
            </div>

            @if($preview['output_vat'] > 0 || $preview['input_vat'] > 0)
                <form method="POST" action="{{ route('accounting.reports.vat-settlement') }}" onsubmit="return confirm('إنشاء قيد تسوية الضريبة؟')">
                    @csrf
                    <input type="hidden" name="from" value="{{ $from }}">
                    <input type="hidden" name="to" value="{{ $to }}">
                    <input type="hidden" name="settle" value="1">
                    <button type="submit" class="px-6 py-2.5 bg-red-600 text-white rounded-lg font-medium">
                        <i class="fas fa-file-invoice ml-1"></i> إنشاء قيد التسوية
                    </button>
                </form>
            @else
                <p class="text-gray-500 text-sm">لا توجد أرصدة ضريبية في هذه الفترة.</p>
            @endif
        </div>
    @endif
</div>
@endsection
