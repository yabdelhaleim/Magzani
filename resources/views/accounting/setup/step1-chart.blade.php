@extends('layouts.app')

@section('title', 'إعداد المحاسبة — دليل الحسابات')

@section('content')
<div class="space-y-6">
    {{-- Wizard Header --}}
    @include('accounting.setup.partials.wizard-header', ['currentStep' => 1])

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
                <i class="fas fa-sitemap text-blue-600"></i>
                مراجعة دليل الحسابات الافتراضي
            </h3>
            <p class="text-gray-600 mb-6">هذا هو دليل الحسابات الافتراضي. يمكنك تعديل الأسماء لاحقاً من صفحة دليل الحسابات.</p>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-right font-semibold text-gray-700">الكود</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700">الاسم (عربي)</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700">الاسم (إنجليزي)</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700">النوع</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700">ورقي</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700">نظامي</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($accounts as $account)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-2 font-mono text-gray-800" style="padding-right: {{ ($account->level - 1) * 24 + 16 }}px">
                                {{ $account->code }}
                            </td>
                            <td class="px-4 py-2 {{ $account->is_leaf ? 'text-gray-800' : 'font-bold text-gray-900' }}">
                                {{ $account->name_ar }}
                            </td>
                            <td class="px-4 py-2 text-gray-600" dir="ltr">{{ $account->name_en }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $account->accountType->code === 'asset' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $account->accountType->code === 'liability' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $account->accountType->code === 'equity' ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $account->accountType->code === 'revenue' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $account->accountType->code === 'expense' ? 'bg-orange-100 text-orange-700' : '' }}
                                ">
                                    {{ $account->accountType->name_ar }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">
                                @if($account->is_leaf)
                                    <i class="fas fa-check-circle text-green-500"></i>
                                @else
                                    <i class="fas fa-folder text-yellow-500"></i>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center">
                                @if($account->is_system)
                                    <i class="fas fa-lock text-gray-400"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between">
            <div></div>
            <form method="POST" action="{{ route('accounting.setup.save-chart') }}">
                @csrf
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2">
                    تأكيد والمتابعة
                    <i class="fas fa-arrow-left"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
