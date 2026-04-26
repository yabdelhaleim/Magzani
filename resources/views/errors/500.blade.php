<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطأ في الخادم</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-red-50 to-orange-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-red-600 to-orange-600 p-8 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 rounded-full mb-4">
                <i class="fas fa-server text-4xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">عذراً، حدث خطأ</h1>
        </div>
        <div class="p-8 text-center">
            <p class="text-gray-700 text-lg mb-2">{{ $message ?? 'حدث خطأ غير متوقع في الخادم' }}</p>
            <p class="text-gray-500 text-sm mb-6">نحن نعمل على حل المشكلة. يرجى المحاولة مرة أخرى.</p>
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
        </div>
    </div>
</body>
</html>
