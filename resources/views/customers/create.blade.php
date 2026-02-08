@extends('layouts.app')

@section('title', 'إضافة عميل جديد')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">

            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-4">
                <h2 class="text-2xl font-bold text-white">إضافة عميل جديد</h2>
            </div>

            {{-- Form --}}
            <form action="{{ route('customers.store') }}" method="POST" class="p-6">
                @csrf

                {{-- Errors --}}
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            الاسم الكامل *
                        </label>
                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg
                                      focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            رقم الهاتف
                        </label>
                        <input type="text"
                               name="phone"
                               value="{{ old('phone') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg
                                      focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            البريد الإلكتروني
                        </label>
                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg
                                      focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    {{-- Address --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            العنوان
                        </label>
                        <input type="text"
                               name="address"
                               value="{{ old('address') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg
                                      focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    {{-- Code --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            كود العميل
                        </label>
                        <input type="text"
                               name="code"
                               value="{{ old('code') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg
                                      focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    {{-- Active Status --}}
                    <div class="flex items-center mt-6">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            checked
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        >
                        <label class="ml-3 text-sm font-medium text-gray-700">
                            العميل نشط
                        </label>
                    </div>

                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-4 mt-8">
                    <a href="{{ route('customers.index') }}"
                       class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700">
                        رجوع
                    </a>

                    <button type="submit"
                            class="px-6 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                        حفظ العميل
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
