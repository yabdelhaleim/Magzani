<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطأ {{ $status ?? '500' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-8 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 rounded-full mb-4">
                <i class="fas fa-exclamation-triangle text-4xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">خطأ {{ $status ?? '500' }}</h1>
        </div>
        <div class="p-8 text-center">
            <p class="text-gray-700 text-lg mb-6">{{ $message ?? 'حدث خطأ ما' }}</p>
            <div class="space-y-3">
                <a href="{{ url()->previous() }}" class="block w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition">
                    <i class="fas fa-arrow-right ml-2"></i>
                    العودة للخلف
                </a>
                <a href="{{ route('dashboard') }}" class="block w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                    <i class="fas fa-home ml-2"></i>
                    الرئيسية
                </a>
            </div>
            @if(config('app.debug'))
            <div class="mt-6 p-4 bg-red-50 rounded-xl text-right">
                <p class="text-sm text-red-600 font-semibold mb-2">تفاصيل الخطأ (وضع التطوير):</p>
                @if(isset($exception))
                <p class="text-xs text-red-500 font-mono">{{ $exception->getMessage() }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>
</body>
</html>
